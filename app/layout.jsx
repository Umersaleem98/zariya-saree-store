import "./globals.css";
export const metadata = {
  title: "ZARIYA — Sarees for Your Every Story",
  description:
    "Discover exquisite sarees, thoughtfully curated for the women of Pakistan. Luxury silk, festive drapes and timeless classics.",
};
export default function Layout({ children }) {
  return (
    <html lang="en">
      <body>{children}</body>
    </html>
  );
}
