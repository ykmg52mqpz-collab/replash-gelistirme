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

  // 1.1 Hero Parallax & Zoom effect on scroll
  const heroSection = document.getElementById('hero-section');
  const heroBg = document.getElementById('hero-bg-img');

  if (heroSection && heroBg) {
    let ticking = false;
    const handleHeroScroll = () => {
      const scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
      const heroHeight = heroSection.offsetHeight || window.innerHeight || 600;
      if (scrollY <= heroHeight * 1.5) {
        const progress = Math.min(1.5, Math.max(0, scrollY / (heroHeight * 0.75)));
        const scale = 1.0 + (progress * 0.35);
        heroBg.style.transform = `scale(${scale.toFixed(3)})`;
      }
      ticking = false;
    };

    window.addEventListener('scroll', () => {
      if (!ticking) {
        window.requestAnimationFrame(handleHeroScroll);
        ticking = true;
      }
    }, { passive: true });

    handleHeroScroll();
  }

  // 2. Mobile menu toggle
  const mobileToggleBtn = document.getElementById('mobile-menu-btn');
  const mobileMenu = document.getElementById('mobile-menu');
  if (mobileToggleBtn && mobileMenu) {
    mobileToggleBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
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

  // 4b. Scroll reveal (replash.info'daki whileInView karşılığı)
  var revealTargets = document.querySelectorAll('[data-reveal]');
  if (revealTargets.length) {
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduceMotion || !('IntersectionObserver' in window)) {
      revealTargets.forEach(function (el) { el.classList.add('is-visible'); });
    } else {
      var revealObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          // aynı kapsayıcıdaki öğeler sırayla belirsin (30ms kademeli)
          var siblings = el.parentElement ? Array.prototype.filter.call(el.parentElement.children, function (c) { return c.hasAttribute('data-reveal'); }) : [];
          var order = Math.max(0, siblings.indexOf(el));
          el.style.setProperty('--reveal-delay', Math.min(order * 60, 300) + 'ms');
          el.classList.add('is-visible');
          revealObserver.unobserve(el);
        });
      }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
      revealTargets.forEach(function (el) { revealObserver.observe(el); });
    }
  }

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
