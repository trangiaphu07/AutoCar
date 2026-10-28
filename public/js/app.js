/* AutoShop – JavaScript thuần + Bootstrap 5 (bootstrap.bundle.min.js), không cần build */
(function () {
    const root = document.documentElement;

    /* ---------- Chế độ sáng / tối ---------- */
    function setTheme(t) {
        root.setAttribute('data-theme', t);
        root.setAttribute('data-bs-theme', t); // để các thành phần Bootstrap (modal, toast…) đổi màu theo
        try { localStorage.setItem('autoshop-theme', t); } catch (e) {}
        document.querySelectorAll('[data-theme-label]').forEach(el => el.textContent = t === 'dark' ? 'Sáng' : 'Tối');
    }
    document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
        btn.addEventListener('click', () => setTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'));
    });
    setTheme(root.getAttribute('data-theme') || 'light');

    /* ---------- Dropdown ---------- */
    document.querySelectorAll('[data-dropdown]').forEach(dd => {
        dd.querySelector('[data-dropdown-btn]').addEventListener('click', e => {
            e.stopPropagation();
            document.querySelectorAll('[data-dropdown].open').forEach(o => o !== dd && o.classList.remove('open'));
            dd.classList.toggle('open');
        });
    });
    document.addEventListener('click', () => document.querySelectorAll('[data-dropdown].open').forEach(o => o.classList.remove('open')));

    /* ---------- Thông báo flash: Bootstrap Toast (tự ẩn sau vài giây) ---------- */
    if (window.bootstrap) {
        document.querySelectorAll('.toast').forEach(t => bootstrap.Toast.getOrCreateInstance(t).show());
    }

    /* ---------- Hero slider ---------- */
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dots button');
    if (slides.length > 1) {
        let idx = 0, timer;
        const go = n => {
            slides[idx].classList.remove('active'); dots[idx]?.classList.remove('active');
            idx = (n + slides.length) % slides.length;
            slides[idx].classList.add('active'); dots[idx]?.classList.add('active');
        };
        const start = () => { timer = setInterval(() => go(idx + 1), 5000); };
        const jump = n => { clearInterval(timer); go(n); start(); };
        dots.forEach((d, i) => d.addEventListener('click', () => jump(i)));
        document.querySelector('[data-hero-prev]')?.addEventListener('click', () => jump(idx - 1));
        document.querySelector('[data-hero-next]')?.addEventListener('click', () => jump(idx + 1));
        start();
    }

    /* ---------- Tabs ---------- */
    document.querySelectorAll('[data-tabs]').forEach(wrap => {
        const btns = wrap.querySelectorAll('[data-tab]');
        btns.forEach(b => b.addEventListener('click', () => {
            btns.forEach(x => x.classList.toggle('active', x === b));
            wrap.querySelectorAll('.tab-panel').forEach(p => p.classList.toggle('active', p.id === b.dataset.tab));
        }));
    });

    /* Nút "Lái thử" trên thẻ xe dẫn tới #lai-thu: mở sẵn tab đăng ký lái thử */
    if (location.hash === '#lai-thu') {
        const driveTab = document.querySelector('[data-tab="tab-drive"]');
        if (driveTab) { driveTab.click(); driveTab.scrollIntoView({ block: 'center' }); }
    }

    /* ---------- Ô tìm kiếm & menu trên điện thoại ---------- */
    document.querySelectorAll('[data-search-toggle]').forEach(btn => btn.addEventListener('click', () => {
        const panel = document.getElementById('search-panel');
        panel.hidden = !panel.hidden;
        btn.setAttribute('aria-expanded', String(!panel.hidden));
        if (!panel.hidden) panel.querySelector('input')?.focus();
    }));
    document.querySelectorAll('[data-nav-toggle]').forEach(btn => btn.addEventListener('click', () => {
        const nav = document.getElementById('main-nav');
        nav.classList.toggle('open');
        btn.setAttribute('aria-expanded', String(nav.classList.contains('open')));
    }));

    /* ---------- Hiện / ẩn mật khẩu ---------- */
    document.querySelectorAll('[data-pw-toggle]').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('input');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.textContent = show ? 'Ẩn' : 'Hiện';
        });
    });

    /* ---------- Bộ lọc trên mobile ---------- */
    document.querySelectorAll('[data-filter-toggle]').forEach(b => b.addEventListener('click', () => {
        document.querySelector('.filters')?.classList.toggle('open');
    }));

    /* ---------- Tự gửi form khi đổi select ---------- */
    document.querySelectorAll('[data-autosubmit]').forEach(el => el.addEventListener('change', () => el.form.submit()));

    /* ---------- Hỏi lại trước khi thao tác nguy hiểm: Bootstrap Modal ---------- */
    const confirmEl = document.getElementById('confirmModal');
    const confirmModal = (window.bootstrap && confirmEl) ? bootstrap.Modal.getOrCreateInstance(confirmEl) : null;
    let pendingForm = null;
    document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
        e.preventDefault();
        if (!confirmModal) { if (window.confirm(f.dataset.confirm)) f.submit(); return; }
        pendingForm = f;
        confirmEl.querySelector('[data-confirm-text]').textContent = f.dataset.confirm;
        confirmModal.show();
    }));
    confirmEl?.querySelector('[data-confirm-ok]')?.addEventListener('click', () => {
        if (pendingForm) { const f = pendingForm; pendingForm = null; confirmModal.hide(); f.submit(); }
    });

    /* ---------- Mở lại modal Bootstrap còn lỗi validation sau khi submit ---------- */
    if (window.bootstrap) {
        document.querySelectorAll('.modal[data-open-on-load]').forEach(m => bootstrap.Modal.getOrCreateInstance(m).show());
    }

    /* ---------- Công cụ tính trả góp ---------- */
    const loan = document.querySelector('[data-loan]');
    if (loan) {
        const price = +loan.dataset.price;
        const fmt = n => Math.round(n).toLocaleString('vi-VN') + ' ₫';
        const $ = s => loan.querySelector(s);
        const calc = () => {
            const pct = +$('[name=loan_pct]').value, months = +$('[name=loan_months]').value, rate = +$('[name=loan_rate]').value;
            $('[data-out=pct]').textContent = pct + '%';
            $('[data-out=months]').textContent = months + ' tháng';
            const principal = price * pct / 100;
            const r = rate / 100 / 12;
            const monthly = r ? principal * r / (1 - Math.pow(1 + r, -months)) : principal / months;
            $('[data-out=principal]').textContent = fmt(principal);
            $('[data-out=upfront]').textContent = fmt(price - principal);
            $('[data-out=monthly]').textContent = fmt(monthly);
            $('[data-out=interest]').textContent = fmt(monthly * months - principal);
        };
        loan.querySelectorAll('input').forEach(i => i.addEventListener('input', calc));
        calc();
    }

    /* ---------- Admin: đổi dòng xe -> tải lại ô thông số ---------- */
    const catSelect = document.querySelector('[data-attr-source]');
    if (catSelect) {
        catSelect.addEventListener('change', async () => {
            const box = document.querySelector('[data-attr-box]');
            box.style.opacity = '.5';
            const url = catSelect.dataset.attrSource.replace('__ID__', catSelect.value);
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            box.innerHTML = await res.text();
            box.style.opacity = '1';
        });
    }

    /* ---------- Xem trước màu xe trong form admin ---------- */
    const hex = document.querySelector('[name=color_hex]');
    if (hex) hex.addEventListener('input', () => {
        document.querySelectorAll('[data-car-paint]').forEach(p => p.setAttribute('fill', hex.value));
    });
})();
