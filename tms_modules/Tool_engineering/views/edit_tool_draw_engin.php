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
                            <h4 class="m-0 font-weight-bold text-primary">Edit Tool Drawing Engineering</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($drawing['TD_ID']); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_draw_engin'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formToolDrawing" method="post" action="<?= base_url('Tool_engineering/tool_draw_engin/submit_data'); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="EDIT">
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
                                    <label class="label-required">Tool Draw No</label>
                                    <input type="text" name="TD_DRAWING_NO" class="form-control" placeholder="Enter Drawing Number" value="<?= htmlspecialchars(isset($drawing['TD_DRAWING_NO']) ? $drawing['TD_DRAWING_NO'] : ''); ?>" required>
                                    <small class="form-text text-muted">Masukkan nomor drawing (contoh: TD-001, DWG-2024-001, dll)</small>
                                    <div class="invalid-feedback">Tool Draw No wajib diisi.</div>
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
                                <div class="form-group col-md-12">
                                    <label>Drawing (upload file)</label>
                                    <input type="file" name="TD_DRAWING_FILE" class="form-control" accept="*">
                                    <?php if (!empty($drawing['TD_DRAWING_NO'])): ?>
                                        <div class="small text-muted mt-1">Current: <?= htmlspecialchars($drawing['TD_DRAWING_NO']); ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted">Biarkan kosong jika tidak mengganti file.</small>
                                    <div class="invalid-feedback">Drawing wajib diisi.</div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Revision</label>
                                    <input type="number" name="TD_REVISION" class="form-control" value="<?= htmlspecialchars(isset($drawing['TD_REVISION']) ? $drawing['TD_REVISION'] : 0); ?>" min="0">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Status</label>
                                    <select name="TD_STATUS" class="form-control">
                                        <option value="2" <?= (isset($drawing['TD_STATUS']) && (int)$drawing['TD_STATUS'] === 2) ? 'selected' : ''; ?>>Active</option>
                                        <option value="0" <?= (isset($drawing['TD_STATUS']) && (int)$drawing['TD_STATUS'] === 0) ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="1" <?= (isset($drawing['TD_STATUS']) && (int)$drawing['TD_STATUS'] === 1) ? 'selected' : ''; ?>>Pending</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
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
                                <div class="form-group col-md-3">
                                    <label>Maker</label>
                                    <select name="TD_MAKER_ID" class="form-control">
                                        <option value="">-- Select Maker --</option>
                                        <?php 
                                        $makers = isset($makers) ? $makers : array();
                                        foreach ($makers as $mk): ?>
                                            <option value="<?= (int)$mk['MAKER_ID']; ?>" <?= (isset($drawing['TD_MAKER_ID']) && (int)$drawing['TD_MAKER_ID'] === (int)$mk['MAKER_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($mk['MAKER_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
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
                                            <option value="<?= (int)$mg['MACHINE_ID']; ?>" <?= (isset($drawing['TD_MACG_ID']) && (int)$drawing['TD_MACG_ID'] === (int)$mg['MACHINE_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($mg['MACHINE_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Effective Date</label>
                                    <input type="date" name="TD_EFFECTIVE_DATE" class="form-control" value="<?= isset($drawing['TD_EFFECTIVE_DATE']) && !empty($drawing['TD_EFFECTIVE_DATE']) ? date('Y-m-d', strtotime($drawing['TD_EFFECTIVE_DATE'])) : ''; ?>">
                                    <small class="form-text text-muted">Optional: Select effective date</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Shared Tool Section -->
                <div class="card mb-3 mt-3" id="shared-tool-card">
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
            <!-- Extra spacer to prevent footer overlap -->
            <div style="height: 15rem; min-height: 15rem; clear: both;"></div>
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
            if (drawingNo === '') {
                $('[name="TD_DRAWING_NO"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('[name="TD_DRAWING_NO"]').removeClass('is-invalid');
            }
            // File is optional for edit - only required if no old drawing exists
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
                    toastr.success(res.message || 'Tool Drawing berhasil diperbarui');
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

        // Update Shared Tool table when Product changes
        $('[name="TD_PRODUCT_ID"]').on('change', function(){
            var productId = $(this).val();
            if (productId && productId > 0) {
                // Reload shared tool BOM via AJAX
                $.ajax({
                    url: '<?= base_url("Tool_engineering/tool_draw_engin/get_tool_bom_by_product"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: { PRODUCT_ID: productId }
                }).done(function(res){
                    if (res && res.success && res.data) {
                        var tbody = $('.card.mb-3.mt-3 table tbody');
                        tbody.empty();
                        if (res.data.length > 0) {
                            var productName = res.product_name || '';
                            res.data.forEach(function(bom){
                                var row = '<tr>' +
                                    '<td class="text-center">' + (bom.ID || '') + '</td>' +
                                    '<td>' + (productName || bom.PRODUCT || '') + '</td>' +
                                    '<td>' + (bom.TOOL_BOM || '') + '</td>' +
                                    '</tr>';
                                tbody.append(row);
                            });
                        } else {
                            tbody.append('<tr><td colspan="3" class="text-center text-muted">No shared tool BOM found for this product.</td></tr>');
                        }
                    }
                }).fail(function(){
                    // Silently fail - keep existing data
                });
            } else {
                // Clear table if no product selected
                $('.card.mb-3.mt-3 table tbody').html('<tr><td colspan="3" class="text-center text-muted">Please select a product to view shared tools.</td></tr>');
            }
        });
    });
})(jQuery);
</script>
</body>
</html>

