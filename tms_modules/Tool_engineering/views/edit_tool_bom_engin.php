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
                            <h4 class="m-0 font-weight-bold text-primary">Edit Tool BOM Engineering</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($bom['ID']); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('tool_engineering/tool_bom_engin'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formBom" method="post" action="<?= base_url('Tool_engineering/tool_bom_engin/submit_data'); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="EDIT">
                            <input type="hidden" name="ID" value="<?= htmlspecialchars($bom['ID']); ?>">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="label-required">Product</label>
                                    <select name="PRODUCT_ID" class="form-control">
                                        <option value="">-- Select Product --</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= (int)$p['PRODUCT_ID']; ?>" <?= (isset($bom['PRODUCT_ID']) && (int)$bom['PRODUCT_ID'] === (int)$p['PRODUCT_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="label-required">Tool BOM</label>
                                    <input type="text" class="form-control" name="TOOL_BOM" required value="<?= htmlspecialchars($bom['TOOL_BOM']); ?>">
                                    <div class="invalid-feedback">Tool BOM wajib diisi.</div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Revision</label>
                                    <input type="number" class="form-control" name="REVISION" value="<?= htmlspecialchars(isset($bom['REVISION']) ? $bom['REVISION'] : 0); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Process</label>
                                    <select name="PROCESS_ID" class="form-control">
                                        <option value="">-- Select Process --</option>
                                        <?php foreach ($operations as $o): ?>
                                            <option value="<?= (int)$o['OPERATION_ID']; ?>" <?= (isset($bom['PROCESS_ID']) && (int)$bom['PROCESS_ID'] === (int)$o['OPERATION_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($o['OPERATION_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Machine Group</label>
                                    <select name="MACHINE_GROUP_ID" class="form-control">
                                        <option value="">-- Select Machine Group --</option>
                                        <?php foreach ($machine_groups as $mg): ?>
                                            <option value="<?= (int)$mg['MACHINE_ID']; ?>" <?= (isset($bom['MACHINE_GROUP_ID']) && (int)$bom['MACHINE_GROUP_ID'] === (int)$mg['MACHINE_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($mg['MACHINE_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Description</label>
                                    <input type="text" class="form-control" name="DESCRIPTION" value="<?= htmlspecialchars(isset($bom['DESCRIPTION']) ? $bom['DESCRIPTION'] : ''); ?>">
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
                                    <select name="STATUS" class="form-control">
                                        <option value="1" <?= $statusVal === 1 ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?= $statusVal === 0 ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="2" <?= $statusVal === 2 ? 'selected' : ''; ?>>Pending</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Effective Date</label>
                                    <?php
                                        $eff = isset($bom['EFFECTIVE_DATE']) ? substr($bom['EFFECTIVE_DATE'], 0, 10) : '';
                                    ?>
                                    <input type="date" class="form-control" name="EFFECTIVE_DATE" value="<?= htmlspecialchars($eff); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Change Summary</label>
                                    <textarea name="CHANGE_SUMMARY" rows="3" class="form-control"><?= htmlspecialchars(isset($bom['CHANGE_SUMMARY']) ? $bom['CHANGE_SUMMARY'] : 'Additional Information'); ?></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Drawing</label>
                                    <input type="file" name="DRAWING_FILE" class="form-control" accept="image/*">
                                    <?php if (!empty($bom['DRAWING'])): ?>
                                        <div class="small-muted mt-1">Current: <?= htmlspecialchars($bom['DRAWING']); ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Biarkan kosong jika tidak mengganti file.</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Save BOM</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h5 class="m-0 font-weight-bold text-primary">Additional Information (Tool Drawing Engin)</h5>
                        <button id="btn-add-additional" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Add</button>
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
                                        <th>ACTION</th>
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
                                            <td><?= htmlspecialchars(isset($row['TD_DESCRIPTION']) ? $row['TD_DESCRIPTION'] : ''); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-warning btn-edit-additional" data-edit='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8"); ?>'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger btn-delete-additional" data-id="<?= (int)$row['TD_ID']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal Additional -->
                <div class="modal fade" id="modalAdditional" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Additional Information</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="formAdditional" method="post" action="<?= base_url('Tool_engineering/tool_draw_engin/submit_data'); ?>" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="ADD">
                                    <input type="hidden" name="TD_ID" value="">
                                    <input type="hidden" name="TD_DRAWING_NO_OLD" value="">

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="label-required">Product</label>
                                            <select name="TD_PRODUCT_ID" class="form-control" required>
                                                <option value="">-- Select Product --</option>
                                                <?php foreach ($products as $p): ?>
                                                    <option value="<?= (int)$p['PRODUCT_ID']; ?>" <?= (isset($bom['PRODUCT_ID']) && (int)$bom['PRODUCT_ID'] === (int)$p['PRODUCT_ID']) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="label-required">Process</label>
                                            <select name="TD_PROCESS_ID" class="form-control" required>
                                                <option value="">-- Select Process --</option>
                                                <?php foreach ($operations as $o): ?>
                                                    <option value="<?= (int)$o['OPERATION_ID']; ?>" <?= (isset($bom['PROCESS_ID']) && (int)$bom['PROCESS_ID'] === (int)$o['OPERATION_ID']) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($o['OPERATION_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="label-required">Tool Drawing No.</label>
                                            <input type="text" class="form-control" name="TD_DRAWING_NO" placeholder="e.g. TD DCM 10A" required>
                                            <small class="form-text text-muted">Isi jika tidak mengunggah file.</small>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Tool Name</label>
                                            <input type="text" class="form-control" name="TD_TOOL_NAME" placeholder="Tool name">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Revision</label>
                                            <input type="number" class="form-control" name="TD_REVISION" value="0">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Status</label>
                                            <select name="TD_STATUS" class="form-control">
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                                <option value="2">Pending</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Quantity</label>
                                            <input type="number" class="form-control" name="TD_MIN_QTY" min="0" step="1">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Std. Quantity</label>
                                            <input type="number" class="form-control" name="TD_REPLENISH_QTY" min="0" step="1">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Sequence</label>
                                            <input type="number" class="form-control" name="TD_SEQUENCE" min="0" step="1">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Material</label>
                                            <select name="TD_MATERIAL_ID" class="form-control">
                                                <option value="">-- Select Material --</option>
                                                <?php foreach ($materials as $m): ?>
                                                    <option value="<?= (int)$m['MATERIAL_ID']; ?>"><?= htmlspecialchars($m['MATERIAL_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Maker</label>
                                            <select name="TD_MAKER_ID" class="form-control">
                                                <option value="">-- Select Maker --</option>
                                                <?php foreach ($makers as $mk): ?>
                                                    <option value="<?= (int)$mk['MAKER_ID']; ?>"><?= htmlspecialchars($mk['MAKER_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Remarks</label>
                                            <textarea name="TD_DESCRIPTION" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>Drawing File</label>
                                            <input type="file" name="TD_DRAWING_FILE" class="form-control" accept="image/*">
                                            <small class="form-text text-muted">Wajib diunggah saat membuat data baru.</small>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button id="btn-save-additional" class="btn btn-primary">Save</button>
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
                    toastr.success(res.message || 'BOM updated');
                } else {
                    toastr.warning(res && res.message ? res.message : 'Gagal menyimpan BOM');
                }
            }).fail(function(xhr, status){
                toastr.error('Gagal menyimpan BOM: ' + status);
            });
        });

        var table = $('#table-additional').DataTable({
            lengthMenu: [[10,25,50,-1],[10,25,50,"ALL"]],
            pageLength: 10,
            order: [[1,'desc']],
            autoWidth: false,
            columnDefs: [
                { orderable:false, targets:[0,11] },
                { width: '50px', targets:0 },
                { width: '70px', targets:1 }
            ]
        });

        // helper reset additional form
        function resetAdditionalForm() {
            var f = $('#formAdditional')[0];
            f.reset();
            $('[name="action"]', f).val('ADD');
            $('[name="TD_ID"]', f).val('');
            $('[name="TD_DRAWING_NO_OLD"]', f).val('');
            // default select product/process from BOM
            $('[name="TD_PRODUCT_ID"]', f).val('<?= isset($bom['PRODUCT_ID']) ? (int)$bom["PRODUCT_ID"] : '' ; ?>');
            $('[name="TD_PROCESS_ID"]', f).val('<?= isset($bom['PROCESS_ID']) ? (int)$bom["PROCESS_ID"] : '' ; ?>');
            $('[name="TD_STATUS"]', f).val(2); // Pending by default
        }

        $('#btn-add-additional').on('click', function(){
            resetAdditionalForm();
            $('#modalAdditional').modal('show');
        });

        $('#table-additional').on('click', '.btn-edit-additional', function(){
            var raw = $(this).data('edit');
            if (!raw) {
                toastr.error('Data edit tidak valid.');
                return;
            }
            resetAdditionalForm();
            $('[name="action"]', '#formAdditional').val('EDIT');
            $('[name="TD_ID"]', '#formAdditional').val(raw.TD_ID || '');
            $('[name="TD_DRAWING_NO"]', '#formAdditional').val(raw.TD_DRAWING_NO || '');
            $('[name="TD_DRAWING_NO_OLD"]', '#formAdditional').val(raw.TD_DRAWING_NO || '');
            $('[name="TD_TOOL_NAME"]', '#formAdditional').val(raw.TD_TOOL_NAME || '');
            $('[name="TD_REVISION"]', '#formAdditional').val(raw.TD_REVISION || 0);
            $('[name="TD_STATUS"]', '#formAdditional').val(raw.TD_STATUS !== undefined ? raw.TD_STATUS : 1);
            $('[name="TD_MIN_QTY"]', '#formAdditional').val(raw.TD_MIN_QTY || '');
            $('[name="TD_REPLENISH_QTY"]', '#formAdditional').val(raw.TD_REPLENISH_QTY || '');
            $('[name="TD_SEQUENCE"]', '#formAdditional').val(raw.TD_SEQUENCE || '');
            $('[name="TD_DESCRIPTION"]', '#formAdditional').val(raw.TD_DESCRIPTION || '');
            $('[name="TD_PRODUCT_ID"]', '#formAdditional').val(raw.TD_PRODUCT_ID || '');
            $('[name="TD_PROCESS_ID"]', '#formAdditional').val(raw.TD_PROCESS_ID || '');
            $('[name="TD_MATERIAL_ID"]', '#formAdditional').val(raw.TD_MATERIAL_ID || '');
            $('[name="TD_MAKER_ID"]', '#formAdditional').val(raw.TD_MAKER_ID || '');
            $('#modalAdditional').modal('show');
        });

        $('#table-additional').on('click', '.btn-delete-additional', function(){
            var id = Number($(this).data('id')) || 0;
            if (id <= 0) { toastr.error('ID tidak valid'); return; }
            if (!confirm('Hapus data additional ini?')) return;
            $.ajax({
                url: '<?= base_url("Tool_engineering/tool_draw_engin/delete_data"); ?>',
                type: 'POST',
                dataType: 'json',
                data: { TD_ID: id }
            }).done(function(res){
                if (res && res.success) {
                    toastr.success(res.message || 'Berhasil dihapus');
                    setTimeout(function(){ location.reload(); }, 600);
                } else {
                    toastr.warning(res && res.message ? res.message : 'Gagal hapus data');
                }
            }).fail(function(){
                toastr.error('Gagal menghapus data');
            });
        });

        $('#btn-save-additional').on('click', function(e){
            e.preventDefault();
            $('#formAdditional').submit();
        });

        $('#formAdditional').on('submit', function(e){
            e.preventDefault();
            var drawingNo = $.trim($('[name="TD_DRAWING_NO"]', this).val());
            var action = $('[name="action"]', this).val();
            if (action === 'ADD' && drawingNo === '' && $('[name="TD_DRAWING_FILE"]', this)[0].files.length === 0) {
                toastr.warning('Drawing wajib diisi atau upload file.');
                return;
            }
            var fd = new FormData(this);
            // if drawing text provided but no file, keep as DRAWING_NO_OLD to satisfy controller
            if (drawingNo !== '' && $('[name="TD_DRAWING_FILE"]', this)[0].files.length === 0) {
                fd.set('TD_DRAWING_NO_OLD', drawingNo);
            }
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
                    toastr.success(res.message || 'Berhasil disimpan');
                    $('#modalAdditional').modal('hide');
                    setTimeout(function(){ location.reload(); }, 500);
                } else {
                    toastr.warning(res && res.message ? res.message : 'Gagal menyimpan data');
                }
            }).fail(function(xhr, status){
                toastr.error('Gagal menyimpan additional: ' + status);
            });
        });
    });
})(jQuery);
</script>
</body>
</html>

