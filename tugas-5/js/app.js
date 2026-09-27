// Data katalog: setiap object berisi informasi yang ditampilkan pada satu kartu produk.
const products = [
  {
    id: 1,
    name: "Headphone Studio One",
    category: "Teknologi",
    price: 649000,
    description: "Suara detail, nyaman dipakai seharian.",
    image: "assets/images/headphone-studio.jpg",
    label: "Favorit",
    color: "#e7e5df"
  },
  {
    id: 2,
    name: "Lampu Meja Arc",
    category: "Rumah",
    price: 429000,
    description: "Cahaya hangat untuk sudut yang tenang.",
    image: "assets/images/lampu-meja.jpg",
    label: "Pilihan editor",
    color: "#e7e7df"
  },
  {
    id: 3,
    name: "Jam Tangan Field",
    category: "Gaya hidup",
    price: 789000,
    description: "Desain sederhana, siap ikut ke mana saja.",
    image: "assets/images/jam-tangan.jpg",
    label: "Baru",
    color: "#e5e8e3"
  },
  {
    id: 4,
    name: "Sneaker Everyday",
    category: "Gaya hidup",
    price: 579000,
    description: "Ringan untuk langkah panjang dan santai.",
    image: "assets/images/sneaker.jpg",
    label: "Terlaris",
    color: "#eee4dc"
  },
  {
    id: 5,
    name: "Tumbler Transit",
    category: "Gaya hidup",
    price: 189000,
    description: "Menjaga minuman tetap pas sepanjang hari.",
    image: "assets/images/tumbler.jpg",
    label: "",
    color: "#e2e8e8"
  },
  {
    id: 6,
    name: "Kamera Pocket 35",
    category: "Teknologi",
    price: 1199000,
    description: "Momen sehari-hari, tersimpan lebih berkesan.",
    image: "assets/images/kamera-pocket.jpg",
    label: "Edisi pilihan",
    color: "#e7e4dc"
  },
  {
    id: 7,
    name: "Vas Bentuk Sora",
    category: "Rumah",
    price: 249000,
    description: "Siluet organik untuk meja dan rak favorit.",
    image: "assets/images/vas-sora.jpg",
    label: "",
    color: "#e8e3df"
  },
  {
    id: 8,
    name: "Tas Harian Transit",
    category: "Aksesori",
    price: 359000,
    description: "Ruang cukup untuk semua yang penting.",
    image: "assets/images/tas-transit.jpg",
    label: "",
    color: "#e6e7df"
  }
];

const categoryList = document.querySelector("#category-list");
const navCategoryList = document.querySelector("#nav-category-list");
const allProductsLink = document.querySelector("#all-products-link");
const productGrid = document.querySelector("#product-grid");
const resultCount = document.querySelector("#result-count");
const emptyState = document.querySelector("#empty-state");
let activeCategory = "Semua";

// Ubah angka harga menjadi format rupiah untuk ditampilkan pada kartu.
function formatPrice(price) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0
  }).format(price);
}

// Buat elemen kartu dari satu object produk agar data dan tampilan tetap terpisah.
function createProductCard(product, index) {
  const card = document.createElement("article");
  card.className = "product-card";
  card.style.setProperty("--product-color", product.color);
  card.style.setProperty("--card-order", index);

  const imageFrame = document.createElement("div");
  imageFrame.className = "product-image-frame";
  const image = document.createElement("img");
  image.src = product.image;
  image.alt = product.name;
  image.loading = "lazy";
  imageFrame.append(image);

  if (product.label) {
    const label = document.createElement("span");
    label.className = "product-label";
    label.textContent = product.label;
    imageFrame.append(label);
  }

  const details = document.createElement("div");
  details.className = "product-details";
  const category = document.createElement("p");
  category.className = "product-category";
  category.textContent = product.category;
  const name = document.createElement("h3");
  name.textContent = product.name;
  const description = document.createElement("p");
  description.className = "product-description";
  description.textContent = product.description;
  const price = document.createElement("strong");
  price.className = "product-price";
  price.textContent = formatPrice(product.price);

  details.append(category, name, description, price);
  card.append(imageFrame, details);
  return card;
}

// Ambil kategori unik dari array agar pilihan navbar dan filter selalu sesuai isi katalog.
function renderCategories() {
  const categories = ["Semua", ...new Set(products.map((product) => product.category))];
  categoryList.replaceChildren();
  navCategoryList.replaceChildren();
  allProductsLink.toggleAttribute("aria-current", activeCategory === "Semua");
  if (activeCategory === "Semua") allProductsLink.setAttribute("aria-current", "page");

  categories.forEach((category) => {
    const filterButton = document.createElement("button");
    filterButton.type = "button";
    filterButton.className = "category-button";
    filterButton.textContent = category;
    filterButton.setAttribute("aria-pressed", String(category === activeCategory));
    filterButton.addEventListener("click", () => selectCategory(category));
    categoryList.append(filterButton);

    if (category !== "Semua") {
      const navLink = document.createElement("a");
      navLink.href = "#produk";
      navLink.textContent = category;
      if (category === activeCategory) navLink.setAttribute("aria-current", "page");
      navLink.addEventListener("click", () => selectCategory(category));
      navCategoryList.append(navLink);
    }
  });
}

// Saat kategori dipilih, perbarui penanda navigasi dan tampilkan produk yang cocok.
function selectCategory(category) {
  activeCategory = category;
  renderCategories();
  renderProducts();
}

// Filter array produk, lalu gunakan loop untuk membangun ulang kartu pada halaman.
function renderProducts() {
  const visibleProducts = products.filter((product) => (
    activeCategory === "Semua" || product.category === activeCategory
  ));

  productGrid.replaceChildren();
  visibleProducts.forEach((product, index) => {
    productGrid.append(createProductCard(product, index));
  });
  resultCount.textContent = `${visibleProducts.length} produk`;
  emptyState.hidden = visibleProducts.length > 0;
}

// Tautan Belanja mengembalikan filter ke seluruh produk.
allProductsLink.addEventListener("click", () => selectCategory("Semua"));

// Tampilkan semua produk dan kategori saat halaman pertama kali dibuka.
renderCategories();
renderProducts();
