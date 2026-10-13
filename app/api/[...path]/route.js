import { NextResponse } from "next/server";
import { cookies } from "next/headers";
import { query, db } from "../../../lib/db.mjs";
import { user, createSession } from "../../../lib/auth.mjs";
import {
  paymentSettings,
  validatePaymentSettings,
  googleReady,
} from "../../../lib/settings.mjs";
import { products, categories, brands } from "../../../lib/catalog.mjs";
import {
  hashPassword,
  verifyPassword,
  token,
  digest,
  sameOrigin,
} from "../../../lib/security.mjs";
import {
  validateProduct,
  normalizeItems,
  shippingFor,
} from "../../../lib/validation.mjs";
export const runtime = "nodejs";
export const dynamic = "force-dynamic";
const json = (data, status = 200) => NextResponse.json(data, { status });
async function handle(req, ctx) {
  const path = (await ctx.params).path.join("/");
  const method = req.method;
  try {
    if (method !== "GET" && !sameOrigin(req))
      return json({ error: "This request must come from the store." }, 403);
    if (path === "catalog" && method === "GET") {
      try {
        return json({
          products: await query(
            "SELECT * FROM products WHERE active=TRUE ORDER BY id",
          ),
          categories: (await query("SELECT name FROM categories")).map(
            (x) => x.name,
          ),
          brands: (await query("SELECT name FROM brands")).map((x) => x.name),
          demo: false,
          payments: await paymentSettings(),
        });
      } catch {
        return json({ products, categories, brands, demo: true });
      }
    }
    if (path === "auth" && method === "GET")
      return json({ user: await user(), google_enabled: googleReady() });
    if (path === "settings" && method === "GET")
      return json({
        payments: await paymentSettings(),
        google_enabled: googleReady(),
      });
    if (path === "auth/logout" && method === "POST") {
      const jar = await cookies();
      const t = jar.get("zariya_session")?.value;
      if (t)
        await query("DELETE FROM sessions WHERE token_hash=?", [digest(t)]);
      jar.delete("zariya_session");
      return json({ ok: true });
    }
    if (
      (path === "auth/login" || path === "auth/register") &&
      method === "POST"
    ) {
      const b = await req.json();
      const email = String(b.email || "")
        .toLowerCase()
        .trim();
      if (
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) ||
        email.length > 190 ||
        typeof b.password !== "string" ||
        b.password.length > 128
      )
        return json({ error: "Enter a valid email and password." }, 400);
      const key = digest(email);
      await query(
        "INSERT IGNORE INTO login_attempts (attempt_key,attempts,window_start) VALUES (?,0,NOW())",
        [key],
      );
      await query(
        "UPDATE login_attempts SET attempts=IF(window_start<DATE_SUB(NOW(),INTERVAL 15 MINUTE),1,attempts+1),window_start=IF(window_start<DATE_SUB(NOW(),INTERVAL 15 MINUTE),NOW(),window_start) WHERE attempt_key=?",
        [key],
      );
      const [limit] = await query(
        "SELECT attempts FROM login_attempts WHERE attempt_key=?",
        [key],
      );
      if (limit.attempts > 10)
        return json(
          { error: "Too many attempts. Try again in 15 minutes." },
          429,
        );
      if (path.endsWith("register")) {
        if (
          b.password.length < 12 ||
          !String(b.name || "").trim() ||
          String(b.name).length > 100
        )
          return json(
            {
              error:
                "Enter your name and a password of at least 12 characters.",
            },
            400,
          );
        await query(
          "INSERT INTO users (name,email,password_hash) VALUES (?,?,?)",
          [b.name.trim(), email, hashPassword(b.password)],
        );
      }
      const [u] = await query("SELECT * FROM users WHERE email=?", [email]);
      if (!u || !verifyPassword(b.password, u.password_hash))
        return json({ error: "Email or password is incorrect." }, 401);
      await query("DELETE FROM login_attempts WHERE attempt_key=?", [key]);
      await createSession(u.id);
      return json({
        user: { id: u.id, name: u.name, email: u.email, role: u.role },
      });
    }
    if (path === "orders" && method === "POST") {
      const u = await user();
      if (!u)
        return json(
          {
            error: "Sign in or create an account before placing your order.",
            code: "AUTH_REQUIRED",
          },
          401,
        );
      const b = await req.json();
      const payments = await paymentSettings();
      if (
        !["cod", "bank"].includes(b.payment_method) ||
        !payments[b.payment_method + "_enabled"]
      )
        return json(
          {
            error:
              "This payment option is currently disabled. Please choose an available option.",
          },
          400,
        );
      const items = normalizeItems(b.items);
      for (const k of ["name", "email", "phone", "address", "city"])
        if (
          typeof b[k] !== "string" ||
          !b[k].trim() ||
          b[k].length > (k === "address" ? 500 : 190)
        )
          return json({ error: `Enter a valid ${k}.` }, 400);
      if (
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(b.email) ||
        !/^\+?[\d\s-]{10,20}$/.test(b.phone) ||
        b.name.length > 100 ||
        b.city.length > 100
      )
        return json(
          { error: "Please check your name, email, phone and city." },
          400,
        );
      if (!["cod", "bank"].includes(b.payment_method))
        return json({ error: "Choose a supported payment method." }, 400);
      const c = await db().getConnection();
      try {
        await c.beginTransaction();
        let subtotal = 0;
        const lines = [];
        for (const i of items) {
          const [rows] = await c.execute(
            "SELECT * FROM products WHERE id=? AND active=TRUE FOR UPDATE",
            [i.id],
          );
          const p = rows[0];
          if (!p || p.stock < i.quantity)
            throw Error(
              `${p?.name || "A saree"} has insufficient stock. Please update your bag.`,
            );
          subtotal += Number(p.price) * i.quantity;
          lines.push({ ...i, name: p.name, price: Number(p.price) });
        }
        const shipping = shippingFor(subtotal);
        const reference =
          "ZR-" +
          Date.now().toString(36).toUpperCase() +
          "-" +
          token().slice(0, 6).toUpperCase();
        const [r] = await c.execute(
          "INSERT INTO orders (reference,user_id,name,email,phone,address,city,payment_method,total,shipping) VALUES (?,?,?,?,?,?,?,?,?,?)",
          [
            reference,
            u?.id || null,
            b.name.trim(),
            b.email.trim().toLowerCase(),
            b.phone,
            b.address,
            b.city,
            b.payment_method,
            subtotal + shipping,
            shipping,
          ],
        );
        for (const i of lines) {
          await c.execute(
            "INSERT INTO order_items (order_id,product_id,name,quantity,price) VALUES (?,?,?,?,?)",
            [r.insertId, i.id, i.name, i.quantity, i.price],
          );
          await c.execute("UPDATE products SET stock=stock-? WHERE id=?", [
            i.quantity,
            i.id,
          ]);
        }
        await c.commit();
        return json(
          {
            reference,
            total: subtotal + shipping,
            payment_method: b.payment_method,
          },
          201,
        );
      } catch (e) {
        await c.rollback();
        throw e;
      } finally {
        c.release();
      }
    }
    if (path === "orders" && method === "GET") {
      const u = await user();
      if (!u) return json({ error: "Please sign in." }, 401);
      return json({
        orders: await query(
          "SELECT * FROM orders WHERE user_id=? ORDER BY id DESC",
          [u.id],
        ),
      });
    }
    if (path === "track" && method === "POST") {
      const b = await req.json();
      const rows = await query(
        "SELECT reference,status,total,created_at,payment_method FROM orders WHERE reference=? AND email=?",
        [
          String(b.reference || "")
            .trim()
            .toUpperCase(),
          String(b.email || "")
            .trim()
            .toLowerCase(),
        ],
      );
      if (!rows.length)
        return json({ error: "No order matches those details." }, 404);
      return json({ order: rows[0] });
    }
    if (path.startsWith("admin/")) {
      const u = await user();
      if (u?.role !== "admin")
        return json({ error: "Administrator access required." }, 403);
      if (path === "admin/settings" && method === "PUT") {
        const value = validatePaymentSettings(await req.json());
        await query(
          "UPDATE store_settings SET cod_enabled=?,bank_enabled=?,card_enabled=FALSE WHERE id=1",
          [value.cod_enabled, value.bank_enabled],
        );
        return json({ payments: await paymentSettings() });
      }
      if (path === "admin/data" && method === "GET") {
        const [products, orders, categories, brands, customers, stats] =
          await Promise.all([
            query("SELECT * FROM products ORDER BY id DESC"),
            query("SELECT * FROM orders ORDER BY id DESC LIMIT 500"),
            query("SELECT * FROM categories"),
            query("SELECT * FROM brands"),
            query(
              "SELECT id,name,email,created_at FROM users WHERE role='customer' ORDER BY id DESC LIMIT 500",
            ),
            query(
              "SELECT COUNT(*) AS total_orders,COALESCE(SUM(status='Pending'),0) AS pending_orders,COALESCE(SUM(CASE WHEN status='Delivered' THEN total ELSE 0 END),0) AS revenue FROM orders",
            ),
          ]);
        return json({
          products,
          orders,
          categories,
          brands,
          customers,
          stats: stats[0],
          payments: await paymentSettings(),
          google_enabled: googleReady(),
        });
      }
      if (path === "admin/products" && ["POST", "PUT"].includes(method)) {
        const b = await req.json();
        const p = validateProduct(b);
        if (
          !(await query("SELECT id FROM categories WHERE name=?", [p.category]))
            .length ||
          !(await query("SELECT id FROM brands WHERE name=?", [p.brand])).length
        )
          return json({ error: "Category and brand must exist first." }, 400);
        const vals = Object.values(p);
        if (method === "POST")
          await query(
            "INSERT INTO products (name,category,brand,price,compare_price,stock,color,fabric,badge,image,description) VALUES (?,?,?,?,?,?,?,?,?,?,?)",
            vals,
          );
        else {
          if (!Number.isInteger(b.id))
            return json({ error: "Invalid product ID." }, 400);
          await query(
            "UPDATE products SET name=?,category=?,brand=?,price=?,compare_price=?,stock=?,color=?,fabric=?,badge=?,image=?,description=? WHERE id=?",
            [...vals, b.id],
          );
        }
        return json({ ok: true });
      }
      if (path === "admin/products" && method === "DELETE") {
        const b = await req.json();
        await query("UPDATE products SET active=NOT active WHERE id=?", [b.id]);
        return json({ ok: true });
      }
      if (["admin/categories", "admin/brands"].includes(path)) {
        const table = path.split("/")[1];
        const b = await req.json();
        if (method === "POST") {
          if (
            typeof b.name !== "string" ||
            !b.name.trim() ||
            b.name.length > 100
          )
            return json({ error: "Enter a valid name." }, 400);
          await query(`INSERT INTO ${table} (name) VALUES (?)`, [
            b.name.trim(),
          ]);
          return json({ ok: true });
        }
        if (method === "DELETE") {
          const [entry] = await query(`SELECT name FROM ${table} WHERE id=?`, [
            b.id,
          ]);
          if (!entry) return json({ error: "Entry not found." }, 404);
          const field = table === "brands" ? "brand" : "category";
          if (
            (
              await query(`SELECT id FROM products WHERE ${field}=? LIMIT 1`, [
                entry.name,
              ])
            ).length
          )
            return json(
              { error: "Move associated products before deleting this entry." },
              409,
            );
          await query(`DELETE FROM ${table} WHERE id=?`, [b.id]);
          return json({ ok: true });
        }
      }
      if (path === "admin/orders" && method === "PUT") {
        const b = await req.json();
        const stages = [
          "Pending",
          "Confirmed",
          "Shipped",
          "Delivered",
          "Cancelled",
        ];
        if (!stages.includes(b.status) || !Number.isInteger(b.id))
          return json({ error: "Invalid order update." }, 400);
        const c = await db().getConnection();
        try {
          await c.beginTransaction();
          const [rows] = await c.execute(
            "SELECT * FROM orders WHERE id=? FOR UPDATE",
            [b.id],
          );
          const order = rows[0];
          if (!order) throw Error("Order not found.");
          const allowed = {
            Pending: ["Confirmed", "Cancelled"],
            Confirmed: ["Shipped", "Cancelled"],
            Shipped: ["Delivered"],
            Delivered: [],
            Cancelled: [],
          };
          if (
            order.status !== b.status &&
            !allowed[order.status].includes(b.status)
          )
            throw Error("That status transition is not allowed.");
          if (b.status === "Cancelled" && order.status !== "Cancelled") {
            const [lines] = await c.execute(
              "SELECT * FROM order_items WHERE order_id=? ORDER BY product_id",
              [b.id],
            );
            for (const line of lines)
              await c.execute("UPDATE products SET stock=stock+? WHERE id=?", [
                line.quantity,
                line.product_id,
              ]);
          }
          await c.execute("UPDATE orders SET status=? WHERE id=?", [
            b.status,
            b.id,
          ]);
          await c.commit();
          return json({ ok: true });
        } catch (e) {
          await c.rollback();
          throw e;
        } finally {
          c.release();
        }
      }
      if (path === "admin/order-items" && method === "GET")
        return json({
          items: await query("SELECT * FROM order_items WHERE order_id=?", [
            new URL(req.url).searchParams.get("id"),
          ]),
        });
    }
    return json({ error: "Endpoint not found." }, 404);
  } catch (e) {
    if (
      [
        "ECONNREFUSED",
        "ER_BAD_DB_ERROR",
        "ER_NO_SUCH_TABLE",
        "ER_ACCESS_DENIED_ERROR",
      ].includes(e.code)
    )
      return json(
        {
          error:
            "Database is not ready. Start MySQL in XAMPP and follow the database setup in README.md.",
        },
        503,
      );
    if (e.code === "ER_DUP_ENTRY")
      return json({ error: "This email or name already exists." }, 409);
    if (e instanceof SyntaxError)
      return json({ error: "Invalid request." }, 400);
    if (e.code) {
      console.error("Database request failed:", e.code);
      return json(
        { error: "The request could not be completed. Please try again." },
        500,
      );
    }
    return json({ error: e.message || "Request failed." }, 400);
  }
}
export const GET = handle;
export const POST = handle;
export const PUT = handle;
export const DELETE = handle;
