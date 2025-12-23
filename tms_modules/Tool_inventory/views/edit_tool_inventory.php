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
                        <div>
                            <h4 class="m-0 font-weight-bold text-primary">Edit Tool Inventory</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($inventory['INV_ID']); ?></div>
                        </div>
                        <a href="<?= base_url('Tool_inventory/tool_inventory'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="formToolInventory" method="post" action="<?= base_url('Tool_inventory/tool_inventory/submit_data'); ?>">
                            <input type="hidden" name="action" value="EDIT">
                            <input type="hidden" name="INV_ID" value="<?= htmlspecialchars($inventory['INV_ID']); ?>">
                            
                            <!-- Tool Drawing Section - Full Width -->
                            <div class="section-title">Tool Drawing Information</div>
                            
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="form-group form-check-inline-custom">
                                        <label class="form-check-label" for="allowOldRevision">Allow Select Old Revision</label>
                                        <input type="checkbox" class="form-check-input" id="allowOldRevision" name="allow_old_revision" value="1" <?= isset($inventory['INV_MLR_ID']) && $inventory['INV_MLR_ID'] ? '' : ''; ?>>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool Drawing No.</label>
                                        <input type="text" class="form-control" value="<?= isset($inventory['TOOL_DRAWING_NO']) ? htmlspecialchars($inventory['TOOL_DRAWING_NO'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                        <input type="hidden" name="tool_drawing_no" id="tool_drawing_no" value="<?= isset($inventory['TOOL_DRAWING_ML_ID']) ? (int)$inventory['TOOL_DRAWING_ML_ID'] : ''; ?>">
                                        <input type="hidden" name="mlr_id" id="mlr_id" value="<?= isset($inventory['INV_MLR_ID']) ? (int)$inventory['INV_MLR_ID'] : ''; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool ID</label>
                                        <input type="text" name="tool_id" id="tool_id" class="form-control" placeholder="Enter Tool ID" value="<?= isset($inventory['INV_TOOL_ID']) ? htmlspecialchars($inventory['INV_TOOL_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Product</label>
                                        <select name="product_id" id="product_id" class="form-control" required disabled>
                                            <option value="">-- Select Product --</option>
                                            <?php foreach ($products as $p): ?>
                                                <option value="<?= (int)$p['PRODUCT_ID']; ?>" <?= (isset($inventory['PRODUCT_ID']) && (int)$inventory['PRODUCT_ID'] === (int)$p['PRODUCT_ID']) ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="product_id" id="product_id_hidden" value="<?= isset($inventory['PRODUCT_ID']) ? (int)$inventory['PRODUCT_ID'] : ''; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Process</label>
                                        <select name="process_id" id="process_id" class="form-control" required disabled>
                                            <option value="">-- Select Process --</option>
                                            <?php foreach ($operations as $o): ?>
                                                <option value="<?= (int)$o['OPERATION_ID']; ?>" <?= (isset($inventory['PROCESS_ID']) && (int)$inventory['PROCESS_ID'] === (int)$o['OPERATION_ID']) ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($o['OPERATION_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="process_id" id="process_id_hidden" value="<?= isset($inventory['PROCESS_ID']) ? (int)$inventory['PROCESS_ID'] : ''; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool Name</label>
                                        <select name="tool_name" id="tool_name" class="form-control" required disabled>
                                            <option value="">-- Select Tool Name --</option>
                                            <?php foreach ($tools as $t): ?>
                                                <option value="<?= (int)$t['TOOL_ID']; ?>" <?= (isset($inventory['TOOL_NAME_ID']) && (int)$inventory['TOOL_NAME_ID'] === (int)$t['TOOL_ID']) ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($t['TOOL_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="tool_name" id="tool_name_hidden" value="<?= isset($inventory['TOOL_NAME_ID']) ? (int)$inventory['TOOL_NAME_ID'] : ''; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>Revision</label>
                                        <input type="number" name="revision" id="revision" class="form-control" placeholder="Enter Revision" min="0" value="<?= isset($inventory['REVISION']) ? htmlspecialchars($inventory['REVISION'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                    </div>

                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="form-group form-check-inline-custom">
                                        <label class="form-check-label" for="assetized">Assetized</label>
                                        <input type="checkbox" class="form-check-input" id="assetized" name="assetized" value="1" <?= (isset($inventory['INV_ASSETIZED']) && $inventory['INV_ASSETIZED'] == 1) ? 'checked' : ''; ?>>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool Tag</label>
                                        <input type="text" name="tool_tag" id="tool_tag" class="form-control" placeholder="Enter Tool Tag" value="<?= isset($inventory['INV_TOOL_TAG']) ? htmlspecialchars($inventory['INV_TOOL_TAG'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                    </div>
    
                                    <div class="form-group">
                                        <label class="label-required">Tool Status</label>
                                        <input type="text" class="form-control" value="<?php
                                            $status_map = array(
                                                1 => 'New',
                                                2 => 'Allocated',
                                                3 => 'Available',
                                                4 => 'InUsed',
                                                5 => 'Onhold',
                                                6 => 'Scrapped',
                                                7 => 'Repairing',
                                                8 => 'Modifying',
                                                9 => 'DesignChange'
                                            );
                                            $status = isset($inventory['INV_STATUS']) ? (int)$inventory['INV_STATUS'] : 1;
                                            echo htmlspecialchars(isset($status_map[$status]) ? $status_map[$status] : 'New', ENT_QUOTES, 'UTF-8');
                                        ?>" readonly>
                                        <input type="hidden" name="tool_status" value="<?= isset($inventory['INV_STATUS']) ? (int)$inventory['INV_STATUS'] : 1; ?>">
                                    </div>
    
                                    <div class="form-group">
                                        <label>Tool Condition</label>
                                        <input type="number" name="tool_condition" class="form-control" placeholder="Enter Tool Condition" min="0" value="<?= isset($inventory['INV_TOOL_CONDITION']) ? htmlspecialchars($inventory['INV_TOOL_CONDITION'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>In Tool Set</label>
                                        <input type="number" name="in_tool_set" class="form-control" placeholder="Enter In Tool Set" min="0" value="<?= isset($inventory['INV_IN_TOOL_SET']) ? htmlspecialchars($inventory['INV_IN_TOOL_SET'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>Storage Location</label>
                                        <div class="input-group">
                                            <input type="text" name="storage_location_display" id="storage_location_display" class="form-control" placeholder="Click to select Storage Location" readonly value="<?= isset($inventory['STORAGE_LOCATION_NAME']) ? htmlspecialchars($inventory['STORAGE_LOCATION_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                            <input type="hidden" name="storage_location_id" id="storage_location_id" value="<?= isset($inventory['STORAGE_LOCATION_ID']) ? (int)$inventory['STORAGE_LOCATION_ID'] : ''; ?>">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btn-select-storage-location" data-toggle="modal" data-target="#modalStorageLocation">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter Notes"><?= isset($inventory['NOTES']) ? htmlspecialchars($inventory['NOTES'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Begin Cycle</label>
                                        <input type="number" name="begin_cycle" class="form-control" placeholder="Enter Begin Cycle" min="0" value="<?= isset($inventory['INV_BEGIN_CYCLE']) ? htmlspecialchars($inventory['INV_BEGIN_CYCLE'], ENT_QUOTES, 'UTF-8') : '0'; ?>">
                                    </div>

                                    <div class="form-group">
                                        <label>End Cycle</label>
                                        <input type="number" name="end_cycle" class="form-control" placeholder="Enter End Cycle" min="0" value="<?= isset($inventory['END_CYCLE']) ? htmlspecialchars($inventory['END_CYCLE'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
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
                                                        <option value="<?= htmlspecialchars($rq['RQ_NO'], ENT_QUOTES, 'UTF-8'); ?>" <?= (isset($inventory['RQ_NO']) && $inventory['RQ_NO'] === $rq['RQ_NO']) ? 'selected' : ''; ?>>
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
                                                    <input type="text" name="maker_display" id="maker_display" class="form-control" placeholder="Click to select Maker" readonly value="<?= isset($inventory['MAKER_NAME']) ? htmlspecialchars($inventory['MAKER_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                                    <input type="hidden" name="maker_id" id="maker_id" value="<?= isset($inventory['INV_MAKER_ID']) ? (int)$inventory['INV_MAKER_ID'] : ''; ?>">
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
                                                <input type="text" name="maker_code" id="maker_code" class="form-control" placeholder="Maker Code" readonly value="<?= isset($inventory['MAKER_CODE']) ? htmlspecialchars($inventory['MAKER_CODE'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Material</label>
                                                <select name="material_id" class="form-control">
                                                    <option value="">-- Select Material --</option>
                                                    <?php foreach ($materials as $m): ?>
                                                        <option value="<?= (int)$m['MATERIAL_ID']; ?>" <?= (isset($inventory['MATERIAL_ID']) && (int)$inventory['MATERIAL_ID'] === (int)$m['MATERIAL_ID']) ? 'selected' : ''; ?>>
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
                                                    <?php 
                                                    $purchase_type_value = isset($inventory['INV_PURCHASE_TYPE_STRING']) ? $inventory['INV_PURCHASE_TYPE_STRING'] : 
                                                                          (isset($inventory['INV_PURCHASE_TYPE']) ? $inventory['INV_PURCHASE_TYPE'] : '');
                                                    ?>
                                                    <option value="Local" <?= ($purchase_type_value === 'Local' || $purchase_type_value === '1' || $purchase_type_value === 1) ? 'selected' : ''; ?>>Local</option>
                                                    <option value="Overseas" <?= ($purchase_type_value === 'Overseas' || $purchase_type_value === '2' || $purchase_type_value === 2) ? 'selected' : ''; ?>>Overseas</option>
                                                    <option value="Internal Fabrication" <?= ($purchase_type_value === 'Internal Fabrication' || $purchase_type_value === '3' || $purchase_type_value === 3) ? 'selected' : ''; ?>>Internal Fabrication</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Received Date</label>
                                                <input type="date" name="received_date" class="form-control" value="<?= isset($inventory['RECEIVED_DATE']) && !empty($inventory['RECEIVED_DATE']) ? date('Y-m-d', strtotime($inventory['RECEIVED_DATE'])) : ''; ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Do No.</label>
                                                <input type="text" name="do_no" class="form-control" placeholder="Enter Do No." value="<?= isset($inventory['DO_NO']) ? htmlspecialchars($inventory['DO_NO'], ENT_QUOTES, 'UTF-8') : ''; ?>">
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

        // Handle Tool Drawing No change - load revisions if "Allow Select Old Revision" is checked
        $('#tool_drawing_no').on('change', function() {
            var mlId = $(this).val();
            var allowOld = $('#allowOldRevision').is(':checked');
            var $revisionSelect = $('#mlr_revision');
            var $mlrIdInput = $('#mlr_id');
            
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
                        var currentMlrId = $mlrIdInput.val();
                        $.each(res.data, function(i, rev) {
                            var isSelected = (currentMlrId && parseInt(currentMlrId) === rev.MLR_ID) ? 'selected' : '';
                            $revisionSelect.append(
                                '<option value="' + rev.MLR_ID + '" ' + isSelected + '>Revision ' + rev.MLR_REV + 
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
                    $mlrIdInput.val(latestMlrId);
                }
            }
        });

        // Handle revision select change
        $('#mlr_revision').on('change', function() {
            $('#mlr_id').val($(this).val());
        });

        // Handle "Allow Select Old Revision" checkbox
        $('#allowOldRevision').on('change', function() {
            if ($(this).is(':checked')) {
                $('#tool_drawing_no').trigger('change');
            } else {
                $('#mlr_revision').hide().val('');
                // If unchecking, use latest revision
                var mlId = $('#tool_drawing_no').val();
                if (mlId) {
                    var selectedOption = $('#tool_drawing_no option:selected');
                    var latestMlrId = selectedOption.data('mlr-id') || '';
                    $('#mlr_id').val(latestMlrId);
                }
            }
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
                        toastr.success(res.message || 'Tool Inventory berhasil diupdate');
                    } else {
                        alert(res.message || 'Tool Inventory berhasil diupdate');
                    }
                    setTimeout(function() {
                        window.location.href = '<?= base_url("Tool_inventory/tool_inventory"); ?>';
                    }, 1000);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(res && res.message ? res.message : 'Gagal mengupdate Tool Inventory');
                    } else {
                        alert(res && res.message ? res.message : 'Gagal mengupdate Tool Inventory');
                    }
                }
            }).fail(function(xhr, status, error) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Gagal mengupdate: ' + (error || status));
                } else {
                    alert('Gagal mengupdate: ' + (error || status));
                }
            });
        });
    });
})(jQuery);
</script>
</body>
</html>
