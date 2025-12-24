﻿<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <style>
        /* konsisten: teks hitam */
        html, body, #content-wrapper { color: #000; }
        .table td, .table th { color: #000 !important; }
        .card, .table, label, .form-text { color: #000; }
        .label-required::after { content: " *"; color: #dc3545; font-weight: 600; }
        .is-invalid + .invalid-feedback { display: block; }
        /* Keep navbar pinned */
        .navbar { position: sticky; top: 0; z-index: 1030; }
        /* Fix footer spacing */
        #content-wrapper { min-height: calc(100vh - 56px); }
        #container-wrapper { 
            padding-bottom: 15rem !important; 
            margin-bottom: 5rem !important; 
        }
        .card { margin-bottom: 2rem; }
        /* Ensure footer doesn't overlap - extra spacing for Shared Tool section */
        #content {
            padding-bottom: 15rem !important;
            min-height: calc(100vh - 56px);
        }
        /* Extra margin for Shared Tool card to prevent footer overlap */
        #shared-tool-card {
            margin-bottom: 10rem !important;
        }
        #shared-tool-card .card-body {
            padding-bottom: 4rem !important;
        }
        /* Ensure table inside Shared Tool card has spacing */
        #shared-tool-card .table-responsive {
            margin-bottom: 2rem;
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
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="m-0 font-weight-bold text-primary">View Revision - Tool Drawing Engineering</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($drawing['TD_ID']); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_draw_engin'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="formToolDrawingView">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Product</label>
                                    <input type="text" class="form-control" readonly value="<?php 
                                        $product_name = '';
                                        if (isset($drawing['TD_PRODUCT_ID'])) {
                                            foreach ($products as $p) {
                                                if ((int)$p['PRODUCT_ID'] === (int)$drawing['TD_PRODUCT_ID']) {
                                                    $product_name = $p['PRODUCT_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');
                                    ?>">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Process</label>
                                    <input type="text" class="form-control" readonly value="<?php 
                                        $process_name = '';
                                        if (isset($drawing['TD_PROCESS_ID'])) {
                                            foreach ($operations as $o) {
                                                if ((int)$o['OPERATION_ID'] === (int)$drawing['TD_PROCESS_ID']) {
                                                    $process_name = $o['OPERATION_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        echo htmlspecialchars($process_name, ENT_QUOTES, 'UTF-8');
                                    ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Tool Draw No</label>
                                    <input type="text" class="form-control" readonly value="<?= htmlspecialchars(isset($drawing['TD_DRAWING_NO']) ? $drawing['TD_DRAWING_NO'] : ''); ?>">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Tool</label>
                                    <input type="text" class="form-control" readonly value="<?php 
                                        $tool_name = '';
                                        $selected_tool_id = isset($drawing['TD_TOOL_ID']) ? (int)$drawing['TD_TOOL_ID'] : 0;
                                        if ($selected_tool_id > 0) {
                                            foreach ($tools as $t) {
                                                if ((int)$t['TOOL_ID'] === $selected_tool_id) {
                                                    $tool_name = $t['TOOL_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        echo htmlspecialchars($tool_name, ENT_QUOTES, 'UTF-8');
                                    ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Drawing File</label>
                                    <input type="text" class="form-control" readonly value="<?= htmlspecialchars(isset($drawing['TD_DRAWING_NO']) ? $drawing['TD_DRAWING_NO'] : ''); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Revision</label>
                                    <input type="text" class="form-control" readonly value="<?= htmlspecialchars(isset($drawing['TD_REVISION']) ? $drawing['TD_REVISION'] : 0); ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Status</label>
                                    <input type="text" class="form-control" readonly value="<?php 
                                        $status_text = 'Inactive';
                                        if (isset($drawing['TD_STATUS'])) {
                                            $st = (int)$drawing['TD_STATUS'];
                                            if ($st === 2) $status_text = 'Active';
                                            elseif ($st === 1) $status_text = 'Pending';
                                        }
                                        echo $status_text;
                                    ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Material</label>
                                    <input type="text" class="form-control" readonly value="<?php 
                                        $material_name = '';
                                        if (isset($drawing['TD_MATERIAL_ID'])) {
                                            foreach ($materials as $m) {
                                                if ((int)$m['MATERIAL_ID'] === (int)$drawing['TD_MATERIAL_ID']) {
                                                    $material_name = $m['MATERIAL_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        echo htmlspecialchars($material_name, ENT_QUOTES, 'UTF-8');
                                    ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Maker</label>
                                    <input type="text" class="form-control" readonly value="<?php 
                                        $maker_name = '';
                                        $makers = isset($makers) ? $makers : array();
                                        if (isset($drawing['TD_MAKER_ID'])) {
                                            foreach ($makers as $mk) {
                                                if ((int)$mk['MAKER_ID'] === (int)$drawing['TD_MAKER_ID']) {
                                                    $maker_name = $mk['MAKER_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        echo htmlspecialchars($maker_name, ENT_QUOTES, 'UTF-8');
                                    ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Machine Group</label>
                                    <input type="text" class="form-control" readonly value="<?php 
                                        $machine_name = '';
                                        $machine_groups = isset($machine_groups) ? $machine_groups : array();
                                        if (isset($drawing['TD_MACG_ID'])) {
                                            foreach ($machine_groups as $mg) {
                                                if ((int)$mg['MACHINE_ID'] === (int)$drawing['TD_MACG_ID']) {
                                                    $machine_name = $mg['MACHINE_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        echo htmlspecialchars($machine_name, ENT_QUOTES, 'UTF-8');
                                    ?>">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Effective Date</label>
                                    <input type="text" class="form-control" readonly value="<?= isset($drawing['TD_EFFECTIVE_DATE']) && !empty($drawing['TD_EFFECTIVE_DATE']) ? date('Y-m-d', strtotime($drawing['TD_EFFECTIVE_DATE'])) : ''; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shared Tool Section -->
                <div class="card mb-3 mt-3" id="shared-tool-card">
                    <div class="card-header">
                        <h5 class="m-0 font-weight-bold text-primary">Shared Tool (Tool BOM yang menggunakan tool ini)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tool BOM No</th>
                                        <th>Product</th>
                                        <th>Rev</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($tool_bom_list)): ?>
                                        <?php $no = 1; foreach ($tool_bom_list as $bom): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td><?= htmlspecialchars(isset($bom['TOOL_BOM']) ? $bom['TOOL_BOM'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars(isset($bom['PRODUCT']) ? $bom['PRODUCT'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-center"><?= htmlspecialchars(isset($bom['BOM_REV']) ? $bom['BOM_REV'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-center"><?= htmlspecialchars(isset($bom['QTY']) ? $bom['QTY'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Tidak ada Tool BOM yang menggunakan tool drawing ini.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Extra spacer to prevent footer overlap -->
            <div style="height: 15rem; min-height: 15rem; clear: both;"></div>
            <?= isset($modal_logout) ? $modal_logout : ''; ?>
        </div>
        <?= isset($footer) ? $footer : ''; ?>
    </div>
</div>

<?= isset($foot) ? $foot : ''; ?>
</body>
</html>