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
        .form-control[readonly], .form-control[disabled], select[disabled] {
            background-color: #e9ecef;
            cursor: not-allowed;
            opacity: 1;
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
                        <h4 class="m-0 font-weight-bold text-primary">Work Order Detail</h4>
                        <div>
                            <a href="<?= base_url('Tool_management/tool_work_order/edit_page/' . (isset($work_order['WO_ID']) ? $work_order['WO_ID'] : 0)); ?>" class="btn btn-sm btn-primary shadow-sm mr-2">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <a href="<?= base_url('Tool_management/tool_work_order'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Kolom Kiri -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date</label>
                                    <input type="date" class="form-control" readonly
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
                                    <input type="text" class="form-control" readonly
                                           value="<?= isset($work_order['REQUESTED_BY_NAME']) ? htmlspecialchars($work_order['REQUESTED_BY_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label>Department</label>
                                    <input type="text" class="form-control" readonly
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
                                    <textarea class="form-control" rows="2" readonly><?= isset($work_order['WO_REMARKS']) ? htmlspecialchars($work_order['WO_REMARKS'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Quantity (pcs)</label>
                                    <input type="number" class="form-control" readonly
                                           value="<?= isset($work_order['WO_QTY']) ? htmlspecialchars($work_order['WO_QTY'], ENT_QUOTES, 'UTF-8') : '1'; ?>">
                                </div>
                            </div>

                            <!-- Kolom Kanan -->
                            <div class="col-md-6">
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

                                <div class="form-group">
                                    <label>Target Completion Date</label>
                                    <input type="date" class="form-control" readonly
                                           value="<?= isset($work_order['TARGET_COMPLETION_DATE']) && !empty($work_order['TARGET_COMPLETION_DATE']) ? date('Y-m-d', strtotime($work_order['TARGET_COMPLETION_DATE'])) : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label>Actual Completion Date</label>
                                    <input type="date" class="form-control" readonly
                                           value="<?= isset($work_order['ACTUAL_COMPLETION_DATE']) && !empty($work_order['ACTUAL_COMPLETION_DATE']) ? date('Y-m-d', strtotime($work_order['ACTUAL_COMPLETION_DATE'])) : ''; ?>">
                                </div>

                                <div class="form-group">
                                    <label>W/O Status</label>
                                    <div class="info-display">
                                        <?= $this->tool_work_order->get_wo_status_badge(isset($work_order['WO_STATUS']) ? $work_order['WO_STATUS'] : 0); ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Condition After Repair</label>
                                    <select class="form-control" disabled>
                                        <?php 
                                        $current_wo_condition = isset($work_order['WO_CONDITION']) ? $work_order['WO_CONDITION'] : null;
                                        
                                        $condition_map = array(
                                            '' => '-- Select Condition --',
                                            '0' => 'None',
                                            '1' => 'O K Modified',
                                            '2' => 'N G Needs Repair',
                                            '3' => 'N G Not Repairable',
                                            '4' => 'O K Repaired'
                                        );
                                        
                                        foreach ($condition_map as $val => $label): 
                                            $selected = false;
                                            
                                            if ($val === '') {
                                                $selected = ($current_wo_condition === null || $current_wo_condition === '' || $current_wo_condition === false);
                                            } else {
                                                $selected = (isset($current_wo_condition) && (int)$current_wo_condition === (int)$val);
                                            }
                                        ?>
                                            <option value="<?= $val === '' ? '' : $val; ?>" <?= $selected ? 'selected="selected"' : ''; ?>>
                                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Urgency (Range from * to *****)</label>
                                    <input type="text" class="form-control" readonly
                                           value="<?= isset($work_order['WO_URGENCY']) ? htmlspecialchars($work_order['WO_URGENCY'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                </div>
                            </div>
                        </div>
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
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr class="no-data-row">
                                                <td colspan="11" class="text-center">No external costs found</td>
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

<?= isset($foot) ? $foot : ''; ?>
<script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js'); ?>"></script>
<script>
(function($){
    $(function(){
        // Initialize DataTable for external costs
        var $tableExternalCosts = $('#table-external-costs');
        if ($tableExternalCosts.length > 0 && $tableExternalCosts.find('thead tr').length > 0) {
            var $extRows = $tableExternalCosts.find('tbody tr:not(.no-data-row)');
            var hasValidExtRows = false;
            var expectedCols = $tableExternalCosts.find('thead tr th').length;
            
            // Remove no-data-row before checking
            $tableExternalCosts.find('tbody tr.no-data-row').remove();
            
            $extRows.each(function() {
                var $row = $(this);
                var colCount = $row.find('td').length;
                if (colCount === expectedCols && !$row.find('td[colspan]').length) {
                    hasValidExtRows = true;
                    return false; // break
                }
            });
            
            if (hasValidExtRows) {
                try {
                    $tableExternalCosts.DataTable({
                        pageLength: 10,
                        order: [[0, 'desc']],
                        autoWidth: false,
                        scrollX: true
                    });
                } catch(e) {
                    console.error('Error initializing table-external-costs DataTable:', e);
                }
            } else {
                // If no valid rows, add back the no-data message
                if ($tableExternalCosts.find('tbody tr').length === 0) {
                    $tableExternalCosts.find('tbody').append('<tr><td colspan="' + expectedCols + '" class="text-center">No external costs found</td></tr>');
                }
            }
        }
    });
})(jQuery);
</script>
</body>
</html>

