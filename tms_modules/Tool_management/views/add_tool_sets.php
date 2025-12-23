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
                        <h4 class="m-0 font-weight-bold text-primary">Add Tool Set</h4>
                        <a href="<?= base_url('Tool_management/tool_sets'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                    <div class="card-body">
                        <form id="formToolSet" method="post" action="<?= base_url('Tool_management/tool_sets/submit_data'); ?>">
                            <input type="hidden" name="action" value="ADD">
                            
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="label-required">Toolset Name</label>
                                        <input type="text" name="toolset_name" id="toolset_name" class="form-control" required>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Tool BOM No.</label>
                                        <div class="input-group">
                                            <input type="text" name="tool_bom_no_display" id="tool_bom_no_display" class="form-control" placeholder="Click to select Tool BOM No." readonly required>
                                            <input type="hidden" name="tool_bom_mlr_id" id="tool_bom_mlr_id" value="">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary" id="btn-select-tool-bom" data-toggle="modal" data-target="#modalToolBOM">
                                                    <i class="fa fa-search"></i> Select
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool BOM Description</label>
                                        <input type="text" name="tool_bom_description" id="tool_bom_description" class="form-control" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label>Tool BOM Revision</label>
                                        <input type="number" name="tool_bom_revision" id="tool_bom_revision" class="form-control" readonly>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Product</label>
                                        <input type="text" name="product" id="product" class="form-control" readonly>
                                        <input type="hidden" name="product_id" id="product_id" value="">
                                    </div>

                                    <div class="form-group">
                                        <label>Process</label>
                                        <input type="text" name="process" id="process" class="form-control" readonly>
                                        <input type="hidden" name="process_id" id="process_id" value="">
                                    </div>

                                    <div class="form-group">
                                        <label>Toolsets Status</label>
                                        <input type="text" name="toolset_status_display" id="toolset_status_display" class="form-control" value="Incomplete" readonly>
                                        <input type="hidden" name="toolset_status" id="toolset_status" value="1">
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

<!-- Modal for Tool BOM Selection -->
<div class="modal fade" id="modalToolBOM" tabindex="-1" role="dialog" aria-labelledby="modalToolBOMLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalToolBOMLabel">Select Tool BOM No.</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="table-tool-bom-modal" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>BOM No.</th>
                                <th>Machine Group</th>
                                <th>BOM Description</th>
                                <th>BOM Revision</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($tool_bom_modal) && is_array($tool_bom_modal)): ?>
                                <?php foreach ($tool_bom_modal as $bom): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($bom['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <a href="#" class="btn-select-tool-bom-link text-primary" 
                                               data-mlr-id="<?= htmlspecialchars($bom['MLR_ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                               data-bom-no="<?= htmlspecialchars($bom['BOM_NO'], ENT_QUOTES, 'UTF-8'); ?>"
                                               style="text-decoration: underline; cursor: pointer;">
                                                <?= htmlspecialchars($bom['BOM_NO'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($bom['MACHINE_GROUP'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($bom['BOM_DESCRIPTION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($bom['BOM_REVISION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary btn-select-tool-bom" 
                                                    data-mlr-id="<?= htmlspecialchars($bom['MLR_ID'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-bom-no="<?= htmlspecialchars($bom['BOM_NO'], ENT_QUOTES, 'UTF-8'); ?>">
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
        // Initialize DataTable for Tool BOM modal
        var tableToolBOM = $('#table-tool-bom-modal').DataTable({
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10,
            order: [[1, 'asc']], // Order by BOM No. (column index 1)
            columnDefs: [{ orderable: false, targets: [5] }] // Action column is not sortable
        });

        // Handle Tool BOM selection
        $(document).on('click', '.btn-select-tool-bom, .btn-select-tool-bom-link', function(e) {
            e.preventDefault();
            var mlrId = $(this).data('mlr-id');
            var bomNo = $(this).data('bom-no');
            
            if (!mlrId || mlrId === '') {
                if (typeof toastr !== 'undefined') {
                    toastr.warning('Tool BOM ID tidak valid');
                }
                return;
            }
            
            // Set Tool BOM No. display
            $('#tool_bom_mlr_id').val(mlrId);
            $('#tool_bom_no_display').val(bomNo);
            
            // Close modal
            $('#modalToolBOM').modal('hide');
            
            // Load Tool BOM details to auto-fill fields
            loadToolBOMDetails(mlrId);
        });

        // Function to load Tool BOM details
        function loadToolBOMDetails(mlrId) {
            if (!mlrId || mlrId === '') {
                clearToolBOMFields();
                return;
            }

            // Show loading indicator
            var $bomNoDisplay = $('#tool_bom_no_display');
            var originalVal = $bomNoDisplay.val();
            $bomNoDisplay.val('Loading...');

            $.ajax({
                url: '<?= base_url("Tool_management/tool_sets/get_tool_bom_details"); ?>',
                type: 'POST',
                dataType: 'json',
                data: { 
                    mlr_id: mlrId 
                }
            }).done(function(res) {
                $bomNoDisplay.val(originalVal); // Restore original value
                
                if (res && res.success && res.data) {
                    var d = res.data;
                    
                    // Auto-fill all fields
                    $('#tool_bom_description').val(d.BOM_DESCRIPTION || '');
                    $('#tool_bom_revision').val(d.BOM_REVISION || '0');
                    $('#product').val(d.PRODUCT_NAME || '');
                    $('#product_id').val(d.PRODUCT_ID || '');
                    $('#process').val(d.PROCESS_NAME || '');
                    $('#process_id').val(d.PROCESS_ID || '');
                    
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Tool BOM Information loaded successfully');
                    }
                } else {
                    clearToolBOMFields();
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(res && res.message ? res.message : 'Tool BOM tidak ditemukan');
                    } else {
                        alert(res && res.message ? res.message : 'Tool BOM tidak ditemukan');
                    }
                }
            }).fail(function(xhr, status, error) {
                $bomNoDisplay.val(originalVal); // Restore original value
                clearToolBOMFields();
                if (typeof toastr !== 'undefined') {
                    toastr.error('Gagal memuat data Tool BOM: ' + (error || status));
                } else {
                    alert('Gagal memuat data Tool BOM: ' + (error || status));
                }
            });
        }

        // Function to clear Tool BOM fields
        function clearToolBOMFields() {
            $('#tool_bom_description').val('');
            $('#tool_bom_revision').val('0');
            $('#product').val('');
            $('#product_id').val('');
            $('#process').val('');
            $('#process_id').val('');
        }

        // Form submit with AJAX
        $('#formToolSet').on('submit', function(e) {
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
                        toastr.success(res.message || 'Tool Set berhasil ditambahkan');
                    } else {
                        alert(res.message || 'Tool Set berhasil ditambahkan');
                    }
                    setTimeout(function() {
                        window.location.href = '<?= base_url("Tool_management/tool_sets"); ?>';
                    }, 1000);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(res && res.message ? res.message : 'Gagal menyimpan Tool Set');
                    } else {
                        alert(res && res.message ? res.message : 'Gagal menyimpan Tool Set');
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

