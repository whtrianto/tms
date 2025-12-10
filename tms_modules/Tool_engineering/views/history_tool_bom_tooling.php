<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        html, body, #content-wrapper { color: #000; }
        .card, .table, label, .form-text, .dataTables_wrapper { color: #000; }
        .table td, .table th { padding: 0.4rem 0.45rem !important; font-size: 0.88rem; }
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
                            <h4 class="m-0 font-weight-bold text-primary">Tool BOM Revision History</h4>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_bom_tooling'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
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
                                    <td>: <?= htmlspecialchars($product_name !== '' ? $product_name : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <tr>
                                    <th>Tool BOM</th>
                                    <td>: <?= htmlspecialchars($tool_bom !== '' ? $tool_bom : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <tr>
                                    <th>Process</th>
                                    <td>: <?= htmlspecialchars($process_name !== '' ? $process_name : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                                <tr>
                                    <th>Machine Group</th>
                                    <td>: <?= htmlspecialchars($machine_group_name !== '' ? $machine_group_name : '-', ENT_QUOTES, 'UTF-8'); ?></td>
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
                                        <th>Effective Date</th>
                                        <th>Modified Date</th>
                                        <th>Modified By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach ($history as $h): 
                                        // Map status
                                        $statusVal = isset($h['STATUS']) ? $h['STATUS'] : 'INACTIVE';
                                        $status = 'Inactive';
                                        $statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                                        if (is_string($statusVal)) {
                                            $s = strtoupper(trim($statusVal));
                                            if ($s === 'ACTIVE' || $s === '1') {
                                                $status = 'Active';
                                                $statusBadge = '<span class="badge badge-success">Active</span>';
                                            } elseif ($s === 'PENDING' || $s === '2') {
                                                $status = 'Pending';
                                                $statusBadge = '<span class="badge badge-warning">Pending</span>';
                                            }
                                        } else {
                                            $n = (int)$statusVal;
                                            if ($n === 1) {
                                                $status = 'Active';
                                                $statusBadge = '<span class="badge badge-success">Active</span>';
                                            } elseif ($n === 2) {
                                                $status = 'Pending';
                                                $statusBadge = '<span class="badge badge-warning">Pending</span>';
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars(isset($h['ID']) ? $h['ID'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars(isset($h['REVISION']) ? $h['REVISION'] : '0', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= $statusBadge; ?></td>
                                            <td><?= htmlspecialchars(isset($h['EFFECTIVE_DATE']) && $h['EFFECTIVE_DATE'] !== '' ? substr($h['EFFECTIVE_DATE'], 0, 10) : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars(isset($h['MODIFIED_DATE']) && $h['MODIFIED_DATE'] !== '' ? $h['MODIFIED_DATE'] : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars(isset($h['MODIFIED_BY']) && $h['MODIFIED_BY'] !== '' ? $h['MODIFIED_BY'] : '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
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
                { width: '150px', targets: 4 },
                { width: '120px', targets: 5 }
            ]
        });
    });
})(jQuery);
</script>
</body>
</html>

