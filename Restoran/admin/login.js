document.addEventListener('DOMContentLoaded', function(){
  const toggle = document.getElementById('togglePwd');
  const pwd = document.getElementById('password');
  const form = document.getElementById('loginForm');
  const submitBtn = document.getElementById('submitBtn');

  // Clear form fields on load to avoid browser autofill persistence
  if (form) {
    try {
      form.reset();
      if (form.username) form.username.value = '';
      if (form.password) form.password.value = '';
      // remove any remember flags in localStorage/sessionStorage
      try { localStorage.removeItem('remember'); } catch(e){}
      try { sessionStorage.removeItem('remember'); } catch(e){}
    } catch(e) {}
  }

  if(toggle && pwd){
    toggle.addEventListener('click', function(){
      if(pwd.type === 'password'){
        pwd.type = 'text';
        toggle.classList.add('revealed');
        toggle.setAttribute('aria-pressed','true');
      } else {
        pwd.type = 'password';
        toggle.classList.remove('revealed');
        toggle.setAttribute('aria-pressed','false');
      }
    });
  }

  if(form){
    form.addEventListener('submit', function(e){
      // basic client-side guard
      const u = form.username.value.trim();
      const p = form.password.value;
      if(!u || !p){
        e.preventDefault();
        alert('Isi username dan password terlebih dahulu');
        return;
      }
      submitBtn.disabled = true;
      submitBtn.textContent = 'Memeriksa...';
    });
  }
});
