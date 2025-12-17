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
        .navbar { position: sticky; top: 0; z-index: 1030; }
        #content-wrapper { min-height: calc(100vh - 56px); }
        #container-wrapper { padding-bottom: 15rem !important; margin-bottom: 5rem !important; }
        .card { margin-bottom: 2rem; }
        #content { padding-bottom: 15rem !important; min-height: calc(100vh - 56px); }
        #shared-tool-card { margin-bottom: 10rem !important; }
        #shared-tool-card .card-body { padding-bottom: 4rem !important; }
        #shared-tool-card .table-responsive { margin-bottom: 2rem; }
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
                            <h4 class="m-0 font-weight-bold text-primary">Edit Tool Drawing Tooling</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($drawing['MLR_ID']); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_draw_tooling'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formToolDrawing" method="post" action="<?= base_url('Tool_engineering/tool_draw_tooling/submit_data'); ?>" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="EDIT">
                            <input type="hidden" name="MLR_ID" value="<?= htmlspecialchars($drawing['MLR_ID']); ?>">
                            <input type="hidden" name="ML_TOOL_DRAW_NO_OLD" value="<?= htmlspecialchars(isset($drawing['ML_TOOL_DRAW_NO']) ? $drawing['ML_TOOL_DRAW_NO'] : ''); ?>">

                            <!-- Row 1: Product, Tool Name -->
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="label-required">Product</label>
                                    <select name="MLR_PRODUCT_ID" class="form-control" required>
                                        <option value="">-- Select Product --</option>
                                        <?php 
                                        $products = isset($products) ? $products : array();
                                        foreach ($products as $p): ?>
                                            <option value="<?= (int)$p['PRODUCT_ID']; ?>" <?= (isset($drawing['MLR_PRODUCT_ID']) && (int)$drawing['MLR_PRODUCT_ID'] === (int)$p['PRODUCT_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Product wajib dipilih.</div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="label-required">Tool Name</label>
                                    <select name="MLR_TC_ID" class="form-control" required>
                                        <option value="">-- Select Tool --</option>
                                        <?php 
                                        $selected_tool_id = null;
                                        if (isset($drawing['MLR_TC_ID']) && (int)$drawing['MLR_TC_ID'] > 0) {
                                            $selected_tool_id = (int)$drawing['MLR_TC_ID'];
                                        }
                                        foreach ($tools as $t): ?>
                                            <option value="<?= (int)$t['TC_ID']; ?>" <?= ($selected_tool_id !== null && (int)$t['TC_ID'] === $selected_tool_id) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($t['TC_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Tool Name wajib dipilih.</div>
                                </div>
                            </div>

                            <!-- Row 2: Tool Drawing No, Revision, Maker -->
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label class="label-required">Tool Drawing No.</label>
                                    <input type="text" name="ML_TOOL_DRAW_NO" class="form-control" value="<?= htmlspecialchars(isset($drawing['ML_TOOL_DRAW_NO']) ? $drawing['ML_TOOL_DRAW_NO'] : ''); ?>" required>
                                    <div class="invalid-feedback">Tool Drawing No wajib diisi.</div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="label-required">Revision</label>
                                    <input type="number" name="MLR_REV" class="form-control" value="<?= htmlspecialchars(isset($drawing['MLR_REV']) ? (int)$drawing['MLR_REV'] : 0); ?>" min="0" required>
                                    <div class="invalid-feedback">Revision wajib diisi.</div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Maker</label>
                                    <select name="MLR_MAKER_ID" class="form-control">
                                        <option value="">-- Select Maker --</option>
                                        <?php foreach ($makers as $m): ?>
                                            <option value="<?= (int)$m['MAKER_ID']; ?>" <?= (isset($drawing['MLR_MAKER_ID']) && (int)$drawing['MLR_MAKER_ID'] === (int)$m['MAKER_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($m['MAKER_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-row mb-3">
                                <div class="form-group col-md-12">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>

                            <!-- Row 3: Min Quantity, Replenish Quantity, Process -->
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Min Quantity</label>
                                    <input type="number" name="MLR_MIN_QTY" class="form-control" value="<?= htmlspecialchars(isset($drawing['MLR_MIN_QTY']) ? (int)$drawing['MLR_MIN_QTY'] : 1); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Replenish Quantity</label>
                                    <input type="number" name="MLR_REPLENISH_QTY" class="form-control" value="<?= htmlspecialchars(isset($drawing['MLR_REPLENISH_QTY']) ? (int)$drawing['MLR_REPLENISH_QTY'] : 1); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="label-required">Process</label>
                                    <select name="MLR_OP_ID" class="form-control" required>
                                        <option value="">-- Select Process --</option>
                                        <?php 
                                        $operations = isset($operations) ? $operations : array();
                                        foreach ($operations as $o): ?>
                                            <option value="<?= (int)$o['OP_ID']; ?>" <?= (isset($drawing['MLR_OP_ID']) && (int)$drawing['MLR_OP_ID'] === (int)$o['OP_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($o['OP_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Process wajib dipilih.</div>
                                </div>
                            </div>

                            <!-- Row 4: Price, Standard Tool Life, Standard Rework -->
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Price</label>
                                    <input type="number" name="MLR_PRICE" class="form-control" step="0.01" value="<?= htmlspecialchars(isset($drawing['MLR_PRICE']) ? (float)$drawing['MLR_PRICE'] : 0); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Standard Tool Life</label>
                                    <input type="text" name="MLR_STD_TL_LIFE" class="form-control" value="<?= htmlspecialchars(isset($drawing['MLR_STD_TL_LIFE']) ? $drawing['MLR_STD_TL_LIFE'] : ''); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Standard Rework</label>
                                    <input type="text" name="MLR_STD_RW" class="form-control" value="<?= htmlspecialchars(isset($drawing['MLR_STD_RW']) ? $drawing['MLR_STD_RW'] : ''); ?>">
                                </div>
                            </div>

                            <!-- Row 5: Description -->
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Description</label>
                                    <textarea name="MLR_DESC" class="form-control" rows="2"><?= htmlspecialchars(isset($drawing['MLR_DESC']) ? $drawing['MLR_DESC'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                            </div>

                            <!-- Row 6: Status, Effective Date -->
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Status</label>
                                    <select name="MLR_STATUS" class="form-control">
                                        <option value="2" <?= (isset($drawing['MLR_STATUS']) && (int)$drawing['MLR_STATUS'] === 2) ? 'selected' : ''; ?>>Active</option>
                                        <option value="1" <?= (isset($drawing['MLR_STATUS']) && (int)$drawing['MLR_STATUS'] === 1) ? 'selected' : ''; ?>>Pending</option>
                                        <option value="0" <?= (isset($drawing['MLR_STATUS']) && (int)$drawing['MLR_STATUS'] === 0) ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Effective Date</label>
                                    <input type="date" name="MLR_EFFECTIVE_DATE" class="form-control" value="<?= isset($drawing['MLR_EFFECTIVE_DATE']) && !empty($drawing['MLR_EFFECTIVE_DATE']) ? date('Y-m-d', strtotime($drawing['MLR_EFFECTIVE_DATE'])) : ''; ?>">
                                </div>
                            </div>

                            <!-- Row 7: Change Summary -->
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Change Summary</label>
                                    <textarea name="MLR_CHANGE_SUMMARY" class="form-control" rows="2"><?= htmlspecialchars(isset($drawing['MLR_CHANGE_SUMMARY']) ? $drawing['MLR_CHANGE_SUMMARY'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                            </div>

                            <!-- Additional Information Section -->
                            <h5 class="mt-4 mb-3 font-weight-bold">Additional Information</h5>

                            <!-- Row 8: Drawing, Material -->
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="label-required">Drawing</label>
                                    <input type="text" name="MLR_DRAWING" class="form-control" value="<?= htmlspecialchars(isset($drawing['MLR_DRAWING']) ? $drawing['MLR_DRAWING'] : (isset($drawing['ML_TOOL_DRAW_NO']) ? $drawing['ML_TOOL_DRAW_NO'] : '')); ?>" readonly>
                                    <small class="form-text text-muted">File drawing saat ini</small>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Material</label>
                                    <select name="MLR_MAT_ID" class="form-control">
                                        <option value="">-- Select Material --</option>
                                        <?php foreach ($materials as $m): ?>
                                            <option value="<?= (int)$m['MAT_ID']; ?>" <?= (isset($drawing['MLR_MAT_ID']) && (int)$drawing['MLR_MAT_ID'] === (int)$m['MAT_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($m['MAT_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Row 9: Sketch file upload -->
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Sketch</label>
                                    <input type="file" name="MLR_SKETCH_FILE" class="form-control" accept="*">
                                    <?php if (!empty($drawing['MLR_SKETCH'])): ?>
                                        <div class="small text-muted mt-1">Current: <?= htmlspecialchars($drawing['MLR_SKETCH']); ?></div>
                                    <?php else: ?>
                                        <div class="small text-muted mt-1">No file chosen</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Shared Tool Section -->
                <div class="card mb-3 mt-3" id="shared-tool-card">
                    <div class="card-header">
                        <h5 class="m-0 font-weight-bold text-primary">Shared Tool (Tool BOM yang menggunakan tool ini)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tool BOM No</th>
                                        <th>Product</th>
                                        <th>Rev</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($tool_bom_list)): ?>
                                        <?php $no = 1; foreach ($tool_bom_list as $bom): ?>
                                            <tr>
                                                <td class="text-center"><?= $no++; ?></td>
                                                <td><?= htmlspecialchars(isset($bom['TOOL_BOM']) ? $bom['TOOL_BOM'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?= htmlspecialchars(isset($bom['PRODUCT']) ? $bom['PRODUCT'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-center"><?= htmlspecialchars(isset($bom['BOM_REV']) ? $bom['BOM_REV'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-center"><?= htmlspecialchars(isset($bom['QTY']) ? $bom['QTY'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Tidak ada Tool BOM yang menggunakan tool drawing ini.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
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
            var productId = $.trim($('[name="MLR_PRODUCT_ID"]').val());
            var toolId = $.trim($('[name="MLR_TC_ID"]').val());
            var drawingNo = $.trim($('[name="ML_TOOL_DRAW_NO"]').val());
            var revision = $.trim($('[name="MLR_REV"]').val());
            var processId = $.trim($('[name="MLR_OP_ID"]').val());

            var isValid = true;
            
            if (productId === '' || productId <= 0) {
                $('[name="MLR_PRODUCT_ID"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('[name="MLR_PRODUCT_ID"]').removeClass('is-invalid');
            }
            
            if (toolId === '' || toolId <= 0) {
                $('[name="MLR_TC_ID"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('[name="MLR_TC_ID"]').removeClass('is-invalid');
            }
            
            if (drawingNo === '') {
                $('[name="ML_TOOL_DRAW_NO"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('[name="ML_TOOL_DRAW_NO"]').removeClass('is-invalid');
            }
            
            if (revision === '') {
                $('[name="MLR_REV"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('[name="MLR_REV"]').removeClass('is-invalid');
            }
            
            if (processId === '' || processId <= 0) {
                $('[name="MLR_OP_ID"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('[name="MLR_OP_ID"]').removeClass('is-invalid');
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
                        window.location.href = '<?= base_url("Tool_engineering/tool_draw_tooling"); ?>';
                    }, 600);
                } else {
                    toastr.warning(res && res.message ? res.message : 'Gagal menyimpan data');
                }
            }).fail(function(xhr, status){
                toastr.error('Gagal menyimpan: ' + status);
            });
        });

        // Update Shared Tool table when Product changes
        $('[name="MLR_PRODUCT_ID"]').on('change', function(){
            var productId = $(this).val();
            if (productId && productId > 0) {
                $.ajax({
                    url: '<?= base_url("Tool_engineering/tool_draw_tooling/get_tool_bom_by_product"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: { PRODUCT_ID: productId }
                }).done(function(res){
                    if (res && res.success && res.data) {
                        var tbody = $('#shared-tool-card table tbody');
                        tbody.empty();
                        if (res.data.length > 0) {
                            var no = 1;
                            res.data.forEach(function(bom){
                                var row = '<tr>' +
                                    '<td class="text-center">' + (no++) + '</td>' +
                                    '<td>' + (bom.TOOL_BOM || '') + '</td>' +
                                    '<td>' + (bom.PRODUCT || '') + '</td>' +
                                    '<td class="text-center">' + (bom.BOM_REV || '') + '</td>' +
                                    '<td class="text-center">' + (bom.QTY || '') + '</td>' +
                                    '</tr>';
                                tbody.append(row);
                            });
                        } else {
                            tbody.append('<tr><td colspan="5" class="text-center text-muted">Tidak ada Tool BOM yang menggunakan tool drawing ini.</td></tr>');
                        }
                    }
                }).fail(function(){
                    // Silently fail
                });
            }
        });
    });
})(jQuery);
</script>
</body>
</html>
