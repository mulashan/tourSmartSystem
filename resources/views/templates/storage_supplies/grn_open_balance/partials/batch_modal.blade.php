<div class="modal fade" id="batchModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Batches</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-12"><label class="form-label">Item Name</label><input type="text" class="form-control" id="batchItemName" disabled></div>
                </div>

                <h6>Batch Details</h6>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Batch Number *</label><input type="text" id="batchNumber" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Units *</label><input type="number" min="1" id="batchUnits" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Items per Unit *</label><input type="number" min="1" id="batchItemsPerUnit" class="form-control" value="1"></div>

                    <div class="col-md-4"><label class="form-label">Quantity</label><input type="text" id="batchQuantityPreview" class="form-control" disabled value="0"></div>
                    <div class="col-md-4"><label class="form-label">Buying Price *</label><input type="number" min="0" step="0.01" id="batchBuyingPrice" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Manufacture Date *</label><input type="date" id="batchManufactureDate" class="form-control"></div>

                    <div class="col-md-6"><label class="form-label">Expiry Date *</label><input type="date" id="batchExpiryDate" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label">Received Date *</label><input type="date" id="batchReceivedDate" class="form-control"></div>
                </div>

                <div class="text-danger small mt-2" id="batchFormError"></div>

                <div class="settings-panel-head mt-4"><h6>Batch List</h6><button type="button" class="btn btn-sm btn-info text-white" id="js-add-batch-row">Add</button></div>

                <div class="table-responsive">
                    <table class="table table-sm" id="batchListTable" style="min-width:900px;">
                        <thead>
                            <tr>
                                <th>S/N</th><th>Batch No.</th><th>Units</th><th>Items per Unit</th><th>Quantity</th>
                                <th>Buying Price</th><th>Manufacture Date</th><th>Expiry Date</th><th>Received Date</th><th>Action</th>
                            </tr>
                        </thead>
                        <tbody><tr class="js-batch-empty-row"><td colspan="10" class="text-center text-muted">No data available</td></tr></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>