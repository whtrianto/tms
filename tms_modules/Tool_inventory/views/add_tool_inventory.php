<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <style>
        .table td, .table th {
            color: #000 !important;
            padding: 0.35rem 0.4rem !important;
            font-size: 0.85rem;
        }
        .label-required::after {
            content: " *";
            color: #dc3545;
            font-weight: 600;
        }
        .navbar { position: sticky; top: 0; z-index: 1030; }
        #content-wrapper { min-height: calc(100vh - 56px); }
        #container-wrapper { 
            padding-bottom: 2rem !important; 
        }
        .card { 
            margin-bottom: 2rem; 
        }
        .card-body {
            padding-bottom: 5rem !important;
        }
        .section-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #dee2e6;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-check-inline-custom {
            display: inline-flex;
            align-items: center;
        }
        .form-check-inline-custom label {
            margin-right: 8px;
            margin-bottom: 0;
        }
        .form-check-inline-custom input[type="checkbox"] {
            position: static;
            margin-left: 0;
            vertical-align: middle;
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
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0 font-weight-bold text-primary">Add Tool Inventory</h4>
                        <a href="<?= base_url('Tool_inventory/tool_inventory'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="formToolInventory" method="post" action="<?= base_url('Tool_inventory/tool_inventory/submit_data'); ?>">
                            <input type="hidden" name="action" value="ADD">
                            
                            <!-- Tool Drawing Section - Full Width -->
                            <div class="section-title">Tool Drawing Information</div>
                            
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="form-group form-check-inline-custom">
                                        <label class="form-check-label" for="allowOldRevision">Allow Select Old Revision</label>
                                        <input type="checkbox" class="form-check-input" id="allowOldRevision" name="allow_old_revision" value="1">
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool Drawing No.</label>
                                        <select name="tool_drawing_no" id="tool_drawing_no" class="form-control" required>
                                            <option value="">-- Select Tool Drawing No. --</option>
                                            <?php foreach ($tool_drawing_nos as $tdn): ?>
                                                <option value="<?= (int)$tdn['ML_ID']; ?>" data-mlr-id="<?= isset($tdn['LATEST_MLR_ID']) ? (int)$tdn['LATEST_MLR_ID'] : 0; ?>">
                                                    <?= htmlspecialchars($tdn['ML_TOOL_DRAW_NO'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <select name="mlr_revision" id="mlr_revision" class="form-control mt-2" style="display:none;">
                                            <option value="">-- Select Revision --</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool ID</label>
                                        <select name="tool_id" id="tool_id" class="form-control" required>
                                            <option value="">-- Select Tool ID --</option>
                                            <?php if (isset($existing_tool_ids) && is_array($existing_tool_ids) && count($existing_tool_ids) > 0): ?>
                                                <?php foreach ($existing_tool_ids as $tid): ?>
                                                    <option value="<?= htmlspecialchars($tid['INV_TOOL_ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-process-id="<?= isset($tid['PROCESS_ID']) ? (int)$tid['PROCESS_ID'] : ''; ?>"
                                                            data-tool-name-id="<?= isset($tid['TOOL_NAME_ID']) ? (int)$tid['TOOL_NAME_ID'] : ''; ?>"
                                                            data-revision="<?= isset($tid['REVISION']) ? (int)$tid['REVISION'] : ''; ?>"
                                                            data-product-id="<?= isset($tid['PRODUCT_ID']) ? (int)$tid['PRODUCT_ID'] : ''; ?>">
                                                        <?= htmlspecialchars($tid['INV_TOOL_ID'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                        <small class="form-text text-muted">Pilih Tool ID yang sudah ada untuk auto-fill data. (Menampilkan maksimal 500 Tool ID terbaru)</small>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Product</label>
                                        <select name="product_id" id="product_id" class="form-control" required disabled>
                                            <option value="">-- Select Product --</option>
                                            <?php foreach ($products as $p): ?>
                                                <option value="<?= (int)$p['PRODUCT_ID']; ?>">
                                                    <?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="product_id" id="product_id_hidden" value="">
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Process</label>
                                        <select name="process_id" id="process_id" class="form-control" required disabled>
                                            <option value="">-- Select Process --</option>
                                            <?php foreach ($operations as $o): ?>
                                                <option value="<?= (int)$o['OPERATION_ID']; ?>">
                                                    <?= htmlspecialchars($o['OPERATION_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="process_id" id="process_id_hidden" value="">
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool Name</label>
                                        <select name="tool_name" id="tool_name" class="form-control" required disabled>
                                            <option value="">-- Select Tool Name --</option>
                                            <?php foreach ($tools as $t): ?>
                                                <option value="<?= (int)$t['TOOL_ID']; ?>">
                                                    <?= htmlspecialchars($t['TOOL_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="tool_name" id="tool_name_hidden" value="">
                                    </div>

                                    <div class="form-group">
                                        <label>Revision</label>
                                        <input type="number" name="revision" id="revision" class="form-control" placeholder="Enter Revision" min="0" value="0" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool Tag</label>
                                        <input type="text" name="tool_tag" id="tool_tag" class="form-control" placeholder="Enter Tool Tag" value="<?= isset($next_tool_tag) ? htmlspecialchars($next_tool_tag, ENT_QUOTES, 'UTF-8') : '1'; ?>" required>
                                        <small class="form-text text-muted">Auto-generated from latest number</small>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool Status</label>
                                        <select name="tool_status" class="form-control" required>
                                            <option value="1">New</option>
                                            <option value="2">Allocated</option>
                                            <option value="3">Available</option>
                                            <option value="4">InUsed</option>
                                            <option value="5">Onhold</option>
                                            <option value="6">Scrapped</option>
                                            <option value="7">Repairing</option>
                                            <option value="8">Modifying</option>
                                            <option value="9">DesignChange</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool Condition</label>
                                        <input type="number" name="tool_condition" class="form-control" placeholder="Enter Tool Condition" min="0">
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="form-group form-check-inline-custom">
                                        <label class="form-check-label" for="assetized">Assetized</label>
                                        <input type="checkbox" class="form-check-input" id="assetized" name="assetized" value="1">
                                    </div>

                                    <div class="form-group">
                                        <label>In Tool Set</label>
                                        <input type="number" name="in_tool_set" class="form-control" placeholder="Enter In Tool Set" min="0">
                                    </div>

                                    <div class="form-group">
                                        <label>Storage Location</label>
                                        <select name="storage_location_id" class="form-control">
                                            <option value="">-- Select Storage Location --</option>
                                            <?php foreach ($storage_locations as $sl): ?>
                                                <option value="<?= (int)$sl['STORAGE_LOCATION_ID']; ?>">
                                                    <?= htmlspecialchars($sl['STORAGE_LOCATION_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter Notes"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Begin Cycle</label>
                                        <input type="number" name="begin_cycle" class="form-control" placeholder="Enter Begin Cycle" min="0" value="0">
                                    </div>

                                    <div class="form-group">
                                        <label>End Cycle</label>
                                        <input type="number" name="end_cycle" class="form-control" placeholder="Enter End Cycle" min="0" value="0" readonly>
                                        <small class="form-text text-muted">Automatically calculated</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Received Date</label>
                                        <input type="date" name="received_date" class="form-control">
                                    </div>

                                    <div class="form-group">
                                        <label>Do No.</label>
                                        <input type="text" name="do_no" class="form-control" placeholder="Enter Do No.">
                                    </div>
                                </div>
                            </div>

                            <!-- Order Information Section - Full Width -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="section-title">Order Information</div>
                                    
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>RQ No.</label>
                                                <select name="rq_no" class="form-control">
                                                    <option value="">-- Select RQ No. --</option>
                                                    <?php foreach ($rq_numbers as $rq): ?>
                                                        <option value="<?= htmlspecialchars($rq['RQ_NO'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?= htmlspecialchars($rq['RQ_NO'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Maker</label>
                                                <select name="maker_id" id="maker_id" class="form-control">
                                                    <option value="">-- Select Maker --</option>
                                                    <?php foreach ($makers as $mk): ?>
                                                        <option value="<?= (int)$mk['MAKER_ID']; ?>" data-maker-code="<?= htmlspecialchars(isset($mk['MAKER_CODE']) ? $mk['MAKER_CODE'] : '', ENT_QUOTES, 'UTF-8'); ?>">
                                                            <?= htmlspecialchars($mk['MAKER_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Maker Code</label>
                                                <input type="text" name="maker_code" id="maker_code" class="form-control" placeholder="Maker Code" readonly>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Material</label>
                                                <select name="material_id" class="form-control">
                                                    <option value="">-- Select Material --</option>
                                                    <?php foreach ($materials as $m): ?>
                                                        <option value="<?= (int)$m['MATERIAL_ID']; ?>">
                                                            <?= htmlspecialchars($m['MATERIAL_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Purchase Type</label>
                                                <select name="purchase_type" class="form-control">
                                                    <option value="">-- Select Purchase Type --</option>
                                                    <option value="Local">Local</option>
                                                    <option value="Overseas">Overseas</option>
                                                    <option value="Internal Fabrication">Internal Fabrication</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
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
        // Handle Tool Drawing No change - load revisions if "Allow Select Old Revision" is checked
        $('#tool_drawing_no').on('change', function() {
            var mlId = $(this).val();
            var allowOld = $('#allowOldRevision').is(':checked');
            var $revisionSelect = $('#mlr_revision');
            
            if (mlId && allowOld) {
                // Show loading
                $revisionSelect.html('<option value="">Loading...</option>').show();
                
                // Load revisions via AJAX
                $.ajax({
                    url: '<?= base_url("Tool_inventory/tool_inventory/get_revisions"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: { ml_id: mlId }
                }).done(function(res) {
                    if (res && res.success && res.data && res.data.length > 0) {
                        $revisionSelect.html('<option value="">-- Select Revision --</option>');
                        $.each(res.data, function(i, rev) {
                            $revisionSelect.append(
                                '<option value="' + rev.MLR_ID + '">Revision ' + rev.MLR_REV + 
                                (rev.MLR_STATUS === 2 ? ' (Active)' : '') + '</option>'
                            );
                        });
                    } else {
                        $revisionSelect.html('<option value="">No revisions found</option>');
                    }
                }).fail(function() {
                    $revisionSelect.html('<option value="">Error loading revisions</option>');
                });
            } else {
                $revisionSelect.hide().val('');
                // If not allowing old revision, use latest MLR_ID from selected option
                if (mlId && !allowOld) {
                    var selectedOption = $('#tool_drawing_no option:selected');
                    var latestMlrId = selectedOption.data('mlr-id') || '';
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'mlr_id',
                        value: latestMlrId
                    }).appendTo('#formToolInventory');
                } else {
                    $('input[name="mlr_id"]').remove();
                }
            }
        });

        // Handle "Allow Select Old Revision" checkbox
        $('#allowOldRevision').on('change', function() {
            $('input[name="mlr_id"]').remove(); // Remove hidden field if exists
            if ($(this).is(':checked')) {
                $('#tool_drawing_no').trigger('change');
            } else {
                $('#mlr_revision').hide().val('');
                // If unchecking, use latest revision
                var mlId = $('#tool_drawing_no').val();
                if (mlId) {
                    var selectedOption = $('#tool_drawing_no option:selected');
                    var latestMlrId = selectedOption.data('mlr-id') || '';
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'mlr_id',
                        value: latestMlrId
                    }).appendTo('#formToolInventory');
                }
            }
        });

        // Handle Tool ID selection - auto-fill Product, Process, Tool Name, and Revision
        $('#tool_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var processId = selectedOption.data('process-id') || '';
            var toolNameId = selectedOption.data('tool-name-id') || '';
            var revision = selectedOption.data('revision') || '';
            var productId = selectedOption.data('product-id') || '';

            // Auto-fill Product (disabled select + hidden input)
            if (productId) {
                $('#product_id').val(productId);
                $('#product_id_hidden').val(productId);
            } else {
                $('#product_id').val('');
                $('#product_id_hidden').val('');
            }

            // Auto-fill Process (disabled select + hidden input)
            if (processId) {
                $('#process_id').val(processId);
                $('#process_id_hidden').val(processId);
            } else {
                $('#process_id').val('');
                $('#process_id_hidden').val('');
            }

            // Auto-fill Tool Name (disabled select + hidden input)
            if (toolNameId) {
                $('#tool_name').val(toolNameId);
                $('#tool_name_hidden').val(toolNameId);
            } else {
                $('#tool_name').val('');
                $('#tool_name_hidden').val('');
            }

            // Auto-fill Revision (readonly input)
            if (revision !== '') {
                $('#revision').val(revision);
            } else {
                $('#revision').val('0');
            }
        });

        // Handle Maker selection - auto-fill Maker Code
        $('#maker_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var makerCode = selectedOption.data('maker-code') || '';
            $('#maker_code').val(makerCode);
        });

        // Form submit with AJAX
        $('#formToolInventory').on('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                $(this).addClass('was-validated');
                return;
            }

            var formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                cache: false,
                timeout: 30000
            }).done(function(res) {
                if (res && res.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Tool Inventory berhasil ditambahkan');
                    } else {
                        alert(res.message || 'Tool Inventory berhasil ditambahkan');
                    }
                    setTimeout(function() {
                        window.location.href = '<?= base_url("Tool_inventory/tool_inventory"); ?>';
                    }, 1000);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(res && res.message ? res.message : 'Gagal menyimpan Tool Inventory');
                    } else {
                        alert(res && res.message ? res.message : 'Gagal menyimpan Tool Inventory');
                    }
                }
            }).fail(function(xhr, status, error) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Gagal menyimpan: ' + (error || status));
                } else {
                    alert('Gagal menyimpan: ' + (error || status));
                }
            });
        });
    });
})(jQuery);
</script>
</body>
</html>

