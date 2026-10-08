const searchForm = document.querySelector('.search-form');
const searchInput = document.querySelector('#search-products');
const productResults = document.querySelector('#product-results');
const resultSummary = document.querySelector('.result-count');
const categoryLinks = document.querySelectorAll('.category-button');
let searchTimer;
let activeRequest;

function updateCategoryLinks(searchTerm) {
  categoryLinks.forEach((link) => {
    const linkUrl = new URL(link.href, window.location.href);
    const category = linkUrl.searchParams.get('kategori');
    const parameters = new URLSearchParams();

    if (category) parameters.set('kategori', category);
    if (searchTerm) parameters.set('q', searchTerm);

    const query = parameters.toString();
    link.href = `${window.location.pathname}${query ? `?${query}` : ''}#produk`;
  });
}

async function filterProducts() {
  const searchTerm = searchInput.value.trim();
  const formData = new FormData(searchForm);
  const selectedCategory = (formData.get('kategori') || '').toString();
  const parameters = new URLSearchParams();

  if (searchTerm) parameters.set('q', searchTerm);
  if (selectedCategory) parameters.set('kategori', selectedCategory);
  parameters.set('ajax', '1');

  activeRequest?.abort();
  activeRequest = new AbortController();
  productResults.setAttribute('aria-busy', 'true');

  try {
    const response = await fetch(`${window.location.pathname}?${parameters.toString()}`, {
      signal: activeRequest.signal
    });
    if (!response.ok) throw new Error('Pencarian produk gagal.');

    productResults.innerHTML = await response.text();
    const summary = [];
    if (searchTerm) summary.push(`Pencarian: ${searchTerm}`);
    if (selectedCategory) summary.push(`Kategori: ${selectedCategory}`);
    resultSummary.textContent = summary.join(' · ') || 'Semua kategori';

    const urlParameters = new URLSearchParams();
    if (searchTerm) urlParameters.set('q', searchTerm);
    if (selectedCategory) urlParameters.set('kategori', selectedCategory);
    const query = urlParameters.toString();
    window.history.replaceState(null, '', `${window.location.pathname}${query ? `?${query}` : ''}#produk`);
    updateCategoryLinks(searchTerm);
  } catch (error) {
    if (error.name !== 'AbortError') searchForm.submit();
  } finally {
    productResults.removeAttribute('aria-busy');
  }
}

searchInput?.addEventListener('input', () => {
  window.clearTimeout(searchTimer);
  searchTimer = window.setTimeout(filterProducts, 250);
});

searchForm?.addEventListener('submit', (event) => {
  event.preventDefault();
  window.clearTimeout(searchTimer);
  filterProducts();
});