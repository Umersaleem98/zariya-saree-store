// Run against this LOCAL development store only: node tests/integration.mjs
// Creates uniquely named disposable records, then removes only those records.
import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
for (const line of readFileSync(".env.local", "utf8").split(/\r?\n/)) {
  const m = line.match(/^([A-Z_]+)=(.*)$/);
  if (m) process.env[m[1]] = m[2];
}
if (
  process.env.DB_HOST !== "127.0.0.1" ||
  process.env.DB_NAME !== "zariya_store"
)
  throw Error("This check only runs on the local zariya_store database.");
const { query, db } = await import("../lib/db.mjs");
const base = "http://127.0.0.1:3000";
let adminCookie = "",
  customerCookie = "";
const name = "QA-" + Date.now(),
  email = name.toLowerCase() + "@example.test";
let pid, uid;
const orderIds = [];
async function call(path, method = "GET", body, cookie = "") {
  const r = await fetch(base + "/api/" + path, {
    method,
    headers: { origin: base, "Content-Type": "application/json", cookie },
    ...(body ? { body: JSON.stringify(body) } : {}),
  });
  const data = await r.json();
  return {
    status: r.status,
    data,
    cookie: r.headers.get("set-cookie")?.split(";")[0] || "",
  };
}
try {
  assert.equal((await call("admin/data")).status, 403);
  assert.equal((await call("orders", "POST", {})).status, 401);
  assert.equal(
    (
      await call("admin/settings", "PUT", {
        cod_enabled: true,
        bank_enabled: false,
        card_enabled: false,
      })
    ).status,
    403,
  );
  const csrf = await fetch(base + "/api/orders", {
    method: "POST",
    headers: {
      origin: "http://evil.example",
      "Content-Type": "application/json",
    },
    body: "{}",
  });
  assert.equal(csrf.status, 403);
  const admin = await call("auth/login", "POST", {
    email: process.env.ADMIN_EMAIL,
    password: process.env.ADMIN_PASSWORD,
  });
  assert.equal(admin.status, 200, JSON.stringify(admin.data));
  adminCookie = admin.cookie;
  const savedPayments = (await call("settings")).data.payments;
  assert.equal(
    (
      await call(
        "admin/settings",
        "PUT",
        {
          cod_enabled: savedPayments.cod_enabled,
          bank_enabled: savedPayments.bank_enabled,
          card_enabled: false,
        },
        adminCookie,
      )
    ).status,
    200,
  );
  const callback = await fetch(
    base + "/api/auth/google/callback?state=invalid&code=invalid",
    { redirect: "manual" },
  );
  assert.equal(callback.status, 307);
  assert.match(callback.headers.get("location"), /auth_error=google_failed/);
  const reg = await call("auth/register", "POST", {
    email,
    name,
    password: "Integration-test-password!" + Date.now(),
    role: "admin",
  });
  assert.equal(reg.status, 200, JSON.stringify(reg.data));
  assert.equal(reg.data.user.role, "customer");
  uid = reg.data.user.id;
  customerCookie = reg.cookie;
  assert.equal(
    (
      await call(
        "admin/settings",
        "PUT",
        { cod_enabled: true, bank_enabled: false, card_enabled: false },
        customerCookie,
      )
    ).status,
    403,
  );
  assert.equal(
    (await call("admin/data", "GET", null, customerCookie)).status,
    403,
  );
  assert.equal(
    (await call("admin/categories", "POST", { name }, adminCookie)).status,
    200,
  );
  assert.equal(
    (await call("admin/brands", "POST", { name }, adminCookie)).status,
    200,
  );
  assert.equal(
    (
      await call(
        "admin/products",
        "POST",
        {
          name,
          category: name,
          brand: name,
          price: 1234,
          stock: 1,
          image: "/images/gold.jpg",
        },
        adminCookie,
      )
    ).status,
    200,
  );
  pid = (await query("SELECT id FROM products WHERE name=?", [name]))[0].id;
  const payload = {
    name: "QA Shopper",
    email,
    phone: "03001234567",
    address: "Test address (not for delivery)",
    city: "Lahore",
    payment_method: "cod",
    items: [{ id: pid, quantity: 1, price: 1 }],
    total: 1,
  };
  const settings = (await call("settings")).data.payments;
  if (!settings.cod_enabled)
    throw Error("Enable cash on delivery before running this checkout test.");
  assert.equal(settings.card_enabled, false);
  assert.equal(
    (
      await call(
        "admin/settings",
        "PUT",
        { cod_enabled: true, bank_enabled: false, card_enabled: true },
        adminCookie,
      )
    ).status,
    400,
  );
  if (!settings.bank_enabled)
    assert.equal(
      (
        await call(
          "orders",
          "POST",
          { ...payload, payment_method: "bank" },
          customerCookie,
        )
      ).status,
      400,
    );
  assert.equal(
    (
      await call(
        "orders",
        "POST",
        { ...payload, payment_method: "card" },
        customerCookie,
      )
    ).status,
    400,
  );
  const results = await Promise.all([
    call("orders", "POST", payload, customerCookie),
    call("orders", "POST", payload, customerCookie),
  ]);
  assert.deepEqual(results.map((r) => r.status).sort(), [201, 400]);
  const order = results.find((r) => r.status === 201).data;
  assert.equal(Number(order.total), 1484);
  assert.equal(
    (await query("SELECT stock FROM products WHERE id=?", [pid]))[0].stock,
    0,
  );
  const row = (
    await query("SELECT id FROM orders WHERE reference=?", [order.reference])
  )[0];
  orderIds.push(row.id);
  assert.equal(
    (await call("track", "POST", { reference: order.reference, email })).status,
    200,
  );
  assert.equal(
    (
      await call("track", "POST", {
        reference: order.reference,
        email: "wrong@example.test",
      })
    ).status,
    404,
  );
  assert.equal(
    (await call("orders", "GET", null, customerCookie)).data.orders.length,
    1,
  );
  assert.equal(
    (
      await call(
        "admin/orders",
        "PUT",
        { id: row.id, status: "Delivered" },
        adminCookie,
      )
    ).status,
    400,
  );
  assert.equal(
    (
      await call(
        "admin/orders",
        "PUT",
        { id: row.id, status: "Cancelled" },
        adminCookie,
      )
    ).status,
    200,
  );
  assert.equal(
    (
      await call(
        "admin/orders",
        "PUT",
        { id: row.id, status: "Cancelled" },
        adminCookie,
      )
    ).status,
    200,
  );
  assert.equal(
    (await query("SELECT stock FROM products WHERE id=?", [pid]))[0].stock,
    1,
  );
  assert.equal(
    (
      await call(
        "admin/orders",
        "PUT",
        { id: row.id, status: "Confirmed" },
        adminCookie,
      )
    ).status,
    400,
  );
  assert.equal(
    (await call("admin/products", "DELETE", { id: pid }, adminCookie)).status,
    200,
  );
  assert.equal(
    (await call("catalog")).data.products.some((p) => p.id === pid),
    false,
  );
  console.log(
    "Integration passed: admin authorization, customer auth, CSRF, concurrent checkout, server pricing, stock reservation, tracking, cancellation, and product archival.",
  );
} finally {
  for (const id of orderIds) {
    await query("DELETE FROM order_items WHERE order_id=?", [id]);
    await query("DELETE FROM orders WHERE id=?", [id]);
  }
  if (pid)
    await query("DELETE FROM products WHERE id=? AND name=?", [pid, name]);
  await query("DELETE FROM categories WHERE name=?", [name]);
  await query("DELETE FROM brands WHERE name=?", [name]);
  if (uid)
    await query("DELETE FROM users WHERE id=? AND email=?", [uid, email]);
  if (adminCookie) await call("auth/logout", "POST", {}, adminCookie);
  await db().end();
}
