/*
 * Variant 3 (SSR + Declarative Shadow DOM) client enhancer.
 * Deliberately does NOT rebuild anything — the content already exists because
 * the browser's HTML parser attached the shadow root before this script ran.
 * This only proves the "enhance in place" model: if this script never loads,
 * the cards are still fully there (test it with JS disabled).
 */
(function () {
    var host = document.getElementById('host');
    if (!host || !host.shadowRoot) {
        // If this fires, declarative shadow DOM didn't attach — browser support
        // issue or the <template> wasn't parsed as part of the original HTML.
        console.warn('[dsd-enhance] no existing shadow root found — declarative attach did not happen');
        return;
    }

    var root = host.shadowRoot;
    var tag = root.getElementById('enh-tag');
    if (tag) tag.style.display = 'inline-block';

    // Prove "enhance, don't rebuild": add interactivity to the EXISTING cards,
    // don't touch their markup.
    root.querySelectorAll('.card').forEach(function (card) {
        card.addEventListener('mouseenter', function () {
            card.style.boxShadow = '0 4px 14px rgba(0,0,0,.12)';
        });
        card.addEventListener('mouseleave', function () {
            card.style.boxShadow = '';
        });
    });
})();
