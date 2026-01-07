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
                        <h4 class="m-0 font-weight-bold text-primary">Add Usage Assignment</h4>
                        <div>
                            <span class="text-muted">* = required information</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="formAssignment" method="post" action="<?= base_url('Tool_management/tool_sets/submit_assignment_data'); ?>">
                            <input type="hidden" name="TSET_ID" value="<?= isset($tool_set['TSET_ID']) ? htmlspecialchars($tool_set['TSET_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                            <input type="hidden" name="action" value="ADD">
                            
                            <div class="row">
                                <!-- Kolom Kiri -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Process</label>
                                        <div class="info-display">
                                            <?= isset($tool_set['PROCESS']) ? htmlspecialchars($tool_set['PROCESS'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Machine</label>
                                        <select name="machine_id" id="machine_id" class="form-control" required>
                                            <option value="">-- Select Machine --</option>
                                            <?php if (isset($machines) && is_array($machines)): ?>
                                                <?php foreach ($machines as $machine): ?>
                                                    <option value="<?= isset($machine['MAC_ID']) ? (int)$machine['MAC_ID'] : ''; ?>">
                                                        <?= isset($machine['MAC_NAME']) ? htmlspecialchars($machine['MAC_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Product</label>
                                        <div class="info-display">
                                            <?= isset($tool_set['PRODUCT']) ? htmlspecialchars($tool_set['PRODUCT'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Remarks</label>
                                        <textarea name="remarks" id="remarks" class="form-control" rows="3" placeholder="Enter remarks (optional)"></textarea>
                                    </div>
                                </div>

                                <!-- Kolom Kanan -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-required">Production Start</label>
                                        <input type="date" name="production_start" id="production_start" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Production Finish</label>
                                        <input type="date" name="production_finish" id="production_finish" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Total Quantity</label>
                                        <input type="number" name="total_quantity" id="total_quantity" class="form-control" min="0" step="1" required placeholder="Enter total quantity">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Save
                                </button>
                                <a href="<?= base_url('Tool_management/tool_sets/edit_page/' . (isset($tool_set['TSET_ID']) ? $tool_set['TSET_ID'] : 0)); ?>" class="btn btn-secondary">
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
            var machineId = $.trim($('#machine_id').val());
            var productionStart = $.trim($('#production_start').val());
            var productionFinish = $.trim($('#production_finish').val());
            var totalQuantity = $.trim($('#total_quantity').val());
            
            if (machineId === '' || machineId <= 0) {
                alert('Machine harus dipilih.');
                $('#machine_id').focus();
                return;
            }
            
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
                        toastr.success(res.message || 'Assignment berhasil ditambahkan');
                    } else {
                        alert(res.message || 'Assignment berhasil ditambahkan');
                    }
                    setTimeout(function() {
                        window.location.href = '<?= base_url("Tool_management/tool_sets/edit_page/" . (isset($tool_set["TSET_ID"]) ? $tool_set["TSET_ID"] : 0)); ?>';
                    }, 600);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res && res.message ? res.message : 'Gagal menyimpan assignment');
                    } else {
                        alert(res && res.message ? res.message : 'Gagal menyimpan assignment');
                    }
                    $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Save');
                }
            }).fail(function(xhr, status) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Gagal menyimpan: ' + status);
                } else {
                    alert('Gagal menyimpan: ' + status);
                }
                $('button[type="submit"]').prop('disabled', false).html('<i class="fa fa-save"></i> Save');
            });
        });
    });
})(jQuery);
</script>
</body>
</html>