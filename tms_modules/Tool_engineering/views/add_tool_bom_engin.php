﻿<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <style>
        html, body, #content-wrapper { color: #000; }
        .card, .table, label, .form-text { color: #000; }
        .label-required::after { content: " *"; color: #dc3545; font-weight: 600; }
        .is-invalid + .invalid-feedback { display: block; }
        .navbar { position: sticky; top: 0; z-index: 1030; }
        #content-wrapper { min-height: calc(100vh - 56px); }
        #container-wrapper { padding-bottom: 15rem !important; margin-bottom: 5rem !important; }
        .card { margin-bottom: 2rem; }
        .form-group {
            margin-bottom: 1rem;
        }
        .select-with-submit {
            display: flex;
            gap: 0.5rem;
        }
        .select-with-submit select {
            flex: 1;
        }
        .date-with-submit {
            display: flex;
            gap: 0.5rem;
        }
        .date-with-submit input {
            flex: 1;
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
                            <h4 class="m-0 font-weight-bold text-primary">Add Tool BOM (Engineering)</h4>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_bom_engin'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formBom" method="post" action="<?= base_url('Tool_engineering/tool_bom_engin/submit_data'); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="ADD">
                            
                            <!-- Trial BOM Checkbox -->
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" name="IS_TRIAL_BOM" id="isTrialBom" value="1">
                                        <label class="form-check-label" for="isTrialBom">
                                            <strong>Trial BOM</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Kolom Kiri -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product</label>
                                        <div class="select-with-submit">
                                            <select name="PRODUCT_ID" id="product_id" class="form-control">
                                                <option value="">-- Select Product --</option>
                                                <?php foreach ($products as $p): ?>
                                                    <option value="<?= (int)$p['PRODUCT_ID']; ?>">
                                                        <?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <!-- <button type="button" class="btn btn-primary btn-sm" onclick="alert('Submit Product functionality')">
                                                Submit
                                            </button> -->
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Revision</label>
                                        <input type="number" name="REVISION" class="form-control" value="0" min="0">
                                    </div>

                                    <div class="form-group">
                                        <label>Machine Group</label>
                                        <div class="select-with-submit">
                                            <select name="MACHINE_GROUP_ID" id="machine_group_id" class="form-control">
                                                <option value="">-- Select Machine Group --</option>
                                                <?php foreach ($machine_groups as $mg): ?>
                                                    <option value="<?= (int)$mg['MACHINE_ID']; ?>">
                                                        <?= htmlspecialchars($mg['MACHINE_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <!-- <button type="button" class="btn btn-primary btn-sm" onclick="alert('Submit Machine Group functionality')">
                                                Submit
                                            </button> -->
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="STATUS" class="form-control">
                                            <option value="1" selected>Pending</option>
                                            <option value="2">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-required">Tool BOM</label>
                                        <input type="text" name="TOOL_BOM" class="form-control" required>
                                        <div class="invalid-feedback">Tool BOM wajib diisi.</div>
                                    </div>

                                    <div class="form-group">
                                        <label>Process</label>
                                        <div class="select-with-submit">
                                            <select name="PROCESS_ID" id="process_id" class="form-control">
                                                <option value="">-- Select Process --</option>
                                                <?php foreach ($operations as $o): ?>
                                                    <option value="<?= (int)$o['OPERATION_ID']; ?>">
                                                        <?= htmlspecialchars($o['OPERATION_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <!-- <button type="button" class="btn btn-primary btn-sm" onclick="alert('Submit Process functionality')">
                                                Submit
                                            </button> -->
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="DESCRIPTION" class="form-control" rows="3"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Effective Date</label>
                                        <div class="date-with-submit">
                                            <input type="date" name="EFFECTIVE_DATE" id="effective_date" class="form-control" value="<?= date('Y-m-d'); ?>">
                                            <!-- <button type="button" class="btn btn-primary btn-sm" onclick="alert('Submit Effective Date functionality')">
                                                Submit
                                            </button> -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Change Summary (Full Width) -->
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Change Summary</label>
                                    <textarea name="CHANGE_SUMMARY" class="form-control" rows="2"></textarea>
                                </div>
                            </div>

                            <!-- Additional Information Section -->
                            <h5 class="mt-4 mb-3 font-weight-bold">Additional Information</h5>

                            <div class="row">
                                <!-- Kolom Kiri -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Drawing</label>
                                        <input type="file" name="DRAWING_FILE" class="form-control" accept="*">
                                        <div class="small text-muted mt-1">No file chosen</div>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Sketch</label>
                                        <input type="file" name="SKETCH_FILE" class="form-control" accept="*">
                                        <div class="small text-muted mt-1">No file chosen</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-row mt-4 mb-3">
                                <div class="form-group col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> Submit
                                    </button>
                                    <a href="<?= base_url('Tool_engineering/tool_bom_engin'); ?>" class="btn btn-secondary">
                                        <i class="fa fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div><br><br><br>
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
        $('#formBom').on('submit', function(e){
            e.preventDefault();
            
            var toolBom = $.trim($('[name="TOOL_BOM"]').val());
            var isValid = true;
            
            if (toolBom === '') {
                $('[name="TOOL_BOM"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('[name="TOOL_BOM"]').removeClass('is-invalid');
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
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Tool BOM berhasil ditambahkan');
                    } else {
                        alert(res.message || 'Tool BOM berhasil ditambahkan');
                    }
                    setTimeout(function(){
                        window.location.href = '<?= base_url("Tool_engineering/tool_bom_engin"); ?>';
                    }, 600);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(res && res.message ? res.message : 'Gagal menyimpan data');
                    } else {
                        alert(res && res.message ? res.message : 'Gagal menyimpan data');
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

        // Update file chosen text
        $('input[type="file"]').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            if (fileName) {
                $(this).next('.text-muted').text('Selected: ' + fileName);
            } else {
                $(this).next('.text-muted').text('No file chosen');
            }
        });
    });
})(jQuery);
</script>
</body>
</html>