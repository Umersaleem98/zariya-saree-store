import { cookies } from "next/headers";
import { query } from "./db.mjs";
import { digest, token } from "./security.mjs";
export async function createSession(id) {
  const value = token();
  await query(
    "INSERT INTO sessions (token_hash,user_id,expires_at) VALUES (?,?,DATE_ADD(NOW(),INTERVAL 7 DAY))",
    [digest(value), id],
  );
  (await cookies()).set("zariya_session", value, {
    httpOnly: true,
    sameSite: "lax",
    secure: process.env.NODE_ENV === "production",
    maxAge: 604800,
    path: "/",
  });
}
export async function user() {
  const value = (await cookies()).get("zariya_session")?.value;
  if (!value) return null;
  const rows = await query(
    "SELECT u.id,u.name,u.email,u.role FROM sessions s JOIN users u ON u.id=s.user_id WHERE s.token_hash=? AND s.expires_at>NOW()",
    [digest(value)],
  );
  return rows[0] || null;
}
