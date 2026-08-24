/*
 * Variant 1 (CSR) client renderer.
 * This is the step that, today, happens for every FalcoSense search/category
 * result: fetch JSON, then build real HTML from it, in the browser.
 * A crawler that looks at the page before this runs sees an empty <div id="grid">.
 */
(function () {
    var cardCss = document.createElement('style');
    cardCss.textContent =
        '.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px}' +
        '.card{display:block;text-decoration:none;color:#111;border:1px solid #eee;border-radius:8px;overflow:hidden;font-family:system-ui,sans-serif}' +
        '.card-img{aspect-ratio:1;background:#f5f5f5;overflow:hidden}' +
        '.card-img img{width:100%;height:100%;object-fit:cover;display:block}' +
        '.card-name{padding:8px 10px 2px;font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}' +
        '.card-price{padding:0 10px 10px;font-size:13px;color:#555}';
    document.head.appendChild(cardCss);

    function esc(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function renderCard(p) {
        return '<a class="card" href="' + esc(p.url) + '">' +
            '<div class="card-img"><img src="' + esc(p.image) + '" alt="' + esc(p.name) + '" loading="lazy"></div>' +
            '<div class="card-name">' + esc(p.name) + '</div>' +
            '<div class="card-price">$' + Number(p.price).toFixed(2) + '</div>' +
            '</a>';
    }

    var fetchStart = performance.now();

    fetch('/shadowbench/index/data')
        .then(function (r) { return r.json(); })
        .then(function (data) {
            var fetchMs = Math.round(performance.now() - fetchStart);
            var grid = document.getElementById('grid');
            grid.innerHTML = (data.products || []).map(renderCard).join('');
            var totalMs = Math.round(performance.now() - window.__benchStart);
            document.getElementById('timing-banner').textContent =
                'CSR — network+JSON fetch: ' + fetchMs + 'ms, total (page-start to cards-visible): ' + totalMs + 'ms, server JSON build: ' + data.server_fetch_ms + 'ms';
        })
        .catch(function (e) {
            document.getElementById('timing-banner').textContent = 'CSR — fetch failed: ' + e;
        });
})();
