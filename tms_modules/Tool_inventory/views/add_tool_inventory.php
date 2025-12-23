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
                                        <div class="input-group">
                                            <input type="text" name="tool_drawing_no_display" id="tool_drawing_no_display" class="form-control" placeholder="Click to select Tool Drawing No." readonly required>
                                            <input type="hidden" name="tool_drawing_no" id="tool_drawing_no" value="">
                                            <input type="hidden" name="mlr_id" id="mlr_id_hidden" value="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btn-select-tool-drawing" data-toggle="modal" data-target="#modalToolDrawing">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>
                                        <select name="mlr_revision" id="mlr_revision" class="form-control mt-2" style="display:none;">
                                            <option value="">-- Select Revision --</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool ID</label>
                                        <input type="text" name="tool_id" id="tool_id" class="form-control" placeholder="Auto-generated from Tool Drawing No." required readonly>
                                        <small class="form-text text-muted">Tool ID akan terisi otomatis dari Tool Drawing No. yang dipilih.</small>
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

                                </div>
                                
                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="form-group form-check-inline-custom">
                                        <label class="form-check-label" for="assetized">Assetized</label>
                                        <input type="checkbox" class="form-check-input" id="assetized" name="assetized" value="1">
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
                                    <div class="form-group">
                                        <label>In Tool Set</label>
                                        <input type="number" name="in_tool_set" class="form-control" placeholder="Enter In Tool Set" min="0">
                                    </div>

                                    <div class="form-group">
                                        <label>Storage Location</label>
                                        <div class="input-group">
                                            <input type="text" name="storage_location_display" id="storage_location_display" class="form-control" placeholder="Click to select Storage Location" readonly>
                                            <input type="hidden" name="storage_location_id" id="storage_location_id" value="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btn-select-storage-location" data-toggle="modal" data-target="#modalStorageLocation">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>
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
                                                <div class="input-group">
                                                    <input type="text" name="maker_display" id="maker_display" class="form-control" placeholder="Click to select Maker" readonly>
                                                    <input type="hidden" name="maker_id" id="maker_id" value="">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-primary" id="btn-select-maker" data-toggle="modal" data-target="#modalMaker">
                                                            <i class="fa fa-search"></i> Select
                                                        </button>
                                                    </div>
                                                </div>
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

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Received Date</label>
                                                <input type="date" name="received_date" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Do No.</label>
                                                <input type="text" name="do_no" class="form-control" placeholder="Enter Do No.">
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

<!-- Modal for Maker Selection -->
<div class="modal fade" id="modalMaker" tabindex="-1" role="dialog" aria-labelledby="modalMakerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMakerLabel">Select Maker</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-maker-modal" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($makers_modal) && is_array($makers_modal)): ?>
                                <?php foreach ($makers_modal as $mk): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($mk['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($mk['NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($mk['DESCRIPTION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-select-maker" 
                                                    data-id="<?= htmlspecialchars($mk['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-name="<?= htmlspecialchars($mk['NAME'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-maker-code="<?= htmlspecialchars($mk['MAKER_CODE'], ENT_QUOTES, 'UTF-8'); ?>">
                                                Select
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Tool Drawing Selection -->
<div class="modal fade" id="modalToolDrawing" tabindex="-1" role="dialog" aria-labelledby="modalToolDrawingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalToolDrawingLabel">Select Tool Drawing No.</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-tool-drawing-modal" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Drawing No.</th>
                                <th>Tool Name</th>
                                <th>Revision</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($tool_drawings_modal) && is_array($tool_drawings_modal)): ?>
                                <?php foreach ($tool_drawings_modal as $td): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($td['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($td['DRAWING_NO'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($td['TOOL_NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($td['REVISION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($td['DESCRIPTION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-select-td" 
                                                    data-ml-id="<?= htmlspecialchars($td['ML_ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-mlr-id="<?= htmlspecialchars($td['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-drawing-no="<?= htmlspecialchars($td['DRAWING_NO'], ENT_QUOTES, 'UTF-8'); ?>">
                                                Select
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Storage Location Selection -->
<div class="modal fade" id="modalStorageLocation" tabindex="-1" role="dialog" aria-labelledby="modalStorageLocationLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalStorageLocationLabel">Select Storage Location</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-storage-location-modal" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($storage_locations_modal) && is_array($storage_locations_modal)): ?>
                                <?php foreach ($storage_locations_modal as $sl): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($sl['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($sl['NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($sl['DESCRIPTION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-select-sl" 
                                                    data-id="<?= htmlspecialchars($sl['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-name="<?= htmlspecialchars($sl['NAME'], ENT_QUOTES, 'UTF-8'); ?>">
                                                Select
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?= isset($foot) ? $foot : ''; ?>
<link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
<link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
<script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js'); ?>"></script>
<script>
(function($){
    $(function(){
        // Initialize DataTable for Maker Modal
        var tableMaker = $('#table-maker-modal').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[1, 'asc']], // Order by Name
            columnDefs: [
                { orderable: false, targets: [3] } // Action column not sortable
            ]
        });

        // Handle Maker selection from modal
        $(document).on('click', '.btn-select-maker', function() {
            var makerId = $(this).data('id');
            var makerName = $(this).data('name');
            var makerCode = $(this).data('maker-code') || '';
            
            // Set values
            $('#maker_id').val(makerId);
            $('#maker_display').val(makerName);
            $('#maker_code').val(makerCode);
            
            // Close modal
            $('#modalMaker').modal('hide');
        });

        // Initialize DataTable for Storage Location Modal
        var tableStorageLocation = $('#table-storage-location-modal').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[1, 'asc']], // Order by Name
            columnDefs: [
                { orderable: false, targets: [3] } // Action column not sortable
            ]
        });

        // Handle Storage Location selection from modal
        $(document).on('click', '.btn-select-sl', function() {
            var slId = $(this).data('id');
            var slName = $(this).data('name');
            
            // Set values
            $('#storage_location_id').val(slId);
            $('#storage_location_display').val(slName);
            
            // Close modal
            $('#modalStorageLocation').modal('hide');
        });

        // Initialize DataTable for Tool Drawing Modal
        var tableToolDrawing = $('#table-tool-drawing-modal').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[1, 'asc']], // Order by Drawing No.
            columnDefs: [
                { orderable: false, targets: [5] } // Action column not sortable
            ]
        });

        // Handle Tool Drawing selection from modal
        $(document).on('click', '.btn-select-td', function() {
            var mlId = $(this).data('ml-id');
            var mlrId = $(this).data('mlr-id');
            var drawingNo = $(this).data('drawing-no');
            
            // Set values
            $('#tool_drawing_no').val(mlId);
            $('#tool_drawing_no_display').val(drawingNo);
            $('#mlr_id_hidden').val(mlrId);
            
            // Close modal
            $('#modalToolDrawing').modal('hide');
            
            // Auto-generate Tool ID from Drawing No and Tool Tag
            generateToolId(drawingNo);
            
            // Auto-fill Product, Process, Tool Name, Revision
            if (mlrId) {
                loadToolDrawingDetails(mlrId);
            }
            
            // Handle "Allow Select Old Revision" checkbox
            var allowOld = $('#allowOldRevision').is(':checked');
            if (allowOld && mlId) {
                // Load revisions
                loadRevisionsForToolDrawing(mlId);
            } else {
                $('#mlr_revision').hide().val('');
            }
        });

        // Function to load revisions for Tool Drawing
        function loadRevisionsForToolDrawing(mlId) {
            var $revisionSelect = $('#mlr_revision');
            $revisionSelect.html('<option value="">Loading...</option>').show();
            
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
        }

        // Handle Tool Drawing No change (for backward compatibility if needed)
        $('#tool_drawing_no').on('change', function() {
            var mlId = $(this).val();
            var allowOld = $('#allowOldRevision').is(':checked');
            var $revisionSelect = $('#mlr_revision');
            
            // Remove existing mlr_id hidden input
            $('input[name="mlr_id"]').remove();
            
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
                    var drawingNo = $('#tool_drawing_no_display').val();
                    if (latestMlrId) {
                        // Auto-generate Tool ID
                        if (drawingNo) {
                            generateToolId(drawingNo);
                        }
                        // Auto-fill Product, Process, Tool Name, Revision from Tool Drawing No
                        loadToolDrawingDetails(latestMlrId);
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'mlr_id',
                        value: latestMlrId
                    }).appendTo('#formToolInventory');
                    }
                } else {
                    // Clear fields if no Tool Drawing No selected
                    clearAutoFillFields();
                }
            }
        });

        // Handle Revision selection change (when "Allow Select Old Revision" is checked)
        $('#mlr_revision').on('change', function() {
            var mlrId = $(this).val();
            if (mlrId) {
                // Remove existing mlr_id hidden input
                $('input[name="mlr_id"]').remove();
                // Add new mlr_id hidden input
                $('<input>').attr({
                    type: 'hidden',
                    name: 'mlr_id',
                    value: mlrId
                }).appendTo('#formToolInventory');
                // Auto-generate Tool ID
                var drawingNo = $('#tool_drawing_no_display').val();
                if (drawingNo) {
                    generateToolId(drawingNo);
                }
                // Auto-fill Product, Process, Tool Name, Revision
                loadToolDrawingDetails(mlrId);
            } else {
                clearAutoFillFields();
            }
        });

        // Function to generate Tool ID from Drawing No and Tool Tag
        function generateToolId(drawingNo) {
            if (!drawingNo || drawingNo === '') {
                $('#tool_id').val('');
                return;
            }
            
            var toolTag = $('#tool_tag').val() || '';
            if (toolTag === '') {
                // If Tool Tag is empty, use Drawing No only
                $('#tool_id').val(drawingNo);
            } else {
                // Generate Tool ID: Drawing No + "-" + Tool Tag
                $('#tool_id').val(drawingNo + '-' + toolTag);
            }
        }

        // Handle Tool Tag change - regenerate Tool ID
        $('#tool_tag').on('input change', function() {
            var drawingNo = $('#tool_drawing_no_display').val();
            if (drawingNo) {
                generateToolId(drawingNo);
            }
        });

        // Function to load Tool Drawing details and auto-fill fields
        function loadToolDrawingDetails(mlrId) {
            if (!mlrId || mlrId === '') {
                clearAutoFillFields();
                return;
            }

            $.ajax({
                url: '<?= base_url("Tool_inventory/tool_inventory/get_tool_drawing_details"); ?>',
                type: 'POST',
                dataType: 'json',
                data: { mlr_id: mlrId }
            }).done(function(res) {
                if (res && res.success && res.data) {
                    var d = res.data;
                    
                    // Auto-fill Product
                    if (d.PRODUCT_ID) {
                        $('#product_id').val(d.PRODUCT_ID);
                        $('#product_id_hidden').val(d.PRODUCT_ID);
                    } else {
                        $('#product_id').val('');
                        $('#product_id_hidden').val('');
                    }

                    // Auto-fill Process
                    if (d.PROCESS_ID) {
                        $('#process_id').val(d.PROCESS_ID);
                        $('#process_id_hidden').val(d.PROCESS_ID);
                    } else {
                        $('#process_id').val('');
                        $('#process_id_hidden').val('');
                    }

                    // Auto-fill Tool Name
                    if (d.TOOL_NAME_ID) {
                        $('#tool_name').val(d.TOOL_NAME_ID);
                        $('#tool_name_hidden').val(d.TOOL_NAME_ID);
                    } else {
                        $('#tool_name').val('');
                        $('#tool_name_hidden').val('');
                    }

                    // Auto-fill Revision
                    if (d.REVISION !== undefined && d.REVISION !== null) {
                        $('#revision').val(d.REVISION);
                    } else {
                        $('#revision').val('0');
                    }
                } else {
                    clearAutoFillFields();
                }
            }).fail(function() {
                clearAutoFillFields();
            });
        }

        // Function to clear auto-fill fields
        function clearAutoFillFields() {
            $('#product_id').val('');
            $('#product_id_hidden').val('');
            $('#process_id').val('');
            $('#process_id_hidden').val('');
            $('#tool_name').val('');
            $('#tool_name_hidden').val('');
            $('#revision').val('0');
            $('#tool_id').val('');
        }

        // Handle "Allow Select Old Revision" checkbox
        $('#allowOldRevision').on('change', function() {
            $('input[name="mlr_id"]').remove(); // Remove hidden field if exists
            if ($(this).is(':checked')) {
                $('#tool_drawing_no').trigger('change');
            } else {
                $('#mlr_revision').hide().val('');
                // If unchecking, use latest revision and auto-fill
                var mlId = $('#tool_drawing_no').val();
                if (mlId) {
                    var selectedOption = $('#tool_drawing_no option:selected');
                    var latestMlrId = selectedOption.data('mlr-id') || '';
                    var drawingNo = $('#tool_drawing_no_display').val();
                    if (latestMlrId) {
                        // Auto-generate Tool ID
                        if (drawingNo) {
                            generateToolId(drawingNo);
                        }
                        loadToolDrawingDetails(latestMlrId);
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'mlr_id',
                        value: latestMlrId
                    }).appendTo('#formToolInventory');
                    }
                } else {
                    clearAutoFillFields();
                }
            }
        });

        // Tool ID selection - no longer auto-fills Product, Process, Tool Name, Revision
        // These fields are now filled from Tool Drawing No only

        // Maker selection is now handled via modal popup (see .btn-select-maker handler above)

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

