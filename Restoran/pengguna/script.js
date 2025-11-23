// script.js
document.addEventListener('DOMContentLoaded', ()=> {
  // SCROLL 
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

  const contactBtn = document.getElementById("contact");
  const contactFooter = document.getElementById("contactFooter");
  
  if(contactBtn && contactFooter) {
    contactBtn.addEventListener("click", function(e) {
      e.preventDefault();
      contactFooter.scrollIntoView({ behavior: "smooth", block: "start" });
    });
  }

  const homeBtn = document.getElementById("home");
  const heroContainer = document.getElementById("heroContainer");

  function scrollToHome() {
    if(heroContainer){
          heroContainer.scrollIntoView({ behavior: "smooth", block: "start" });
      }
  }

  if(homeBtn) homeBtn.addEventListener("click", function(e) {
    e.preventDefault();
    scrollToHome();
  });


  const chips = Array.from(document.querySelectorAll('.chip'));
  const grid = document.getElementById('grid');
  const searchInput = document.getElementById('searchInput');
  const sortSel = document.getElementById('sortSel');

  let cart = [];

  const cartPanel = document.getElementById("cartPanel");
  const openCart = document.getElementById("openCart");
  const closeCart = document.getElementById("closeCart");
  const cartItems = document.getElementById("cartItems");
  const cartTotal = document.getElementById("cartTotal");

  if (!cartPanel) return;

  openCart.onclick = () => cartPanel.classList.add("open");
  closeCart.onclick = () => cartPanel.classList.remove("open");

  function formatRupiah(num){
    return num.toLocaleString("id-ID");
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

      // get stock from data attribute if present
      const stock = card.dataset.stock ? parseInt(card.dataset.stock) : null;
      const exist = cart.find(it => it.title === title);
      if (exist) {
        // check against stock
        if (stock !== null && exist.qty + 1 > stock) {
          return alert('Stok tidak cukup untuk item ini.');
        }
        exist.qty++;
      } else {
        if (stock !== null && 1 > stock) {
          return alert('Stok tidak tersedia untuk item ini.');
        }
        cart.push({ title, price, qty:1 });
      }

      updateCart();
    });
  });

  const checkoutBtn = document.getElementById("checkoutBtn");
  checkoutBtn.addEventListener("click", () => {
    const table = document.getElementById("tableNumber").value;
    const name = document.getElementById("customerName").value;

    if(cart.length === 0) return alert("Keranjang masih kosong!");
    if(!name.trim()) return alert("Nama harus diisi!");
    if(!table) return alert("Nomor meja harus diisi!");

    const order = {
      table: table,
      customer: name,
      items: cart,
      status: "Pending",
      time: new Date().toISOString()
    };

    fetch("save_order.php", {
      method:"POST",
      headers:{ "Content-Type":"application/json" },
      body: JSON.stringify(order)
    })
    .then(res => res.json())
    .then(res => {
      if(res.success){
        // kosongkan keranjang & UI
        cart = [];
        updateCart();
        cartPanel.classList.remove("open");

        // Redirect ke nota.php menggunakan order_code yang dikembalikan PHP
        // nota.php kamu sebelumnya mencari param 'code', jadi gunakan key 'order_code'
        window.location.href = "nota.php?code=" + encodeURIComponent(res.order_code);
      } else {
        alert("Gagal menyimpan pesanan: " + (res.error || "Unknown error"));
      }
    })
    .catch(err => {
      console.error(err);
      alert("Terjadi error saat menyimpan pesanan. Cek console atau log server.");
    });
  });


  function applyFilters() {
    const activeChip = document.querySelector('.chip.active');
    const cat = activeChip ? activeChip.dataset.cat : 'All';
    const q = searchInput.value.trim().toLowerCase();
    const sort = sortSel.value;

    let cards = Array.from(grid.querySelectorAll('.product'));

    cards.forEach(card=>{
      const pCat = card.dataset.cat;
      const title = card.querySelector('h4').textContent.toLowerCase();
      const matches = (cat === 'All' || pCat === cat) &&
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

  searchInput.addEventListener('input', applyFilters);
  sortSel.addEventListener('change', applyFilters);

  document.querySelector('[data-cat="All"]').classList.add('active');

  const testiSlider = document.getElementById("testiSlider");
  const testiItems = testiSlider.querySelectorAll(".single-testimoni");
  let testiIndex = 0;

  // tampilkan testimoni pertama
  if(testiItems.length > 0) testiItems[0].classList.add("active");

  // tombol prev/next
  document.getElementById("prevTesti").addEventListener("click", ()=>{
      testiItems[testiIndex].classList.remove("active");
      testiIndex = (testiIndex - 1 + testiItems.length) % testiItems.length;
      testiItems[testiIndex].classList.add("active");
  });

  document.getElementById("nextTesti").addEventListener("click", ()=>{
      testiItems[testiIndex].classList.remove("active");
      testiIndex = (testiIndex + 1) % testiItems.length;
      testiItems[testiIndex].classList.add("active");
  });

  // Optional: auto slide setiap 5 detik
  setInterval(()=>{
      testiItems[testiIndex].classList.remove("active");
      testiIndex = (testiIndex + 1) % testiItems.length;
      testiItems[testiIndex].classList.add("active");
  }, 5000);


});
