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
                            <h4 class="m-0 font-weight-bold text-primary">Edit Tool BOM</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($bom['ID']); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_bom_engin'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formBom" method="post" action="<?= base_url('Tool_engineering/tool_bom_engin/submit_data'); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="EDIT">
                            <input type="hidden" name="ID" value="<?= htmlspecialchars($bom['ID']); ?>">
                            
                            <!-- Trial BOM Checkbox -->
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="IS_TRIAL_BOM" id="isTrialBom" value="1" <?= (isset($bom['IS_TRIAL_BOM']) && ((int)$bom['IS_TRIAL_BOM'] === 1 || $bom['IS_TRIAL_BOM'] === true)) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="isTrialBom">
                                            <strong>Trial BOM</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>

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
                                    <input type="file" name="DRAWING_FILE" class="form-control" accept="*">
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
                                            <td><?= htmlspecialchars(isset($row['TD_REMARKS']) && $row['TD_REMARKS'] !== '' ? $row['TD_REMARKS'] : '-', ENT_QUOTES, 'UTF-8'); ?></td>
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
                                    <input type="hidden" name="TB_MLR_PARENT_ID" value="<?= htmlspecialchars($bom['ID']); ?>">
                                    <input type="hidden" name="TB_ID" value="">

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
                                            <div id="drawing_no_container">
                                                <select name="TD_DRAWING_NO" id="select_drawing_no" class="form-control" required>
                                                    <option value="">-- Select Tool Drawing No. --</option>
                                                    <?php if (isset($tool_drawings) && is_array($tool_drawings)): ?>
                                                        <?php foreach ($tool_drawings as $td): ?>
                                                            <option value="<?= (int)$td['TD_ID']; ?>" data-drawing-no="<?= htmlspecialchars($td['TD_DRAWING_NO'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?= htmlspecialchars($td['TD_DRAWING_NO'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <input type="hidden" name="TD_DRAWING_NO_TEXT" id="drawing_no_text" value="">
                                            <small class="form-text text-muted">Pilih Tool Drawing No. untuk auto-fill data.</small>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Tool Name</label>
                                            <select name="TD_TOOL_NAME" id="select_tool_name" class="form-control" readonly>
                                                <option value="">-- Select Tool --</option>
                                                <?php foreach ($tools as $t): ?>
                                                    <option value="<?= (int)$t['TOOL_ID']; ?>">
                                                        <?= htmlspecialchars($t['TOOL_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="form-text text-muted">Terisi otomatis dari Tool Drawing No.</small>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Revision</label>
                                            <input type="number" class="form-control" name="TD_REVISION" id="input_revision" value="0" readonly>
                                            <small class="form-text text-muted">Terisi otomatis dari Tool Drawing No.</small>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Status</label>
                                            <select name="TD_STATUS" class="form-control" id="select_status">
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
                                            <textarea name="TD_REMARKS" class="form-control" rows="2"></textarea>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>Drawing File</label>
                                            <input type="file" name="TD_DRAWING_FILE" id="input_drawing_file" class="form-control" accept="*">
                                            <div id="drawing_file_info" class="small text-muted mt-1" style="display:none;"></div>
                                            <small class="form-text text-muted">Wajib diunggah saat membuat data baru. Jika Tool Drawing No. sudah memiliki file, field ini tidak bisa diedit.</small>
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
            order: [], // No default sorting - maintain order from database (TB_SEQ)
            autoWidth: false,
            columnDefs: [
                { orderable:false, targets:[0,11] }, // No. column not sortable (it's auto-increment)
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
            
            // Restore select dropdown if it was replaced with text input
            var drawingNoContainer = $('#drawing_no_container');
            if (drawingNoContainer.find('input[type="text"]').length > 0) {
                var selectHtml = '<select name="TD_DRAWING_NO" id="select_drawing_no" class="form-control" required>' +
                    '<option value="">-- Select Tool Drawing No. --</option>';
                <?php if (isset($tool_drawings) && is_array($tool_drawings)): ?>
                    <?php foreach ($tool_drawings as $td): ?>
                        selectHtml += '<option value="<?= (int)$td['TD_ID']; ?>" data-drawing-no="<?= htmlspecialchars($td['TD_DRAWING_NO'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($td['TD_DRAWING_NO'], ENT_QUOTES, 'UTF-8'); ?></option>';
                    <?php endforeach; ?>
                <?php endif; ?>
                selectHtml += '</select>';
                drawingNoContainer.html(selectHtml);
            } else if ($('#select_drawing_no').length > 0) {
                $('#select_drawing_no').val('').trigger('change');
            }
            
            $('[name="TD_DRAWING_NO_OLD"]', f).val('');
            $('[name="TB_ID"]', f).val('');
            $('#select_tool_name').val('').prop('readonly', false);
            $('#input_revision').val('0').prop('readonly', false);
            $('[name="TD_STATUS"]', f).val(2); // Pending by default
            $('[name="TD_MIN_QTY"]', f).val('');
            $('[name="TD_REPLENISH_QTY"]', f).val('');
            $('[name="TD_SEQUENCE"]', f).val('');
            $('[name="TD_REMARKS"]', f).val('');
            $('[name="TD_MATERIAL_ID"]', f).val('');
            $('[name="TD_MAKER_ID"]', f).val('');
            $('#drawing_file_info').hide();
            $('#input_drawing_file').prop('disabled', false);
            $('#drawing_no_text').val('');
            // default select product/process from BOM
            $('[name="TD_PRODUCT_ID"]', f).val('<?= isset($bom['PRODUCT_ID']) ? (int)$bom["PRODUCT_ID"] : '' ; ?>');
            $('[name="TD_PROCESS_ID"]', f).val('<?= isset($bom['PROCESS_ID']) ? (int)$bom["PROCESS_ID"] : '' ; ?>');
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
            
            // For EDIT, replace select with text input (read-only)
            var drawingNoContainer = $('#drawing_no_container');
            if (drawingNoContainer.length > 0) {
                drawingNoContainer.html('<input type="text" name="TD_DRAWING_NO" class="form-control" value="' + (raw.TD_DRAWING_NO || '') + '" readonly>');
            } else {
                $('[name="TD_DRAWING_NO"]', '#formAdditional').val(raw.TD_DRAWING_NO || '').prop('readonly', true);
            }
            
            $('[name="TD_DRAWING_NO_OLD"]', '#formAdditional').val(raw.TD_DRAWING_NO || '');
            // Use TD_TOOL_ID (MLR_TC_ID) for select, fallback to TD_TOOL_NAME if ID not available
            var toolId = raw.TD_TOOL_ID || (raw.TD_TOOL_NAME ? raw.TD_TOOL_NAME : '');
            $('#select_tool_name').val(toolId || '').prop('readonly', true);
            $('#input_revision').val(raw.TD_REVISION !== undefined ? raw.TD_REVISION : 0).prop('readonly', true);
            $('[name="TD_STATUS"]', '#formAdditional').val(raw.TD_STATUS !== undefined ? raw.TD_STATUS : 1);
            $('[name="TD_MIN_QTY"]', '#formAdditional').val(raw.TD_MIN_QTY !== undefined ? raw.TD_MIN_QTY : '');
            $('[name="TD_REPLENISH_QTY"]', '#formAdditional').val(raw.TD_REPLENISH_QTY !== undefined ? raw.TD_REPLENISH_QTY : '');
            $('[name="TD_SEQUENCE"]', '#formAdditional').val(raw.TD_SEQUENCE !== undefined ? raw.TD_SEQUENCE : '');
            $('[name="TD_REMARKS"]', '#formAdditional').val(raw.TD_REMARKS || '');
            $('[name="TD_PRODUCT_ID"]', '#formAdditional').val(raw.TD_PRODUCT_ID || '');
            $('[name="TD_PROCESS_ID"]', '#formAdditional').val(raw.TD_PROCESS_ID || '');
            $('[name="TD_MATERIAL_ID"]', '#formAdditional').val(raw.TD_MATERIAL_ID || '');
            $('[name="TD_MAKER_ID"]', '#formAdditional').val(raw.TD_MAKER_ID || '');
            // Get TB_ID from data
            $('[name="TB_ID"]', '#formAdditional').val(raw.TB_ID || '');
            // Disable drawing file input for edit if file exists
            if (raw.TD_DRAWING_NO && raw.TD_DRAWING_NO !== '') {
                $('#input_drawing_file').prop('disabled', true);
                $('#drawing_file_info').html('File sudah ada: ' + (raw.TD_DRAWING_NO || '')).show();
            }
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

        // Auto-fill when Tool Drawing No. is selected (only for ADD action)
        // Use event delegation to handle dynamically created elements
        $(document).on('change', '#select_drawing_no', function(){
            var action = $('[name="action"]', '#formAdditional').val();
            if (action !== 'ADD') return; // Only for ADD
            
            var mlr_id = $(this).val();
            if (!mlr_id || mlr_id === '') {
                // Reset fields
                $('#input_revision').val('0').prop('readonly', false);
                $('#select_tool_name').val('').prop('readonly', false);
                $('#drawing_file_info').hide();
                $('#input_drawing_file').prop('disabled', false);
                $('#drawing_no_text').val('');
                return;
            }
            
            // Get drawing detail
            $.ajax({
                url: '<?= base_url("Tool_engineering/tool_bom_engin/get_drawing_detail"); ?>',
                type: 'POST',
                dataType: 'json',
                data: { MLR_ID: mlr_id }
            }).done(function(res){
                if (res && res.success && res.data) {
                    var d = res.data;
                    
                    // Set drawing no text
                    var drawingNoText = $('#select_drawing_no option:selected').data('drawing-no') || '';
                    $('#drawing_no_text').val(drawingNoText);
                    
                    // Auto-fill Revision (read-only)
                    $('#input_revision').val(d.TD_REVISION || 0).prop('readonly', true);
                    
                    // Auto-fill Tool Name (read-only)
                    $('#select_tool_name').val(d.TD_TOOL_ID || '').prop('readonly', true);
                    
                    // Auto-fill Product and Process if empty
                    if (!$('[name="TD_PRODUCT_ID"]', '#formAdditional').val()) {
                        $('[name="TD_PRODUCT_ID"]', '#formAdditional').val(d.TD_PRODUCT_ID || '');
                    }
                    if (!$('[name="TD_PROCESS_ID"]', '#formAdditional').val()) {
                        $('[name="TD_PROCESS_ID"]', '#formAdditional').val(d.TD_PROCESS_ID || '');
                    }
                    
                    // Auto-fill Material and Maker if empty
                    if (!$('[name="TD_MATERIAL_ID"]', '#formAdditional').val()) {
                        $('[name="TD_MATERIAL_ID"]', '#formAdditional').val(d.TD_MATERIAL_ID || '');
                    }
                    if (!$('[name="TD_MAKER_ID"]', '#formAdditional').val()) {
                        $('[name="TD_MAKER_ID"]', '#formAdditional').val(d.TD_MAKER_ID || '');
                    }
                    
                    // Auto-fill Status if empty
                    if (!$('[name="TD_STATUS"]', '#formAdditional').val()) {
                        $('[name="TD_STATUS"]', '#formAdditional').val(d.TD_STATUS || 1);
                    }
                    
                    // Check if drawing file exists
                    if (d.TD_DRAWING_FILE && d.TD_DRAWING_FILE !== '') {
                        $('#drawing_file_info').html('File sudah ada: ' + d.TD_DRAWING_FILE).show();
                        $('#input_drawing_file').prop('disabled', true);
                    } else {
                        $('#drawing_file_info').hide();
                        $('#input_drawing_file').prop('disabled', false);
                    }
                } else {
                    toastr.warning('Gagal mengambil data Tool Drawing.');
                }
            }).fail(function(){
                toastr.error('Gagal mengambil data Tool Drawing.');
            });
        });

        $('#btn-save-additional').on('click', function(e){
            e.preventDefault();
            $('#formAdditional').submit();
        });

        $('#formAdditional').on('submit', function(e){
            e.preventDefault();
            var action = $('[name="action"]', this).val();
            var drawingNoSelect = $('#select_drawing_no').val();
            var drawingNoText = $('#drawing_no_text').val();
            
            if (action === 'ADD') {
                if (!drawingNoSelect || drawingNoSelect === '') {
                    toastr.warning('Tool Drawing No. wajib dipilih.');
                    return;
                }
                
                // Set TD_DRAWING_NO to MLR_ID for controller
                var fd = new FormData(this);
                fd.set('TD_DRAWING_NO', drawingNoText); // Use text value
                fd.set('TD_MLR_ID', drawingNoSelect); // Use MLR_ID for reference (existing drawing)
                
                // If drawing file exists and is disabled, don't send file
                if ($('#input_drawing_file').prop('disabled')) {
                    fd.delete('TD_DRAWING_FILE');
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
            } else {
                // EDIT action - use existing logic
                var drawingNo = $.trim($('[name="TD_DRAWING_NO"]', this).val());
                if (drawingNo === '' && $('[name="TD_DRAWING_FILE"]', this)[0].files.length === 0) {
                    toastr.warning('Drawing wajib ada.');
                    return;
                }
                var fd = new FormData(this);
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
            }
        });
    });
})(jQuery);
</script>
</body>
</html>

