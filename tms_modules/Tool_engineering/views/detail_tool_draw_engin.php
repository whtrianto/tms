<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <style>
        html, body, #content-wrapper { color: #000; }
        .card, .table, label, .form-text { color: #000; }
        .info-label { font-weight: 600; color: #495057; }
        .info-value { color: #212529; }
        .readonly-field {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            min-height: 38px;
            display: flex;
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
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="m-0 font-weight-bold text-primary">Tool Drawing Engineering Detail</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($drawing['TD_ID']); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('tool_engineering/tool_bom_tooling'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="info-label">Product</label>
                                <div class="readonly-field">
                                    <?php
                                    $product_name = '';
                                    if (isset($drawing['TD_PRODUCT_ID']) && (int)$drawing['TD_PRODUCT_ID'] > 0) {
                                        foreach ($products as $p) {
                                            if ((int)$p['PRODUCT_ID'] === (int)$drawing['TD_PRODUCT_ID']) {
                                                $product_name = $p['PRODUCT_NAME'];
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <span class="info-value"><?= htmlspecialchars($product_name !== '' ? $product_name : 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="info-label">Process</label>
                                <div class="readonly-field">
                                    <?php
                                    $operation_name = '';
                                    if (isset($drawing['TD_PROCESS_ID']) && (int)$drawing['TD_PROCESS_ID'] > 0) {
                                        foreach ($operations as $o) {
                                            if ((int)$o['OPERATION_ID'] === (int)$drawing['TD_PROCESS_ID']) {
                                                $operation_name = $o['OPERATION_NAME'];
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <span class="info-value"><?= htmlspecialchars($operation_name !== '' ? $operation_name : 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="info-label">Drawing</label>
                                <div class="readonly-field">
                                    <?php if (!empty($drawing['TD_DRAWING_NO'])): 
                                        $fileUrl = base_url('tool_engineering/img/' . $drawing['TD_DRAWING_NO']);
                                    ?>
                                        <a href="<?= $fileUrl; ?>" target="_blank" class="info-value">
                                            <?= htmlspecialchars($drawing['TD_DRAWING_NO'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="info-value text-muted">N/A</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="info-label">Tool</label>
                                <div class="readonly-field">
                                    <?php
                                    $tool_name = '';
                                    if (isset($drawing['TD_TOOL_ID']) && (int)$drawing['TD_TOOL_ID'] > 0) {
                                        foreach ($tools as $t) {
                                            if ((int)$t['TOOL_ID'] === (int)$drawing['TD_TOOL_ID']) {
                                                $tool_name = $t['TOOL_NAME'];
                                                break;
                                            }
                                        }
                                    } elseif (isset($drawing['TD_TOOL_NAME']) && $drawing['TD_TOOL_NAME'] !== '') {
                                        if (is_numeric($drawing['TD_TOOL_NAME'])) {
                                            foreach ($tools as $t) {
                                                if ((int)$t['TOOL_ID'] === (int)$drawing['TD_TOOL_NAME']) {
                                                    $tool_name = $t['TOOL_NAME'];
                                                    break;
                                                }
                                            }
                                        } else {
                                            $tool_name = $drawing['TD_TOOL_NAME'];
                                        }
                                    }
                                    ?>
                                    <span class="info-value"><?= htmlspecialchars($tool_name !== '' ? $tool_name : 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="info-label">Revision</label>
                                <div class="readonly-field">
                                    <span class="info-value"><?= htmlspecialchars(isset($drawing['TD_REVISION']) ? $drawing['TD_REVISION'] : '0', ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="info-label">Status</label>
                                <div class="readonly-field">
                                    <?php
                                    $status_text = 'N/A';
                                    if (isset($drawing['TD_STATUS'])) {
                                        $status_text = ((int)$drawing['TD_STATUS'] === 1) ? 'Active' : 'Inactive';
                                    }
                                    ?>
                                    <span class="info-value"><?= htmlspecialchars($status_text, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="info-label">Material</label>
                                <div class="readonly-field">
                                    <?php
                                    $material_name = '';
                                    if (isset($drawing['TD_MATERIAL_ID']) && (int)$drawing['TD_MATERIAL_ID'] > 0) {
                                        foreach ($materials as $m) {
                                            if ((int)$m['MATERIAL_ID'] === (int)$drawing['TD_MATERIAL_ID']) {
                                                $material_name = $m['MATERIAL_NAME'];
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <span class="info-value"><?= htmlspecialchars($material_name !== '' ? $material_name : 'N/A', ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                        </div>

                        <?php if (isset($drawing['TD_EFFECTIVE_DATE']) || isset($drawing['TD_MODIFIED_DATE']) || isset($drawing['TD_MODIFIED_BY'])): ?>
                        <div class="form-row">
                            <?php if (isset($drawing['TD_EFFECTIVE_DATE'])): ?>
                            <div class="form-group col-md-4">
                                <label class="info-label">Effective Date</label>
                                <div class="readonly-field">
                                    <span class="info-value"><?= htmlspecialchars($drawing['TD_EFFECTIVE_DATE'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (isset($drawing['TD_MODIFIED_DATE'])): ?>
                            <div class="form-group col-md-4">
                                <label class="info-label">Modified Date</label>
                                <div class="readonly-field">
                                    <span class="info-value"><?= htmlspecialchars($drawing['TD_MODIFIED_DATE'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (isset($drawing['TD_MODIFIED_BY'])): ?>
                            <div class="form-group col-md-4">
                                <label class="info-label">Modified By</label>
                                <div class="readonly-field">
                                    <span class="info-value"><?= htmlspecialchars($drawing['TD_MODIFIED_BY'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Shared Tool Section -->
                <div class="card mb-3 mt-3">
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
                                        <?php 
                                        // Get product name for display
                                        $current_product_name = '';
                                        if (isset($drawing['TD_PRODUCT_ID']) && (int)$drawing['TD_PRODUCT_ID'] > 0) {
                                            foreach ($products as $p) {
                                                if ((int)$p['PRODUCT_ID'] === (int)$drawing['TD_PRODUCT_ID']) {
                                                    $current_product_name = $p['PRODUCT_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        ?>
                                        <?php foreach ($tool_bom_list as $bom): ?>
                                            <tr>
                                                <td class="text-center"><?= htmlspecialchars(isset($bom['ID']) ? (int)$bom['ID'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($current_product_name !== '' ? $current_product_name : (isset($bom['PRODUCT']) ? $bom['PRODUCT'] : ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars(isset($bom['TOOL_BOM']) ? $bom['TOOL_BOM'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No shared tool BOM found for this product.</td>
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

