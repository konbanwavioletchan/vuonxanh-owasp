// Xac nhan truoc khi mua cay
document.addEventListener('submit', function (e) {
    var f = e.target;
    if (f.action && f.action.indexOf('buy.php') !== -1) {
        if (!confirm('Bạn chắc chắn muốn mua cây này?')) {
            e.preventDefault();
        }
    }
});

// Chuyen doi giao dien Sang / Toi (luu trong localStorage)
(function () {
    var root = document.documentElement;
    var btn = document.getElementById('theme-toggle');
    function current() {
        return root.getAttribute('data-theme') ||
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    }
    function refreshIcon() { if (btn) btn.textContent = current() === 'dark' ? '☀️' : '🌙'; }
    refreshIcon();
    if (btn) {
        btn.addEventListener('click', function () {
            var next = current() === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
            refreshIcon();
        });
    }
})();

// Bo dem so lieu (chay khi cuon toi)
(function () {
    var nums = document.querySelectorAll('.stat-num[data-count]');
    if (!nums.length) return;
    function run(el) {
        var target = parseInt(el.getAttribute('data-count'), 10) || 0;
        var suffix = el.getAttribute('data-suffix') || '';
        var start = null, dur = 1400;
        function step(ts) {
            if (start === null) start = ts;
            var p = Math.min((ts - start) / dur, 1);
            var val = Math.floor((1 - Math.pow(1 - p, 3)) * target);
            el.textContent = val.toLocaleString('vi-VN') + suffix;
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { run(en.target); io.unobserve(en.target); }
            });
        }, { threshold: 0.4 });
        nums.forEach(function (n) { io.observe(n); });
    } else {
        nums.forEach(run);
    }
})();

// Form demo (dang ky nhan tin / lien he): khong gui di, hien thong bao
(function () {
    document.querySelectorAll('form[data-demo]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            e.preventDefault();
            var msg = document.createElement('span');
            msg.className = 'form-ok';
            msg.textContent = '🌿 Cảm ơn bạn! Vườn Xanh sẽ liên hệ sớm.';
            var btn = f.querySelector('button');
            if (btn) btn.disabled = true;
            f.appendChild(msg);
        });
    });
})();
