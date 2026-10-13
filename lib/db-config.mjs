export function databaseConfig(env = process.env) {
  const sslEnabled = env.DB_SSL === "true";
  if (
    env.NODE_ENV === "production" &&
    env.DB_HOST &&
    !["localhost", "127.0.0.1"].includes(env.DB_HOST) &&
    !sslEnabled
  )
    throw Error("Enable DB_SSL=true for the hosted database.");
  return {
    host: env.DB_HOST || "127.0.0.1",
    port: Number(env.DB_PORT || 3306),
    user: env.DB_USER || "root",
    password: env.DB_PASSWORD || "",
    database: env.DB_NAME || "zariya_store",
    connectTimeout: 10000,
    ...(sslEnabled
      ? {
          ssl: {
            rejectUnauthorized: true,
            ...(env.DB_SSL_CA
              ? { ca: env.DB_SSL_CA.replaceAll("\\n", "\n") }
              : {}),
          },
        }
      : {}),
  };
}
