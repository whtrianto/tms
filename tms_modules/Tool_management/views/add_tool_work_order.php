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
        .tool-id-input-group {
            display: flex;
            gap: 0.5rem;
        }
        .tool-id-input-group input {
            flex: 1;
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
                        <h4 class="m-0 font-weight-bold text-primary">Add Work Order</h4>
                        <div>
                            <span class="text-muted">* = required information</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formWorkOrder" method="post" action="<?= base_url('Tool_management/tool_work_order/submit_data'); ?>">
                            <input type="hidden" name="action" value="ADD">
                            
                            <div class="row">
                                <!-- Kolom Kiri -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="date" name="WO_CREATED_DATE" class="form-control" 
                                               value="<?= date('Y-m-d'); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>W/O No.</label>
                                        <div class="info-display">
                                            (Auto-generated)
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>W/O Type</label>
                                        <div class="info-display">
                                            Repair
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Created By</label>
                                        <div class="info-display">
                                            <?php 
                                            $username = $this->session->userdata('username');
                                            echo htmlspecialchars($username ? $username : 'SYSTEM', ENT_QUOTES, 'UTF-8'); 
                                            ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Requested By</label>
                                        <div class="tool-id-input-group">
                                            <input type="text" id="selected_user_name" class="form-control" readonly 
                                                   placeholder="Click button to select user">
                                            <input type="hidden" name="WO_REQUESTED_BY" id="selected_user_id" value="">
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalSelectUser">
                                                <i class="fa fa-search"></i> Select
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Department</label>
                                        <div class="tool-id-input-group">
                                            <input type="text" id="selected_dept_name" class="form-control" readonly 
                                                   placeholder="Click button to select department">
                                            <input type="hidden" name="WO_DEPARTMENT" id="selected_dept_name_hidden" value="">
                                            <input type="hidden" name="WO_DEPARTMENT_ID" id="selected_dept_id" value="">
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalSelectDepartment">
                                                <i class="fa fa-search"></i> Select
                                            </button>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Reason</label>
                                        <select name="WO_REASON" class="form-control">
                                            <option value="">-- Select Reason --</option>
                                            <option value="1">Accident</option>
                                            <option value="2">Crack</option>
                                            <option value="3">Chipping</option>
                                            <option value="4">Dented</option>
                                            <option value="5">Wear</option>
                                            <option value="6">Scratch</option>
                                            <option value="7">Others</option>
                                            <option value="8">None</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <textarea name="WO_REMARKS" class="form-control" rows="2"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool ID</label>
                                        <div class="info-display">
                                            -
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool Tag</label>
                                        <div class="info-display">
                                            -
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool Drawing No</label>
                                        <div class="info-display">
                                            -
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Revision</label>
                                        <div class="info-display">
                                            0
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool Name</label>
                                        <div class="info-display">
                                            -
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Quantity (pcs)</label>
                                        <input type="number" name="WO_QTY" class="form-control" 
                                               value="1" min="1">
                                    </div>

                                    <div class="form-group">
                                        <label>Target Completion Date</label>
                                        <input type="date" name="WO_TARGET_COM_DATE" class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <label>Actual Completion Date</label>
                                        <input type="date" name="WO_ACTUAL_COM_DATE" class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <label>W/O Status</label>
                                        <select name="WO_STATUS" class="form-control">
                                            <option value="1" selected>Open</option>
                                            <option value="2">In Progress</option>
                                            <option value="3">Closed</option>
                                            <option value="4">Cancelled</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Condition After Repair</label>
                                        <input type="text" name="WO_CONDITION" class="form-control" 
                                               placeholder="e.g., O K Repaired">
                                    </div>

                                    <div class="form-group">
                                        <label>Urgency (Range from * to *****)</label>
                                        <input type="text" name="WO_URGENCY" class="form-control" 
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
                                        <tr>
                                            <td colspan="12" class="text-center">No external costs found</td>
                                        </tr>
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

<!-- Modal Select User -->
<div class="modal fade" id="modalSelectUser" tabindex="-1" role="dialog" aria-labelledby="modalSelectUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSelectUserLabel">Select User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tableUsers" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr class="text-center">
                                <th>ID</th>
                                <th>User</th>
                                <th>Position</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr class="clickable-row" 
                                        data-user-id="<?= isset($user['USR_ID']) ? (int)$user['USR_ID'] : 0; ?>"
                                        data-user-name="<?= isset($user['USR_NAME']) ? htmlspecialchars($user['USR_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                        <td><?= isset($user['USR_ID']) ? htmlspecialchars($user['USR_ID'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                        <td>
                                            <a href="#" class="select-user-link text-primary" style="text-decoration: underline;">
                                                <?= isset($user['USR_NAME']) ? htmlspecialchars($user['USR_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                            </a>
                                        </td>
                                        <td><?= isset($user['POSITION']) ? htmlspecialchars($user['POSITION'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Select Department -->
<div class="modal fade" id="modalSelectDepartment" tabindex="-1" role="dialog" aria-labelledby="modalSelectDepartmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSelectDepartmentLabel">Select Department</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tableDepartments" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr class="text-center">
                                <th>ID</th>
                                <th>Department</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($departments)): ?>
                                <?php foreach ($departments as $dept): ?>
                                    <tr class="clickable-row" 
                                        data-dept-id="<?= isset($dept['DEPART_ID']) ? (int)$dept['DEPART_ID'] : 0; ?>"
                                        data-dept-name="<?= isset($dept['DEPART_NAME']) ? htmlspecialchars($dept['DEPART_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                        <td><?= isset($dept['DEPART_ID']) ? htmlspecialchars($dept['DEPART_ID'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                        <td>
                                            <a href="#" class="select-dept-link text-primary" style="text-decoration: underline;">
                                                <?= isset($dept['DEPART_NAME']) ? htmlspecialchars($dept['DEPART_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                            </a>
                                        </td>
                                        <td><?= isset($dept['DESCRIPTION']) ? htmlspecialchars($dept['DESCRIPTION'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No departments found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal External Cost -->
<div class="modal fade" id="modalExternalCost" tabindex="-1" role="dialog" aria-labelledby="modalExternalCostLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalExternalCostLabel">Add External Cost</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formExternalCost">
                    <input type="hidden" name="action" id="extcost_action" value="ADD">
                    <input type="hidden" name="EXTCOST_ID" id="extcost_id" value="">
                    <input type="hidden" name="EXTCOST_WO_ID" id="extcost_wo_id" value="">
                    
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
        // Initialize DataTable for users
        if ($('#tableUsers tbody tr').length > 0 && !$('#tableUsers tbody tr').has('td[colspan]').length) {
            $('#tableUsers').DataTable({
                pageLength: 10,
                order: [[1, 'asc']],
                autoWidth: false
            });
        }

        // Initialize DataTable for departments
        if ($('#tableDepartments tbody tr').length > 0 && !$('#tableDepartments tbody tr').has('td[colspan]').length) {
            $('#tableDepartments').DataTable({
                pageLength: 10,
                order: [[1, 'asc']],
                autoWidth: false
            });
        }

        // Handle User Selection
        $('#tableUsers tbody').on('click', 'tr.clickable-row', function(e) {
            e.preventDefault();
            var $row = $(this);
            var userId = $row.data('user-id');
            var userName = $row.data('user-name');

            $('#selected_user_id').val(userId);
            $('#selected_user_name').val(userName);
            $('#modalSelectUser').modal('hide');
        });

        $('#tableUsers tbody').on('click', 'a.select-user-link', function(e) {
            e.preventDefault();
            $(this).closest('tr').trigger('click');
        });

        // Handle Department Selection
        $('#tableDepartments tbody').on('click', 'tr.clickable-row', function(e) {
            e.preventDefault();
            var $row = $(this);
            var deptId = $row.data('dept-id');
            var deptName = $row.data('dept-name');

            $('#selected_dept_id').val(deptId);
            $('#selected_dept_name').val(deptName);
            $('#selected_dept_name_hidden').val(deptName);
            $('#modalSelectDepartment').modal('hide');
        });

        $('#tableDepartments tbody').on('click', 'a.select-dept-link', function(e) {
            e.preventDefault();
            $(this).closest('tr').trigger('click');
        });

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
            $('#extcost_wo_id').val(''); // Will be set after work order is created
            $('#extcost_subtotal').text('0.00');
            $('#modalExternalCostLabel').text('Add External Cost');
        };

        // Save External Cost (will be saved after work order is created)
        $('#btnSaveExternalCost').on('click', function() {
            alert('Please save the Work Order first before adding External Costs.');
        });

        // Form submit Work Order
        $('#formWorkOrder').on('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                $(this).addClass('was-validated');
                return;
            }

            var formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false,
                timeout: 30000
            }).done(function(res) {
                if (res && res.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Work Order berhasil ditambahkan');
                    } else {
                        alert(res.message || 'Work Order berhasil ditambahkan');
                    }
                    setTimeout(function() {
                        window.location.href = '<?= base_url("Tool_management/tool_work_order"); ?>';
                    }, 1000);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(res && res.message ? res.message : 'Gagal menyimpan Work Order');
                    } else {
                        alert(res && res.message ? res.message : 'Gagal menyimpan Work Order');
                    }
                }
            }).fail(function(xhr, status, error) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Gagal menyimpan: ' + (error || status));
                } else {
                    alert('Gagal menyimpan: ' + (error || status));
                }
            });
        });
    });
})(jQuery);
</script>
</body>
</html>

