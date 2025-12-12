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
                            <h4 class="m-0 font-weight-bold text-primary">Add Tool Drawing Engineering</h4>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_draw_engin'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formToolDrawing" method="post" action="<?= base_url('Tool_engineering/tool_draw_engin/submit_data'); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="ADD">
                            <input type="hidden" name="TD_ID" value="">
                            <input type="hidden" name="TD_DRAWING_NO_OLD" value="">

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="label-required">Product</label>
                                    <select name="TD_PRODUCT_ID" class="form-control" required>
                                        <option value="">-- Select Product --</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= (int)$p['PRODUCT_ID']; ?>"><?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Product wajib dipilih.</div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="label-required">Process</label>
                                    <select name="TD_PROCESS_ID" class="form-control" required>
                                        <option value="">-- Select Process --</option>
                                        <?php foreach ($operations as $o): ?>
                                            <option value="<?= (int)$o['OPERATION_ID']; ?>"><?= htmlspecialchars($o['OPERATION_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Process wajib dipilih.</div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="label-required">Drawing (upload file)</label>
                                    <input type="file" name="TD_DRAWING_FILE" class="form-control" accept="*" required>
                                    <small class="form-text text-muted">Upload file untuk drawing. Wajib diunggah saat membuat data baru.</small>
                                    <div class="invalid-feedback">Drawing wajib diisi.</div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Tool</label>
                                    <select name="TD_TOOL_NAME" class="form-control">
                                        <option value="">-- Select Tool --</option>
                                        <?php foreach ($tools as $t): ?>
                                            <option value="<?= (int)$t['TOOL_ID']; ?>"><?= htmlspecialchars($t['TOOL_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Revision</label>
                                    <input type="number" name="TD_REVISION" class="form-control" value="0">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Status</label>
                                    <select name="TD_STATUS" class="form-control">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                        <option value="2">Pending</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Material</label>
                                    <select name="TD_MATERIAL_ID" class="form-control">
                                        <option value="">-- Select Material --</option>
                                        <?php foreach ($materials as $m): ?>
                                            <option value="<?= (int)$m['MATERIAL_ID']; ?>"><?= htmlspecialchars($m['MATERIAL_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Save</button>
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
            if (!hasFile) {
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
                    toastr.success(res.message || 'Tool Drawing berhasil ditambahkan');
                    setTimeout(function(){
                        window.location.href = '<?= base_url("Tool_engineering/tool_draw_engin"); ?>';
                    }, 600);
                } else {
                    toastr.warning(res && res.message ? res.message : 'Gagal menyimpan data');
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

