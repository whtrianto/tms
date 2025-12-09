<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <style>
        html, body, #content-wrapper { color: #000; }
        .card, .table, label, .form-text { color: #000; }
        .label-required::after { content: " *"; color: #dc3545; font-weight: 600; }
        .is-invalid + .invalid-feedback { display: block; }
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
                            <h4 class="m-0 font-weight-bold text-primary">Revision Tool Drawing Engineering</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($drawing['TD_ID']); ?> | New Revision: <?= htmlspecialchars($drawing['TD_REVISION']); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('tool_engineering/tool_draw_engin'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formToolDrawing" method="post" action="<?= base_url('Tool_engineering/tool_draw_engin/submit_data'); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="REVISION">
                            <input type="hidden" name="TD_ID" value="<?= htmlspecialchars($drawing['TD_ID']); ?>">
                            <input type="hidden" name="TD_DRAWING_NO_OLD" value="<?= htmlspecialchars(isset($drawing['TD_DRAWING_NO']) ? $drawing['TD_DRAWING_NO'] : ''); ?>">

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="label-required">Product</label>
                                    <select name="TD_PRODUCT_ID" class="form-control" required>
                                        <option value="">-- Select Product --</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= (int)$p['PRODUCT_ID']; ?>" <?= (isset($drawing['TD_PRODUCT_ID']) && (int)$drawing['TD_PRODUCT_ID'] === (int)$p['PRODUCT_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Product wajib dipilih.</div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="label-required">Process</label>
                                    <select name="TD_PROCESS_ID" class="form-control" required>
                                        <option value="">-- Select Process --</option>
                                        <?php foreach ($operations as $o): ?>
                                            <option value="<?= (int)$o['OPERATION_ID']; ?>" <?= (isset($drawing['TD_PROCESS_ID']) && (int)$drawing['TD_PROCESS_ID'] === (int)$o['OPERATION_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($o['OPERATION_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Process wajib dipilih.</div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Drawing (upload file)</label>
                                    <input type="file" name="TD_DRAWING_FILE" class="form-control" accept="*">
                                    <?php if (!empty($drawing['TD_DRAWING_NO'])): ?>
                                        <div class="small text-muted mt-1">Current: <?= htmlspecialchars($drawing['TD_DRAWING_NO']); ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Biarkan kosong untuk menggunakan file yang sama.</small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Tool</label>
                                    <select name="TD_TOOL_NAME" class="form-control">
                                        <option value="">-- Select Tool --</option>
                                        <?php 
                                        // Determine which tool should be selected
                                        $selected_tool_id = null;
                                        if (isset($drawing['TD_TOOL_ID']) && (int)$drawing['TD_TOOL_ID'] > 0) {
                                            $selected_tool_id = (int)$drawing['TD_TOOL_ID'];
                                        } elseif (isset($drawing['TD_TOOL_NAME']) && $drawing['TD_TOOL_NAME'] !== '') {
                                            // Fallback: try to match by TD_TOOL_NAME
                                            if (is_numeric($drawing['TD_TOOL_NAME'])) {
                                                $selected_tool_id = (int)$drawing['TD_TOOL_NAME'];
                                            } else {
                                                // Match by name
                                                $tool_name = trim($drawing['TD_TOOL_NAME']);
                                                foreach ($tools as $t) {
                                                    if (strcasecmp(trim($t['TOOL_NAME']), $tool_name) === 0) {
                                                        $selected_tool_id = (int)$t['TOOL_ID'];
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        foreach ($tools as $t): ?>
                                            <option value="<?= (int)$t['TOOL_ID']; ?>" <?= ($selected_tool_id !== null && (int)$t['TOOL_ID'] === $selected_tool_id) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($t['TOOL_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Revision</label>
                                    <input type="number" name="TD_REVISION" class="form-control" value="<?= htmlspecialchars($drawing['TD_REVISION']); ?>" readonly>
                                    <small class="form-text text-muted">Revision akan otomatis bertambah.</small>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Status</label>
                                    <select name="TD_STATUS" class="form-control">
                                        <option value="1" <?= (isset($drawing['TD_STATUS']) && (int)$drawing['TD_STATUS'] === 1) ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?= (isset($drawing['TD_STATUS']) && (int)$drawing['TD_STATUS'] === 0) ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="2" <?= (isset($drawing['TD_STATUS']) && (int)$drawing['TD_STATUS'] === 2) ? 'selected' : ''; ?>>Pending</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Material</label>
                                    <select name="TD_MATERIAL_ID" class="form-control">
                                        <option value="">-- Select Material --</option>
                                        <?php foreach ($materials as $m): ?>
                                            <option value="<?= (int)$m['MATERIAL_ID']; ?>" <?= (isset($drawing['TD_MATERIAL_ID']) && (int)$drawing['TD_MATERIAL_ID'] === (int)$m['MATERIAL_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($m['MATERIAL_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Save Revision</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?= isset($modal_logout) ? $modal_logout : ''; ?>
        </div>
        <?= isset($footer) ? $footer : ''; ?>
    </div>
</div>

<?= isset($foot) ? $foot : ''; ?>
<script>
(function($){
    $(function(){
        $('#formToolDrawing').on('submit', function(e){
            e.preventDefault();
            var productId = $.trim($('[name="TD_PRODUCT_ID"]').val());
            var processId = $.trim($('[name="TD_PROCESS_ID"]').val());
            var fileInput = $('[name="TD_DRAWING_FILE"]')[0];
            var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
            var oldDrawing = $.trim($('[name="TD_DRAWING_NO_OLD"]').val());
            
            var isValid = true;
            if (productId === '' || productId <= 0) {
                $('[name="TD_PRODUCT_ID"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('[name="TD_PRODUCT_ID"]').removeClass('is-invalid');
            }
            if (processId === '' || processId <= 0) {
                $('[name="TD_PROCESS_ID"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('[name="TD_PROCESS_ID"]').removeClass('is-invalid');
            }
            if (!hasFile && oldDrawing === '') {
                $('[name="TD_DRAWING_FILE"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('[name="TD_DRAWING_FILE"]').removeClass('is-invalid');
            }

            if (!isValid) return;

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
                    toastr.success(res.message || 'Revision berhasil ditambahkan');
                    setTimeout(function(){
                        window.location.href = '<?= base_url("tool_engineering/tool_draw_engin"); ?>';
                    }, 600);
                } else {
                    toastr.warning(res && res.message ? res.message : 'Gagal menyimpan revision');
                }
            }).fail(function(xhr, status){
                toastr.error('Gagal menyimpan: ' + status);
            });
        });
    });
})(jQuery);
</script>
</body>
</html>

