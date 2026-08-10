$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    const panel = $('#settings-panel');
    const modal = new bootstrap.Modal(document.getElementById('lookupModal'));

    function loadList(key, search) {
        $.get(`/settings/other_settings/${key}/list`, { search: search || '' })
            .done(html => panel.html(html))
            .fail(() => panel.html('<div class="text-danger p-4">Failed to load this list.</div>'));
    }

    $('.settings-nav-link').on('click', function (e) {
        e.preventDefault();
        if ($(this).hasClass('disabled')) return;

        $('.settings-nav-link').removeClass('active');
        $(this).addClass('active');

        const key = $(this).data('key');
        const kind = $(this).data('kind');

        if (kind === 'lookup') {
            loadList(key);
        } else if (key === 'branch-departments') {
            loadDepartments();
        } else if (key === 'subdepartments') {
            loadSubdepartments();
        } else if (key === 'suppliers') {
            loadSuppliers();
        } else if(key === 'session-timeout'){
            loadBranchSessions();
        } else {
            panel.html('<div class="text-muted p-4">This section is coming soon.</div>');
        }
    });

    // Auto-load the first category on page load
    $('.settings-nav-link[data-kind="lookup"]').first().trigger('click');

    let searchTimer;
    panel.on('input', '.js-lookup-search', function () {
        const key = $(this).data('key');
        const term = $(this).val();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadList(key, term), 300);
    });

    panel.on('click', '.js-add-lookup', function () {
        $('#lookupModalLabel').text('Add Item');
        $('#lookupForm')[0].reset();
        $('#lookupId, #lookupFormError').val('').text('');
        $('#lookupKey').val($(this).data('key'));
        modal.show();
    });

    panel.on('click', '.js-edit-lookup', function () {
        $('#lookupModalLabel').text('Edit Item');
        $('#lookupId').val($(this).data('id'));
        $('#lookupKey').val($(this).data('key'));
        $('#lookupName').val($(this).data('name'));
        $('#lookupCode').val($(this).data('code'));
        $('#lookupDescription').val($(this).data('description'));
        $('#lookupFormError').text('');
        modal.show();
    });

    $('#lookupForm').on('submit', function (e) {
        e.preventDefault();

        const id = $('#lookupId').val();
        const key = $('#lookupKey').val();
        const url = id ? `/settings/other_settings/${key}/${id}` : `/settings/other_settings/${key}`;

        $.ajax({
            url,
            method: id ? 'PUT' : 'POST',
            data: {
                name: $('#lookupName').val(),
                code: $('#lookupCode').val(),
                description: $('#lookupDescription').val(),
            },
        })
            .done(() => { modal.hide(); loadList(key); })
            .fail(xhr => $('#lookupFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
    });

    panel.on('click', '.js-delete-lookup', function () {
        if (! confirm('Delete this item?')) return;

        const key = $(this).data('key');

        $.ajax({ url: `/settings/other_settings/${key}/${$(this).data('id')}`, method: 'DELETE' })
            .done(() => loadList(key))
            .fail(() => alert('Failed to delete item.'));
    });

    const departmentModal = new bootstrap.Modal(document.getElementById('departmentModal'));

    function loadDepartments(search) {
        $.get('/settings/other_settings/branch-departments/list', { search: search || '' })
            .done(html => panel.html(html))
            .fail(() => panel.html('<div class="text-danger p-4">Failed to load departments.</div>'));
    }


    panel.on('input', '.js-department-search', function () {
        const term = $(this).val();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadDepartments(term), 300);
    });

    panel.on('click', '.js-add-department', function () {
        $('#departmentModalLabel').text('Add Department');
        $('#departmentForm')[0].reset();
        $('#departmentId, #departmentFormError').val('').text('');
        departmentModal.show();
    });

    panel.on('click', '.js-edit-department', function () {
        $('#departmentModalLabel').text('Edit Department');
        $('#departmentId').val($(this).data('id'));
        $('#departmentName').val($(this).data('name'));
        $('#departmentBranch').val($(this).data('branch-id'));
        $('#departmentNature').val($(this).data('nature-id'));
        $('#departmentFormError').text('');
        departmentModal.show();
    });

    $('#departmentForm').on('submit', function (e) {
        e.preventDefault();

        const id = $('#departmentId').val();
        const url = id ? `/settings/other_settings/branch-departments/${id}` : '/settings/other_settings/branch-departments';

        $.ajax({
            url,
            method: id ? 'PUT' : 'POST',
            data: {
                Department_Name: $('#departmentName').val(),
                Branch_ID: $('#departmentBranch').val(),
                department_nature_id: $('#departmentNature').val(),
            },
        })
            .done(() => { departmentModal.hide(); loadDepartments(); })
            .fail(xhr => $('#departmentFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
    });

    panel.on('click', '.js-delete-department', function () {
        if (! confirm('Delete this department?')) return;

        $.ajax({ url: `/settings/other_settings/branch-departments/${$(this).data('id')}`, method: 'DELETE' })
            .done(() => loadDepartments())
            .fail(() => alert('Failed to delete department.'));
    });

    const subdepartmentModal = new bootstrap.Modal(document.getElementById('subdepartmentModal'));

    function loadSubdepartments(search) {
        $.get('/settings/other_settings/subdepartments/list', { search: search || '' })
            .done(html => panel.html(html))
            .fail(() => panel.html('<div class="text-danger p-4">Failed to load subdepartments.</div>'));
    }

    panel.on('input', '.js-subdepartment-search', function () {
        const term = $(this).val();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadSubdepartments(term), 300);
    });

    panel.on('click', '.js-add-subdepartment', function () {
        $('#subdepartmentModalLabel').text('Add Subdepartment');
        $('#subdepartmentForm')[0].reset();
        $('#subdepartmentId, #subdepartmentFormError').val('').text('');
        $('#subdepartmentNaturePreview').val('');
        subdepartmentModal.show();
    });

    panel.on('click', '.js-edit-subdepartment', function () {
        $('#subdepartmentModalLabel').text('Edit Subdepartment');
        $('#subdepartmentId').val($(this).data('id'));
        $('#subdepartmentName').val($(this).data('name'));
        $('#subdepartmentDepartment').val($(this).data('department-id')).trigger('change');
        $('#subdepartmentFormError').text('');
        subdepartmentModal.show();
    });

    $('#subdepartmentDepartment').on('change', function () {
        $('#subdepartmentNaturePreview').val($(this).find(':selected').data('nature') || '');
    });

    $('#subdepartmentForm').on('submit', function (e) {
        e.preventDefault();

        const id = $('#subdepartmentId').val();
        const url = id ? `/settings/other_settings/subdepartments/${id}` : '/settings/other_settings/subdepartments';

        $.ajax({
            url,
            method: id ? 'PUT' : 'POST',
            data: {
                Subdepartment_Name: $('#subdepartmentName').val(),
                Department_ID: $('#subdepartmentDepartment').val(),
            },
        })
            .done(() => { subdepartmentModal.hide(); loadSubdepartments(); })
            .fail(xhr => $('#subdepartmentFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
    });

    panel.on('click', '.js-delete-subdepartment', function () {
        if (! confirm('Delete this subdepartment?')) return;

        $.ajax({ url: `/settings/other_settings/subdepartments/${$(this).data('id')}`, method: 'DELETE' })
            .done(() => loadSubdepartments())
            .fail(() => alert('Failed to delete subdepartment.'));
    });

    const supplierModal = new bootstrap.Modal(document.getElementById('supplierModal'));
    function loadSuppliers(search) {
        $.get('/settings/other_settings/suppliers/list', { search: search || '' })
            .done(html => panel.html(html))
            .fail(() => panel.html('<div class="text-danger p-4">Failed to load suppliers.</div>'));
    }

    panel.on('input', '.js-supplier-search', function () {
        const term = $(this).val();
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadSuppliers(term), 300);
    });

    panel.on('click', '.js-add-supplier', function () {
        $('#supplierModalLabel').text('Add Suppliers');
        $('#supplierForm')[0].reset();
        $('#supplierId, #supplierFormError').val('').text('');
        supplierModal.show();
    });

    panel.on('click', '.js-edit-supplier', function () {
        const d = $(this).data();
        $('#supplierModalLabel').text('Edit Supplier');
        $('#supplierId').val(d.id);
        $('#supplierName').val(d.supplierName);
        $('#supplierAddress').val(d.supplierAddress);
        $('#postalAddress').val(d.postalAddress);
        $('#contactPersonName').val(d.contactPersonName);
        $('#contactPersonMobile').val(d.contactPersonMobile);
        $('#contactPersonEmail').val(d.contactPersonEmail);
        $('#telephone').val(d.telephone);
        $('#fax').val(d.fax);
        $('#physicalAddress').val(d.physicalAddress);
        $('#supplierFormError').text('');
        supplierModal.show();
    });

    $('#supplierForm').on('submit', function (e) {
        e.preventDefault();

        const id = $('#supplierId').val();
        const url = id ? `/settings/other_settings/suppliers/${id}` : '/settings/other_settings/suppliers';

        $.ajax({
            url,
            method: id ? 'PUT' : 'POST',
            data: {
                supplier_name: $('#supplierName').val(),
                supplier_address: $('#supplierAddress').val(),
                postal_address: $('#postalAddress').val(),
                contact_person_name: $('#contactPersonName').val(),
                contact_person_mobile: $('#contactPersonMobile').val(),
                contact_person_email: $('#contactPersonEmail').val(),
                telephone: $('#telephone').val(),
                fax: $('#fax').val(),
                physical_address: $('#physicalAddress').val(),
            },
        })
            .done(() => { supplierModal.hide(); loadSuppliers(); })
            .fail(xhr => $('#supplierFormError').text(xhr.responseJSON?.message || 'Something went wrong.'));
    });

    panel.on('click', '.js-delete-supplier', function () {
        if (! confirm('Delete this supplier?')) return;

        $.ajax({ url: `/settings/other_settings/suppliers/${$(this).data('id')}`, method: 'DELETE' })
            .done(() => loadSuppliers())
            .fail(() => alert('Failed to delete supplier.'));
    });

    function loadBranchSessions() {
    $.get('/settings/other_settings/session-timeout/list')
        .done(function (html) {
            panel.html(html);
        })
        .fail(function () {
            panel.html(
                '<div class="text-danger p-4">Failed to load branches.</div>'
            );
        });
}
});