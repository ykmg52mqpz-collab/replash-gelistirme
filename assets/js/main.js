// Scroll reveal (replash.info'daki whileInView karşılığı).
// Diğer betikleri beklemeden hemen çalışır; JS yoksa içerik zaten görünür kalır.
(function () {
  var root = document.documentElement;
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduceMotion || !('IntersectionObserver' in window)) return;
  root.classList.add('reveal-ready');
  var revealObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      var el = entry.target;
      var siblings = el.parentElement ? Array.prototype.filter.call(el.parentElement.children, function (c) { return c.hasAttribute('data-reveal'); }) : [];
      var order = Math.max(0, siblings.indexOf(el));
      el.style.setProperty('--reveal-delay', Math.min(order * 60, 300) + 'ms');
      el.classList.add('is-visible');
      revealObserver.unobserve(el);
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
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

// Sayaç (replash.info CountUp): görünür olunca 0'dan hedefe 1,4 sn'de, yavaşlayarak çıkar.
// JS yoksa HTML'deki son değer görünür kalır.
(function () {
  var els = document.querySelectorAll('[data-countup]');
  if (!els.length || !('IntersectionObserver' in window)) return;
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var fmt = function (el, v) { el.textContent = (el.getAttribute('data-prefix') || '') + v.toLocaleString() + (el.getAttribute('data-suffix') || ''); };
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      var el = entry.target, to = parseFloat(el.getAttribute('data-countup')) || 0;
      var dur = (parseFloat(el.getAttribute('data-duration')) || 1.4) * 1000, start = performance.now();
      var tick = function (now) {
        var t = Math.min(1, (now - start) / dur);
        // data-ease="smooth": baştan sona eşit hızda, yavaş yavaş yükselir
        var k = el.getAttribute('data-ease') === 'smooth' ? -(Math.cos(Math.PI * t) - 1) / 2 : 1 - Math.pow(1 - t, 3);
        fmt(el, Math.round(to * k));
        if (t < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
      io.unobserve(el);
    });
  }, { rootMargin: '-40px' });
  els.forEach(function (el) { fmt(el, 0); io.observe(el); });
})();

// Hareket azaltma tercihi: maketlerdeki SVG (SMIL) animasyonlarını durdur
if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
  document.querySelectorAll('svg').forEach(function (svg) { if (svg.pauseAnimations) svg.pauseAnimations(); });
}

document.addEventListener('DOMContentLoaded', () => {
  // 1. Header scroll effect
  const pillHeader = document.querySelector('.glass-pill');
  if (pillHeader) {
    const handleScroll = () => {
      if (window.scrollY > 20) {
        pillHeader.classList.add('scrolled');
      } else {
        pillHeader.classList.remove('scrolled');
      }
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
  }

  // 2. Mobile menu toggle
  const mobileToggleBtn = document.getElementById('mobile-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  if (mobileToggleBtn && mobileMenu) {
    mobileToggleBtn.addEventListener('click', () => {
      const open = mobileMenu.classList.toggle('hidden') === false;
      mobileToggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  // 3. Language Selector Dropdown
  const langBtn = document.getElementById('lang-btn');
  const langDropdown = document.getElementById('lang-dropdown');
  if (langBtn && langDropdown) {
    const chevron = langBtn.querySelector('svg');
    langBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isHidden = langDropdown.classList.toggle('hidden');
      if (chevron) {
        chevron.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(180deg)';
      }
    });

    document.addEventListener('click', (e) => {
      if (!langDropdown.contains(e.target) && !langBtn.contains(e.target)) {
        langDropdown.classList.add('hidden');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        langDropdown.classList.add('hidden');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
      }
    });
  }

  // 4. FAQ Accordion
  const faqToggles = document.querySelectorAll('.faq-toggle');
  faqToggles.forEach(toggle => {
    toggle.addEventListener('click', () => {
      const content = toggle.nextElementSibling;
      const icon = toggle.querySelector('.faq-icon');
      
      const isOpen = !content.classList.contains('hidden');
      
      // Close all others
      document.querySelectorAll('.faq-content').forEach(c => c.classList.add('hidden'));
      document.querySelectorAll('.faq-icon').forEach(i => i.classList.remove('rotate-180'));

      if (!isOpen) {
        content.classList.remove('hidden');
        if (icon) icon.classList.add('rotate-180');
      }
    });
  });

  // 5. Dynamic Date/Slot lookup on Home Page
  const facilitySelect = document.getElementById('home-facility-select');
  const dateInput = document.getElementById('home-date-select');
  const slotSelect = document.getElementById('home-slot-select');

  if (facilitySelect && dateInput && slotSelect) {
    const fetchSlots = async () => {
      const facId = facilitySelect.value;
      const dateVal = dateInput.value;
      if (!facId || !dateVal) return;

      slotSelect.innerHTML = '<option value="">Slotlar yükleniyor...</option>';
      try {
        const res = await fetch(`api/matches.php?action=get_slots&facility_id=${encodeURIComponent(facId)}&date=${encodeURIComponent(dateVal)}`);
        const data = await res.json();
        if (data.ok && data.slots && data.slots.length > 0) {
          slotSelect.innerHTML = '<option value="">Bir slot seçin</option>';
          data.slots.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.time_slot;
            opt.textContent = `${s.court} · ${s.time_slot} (Hazır)`;
            opt.dataset.matchId = s.id;
            slotSelect.appendChild(opt);
          });
        } else {
          slotSelect.innerHTML = '<option value="">Bu tarihte kayıtlı maç bulunamadı</option>';
        }
      } catch (err) {
        slotSelect.innerHTML = '<option value="">Slotlar alınamadı</option>';
      }
    };

    facilitySelect.addEventListener('change', fetchSlots);
    dateInput.addEventListener('change', fetchSlots);
  }
});
