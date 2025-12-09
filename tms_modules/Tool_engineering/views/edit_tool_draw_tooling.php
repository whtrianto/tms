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
                            <h4 class="m-0 font-weight-bold text-primary">Edit Tool Drawing Tooling</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($drawing['TD_ID']); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_draw_tooling'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formToolDrawing" method="post" action="<?= base_url('Tool_engineering/tool_draw_tooling/submit_data'); ?>">
                            <input type="hidden" name="action" value="EDIT">
                            <input type="hidden" name="TT_ID" value="<?= htmlspecialchars($drawing['TD_ID']); ?>">

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="label-required">Tool</label>
                                    <select name="TT_TOOL_ID" class="form-control" required>
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
                                    <div class="invalid-feedback">Tool wajib dipilih.</div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Maker</label>
                                    <select name="TT_MAKER_ID" class="form-control">
                                        <option value="">-- Select Maker --</option>
                                        <?php foreach ($makers as $m): ?>
                                            <option value="<?= (int)$m['MAKER_ID']; ?>" <?= (isset($drawing['TD_MAKER_ID']) && (int)$drawing['TD_MAKER_ID'] === (int)$m['MAKER_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($m['MAKER_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Min Quantity</label>
                                    <input type="number" name="TT_MIN_QTY" class="form-control" value="<?= htmlspecialchars(isset($drawing['TD_MIN_QTY']) ? (int)$drawing['TD_MIN_QTY'] : 0); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Replenish Quantity</label>
                                    <input type="number" name="TT_REPLENISH_QTY" class="form-control" value="<?= htmlspecialchars(isset($drawing['TD_REPLENISH_QTY']) ? (int)$drawing['TD_REPLENISH_QTY'] : 0); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Price</label>
                                    <input type="number" name="TT_PRICE" class="form-control" step="0.01" value="<?= htmlspecialchars(isset($drawing['TD_PRICE']) ? (float)$drawing['TD_PRICE'] : 0); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Material</label>
                                    <select name="TT_MATERIAL_ID" class="form-control">
                                        <option value="">-- Select Material --</option>
                                        <?php foreach ($materials as $m): ?>
                                            <option value="<?= (int)$m['MATERIAL_ID']; ?>" <?= (isset($drawing['TD_MATERIAL_ID']) && (int)$drawing['TD_MATERIAL_ID'] === (int)$m['MATERIAL_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($m['MATERIAL_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Tool Life</label>
                                    <input type="number" name="TT_TOOL_LIFE" class="form-control" value="<?= htmlspecialchars(isset($drawing['TD_TOOL_LIFE']) ? (int)$drawing['TD_TOOL_LIFE'] : 0); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label>Description</label>
                                    <textarea name="TT_DESCRIPTION" class="form-control" rows="3"><?= htmlspecialchars(isset($drawing['TD_DESCRIPTION']) ? $drawing['TD_DESCRIPTION'] : '', ENT_QUOTES, 'UTF-8'); ?></textarea>
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
            var toolId = $.trim($('[name="TT_TOOL_ID"]').val());

            var isValid = true;
            if (toolId === '' || toolId <= 0) {
                $('[name="TT_TOOL_ID"]').addClass('is-invalid');
                isValid = false;
            } else {
                $('[name="TT_TOOL_ID"]').removeClass('is-invalid');
            }

            if (!isValid) return;

            var formEl = $(this)[0];
            var fd = new FormData(formEl);
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
    });
})(jQuery);
</script>
</body>
</html>

