document.addEventListener('DOMContentLoaded', ()=> {

  /* ===========================
     SCROLL NAV
  =========================== */
  const orderNowBtn = document.getElementById("orderNowBtn");
  const searchBtn = document.getElementById("searchBtn");
  const ourMenu = document.getElementById("ourMenu");
  const menuSection = document.getElementById("menuSection");

  function scrollToMenu() {
    if(menuSection){
        menuSection.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }

  if(orderNowBtn) orderNowBtn.addEventListener("click", scrollToMenu);
  if(searchBtn) searchBtn.addEventListener("click", scrollToMenu);
  if(ourMenu) ourMenu.addEventListener("click", function(e) {
    e.preventDefault();
    scrollToMenu();
  });


  /* ===========================
     KATEGORI FILTER
  =========================== */
  const chips = Array.from(document.querySelectorAll('.chip'));
  const grid = document.getElementById('grid');
  const searchInput = document.getElementById('searchInput');
  const sortSel = document.getElementById('sortSel');

  function applyFilters() {
    const activeChip = document.querySelector('.chip.active');
    const cat = activeChip ? activeChip.dataset.cat : "all";

    const q = searchInput.value.trim().toLowerCase();
    const sort = sortSel.value;

    let cards = Array.from(grid.querySelectorAll('.product'));

    cards.forEach(card=>{
      const pCat = card.dataset.cat; // angka ID kategori
      const title = card.querySelector('h4').textContent.toLowerCase();

      const matches =
        (cat === "all" || pCat === cat) &&
        title.includes(q);

      card.style.display = matches ? '' : 'none';
    });

    if(sort.includes("price")){
      const visible = cards.filter(c => c.style.display !== 'none');
      visible.sort((a,b)=>{
        const pa = parseFloat(a.dataset.price);
        const pb = parseFloat(b.dataset.price);
        return sort === 'price-asc' ? pa-pb : pb-pa;
      });
      visible.forEach(v => grid.appendChild(v));
    }
  }

  chips.forEach(chip=>{
    chip.addEventListener('click', ()=>{
      chips.forEach(c=>c.classList.remove('active'));
      chip.classList.add('active');
      applyFilters();
    });
  });

  // Set default = ALL
  const allChip = document.querySelector('[data-cat="all"]');
  if(allChip) allChip.classList.add('active');


  /* ===========================
     SEARCH & SORT
  =========================== */
  searchInput.addEventListener('input', applyFilters);
  sortSel.addEventListener('change', applyFilters);


  /* ===========================
     KERANJANG
  =========================== */
  let cart = [];

  function formatRupiah(num){
    return num.toLocaleString("id-ID");
  }

  const cartPanel = document.getElementById("cartPanel");
  const openCart = document.getElementById("openCart");
  const closeCart = document.getElementById("closeCart");
  const cartItems = document.getElementById("cartItems");
  const cartTotal = document.getElementById("cartTotal");

  if(openCart && cartPanel) {
    openCart.onclick = () => cartPanel.classList.add("open");
  }
  if(closeCart && cartPanel) {
    closeCart.onclick = () => cartPanel.classList.remove("open");
  }

  function updateCart() {
    cartItems.innerHTML = "";
    let total = 0;

    cart.forEach((item, index) => {
      total += item.price * item.qty;
      const div = document.createElement('div');
      div.className = 'cart-item';
      div.innerHTML = `
        <div>
          <h4>${item.title}</h4>
          <small>Rp ${formatRupiah(item.price)}</small>
        </div>
        <div>
          <button class="qty-btn" data-i="${index}" data-d="-1">−</button>
          <span style="margin:0 8px">${item.qty}</span>
          <button class="qty-btn" data-i="${index}" data-d="1">+</button>
        </div>
      `;
      cartItems.appendChild(div);
    });

    cartTotal.innerText = formatRupiah(total);

    document.querySelectorAll('.qty-btn').forEach(btn=>{
      btn.onclick = () => {
        const i = parseInt(btn.dataset.i);
        const d = parseInt(btn.dataset.d);
        cart[i].qty += d;
        if (cart[i].qty <= 0) cart.splice(i,1);
        updateCart();
      };
    });
  }

  document.querySelectorAll(".product .btn.small").forEach((btn) => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".product");
      const title = card.querySelector("h4").textContent;
      const price = parseFloat(card.dataset.price);

      const exist = cart.find(it => it.title === title);
      if(exist) exist.qty++;
      else cart.push({ title, price, qty:1 });

      updateCart();
    });
  });


});
