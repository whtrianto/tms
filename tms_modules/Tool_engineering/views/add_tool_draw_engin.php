<!DOCTYPE html>
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
        #container-wrapper { padding-bottom: 4rem; margin-bottom: 2rem; }
        .card { margin-bottom: 2rem; }
        /* Ensure footer doesn't overlap */
        #content {
            padding-bottom: 4rem;
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
                                    <label class="label-required">Tool Draw No</label>
                                    <input type="text" name="TD_DRAWING_NO" class="form-control" placeholder="Enter Drawing Number" required>
                                    <small class="form-text text-muted">Masukkan nomor drawing (contoh: TD-001, DWG-2024-001, dll)</small>
                                    <div class="invalid-feedback">Tool Draw No wajib diisi.</div>
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
                                <div class="form-group col-md-12">
                                    <label class="label-required">Drawing (upload file)</label>
                                    <input type="file" name="TD_DRAWING_FILE" class="form-control" accept="*" required>
                                    <small class="form-text text-muted">Upload file untuk drawing. Wajib diunggah saat membuat data baru.</small>
                                    <div class="invalid-feedback">Drawing wajib diisi.</div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Revision</label>
                                    <input type="number" name="TD_REVISION" class="form-control" value="0" min="0">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Status</label>
                                    <select name="TD_STATUS" class="form-control">
                                        <option value="2">Active</option>
                                        <option value="0">Inactive</option>
                                        <option value="1">Pending</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Material</label>
                                    <select name="TD_MATERIAL_ID" class="form-control">
                                        <option value="">-- Select Material --</option>
                                        <?php foreach ($materials as $m): ?>
                                            <option value="<?= (int)$m['MATERIAL_ID']; ?>"><?= htmlspecialchars($m['MATERIAL_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Maker</label>
                                    <select name="TD_MAKER_ID" class="form-control">
                                        <option value="">-- Select Maker --</option>
                                        <?php 
                                        $makers = isset($makers) ? $makers : array();
                                        foreach ($makers as $mk): ?>
                                            <option value="<?= (int)$mk['MAKER_ID']; ?>"><?= htmlspecialchars($mk['MAKER_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Machine Group</label>
                                    <select name="TD_MACG_ID" class="form-control">
                                        <option value="">-- Select Machine Group --</option>
                                        <?php 
                                        $machine_groups = isset($machine_groups) ? $machine_groups : array();
                                        foreach ($machine_groups as $mg): ?>
                                            <option value="<?= (int)$mg['MACHINE_ID']; ?>"><?= htmlspecialchars($mg['MACHINE_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Effective Date</label>
                                    <input type="date" name="TD_EFFECTIVE_DATE" class="form-control" value="">
                                    <small class="form-text text-muted">Optional: Select effective date</small>
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
            var drawingNo = $.trim($('[name="TD_DRAWING_NO"]').val());
            var fileInput = $('[name="TD_DRAWING_FILE"]')[0];
            var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
            
            // Clear previous validation
            $('.is-invalid').removeClass('is-invalid');
            
            var isValid = true;
            var errorMessages = [];
            
            if (productId === '' || productId <= 0) {
                $('[name="TD_PRODUCT_ID"]').addClass('is-invalid');
                isValid = false;
                errorMessages.push('Product wajib dipilih');
            }
            
            if (processId === '' || processId <= 0) {
                $('[name="TD_PROCESS_ID"]').addClass('is-invalid');
                isValid = false;
                errorMessages.push('Process wajib dipilih');
            }
            
            if (drawingNo === '') {
                $('[name="TD_DRAWING_NO"]').addClass('is-invalid');
                isValid = false;
                errorMessages.push('Tool Draw No wajib diisi');
            }
            
            if (!hasFile) {
                $('[name="TD_DRAWING_FILE"]').addClass('is-invalid');
                isValid = false;
                errorMessages.push('Drawing file wajib diunggah');
            }

            if (!isValid) {
                if (errorMessages.length > 0 && typeof toastr !== 'undefined') {
                    toastr.warning(errorMessages.join('<br>'));
                }
                return;
            }

            // Show loading indicator
            var $submitBtn = $(this).find('button[type="submit"]');
            var originalText = $submitBtn.html();
            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            
            var fd = new FormData(this);
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: fd,
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false,
                timeout: 60000 // Increase timeout for file upload
            }).done(function(res){
                if (res && res.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Tool Drawing berhasil ditambahkan');
                    } else {
                        alert(res.message || 'Tool Drawing berhasil ditambahkan');
                    }
                    setTimeout(function(){
                        window.location.href = '<?= base_url("Tool_engineering/tool_draw_engin"); ?>';
                    }, 1000);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res && res.message ? res.message : 'Gagal menyimpan data');
                    } else {
                        alert(res && res.message ? res.message : 'Gagal menyimpan data');
                    }
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            }).fail(function(xhr, status, error){
                var errorMsg = 'Gagal menyimpan: ' + status;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMsg);
                } else {
                    alert(errorMsg);
                }
                $submitBtn.prop('disabled', false).html(originalText);
            });
        });
    });
})(jQuery);
</script>
</body>
</html>

