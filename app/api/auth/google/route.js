import { NextResponse } from "next/server";
import { cookies } from "next/headers";
import { OAuth2Client } from "google-auth-library";
import { token } from "../../../../lib/security.mjs";
import { googleReady } from "../../../../lib/settings.mjs";
export const runtime = "nodejs";
export async function GET(req) {
  const base = process.env.APP_URL || "http://127.0.0.1:3000";
  if (!googleReady())
    return NextResponse.redirect(new URL("/?auth_error=google_setup", base));
  const client = new OAuth2Client(
    process.env.GOOGLE_CLIENT_ID,
    process.env.GOOGLE_CLIENT_SECRET,
    new URL("/api/auth/google/callback", base).href,
  );
  const { codeVerifier, codeChallenge } =
    await client.generateCodeVerifierAsync();
  const state = token(),
    nonce = token(),
    checkout = new URL(req.url).searchParams.get("next") === "checkout";
  (await cookies()).set(
    "zariya_google_flow",
    JSON.stringify({ state, nonce, codeVerifier, checkout }),
    {
      httpOnly: true,
      sameSite: "lax",
      secure: process.env.NODE_ENV === "production",
      maxAge: 600,
      path: "/api/auth/google",
    },
  );
  return NextResponse.redirect(
    client.generateAuthUrl({
      scope: ["openid", "email", "profile"],
      state,
      nonce,
      code_challenge: codeChallenge,
      code_challenge_method: "S256",
      prompt: "select_account",
    }),
  );
}
