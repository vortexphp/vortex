(function () {
    var input = document.getElementById('docs-nav-filter');
    if (!input || input.dataset.bound === '1') {
        return;
    }
    input.dataset.bound = '1';

    function applyFilter() {
        var q = (input.value || '').trim().toLowerCase();
        var sections = document.querySelectorAll('[data-docs-nav-section]');

        if (q === '') {
            document.querySelectorAll('[data-docs-nav-item]').forEach(function (li) {
                li.classList.remove('hidden');
            });
            sections.forEach(function (sec) {
                sec.classList.remove('hidden');
            });
            return;
        }

        sections.forEach(function (sec) {
            var any = false;
            sec.querySelectorAll('[data-docs-nav-item]').forEach(function (li) {
                var t = (li.getAttribute('data-docs-nav-text') || '').toLowerCase();
                var match = t.indexOf(q) !== -1;
                li.classList.toggle('hidden', !match);
                if (match) {
                    any = true;
                }
            });
            sec.classList.toggle('hidden', !any);
        });
    }

    input.addEventListener('input', applyFilter);
    input.addEventListener('search', applyFilter);
})();
