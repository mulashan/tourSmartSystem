<div class="item-picker" data-endpoint="{{ $pickerEndpoint }}">
    <label class="text-danger">*</label>
    <select class="form-select mb-2 js-picker-category">
        <option value="">Item Category</option>
        @foreach($itemCategories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>

    <input type="text" class="form-control mb-2 js-picker-search" placeholder="Item Name">

    <table class="table table-sm table-hover">
        <thead>
            <tr><th>Item Name</th><th>Balance</th></tr>
        </thead>
        <tbody class="js-picker-results">
            <tr><td colspan="2" class="text-muted text-center">Select a category or search to see items</td></tr>
        </tbody>
    </table>
</div>