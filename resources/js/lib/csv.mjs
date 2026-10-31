export function parseCSV(text) {
  const rows = [];
  let row = [],
    cell = "",
    quoted = false;
  for (let i = 0; i < text.length; i++) {
    const ch = text[i];
    if (ch === '"') {
      if (quoted && text[i + 1] === '"') {
        cell += '"';
        i++;
      } else quoted = !quoted;
    } else if (ch === "," && !quoted) {
      row.push(cell);
      cell = "";
    } else if ((ch === "\n" || ch === "\r") && !quoted) {
      if (ch === "\r" && text[i + 1] === "\n") i++;
      row.push(cell);
      if (row.some(Boolean)) rows.push(row);
      row = [];
      cell = "";
    } else cell += ch;
  }
  if (quoted) throw Error("Unclosed quoted field in CSV.");
  row.push(cell);
  if (row.some(Boolean)) rows.push(row);
  if (!rows.length) throw Error("CSV is empty.");
  const headers = rows.shift().map((h) => h.trim().replace(/^\uFEFF/, ""));
  return rows.map((values, i) => {
    if (values.length !== headers.length)
      throw Error(`Wrong column count on row ${i + 2}.`);
    return Object.fromEntries(headers.map((h, k) => [h, values[k]]));
  });
}
export function toCSV(rows, keys) {
  return [keys, ...rows.map((r) => keys.map((k) => r[k] ?? ""))]
    .map((row) =>
      row.map((v) => '"' + String(v).replaceAll('"', '""') + '"').join(","),
    )
    .join("\r\n");
}
