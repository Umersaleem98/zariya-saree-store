import { test } from "node:test";
import assert from "node:assert/strict";
import {
  hashPassword,
  verifyPassword,
  digest,
  token,
  sameOrigin,
} from "../lib/security.mjs";
import {
  normalizeItems,
  shippingFor,
  validateProduct,
} from "../lib/validation.mjs";
import { parseCSV, toCSV } from "../lib/csv.mjs";
import { validatePaymentSettings } from "../lib/settings.mjs";
import { databaseConfig } from "../lib/db-config.mjs";
test("Cloud MySQL uses verified TLS and preserves local XAMPP defaults", () => {
  assert.equal(databaseConfig({}).host, "127.0.0.1");
  assert.equal(
    databaseConfig({ DB_SSL: "true", DB_SSL_CA: "line1\\nline2" }).ssl.ca,
    "line1\nline2",
  );
  assert.equal(databaseConfig({ DB_SSL: "true" }).ssl.rejectUnauthorized, true);
  assert.throws(
    () =>
      databaseConfig({
        NODE_ENV: "production",
        DB_HOST: "cloud.example",
        DB_SSL: "false",
      }),
    /DB_SSL/,
  );
});
test("Payment settings keep checkout available and block unconfigured cards", () => {
  const defaults = {
    cod_enabled: true,
    bank_enabled: false,
    card_enabled: false,
  };
  assert.deepEqual(validatePaymentSettings(defaults), defaults);
  assert.throws(
    () => validatePaymentSettings({ ...defaults, card_enabled: true }),
    /gateway/,
  );
  assert.throws(
    () => validatePaymentSettings({ ...defaults, cod_enabled: false }),
    /at least one/,
  );
  assert.throws(() =>
    validatePaymentSettings({ ...defaults, bank_enabled: "yes" }),
  );
});
test("Passwords are salted and never accepted with incorrect input", () => {
  const h = hashPassword("a secure password");
  assert.notEqual(h, hashPassword("a secure password"));
  assert.equal(verifyPassword("a secure password", h), true);
  assert.equal(verifyPassword("incorrect", h), false);
  assert.equal(verifyPassword("x", "bad"), false);
  assert.equal(digest(token()).length, 64);
});
test("Duplicate cart entries combine before checking stock", () => {
  assert.deepEqual(
    normalizeItems([
      { id: 2, quantity: 2 },
      { id: 1, quantity: 1 },
      { id: 2, quantity: 3 },
    ]),
    [
      { id: 1, quantity: 1 },
      { id: 2, quantity: 5 },
    ],
  );
  for (const items of [
    [],
    [{ id: 1, quantity: -1 }],
    [{ id: 1, quantity: 1.5 }],
    [
      { id: 1, quantity: 20 },
      { id: 1, quantity: 1 },
    ],
  ])
    assert.throws(() => normalizeItems(items));
});
test("Delivery threshold is consistent", () => {
  assert.equal(shippingFor(14999), 250);
  assert.equal(shippingFor(15000), 0);
});
test("CSV preserves quotes, commas, newlines and Unicode", () => {
  const rows = [
    { name: 'Gulabi, "Rose"', description: "First line\nSecond line — گلابی" },
  ];
  assert.deepEqual(parseCSV(toCSV(rows, ["name", "description"])), rows);
  assert.throws(() => parseCSV("name,price\nOnly one"));
  assert.throws(() => parseCSV('name\n"unclosed'));
});
test("Product import rejects invalid prices and stock", () => {
  const valid = {
    name: "Noor",
    category: "Silk",
    brand: "Zariya",
    price: 100,
    stock: 3,
    image: "/images/gold.jpg",
  };
  assert.equal(validateProduct(valid).stock, 3);
  assert.throws(() => validateProduct({ ...valid, price: -2 }));
  assert.throws(() => validateProduct({ ...valid, stock: 1.5 }));
  assert.throws(() =>
    validateProduct({ ...valid, image: "javascript:alert(1)" }),
  );
});
test("Mutations enforce same origin, including Next.js internal hostname", () => {
  assert.equal(
    sameOrigin(
      new Request("http://localhost:3000/api/orders", {
        headers: { origin: "http://localhost:3000" },
      }),
    ),
    true,
  );
  assert.equal(
    sameOrigin(
      new Request("http://localhost:3000/api/orders", {
        headers: { origin: "http://127.0.0.1:3000", host: "127.0.0.1:3000" },
      }),
    ),
    true,
  );
  assert.equal(
    sameOrigin(
      new Request("http://localhost:3000/api/orders", {
        headers: { origin: "http://evil.example" },
      }),
    ),
    false,
  );
  assert.equal(
    sameOrigin(new Request("http://localhost:3000/api/orders")),
    false,
  );
});
