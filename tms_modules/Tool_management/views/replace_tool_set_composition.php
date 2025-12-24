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
        .existing-tool-section, .replace-tool-section {
            margin-bottom: 2rem;
        }
        .tool-id-input-group {
            display: flex;
            gap: 0.5rem;
        }
        .tool-id-input-group input {
            flex: 1;
        }
        .clickable-row {
            cursor: pointer;
        }
        .clickable-row:hover {
            background-color: #f8f9fa;
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
                        <h4 class="m-0 font-weight-bold text-primary">Replace Toolset Composition</h4>
                        <div>
                            <span class="text-muted">* = required information</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formReplaceComposition" method="post" action="<?= base_url('Tool_management/tool_sets/submit_replace_composition_data'); ?>">
                            <input type="hidden" name="TSCOMP_ID" value="<?= isset($composition['TSCOMP_ID']) ? htmlspecialchars($composition['TSCOMP_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <input type="hidden" name="NEW_INV_ID" id="NEW_INV_ID" value="">
                            
                            <!-- Existing Tool Section -->
                            <div class="existing-tool-section">
                                <div class="section-title">Existing Tool</div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tool Drawing No.</label>
                                            <div class="info-display">
                                                <?php 
                                                $drawing_mlr_id = isset($composition['TSCOMP_MLR_ID']) ? (int)$composition['TSCOMP_MLR_ID'] : 0;
                                                $drawing_no = isset($composition['TOOL_DRAWING_NO']) ? htmlspecialchars($composition['TOOL_DRAWING_NO'], ENT_QUOTES, 'UTF-8') : '';
                                                if ($drawing_mlr_id > 0 && !empty($drawing_no)): 
                                                    $drawing_detail_url = base_url('Tool_engineering/tool_draw_tooling/detail_page/' . $drawing_mlr_id);
                                                ?>
                                                    <a href="<?= $drawing_detail_url; ?>" class="text-primary" style="text-decoration: underline;" target="_blank">
                                                        <?= $drawing_no; ?>
                                                    </a>
                                                <?php else: ?>
                                                    <?= $drawing_no; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Revision</label>
                                            <div class="info-display">
                                                <?= isset($composition['REVISION']) ? htmlspecialchars($composition['REVISION'], ENT_QUOTES, 'UTF-8') : '0'; ?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Std Req.</label>
                                            <div class="info-display">
                                                <?= isset($composition['TSCOMP_STD_REQ']) ? htmlspecialchars($composition['TSCOMP_STD_REQ'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Standard Tool Life</label>
                                            <div class="info-display">
                                                <?= isset($composition['STANDARD_TOOL_LIFE']) ? htmlspecialchars($composition['STANDARD_TOOL_LIFE'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Remarks</label>
                                            <div class="info-display">
                                                <?= isset($composition['TSCOMP_REMARKS']) ? htmlspecialchars($composition['TSCOMP_REMARKS'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tool ID</label>
                                            <div class="info-display">
                                                <?php 
                                                $tool_inv_id = isset($composition['TSCOMP_INV_ID']) ? (int)$composition['TSCOMP_INV_ID'] : 0;
                                                $tool_id = isset($composition['TOOL_ID']) ? htmlspecialchars($composition['TOOL_ID'], ENT_QUOTES, 'UTF-8') : '';
                                                if ($tool_inv_id > 0 && !empty($tool_id)): 
                                                    $inventory_detail_url = base_url('Tool_inventory/tool_inventory/detail_page/' . $tool_inv_id);
                                                ?>
                                                    <a href="<?= $inventory_detail_url; ?>" class="text-primary" style="text-decoration: underline;" target="_blank">
                                                        <?= $tool_id; ?>
                                                    </a>
                                                <?php else: ?>
                                                    <?= $tool_id; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Tool Name</label>
                                            <div class="info-display">
                                                <?= isset($composition['TOOL_NAME']) ? htmlspecialchars($composition['TOOL_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Tool Status</label>
                                            <div class="info-display">
                                                <?php 
                                                $tool_status = isset($composition['TOOL_STATUS']) ? (int)$composition['TOOL_STATUS'] : 0;
                                                $status_map = array(1 => 'New', 2 => 'Allocated', 3 => 'Available', 4 => 'InUsed', 5 => 'Onhold', 6 => 'Scrapped', 7 => 'Repairing', 8 => 'Modifying', 9 => 'DesignChange');
                                                $status_name = isset($status_map[$tool_status]) ? $status_map[$tool_status] : 'Unknown';
                                                $badge_class = in_array($tool_status, [3]) ? 'badge-success' : (in_array($tool_status, [4, 2]) ? 'badge-warning' : (in_array($tool_status, [5, 6]) ? 'badge-danger' : 'badge-secondary'));
                                                echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8') . '</span>';
                                                ?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>End Cycle</label>
                                            <div class="info-display">
                                                <?= isset($composition['END_CYCLE']) ? htmlspecialchars($composition['END_CYCLE'], ENT_QUOTES, 'UTF-8') : '0'; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Replace Tool Section -->
                            <div class="replace-tool-section">
                                <div class="section-title">Replace Tool</div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="label-required">Tool ID</label>
                                            <div class="tool-id-input-group">
                                                <input type="text" id="selected_tool_id" class="form-control" readonly placeholder="Click button to select tool">
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalSelectTool">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Remarks</label>
                                            <textarea name="TSCOMP_REMARKS" class="form-control" rows="3" placeholder="Enter remarks for replacement"><?= isset($composition['TSCOMP_REMARKS']) ? htmlspecialchars($composition['TSCOMP_REMARKS'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Tool Status</label>
                                            <div class="info-display" id="replace_tool_status">
                                                -
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>End Cycle</label>
                                            <div class="info-display" id="replace_end_cycle">
                                                -
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary" id="btnSubmit" disabled>
                                    <i class="fa fa-save"></i> Submit
                                </button>
                                <a href="<?= base_url('Tool_management/tool_sets/edit_page/' . (isset($composition['TSCOMP_TSET_ID']) ? $composition['TSCOMP_TSET_ID'] : 0)); ?>" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
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

<!-- Modal Select Tool -->
<div class="modal fade" id="modalSelectTool" tabindex="-1" role="dialog" aria-labelledby="modalSelectToolLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSelectToolLabel">Select Tool</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tableAvailableTools" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr class="text-center">
                                <th>Tool ID</th>
                                <th>Tool Drw No</th>
                                <th>Revision</th>
                                <th>Status</th>
                                <th>EndCycle</th>
                                <th>Storage Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($available_tools)): ?>
                                <?php foreach ($available_tools as $tool): ?>
                                    <tr class="clickable-row" 
                                        data-inv-id="<?= isset($tool['INV_ID']) ? (int)$tool['INV_ID'] : 0; ?>"
                                        data-tool-id="<?= isset($tool['INV_TOOL_ID']) ? htmlspecialchars($tool['INV_TOOL_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                        data-status="<?= isset($tool['TOOL_STATUS']) ? (int)$tool['TOOL_STATUS'] : 0; ?>"
                                        data-end-cycle="<?= isset($tool['END_CYCLE']) ? htmlspecialchars($tool['END_CYCLE'], ENT_QUOTES, 'UTF-8') : '0'; ?>">
                                        <td>
                                            <a href="#" class="select-tool-link text-primary" style="text-decoration: underline;">
                                                <?= isset($tool['INV_TOOL_ID']) ? htmlspecialchars($tool['INV_TOOL_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                            </a>
                                        </td>
                                        <td><?= isset($tool['TOOL_DRAWING_NO']) ? htmlspecialchars($tool['TOOL_DRAWING_NO'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                        <td><?= isset($tool['REVISION']) ? htmlspecialchars($tool['REVISION'], ENT_QUOTES, 'UTF-8') : '0'; ?></td>
                                        <td>
                                            <?php 
                                            $tool_status = isset($tool['TOOL_STATUS']) ? (int)$tool['TOOL_STATUS'] : 0;
                                            $status_map = array(1 => 'New', 2 => 'Allocated', 3 => 'Available', 4 => 'InUsed', 5 => 'Onhold', 6 => 'Scrapped', 7 => 'Repairing', 8 => 'Modifying', 9 => 'DesignChange');
                                            $status_name = isset($status_map[$tool_status]) ? $status_map[$tool_status] : 'Unknown';
                                            $badge_class = in_array($tool_status, [3]) ? 'badge-success' : (in_array($tool_status, [4, 2]) ? 'badge-warning' : (in_array($tool_status, [5, 6]) ? 'badge-danger' : 'badge-secondary'));
                                            echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8') . '</span>';
                                            ?>
                                        </td>
                                        <td><?= isset($tool['END_CYCLE']) ? htmlspecialchars($tool['END_CYCLE'], ENT_QUOTES, 'UTF-8') : '0'; ?></td>
                                        <td><?= isset($tool['STORAGE_LOCATION']) ? htmlspecialchars($tool['STORAGE_LOCATION'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No available tools found</td>
                                </tr>
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
<script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js'); ?>"></script>
<script>
(function($){
    $(function(){
        // Initialize DataTable for available tools
        if ($('#tableAvailableTools tbody tr').length > 0) {
            $('#tableAvailableTools').DataTable({
                pageLength: 10,
                order: [[0, 'asc']],
                autoWidth: false
            });
        }

        // Status map for display
        var statusMap = {
            1: 'New', 2: 'Allocated', 3: 'Available', 4: 'InUsed', 
            5: 'Onhold', 6: 'Scrapped', 7: 'Repairing', 8: 'Modifying', 9: 'DesignChange'
        };

        function getStatusBadge(status) {
            var statusName = statusMap[status] || 'Unknown';
            var badgeClass = 'badge-secondary';
            if (status === 3) badgeClass = 'badge-success';
            else if ([4, 2].indexOf(status) !== -1) badgeClass = 'badge-warning';
            else if ([5, 6].indexOf(status) !== -1) badgeClass = 'badge-danger';
            return '<span class="badge ' + badgeClass + '">' + statusName + '</span>';
        }

        // Handle tool selection
        $('#tableAvailableTools tbody').on('click', 'tr.clickable-row', function(e) {
            e.preventDefault();
            var $row = $(this);
            var invId = $row.data('inv-id');
            var toolId = $row.data('tool-id');
            var status = $row.data('status');
            var endCycle = $row.data('end-cycle');

            // Set selected tool
            $('#selected_tool_id').val(toolId);
            $('#NEW_INV_ID').val(invId);
            $('#replace_tool_status').html(getStatusBadge(status));
            $('#replace_end_cycle').html(endCycle || '0');
            $('#btnSubmit').prop('disabled', false);

            // Close modal
            $('#modalSelectTool').modal('hide');
        });

        // Prevent default on link click
        $('#tableAvailableTools tbody').on('click', 'a.select-tool-link', function(e) {
            e.preventDefault();
            $(this).closest('tr').trigger('click');
        });

        // Form submit
        $('#formReplaceComposition').on('submit', function(e) {
            e.preventDefault();
            
            if (!$('#NEW_INV_ID').val()) {
                alert('Please select a tool first.');
                return;
            }
            
            var formData = $(this).serialize();
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('#btnSubmit').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Composition berhasil diganti.');
                        // Redirect back to edit tool set page
                        var tset_id = <?= isset($composition['TSCOMP_TSET_ID']) ? (int)$composition['TSCOMP_TSET_ID'] : 0; ?>;
                        if (tset_id > 0) {
                            window.location.href = '<?= base_url('Tool_management/tool_sets/edit_page/'); ?>' + tset_id;
                        } else {
                            window.history.back();
                        }
                    } else {
                        alert(response.message || 'Gagal mengganti composition.');
                        $('#btnSubmit').prop('disabled', false).html('<i class="fa fa-save"></i> Submit');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error: ' + error);
                    $('#btnSubmit').prop('disabled', false).html('<i class="fa fa-save"></i> Submit');
                }
            });
        });
    });
})(jQuery);
</script>
</body>
</html>