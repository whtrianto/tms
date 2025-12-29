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
        .navbar { position: sticky; top: 0; z-index: 1030; }
        #content-wrapper { min-height: calc(100vh - 56px); }
        #container-wrapper { 
            padding-bottom: 2rem !important; 
        }
        .card { 
            margin-bottom: 2rem; 
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
                        <h4 class="m-0 font-weight-bold text-primary">Edit Usage Assignment</h4>
                        <div>
                            <span class="text-muted">* = required information</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formAssignment" method="post" action="<?= base_url('Tool_management/tool_sets/submit_assignment_data'); ?>">
                            <input type="hidden" name="TASGN_ID" value="<?= isset($assignment['TASGN_ID']) ? htmlspecialchars($assignment['TASGN_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <input type="hidden" name="TSET_ID" value="<?= isset($assignment['TASGN_TSET_ID']) ? htmlspecialchars($assignment['TASGN_TSET_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <input type="hidden" name="action" value="EDIT">
                            
                            <div class="row">
                                <!-- Kolom Kiri -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Process</label>
                                        <div class="info-display">
                                            <?= isset($assignment['OPERATION_NAME']) ? htmlspecialchars($assignment['OPERATION_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Machine</label>
                                        <div class="info-display">
                                            <?= isset($assignment['MACHINE_NAME']) ? htmlspecialchars($assignment['MACHINE_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Product</label>
                                        <div class="info-display">
                                            <?= isset($assignment['PRODUCT_NAME']) ? htmlspecialchars($assignment['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <textarea name="remarks" id="remarks" class="form-control" rows="3" placeholder="Enter remarks (optional)"><?= isset($assignment['REMARKS']) ? htmlspecialchars($assignment['REMARKS'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-required">Production Start</label>
                                        <input type="date" name="production_start" id="production_start" class="form-control" 
                                               value="<?= isset($assignment['PRODUCTION_START']) && !empty($assignment['PRODUCTION_START']) ? date('Y-m-d', strtotime($assignment['PRODUCTION_START'])) : date('Y-m-d'); ?>" 
                                               required>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Production Finish</label>
                                        <input type="date" name="production_finish" id="production_finish" class="form-control" 
                                               value="<?= isset($assignment['PRODUCTION_END']) && !empty($assignment['PRODUCTION_END']) ? date('Y-m-d', strtotime($assignment['PRODUCTION_END'])) : date('Y-m-d'); ?>" 
                                               required>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Total Quantity</label>
                                        <input type="number" name="total_quantity" id="total_quantity" class="form-control" 
                                               value="<?= isset($assignment['USAGE']) ? htmlspecialchars($assignment['USAGE'], ENT_QUOTES, 'UTF-8') : '0'; ?>" 
                                               min="0" step="1" required placeholder="Enter total quantity">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Save Changes
                                </button>
                                <a href="<?= base_url('Tool_management/tool_sets/edit_page/' . (isset($assignment['TASGN_TSET_ID']) ? $assignment['TASGN_TSET_ID'] : 0)); ?>" class="btn btn-secondary">
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
(function($) {
    $(function() {
        $('#formAssignment').on('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            var productionStart = $.trim($('#production_start').val());
            var productionFinish = $.trim($('#production_finish').val());
            var totalQuantity = $.trim($('#total_quantity').val());
            
            if (productionStart === '') {
                alert('Production Start wajib diisi.');
                $('#production_start').focus();
                return;
            }
            
            if (productionFinish === '') {
                alert('Production Finish wajib diisi.');
                $('#production_finish').focus();
                return;
            }
            
            if (totalQuantity === '' || totalQuantity < 0) {
                alert('Total Quantity wajib diisi.');
                $('#total_quantity').focus();
                return;
            }
            
            // Submit form
            var formData = $(this).serialize();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('button[type="submit"]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                }
            }).done(function(res) {
                if (res && res.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Assignment berhasil diperbarui');
                    } else {
                        alert(res.message || 'Assignment berhasil diperbarui');
                    }
                    setTimeout(function() {
                        var tset_id = <?= isset($assignment['TASGN_TSET_ID']) ? (int)$assignment['TASGN_TSET_ID'] : 0; ?>;
                        window.location.href = '<?= base_url("Tool_management/tool_sets/edit_page/"); ?>' + tset_id;
                    }, 600);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res && res.message ? res.message : 'Gagal memperbarui assignment');
                    } else {
                        alert(res && res.message ? res.message : 'Gagal memperbarui assignment');
                    }
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Save Changes');
                }
            }).fail(function(xhr, status) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Gagal menyimpan: ' + status);
                } else {
                    alert('Gagal menyimpan: ' + status);
                }
                $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Save Changes');
            });
        });
    });
})(jQuery);
</script>
</body>
</html>

