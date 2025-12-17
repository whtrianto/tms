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
        #container-wrapper { padding-bottom: 15rem !important; margin-bottom: 5rem !important; }
        .card { margin-bottom: 2rem; }
        #content { padding-bottom: 15rem !important; min-height: calc(100vh - 56px); }
        #shared-tool-card { margin-bottom: 10rem !important; }
        #shared-tool-card .card-body { padding-bottom: 4rem !important; }
        #shared-tool-card .table-responsive { margin-bottom: 2rem; }
        .detail-row { margin-bottom: 0.5rem; }
        .detail-label { font-weight: 600; color: #555; }
        .detail-value { color: #000; }
        .section-title { font-weight: bold; margin-top: 1.5rem; margin-bottom: 1rem; border-bottom: 1px solid #ddd; padding-bottom: 0.5rem; }
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
                        <div>
                            <h4 class="m-0 font-weight-bold text-primary">Detail Tool Drawing Tooling</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($drawing['MLR_ID']); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_draw_tooling'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                            <a href="<?= base_url('Tool_engineering/tool_draw_tooling/edit_page/' . $drawing['MLR_ID']); ?>" class="btn btn-sm btn-primary shadow-sm">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get values from drawing data
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
                        $effective_date = isset($drawing['MLR_EFFECTIVE_DATE']) && !empty($drawing['MLR_EFFECTIVE_DATE']) ? date('d/m/Y', strtotime($drawing['MLR_EFFECTIVE_DATE'])) : '';
                        $change_summary = isset($drawing['MLR_CHANGE_SUMMARY']) ? $drawing['MLR_CHANGE_SUMMARY'] : '';
                        $drawing_file = isset($drawing['MLR_DRAWING']) ? $drawing['MLR_DRAWING'] : '';
                        $material_name = isset($drawing['MAT_NAME']) ? $drawing['MAT_NAME'] : '';
                        $sketch = isset($drawing['MLR_SKETCH']) ? $drawing['MLR_SKETCH'] : '';
                        ?>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Product</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Tool Name</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($tool_name, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Tool Drawing No.</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($drawing_no, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Revision</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= $revision; ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Maker</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($maker_name, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Min Quantity</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= $min_qty; ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Replenish Quantity</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= $replenish_qty; ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Process</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($process_name, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Price</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= number_format($price, 2); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Standard Tool Life</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($tool_life, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Standard Rework</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($std_rework, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Description</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Status</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($status_text, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Effective Date</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($effective_date, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Change Summary</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($change_summary, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <!-- Additional Information Section -->
                        <h5 class="section-title">Additional Information</h5>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Drawing</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($drawing_file, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Material</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($material_name, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>

                        <div class="row detail-row">
                            <div class="col-md-3 detail-label">Sketch</div>
                            <div class="col-md-1">:</div>
                            <div class="col-md-8 detail-value"><?= htmlspecialchars($sketch, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Shared Tool Section -->
                <div class="card mb-3 mt-3" id="shared-tool-card">
                    <div class="card-header">
                        <h5 class="m-0 font-weight-bold text-primary">Shared Tool</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
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
            <div style="height: 15rem; min-height: 15rem; clear: both;"></div>
            <?= isset($modal_logout) ? $modal_logout : ''; ?>
        </div>
        <?= isset($footer) ? $footer : ''; ?>
    </div>
</div>

<?= isset($foot) ? $foot : ''; ?>
</body>
</html>

