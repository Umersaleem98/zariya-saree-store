import mysql from "mysql2/promise";
import { databaseConfig } from "./db-config.mjs";
let pool;
export function db() {
  if (!pool)
    pool = mysql.createPool({
      ...databaseConfig(),
      connectionLimit: 3,
      decimalNumbers: true,
    });
  return pool;
}
export async function query(sql, values = []) {
  const [rows] = await db().execute(sql, values);
  return rows;
}
