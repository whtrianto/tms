<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
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
                        <h4 class="m-0 font-weight-bold text-primary">Edit Toolset Composition</h4>
                        <div>
                            <span class="text-muted">* = required information</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formComposition" method="post" action="<?= base_url('Tool_management/tool_sets/submit_composition_data'); ?>">
                            <input type="hidden" name="TSCOMP_ID" value="<?= isset($composition['TSCOMP_ID']) ? htmlspecialchars($composition['TSCOMP_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            
                            <div class="row">
                                <!-- Kolom Kiri -->
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
                                        <label>Tool Name</label>
                                        <div class="info-display">
                                            <?= isset($composition['TOOL_NAME']) ? htmlspecialchars($composition['TOOL_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>
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
                                        <textarea name="TSCOMP_REMARKS" class="form-control" rows="3"><?= isset($composition['TSCOMP_REMARKS']) ? htmlspecialchars($composition['TSCOMP_REMARKS'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
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
                                        <label>Tool Status</label>
                                        <div class="info-display">
                                            <?php 
                                            $tool_status = isset($composition['TOOL_STATUS']) ? (int)$composition['TOOL_STATUS'] : 0;
                                            // Tool Inventory Status: 1=New, 2=Allocated, 3=Available, 4=InUsed, 5=Onhold, 6=Scrapped, 7=Repairing, 8=Modifying, 9=DesignChange
                                            $status_map = array(1 => 'New', 2 => 'Allocated', 3 => 'Available', 4 => 'InUsed', 5 => 'Onhold', 6 => 'Scrapped', 7 => 'Repairing', 8 => 'Modifying', 9 => 'DesignChange');
                                            $status_name = isset($status_map[$tool_status]) ? $status_map[$tool_status] : 'Unknown';
                                            $badge_class = in_array($tool_status, [3]) ? 'badge-success' : (in_array($tool_status, [4, 2]) ? 'badge-warning' : (in_array($tool_status, [5, 6]) ? 'badge-danger' : 'badge-secondary'));
                                            echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8') . '</span>';
                                            ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>End Cycle</label>
                                        <input type="number" name="END_CYCLE" class="form-control" 
                                               value="<?= isset($composition['END_CYCLE']) ? htmlspecialchars($composition['END_CYCLE'], ENT_QUOTES, 'UTF-8') : '0'; ?>" 
                                               min="0" step="1">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Save Changes
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

<?= isset($foot) ? $foot : ''; ?>
<script>
(function($){
    $(function(){
        // Form submit
        $('#formComposition').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Composition berhasil diupdate.');
                        // Redirect back to edit tool set page
                        var tset_id = <?= isset($composition['TSCOMP_TSET_ID']) ? (int)$composition['TSCOMP_TSET_ID'] : 0; ?>;
                        if (tset_id > 0) {
                            window.location.href = '<?= base_url('Tool_management/tool_sets/edit_page/'); ?>' + tset_id;
                        } else {
                            window.history.back();
                        }
                    } else {
                        alert(response.message || 'Gagal mengupdate composition.');
                        $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Save Changes');
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error: ' + error);
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Save Changes');
                }
            });
        });
    });
})(jQuery);
</script>
</body>
</html>

