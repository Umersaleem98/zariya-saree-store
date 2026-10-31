"use client";
import { useEffect, useState } from "react";
import {
  Search,
  ShoppingBag,
  UserRound,
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
  Sparkles,
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
    const error = Error(d.error);
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
    [user, setUser] = useState(null),
    [error, setError] = useState(""),
    [busy, setBusy] = useState(false),
    [toast, setToast] = useState(""),
    [confirmation, setConfirmation] = useState(null),
    [register, setRegister] = useState(false),
    [tracked, setTracked] = useState(null),
    [orders, setOrders] = useState([]),
    [mobile, setMobile] = useState(false),
    [brand, setBrand] = useState("All brands");
  const [payments, setPayments] = useState(defaultPayments),
    [googleEnabled, setGoogleEnabled] = useState(false),
    [checkoutIntent, setCheckoutIntent] = useState(false);
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
    });
    api("auth")
      .then((d) => {
        setUser(d.user);
        setGoogleEnabled(d.google_enabled);
        const params = new URLSearchParams(window.location.search);
        if (params.has("auth_error")) {
          setPanel("account");
          setToast(
            params.get("auth_error") === "google_setup"
              ? "Google sign-in needs configuration. You can use email and password."
              : "Google sign-in could not be completed. Please try email and password.",
          );
        }
        if (params.get("checkout") === "1") {
          setCheckoutIntent(true);
          setPanel(d.user ? "checkout" : "account");
        }
        if (params.get("signed_in") === "1") setToast("Welcome to Rang Mahal");
        if (
          params.has("auth_error") ||
          params.has("checkout") ||
          params.has("signed_in")
        )
          window.history.replaceState({}, "", window.location.pathname);
      })
      .catch(() => {});
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
  const items = bag
      .map((i) => ({ ...i, product: products.find((p) => p.id === i.id) }))
      .filter((i) => i.product),
    subtotal = items.reduce((a, i) => a + i.product.price * i.quantity, 0),
    shipping = subtotal >= 15000 ? 0 : 250,
    count = bag.reduce((a, i) => a + i.quantity, 0);
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
  function add(p) {
    const existing = bag.find((i) => i.id === p.id)?.quantity || 0;
    if (existing >= p.stock) {
      setToast("There are no more of this saree available.");
      return;
    }
    setBag((b) =>
      b.some((i) => i.id === p.id)
        ? b.map((i) => (i.id === p.id ? { ...i, quantity: i.quantity + 1 } : i))
        : [...b, { id: p.id, quantity: 1 }],
    );
    setToast(`${p.name} added to your bag`);
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
      const d = await api(path, path === "orders" ? { ...b, items: bag } : b);
      if (path.startsWith("auth")) {
        setUser(d.user);
        if (checkoutIntent) {
          const settings = await api("settings");
          setPayments(settings.payments);
          setPanel("checkout");
        } else setPanel(null);
        setToast("Welcome to Rang Mahal");
      }
      if (path === "orders") {
        setConfirmation(d);
        setBag([]);
        setCheckoutIntent(false);
        api("catalog").then((d) => setProducts(d.products));
      }
      if (path === "track") setTracked(d.order);
    } catch (e) {
      if (path === "orders" && e.status === 401) {
        setUser(null);
        setCheckoutIntent(true);
        setRegister(false);
        setPanel("account");
      }
      setError(e.message);
    } finally {
      setBusy(false);
    }
  }
  async function beginCheckout() {
    setBusy(true);
    setConfirmation(null);
    try {
      const auth = await api("auth");
      setUser(auth.user);
      setGoogleEnabled(auth.google_enabled);
      setCheckoutIntent(true);
      if (auth.user) {
        const settings = await api("settings");
        setPayments(settings.payments);
        setPanel("checkout");
      } else {
        setRegister(false);
        setPanel("account");
      }
    } catch (e) {
      setError(e.message);
    } finally {
      setBusy(false);
    }
  }
  async function account() {
    setCheckoutIntent(false);
    setPanel("account");
    if (user)
      try {
        setOrders((await api("orders")).orders);
      } catch (e) {
        setError(e.message);
      }
  }
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
            <a className="brand-logo header-logo" href="/" aria-label="Rang Mahal — home">
              <img src="/images/rang-mahal-logo.webp" alt="Rang Mahal — Sarees & Couture" width="640" height="640" fetchPriority="high" />
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
              <IconButton label="My account" onClick={account}>
                <UserRound size={20} />
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
              <div className="hero-bottom">
                <span>A little heritage. A little you.</span>
                <span className="hero-line" />
              </div>
            </div>
            <div className="hero-caption">
              <span>THE NOOR EDIT</span>
              <span>Tradition, reimagined.</span>
            </div>
            <div className="hero-count">
              <span>SIX YARDS OF GRACE</span>
            </div>
          </section>
          <div className="trust-bar">
            <span>
              <Truck /> DELIVERY ACROSS PAKISTAN
            </span>
            <span>
              <Sparkles /> THOUGHTFULLY CURATED QUALITY
            </span>
            <span>
              <ShieldCheck /> SECURE SHOPPING
            </span>
            <span>
              <PackageCheck />{" "}
              {payments.cod_enabled ? "CASH ON DELIVERY" : "SECURE CHECKOUT"}
            </span>
          </div>
          <section className="category-section wrap">
            <div className="section-intro">
              <div>
                <div className="eyebrow">FOR YOUR EVERY CELEBRATION</div>
                <h2>
                  Tradition, in <em>your favourite colours.</em>
                </h2>
              </div>
              <button className="text-link" onClick={() => browse()}>
                Shop all collections <ArrowUpRight size={16} />
              </button>
            </div>
            <div className="category-grid">
              {[
                {
                  name: "Silk Sarees",
                  sub: "Rich colour. Graceful drapes.",
                  img: "ruby",
                },
                {
                  name: "Wedding Edit",
                  sub: "For mehndi nights and wedding memories",
                  img: "emerald",
                },
                {
                  name: "Party Wear",
                  sub: "An elegant choice for your next dawat",
                  img: "black",
                },
                {
                  name: "Everyday Elegance",
                  sub: "A little beauty in everyday moments",
                  img: "ivory",
                },
              ].map((c) => (
                <button
                  key={c.name}
                  onClick={() => browse(c.name)}
                  className="category-card"
                >
                  <img
                    loading="lazy"
                    src={`/images/${c.img}.jpg`}
                    alt={c.name}
                  />
                  <div>
                    <span>{c.sub}</span>
                    <h3>
                      {c.name} <ArrowUpRight size={23} />
                    </h3>
                  </div>
                </button>
              ))}
            </div>
          </section>
          <section className="collection wrap" id="collection">
            <div className="section-intro">
              <div>
                <div className="eyebrow">
                  SAREES ONLINE IN PAKISTAN
                </div>
                <h2>
                  {category === "Wishlist" ? (
                    "Your favourites."
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
              <div className="tabs">
                {["All Sarees", ...categories].map((c) => (
                  <button
                    className={category === c ? "active" : ""}
                    onClick={() => setCategory(c)}
                    key={c}
                  >
                    {c}
                  </button>
                ))}
              </div>
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
              {visible.map((p) => (
                <article className="product-card" key={p.id}>
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
                    {p.badge && (
                      <span
                        className={
                          "badge " + (p.badge === "NEW ARRIVAL" ? "rose" : "")
                        }
                      >
                        {p.badge}
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
                    <span>{p.fabric}</span>
                    <span
                      className={"swatch " + p.color.toLowerCase()}
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
                </article>
              ))}
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
              <button
                className="text-link"
                onClick={() => browse("Silk Sarees")}
              >
                Explore the saree collection <ArrowUpRight size={18} />
              </button>
              <div className="story-signature">With love, Rang Mahal.</div>
            </div>
          </section>
          <section className="service-section wrap">
            <div>
              <Truck size={28} />
              <h3>From us, to your doorstep.</h3>
              <p>
                Delivery across Pakistan.
                <br />
                Free on orders of Rs. 15,000 or more.
              </p>
            </div>
            <div>
              <Sparkles size={28} />
              <h3>Beauty in every detail.</h3>
              <p>
                Beautiful fabrics. Thoughtful finishes.
                <br />
                Chosen for your special moments.
              </p>
            </div>
            <div>
              <Heart size={28} />
              <h3>A little help, a lovely choice.</h3>
              <p>
                Fabric and draping details
                <br />
                on every product page.
              </p>
            </div>
          </section>
          <section className="closing">
            <div className="eyebrow">YOUR NEXT BEAUTIFUL MOMENT</div>
            <h2>
              Your next celebration. <em>Your own colours.</em>
            </h2>
            <button className="gold-button" onClick={() => browse()}>
              FIND YOUR DRAPE <ArrowUpRight size={17} />
            </button>
          </section>
        </main>
        <footer>
          <div className="footer-main wrap">
            <div className="footer-brand">
              <a className="brand-logo footer-logo" href="/" aria-label="Rang Mahal — home">
                <img src="/images/rang-mahal-logo.webp" alt="Rang Mahal — Sarees & Couture" width="640" height="640" loading="lazy" decoding="async" />
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
              <button onClick={account}>My account</button>
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
              <a href="/admin" className="admin-link">
                Store administration <ArrowUpRight size={12} />
              </a>
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
            <img src={selected.image} alt={selected.name} />
            <div className="product-detail">
              <div className="eyebrow">{selected.brand}</div>
              <h2>{selected.name}</h2>
              <div className="price">{money(selected.price)}</div>
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
                  Availability <b>{selected.stock ? "In stock" : "Sold out"}</b>
                </span>
              </div>
              <button
                className="gold-button"
                disabled={!selected.stock}
                onClick={() => add(selected)}
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
                Sample collection image. Replace with actual product photography
                before launch.
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
                    account: user ? "Your account" : "Welcome to Rang Mahal",
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
                      <div className="bag-item" key={i.id}>
                        <img src={i.product.image} alt={i.product.name} />
                        <div>
                          <h3>{i.product.name}</h3>
                          <p>
                            {i.product.color} · {i.product.fabric}
                          </p>
                          <b>{money(i.product.price)}</b>
                          <div className="quantity">
                            <IconButton
                              label="Decrease quantity"
                              onClick={() =>
                                setBag((b) =>
                                  b
                                    .map((x) =>
                                      x.id === i.id
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
                              onClick={() => add(i.product)}
                            >
                              <Plus size={13} />
                            </IconButton>
                          </div>
                        </div>
                        <IconButton
                          label="Remove item"
                          onClick={() =>
                            setBag((b) => b.filter((x) => x.id !== i.id))
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
                            ? "Calculated at checkout"
                            : "Complimentary"}
                        </b>
                      </span>
                    </div>
                    <button
                      className="gold-button"
                      onClick={beginCheckout}
                      disabled={busy}
                    >
                      {busy
                        ? "PLEASE WAIT…"
                        : user
                          ? "PROCEED TO CHECKOUT"
                          : "SIGN IN TO CHECKOUT"}{" "}
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
              user &&
              (confirmation ? (
                <div className="success">
                  <Check size={42} />
                  <h2>Your order is placed.</h2>
                  <p>
                    Thank you for choosing Rang Mahal. Save your reference to track
                    your order.
                  </p>
                  <strong>{confirmation.reference}</strong>
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
                          defaultValue={user?.name}
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
                        defaultValue={user?.email}
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
                        <span key={i.id}>
                          {i.product.name} × {i.quantity}
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
                      Your final total and availability are verified securely by
                      the server.
                    </p>
                  </form>
                </>
              ))}
            {panel === "account" &&
              (user ? (
                <>
                  <p>Welcome, {user.name}.</p>
                  {user.role === "admin" && (
                    <a className="gold-button" href="/admin">
                      OPEN ADMIN DASHBOARD <ArrowUpRight size={17} />
                    </a>
                  )}
                  <h3>Your orders</h3>
                  {orders.length ? (
                    orders.map((o) => (
                      <div className="account-order" key={o.id}>
                        <b>{o.reference}</b>
                        <span>
                          {o.status} · {money(o.total)}
                        </span>
                      </div>
                    ))
                  ) : (
                    <p className="muted">
                      Your orders will appear here when you shop while signed
                      in.
                    </p>
                  )}
                  <button
                    className="outline-button"
                    onClick={async () => {
                      try {
                        await api("auth/logout", {});
                        setUser(null);
                        setOrders([]);
                      } catch (e) {
                        setError(e.message);
                      }
                    }}
                  >
                    SIGN OUT
                  </button>
                </>
              ) : (
                <>
                  {checkoutIntent && (
                    <div className="checkout-auth-banner">
                      <ShieldCheck size={22} />
                      <div>
                        <strong>Your beautiful choice is saved.</strong>
                        <p>
                          Sign in or create an account to place your order. Your
                          shopping bag stays right here.
                        </p>
                      </div>
                    </div>
                  )}
                  <button
                    className="google-button"
                    disabled={!googleEnabled || busy}
                    onClick={() => {
                      window.location.href =
                        "/api/auth/google" +
                        (checkoutIntent ? "?next=checkout" : "");
                    }}
                  >
                    <span className="google-letter">G</span> Continue with
                    Google
                  </button>
                  {!googleEnabled && (
                    <p className="google-setup-note">
                      Google sign-in is currently unavailable. Use your Gmail or
                      another email with a password below.
                    </p>
                  )}
                  <div className="auth-divider">
                    <span />
                    or continue with email
                    <span />
                  </div>
                  <div className="auth-tabs">
                    <button
                      className={!register ? "active" : ""}
                      onClick={() => setRegister(false)}
                    >
                      Sign in
                    </button>
                    <button
                      className={register ? "active" : ""}
                      onClick={() => setRegister(true)}
                    >
                      Create account
                    </button>
                  </div>
                  <form
                    onSubmit={(e) =>
                      submit(e, register ? "auth/register" : "auth/login")
                    }
                  >
                    {register && (
                      <label>
                        Your name
                        <input
                          required
                          name="name"
                          maxLength={100}
                          autoComplete="name"
                        />
                      </label>
                    )}
                    <label>
                      Email
                      <input
                        required
                        type="email"
                        name="email"
                        autoComplete="email"
                      />
                    </label>
                    <label>
                      Password
                      <input
                        required
                        type="password"
                        name="password"
                        minLength={register ? 12 : 1}
                        maxLength={128}
                        autoComplete={
                          register ? "new-password" : "current-password"
                        }
                      />
                    </label>
                    {register && <small>Use at least 12 characters.</small>}
                    <button className="gold-button" disabled={busy}>
                      {busy
                        ? "PLEASE WAIT…"
                        : register
                          ? "CREATE ACCOUNT"
                          : checkoutIntent
                            ? "SIGN IN & CONTINUE"
                            : "SIGN IN"}{" "}
                      <ArrowRight size={17} />
                    </button>
                  </form>
                </>
              ))}
            {panel === "track" && (
              <>
                <p className="muted">
                  Enter the reference and email used at checkout.
                </p>
                <form onSubmit={(e) => submit(e, "track")}>
                  <label>
                    Order reference
                    <input name="reference" required placeholder="ZR-…" />
                  </label>
                  <label>
                    Email
                    <input type="email" name="email" required />
                  </label>
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
                    <p>{money(tracked.total)}</p>
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
