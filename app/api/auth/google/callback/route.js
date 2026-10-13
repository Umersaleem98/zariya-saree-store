import { NextResponse } from "next/server";
import { cookies } from "next/headers";
import { OAuth2Client } from "google-auth-library";
import { query } from "../../../../../lib/db.mjs";
import { createSession } from "../../../../../lib/auth.mjs";
import { hashPassword, token } from "../../../../../lib/security.mjs";
export const runtime = "nodejs";
export async function GET(req) {
  const base = process.env.APP_URL || "http://127.0.0.1:3000";
  const jar = await cookies();
  const stored = jar.get("zariya_google_flow")?.value;
  jar.delete({ name: "zariya_google_flow", path: "/api/auth/google" });
  try {
    const flow = JSON.parse(stored || "null");
    const url = new URL(req.url);
    if (
      !flow ||
      url.searchParams.get("state") !== flow.state ||
      !url.searchParams.get("code") ||
      url.searchParams.has("error")
    )
      throw Error("Invalid Google sign-in flow");
    const client = new OAuth2Client(
      process.env.GOOGLE_CLIENT_ID,
      process.env.GOOGLE_CLIENT_SECRET,
      new URL("/api/auth/google/callback", base).href,
    );
    const { tokens } = await client.getToken({
      code: url.searchParams.get("code"),
      codeVerifier: flow.codeVerifier,
    });
    const ticket = await client.verifyIdToken({
      idToken: tokens.id_token,
      audience: process.env.GOOGLE_CLIENT_ID,
    });
    const p = ticket.getPayload();
    if (!p?.sub || !p.email_verified || !p.email || p.nonce !== flow.nonce)
      throw Error("Google verification failed");
    const email = p.email.toLowerCase();
    let [account] = await query(
      "SELECT id,role,google_subject FROM users WHERE google_subject=?",
      [p.sub],
    );
    if (!account)
      [account] = await query(
        "SELECT id,role,google_subject FROM users WHERE email=?",
        [email],
      );
    if (account?.role === "admin")
      throw Error("Administrators must use password login");
    if (account?.google_subject && account.google_subject !== p.sub)
      throw Error("Identity does not match");
    if (
      account &&
      !account.google_subject &&
      !email.endsWith("@gmail.com") &&
      !p.hd
    )
      throw Error("Use email/password for this existing account");
    if (!account) {
      const r = await query(
        "INSERT INTO users (name,email,password_hash,google_subject) VALUES (?,?,?,?)",
        [
          (p.name || email.split("@")[0]).slice(0, 100),
          email,
          hashPassword(token()),
          p.sub,
        ],
      );
      account = { id: r.insertId };
    } else if (!account.google_subject)
      await query("UPDATE users SET google_subject=? WHERE id=?", [
        p.sub,
        account.id,
      ]);
    await createSession(account.id);
    return NextResponse.redirect(
      new URL(flow.checkout ? "/?checkout=1" : "/?signed_in=1", base),
    );
  } catch {
    return NextResponse.redirect(new URL("/?auth_error=google_failed", base));
  }
}
