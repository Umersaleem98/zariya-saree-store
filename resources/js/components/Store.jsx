"use client";
import { useEffect, useState } from "react";
import {
  Search,
  ShoppingBag,
  Heart,
  ArrowUpRight,
  ArrowRight,
  ChevronDown,
  ChevronRight,
  X,
  Plus,
  Minus,
  Menu,
  Truck,
  ShieldCheck,
  PackageCheck,
  Check,
  Instagram,
  MapPin,
  SlidersHorizontal,
} from "lucide-react";
import { money } from "../lib/catalog.mjs";
import { defaultPayments } from "../lib/settings-client.mjs";
const IconButton = ({ label, children, ...props }) => (
  <button className="icon-button" aria-label={label} {...props}>
    {children}
  </button>
);
async function api(path, body) {
  const r = await fetch(
    "/api/" + path,
    body
      ? {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(body),
        }
      : {},
  );
  const d = await r.json();
  if (!r.ok) {
    const error = Error(d.error || Object.values(d.errors || {}).flat()[0] || d.message || "Please try again.");
    error.status = r.status;
    throw error;
  }
  return d;
}
export default function Store({ initialProducts }) {
  const [products, setProducts] = useState(initialProducts),
    [demo, setDemo] = useState(false),
    [bag, setBag] = useState([]),
    [wish, setWish] = useState([]),
    [ready, setReady] = useState(false),
    [panel, setPanel] = useState(null),
    [selected, setSelected] = useState(null),
    [category, setCategory] = useState("All Sarees"),
    [search, setSearch] = useState(""),
    [sort, setSort] = useState("featured"),
    [error, setError] = useState(""),
    [busy, setBusy] = useState(false),
    [toast, setToast] = useState(""),
    [confirmation, setConfirmation] = useState(null),
    [tracked, setTracked] = useState(null),
    [mobile, setMobile] = useState(false),
    [brand, setBrand] = useState("All brands");
  const [galleryIndex, setGalleryIndex] = useState(0);
  const [chosenVariant, setChosenVariant] = useState(null);
  const [trackingReference, setTrackingReference] = useState("");
  const [checkoutToken, setCheckoutToken] = useState(() => crypto.randomUUID());
  useEffect(() => { setGalleryIndex(0); setChosenVariant(selected?.variants?.find(v => v.enabled && v.stock > 0)?.id ?? null); }, [selected]);
  const [payments, setPayments] = useState(defaultPayments);
  useEffect(() => {
    try {
      setBag(JSON.parse(localStorage.getItem("zariya-bag") || "[]"));
      setWish(JSON.parse(localStorage.getItem("zariya-wish") || "[]"));
    } catch {}
    setReady(true);
    api("catalog").then((d) => {
      setProducts(d.products);
      setDemo(d.demo);
      if (d.payments) setPayments(d.payments);
    }).catch(() => { setDemo(true); setToast("The collection could not load. Please refresh before ordering."); });
    const tracking = new URLSearchParams(location.search).get("tracking");
    if (tracking) { setPanel("track"); setTrackingReference(tracking); }
  }, []);
  useEffect(() => {
    if (
      window.matchMedia("(prefers-reduced-motion: reduce)").matches ||
      !("IntersectionObserver" in window)
    )
      return;
    const targets = document.querySelectorAll(
      ".category-card,.product-card,.editorial-copy,.service-section>div",
    );
    const observer = new IntersectionObserver(
      (entries) => {
        for (const entry of entries)
          if (entry.isIntersecting) {
            entry.target.classList.add("motion-visible");
            observer.unobserve(entry.target);
          }
      },
      { threshold: 0.08 },
    );
    targets.forEach((target, i) => {
      target.style.setProperty("--motion-delay", `${(i % 4) * 80}ms`);
      target.classList.add("motion-ready");
      observer.observe(target);
    });
    return () => {
      observer.disconnect();
      targets.forEach((t) => t.classList.remove("motion-ready"));
    };
  }, [products, category, search, sort, brand]);
  useEffect(() => {
    if (ready) {
      localStorage.setItem("zariya-bag", JSON.stringify(bag));
      localStorage.setItem("zariya-wish", JSON.stringify(wish));
    }
  }, [bag, wish, ready]);
  useEffect(() => {
    if (!toast) return;
    const t = setTimeout(() => setToast(""), 3200);
    return () => clearTimeout(t);
  }, [toast]);
  useEffect(() => {
    setError("");
    document.body.style.overflow = panel || selected ? "hidden" : "";
    if (!panel && !selected)
      return () => {
        document.body.style.overflow = "";
      };
    const previous = document.activeElement;
    const dialog = document.querySelector('[role="dialog"]');
    const first = dialog?.querySelector("button,input,select,textarea,a[href]");
    first?.focus({ preventScroll: true });
    const trap = (e) => {
      if (e.key !== "Tab" || !dialog) return;
      const nodes = [
        ...dialog.querySelectorAll(
          "button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),a[href]",
        ),
      ].filter((n) => n.getClientRects().length);
      if (!nodes.length) return;
      if (e.shiftKey && document.activeElement === nodes[0]) {
        e.preventDefault();
        nodes.at(-1).focus();
      } else if (!e.shiftKey && document.activeElement === nodes.at(-1)) {
        e.preventDefault();
        nodes[0].focus();
      }
    };
    document.addEventListener("keydown", trap);
    return () => {
      document.body.style.overflow = "";
      document.removeEventListener("keydown", trap);
      previous?.focus?.({ preventScroll: true });
    };
  }, [panel, selected]);
  useEffect(() => {
    const f = (e) => {
      if (e.key === "Escape") {
        setPanel(null);
        setSelected(null);
      }
    };
    window.addEventListener("keydown", f);
    return () => window.removeEventListener("keydown", f);
  }, []);
  const items = bag.map((i) => {
    const base = products.find((p) => p.id === i.id);
    const variant = base?.variants?.find(v => v.id === i.variant_id) || (!i.variant_id ? base?.variants?.find(v => v.type === "Unstitched") : null);
    return { ...i, variant_id: variant?.id, variant, product: base && variant ? { ...base, price: variant.price, stock: variant.enabled ? variant.stock : 0 } : null };
  }).filter(i => i.product),
    subtotal = items.reduce((a, i) => a + i.product.price * i.quantity, 0),
    shipping = subtotal >= 15000 ? 0 : 250,
    count = items.reduce((a, i) => a + i.quantity, 0);
  const selectedOption = selected?.variants?.find(v => v.id === chosenVariant);
  const selectedImages = selected?.images?.length ? selected.images : selected ? [selected.image] : [];
  const visible = products
    .filter(
      (p) =>
        (category === "All Sarees" ||
          (category === "Wishlist" && wish.includes(p.id)) ||
          p.category === category) &&
        (brand === "All brands" || p.brand === brand) &&
        `${p.name} ${p.color} ${p.fabric} ${p.brand}`
          .toLowerCase()
          .includes(search.toLowerCase()),
    )
    .sort((a, b) =>
      sort === "low"
        ? a.price - b.price
        : sort === "high"
          ? b.price - a.price
          : sort === "new"
            ? b.id - a.id
            : 0,
    );
  const categories = [...new Set(products.map((p) => p.category))];
  function browse(c = "All Sarees") {
    setCategory(c);
    setMobile(false);
    setPanel(null);
    setTimeout(
      () =>
        document
          .getElementById("collection")
          ?.scrollIntoView({ behavior: "smooth" }),
      50,
    );
  }
  function add(p, variantId) {
    if (!p.variants) { setToast("The collection is loading. Please try again in a moment."); return; }
    const options = p.variants?.filter(v => v.enabled) || [];
    const variant = options.find(v => v.id === variantId) || (!variantId && options.length === 1 ? options[0] : null);
    if (!variant) { setSelected(p); return; }
    const existing = bag.find(i => i.id === p.id && (i.variant_id === variant.id || (!i.variant_id && variant.type === "Unstitched")))?.quantity || 0;
    if (existing >= variant.stock || existing >= 20) { setToast("No more of this option is available."); return; }
    setBag(b => b.some(i => i.id === p.id && (i.variant_id === variant.id || (!i.variant_id && variant.type === "Unstitched")))
      ? b.map(i => i.id === p.id && (i.variant_id === variant.id || (!i.variant_id && variant.type === "Unstitched")) ? { ...i, variant_id: variant.id, quantity: i.quantity + 1 } : i)
      : [...b, { id: p.id, variant_id: variant.id, quantity: 1 }]);
    setSelected(null); setPanel("bag");
  }
  function toggleWish(id) {
    setWish((v) => (v.includes(id) ? v.filter((x) => x !== id) : [...v, id]));
  }
  async function submit(e, path) {
    e.preventDefault();
    setError("");
    setBusy(true);
    try {
      const b = Object.fromEntries(new FormData(e.currentTarget));
      const d = await api(path, path === "orders" ? { ...b, checkout_token: checkoutToken, items: items.map(i => ({id:i.id,variant_id:i.variant_id,quantity:i.quantity})) } : b);
      if (path === "orders") {
        setConfirmation(d);
        setCheckoutToken(crypto.randomUUID());
        setBag([]);
        api("catalog").then((d) => setProducts(d.products));
      }
      if (path === "track") setTracked(d.order);
    } catch (e) {
      setError(e.message);
    } finally {
      setBusy(false);
    }
  }
  async function beginCheckout() {
    setBusy(true); setConfirmation(null); setCheckoutToken(crypto.randomUUID());
    try { const settings = await api("settings"); setPayments(settings.payments); setPanel("checkout"); }
    catch (e) { setError(e.message); } finally { setBusy(false); }
  }
  function account() { setTracked(null); setPanel("track"); }
  function productCard(p) { return (<article className="product-card" key={p.id}>
                  <div className="product-image">
                    <button
                      className="product-open"
                      onClick={() => setSelected(p)}
                      aria-label={`View ${p.name}`}
                    >
                      <img
                        src={p.image}
                        alt={`${p.name} in ${p.color}`}
                        loading="lazy"
                      />
                    </button>
                    {(p.discount > 0 || p.badge) && (
                      <span
                        className={
                          "badge " + (p.badge === "NEW ARRIVAL" ? "rose" : "")
                        }
                      >
                        {p.discount > 0 ? `${p.discount}% OFF` : p.badge}
                      </span>
                    )}
                    <IconButton
                      label={`Save ${p.name}`}
                      className={
                        "wish-button " + (wish.includes(p.id) ? "saved" : "")
                      }
                      onClick={() => toggleWish(p.id)}
                    >
                      <Heart
                        size={18}
                        fill={wish.includes(p.id) ? "currentColor" : "none"}
                      />
                    </IconButton>
                    <button
                      className="quick-add"
                      disabled={!p.stock}
                      onClick={() => add(p)}
                    >
                      {p.stock ? "ADD TO BAG" : "SOLD OUT"} <Plus size={16} />
                    </button>
                  </div>
                  <div className="product-meta">
                    <span>{p.fabric} · {p.stock ? `${p.stock} left` : "Out of stock"}</span>
                    <span
                      className={"swatch " + (p.color || "").toLowerCase()}
                      title={p.color}
                    />
                  </div>
                  <button
                    className="product-name"
                    onClick={() => setSelected(p)}
                  >
                    {p.name}
                  </button>
                  <div className="price">
                    {money(p.price)}{" "}
                    {p.compare_price > p.price && (
                      <del>{money(p.compare_price)}</del>
                    )}
                  </div>
                </article>); }
  return (
    <>
      <div
        className="store-surface"
        inert={panel || selected ? true : undefined}
      >
        <div className="announcement" aria-label="Store announcement"><span className="announcement-message">COMPLIMENTARY DELIVERY ON ORDERS OF RS. 15,000 OR MORE <ArrowRight size={12}/></span></div><header>
          <div className="header-top">
            <IconButton label="Open menu" onClick={() => setMobile(!mobile)}>
              <Menu />
            </IconButton>
            <a className="brand-lockup header-brand" href="/" aria-label="Rang Mahal — home">
              <img className="brand-emblem" src="/images/rang-mahal-emblem.webp" alt="" width="320" height="320" fetchPriority="high" />
              <span className="brand-wordmark">RANG MAHAL<span className="brand-tagline">COLOURS OF TRADITION</span></span>
            </a>
            <div className="header-actions">
              <IconButton
                label="Search sarees"
                onClick={() => {
                  setPanel("search");
                }}
              >
                <Search size={20} />
              </IconButton>
              <IconButton label="Track your order" onClick={account}>
                <PackageCheck size={20} />
              </IconButton>
              <IconButton label="Wishlist" onClick={() => browse("Wishlist")}>
                <Heart size={20} />
                {wish.length > 0 && <i>{wish.length}</i>}
              </IconButton>
              <IconButton
                label="Shopping bag"
                onClick={() => {
                  setConfirmation(null);
                  setPanel("bag");
                }}
              >
                <ShoppingBag size={20} />
                <i>{count}</i>
              </IconButton>
            </div>
          </div>
          </header><nav aria-label="Saree collections" className={mobile ? "category-nav open" : "category-nav"}>
            <button onClick={() => browse()}>All Sarees</button>
            <button onClick={() => browse("Silk Sarees")}>Silk Sarees</button>
            <button onClick={() => browse("Party Wear")}>Party Wear</button>
            <button onClick={() => browse("Wedding Edit")}>
              The Wedding Edit <span className="nav-new">NEW</span>
            </button>
            <button onClick={() => browse("Festive Wear")}>Festive Wear</button>
            <button onClick={() => browse("Everyday Elegance")}>
              Everyday Elegance
            </button>
            <button
              onClick={() =>
                document
                  .getElementById("story")
                  .scrollIntoView({ behavior: "smooth" })
              }
            >
              Our Story
            </button>
          </nav>
        <main>
          <section className="hero">
            <img
              src="/images/campaign.jpg"
              alt="Golden saree styled for a Pakistani celebration — Rang Mahal"
              fetchPriority="high"
            />
            <div className="hero-shade" />
            <div className="hero-copy">
              <div className="eyebrow">
                <span /> RANG MAHAL — COLOURS OF TRADITION
              </div>
              <h1>
                Rooted in
                <br />
                tradition.
                <br />
                Worn with <em>love.</em>
              </h1>
              <p>
                Sarees for the celebrations that bring us together.
                <br />
                Discover colours and graceful drapes for your own story.
              </p>
              <button className="gold-button" onClick={() => browse()}>
                EXPLORE THE COLLECTION <ArrowUpRight size={18} />
              </button>
            </div>
          </section>
          <section className="collection-menu wrap" aria-label="Browse saree collections">
            <div className="collection-segments">
              {["All Sarees", ...categories].map((c) => (
                <button
                  type="button"
                  className={category === c ? "active" : ""}
                  aria-pressed={category === c}
                  onClick={() => browse(c)}
                  key={c}
                >
                  {c}
                </button>
              ))}
            </div>
          </section>
          {category === "All Sarees" && !search && products.some(p=>p.featured) && <section className="featured-collection wrap"><div className="section-intro"><div><div className="eyebrow">CHOSEN FOR YOU</div><h2>Featured <em>sarees.</em></h2></div></div><div className="product-grid">{products.filter(p=>p.featured).map(productCard)}</div></section>}
          <section className="collection wrap" id="collection">
            <div className="section-intro">
              <div>
                <div className="eyebrow">
                  SAREES ONLINE IN PAKISTAN
                </div>
                <h2>
                  {category === "Wishlist" ? (
                    "Your favourites."
                  ) : category !== "All Sarees" ? (
                    <>{category}. <em>Make it yours.</em></>
                  ) : (
                    <>
                      Find a colour. <em>Make it yours.</em>
                    </>
                  )}
                </h2>
              </div>
              <span className="small-note">
                From Eid gatherings to wedding celebrations.
              </span>
            </div>
            <div className="collection-tools">
              <div className="sort">
                <SlidersHorizontal size={15} />
                <select
                  aria-label="Sort products"
                  value={sort}
                  onChange={(e) => setSort(e.target.value)}
                >
                  <option value="featured">Featured</option>
                  <option value="new">Newest first</option>
                  <option value="low">Price: low to high</option>
                  <option value="high">Price: high to low</option>
                </select>
              </div>
            </div>
            {(search || brand !== "All brands") && (
              <div className="filter-summary">
                Showing {visible.length} results {search && `for “${search}”`}{" "}
                <button
                  onClick={() => {
                    setSearch("");
                    setBrand("All brands");
                  }}
                >
                  Clear filters
                </button>
              </div>
            )}
            <div className="product-grid">
              {visible.map(productCard)}
            </div>
            {!visible.length && (
              <div className="empty">
                No sarees found. Try another collection or search.
              </div>
            )}
            <div className="center">
              <button
                className="outline-button"
                onClick={() => {
                  setCategory("All Sarees");
                  setSearch("");
                  setBrand("All brands");
                  setToast("You’re viewing the complete collection.");
                }}
              >
                DISCOVER ALL SAREES <ArrowUpRight size={17} />
              </button>
            </div>
          </section>
          <section className="editorial" id="story">
            <div className="editorial-image">
              <img
                loading="lazy"
                src="/images/craftsmanship.jpg"
                alt="Ruby red saree with a golden Banarasi-style border — Rang Mahal"
              />
              <span>Made to be remembered.</span>
            </div>
            <div className="editorial-copy">
              <div className="eyebrow">OUR STORY — A CONNECTION TO CULTURE</div>
              <h2>
                A love for culture.
                <br />A style of <em>your own.</em>
              </h2>
              <p>
                In Pakistan, colour is part of how we celebrate. It fills our mehndi evenings, brightens our Eid gatherings and lives on in the clothes we remember with love.
              </p>
              <p>
                Rang Mahal begins with sarees and a love for Pakistani culture, traditions and the beauty of craft. Our vision reaches beyond clothing: to celebrate the colours, stories and creative expression that connect us. Choose a drape that feels like you, and make it part of your own story.
              </p>
              <div className="story-signature">With love, Rang Mahal.</div>
            </div>
          </section>
        </main>
        <footer>
          <div className="footer-main wrap">
            <div className="footer-brand">
              <a className="brand-lockup footer-brand-lockup" href="/" aria-label="Rang Mahal — home">
                <img className="brand-emblem" src="/images/rang-mahal-emblem.webp" alt="" width="320" height="320" loading="lazy" decoding="async" />
                <span className="brand-wordmark">RANG MAHAL<span className="brand-tagline">COLOURS OF TRADITION</span></span>
              </a>
              <p>
                Six yards of grace.
                <br />A lifetime of stories.
              </p>
              <span className="location">
                <MapPin size={15} /> Inspired by Pakistan. Chosen by you.
              </span>
            </div>
            <div>
              <h4>EXPLORE</h4>
              {["All Sarees", "Silk Sarees", "Wedding Edit", "Party Wear"].map(
                (c) => (
                  <button key={c} onClick={() => browse(c)}>
                    {c}
                  </button>
                ),
              )}
            </div>
            <div>
              <h4>HERE TO HELP</h4>
              <button
                onClick={() => {
                  setTracked(null);
                  setPanel("track");
                }}
              >
                Track your order
              </button>
              <button onClick={() => setPanel("delivery")}>
                Shipping & returns
              </button>
              <button onClick={() => setPanel("care")}>Saree care guide</button>
            </div>
            <div>
              <h4>THE RANG MAHAL SPIRIT</h4>
              <p>
                A love for colour. A connection to tradition.
                <br />A style that feels like you.
              </p>
              <span className="footer-pay">
                {payments.cod_enabled ? "CASH ON DELIVERY" : ""}
                {payments.cod_enabled && payments.bank_enabled ? " · " : ""}
                {payments.bank_enabled ? "BANK TRANSFER" : ""}
              </span>
            </div>
          </div>
          <div className="footer-bottom wrap">
            <span>
              © {new Date().getFullYear()} Rang Mahal. All rights reserved.
            </span>
            <span>PAKISTAN · PKR</span>
            <button onClick={() => setPanel("privacy")}>Privacy policy</button>
          </div>
        </footer>
      </div>
      {toast && (
        <div className="toast" role="status">
          <Check size={17} />
          {toast}
        </div>
      )}
      {selected && (
        <div className="overlay" onClick={() => setSelected(null)}>
          <section
            className="product-modal"
            role="dialog"
            aria-modal="true"
            aria-label={selected.name}
            onClick={(e) => e.stopPropagation()}
          >
            <IconButton
              label="Close product"
              className="modal-close"
              onClick={() => setSelected(null)}
            >
              <X />
            </IconButton>
            <div className="product-gallery"><img src={selectedImages[galleryIndex] || selected.image} alt={selected.name} />
              <div className="gallery-thumbnails">{selectedImages.map((src,i)=><button key={src} aria-label={"Product image "+(i+1)} aria-pressed={i===galleryIndex} onClick={()=>setGalleryIndex(i)}><img src={src} alt="" /></button>)}</div></div>
            <div className="product-detail">
              <div className="eyebrow">{selected.brand}</div>
              <h2>{selected.name}</h2>
              <div className="price">{money(selectedOption?.price ?? selected.price)} {selectedOption?.discount > 0 && <><del>{money(selectedOption.regular_price)}</del><span className="sale-label">{selectedOption.discount}% OFF</span></>}</div>
              <fieldset className="variant-picker"><legend>Choose your option</legend>{["Unstitched","Stitched"].map(type=>{const v=selected.variants?.find(v=>v.type===type);return <label key={type} className={!v?.enabled || !v.stock ? "unavailable" : ""}><input type="radio" name="variant" value={v?.id || ""} checked={v?.id === chosenVariant} disabled={!v?.enabled || !v.stock} onChange={()=>setChosenVariant(v.id)} />{type}<span>{v?.enabled ? money(v.price) : "Unavailable"} · {v?.enabled ? v.stock+" left" : "Not offered"}</span></label>;})}</fieldset>
              <p>{selected.description}</p>
              <div className="detail-list">
                <span>
                  Fabric <b>{selected.fabric}</b>
                </span>
                <span>
                  Colour <b>{selected.color}</b>
                </span>
                <span>
                  Length <b>Approx. 6.3 m, including blouse</b>
                </span>
                <span>
                  Availability <b>{selectedOption?.stock ? `${selectedOption.stock} pieces remaining` : "Sold out"}</b>
                </span>
              </div>
              <button
                className="gold-button"
                disabled={!selectedOption?.stock}
                onClick={() => add(selected, chosenVariant)}
              >
                ADD TO BAG <ShoppingBag size={17} />
              </button>
              <button
                className="text-link"
                onClick={() => toggleWish(selected.id)}
              >
                <Heart size={16} />{" "}
                {wish.includes(selected.id)
                  ? "Saved to your wishlist"
                  : "Save to wishlist"}
              </button>
              <small>
                Choose your preferred option and check the available stock before adding to your bag.
              </small>
            </div>
          </section>
        </div>
      )}
      {panel && (
        <div className="overlay" onClick={() => setPanel(null)}>
          <section
            className={
              "drawer " +
              (["checkout", "account"].includes(panel) ? "wide" : "")
            }
            role="dialog"
            aria-modal="true"
            aria-label={panel}
            onClick={(e) => e.stopPropagation()}
          >
            <div className="drawer-head">
              <h2>
                {
                  {
                    bag: "Your shopping bag",
                    checkout: "A beautiful choice.",
                    search: "Find your perfect drape",
                    track: "Track your order",
                    delivery: "Shipping & returns",
                    care: "Caring for your saree",
                    privacy: "Your privacy",
                  }[panel]
                }
              </h2>
              <IconButton label="Close" onClick={() => setPanel(null)}>
                <X />
              </IconButton>
            </div>
            {error && (
              <div className="error" role="alert">
                {error}
              </div>
            )}
            {panel === "search" && (
              <>
                <input
                  autoFocus
                  placeholder="Search silk, ruby, Noor…"
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                />
                <label>
                  Brand
                  <select
                    value={brand}
                    onChange={(e) => setBrand(e.target.value)}
                  >
                    {[
                      "All brands",
                      ...new Set(products.map((p) => p.brand)),
                    ].map((b) => (
                      <option key={b}>{b}</option>
                    ))}
                  </select>
                </label>
                <button className="gold-button" onClick={() => browse()}>
                  SHOW RESULTS <Search size={17} />
                </button>
              </>
            )}
            {panel === "bag" && (
              <>
                {items.length ? (
                  <>
                    <div className="bag-progress">
                      <Truck size={17} />
                      {subtotal >= 15000
                        ? "Your order qualifies for complimentary delivery."
                        : `You’re ${money(15000 - subtotal)} away from free delivery.`}
                    </div>
                    {items.map((i) => (
                      <div className="bag-item" key={i.variant_id}>
                        <img src={i.product.image} alt={i.product.name} />
                        <div>
                          <h3>{i.product.name}</h3>
                          <p>
                            {i.variant?.type} · {i.product.color} · {i.product.fabric}
                          </p>
                          <b>{money(i.product.price)}</b>
                          <div className="quantity">
                            <IconButton
                              label="Decrease quantity"
                              onClick={() =>
                                setBag((b) =>
                                  b
                                    .map((x) =>
                                      (x.variant_id === i.variant_id || (!x.variant_id && x.id === i.id))
                                        ? { ...x, quantity: x.quantity - 1 }
                                        : x,
                                    )
                                    .filter((x) => x.quantity > 0),
                                )
                              }
                            >
                              <Minus size={13} />
                            </IconButton>
                            <span>{i.quantity}</span>
                            <IconButton
                              label="Increase quantity"
                              onClick={() => add(i.product, i.variant_id)}
                            >
                              <Plus size={13} />
                            </IconButton>
                          </div>
                        </div>
                        <IconButton
                          label="Remove item"
                          onClick={() =>
                            setBag((b) => b.filter((x) => !(x.variant_id === i.variant_id || (!x.variant_id && x.id === i.id))))
                          }
                        >
                          <X size={16} />
                        </IconButton>
                      </div>
                    ))}
                    <div className="totals">
                      <span>
                        Subtotal <b>{money(subtotal)}</b>
                      </span>
                      <span>
                        Shipping{" "}
                        <b>
                          {shipping
                            ? money(shipping)
                            : "Complimentary"}
                        </b>
                      </span>
                      <span className="grand-total">Total <b>{money(subtotal + shipping)}</b></span>
                    </div>
                    <button
                      className="gold-button"
                      onClick={beginCheckout}
                      disabled={busy}
                    >
                      {busy ? "PLEASE WAIT…" : "PROCEED TO CHECKOUT"}{" "}
                      <ArrowRight size={17} />
                    </button>
                    <p className="fine-print">
                      {payments.cod_enabled
                        ? "Cash on delivery available"
                        : "Secure checkout"}{" "}
                      · Pakistan-wide delivery
                    </p>
                  </>
                ) : (
                  <div className="empty">
                    <ShoppingBag size={38} />
                    <h3>Your next story is waiting.</h3>
                    <p>Add a beautiful drape to your bag.</p>
                    <button className="gold-button" onClick={() => browse()}>
                      EXPLORE SAREES
                    </button>
                  </div>
                )}
              </>
            )}
            {panel === "checkout" &&
              (confirmation ? (
                <div className="success">
                  <Check size={42} />
                  <h2>Your order is placed.</h2>
                  <p>
                    Thank you for choosing Rang Mahal. Save your reference to track
                    your order.
                  </p>
                  <strong className="tracking-id">{confirmation.reference}</strong>
                  <button type="button" className="outline-button" onClick={() => {setTrackingReference(confirmation.reference);setTracked(null);setPanel("track");}}>TRACK THIS ORDER</button>
                  <p>
                    Total: {money(confirmation.total)}
                    <br />
                    {confirmation.payment_method === "cod"
                      ? "Pay when your order arrives."
                      : "Bank transfer is pending. The store must confirm payment instructions before dispatch."}
                  </p>
                  <button className="gold-button" onClick={() => browse()}>
                    CONTINUE SHOPPING
                  </button>
                </div>
              ) : (
                <>
                  <p className="muted">
                    A few details, and your saree will be on its way.
                  </p>
                  {demo && (
                    <div className="notice">
                      You’re browsing the sample collection. Orders become
                      available after the XAMPP database is configured.
                    </div>
                  )}
                  <form
                    onSubmit={(e) => submit(e, "orders")}
                    className="checkout-form"
                  >
                    <div className="form-grid">
                      <label>
                        Full name
                        <input
                          required
                          name="name"
                          maxLength={100}
                          autoComplete="name"
                          defaultValue=""
                        />
                      </label>
                      <label>
                        Phone number
                        <input
                          required
                          name="phone"
                          placeholder="0300 1234567"
                          autoComplete="tel"
                        />
                      </label>
                    </div>
                    <label>
                      Email
                      <input
                        required
                        type="email"
                        name="email"
                        autoComplete="email"
                        defaultValue=""
                      />
                    </label>
                    <label>
                      Delivery address
                      <textarea
                        required
                        name="address"
                        maxLength={500}
                        autoComplete="street-address"
                        placeholder="House, street, area"
                      />
                    </label>
                    <div className="form-grid">
                      <label>
                        City
                        <input
                          required
                          name="city"
                          maxLength={100}
                          autoComplete="address-level2"
                          placeholder="e.g. Lahore"
                        />
                      </label>
                      <label>
                        Country
                        <input value="Pakistan" readOnly />
                      </label>
                    </div>
                    <label>Postal code<input name="postal_code" required maxLength={15} autoComplete="postal-code" placeholder="e.g. 54000" /></label>
                    <h3>Payment method</h3>
                    {payments.cod_enabled && (
                      <label className="radio-label payment-choice">
                        <input
                          type="radio"
                          name="payment_method"
                          value="cod"
                          defaultChecked
                        />{" "}
                        Cash on delivery <span>Pay at your doorstep</span>
                      </label>
                    )}
                    {payments.bank_enabled && (
                      <label className="radio-label payment-choice">
                        <input
                          type="radio"
                          name="payment_method"
                          value="bank"
                          defaultChecked={!payments.cod_enabled}
                        />{" "}
                        Bank transfer <span>Payment confirmation required</span>
                      </label>
                    )}
                    {!payments.cod_enabled && !payments.bank_enabled && (
                      <div className="notice">
                        Checkout is temporarily unavailable. Please try again
                        later.
                      </div>
                    )}
                    <div className="totals">
                      {items.map((i) => (
                        <span key={i.variant_id}>
                          {i.product.name} · {i.variant?.type} × {i.quantity}
                          <b>{money(i.quantity * i.product.price)}</b>
                        </span>
                      ))}
                      <span>
                        Delivery<b>{shipping ? money(shipping) : "Free"}</b>
                      </span>
                      <span className="grand-total">
                        Total <b>{money(subtotal + shipping)}</b>
                      </span>
                    </div>
                    <button
                      className="gold-button"
                      disabled={
                        busy ||
                        demo ||
                        !items.length ||
                        (!payments.cod_enabled && !payments.bank_enabled)
                      }
                    >
                      {busy ? "PLACING YOUR ORDER…" : "PLACE ORDER"}{" "}
                      <ArrowRight size={17} />
                    </button>
                    <p className="fine-print">
                      No account needed. Your tracking ID appears after ordering. Your final total and availability are verified securely by
                      the server.
                    </p>
                  </form>
                </>
              ))}
            {panel === "track" && (
              <>
                <p className="muted">
                  Enter your tracking ID to see your order status. No login needed.
                </p>
                <form onSubmit={(e) => submit(e, "track")}>
                  <label>
                    Order reference
                    <input name="reference" required placeholder="RM-…" value={trackingReference} onChange={e=>{setTrackingReference(e.target.value);setTracked(null);}} />
                  </label>
                  {trackingReference && !trackingReference.toUpperCase().startsWith("RM-") && <label>Email for an older order<input type="email" name="email" required /></label>}
                  <button disabled={busy} className="gold-button">
                    {busy ? "CHECKING…" : "TRACK ORDER"}{" "}
                    <ArrowRight size={17} />
                  </button>
                </form>
                {tracked && (
                  <div className="tracking-result">
                    <PackageCheck size={28} />
                    <h3>{tracked.reference}</h3>
                    <b>{tracked.status}</b>
                    <p>{money(tracked.total)} · {tracked.city}</p>
                    <ol className="tracking-steps">{["Pending","Confirmed","Processing","Packed","Shipped","Out for Delivery","Delivered"].map((step,i,steps)=><li key={step} className={tracked.status !== "Cancelled" && i <= steps.indexOf(tracked.status) ? "complete" : ""}>{step}</li>)}</ol>
                    {tracked.items?.map((i,n)=><p key={n}>{i.name} · {i.variant || "Unstitched"} × {i.quantity}</p>)}
                  </div>
                )}
              </>
            )}
            {panel === "delivery" && (
              <div className="policy">
                <h3>Delivery across Pakistan</h3>
                <p>
                  Delivery is Rs. 250. Orders of Rs. 15,000 or more qualify for
                  complimentary delivery.
                </p>
                <h3>Delivery estimates</h3>
                <p>
                  Estimated delivery is 3–7 working days after confirmation,
                  depending on your city. The store will confirm dispatch
                  details.
                </p>
                <h3>Returns and exchanges</h3>
                <p>
                  Before launch, the store owner must publish an approved return
                  policy and contact details here. Do not place live orders
                  until this information is confirmed.
                </p>
              </div>
            )}
            {panel === "care" && (
              <div className="policy">
                <h3>A little care goes a long way.</h3>
                <p>
                  Dry clean silk, zari and embellished sarees. Store in a
                  breathable cotton bag away from direct sunlight.
                </p>
                <p>
                  Refold occasionally to protect the fabric. Avoid perfume
                  directly on the silk, and use a low iron temperature with a
                  protective cloth.
                </p>
              </div>
            )}
            {panel === "privacy" && (
              <div className="policy">
                <p>
                  We collect your name, contact details and delivery address to
                  process your order. Account passwords are stored as salted
                  hashes; payment card details are never collected by this
                  store.
                </p>
                <p>
                  Your shopping bag and wishlist are stored on this device.
                  Account sessions use an HTTP-only cookie. The store owner must
                  add business contact details and the final privacy policy
                  before launch.
                </p>
              </div>
            )}
          </section>
        </div>
      )}
    </>
  );
}
