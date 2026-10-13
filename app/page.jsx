import Store from "../components/Store";
import { products } from "../lib/catalog.mjs";
export default function Page() {
  return <Store initialProducts={products} />;
}
