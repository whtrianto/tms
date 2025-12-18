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
            padding-bottom: 4rem !important; 
            margin-bottom: 2rem !important; 
        }
        .card { margin-bottom: 2rem; }
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
                            
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <!-- Tool Drawing Section -->
                                    <div class="section-title">Tool Drawing Information</div>
                                    
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="allowOldRevision" name="allow_old_revision" value="1">
                                            <label class="form-check-label" for="allowOldRevision">Allow Select Old Revision</label>
                                        </div>
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
                                        <input type="text" name="tool_id" class="form-control" placeholder="Enter Tool ID" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Product</label>
                                        <select name="product_id" class="form-control" required>
                                            <option value="">-- Select Product --</option>
                                            <?php foreach ($products as $p): ?>
                                                <option value="<?= (int)$p['PRODUCT_ID']; ?>">
                                                    <?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Process</label>
                                        <select name="process_id" class="form-control" required>
                                            <option value="">-- Select Process --</option>
                                            <?php foreach ($operations as $o): ?>
                                                <option value="<?= (int)$o['OPERATION_ID']; ?>">
                                                    <?= htmlspecialchars($o['OPERATION_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool Name</label>
                                        <select name="tool_name" class="form-control" required>
                                            <option value="">-- Select Tool Name --</option>
                                            <?php foreach ($tools as $t): ?>
                                                <option value="<?= (int)$t['TOOL_ID']; ?>">
                                                    <?= htmlspecialchars($t['TOOL_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="assetized" name="assetized" value="1">
                                            <label class="form-check-label" for="assetized">Assetized</label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Revision</label>
                                        <input type="number" name="revision" class="form-control" placeholder="Enter Revision" min="0" value="0">
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool Tag</label>
                                        <input type="text" name="tool_tag" class="form-control" placeholder="Enter Tool Tag" required>
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

                                    <!-- Order Information Section -->
                                    <div class="section-title mt-4">Order Information</div>

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

                                    <div class="form-group">
                                        <label>Maker Code</label>
                                        <input type="text" name="maker_code" id="maker_code" class="form-control" placeholder="Maker Code" readonly>
                                    </div>

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

                                    <div class="form-group">
                                        <label>Purchase Type</label>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="purchase_type_local" name="purchase_type" value="Local">
                                            <label class="form-check-label" for="purchase_type_local">Local</label>
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

        // Handle Maker selection - auto-fill Maker Code
        $('#maker_id').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var makerCode = selectedOption.data('maker-code') || '';
            $('#maker_code').val(makerCode);
        });

        // Form validation
        $('#formToolInventory').on('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            $(this).addClass('was-validated');
        });
    });
})(jQuery);
</script>
</body>
</html>

