<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <style>
        .table td, .table th {
            color: #000 !important;
            padding: 0.35rem 0.4rem !important;
            font-size: 0.85rem;
        }
        .navbar { position: sticky; top: 0; z-index: 1030; }
        #content-wrapper { min-height: calc(100vh - 56px); }
        #container-wrapper { 
            padding-bottom: 2rem !important; 
        }
        .card { 
            margin-bottom: 2rem; 
        }
        .card-body {
            padding-bottom: 5rem !important;
        }
        .section-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            padding-top: 1rem;
            padding-bottom: 0.5rem;
            border-top: 2px solid #dee2e6;
            border-bottom: 2px solid #dee2e6;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-control:disabled, .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
            cursor: not-allowed;
        }
        select.form-control:disabled {
            background-color: #e9ecef;
            opacity: 1;
            cursor: not-allowed;
        }
        .tool-id-link {
            color: #007bff;
            text-decoration: underline;
            cursor: pointer;
        }
        .tool-id-link:hover {
            color: #0056b3;
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
                        <h4 class="m-0 font-weight-bold text-primary">Tool Scrap Detail</h4>
                        <div>
                            <?php
                            $scrap_id = isset($scrap['SCRAP_ID']) ? (int)$scrap['SCRAP_ID'] : 0;
                            $report_url = base_url('Tool_inventory/tool_scrap/report_page/' . $scrap_id);
                            ?>
                            <a href="<?= $report_url; ?>" class="btn btn-sm btn-info shadow-sm" style="margin-right: 5px;">
                                <i class="fa fa-file-text"></i> View Report
                            </a>
                            <a href="<?= base_url('Tool_inventory/tool_scrap'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Tool Scrap Details Section -->
                        <div class="section-title">Tool Scrap Details</div>
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Issue Date</label>
                                    <input type="date" class="form-control" value="<?= isset($scrap['SCRAP_DATE']) && !empty($scrap['SCRAP_DATE']) ? date('Y-m-d', strtotime($scrap['SCRAP_DATE'])) : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Requested By</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['REQUESTED_BY_NAME']) ? htmlspecialchars($scrap['REQUESTED_BY_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Tool Scrap No.</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['SCRAP_NO']) ? htmlspecialchars($scrap['SCRAP_NO'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Accident/ Scrap Date</label>
                                    <input type="date" class="form-control" value="<?= isset($scrap['SCRAP_ACC_DATE']) && !empty($scrap['SCRAP_ACC_DATE']) ? date('Y-m-d', strtotime($scrap['SCRAP_ACC_DATE'])) : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Machine</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['MACHINE']) ? htmlspecialchars($scrap['MACHINE'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Operator</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['OPERATOR_NAME']) ? htmlspecialchars($scrap['OPERATOR_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Status</label>
                                    <?php
                                    $status_map = array(0 => 'Pending', 1 => 'Approved', 2 => 'Closed', 3 => 'Cancelled');
                                    $status = isset($scrap['SCRAP_STATUS']) ? (int)$scrap['SCRAP_STATUS'] : 0;
                                    $status_text = isset($status_map[$status]) ? $status_map[$status] : 'Unknown';
                                    ?>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($status_text, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Tool Information Section -->
                        <div class="section-title">Tool Information</div>
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tool ID</label>
                                    <?php
                                    $tool_id = isset($scrap['INV_TOOL_ID']) ? $scrap['INV_TOOL_ID'] : '';
                                    $inv_id = isset($scrap['SCRAP_INV_ID']) ? (int)$scrap['SCRAP_INV_ID'] : 0;
                                    if (!empty($tool_id) && $inv_id > 0):
                                        $tool_inventory_url = base_url('Tool_inventory/tool_inventory/detail_page/' . $inv_id);
                                    ?>
                                        <a href="<?= $tool_inventory_url; ?>" class="tool-id-link form-control" style="display: block; padding: 0.375rem 0.75rem; border: 1px solid #ced4da; border-radius: 0.25rem; background-color: #e9ecef;">
                                            <?= htmlspecialchars($tool_id, ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    <?php else: ?>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($tool_id, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label>Tool Name</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['TOOL_NAME']) ? htmlspecialchars($scrap['TOOL_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Material</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['MATERIAL']) ? htmlspecialchars($scrap['MATERIAL'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>RQ No.</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['RQ_NO']) ? htmlspecialchars($scrap['RQ_NO'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Tool Price</label>
                                    <input type="number" class="form-control" step="0.01" value="<?= isset($scrap['TOOL_PRICE']) ? htmlspecialchars($scrap['TOOL_PRICE'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Tool Residue Value</label>
                                    <input type="number" class="form-control" step="0.01" value="0" readonly>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Drawing No.</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['TOOL_DRAWING_NO']) ? htmlspecialchars($scrap['TOOL_DRAWING_NO'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Revision</label>
                                    <input type="number" class="form-control" value="<?= isset($scrap['REVISION']) ? htmlspecialchars($scrap['REVISION'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Tool Assignment No</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['TOOL_ASSIGNMENT_NO']) ? htmlspecialchars($scrap['TOOL_ASSIGNMENT_NO'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Pcs Produced</label>
                                    <input type="number" class="form-control" value="<?= isset($scrap['PCS_PRODUCED']) ? htmlspecialchars($scrap['PCS_PRODUCED'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Tool Inventory Status Section -->
                        <div class="section-title">Tool Inventory Status</div>
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>This Tool Drawing</label>
                                </div>
                                <div class="form-group">
                                    <label>Std. Qty</label>
                                    <input type="number" class="form-control" value="<?= isset($scrap['SCRAP_STD_QTY_THIS']) ? htmlspecialchars($scrap['SCRAP_STD_QTY_THIS'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Not Received Qty</label>
                                    <input type="number" class="form-control" value="<?= isset($scrap['SCRAP_NRCV_QTY_THIS']) ? htmlspecialchars($scrap['SCRAP_NRCV_QTY_THIS'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Curr. Qty</label>
                                    <input type="number" class="form-control" value="<?= isset($scrap['SCRAP_CURRENT_QTY_THIS']) ? htmlspecialchars($scrap['SCRAP_CURRENT_QTY_THIS'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>This + Interchangeable Tool Drawing</label>
                                </div>
                                <div class="form-group">
                                    <label>Std. Qty</label>
                                    <input type="number" class="form-control" value="0" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Not Received Qty</label>
                                    <input type="number" class="form-control" value="0" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Curr. Qty</label>
                                    <input type="number" class="form-control" value="0" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Investigation Section -->
                        <div class="section-title">Investigation</div>
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Reason</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['REASON']) ? htmlspecialchars($scrap['REASON'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Counter Measure</label>
                                    <textarea class="form-control" rows="3" readonly><?= isset($scrap['SCRAP_COUNTER_MEASURE']) ? htmlspecialchars($scrap['SCRAP_COUNTER_MEASURE'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cause</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['CAUSE_NAME']) ? htmlspecialchars($scrap['CAUSE_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Cause Remark</label>
                                    <textarea class="form-control" rows="3" readonly><?= isset($scrap['SCRAP_CAUSE_REMARK']) ? htmlspecialchars($scrap['SCRAP_CAUSE_REMARK'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Investigated By</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['INVESTIGATED_BY_NAME']) ? htmlspecialchars($scrap['INVESTIGATED_BY_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Decision Section -->
                        <div class="section-title">Decision</div>
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Suggestion</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['SCRAP_DISPOSITION']) ? htmlspecialchars($scrap['SCRAP_DISPOSITION'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>To Order</label>
                                    <?php
                                    $to_order = isset($scrap['SCRAP_TO_ORDER']) ? (int)$scrap['SCRAP_TO_ORDER'] : 0;
                                    $to_order_text = $to_order == 1 ? 'Yes' : 'No';
                                    ?>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($to_order_text, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Approve By</label>
                                    <input type="text" class="form-control" value="<?= isset($scrap['APPROVED_BY_NAME']) ? htmlspecialchars($scrap['APPROVED_BY_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Approve Date</label>
                                    <input type="date" class="form-control" value="<?= isset($scrap['SCRAP_APPROVED_DATE']) && !empty($scrap['SCRAP_APPROVED_DATE']) ? date('Y-m-d', strtotime($scrap['SCRAP_APPROVED_DATE'])) : ''; ?>" readonly>
                                </div>
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
</body>
</html>

