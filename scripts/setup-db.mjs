import { readFileSync } from "node:fs";
import mysql from "mysql2/promise";
import { hashPassword } from "../lib/security.mjs";
import { products, categories, brands } from "../lib/catalog.mjs";
import { databaseConfig } from "../lib/db-config.mjs";
try {
  for (const line of readFileSync(".env.local", "utf8").split(/\r?\n/)) {
    const m = line.match(/^([A-Z_]+)=(.*)$/);
    if (m && !process.env[m[1]]) process.env[m[1]] = m[2];
  }
} catch {}
if (
  !process.env.ADMIN_PASSWORD ||
  process.env.ADMIN_PASSWORD.length < 12 ||
  process.env.ADMIN_PASSWORD === "replace-with-a-strong-password"
)
  throw Error(
    "Set a unique ADMIN_PASSWORD of at least 12 characters in .env.local.",
  );
const name = process.env.DB_NAME || "zariya_store";
if (!/^[a-zA-Z0-9_]+$/.test(name)) throw Error("Invalid database name");
const c = await mysql.createConnection({
  ...databaseConfig(),
  database: undefined,
  multipleStatements: true,
});
await c.query(
  `CREATE DATABASE IF NOT EXISTS \`${name}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; USE \`${name}\`;`,
);
await c.query(readFileSync("database/schema.sql", "utf8"));
const [googleColumn] = await c.query(
  "SHOW COLUMNS FROM users LIKE 'google_subject'",
);
if (!googleColumn.length)
  await c.query(
    "ALTER TABLE users ADD google_subject VARCHAR(255) NULL UNIQUE",
  );
for (const v of categories)
  await c.execute("INSERT IGNORE INTO categories (name) VALUES (?)", [v]);
for (const v of brands)
  await c.execute("INSERT IGNORE INTO brands (name) VALUES (?)", [v]);
for (const p of products)
  await c.execute(
    "INSERT IGNORE INTO products (id,name,category,brand,price,compare_price,stock,color,fabric,badge,image,description) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
    Object.values(p),
  );
await c.execute(
  "INSERT IGNORE INTO users (name,email,password_hash,role) VALUES (?,?,?,?)",
  [
    "Store Administrator",
    process.env.ADMIN_EMAIL || "admin@example.com",
    hashPassword(process.env.ADMIN_PASSWORD),
    "admin",
  ],
);
await c.end();
console.log("Database ready. Seed products added; existing data preserved.");
