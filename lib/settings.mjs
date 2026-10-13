import { query } from "./db.mjs";
import { defaultPayments } from "./settings-client.mjs";
export { defaultPayments };
export async function paymentSettings() {
  const [row] = await query(
    "SELECT cod_enabled,bank_enabled,card_enabled FROM store_settings WHERE id=1",
  );
  return {
    cod_enabled: !!(row?.cod_enabled ?? true),
    bank_enabled: !!row?.bank_enabled,
    card_enabled: false,
    card_ready: false,
  };
}
export function validatePaymentSettings(value) {
  if (!value || typeof value !== "object" || Array.isArray(value))
    throw Error("Enter valid payment settings.");
  for (const key of Object.keys(defaultPayments))
    if (typeof value[key] !== "boolean")
      throw Error("Payment settings must be on or off.");
  if (value.card_enabled)
    throw Error(
      "Card payments cannot be enabled until a payment gateway is integrated.",
    );
  if (!value.cod_enabled && !value.bank_enabled)
    throw Error("Keep at least one payment option enabled.");
  return value;
}
export function googleReady() {
  return !!(process.env.GOOGLE_CLIENT_ID && process.env.GOOGLE_CLIENT_SECRET);
}
