/* deliBEE UI/UX sampler — theme toggle only.
   Mirrors the app's `.dark` class switch; no framework needed. */

(function () {
    var root = document.documentElement;
    var button = document.getElementById('theme-toggle');

    if (!button) {
        return;
    }

    var label = button.querySelector('[data-label]');
    var moon = button.querySelector('[data-icon-moon]');
    var sun = button.querySelector('[data-icon-sun]');

    try {
        if (window.localStorage.getItem('delibee-kit-theme') === 'dark') {
            root.classList.add('dark');
        }
    } catch (error) {
        /* localStorage can be unavailable in private file contexts; ignore. */
    }

    function sync() {
        var isDark = root.classList.contains('dark');

        button.setAttribute('aria-pressed', String(isDark));
        label.textContent = isDark ? 'Light mode' : 'Dark mode';
        moon.hidden = isDark;
        sun.hidden = !isDark;
    }

    button.addEventListener('click', function () {
        root.classList.toggle('dark');

        try {
            window.localStorage.setItem(
                'delibee-kit-theme',
                root.classList.contains('dark') ? 'dark' : 'light'
            );
        } catch (error) {
            /* Same as above — the toggle still works for this visit. */
        }

        sync();
    });

    sync();
})();
