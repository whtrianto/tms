<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <style>
        html, body, #content-wrapper { color: #000; }
        .card, .table, label, .form-text { color: #000; }
        .navbar { position: sticky; top: 0; z-index: 1030; }
        #content-wrapper { min-height: calc(100vh - 56px); }
        #container-wrapper { padding-bottom: 10rem !important; }
        .card { margin-bottom: 1.5rem; }
        #shared-tool-card { margin-bottom: 8rem !important; }
        
        .detail-row { display: flex; margin-bottom: 0.6rem; align-items: flex-start; }
        .detail-label { font-weight: 600; color: #5a5c69; min-width: 160px; }
        .detail-colon { width: 20px; text-align: center; }
        .detail-value { color: #2e2e2e; flex: 1; }
        
        .section-divider { border-top: 2px solid #4e73df; margin: 1.5rem 0 1rem 0; padding-top: 0.5rem; }
        .section-title { font-weight: 700; color: #4e73df; font-size: 1.1rem; margin-bottom: 1rem; }
        
        .status-badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; }
        .status-active { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        
        .card-footer-actions { text-align: right; padding-top: 1rem; border-top: 1px solid #e3e6f0; margin-top: 1.5rem; }
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
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="m-0 font-weight-bold text-primary">Detail Tool Drawing Tooling</h4>
                        <a href="<?= base_url('Tool_engineering/tool_draw_tooling'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <div class="card-body">
                        <?php
                        $product_name = isset($drawing['PART_NAME']) ? $drawing['PART_NAME'] : '';
                        $tool_name = isset($drawing['TC_NAME']) ? $drawing['TC_NAME'] : '';
                        $drawing_no = isset($drawing['ML_TOOL_DRAW_NO']) ? $drawing['ML_TOOL_DRAW_NO'] : '';
                        $revision = isset($drawing['MLR_REV']) ? (int)$drawing['MLR_REV'] : 0;
                        $maker_name = isset($drawing['MAKER_NAME']) ? $drawing['MAKER_NAME'] : '';
                        $min_qty = isset($drawing['MLR_MIN_QTY']) ? (int)$drawing['MLR_MIN_QTY'] : 0;
                        $replenish_qty = isset($drawing['MLR_REPLENISH_QTY']) ? (int)$drawing['MLR_REPLENISH_QTY'] : 0;
                        $process_name = isset($drawing['OPERATION_NAME']) ? $drawing['OPERATION_NAME'] : '';
                        $price = isset($drawing['MLR_PRICE']) ? (float)$drawing['MLR_PRICE'] : 0;
                        $tool_life = isset($drawing['MLR_STD_TL_LIFE']) ? $drawing['MLR_STD_TL_LIFE'] : '';
                        $std_rework = isset($drawing['MLR_STD_REWORK']) ? $drawing['MLR_STD_REWORK'] : '';
                        $description = isset($drawing['MLR_DESC']) ? $drawing['MLR_DESC'] : '';
                        $status_val = isset($drawing['MLR_STATUS']) ? (int)$drawing['MLR_STATUS'] : 0;
                        $status_text = $status_val === 2 ? 'Active' : ($status_val === 1 ? 'Pending' : 'Inactive');
                        $status_class = $status_val === 2 ? 'status-active' : ($status_val === 1 ? 'status-pending' : 'status-inactive');
                        $effective_date = isset($drawing['MLR_EFFECTIVE_DATE']) && !empty($drawing['MLR_EFFECTIVE_DATE']) ? date('d/m/Y', strtotime($drawing['MLR_EFFECTIVE_DATE'])) : '';
                        $change_summary = isset($drawing['MLR_CHANGE_SUMMARY']) ? $drawing['MLR_CHANGE_SUMMARY'] : '';
                        $drawing_file = isset($drawing['MLR_DRAWING']) ? $drawing['MLR_DRAWING'] : '';
                        $material_name = isset($drawing['MAT_NAME']) ? $drawing['MAT_NAME'] : '';
                        $sketch = isset($drawing['MLR_SKETCH']) ? $drawing['MLR_SKETCH'] : '';
                        ?>

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="detail-row">
                                    <span class="detail-label">Product</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Tool Name</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($tool_name, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Tool Drawing No.</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($drawing_no, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Revision</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= $revision; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Maker</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($maker_name, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Min Quantity</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= $min_qty; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Replenish Quantity</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= $replenish_qty; ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Process</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($process_name, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="detail-row">
                                    <span class="detail-label">Price</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= number_format($price, 2); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Standard Tool Life</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($tool_life, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Standard Rework</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($std_rework, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Description</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><span class="status-badge <?= $status_class; ?>"><?= $status_text; ?></span></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Effective Date</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($effective_date, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Change Summary</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($change_summary, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="section-divider"></div>
                        <h5 class="section-title">Additional Information</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-row">
                                    <span class="detail-label">Drawing</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($drawing_file, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Material</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($material_name, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-row">
                                    <span class="detail-label">Sketch</span>
                                    <span class="detail-colon">:</span>
                                    <span class="detail-value"><?= htmlspecialchars($sketch, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="card-footer-actions">
                            <a href="<?= base_url('Tool_engineering/tool_draw_tooling/edit_page/' . $drawing['MLR_ID']); ?>" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Shared Tool Section -->
                <div class="card mb-3" id="shared-tool-card">
                    <div class="card-header">
                        <h5 class="m-0 font-weight-bold text-primary">Shared Tool</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="60">ID</th>
                                        <th>Product</th>
                                        <th>Tool BOM</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($tool_bom_list)): ?>
                                        <?php $no = 1; foreach ($tool_bom_list as $bom): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td><?= htmlspecialchars(isset($bom['PRODUCT']) ? $bom['PRODUCT'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars(isset($bom['TOOL_BOM']) ? $bom['TOOL_BOM'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Tidak ada Tool BOM yang menggunakan tool drawing ini.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
