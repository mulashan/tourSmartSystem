$(function () {
    $('.item-picker').each(function () {
        const $picker = $(this);
        const endpoint = $picker.data('endpoint');
        let searchTimer;

        function search() {
            const categoryId = $picker.find('.js-picker-category').val();
            const term = $picker.find('.js-picker-search').val();

            if (! categoryId && ! term) {
                $picker.find('.js-picker-results').html('<tr><td colspan="2" class="text-muted text-center">Select a category or search to see items</td></tr>');
                return;
            }

            $.get(endpoint, { category_id: categoryId, search: term }).done(items => {
                const $tbody = $picker.find('.js-picker-results').empty();

                if (! items.length) {
                    $tbody.html('<tr><td colspan="2" class="text-muted text-center">No items found</td></tr>');
                    return;
                }

                items.forEach(item => {
                    $tbody.append(`
                        <tr class="js-picker-row" style="cursor:pointer" data-id="${item.id}" data-name="${item.name}" data-uom="${item.uom}">
                            <td><span class="form-check-input-visual"><input type="radio" name="picker-item-${$picker.index()}" style="pointer-events:none"></span> ${item.name}</td>
                            <td>${item.balance}</td>
                        </tr>
                    `);
                });
            });
        }

        $picker.on('change', '.js-picker-category', search);
        $picker.on('input', '.js-picker-search', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(search, 300);
        });

        // Clicking the row itself (not a separate button) selects it and fires immediately.
        $picker.on('click', '.js-picker-row', function () {
            $picker.find('.js-picker-row input[type=radio]').prop('checked', false);
            $(this).find('input[type=radio]').prop('checked', true);

            $picker[0].dispatchEvent(new CustomEvent('item-picker:add', {
                detail: { id: $(this).data('id'), name: $(this).data('name'), uom: $(this).data('uom') },
                bubbles: true,
            }));
        });
    });
});