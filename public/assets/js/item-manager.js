$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const wrapper = $('#item-table-wrapper');
    const itemModal = new bootstrap.Modal(document.getElementById('itemModal'));
    const categoryModal = new bootstrap.Modal(document.getElementById('categoryQuickAddModal'));

    function loadItems(search) {
        $.get('/storage-supplies/items/list', {
            search: search || ''
        })
        .done(function (html) {
            wrapper.html(html);
            if (window.DataTableInit) {
                window.DataTableInit.initAll(wrapper[0]);
            }
        })
        .fail(function (xhr) {
            console.error('Failed to load items:', xhr);
            wrapper.html(
                '<div class="text-danger p-4">' +
                'Failed to load items.' +
                '</div>'
            );
        });
    }

    loadItems();

    let searchTimer;
    $('#js-item-search').on('input', function () {
        const term = $(this).val();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadItems(term), 300);
    });

    $('#js-add-item').on('click', function () {
        $('#itemModalLabel').text('Add Item');
        $('#itemForm')[0].reset();
        $('#itemId, #itemFormError').val('').text('');
        $('#itemStatus').val('active');
        itemModal.show();
    });

    wrapper.on('click', '.js-edit-item', function () {
        const d = $(this).data();
        $('#itemModalLabel').text('Edit Item');
        $('#itemId').val(d.id);
        $('#productName').val(d.productName);
        $('#productCodePrefix').val(d.productCodePrefix);
        $('#productCode').val(d.productCode);
        $('#itemCategory').val(d.itemCategoryId);
        $('#unitOfMeasure').val(d.unitOfMeasureId);
        $('#itemStatus').val(d.status);
        $('#reorderLevel').val(d.reorderLevel);
        $('#minimumReorderLevel').val(d.minimumReorderLevel);
        $('#maximumReorderLevel').val(d.maximumReorderLevel);
        $('#itemFormError').text('');
        itemModal.show();
    });

    $('#itemForm').on('submit', function (e) {
        e.preventDefault();

        const id = $('#itemId').val();
        const url = id ? `/storage-supplies/items/${id}` : '/storage-supplies/items';

        $.ajax({
            url,
            method: id ? 'PUT' : 'POST',
            data: {
                product_name: $('#productName').val(),
                product_code_prefix: $('#productCodePrefix').val(),
                product_code: $('#productCode').val(),
                item_category_id: $('#itemCategory').val(),
                unit_of_measure_id: $('#unitOfMeasure').val(),
                status: $('#itemStatus').val(),
                reorder_level: $('#reorderLevel').val(),
                minimum_reorder_level: $('#minimumReorderLevel').val(),
                maximum_reorder_level: $('#maximumReorderLevel').val(),
            },
        })
            .done(() => { itemModal.hide(); loadItems($('#js-item-search').val()); })
            .fail(xhr => $('#itemFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
    });

    wrapper.on('click', '.js-delete-item', function () {
        if (! confirm('Delete this item?')) return;

        $.ajax({ url: `/storage-supplies/items/${$(this).data('id')}`, method: 'DELETE' })
            .done(() => loadItems($('#js-item-search').val()))
            .fail(() => alert('Failed to delete item.'));
    });

    // Quick-add Item Category — reuses the existing Other Settings lookup endpoint.
    $('#js-add-category').on('click', function () {
        $('#categoryQuickAddForm')[0].reset();
        $('#categoryFormError').text('');
        categoryModal.show();
    });

    $('#categoryQuickAddForm').on('submit', function (e) {
        e.preventDefault();

        $.post('/settings/other_settings/item-categories', {
            name: $('#categoryName').val(),
            code: $('#categoryCode').val(),
        })
            .done(response => {
                categoryModal.hide();
                refreshCategoryDropdown(response.item.id);
            })
            .fail(xhr => $('#categoryFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
    });

    function refreshCategoryDropdown(selectId) {
        $.get('/storage-supplies/items/categories').done(categories => {
            const select = $('#itemCategory');
            select.empty().append('<option value="">Select Category</option>');
            categories.forEach(c => select.append(`<option value="${c.id}">${c.name}</option>`));
            if (selectId) select.val(selectId);
        });
    }
});