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
            padding-top: 1rem;
            padding-bottom: 0.5rem;
            border-top: 2px solid #dee2e6;
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
                        <h4 class="m-0 font-weight-bold text-primary">Add Tool Scrap</h4>
                        <a href="<?= base_url('Tool_inventory/tool_scrap'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="formToolScrap" method="post" action="<?= base_url('Tool_inventory/tool_scrap/submit_data'); ?>">
                            <input type="hidden" name="action" value="ADD">
                            
                            <!-- Tool Scrap Details Section -->
                            <div class="section-title">Tool Scrap Details</div>
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-required">Issue Date</label>
                                        <input type="date" name="issue_date" id="issue_date" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Requested By</label>
                                        <div class="input-group">
                                            <input type="text" name="requested_by_display" id="requested_by_display" class="form-control" placeholder="Click to select User" readonly required>
                                            <input type="hidden" name="requested_by" id="requested_by" value="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btn-select-requested-by" data-toggle="modal" data-target="#modalRequestedBy">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool Scrap No.</label>
                                        <input type="text" name="scrap_no" id="scrap_no" class="form-control" value="<?= isset($next_scrap_no) ? htmlspecialchars($next_scrap_no, ENT_QUOTES, 'UTF-8') : ''; ?>" readonly>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-required">Accident/ Scrap Date</label>
                                        <input type="date" name="acc_scrap_date" id="acc_scrap_date" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Machine</label>
                                        <div class="input-group">
                                            <input type="text" name="machine_display" id="machine_display" class="form-control" placeholder="Click to select Machine" readonly required>
                                            <input type="hidden" name="machine_id" id="machine_id" value="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btn-select-machine" data-toggle="modal" data-target="#modalMachine">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Operator</label>
                                        <div class="input-group">
                                            <input type="text" name="operator_display" id="operator_display" class="form-control" placeholder="Click to select User" readonly required>
                                            <input type="hidden" name="operator" id="operator" value="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btn-select-operator" data-toggle="modal" data-target="#modalOperator">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Status</label>
                                        <input type="text" class="form-control" value="Pending" readonly>
                                        <input type="hidden" name="status" value="0">
                                    </div>
                                </div>
                            </div>

                            <!-- Tool Information Section -->
                            <div class="section-title">Tool Information</div>
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-required">Tool ID</label>
                                        <div class="input-group">
                                            <input type="text" name="tool_id_display" id="tool_id_display" class="form-control" placeholder="Click to select Tool ID" readonly required>
                                            <input type="hidden" name="tool_id" id="tool_id" value="">
                                            <input type="hidden" name="inv_id" id="inv_id" value="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btn-select-tool-id" data-toggle="modal" data-target="#modalToolInventory">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool Name</label>
                                        <input type="text" name="tool_name" id="tool_name" class="form-control" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>Material</label>
                                        <input type="text" name="material" id="material" class="form-control" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>RQ No.</label>
                                        <input type="text" name="rq_no" id="rq_no" class="form-control" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool Price</label>
                                        <input type="number" name="tool_price" id="tool_price" class="form-control" step="0.01" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool Residue Value</label>
                                        <input type="number" name="tool_residue_value" id="tool_residue_value" class="form-control" step="0.01" readonly>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Drawing No.</label>
                                        <input type="text" name="drawing_no" id="drawing_no" class="form-control" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>Revision</label>
                                        <input type="number" name="revision" id="revision" class="form-control" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool Assignment No</label>
                                        <input type="text" name="tool_assignment_no" id="tool_assignment_no" class="form-control" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>Pcs Produced</label>
                                        <input type="number" name="pcs_produced" id="pcs_produced" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Tool Inventory Status Section -->
                            <div class="section-title">Tool Inventory Status</div>
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>This Tool Drawing</label>
                                    </div>
                                    <div class="form-group">
                                        <label>Std. Qty</label>
                                        <input type="number" name="std_qty_this" id="std_qty_this" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Not Received Qty</label>
                                        <input type="number" name="not_received_qty_this" id="not_received_qty_this" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Curr. Qty</label>
                                        <input type="number" name="curr_qty_this" id="curr_qty_this" class="form-control" readonly>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>This + Interchangeable Tool Drawing</label>
                                    </div>
                                    <div class="form-group">
                                        <label>Std. Qty</label>
                                        <input type="number" name="std_qty_all" id="std_qty_all" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Not Received Qty</label>
                                        <input type="number" name="not_received_qty_all" id="not_received_qty_all" class="form-control" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label>Curr. Qty</label>
                                        <input type="number" name="curr_qty_all" id="curr_qty_all" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- Investigation Section -->
                            <div class="section-title">Investigation</div>
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-required">Reason</label>
                                        <div class="input-group">
                                            <input type="text" name="reason_display" id="reason_display" class="form-control" placeholder="Click to select Reason" readonly required>
                                            <input type="hidden" name="reason_id" id="reason_id" value="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btn-select-reason" data-toggle="modal" data-target="#modalReason">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Counter Measure</label>
                                        <textarea name="counter_measure" id="counter_measure" class="form-control" rows="3" placeholder="Enter Counter Measure" required></textarea>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-required">Cause</label>
                                        <div class="input-group">
                                            <input type="text" name="cause_display" id="cause_display" class="form-control" placeholder="Click to select Cause" readonly required>
                                            <input type="hidden" name="cause_id" id="cause_id" value="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btn-select-cause" data-toggle="modal" data-target="#modalCause">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Cause Remark</label>
                                        <textarea name="cause_remark" id="cause_remark" class="form-control" rows="3" placeholder="Enter Cause Remark" required></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Investigated By</label>
                                        <div class="input-group">
                                            <input type="text" name="investigated_by_display" id="investigated_by_display" class="form-control" placeholder="Click to select User" readonly>
                                            <input type="hidden" name="investigated_by" id="investigated_by" value="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btn-select-investigated-by" data-toggle="modal" data-target="#modalInvestigatedBy">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Decision Section -->
                            <div class="section-title">Decision</div>
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-required">Suggestion</label>
                                        <select name="suggestion" id="suggestion" class="form-control" required>
                                            <option value="">-- Select Suggestion --</option>
                                            <option value="Scrap">Scrap</option>
                                            <option value="Can Use">Can Use</option>
                                            <option value="On Hold">On Hold</option>
                                            <option value="Repair">Repair</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">To Order</label>
                                        <select name="to_order" id="to_order" class="form-control" required>
                                            <option value="">-- Select --</option>
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Approve By</label>
                                        <div class="input-group">
                                            <input type="text" name="approve_by_display" id="approve_by_display" class="form-control" placeholder="Click to select User" readonly>
                                            <input type="hidden" name="approve_by" id="approve_by" value="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btn-select-approve-by" data-toggle="modal" data-target="#modalApproveBy">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Approve Date</label>
                                        <input type="date" name="approve_date" id="approve_date" class="form-control" readonly>
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

<!-- Modal for Requested By Selection -->
<div class="modal fade" id="modalRequestedBy" tabindex="-1" role="dialog" aria-labelledby="modalRequestedByLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRequestedByLabel">Select Requested By</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-requested-by-modal" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Position</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($users_modal) && is_array($users_modal)): ?>
                                <?php foreach ($users_modal as $u): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($u['USER'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($u['POSITION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-select-user" 
                                                    data-id="<?= htmlspecialchars($u['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-user="<?= htmlspecialchars($u['USER'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-field="requested_by">
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

<!-- Modal for Operator Selection -->
<div class="modal fade" id="modalOperator" tabindex="-1" role="dialog" aria-labelledby="modalOperatorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalOperatorLabel">Select Operator</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-operator-modal" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Position</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($users_modal) && is_array($users_modal)): ?>
                                <?php foreach ($users_modal as $u): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($u['USER'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($u['POSITION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-select-user" 
                                                    data-id="<?= htmlspecialchars($u['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-user="<?= htmlspecialchars($u['USER'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-field="operator">
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

<!-- Modal for Machine Selection -->
<div class="modal fade" id="modalMachine" tabindex="-1" role="dialog" aria-labelledby="modalMachineLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMachineLabel">Select Machine</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-machine-modal" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Machine</th>
                                <th>Operation</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($machines_modal) && is_array($machines_modal)): ?>
                                <?php foreach ($machines_modal as $m): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($m['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($m['MACHINE'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($m['OPERATION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-select-machine" 
                                                    data-id="<?= htmlspecialchars($m['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-name="<?= htmlspecialchars($m['MACHINE'], ENT_QUOTES, 'UTF-8'); ?>">
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

<!-- Modal for Tool Inventory Selection -->
<div class="modal fade" id="modalToolInventory" tabindex="-1" role="dialog" aria-labelledby="modalToolInventoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalToolInventoryLabel">Select Tool ID</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-tool-inventory-modal" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tool ID</th>
                                <th>Tool Drawing No</th>
                                <th>Revision</th>
                                <th>Tool Name</th>
                                <th>Tool Status</th>
                                <th>Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($tool_inventory_modal) && is_array($tool_inventory_modal)): ?>
                                <?php foreach ($tool_inventory_modal as $ti): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($ti['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <a href="#" class="btn-select-tool-id-link text-primary" 
                                               data-inv-id="<?= htmlspecialchars($ti['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                               data-tool-id="<?= htmlspecialchars($ti['TOOL_ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                               style="text-decoration: underline; cursor: pointer;">
                                                <?= htmlspecialchars($ti['TOOL_ID'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($ti['TOOL_DRAWING_NO'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($ti['REVISION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($ti['TOOL_NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($ti['TOOL_STATUS'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($ti['REMARKS'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-select-tool-id" 
                                                    data-inv-id="<?= htmlspecialchars($ti['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-tool-id="<?= htmlspecialchars($ti['TOOL_ID'], ENT_QUOTES, 'UTF-8'); ?>">
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

<!-- Modal for Reason Selection -->
<div class="modal fade" id="modalReason" tabindex="-1" role="dialog" aria-labelledby="modalReasonLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReasonLabel">Select Reason</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-reason-modal" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($reasons_modal) && is_array($reasons_modal)): ?>
                                <?php foreach ($reasons_modal as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($r['NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($r['DESCRIPTION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-select-reason" 
                                                    data-id="<?= htmlspecialchars($r['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-name="<?= htmlspecialchars($r['NAME'], ENT_QUOTES, 'UTF-8'); ?>">
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

<!-- Modal for Cause Selection -->
<div class="modal fade" id="modalCause" tabindex="-1" role="dialog" aria-labelledby="modalCauseLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCauseLabel">Select Cause</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-cause-modal" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($causes_modal) && is_array($causes_modal)): ?>
                                <?php foreach ($causes_modal as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($c['NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($c['DESCRIPTION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-select-cause" 
                                                    data-id="<?= htmlspecialchars($c['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-name="<?= htmlspecialchars($c['NAME'], ENT_QUOTES, 'UTF-8'); ?>">
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

<!-- Modal for Investigated By Selection -->
<div class="modal fade" id="modalInvestigatedBy" tabindex="-1" role="dialog" aria-labelledby="modalInvestigatedByLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInvestigatedByLabel">Select Investigated By</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-investigated-by-modal" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Position</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($users_modal) && is_array($users_modal)): ?>
                                <?php foreach ($users_modal as $u): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($u['USER'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($u['POSITION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-select-user" 
                                                    data-id="<?= htmlspecialchars($u['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-user="<?= htmlspecialchars($u['USER'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-field="investigated_by">
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

<!-- Modal for Approve By Selection -->
<div class="modal fade" id="modalApproveBy" tabindex="-1" role="dialog" aria-labelledby="modalApproveByLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalApproveByLabel">Select Approve By</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-approve-by-modal" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Position</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($users_modal) && is_array($users_modal)): ?>
                                <?php foreach ($users_modal as $u): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($u['USER'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($u['POSITION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-select-user" 
                                                    data-id="<?= htmlspecialchars($u['ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-user="<?= htmlspecialchars($u['USER'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-field="approve_by">
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
        // Initialize DataTables for all modals
        var tableRequestedBy = $('#table-requested-by-modal').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [3] }]
        });

        var tableOperator = $('#table-operator-modal').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [3] }]
        });

        var tableMachine = $('#table-machine-modal').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[0, 'asc']], // Order by ID (column index 0) from small to large
            columnDefs: [{ orderable: false, targets: [3] }] // Action column is not sortable
        });

        var tableToolInventory = $('#table-tool-inventory-modal').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[1, 'asc']], // Order by Tool ID (column index 1)
            columnDefs: [{ orderable: false, targets: [7] }] // Action column is not sortable
        });

        var tableReason = $('#table-reason-modal').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [3] }]
        });

        var tableCause = $('#table-cause-modal').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [3] }]
        });

        var tableInvestigatedBy = $('#table-investigated-by-modal').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [3] }]
        });

        var tableApproveBy = $('#table-approve-by-modal').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [3] }]
        });

        // Handle User selection (Requested By, Operator, Investigated By, Approve By)
        $(document).on('click', '.btn-select-user', function() {
            var userId = $(this).data('id');
            var userName = $(this).data('user');
            var field = $(this).data('field');
            
            $('#' + field).val(userId);
            $('#' + field + '_display').val(userName);
            
            $('#modal' + field.charAt(0).toUpperCase() + field.slice(1).replace('_', '')).modal('hide');
        });

        // Handle Machine selection
        $(document).on('click', '.btn-select-machine', function() {
            var machineId = $(this).data('id');
            var machineName = $(this).data('name');
            
            $('#machine_id').val(machineId);
            $('#machine_display').val(machineName);
            
            $('#modalMachine').modal('hide');
        });

        // Handle Tool ID selection
        $(document).on('click', '.btn-select-tool-id, .btn-select-tool-id-link', function(e) {
            e.preventDefault();
            var invId = $(this).data('inv-id');
            var toolId = $(this).data('tool-id');
            
            if (!toolId || toolId === '') {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('Tool ID tidak valid');
                }
                return;
            }
            
            // Set values
            $('#inv_id').val(invId || '');
            $('#tool_id').val(toolId);
            $('#tool_id_display').val(toolId);
            
            // Close modal
            $('#modalToolInventory').modal('hide');
            
            // Load tool inventory details to auto-fill all fields
            loadToolInventoryDetails(toolId);
        });

        // Handle Reason selection
        $(document).on('click', '.btn-select-reason', function() {
            var reasonId = $(this).data('id');
            var reasonName = $(this).data('name');
            
            $('#reason_id').val(reasonId);
            $('#reason_display').val(reasonName);
            
            $('#modalReason').modal('hide');
        });

        // Handle Cause selection
        $(document).on('click', '.btn-select-cause', function() {
            var causeId = $(this).data('id');
            var causeName = $(this).data('name');
            
            $('#cause_id').val(causeId);
            $('#cause_display').val(causeName);
            
            $('#modalCause').modal('hide');
        });

        // Function to load Tool Inventory details
        function loadToolInventoryDetails(toolId) {
            if (!toolId || toolId === '') {
                clearToolFields();
                return;
            }

            // Show loading indicator
            var $toolIdDisplay = $('#tool_id_display');
            var originalVal = $toolIdDisplay.val();
            $toolIdDisplay.val('Loading...');

            $.ajax({
                url: '<?= base_url("Tool_inventory/tool_scrap/get_tool_inventory_details"); ?>',
                type: 'POST',
                dataType: 'json',
                data: { 
                    tool_id: toolId 
                },
                beforeSend: function() {
                    console.log('Loading Tool ID:', toolId);
                }
            }).done(function(res) {
                console.log('Response:', res);
                $toolIdDisplay.val(originalVal); // Restore original value
                
                if (res && res.success && res.data) {
                    var d = res.data;
                    
                    // Auto-fill all Tool Information fields
                    $('#tool_name').val(d.TOOL_NAME || '');
                    $('#material').val(d.MATERIAL || '');
                    $('#rq_no').val(d.RQ_NO || '');
                    $('#tool_price').val(d.TOOL_PRICE || '0');
                    $('#drawing_no').val(d.TOOL_DRAWING_NO || '');
                    $('#revision').val(d.REVISION || '0');
                    $('#tool_assignment_no').val(d.TOOL_ASSIGNMENT_NO || '');
                    $('#pcs_produced').val(d.PCS_PRODUCED || '0');
                    
                    // Tool Residue Value - calculate if needed (can be 0 or calculated from tool price)
                    // For now, set to 0 or leave empty
                    $('#tool_residue_value').val('0');
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Tool Information loaded successfully');
                    }
                } else {
                    clearToolFields();
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(res && res.message ? res.message : 'Tool ID tidak ditemukan');
                    } else {
                        alert(res && res.message ? res.message : 'Tool ID tidak ditemukan');
                    }
                }
            }).fail(function(xhr, status, error) {
                $toolIdDisplay.val(originalVal); // Restore original value
                clearToolFields();
                if (typeof toastr !== 'undefined') {
                    toastr.error('Gagal memuat data Tool Information: ' + (error || status));
                } else {
                    alert('Gagal memuat data Tool Information: ' + (error || status));
                }
            });
        }

        // Function to clear tool fields
        function clearToolFields() {
            $('#tool_name').val('');
            $('#material').val('');
            $('#rq_no').val('');
            $('#tool_price').val('0');
            $('#drawing_no').val('');
            $('#revision').val('0');
            $('#tool_assignment_no').val('');
            $('#pcs_produced').val('0');
            $('#tool_residue_value').val('0');
        }

        // Form submit with AJAX
        $('#formToolScrap').on('submit', function(e) {
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
                        toastr.success(res.message || 'Tool Scrap berhasil ditambahkan');
                    } else {
                        alert(res.message || 'Tool Scrap berhasil ditambahkan');
                    }
                    setTimeout(function() {
                        window.location.href = '<?= base_url("Tool_inventory/tool_scrap"); ?>';
                    }, 1000);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(res && res.message ? res.message : 'Gagal menyimpan Tool Scrap');
                    } else {
                        alert(res && res.message ? res.message : 'Gagal menyimpan Tool Scrap');
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

