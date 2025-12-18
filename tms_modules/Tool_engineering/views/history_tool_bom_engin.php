<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        html, body, #content-wrapper { color: #000; }
        .card, .table, label, .form-text, .dataTables_wrapper { color: #000; }
        .table td, .table th { 
            padding: 0.4rem 0.45rem !important; 
            font-size: 0.88rem; 
            color: #000 !important;
        }
        .info-section {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
        }
        .info-section table {
            margin-bottom: 0;
        }
        .info-section th {
            width: 140px;
            font-weight: 600;
            color: #495057;
        }
        .info-section td {
            color: #212529;
        }
        .navbar { position: sticky; top: 0; z-index: 1030; }
        #content-wrapper { min-height: calc(100vh - 56px); }
        #container-wrapper { 
            padding-bottom: 6rem !important; 
            margin-bottom: 2rem !important; 
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
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="m-0 font-weight-bold text-primary">Revision History - Tool BOM (Engineering)</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars(isset($drawing['TD_ID']) ? $drawing['TD_ID'] : ''); ?> | Tool BOM: <?= htmlspecialchars(isset($drawing['TD_TOOL_BOM']) ? $drawing['TD_TOOL_BOM'] : ''); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_bom_engin'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Information Section -->
                        <div class="info-section">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th>Product</th>
                                    <td>: <?= htmlspecialchars(isset($product_name) && $product_name !== '' ? $product_name : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <tr>
                                    <th>Tool BOM</th>
                                    <td>: <?= htmlspecialchars(isset($tool_bom) && $tool_bom !== '' ? $tool_bom : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <tr>
                                    <th>Process</th>
                                    <td>: <?= htmlspecialchars(isset($process_name) && $process_name !== '' ? $process_name : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <tr>
                                    <th>Machine Group</th>
                                    <td>: <?= htmlspecialchars(isset($machine_group_name) && $machine_group_name !== '' ? $machine_group_name : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            </table>
                        </div>

                        <!-- History Table -->
                        <h5 class="mb-3">Tool BOM Revision History</h5>
                        <div class="table-responsive">
                            <table id="tableHistory" class="table table-bordered table-striped table-sm w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Revision</th>
                                        <th>Status</th>
                                        <!-- <th>Product</th>
                                        <th>Process</th>
                                        <th>Machine Group</th> -->
                                        <th>Effective Date</th>
                                        <th>Modified Date</th>
                                        <th>Modified By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (empty($history) || !is_array($history)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No revision history found.</td>
                                        </tr>
                                    <?php else:
                                        foreach ($history as $h): 
                                            // Status mapping: 2=Active, 3=Pending, 5/lainnya=Inactive (sesuai index_tool_bom_engin.php)
                                            $statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                                            if (isset($h['TD_STATUS'])) {
                                                $st = (int)$h['TD_STATUS'];
                                                if ($st === 2) {
                                                    $statusBadge = '<span class="badge badge-success">Active</span>';
                                                } elseif ($st === 3) {
                                                    $statusBadge = '<span class="badge badge-warning">Pending</span>';
                                                } elseif ($st === 5) {
                                                    $statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                                                }
                                            }
                                            
                                            // Data dari query
                                            $td_id = isset($h['TD_ID']) ? (int)$h['TD_ID'] : 0;
                                            $revision = isset($h['TD_REVISION']) ? (int)$h['TD_REVISION'] : 0;
                                            $product_name_history = isset($h['TD_PRODUCT_NAME']) ? $h['TD_PRODUCT_NAME'] : '';
                                            $process_name_history = isset($h['TD_PROCESS_NAME']) ? $h['TD_PROCESS_NAME'] : '';
                                            $machine_group_history = isset($h['TD_MACHINE_GROUP']) ? $h['TD_MACHINE_GROUP'] : '';
                                            $effective_date = isset($h['TD_EFFECTIVE_DATE']) ? $h['TD_EFFECTIVE_DATE'] : '';
                                            $modified_date = isset($h['TD_MODIFIED_DATE']) ? $h['TD_MODIFIED_DATE'] : '';
                                            $modified_by = isset($h['TD_MODIFIED_BY']) ? $h['TD_MODIFIED_BY'] : '';
                                    ?>
                                        <tr>
                                            <td class="text-left"><?= htmlspecialchars($td_id, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-left"><?= htmlspecialchars($revision, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= $statusBadge; ?></td>
                                            <!-- <td class="text-left"><?= htmlspecialchars($product_name_history, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-left"><?= htmlspecialchars($process_name_history, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-left"><?= htmlspecialchars($machine_group_history, ENT_QUOTES, 'UTF-8'); ?></td> -->
                                            <td class="text-left"><?= htmlspecialchars($effective_date !== '' ? $effective_date : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-left"><?= htmlspecialchars($modified_date !== '' ? $modified_date : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-left"><?= htmlspecialchars($modified_by !== '' ? $modified_by : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php 
                                        endforeach;
                                    endif; ?>
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
        $('#tableHistory').DataTable({
            lengthMenu: [[10,25,50,-1],[10,25,50,"ALL"]],
            pageLength: 25,
            order: [[1,'desc']],
            autoWidth: false,
            columnDefs: [
                { width: '70px', targets: 0 },
                { width: '100px', targets: 1 },
                { width: '100px', targets: 2 },
                { width: '120px', targets: 3 },
                { width: '120px', targets: 4 },
                { width: '120px', targets: 5 },
                { width: '120px', targets: 6 },
                { width: '150px', targets: 7 },
                { width: '120px', targets: 8 },
                { className: "text-left", targets: "_all" }
            ]
        });
    });
})(jQuery);
</script>
</body>
</html>

