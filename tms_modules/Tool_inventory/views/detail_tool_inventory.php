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
        .navbar { position: sticky; top: 0; z-index: 1030; }
        #content-wrapper { min-height: calc(100vh - 56px); }
        #container-wrapper { 
            padding-bottom: 6rem !important; 
            margin-bottom: 4rem !important; 
        }
        .card { 
            margin-bottom: 2rem; 
        }
        .card-body {
            padding-bottom: 3rem !important;
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
            pointer-events: none;
        }
        .form-control:disabled, .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
            cursor: not-allowed;
        }
        select.form-control:disabled {
            background-color: #e9ecef;
            opacity: 1;
            cursor: not-allowed;
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
                            <h4 class="m-0 font-weight-bold text-primary">Tool Inventory Detail</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($inventory['INV_ID']); ?></div>
                        </div>
                        <a href="<?= base_url('Tool_inventory/tool_inventory'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Tool Drawing Section - Full Width -->
                        <div class="section-title">Tool Drawing Information</div>
                        
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="form-group form-check-inline-custom">
                                    <label class="form-check-label" for="allowOldRevision">Allow Select Old Revision</label>
                                    <input type="checkbox" class="form-check-input" id="allowOldRevision" disabled <?= isset($inventory['INV_MLR_ID']) && $inventory['INV_MLR_ID'] ? 'checked' : ''; ?>>
                                </div>

                                <div class="form-group">
                                    <label>Tool Drawing No.</label>
                                    <select class="form-control" disabled>
                                        <option value="">-- Select Tool Drawing No. --</option>
                                        <?php foreach ($tool_drawing_nos as $tdn): ?>
                                            <option value="<?= (int)$tdn['ML_ID']; ?>" <?= (isset($inventory['TOOL_DRAWING_ML_ID']) && (int)$inventory['TOOL_DRAWING_ML_ID'] === (int)$tdn['ML_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($tdn['ML_TOOL_DRAW_NO'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Tool ID</label>
                                    <input type="text" class="form-control" value="<?= isset($inventory['INV_TOOL_ID']) ? htmlspecialchars($inventory['INV_TOOL_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Product</label>
                                    <select class="form-control" disabled>
                                        <option value="">-- Select Product --</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= (int)$p['PRODUCT_ID']; ?>" <?= (isset($inventory['PRODUCT_ID']) && (int)$inventory['PRODUCT_ID'] === (int)$p['PRODUCT_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Process</label>
                                    <select class="form-control" disabled>
                                        <option value="">-- Select Process --</option>
                                        <?php foreach ($operations as $o): ?>
                                            <option value="<?= (int)$o['OPERATION_ID']; ?>" <?= (isset($inventory['PROCESS_ID']) && (int)$inventory['PROCESS_ID'] === (int)$o['OPERATION_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($o['OPERATION_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Tool Name</label>
                                    <select class="form-control" disabled>
                                        <option value="">-- Select Tool Name --</option>
                                        <?php foreach ($tools as $t): ?>
                                            <option value="<?= (int)$t['TOOL_ID']; ?>" <?= (isset($inventory['TOOL_NAME_ID']) && (int)$inventory['TOOL_NAME_ID'] === (int)$t['TOOL_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($t['TOOL_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Revision</label>
                                    <input type="number" class="form-control" value="<?= isset($inventory['REVISION']) ? htmlspecialchars($inventory['REVISION'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Tool Tag</label>
                                    <input type="text" class="form-control" value="<?= isset($inventory['INV_TOOL_TAG']) ? htmlspecialchars($inventory['INV_TOOL_TAG'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Tool Status</label>
                                    <select class="form-control" disabled>
                                        <option value="1" <?= (isset($inventory['INV_STATUS']) && (int)$inventory['INV_STATUS'] === 1) ? 'selected' : ''; ?>>New</option>
                                        <option value="2" <?= (isset($inventory['INV_STATUS']) && (int)$inventory['INV_STATUS'] === 2) ? 'selected' : ''; ?>>Allocated</option>
                                        <option value="3" <?= (isset($inventory['INV_STATUS']) && (int)$inventory['INV_STATUS'] === 3) ? 'selected' : ''; ?>>Available</option>
                                        <option value="4" <?= (isset($inventory['INV_STATUS']) && (int)$inventory['INV_STATUS'] === 4) ? 'selected' : ''; ?>>InUsed</option>
                                        <option value="5" <?= (isset($inventory['INV_STATUS']) && (int)$inventory['INV_STATUS'] === 5) ? 'selected' : ''; ?>>Onhold</option>
                                        <option value="6" <?= (isset($inventory['INV_STATUS']) && (int)$inventory['INV_STATUS'] === 6) ? 'selected' : ''; ?>>Scrapped</option>
                                        <option value="7" <?= (isset($inventory['INV_STATUS']) && (int)$inventory['INV_STATUS'] === 7) ? 'selected' : ''; ?>>Repairing</option>
                                        <option value="8" <?= (isset($inventory['INV_STATUS']) && (int)$inventory['INV_STATUS'] === 8) ? 'selected' : ''; ?>>Modifying</option>
                                        <option value="9" <?= (isset($inventory['INV_STATUS']) && (int)$inventory['INV_STATUS'] === 9) ? 'selected' : ''; ?>>DesignChange</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Tool Condition</label>
                                    <input type="number" class="form-control" value="<?= isset($inventory['INV_TOOL_CONDITION']) ? htmlspecialchars($inventory['INV_TOOL_CONDITION'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="form-group form-check-inline-custom">
                                    <label class="form-check-label" for="assetized">Assetized</label>
                                    <input type="checkbox" class="form-check-input" id="assetized" disabled <?= (isset($inventory['INV_ASSETIZED']) && $inventory['INV_ASSETIZED'] == 1) ? 'checked' : ''; ?>>
                                </div>

                                <div class="form-group">
                                    <label>In Tool Set</label>
                                    <input type="number" class="form-control" value="<?= isset($inventory['INV_IN_TOOL_SET']) ? htmlspecialchars($inventory['INV_IN_TOOL_SET'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Storage Location</label>
                                    <select class="form-control" disabled>
                                        <option value="">-- Select Storage Location --</option>
                                        <?php foreach ($storage_locations as $sl): ?>
                                            <option value="<?= (int)$sl['STORAGE_LOCATION_ID']; ?>" <?= (isset($inventory['STORAGE_LOCATION_ID']) && (int)$inventory['STORAGE_LOCATION_ID'] === (int)$sl['STORAGE_LOCATION_ID']) ? 'selected' : ''; ?>>
                                                <?= htmlspecialchars($sl['STORAGE_LOCATION_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea class="form-control" rows="3" readonly><?= isset($inventory['NOTES']) ? htmlspecialchars($inventory['NOTES'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label>Begin Cycle</label>
                                    <input type="number" class="form-control" value="<?= isset($inventory['INV_BEGIN_CYCLE']) ? htmlspecialchars($inventory['INV_BEGIN_CYCLE'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>End Cycle</label>
                                    <input type="number" class="form-control" value="<?= isset($inventory['END_CYCLE']) ? htmlspecialchars($inventory['END_CYCLE'], ENT_QUOTES, 'UTF-8') : '0'; ?>" readonly>
                                    <small class="form-text text-muted">Automatically calculated</small>
                                </div>

                                <div class="form-group">
                                    <label>Received Date</label>
                                    <input type="date" class="form-control" value="<?= isset($inventory['RECEIVED_DATE']) && !empty($inventory['RECEIVED_DATE']) ? date('Y-m-d', strtotime($inventory['RECEIVED_DATE'])) : ''; ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label>Do No.</label>
                                    <input type="text" class="form-control" value="<?= isset($inventory['DO_NO']) ? htmlspecialchars($inventory['DO_NO'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
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
                                            <select class="form-control" disabled>
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
                                            <select class="form-control" disabled>
                                                <option value="">-- Select Maker --</option>
                                                <?php foreach ($makers as $mk): ?>
                                                    <option value="<?= (int)$mk['MAKER_ID']; ?>" <?= (isset($inventory['INV_MAKER_ID']) && (int)$inventory['INV_MAKER_ID'] === (int)$mk['MAKER_ID']) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($mk['MAKER_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Maker Code</label>
                                            <input type="text" class="form-control" value="<?= isset($inventory['MAKER_CODE']) ? htmlspecialchars($inventory['MAKER_CODE'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Material</label>
                                            <select class="form-control" disabled>
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
                                            <select class="form-control" disabled>
                                                <option value="">-- Select Purchase Type --</option>
                                                <option value="Local" <?= (isset($inventory['INV_PURCHASE_TYPE']) && $inventory['INV_PURCHASE_TYPE'] === 'Local') ? 'selected' : ''; ?>>Local</option>
                                                <option value="Overseas" <?= (isset($inventory['INV_PURCHASE_TYPE']) && $inventory['INV_PURCHASE_TYPE'] === 'Overseas') ? 'selected' : ''; ?>>Overseas</option>
                                                <option value="Internal Fabrication" <?= (isset($inventory['INV_PURCHASE_TYPE']) && $inventory['INV_PURCHASE_TYPE'] === 'Internal Fabrication') ? 'selected' : ''; ?>>Internal Fabrication</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?= isset($modal_logout) ? $modal_logout : ''; ?>
        </div>
        <?= isset($footer) ? $footer : ''; ?>
    </div>
</div>

<?= isset($foot) ? $foot : ''; ?>
</body>
</html>

