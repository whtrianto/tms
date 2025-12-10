<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        html, body, #content-wrapper { color: #000; }
        .card, .table, label, .form-text, .dataTables_wrapper { color: #000; }
        .table td, .table th { padding: 0.4rem 0.45rem !important; font-size: 0.88rem; }
        .action-buttons { display: flex; gap: 6px; flex-wrap: wrap; justify-content: center; }
        .small-muted { font-size: 0.82rem; color: #6c757d; }
        .info-label { font-weight: 600; color: #495057; }
        .info-value { 
            color: #212529; 
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            background-color: #f8f9fa;
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
                            <h4 class="m-0 font-weight-bold text-primary">Detail Tool BOM</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($bom['ID']); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('tool_engineering/tool_bom_tooling'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                         <!-- Trial BOM Checkbox -->
                         <div class="form-row">
                                <div class="form-group col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="IS_TRIAL_BOM" id="isTrialBom" value="1" <?= (isset($bom['IS_TRIAL_BOM']) && ((int)$bom['IS_TRIAL_BOM'] === 1 || $bom['IS_TRIAL_BOM'] === true)) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="isTrialBom">
                                            <strong>Trial BOM</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <!-- BOM Information (Read-only) -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="info-label">Product</label>
                                <div class="info-value">
                                    <?php
                                    $product_name = '';
                                    if (isset($bom['PRODUCT_ID']) && (int)$bom['PRODUCT_ID'] > 0) {
                                        foreach ($products as $p) {
                                            if ((int)$p['PRODUCT_ID'] === (int)$bom['PRODUCT_ID']) {
                                                $product_name = $p['PRODUCT_NAME'];
                                                break;
                                            }
                                        }
                                    }
                                    if ($product_name === '' && isset($bom['PRODUCT'])) {
                                        $product_name = $bom['PRODUCT'];
                                    }
                                    echo htmlspecialchars($product_name !== '' ? $product_name : '-', ENT_QUOTES, 'UTF-8');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="info-label">Tool BOM</label>
                                <div class="info-value"><?= htmlspecialchars(isset($bom['TOOL_BOM']) ? $bom['TOOL_BOM'] : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="info-label">Revision</label>
                                <div class="info-value"><?= htmlspecialchars(isset($bom['REVISION']) ? $bom['REVISION'] : '0', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="info-label">Process</label>
                                <div class="info-value">
                                    <?php
                                    $process_name = '';
                                    if (isset($bom['PROCESS_ID']) && (int)$bom['PROCESS_ID'] > 0) {
                                        foreach ($operations as $o) {
                                            if ((int)$o['OPERATION_ID'] === (int)$bom['PROCESS_ID']) {
                                                $process_name = $o['OPERATION_NAME'];
                                                break;
                                            }
                                        }
                                    }
                                    echo htmlspecialchars($process_name !== '' ? $process_name : '-', ENT_QUOTES, 'UTF-8');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="info-label">Machine Group</label>
                                <div class="info-value">
                                    <?php
                                    $machine_group_name = '';
                                    if (isset($bom['MACHINE_GROUP_ID']) && (int)$bom['MACHINE_GROUP_ID'] > 0) {
                                        foreach ($machine_groups as $mg) {
                                            if ((int)$mg['MACHINE_ID'] === (int)$bom['MACHINE_GROUP_ID']) {
                                                $machine_group_name = $mg['MACHINE_NAME'];
                                                break;
                                            }
                                        }
                                    }
                                    if ($machine_group_name === '' && isset($bom['MACHINE_GROUP'])) {
                                        $machine_group_name = $bom['MACHINE_GROUP'];
                                    }
                                    echo htmlspecialchars($machine_group_name !== '' ? $machine_group_name : '-', ENT_QUOTES, 'UTF-8');
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="info-label">Description</label>
                                <div class="info-value"><?= htmlspecialchars(isset($bom['DESCRIPTION']) && $bom['DESCRIPTION'] !== '' ? $bom['DESCRIPTION'] : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="info-label">Status</label>
                                <div class="info-value">
                                    <?php
                                    $statusVal = 1;
                                    if (isset($bom['STATUS'])) {
                                        $st = strtoupper((string)$bom['STATUS']);
                                        if ($st === 'INACTIVE' || $st === '0') $statusVal = 0;
                                        elseif ($st === 'PENDING' || $st === '2') $statusVal = 2;
                                    }
                                    $statusText = 'Active';
                                    $statusBadge = '<span class="badge badge-success">Active</span>';
                                    if ($statusVal === 0) {
                                        $statusText = 'Inactive';
                                        $statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                                    } elseif ($statusVal === 2) {
                                        $statusText = 'Pending';
                                        $statusBadge = '<span class="badge badge-warning">Pending</span>';
                                    }
                                    echo $statusBadge;
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="info-label">Effective Date</label>
                                <div class="info-value">
                                    <?php
                                    $eff = isset($bom['EFFECTIVE_DATE']) ? $bom['EFFECTIVE_DATE'] : '';
                                    if ($eff !== '') {
                                        // Format date if needed
                                        echo htmlspecialchars(substr($eff, 0, 10), ENT_QUOTES, 'UTF-8');
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label class="info-label">Change Summary</label>
                                <div class="info-value"><?= htmlspecialchars(isset($bom['CHANGE_SUMMARY']) && $bom['CHANGE_SUMMARY'] !== '' ? $bom['CHANGE_SUMMARY'] : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="info-label">Drawing</label>
                                <div class="info-value">
                                    <?php if (!empty($bom['DRAWING'])): ?>
                                        <a href="<?= base_url('tool_engineering/img/' . htmlspecialchars($bom['DRAWING'], ENT_QUOTES, 'UTF-8')); ?>" target="_blank" class="text-primary">
                                            <?= htmlspecialchars($bom['DRAWING'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="info-label">Modified Date</label>
                                <div class="info-value">
                                    <?php
                                    $modified = isset($bom['MODIFIED_DATE']) ? $bom['MODIFIED_DATE'] : '';
                                    echo htmlspecialchars($modified !== '' ? $modified : '-', ENT_QUOTES, 'UTF-8');
                                    ?>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="info-label">Modified By</label>
                                <div class="info-value"><?= htmlspecialchars(isset($bom['MODIFIED_BY']) && $bom['MODIFIED_BY'] !== '' ? $bom['MODIFIED_BY'] : '-', ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information (Read-only) -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0 font-weight-bold text-primary">Additional Information (Tool Drawing Engin)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-additional" class="table table-bordered table-striped w-100 text-center">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>ID</th>
                                        <th>Drawing</th>
                                        <th>Tool Drawing No.</th>
                                        <th>Tool Name</th>
                                        <th>Revision</th>
                                        <th>Status</th>
                                        <th>Quantity</th>
                                        <th>Std. Quantity</th>
                                        <th>Sequence</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($additional_info)): ?>
                                        <?php $no = 1; foreach ($additional_info as $row): 
                                            $statusBadge = '<span class="badge badge-success">Active</span>';
                                            if (isset($row['TD_STATUS'])) {
                                                $rst = (int)$row['TD_STATUS'];
                                                if ($rst === 0) $statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                                                elseif ($rst === 2) $statusBadge = '<span class="badge badge-warning">Pending</span>';
                                            }
                                            
                                            // Resolve tool name
                                            $tool_display_name = isset($row['TD_TOOL_NAME']) ? $row['TD_TOOL_NAME'] : '';
                                            if (is_numeric($tool_display_name)) {
                                                foreach ($tools as $t) {
                                                    if ((int)$t['TOOL_ID'] === (int)$tool_display_name) {
                                                        $tool_display_name = $t['TOOL_NAME'];
                                                        break;
                                                    }
                                                }
                                            }
                                        ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= htmlspecialchars($row['TD_ID']); ?></td>
                                                <td>
                                                    <?php if (!empty($row['TD_DRAWING_NO'])): ?>
                                                        <a href="<?= base_url('tool_engineering/img/' . htmlspecialchars($row['TD_DRAWING_NO'], ENT_QUOTES, 'UTF-8')); ?>" target="_blank" class="text-primary">
                                                            <?= htmlspecialchars($row['TD_DRAWING_NO'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars(isset($row['TD_DRAWING_NO']) ? $row['TD_DRAWING_NO'] : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars($tool_display_name !== '' ? $tool_display_name : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars(isset($row['TD_REVISION']) ? $row['TD_REVISION'] : '0', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= $statusBadge; ?></td>
                                                <td><?= htmlspecialchars(isset($row['TD_MIN_QTY']) && $row['TD_MIN_QTY'] !== '' ? $row['TD_MIN_QTY'] : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars(isset($row['TD_REPLENISH_QTY']) && $row['TD_REPLENISH_QTY'] !== '' ? $row['TD_REPLENISH_QTY'] : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars(isset($row['TD_SEQUENCE']) && $row['TD_SEQUENCE'] !== '' ? $row['TD_SEQUENCE'] : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars(isset($row['TD_DESCRIPTION']) && $row['TD_DESCRIPTION'] !== '' ? $row['TD_DESCRIPTION'] : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="11" class="text-center text-muted">No additional information available.</td>
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
<script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js'); ?>"></script>
<script>
(function($){
    $(function(){
        var table = $('#table-additional').DataTable({
            lengthMenu: [[10,25,50,-1],[10,25,50,"ALL"]],
            pageLength: 10,
            order: [[1,'desc']],
            autoWidth: false,
            columnDefs: [
                { orderable:false, targets:[0] },
                { width: '50px', targets:0 },
                { width: '70px', targets:1 }
            ]
        });
    });
})(jQuery);
</script>
</body>
</html>

