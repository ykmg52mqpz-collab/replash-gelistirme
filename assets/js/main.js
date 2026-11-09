// Scroll reveal (replash.info'daki whileInView karşılığı).
// Diğer betikleri beklemeden hemen çalışır; JS yoksa içerik zaten görünür kalır.
(function () {
  var root = document.documentElement;
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduceMotion || !('IntersectionObserver' in window)) return;
  root.classList.add('reveal-ready');
  // Güvence: içerik hiçbir koşulda animasyona bağlı kalmaz. Gözlemci çalışmazsa (ya da betik hatası olursa)
  // kısa süre sonra her şey görünür yapılır.
  setTimeout(function () { root.classList.add('reveal-all'); }, 2500);
  var revealObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      var el = entry.target;
      el.classList.add('is-visible');
      revealObserver.unobserve(el);
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
  // Yenileme D11: iç içe animasyon yok — üstünde zaten belirecek bir blok varsa içtekini doğrudan göster
  document.querySelectorAll('[data-reveal] [data-reveal]').forEach(function (el) { el.removeAttribute('data-reveal'); });
  document.querySelectorAll('[data-reveal]').forEach(function (el) { revealObserver.observe(el); });
})();

// Bölüm arka planı paralaksı (replash.info ParallaxImage karşılığı):
// bölüm ekrana girerken görsel -%16'dan başlar, çıkarken +%16'ya varır.
(function () {
  var layers = document.querySelectorAll('[data-parallax]');
  if (!layers.length) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var ticking = false;
  var update = function () {
    var vh = window.innerHeight;
    layers.forEach(function (layer) {
      var host = layer.parentElement;
      var r = host.getBoundingClientRect();
      if (r.bottom < -200 || r.top > vh + 200) return;
      var strength = parseFloat(layer.getAttribute('data-parallax')) || 16;
      var progress = Math.min(1, Math.max(0, (vh - r.top) / (vh + r.height)));
      layer.style.transform = 'translate3d(0,' + (-strength + progress * 2 * strength).toFixed(2) + '%,0)';
    });
    ticking = false;
  };
  window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
  window.addEventListener('resize', update, { passive: true });
  update();
})();

// Görsellerde kaydırma geçişi (ana sayfa girişindeki gibi):
// data-scroll-img="hero"  → sayfanın üstündeki görsel: kaydırdıkça aşağı kayar ve büyür (%0→%28, 1→1.3)
// data-scroll-img="band"  → sayfa ortasındaki görsel: ekrandan geçerken yavaşça kayar ve uzaklaşır
// Dar ekranda hareket yarıya iner; hareket azaltılmışsa hiç çalışmaz.
(function () {
  var els = document.querySelectorAll('[data-scroll-img]');
  if (!els.length) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  // Kenarı eriyen (photo-melt) görsellerde maske çerçeveye taşınır: görsel büyüyüp kayınca kenar sert kesilmez.
  // Dar ekranda bu görseller akış içinde durduğu için hareket uygulanmaz.
  var isMelt = function (el) { return el.classList && /(^|\s)photo-melt-/.test(el.className); };
  els.forEach(function (el) {
    el.style.transformOrigin = 'center center';
    if (isMelt(el)) {
      el.setAttribute('data-melt', '1');
      Array.prototype.slice.call(el.classList).filter(function (c) { return c.indexOf('photo-melt-') === 0; }).forEach(function (c) {
        el.classList.remove(c); el.parentElement.classList.add(c);
      });
      el.parentElement.style.overflow = 'hidden';
    }
  });
  var ticking = false;
  var update = function () {
    var vh = window.innerHeight, k = window.innerWidth < 1024 ? 0.5 : 1;
    els.forEach(function (el) {
      if (el.hasAttribute('data-melt') && window.innerWidth < 1024) { el.style.transform = ''; return; }
      var host = el.closest('section') || el.parentElement;
      var r = host.getBoundingClientRect();
      if (r.bottom < -200 || r.top > vh + 200) return;
      if (el.getAttribute('data-scroll-img') === 'hero') {
        var p = Math.min(1, Math.max(0, -r.top / (r.height || 1)));
        el.style.transform = 'translate3d(0,' + (p * 28 * k).toFixed(2) + '%,0) scale(' + (1 + p * 0.3 * k).toFixed(4) + ')';
      } else {
        var q = Math.min(1, Math.max(0, (vh - r.top) / (vh + r.height)));
        // kayma ±%6, büyütme 1.16→1.12: görsel her an çerçeveyi doldurur, üstte/altta boşluk açılmaz
        el.style.transform = 'translate3d(0,' + ((q - 0.5) * 12 * k).toFixed(2) + '%,0) scale(' + (1 + (0.16 - q * 0.04) * k).toFixed(4) + ')';
      }
    });
    ticking = false;
  };
  window.addEventListener('scroll', function () { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
  window.addEventListener('resize', update, { passive: true });
  update();
})();

// Hareket azaltma tercihi: maketlerdeki SVG (SMIL) animasyonlarını durdur
if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
  document.querySelectorAll('svg').forEach(function (svg) { if (svg.pauseAnimations) svg.pauseAnimations(); });
}

document.addEventListener('DOMContentLoaded', () => {
  // 2. Mobile menu toggle
  const mobileToggleBtn = document.getElementById('mobile-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  if (mobileToggleBtn && mobileMenu) {
    const setMenu = (open) => {
      mobileMenu.classList.toggle('hidden', !open);
      mobileToggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    mobileToggleBtn.addEventListener('click', () => setMenu(mobileMenu.classList.contains('hidden')));
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !mobileMenu.classList.contains('hidden')) { setMenu(false); mobileToggleBtn.focus(); }
    });
  }

});

// Yenileme D01: dil seçici dışına tıklanınca kapanır
document.addEventListener('click', function (ev) {
  document.querySelectorAll('details.lang-menu[open]').forEach(function (d) { if (!d.contains(ev.target)) d.removeAttribute('open'); });
});

// Yenileme İşlevsellik 02–04: maç kodu kartı (dört kutu, yapıştırma, son maç, sitenin dilinde uyarılar)
(function () {
  var KEY = 'rp_son_kod';
  document.querySelectorAll('form[data-cc]').forEach(function (form) {
    var boxes = Array.prototype.slice.call(form.querySelectorAll('.cc-d'));
    var hidden = form.querySelector('.cc-hidden');
    var err = form.querySelector('.cc-err'), cerr = form.querySelector('.cc-consent-err');
    var cb = form.querySelector('input[name="consent"]');
    var sent = false;
    function digits() { return boxes.map(function (b) { return b.value; }).join(''); }
    function sync() { hidden.value = digits(); }
    function setErr(msg) {
      err.textContent = msg || ''; err.classList.toggle('hidden', !msg);
      boxes.forEach(function (b) { b.classList.toggle('border-rose-400/80', !!msg); b.classList.toggle('border-white/15', !msg); if (msg) b.setAttribute('aria-invalid', 'true'); else b.removeAttribute('aria-invalid'); });
    }
    function setConsentErr(msg) { if (cerr) { cerr.textContent = msg || ''; cerr.classList.toggle('hidden', !msg); } }
    function fill(str) {
      var d = (str || '').replace(/\D/g, '').slice(0, 4).split('');
      boxes.forEach(function (b, i) { b.value = d[i] || ''; });
      sync(); return d.length;
    }
    function trySubmit(auto) {
      sync();
      var v = hidden.value;
      if (!v) { if (!auto) { setErr(form.dataset.msgEmpty); boxes[0].focus(); } return; }
      if (v.length < 4) { if (!auto) { setErr(form.dataset.msgShort); boxes[v.length].focus(); } return; }
      if (cb && !cb.checked) { if (!auto) { setConsentErr(form.dataset.msgConsent); cb.focus(); } else { setConsentErr(form.dataset.msgConsent); } return; }
      if (sent) return; sent = true;
      if (/^\d{4}$/.test(v)) { try { localStorage.setItem(KEY, v); } catch (e) {} }
      form.submit();
    }
    boxes.forEach(function (b, i) {
      b.addEventListener('input', function () {
        var d = b.value.replace(/\D/g, '');
        if (d.length > 1) { fill(digits().slice(0, i) + d); var n = Math.min(3, i + d.length); boxes[n].focus(); }
        else { b.value = d; if (d && i < 3) boxes[i + 1].focus(); }
        setErr(''); sync();
        if (digits().length === 4) trySubmit(true);
      });
      b.addEventListener('keydown', function (ev) {
        if (ev.key === 'Backspace' && !b.value && i > 0) { boxes[i - 1].value = ''; boxes[i - 1].focus(); sync(); ev.preventDefault(); }
        if (ev.key === 'ArrowLeft' && i > 0) boxes[i - 1].focus();
        if (ev.key === 'ArrowRight' && i < 3) boxes[i + 1].focus();
      });
      b.addEventListener('paste', function (ev) {
        var t = (ev.clipboardData || window.clipboardData).getData('text') || '';
        if (/\d/.test(t)) { ev.preventDefault(); var n = fill(t); boxes[Math.min(3, n)].focus(); setErr(''); if (n === 4) trySubmit(true); }
      });
      b.addEventListener('focus', function () { b.select(); });
    });
    if (cb) cb.addEventListener('change', function () { if (cb.checked) { setConsentErr(''); if (digits().length === 4) trySubmit(true); } });
    form.addEventListener('submit', function (ev) { ev.preventDefault(); trySubmit(false); });
    // Son açılan maç: kutunun içinde soluk yazı yerine ayrı satır
    var last = null; try { last = localStorage.getItem(KEY); } catch (e) {}
    var row = form.querySelector('.cc-last');
    if (row && last && /^\d{4}$/.test(last) && !digits()) {
      row.querySelector('.cc-last-code').textContent = last;
      row.classList.remove('hidden'); row.classList.add('flex');
      var lab = form.querySelector('.cc-label'); if (lab && lab.dataset.orNew) lab.textContent = lab.dataset.orNew;
      row.querySelector('.cc-reopen').addEventListener('click', function () {
        fill(last); setErr('');
        if (cb && !cb.checked) { setConsentErr(form.dataset.msgConsent); cb.focus(); return; }
        trySubmit(false);
      });
    }
    sync();
  });
})();
