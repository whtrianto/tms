<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        .section-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #007bff;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.25rem;
        }
        .form-group label.label-required::after {
            content: " *";
            color: red;
        }
        .form-control[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
        .info-display {
            padding: 0.5rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            min-height: 38px;
            display: flex;
            align-items: center;
        }
        .table td, .table th {
            color: #000 !important;
            padding: 0.35rem 0.4rem !important;
            font-size: 0.85rem;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 4px;
            flex-wrap: wrap;
        }
        .sub-section-title {
            font-size: 1rem;
            font-weight: bold;
            color: #495057;
            margin-top: 2rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body id="page-top">
<?= isset($loading) ? $loading : ''; ?>
<div id="wrapper">
    <?= isset($sidebar) ? $sidebar : ''; ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?= isset($topbar) ? $topbar : ''; ?>

            <div class="container-fluid" id="container-wrapper">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0 font-weight-bold text-primary">Edit Work Order</h4>
                        <div>
                            <span class="text-muted">* = required information</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formWorkOrder" method="post" action="<?= base_url('Tool_management/tool_work_order/submit_data'); ?>">
                            <input type="hidden" name="action" value="EDIT">
                            <input type="hidden" name="WO_ID" value="<?= isset($work_order['WO_ID']) ? htmlspecialchars($work_order['WO_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            
                            <div class="row">
                                <!-- Kolom Kiri -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="date" name="WO_CREATED_DATE" class="form-control" 
                                               value="<?= isset($work_order['DATE']) && !empty($work_order['DATE']) ? date('Y-m-d', strtotime($work_order['DATE'])) : ''; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>W/O No.</label>
                                        <div class="info-display">
                                            <?= isset($work_order['WO_NO']) ? htmlspecialchars($work_order['WO_NO'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>W/O Type</label>
                                        <div class="info-display">
                                            <?= $this->tool_work_order->get_wo_type_name(isset($work_order['WO_TYPE']) ? $work_order['WO_TYPE'] : 0); ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Created By</label>
                                        <div class="info-display">
                                            <?= isset($work_order['CREATED_BY_NAME']) ? htmlspecialchars($work_order['CREATED_BY_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Requested By</label>
                                        <div class="info-display">
                                            <?= isset($work_order['REQUESTED_BY_NAME']) ? htmlspecialchars($work_order['REQUESTED_BY_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Department</label>
                                        <input type="text" name="WO_DEPARTMENT" class="form-control" 
                                               value="<?= isset($work_order['WO_DEPARTMENT']) ? htmlspecialchars($work_order['WO_DEPARTMENT'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>Reason</label>
                                        <div class="info-display">
                                            <?= $this->tool_work_order->get_wo_reason_name(isset($work_order['WO_REASON']) ? $work_order['WO_REASON'] : 0); ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <textarea name="WO_REMARKS" class="form-control" rows="3"><?= isset($work_order['WO_REMARKS']) ? htmlspecialchars($work_order['WO_REMARKS'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Quantity (pcs)</label>
                                        <input type="number" name="WO_QTY" class="form-control" 
                                               value="<?= isset($work_order['WO_QTY']) ? htmlspecialchars($work_order['WO_QTY'], ENT_QUOTES, 'UTF-8') : '1'; ?>" 
                                               min="1">
                                    </div>

                                    <div class="form-group">
                                        <label>Tool ID</label>
                                        <div class="info-display">
                                            <?php 
                                            $tool_inv_id = isset($work_order['WO_INV_ID']) ? (int)$work_order['WO_INV_ID'] : 0;
                                            $tool_id = isset($work_order['TOOL_ID']) ? htmlspecialchars($work_order['TOOL_ID'], ENT_QUOTES, 'UTF-8') : '';
                                            if ($tool_inv_id > 0 && !empty($tool_id)): 
                                                $inventory_detail_url = base_url('Tool_inventory/tool_inventory/detail_page/' . $tool_inv_id);
                                            ?>
                                                <a href="<?= $inventory_detail_url; ?>" class="text-primary" style="text-decoration: underline;" target="_blank">
                                                    <?= $tool_id; ?>
                                                </a>
                                            <?php else: ?>
                                                <?= $tool_id; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool Tag</label>
                                        <div class="info-display">
                                            <?= isset($work_order['TOOL_TAG']) ? htmlspecialchars($work_order['TOOL_TAG'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool Drawing No</label>
                                        <div class="info-display">
                                            <?= isset($work_order['TOOL_DRAWING_NO']) ? htmlspecialchars($work_order['TOOL_DRAWING_NO'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Revision</label>
                                        <div class="info-display">
                                            <?= isset($work_order['REVISION']) ? htmlspecialchars($work_order['REVISION'], ENT_QUOTES, 'UTF-8') : '0'; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool Name</label>
                                        <div class="info-display">
                                            <?= isset($work_order['TOOL_NAME']) ? htmlspecialchars($work_order['TOOL_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Target Completion Date</label>
                                        <input type="date" name="WO_TARGET_COM_DATE" class="form-control" 
                                               value="<?= isset($work_order['TARGET_COMPLETION_DATE']) && !empty($work_order['TARGET_COMPLETION_DATE']) ? date('Y-m-d', strtotime($work_order['TARGET_COMPLETION_DATE'])) : ''; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>Actual Completion Date</label>
                                        <input type="date" name="WO_ACTUAL_COM_DATE" class="form-control" 
                                               value="<?= isset($work_order['ACTUAL_COMPLETION_DATE']) && !empty($work_order['ACTUAL_COMPLETION_DATE']) ? date('Y-m-d', strtotime($work_order['ACTUAL_COMPLETION_DATE'])) : ''; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>W/O Status</label>
                                        <select name="WO_STATUS" class="form-control">
                                            <option value="1" <?= (isset($work_order['WO_STATUS']) && (int)$work_order['WO_STATUS'] === 1) ? 'selected' : ''; ?>>Open</option>
                                            <option value="2" <?= (isset($work_order['WO_STATUS']) && (int)$work_order['WO_STATUS'] === 2) ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="3" <?= (isset($work_order['WO_STATUS']) && (int)$work_order['WO_STATUS'] === 3) ? 'selected' : ''; ?>>Closed</option>
                                            <option value="4" <?= (isset($work_order['WO_STATUS']) && (int)$work_order['WO_STATUS'] === 4) ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Condition After Repair</label>
                                        <input type="text" name="WO_CONDITION" class="form-control" 
                                               value="<?= isset($work_order['WO_CONDITION']) ? htmlspecialchars($work_order['WO_CONDITION'], ENT_QUOTES, 'UTF-8') : ''; ?>" 
                                               placeholder="e.g., O K Repaired">
                                    </div>

                                    <div class="form-group">
                                        <label>Urgency (Range from * to *****)</label>
                                        <input type="text" name="WO_URGENCY" class="form-control" 
                                               value="<?= isset($work_order['WO_URGENCY']) ? htmlspecialchars($work_order['WO_URGENCY'], ENT_QUOTES, 'UTF-8') : ''; ?>" 
                                               placeholder="e.g., ***">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Submit
                                </button>
                                <a href="<?= base_url('Tool_management/tool_work_order'); ?>" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Work Order Costing -->
                <div class="card mt-3">
                    <div class="card-header">
                        <div class="sub-section-title">
                            <span>Work Order Costing</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- External Cost Section -->
                        <div class="mb-4">
                            <h5 class="mb-3">External Cost</h5>
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalExternalCost" onclick="resetExternalCostForm()">
                                    <i class="fa fa-plus"></i> Add New
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table id="table-external-costs" class="table table-bordered table-striped w-100 text-left">
                                    <thead>
                                        <tr class="text-center">
                                            <th>ID</th>
                                            <th>Activity</th>
                                            <th>Supplier</th>
                                            <th>PO NO</th>
                                            <th>Invoice No</th>
                                            <th>RF No</th>
                                            <th>GRN Date</th>
                                            <th>GRN No</th>
                                            <th>Qty</th>
                                            <th>Unit Price</th>
                                            <th>Sub Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($external_costs)): ?>
                                            <?php foreach ($external_costs as $ext): ?>
                                                <tr>
                                                    <td><?= isset($ext['EXTCOST_ID']) ? htmlspecialchars($ext['EXTCOST_ID'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                    <td><?= isset($ext['ACTIVITY']) ? htmlspecialchars($ext['ACTIVITY'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                    <td><?= isset($ext['SUPPLIER']) ? htmlspecialchars($ext['SUPPLIER'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                    <td><?= isset($ext['PO_NO']) ? htmlspecialchars($ext['PO_NO'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                    <td><?= isset($ext['INVOICE_NO']) ? htmlspecialchars($ext['INVOICE_NO'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                    <td><?= isset($ext['RF_NO']) ? htmlspecialchars($ext['RF_NO'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                    <td><?= isset($ext['GRN_DATE']) && !empty($ext['GRN_DATE']) ? date('d-m-Y', strtotime($ext['GRN_DATE'])) : ''; ?></td>
                                                    <td><?= isset($ext['GRN_NO']) ? htmlspecialchars($ext['GRN_NO'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                    <td><?= isset($ext['EXTCOST_SUP_QTY']) ? htmlspecialchars($ext['EXTCOST_SUP_QTY'], ENT_QUOTES, 'UTF-8') : '0'; ?></td>
                                                    <td><?= isset($ext['EXTCOST_SUP_UNIT_PRICE']) ? number_format($ext['EXTCOST_SUP_UNIT_PRICE'], 2) : '0.00'; ?></td>
                                                    <td><?= isset($ext['SUB_TOTAL']) ? number_format($ext['SUB_TOTAL'], 2) : '0.00'; ?></td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button class="btn btn-secondary btn-sm btn-edit-extcost" data-id="<?= isset($ext['EXTCOST_ID']) ? $ext['EXTCOST_ID'] : ''; ?>">Edit</button>
                                                            <button class="btn btn-danger btn-sm btn-delete-extcost" data-id="<?= isset($ext['EXTCOST_ID']) ? $ext['EXTCOST_ID'] : ''; ?>">Delete</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="12" class="text-center">No external costs found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?= isset($modal_logout) ? $modal_logout : ''; ?>
        </div>
        <?= isset($footer) ? $footer : ''; ?>
    </div>
</div>

<!-- Modal External Cost -->
<div class="modal fade" id="modalExternalCost" tabindex="-1" role="dialog" aria-labelledby="modalExternalCostLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalExternalCostLabel">Edit External Cost</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formExternalCost">
                    <input type="hidden" name="action" id="extcost_action" value="ADD">
                    <input type="hidden" name="EXTCOST_ID" id="extcost_id" value="">
                    <input type="hidden" name="EXTCOST_WO_ID" value="<?= isset($work_order['WO_ID']) ? htmlspecialchars($work_order['WO_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="EXTCOST_DATE" id="extcost_date" class="form-control">
                            </div>

                            <div class="form-group">
                                <label class="label-required">Activity</label>
                                <select name="EXTCOST_WA_ID" id="extcost_wa_id" class="form-control" required>
                                    <option value="">-- Select Activity --</option>
                                    <?php foreach ($work_activities as $wa): ?>
                                        <option value="<?= (int)$wa['WA_ID']; ?>">
                                            <?= htmlspecialchars($wa['WA_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Supplier</label>
                                <select name="EXTCOST_SUP_ID" id="extcost_sup_id" class="form-control">
                                    <option value="">-- Select Supplier --</option>
                                    <?php foreach ($suppliers as $sup): ?>
                                        <option value="<?= (int)$sup['SUP_ID']; ?>">
                                            <?= htmlspecialchars($sup['SUP_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>PO NO</label>
                                <input type="text" name="EXTCOST_PO_NO" id="extcost_po_no" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Invoice No</label>
                                <input type="text" name="EXTCOST_INVOICE_NO" id="extcost_invoice_no" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>RF No</label>
                                <input type="text" name="EXTCOST_RF_NO" id="extcost_rf_no" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>GRN Date</label>
                                <input type="date" name="EXTCOST_GRN_DATE" id="extcost_grn_date" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>GRN No</label>
                                <input type="text" name="EXTCOST_GRN_NO" id="extcost_grn_no" class="form-control">
                            </div>

                            <div class="form-group">
                                <label>Unit Price</label>
                                <input type="number" name="EXTCOST_SUP_UNIT_PRICE" id="extcost_unit_price" class="form-control" step="0.01" min="0" value="0">
                            </div>

                            <div class="form-group">
                                <label>Qty</label>
                                <input type="number" name="EXTCOST_SUP_QTY" id="extcost_qty" class="form-control" step="0.01" min="0" value="0">
                            </div>

                            <div class="form-group">
                                <label>Sub Total</label>
                                <div class="info-display" id="extcost_subtotal">0.00</div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btnSaveExternalCost">
                    <i class="fa fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<?= isset($foot) ? $foot : ''; ?>
<script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js'); ?>"></script>
<script>
(function($){
    $(function(){
        // Initialize DataTable for external costs
        if ($('#table-external-costs tbody tr').length > 0) {
            $('#table-external-costs').DataTable({
                pageLength: 10,
                order: [[0, 'desc']],
                autoWidth: false,
                scrollX: true
            });
        }

        // Calculate Sub Total
        function calculateSubTotal() {
            var unitPrice = parseFloat($('#extcost_unit_price').val()) || 0;
            var qty = parseFloat($('#extcost_qty').val()) || 0;
            var subTotal = unitPrice * qty;
            $('#extcost_subtotal').text(subTotal.toFixed(2));
        }

        $('#extcost_unit_price, #extcost_qty').on('input', calculateSubTotal);

        // Reset External Cost Form
        window.resetExternalCostForm = function() {
            $('#formExternalCost')[0].reset();
            $('#extcost_action').val('ADD');
            $('#extcost_id').val('');
            $('#extcost_subtotal').text('0.00');
            $('#modalExternalCostLabel').text('Add External Cost');
        };

        // Edit External Cost
        $('.btn-edit-extcost').on('click', function() {
            var extcost_id = $(this).data('id');
            if (!extcost_id) return;

            $.ajax({
                url: '<?= base_url("Tool_management/tool_work_order/get_external_cost"); ?>',
                type: 'POST',
                dataType: 'json',
                data: { EXTCOST_ID: extcost_id }
            }).done(function(res) {
                if (res && res.success && res.data) {
                    var data = res.data;
                    $('#extcost_action').val('EDIT');
                    $('#extcost_id').val(data.EXTCOST_ID || '');
                    $('#extcost_wa_id').val(data.EXTCOST_WA_ID || '');
                    $('#extcost_sup_id').val(data.EXTCOST_SUP_ID || '');
                    $('#extcost_po_no').val(data.PO_NO || '');
                    $('#extcost_invoice_no').val(data.INVOICE_NO || '');
                    $('#extcost_rf_no').val(data.RF_NO || '');
                    $('#extcost_grn_no').val(data.GRN_NO || '');
                    $('#extcost_unit_price').val(data.EXTCOST_SUP_UNIT_PRICE || '0');
                    $('#extcost_qty').val(data.EXTCOST_SUP_QTY || '0');
                    
                    if (data.DATE && data.DATE.length >= 10) {
                        $('#extcost_date').val(data.DATE.substring(0, 10));
                    } else {
                        $('#extcost_date').val('');
                    }
                    
                    if (data.GRN_DATE && data.GRN_DATE.length >= 10) {
                        $('#extcost_grn_date').val(data.GRN_DATE.substring(0, 10));
                    } else {
                        $('#extcost_grn_date').val('');
                    }
                    
                    calculateSubTotal();
                    $('#modalExternalCostLabel').text('Edit External Cost');
                    $('#modalExternalCost').modal('show');
                } else {
                    alert(res && res.message ? res.message : 'Gagal mengambil data');
                }
            }).fail(function() {
                alert('Terjadi kesalahan');
            });
        });

        // Delete External Cost
        $('.btn-delete-extcost').on('click', function() {
            var extcost_id = $(this).data('id');
            if (!extcost_id) return;
            if (!confirm('Hapus external cost ini?')) return;

            $.ajax({
                url: '<?= base_url("Tool_management/tool_work_order/delete_external_cost"); ?>',
                type: 'POST',
                dataType: 'json',
                data: { EXTCOST_ID: extcost_id }
            }).done(function(res) {
                if (res && res.success) {
                    alert(res.message || 'External Cost berhasil dihapus');
                    location.reload();
                } else {
                    alert(res && res.message ? res.message : 'Gagal menghapus');
                }
            }).fail(function() {
                alert('Terjadi kesalahan');
            });
        });

        // Save External Cost
        $('#btnSaveExternalCost').on('click', function() {
            var formData = $('#formExternalCost').serialize();
            
            $.ajax({
                url: '<?= base_url("Tool_management/tool_work_order/submit_external_cost"); ?>',
                type: 'POST',
                dataType: 'json',
                data: formData
            }).done(function(res) {
                if (res && res.success) {
                    alert(res.message || 'External Cost berhasil disimpan');
                    $('#modalExternalCost').modal('hide');
                    location.reload();
                } else {
                    alert(res && res.message ? res.message : 'Gagal menyimpan');
                }
            }).fail(function() {
                alert('Terjadi kesalahan');
            });
        });

        // Form submit Work Order
        $('#formWorkOrder').on('submit', function(e) {
            e.preventDefault();
            // TODO: Implement form submission
            alert('Save Work Order functionality will be implemented');
        });
    });
})(jQuery);
</script>
</body>
</html>

