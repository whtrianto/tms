<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        .section-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #007bff;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.25rem;
        }
        .form-group label.label-required::after {
            content: " *";
            color: red;
        }
        .form-control[readonly] {
            background-color: #e9ecef;
            cursor: not-allowed;
        }
        .info-display {
            padding: 0.5rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            min-height: 38px;
            display: flex;
            align-items: center;
        }
        .table td, .table th {
            color: #000 !important;
            padding: 0.35rem 0.4rem !important;
            font-size: 0.85rem;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 4px;
            flex-wrap: wrap;
        }
        .sub-section-title {
            font-size: 1rem;
            font-weight: bold;
            color: #495057;
            margin-top: 2rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .checkbox-hide {
            display: flex;
            align-items: center;
            gap: 0.5rem;
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
                        <h4 class="m-0 font-weight-bold text-primary">Toolset Details</h4>
                        <div>
                            <span class="text-muted">* = required information</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formToolSet" method="post" action="<?= base_url('Tool_management/tool_sets/submit_data'); ?>">
                            <input type="hidden" name="action" value="EDIT">
                            <input type="hidden" name="TSET_ID" value="<?= isset($tool_set['TSET_ID']) ? htmlspecialchars($tool_set['TSET_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            
                            <div class="row">
                                <!-- Kolom Kiri -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-required">Toolset Name</label>
                                        <input type="text" name="tset_name" class="form-control" 
                                               value="<?= isset($tool_set['TSET_NAME']) ? htmlspecialchars($tool_set['TSET_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>" 
                                               required>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool BOM No.</label>
                                        <div class="info-display">
                                            <?php 
                                            $bom_mlr_id = isset($tool_set['TSET_BOM_MLR_ID']) ? (int)$tool_set['TSET_BOM_MLR_ID'] : 0;
                                            $bom_no = isset($tool_set['TOOL_BOM']) ? htmlspecialchars($tool_set['TOOL_BOM'], ENT_QUOTES, 'UTF-8') : '';
                                            if ($bom_mlr_id > 0 && !empty($bom_no)): 
                                                $bom_detail_url = base_url('Tool_engineering/tool_bom_engin/detail_page/' . $bom_mlr_id);
                                            ?>
                                                <a href="<?= $bom_detail_url; ?>" class="text-primary" style="text-decoration: underline;" target="_blank">
                                                    <?= $bom_no; ?>
                                                </a>
                                            <?php else: ?>
                                                <?= $bom_no; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool BOM Description</label>
                                        <div class="info-display">
                                            <?= isset($tool_set['TOOL_BOM_DESC']) ? htmlspecialchars($tool_set['TOOL_BOM_DESC'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool BOM Revision</label>
                                        <div class="info-display">
                                            <?= isset($tool_set['REVISION']) ? htmlspecialchars($tool_set['REVISION'], ENT_QUOTES, 'UTF-8') : '0'; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product</label>
                                        <div class="info-display">
                                            <?= isset($tool_set['PRODUCT']) ? htmlspecialchars($tool_set['PRODUCT'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Process</label>
                                        <div class="info-display">
                                            <?= isset($tool_set['PROCESS']) ? htmlspecialchars($tool_set['PROCESS'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Toolsets Status</label>
                                        <div class="info-display">
                                            <?= $this->tool_sets->get_status_badge(isset($tool_set['TSET_STATUS']) ? $tool_set['TSET_STATUS'] : 0); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Save Changes
                                </button>
                                <a href="<?= base_url('Tool_management/tool_sets'); ?>" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Toolset Compositions List -->
                <div class="card mt-3">
                    <div class="card-header">
                        <div class="sub-section-title">
                            <span>Toolset Compositions List</span>
                            <div class="checkbox-hide">
                                <input type="checkbox" id="hideCompositions" class="form-check-input">
                                <label for="hideCompositions" class="form-check-label mb-0">Sembunyikan data</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body" id="compositionsSection">
                        <div class="table-responsive">
                            <table id="table-compositions" class="table table-bordered table-striped w-100 text-left">
                                <thead>
                                    <tr class="text-center">
                                        <th>No.</th>
                                        <th>ID</th>
                                        <th>Tool Drawing No.</th>
                                        <th>Revision</th>
                                        <th>Tool Name</th>
                                        <th>Std Req</th>
                                        <th>Tool ID</th>
                                        <th>Standard Tool Life</th>
                                        <th>End Cycle</th>
                                        <th>Remarks</th>
                                        <th>Tool Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($compositions)): ?>
                                        <?php $no = 1; foreach ($compositions as $comp): ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= isset($comp['TSCOMP_ID']) ? htmlspecialchars($comp['TSCOMP_ID'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td>
                                                    <?php 
                                                    $drawing_mlr_id = isset($comp['TSCOMP_MLR_ID']) ? (int)$comp['TSCOMP_MLR_ID'] : 0;
                                                    $drawing_no = isset($comp['TOOL_DRAWING_NO']) ? htmlspecialchars($comp['TOOL_DRAWING_NO'], ENT_QUOTES, 'UTF-8') : '';
                                                    if ($drawing_mlr_id > 0 && !empty($drawing_no)): 
                                                        $drawing_detail_url = base_url('Tool_engineering/tool_draw_tooling/detail_page/' . $drawing_mlr_id);
                                                    ?>
                                                        <a href="<?= $drawing_detail_url; ?>" class="text-primary" style="text-decoration: underline;" target="_blank">
                                                            <?= $drawing_no; ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <?= $drawing_no; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= isset($comp['REVISION']) ? htmlspecialchars($comp['REVISION'], ENT_QUOTES, 'UTF-8') : '0'; ?></td>
                                                <td><?= isset($comp['TOOL_NAME']) ? htmlspecialchars($comp['TOOL_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td><?= isset($comp['TSCOMP_STD_REQ']) ? htmlspecialchars($comp['TSCOMP_STD_REQ'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td>
                                                    <?php 
                                                    $tool_inv_id = isset($comp['TSCOMP_INV_ID']) ? (int)$comp['TSCOMP_INV_ID'] : 0;
                                                    $tool_id = isset($comp['TOOL_ID']) ? htmlspecialchars($comp['TOOL_ID'], ENT_QUOTES, 'UTF-8') : '';
                                                    if ($tool_inv_id > 0 && !empty($tool_id)): 
                                                        $inventory_detail_url = base_url('Tool_inventory/tool_inventory/detail_page/' . $tool_inv_id);
                                                    ?>
                                                        <a href="<?= $inventory_detail_url; ?>" class="text-primary" style="text-decoration: underline;" target="_blank">
                                                            <?= $tool_id; ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <?= $tool_id; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= isset($comp['STANDARD_TOOL_LIFE']) ? htmlspecialchars($comp['STANDARD_TOOL_LIFE'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td><?= isset($comp['END_CYCLE']) ? htmlspecialchars($comp['END_CYCLE'], ENT_QUOTES, 'UTF-8') : '0'; ?></td>
                                                <td><?= isset($comp['TSCOMP_REMARKS']) ? htmlspecialchars($comp['TSCOMP_REMARKS'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td>
                                                    <?php 
                                                    $tool_status = isset($comp['TOOL_STATUS']) ? (int)$comp['TOOL_STATUS'] : 0;
                                                    // Tool Inventory Status: 1=New, 2=Allocated, 3=Available, 4=InUsed, 5=Onhold, 6=Scrapped, 7=Repairing, 8=Modifying, 9=DesignChange
                                                    $status_map = array(1 => 'New', 2 => 'Allocated', 3 => 'Available', 4 => 'InUsed', 5 => 'Onhold', 6 => 'Scrapped', 7 => 'Repairing', 8 => 'Modifying', 9 => 'DesignChange');
                                                    $status_name = isset($status_map[$tool_status]) ? $status_map[$tool_status] : 'Unknown';
                                                    $badge_class = in_array($tool_status, [3]) ? 'badge-success' : (in_array($tool_status, [4, 2]) ? 'badge-warning' : (in_array($tool_status, [5, 6]) ? 'badge-danger' : 'badge-secondary'));
                                                    echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8') . '</span>';
                                                    ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button class="btn btn-secondary btn-sm btn-edit-comp" data-id="<?= isset($comp['TSCOMP_ID']) ? $comp['TSCOMP_ID'] : ''; ?>">Edit</button>
                                                        <button class="btn btn-warning btn-sm btn-replace-comp" data-id="<?= isset($comp['TSCOMP_ID']) ? $comp['TSCOMP_ID'] : ''; ?>">Replace</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="12" class="text-center">No compositions found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Usage Assignments List -->
                <div class="card mt-3">
                    <div class="card-header">
                        <div class="sub-section-title">
                            <span>Usage Assignments List</span>
                            <a href="<?= base_url('Tool_management/tool_sets/add_assignment/' . (isset($tool_set['TSET_ID']) ? $tool_set['TSET_ID'] : 0)); ?>" class="btn btn-sm btn-primary">
                                <i class="fa fa-plus"></i> Add New
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-assignments" class="table table-bordered table-striped w-100 text-left">
                                <thead>
                                    <tr class="text-center">
                                        <th>No.</th>
                                        <th>Operation Name</th>
                                        <th>Machine Name</th>
                                        <th>Product Name</th>
                                        <th>Production Start</th>
                                        <th>Production End</th>
                                        <th>Usage</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($assignments)): ?>
                                        <?php $no = 1; foreach ($assignments as $assign): ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= isset($assign['OPERATION_NAME']) ? htmlspecialchars($assign['OPERATION_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td><?= isset($assign['MACHINE_NAME']) ? htmlspecialchars($assign['MACHINE_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td><?= isset($assign['PRODUCT_NAME']) ? htmlspecialchars($assign['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td><?= isset($assign['PRODUCTION_START']) ? htmlspecialchars($assign['PRODUCTION_START'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td><?= isset($assign['PRODUCTION_END']) ? htmlspecialchars($assign['PRODUCTION_END'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td><?= isset($assign['USAGE']) ? htmlspecialchars($assign['USAGE'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td><?= isset($assign['REMARKS']) ? htmlspecialchars($assign['REMARKS'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="<?= base_url('Tool_management/tool_sets/edit_assignment/' . (isset($assign['TASGN_ID']) ? $assign['TASGN_ID'] : 0)); ?>" class="btn btn-secondary btn-sm">Edit</a>
                                                        <button class="btn btn-danger btn-sm btn-delete-assignment" data-id="<?= isset($assign['TASGN_ID']) ? $assign['TASGN_ID'] : ''; ?>">Delete</button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center">No assignments found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
<script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js'); ?>"></script>
<script>
(function($){
    $(function(){
        // Initialize DataTables
        if ($('#table-compositions tbody tr').length > 0) {
            $('#table-compositions').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                autoWidth: false,
                scrollX: true
            });
        }

        if ($('#table-assignments tbody tr').length > 0) {
            $('#table-assignments').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                autoWidth: false,
                scrollX: true
            });
        }

        // Hide/Show Compositions
        $('#hideCompositions').on('change', function() {
            if ($(this).is(':checked')) {
                $('#compositionsSection').slideUp();
            } else {
                $('#compositionsSection').slideDown();
            }
        });

        // Delete Assignment handler
        $('.btn-delete-assignment').on('click', function() {
            var id = $(this).data('id');
            if (!confirm('Hapus assignment ini?')) return;
            
            // TODO: Implement delete assignment
            alert('Delete assignment functionality will be implemented');
        });

        // Edit/Replace Composition handlers
        $('.btn-edit-comp, .btn-replace-comp').on('click', function() {
            var id = $(this).data('id');
            var action = $(this).hasClass('btn-edit-comp') ? 'Edit' : 'Replace';
            // TODO: Implement edit/replace composition
            alert(action + ' composition functionality will be implemented');
        });

        // Form submit
        $('#formToolSet').on('submit', function(e) {
            e.preventDefault();
            // TODO: Implement form submission
            alert('Save functionality will be implemented');
        });
    });
})(jQuery);
</script>
</body>
</html>

