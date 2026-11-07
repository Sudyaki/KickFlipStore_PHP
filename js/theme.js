/* Dark mode: follows the device setting the first time, then remembers the visitor's choice. */
(function () {
    var KEY = 'theme', root = document.documentElement;
    function preferred() {
        try { var s = localStorage.getItem(KEY); if (s === 'dark' || s === 'light') return s; } catch (e) {}
        return (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
    }
    function apply(t) {
        root.setAttribute('data-theme', t);
        var btns = document.querySelectorAll('.theme-btn');
        for (var i = 0; i < btns.length; i++) {
            btns[i].textContent = t === 'dark' ? '☀️ Light' : '🌙 Dark';
            btns[i].setAttribute('aria-label', t === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        }
    }
    apply(preferred());                       // runs in <head>, so there is no white flash
    document.addEventListener('DOMContentLoaded', function () {
        apply(root.getAttribute('data-theme'));
        var btns = document.querySelectorAll('.theme-btn');
        for (var i = 0; i < btns.length; i++) {
            btns[i].addEventListener('click', function () {
                var t = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                try { localStorage.setItem(KEY, t); } catch (e) {}
                apply(t);
            });
        }
    });
})();
