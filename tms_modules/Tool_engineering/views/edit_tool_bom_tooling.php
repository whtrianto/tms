<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        html, body, #content-wrapper { color: #000; }
        .card, .table, label, .form-text, .dataTables_wrapper { color: #000; }
        .label-required::after { content: " *"; color: #dc3545; font-weight: 600; }
        .is-invalid + .invalid-feedback { display: block; }
        .table td, .table th { padding: 0.4rem 0.45rem !important; font-size: 0.88rem; }
        .action-buttons { display: flex; gap: 6px; flex-wrap: wrap; justify-content: center; }
        .small-muted { font-size: 0.82rem; color: #6c757d; }
        .form-control[readonly], .form-control:disabled { background-color: #e9ecef; cursor: not-allowed; }
        select.form-control[readonly], select.form-control:disabled { background-color: #e9ecef; cursor: not-allowed; }
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
                            <h4 class="m-0 font-weight-bold text-primary">Edit Tool BOM</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($bom['ID']); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_bom_tooling'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formBom" method="post" action="<?= base_url('Tool_engineering/tool_bom_tooling/submit_data'); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="EDIT">
                            <input type="hidden" name="ID" value="<?= htmlspecialchars($bom['ID']); ?>">
                            
                            <!-- Trial BOM Checkbox -->
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="IS_TRIAL_BOM" id="isTrialBom" value="1" disabled <?= (isset($bom['IS_TRIAL_BOM']) && ((int)$bom['IS_TRIAL_BOM'] === 1 || $bom['IS_TRIAL_BOM'] === true)) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="isTrialBom">
                                            <strong>Trial BOM</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="label-required">Product</label>
                                    <select name="PRODUCT_ID_DISPLAY" class="form-control" disabled>
                                        <option value="">-- Select Product --</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= (int)$p['PRODUCT_ID']; ?>" <?= (isset($bom['PRODUCT_ID']) && (int)$bom['PRODUCT_ID'] === (int)$p['PRODUCT_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="PRODUCT_ID" value="<?= htmlspecialchars(isset($bom['PRODUCT_ID']) ? (int)$bom['PRODUCT_ID'] : 0); ?>">
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="label-required">Tool BOM</label>
                                    <input type="text" class="form-control" name="TOOL_BOM" value="<?= htmlspecialchars($bom['TOOL_BOM']); ?>" readonly>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Revision</label>
                                    <input type="number" class="form-control" name="REVISION_DISPLAY" value="<?= htmlspecialchars(isset($bom['REVISION']) ? $bom['REVISION'] : 0); ?>" readonly>
                                    <input type="hidden" name="REVISION" value="<?= htmlspecialchars(isset($bom['REVISION']) ? (int)$bom['REVISION'] : 0); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Process</label>
                                    <select name="PROCESS_ID_DISPLAY" class="form-control" disabled>
                                        <option value="">-- Select Process --</option>
                                        <?php foreach ($operations as $o): ?>
                                            <option value="<?= (int)$o['OPERATION_ID']; ?>" <?= (isset($bom['PROCESS_ID']) && (int)$bom['PROCESS_ID'] === (int)$o['OPERATION_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($o['OPERATION_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="PROCESS_ID" value="<?= htmlspecialchars(isset($bom['PROCESS_ID']) ? (int)$bom['PROCESS_ID'] : 0); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Machine Group</label>
                                    <select name="MACHINE_GROUP_ID_DISPLAY" class="form-control" disabled>
                                        <option value="">-- Select Machine Group --</option>
                                        <?php foreach ($machine_groups as $mg): ?>
                                            <option value="<?= (int)$mg['MACHINE_ID']; ?>" <?= (isset($bom['MACHINE_GROUP_ID']) && (int)$bom['MACHINE_GROUP_ID'] === (int)$mg['MACHINE_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($mg['MACHINE_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="MACHINE_GROUP_ID" value="<?= htmlspecialchars(isset($bom['MACHINE_GROUP_ID']) ? (int)$bom['MACHINE_GROUP_ID'] : 0); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Description</label>
                                    <input type="text" class="form-control" name="DESCRIPTION_DISPLAY" value="<?= htmlspecialchars(isset($bom['DESCRIPTION']) ? $bom['DESCRIPTION'] : ''); ?>" readonly>
                                    <input type="hidden" name="DESCRIPTION" value="<?= htmlspecialchars(isset($bom['DESCRIPTION']) ? $bom['DESCRIPTION'] : ''); ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Status</label>
                                    <?php
                                        $statusVal = 1;
                                        if (isset($bom['STATUS'])) {
                                            $st = strtoupper((string)$bom['STATUS']);
                                            if ($st === 'INACTIVE' || $st === '0') $statusVal = 0;
                                            elseif ($st === 'PENDING' || $st === '2') $statusVal = 2;
                                        }
                                    ?>
                                    <select name="STATUS_DISPLAY" class="form-control" disabled>
                                        <option value="1" <?= $statusVal === 1 ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?= $statusVal === 0 ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="2" <?= $statusVal === 2 ? 'selected' : ''; ?>>Pending</option>
                                    </select>
                                    <input type="hidden" name="STATUS" value="<?= htmlspecialchars($statusVal); ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Effective Date</label>
                                    <?php
                                        $eff = isset($bom['EFFECTIVE_DATE']) ? substr($bom['EFFECTIVE_DATE'], 0, 10) : '';
                                    ?>
                                    <input type="date" class="form-control" name="EFFECTIVE_DATE_DISPLAY" value="<?= htmlspecialchars($eff); ?>" readonly>
                                    <input type="hidden" name="EFFECTIVE_DATE" value="<?= htmlspecialchars($eff); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Change Summary</label>
                                    <textarea name="CHANGE_SUMMARY_DISPLAY" rows="3" class="form-control" readonly><?= htmlspecialchars(isset($bom['CHANGE_SUMMARY']) ? $bom['CHANGE_SUMMARY'] : 'Additional Information'); ?></textarea>
                                    <input type="hidden" name="CHANGE_SUMMARY" value="<?= htmlspecialchars(isset($bom['CHANGE_SUMMARY']) ? $bom['CHANGE_SUMMARY'] : ''); ?>">
                                    <input type="hidden" name="IS_TRIAL_BOM" value="<?= htmlspecialchars(isset($bom['IS_TRIAL_BOM']) && (int)$bom['IS_TRIAL_BOM'] === 1 ? '1' : '0'); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Drawing</label>
                                    <?php if (!empty($bom['DRAWING'])): ?>
                                        <div class="form-control" style="background-color: #e9ecef; cursor: not-allowed;">
                                            <?= htmlspecialchars($bom['DRAWING']); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="form-control" style="background-color: #e9ecef; cursor: not-allowed;">-</div>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Sketch</label>
                                    <input type="file" name="SKETCH_FILE" class="form-control" accept="*">
                                    <?php if (!empty($bom['SKETCH'])): ?>
                                        <div class="small-muted mt-1">Current: <?= htmlspecialchars($bom['SKETCH']); ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Pilih file untuk mengunggah sketch. Biarkan kosong jika tidak mengganti file.</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Save
                                </button>
                                <a href="<?= base_url('Tool_engineering/tool_bom_tooling'); ?>" class="btn btn-secondary ml-2">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

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
                                    <?php $no = 1; foreach ($additional_info as $row): 
                                        $statusBadge = '<span class="badge badge-success">Active</span>';
                                        if (isset($row['TD_STATUS'])) {
                                            $rst = (int)$row['TD_STATUS'];
                                            if ($rst === 0) $statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                                            elseif ($rst === 2) $statusBadge = '<span class="badge badge-warning">Pending</span>';
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($row['TD_ID']); ?></td>
                                            <td><?= htmlspecialchars(isset($row['TD_DRAWING_NO']) ? $row['TD_DRAWING_NO'] : ''); ?></td>
                                            <td><?= htmlspecialchars(isset($row['TD_DRAWING_NO']) ? $row['TD_DRAWING_NO'] : ''); ?></td>
                                            <td><?= htmlspecialchars(isset($row['TD_TOOL_NAME']) ? $row['TD_TOOL_NAME'] : ''); ?></td>
                                            <td><?= htmlspecialchars(isset($row['TD_REVISION']) ? $row['TD_REVISION'] : 0); ?></td>
                                            <td><?= $statusBadge; ?></td>
                                            <td><?= htmlspecialchars(isset($row['TD_MIN_QTY']) ? $row['TD_MIN_QTY'] : ''); ?></td>
                                            <td><?= htmlspecialchars(isset($row['TD_REPLENISH_QTY']) ? $row['TD_REPLENISH_QTY'] : ''); ?></td>
                                            <td><?= htmlspecialchars(isset($row['TD_SEQUENCE']) ? $row['TD_SEQUENCE'] : ''); ?></td>
                                            <td><?= htmlspecialchars(isset($row['TD_REMARKS']) && $row['TD_REMARKS'] !== '' ? $row['TD_REMARKS'] : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
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
            order: [], // No default sorting - maintain order from database (TB_SEQ)
            autoWidth: false,
            columnDefs: [
                { orderable:false, targets:[0] }, // No. column not sortable (it's auto-increment)
                { width: '50px', targets:0 },
                { width: '70px', targets:1 }
            ]
        });

        // BOM submit
        $('#formBom').on('submit', function(e){
            e.preventDefault();
            var toolBom = $.trim($('[name="TOOL_BOM"]').val());
            if (toolBom === '') {
                $('[name="TOOL_BOM"]').addClass('is-invalid');
                return;
            }
            $('[name="TOOL_BOM"]').removeClass('is-invalid');
            var fd = new FormData(this);
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: fd,
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false,
                timeout: 30000
            }).done(function(res){
                if (res && res.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Tool BOM berhasil diubah');
                    } else {
                        alert(res.message || 'Tool BOM berhasil diubah');
                    }
                    setTimeout(function(){
                        window.location.href = '<?= base_url("Tool_engineering/tool_bom_tooling"); ?>';
                    }, 600);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(res && res.message ? res.message : 'Gagal menyimpan Tool BOM');
                    } else {
                        alert(res && res.message ? res.message : 'Gagal menyimpan Tool BOM');
                    }
                }
            }).fail(function(xhr, status){
                if (typeof toastr !== 'undefined') {
                    toastr.error('Gagal menyimpan: ' + status);
                } else {
                    alert('Gagal menyimpan: ' + status);
                }
            });
        });
    });
})(jQuery);
</script>
</body>
</html>
