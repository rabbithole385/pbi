/* PBI Group — progressive enhancements. No dependencies. */
(function () {
  'use strict';

  // Confirm destructive or irreversible actions.
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-confirm]');
    if (el && !window.confirm(el.getAttribute('data-confirm'))) {
      e.preventDefault();
      e.stopPropagation();
    }
  });

  // Stop double submits.
  // Careful: a disabled submit button is excluded from the form data, so where the
  // clicked button carries the decision (approve vs decline) its value must be copied
  // into a hidden field before anything is disabled.
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (f.dataset.busy === '1') { e.preventDefault(); return; }
    f.dataset.busy = '1';

    var clicked = e.submitter || null;

    if (clicked && clicked.name) {
      var keep = f.querySelector('input[data-submitter]');
      if (!keep) {
        keep = document.createElement('input');
        keep.type = 'hidden';
        keep.setAttribute('data-submitter', '');
        f.appendChild(keep);
      }
      keep.name = clicked.name;
      keep.value = clicked.value;
    }

    // Fall back to the first submit button only when the browser gave us no submitter,
    // and never disable a named one in that case — doing so would drop its value.
    var b = clicked || f.querySelector('button[type=submit]');
    if (b && (clicked || !b.name)) {
      var label = b.textContent;
      b.disabled = true;
      b.textContent = b.dataset.busyText || 'Working…';
      setTimeout(function () { b.disabled = false; b.textContent = label; f.dataset.busy = '0'; }, 8000);
    } else {
      setTimeout(function () { f.dataset.busy = '0'; }, 8000);
    }
  });

  // Copy to clipboard.
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-copy]');
    if (!el) return;
    e.preventDefault();
    var text = el.getAttribute('data-copy');
    var done = function () {
      var old = el.textContent;
      el.textContent = 'Copied';
      setTimeout(function () { el.textContent = old; }, 1400);
    };
    if (navigator.clipboard) { navigator.clipboard.writeText(text).then(done); }
    else {
      var t = document.createElement('textarea');
      t.value = text; document.body.appendChild(t); t.select();
      try { document.execCommand('copy'); done(); } catch (err) {}
      document.body.removeChild(t);
    }
  });

  // Live loan quote.
  var lc = document.getElementById('loanCalc');
  if (lc) {
    var amt = lc.querySelector('[name=amount]');
    var ten = lc.querySelector('[name=tenure_months]');
    var rate = parseFloat(lc.dataset.rate || '0');
    var out = {
      monthly: document.getElementById('qMonthly'),
      total: document.getElementById('qTotal'),
      interest: document.getElementById('qInterest')
    };
    var sym = lc.dataset.symbol || '$';
    var fmt = function (n) {
      return sym + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    };
    var calc = function () {
      var a = parseFloat((amt.value || '0').replace(/,/g, '')) || 0;
      var m = parseInt(ten.value, 10) || 1;
      var interest = a * (rate / 100) * (m / 12);
      var total = a + interest;
      if (out.monthly) out.monthly.textContent = fmt(total / m);
      if (out.total) out.total.textContent = fmt(total);
      if (out.interest) out.interest.textContent = fmt(interest);
    };
    amt.addEventListener('input', calc);
    ten.addEventListener('change', calc);
    calc();
  }

  // Reveal card details.
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-reveal]');
    if (!el) return;
    e.preventDefault();
    var box = document.getElementById(el.getAttribute('data-reveal'));
    if (!box) return;
    var shown = box.dataset.shown === '1';
    box.textContent = shown ? box.dataset.masked : box.dataset.full;
    box.dataset.shown = shown ? '0' : '1';
    el.textContent = shown ? 'Show details' : 'Hide details';
  });

  // Transfer type switcher.
  var kind = document.getElementById('kindSelect');
  if (kind) {
    var sync = function () {
      document.querySelectorAll('[data-kind]').forEach(function (n) {
        var kinds = n.getAttribute('data-kind').split(',');
        n.hidden = kinds.indexOf(kind.value) === -1;
      });
    };
    kind.addEventListener('change', sync);
    sync();
  }

  // Scroll reveal — staggered, and skipped entirely for reduced-motion users.
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var revealables = document.querySelectorAll('.reveal');
  if (revealables.length) {
    if (reduce || !('IntersectionObserver' in window)) {
      revealables.forEach(function (n) { n.classList.add('in'); });
    } else {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
        });
      }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
      revealables.forEach(function (n) { io.observe(n); });
    }
  }

  // Count figures up once they scroll into view.
  var counters = document.querySelectorAll('[data-count]');
  if (counters.length) {
    var run = function (el) {
      var target = parseFloat(el.getAttribute('data-count'));
      var dp = parseInt(el.getAttribute('data-dp') || '0', 10);
      var prefix = el.getAttribute('data-prefix') || '';
      var suffix = el.getAttribute('data-suffix') || '';
      var fmt = function (n) {
        return prefix + n.toFixed(dp).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + suffix;
      };
      if (reduce) { el.textContent = fmt(target); return; }
      var start = null, dur = 1100;
      var step = function (ts) {
        if (start === null) start = ts;
        var p = Math.min(1, (ts - start) / dur);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = fmt(target * eased);
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    };
    if (!('IntersectionObserver' in window)) {
      counters.forEach(run);
    } else {
      var co = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { run(en.target); co.unobserve(en.target); }
        });
      }, { threshold: 0.4 });
      counters.forEach(function (n) { co.observe(n); });
    }
  }

  // Product showcase tabs.
  var tabWrap = document.querySelector('[data-tabs]');
  if (tabWrap) {
    var buttons = tabWrap.querySelectorAll('[role=tab]');
    buttons.forEach(function (b) {
      b.addEventListener('click', function () {
        buttons.forEach(function (o) { o.setAttribute('aria-selected', 'false'); });
        b.setAttribute('aria-selected', 'true');
        document.querySelectorAll('[role=tabpanel]').forEach(function (p) {
          p.hidden = p.id !== b.getAttribute('aria-controls');
        });
      });
    });
  }

  // Range sliders that mirror their value into a text field.
  document.querySelectorAll('[data-mirror]').forEach(function (r) {
    var out = document.getElementById(r.getAttribute('data-mirror'));
    if (!out) return;
    var push = function () {
      out.value = r.value;
      out.dispatchEvent(new Event('input', { bubbles: true }));
    };
    r.addEventListener('input', push);
    out.addEventListener('input', function () {
      var v = parseFloat((out.value || '0').replace(/,/g, ''));
      if (!isNaN(v)) r.value = v;
    });
  });
})();

/* ── PBI Group landing: showcase tabs + loan calculator ─────────────── */
(function () {
  // Showcase tabs
  var tabWraps = document.querySelectorAll('.showcase[data-tabs]');
  tabWraps.forEach(function (wrap) {
    var tabs = wrap.querySelectorAll('[role=tab]');
    var panes = wrap.querySelectorAll('.showpane');
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) { t.setAttribute('aria-selected', 'false'); });
        tab.setAttribute('aria-selected', 'true');
        var key = tab.getAttribute('data-tab');
        panes.forEach(function (p) {
          p.classList.toggle('on', p.getAttribute('data-pane') === key);
        });
      });
    });
  });

  // Loan calculator
  var result = document.querySelector('.calc-result');
  if (!result) return;
  var rate = parseFloat(result.getAttribute('data-rate')) || 0;
  var monthsLabel = result.getAttribute('data-months-label') || 'months';
  var rngA = document.getElementById('rngAmount');
  var rngM = document.getElementById('rngMonths');
  var outA = document.getElementById('calcAmount');
  var outM = document.getElementById('calcMonths');
  var crMonthly = document.getElementById('crMonthly');
  var crTotal = document.getElementById('crTotal');
  var crInterest = document.getElementById('crInterest');

  // RUB-style formatting: "1 234,56 ₽"
  function money(v) {
    var neg = v < 0;
    v = Math.abs(v);
    var s = v.toFixed(2).replace('.', ',');
    s = s.replace(/\B(?=(\d{3})+(?!\d))/g, '\u00A0');
    return (neg ? '-' : '') + s + '\u00A0\u20BD';
  }

  function recalc() {
    var amount = parseFloat(rngA.value);
    var months = parseInt(rngM.value, 10);
    // flat annual rate, same as loan_quote() server-side
    var interest = amount * (rate / 100) * (months / 12);
    var total = amount + interest;
    var monthly = total / months;
    outA.textContent = money(amount);
    outM.textContent = months + ' ' + monthsLabel;
    crMonthly.textContent = money(monthly);
    crTotal.textContent = money(total);
    crInterest.textContent = money(interest);
  }
  if (rngA && rngM) {
    rngA.addEventListener('input', recalc);
    rngM.addEventListener('input', recalc);
    recalc();
  }
})();

/* ── PBI Group: loan calculator ─────────────────────────────────────────── */
(function () {
  var box = document.querySelector('.calc2');
  if (!box) return;
  var rate = parseFloat(box.getAttribute('data-rate')) || 0;
  var mlab = box.getAttribute('data-mlabel') || 'mo';
  var rA = document.getElementById('cRngA');
  var rM = document.getElementById('cRngM');
  var oA = document.getElementById('cAmount');
  var oM = document.getElementById('cMonths');
  var monthly = document.getElementById('cMonthly');
  var total = document.getElementById('cTotal');
  var interest = document.getElementById('cInterest');
  if (!rA || !rM) return;

  function money(v) {
    var neg = v < 0; v = Math.abs(v);
    var s = v.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '\u00A0');
    return (neg ? '-' : '') + s + '\u00A0\u20BD';
  }
  function recalc() {
    var amt = parseFloat(rA.value), mo = parseInt(rM.value, 10);
    var intr = amt * (rate / 100) * (mo / 12);
    var tot = amt + intr;
    oA.textContent = money(amt);
    oM.textContent = mo + ' ' + mlab;
    monthly.textContent = money(tot / mo);
    total.textContent = money(tot);
    interest.textContent = money(intr);
  }
  rA.addEventListener('input', recalc);
  rM.addEventListener('input', recalc);
  recalc();
})();

/* reveal-on-scroll for the bright theme */
(function () {
  var els = document.querySelectorAll('.reveal');
  if (!els.length || !('IntersectionObserver' in window)) {
    els.forEach(function (e) { e.classList.add('in'); }); return;
  }
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); } });
  }, { threshold: 0.12 });
  els.forEach(function (e) { io.observe(e); });
})();
