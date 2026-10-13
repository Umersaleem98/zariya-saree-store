import {
  randomBytes,
  scryptSync,
  timingSafeEqual,
  createHash,
} from "node:crypto";
export function hashPassword(password) {
  const salt = randomBytes(16).toString("hex");
  return `${salt}:${scryptSync(password, salt, 64).toString("hex")}`;
}
export function verifyPassword(password, stored) {
  try {
    const [salt, key] = stored.split(":");
    const expected = Buffer.from(key, "hex");
    const actual = scryptSync(password, salt, 64);
    return (
      expected.length === actual.length && timingSafeEqual(expected, actual)
    );
  } catch {
    return false;
  }
}
export function token() {
  return randomBytes(32).toString("hex");
}
export function digest(value) {
  return createHash("sha256").update(value).digest("hex");
}
export function sameOrigin(req) {
  try {
    const value = req.headers.get("origin");
    if (!value) return false;
    const origin = new URL(value);
    const target = new URL(req.url);
    if (process.env.NODE_ENV === "production" && process.env.APP_URL)
      return (
        origin.origin === value &&
        origin.origin === new URL(process.env.APP_URL).origin
      );
    const forwarded = req.headers
      .get("x-forwarded-proto")
      ?.split(",")[0]
      .trim();
    const protocol =
      forwarded === "https" || forwarded === "http"
        ? forwarded + ":"
        : target.protocol;
    return (
      ["http:", "https:"].includes(origin.protocol) &&
      origin.origin === value &&
      origin.host === (req.headers.get("host") || target.host) &&
      origin.protocol === protocol
    );
  } catch {
    return false;
  }
}
