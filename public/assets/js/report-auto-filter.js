/*(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        const $form = $('#reportFilterForm');
        if (! $form.length) return;

        const url = $form.data('endpoint');
        const $results = $('#reportResults');
        let debounce;

        function submit() {
            $results.html('<div class="text-muted p-4">Loading...</div>');
            $.get(url, $form.serialize())
                .done(html => $results.html(html))
                .fail(() => $results.html('<div class="text-danger p-4">Failed to load report.</div>'));
        }

        $form.on('change', 'select, input[type=date]', submit);
        $form.on('input', 'input[type=text]', function () {
            clearTimeout(debounce);
            debounce = setTimeout(submit, 400);
        });

        submit();
    });
});*/

(function whenJQueryReady(fn) {
    if (typeof $ !== 'undefined') { fn(); } else { setTimeout(function () { whenJQueryReady(fn); }, 30); }
})(function () {
    $(function () {
        const $form = $('#reportFilterForm');
        if (! $form.length) return;

        const url = $form.data('endpoint');
        const $results = $('#reportResults');
        let debounce;

        function submit() {
            $results.html('<div class="text-muted p-4">Loading...</div>');
            $.get(url, $form.serialize())
                .done(html => {
                    $results.html(html);
                    if (window.DataTableInit) window.DataTableInit.initAll($results[0]);
                })
                .fail(() => $results.html('<div class="text-danger p-4">Failed to load report.</div>'));
        }

        $form.on('change', 'select, input[type=date]', submit);
        $form.on('input', 'input[type=text]', function () {
            clearTimeout(debounce);
            debounce = setTimeout(submit, 400);
        });

        submit();
    });
});