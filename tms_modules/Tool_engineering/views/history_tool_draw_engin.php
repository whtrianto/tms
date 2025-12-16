<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        /* Konsisten: teks hitam */
        html, body, #content-wrapper { color: #000; }
        .card, .table, label, .form-text, .dataTables_wrapper { color: #000; }
        .table td, .table th { 
            padding: 0.4rem 0.45rem !important; 
            font-size: 0.88rem; 
            color: #000 !important;
        }
        /* Keep navbar pinned */
        .navbar { position: sticky; top: 0; z-index: 1030; }
        /* Fix footer spacing */
        #content-wrapper { min-height: calc(100vh - 56px); }
        #container-wrapper { 
            padding-bottom: 6rem !important; 
            margin-bottom: 2rem !important; 
        }
        /* Ensure footer doesn't overlap */
        #content {
            padding-bottom: 6rem !important;
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
                            <h4 class="m-0 font-weight-bold text-primary">Revision History - Tool Drawing Engineering</h4>
                            <div class="small text-muted">ID: <?= htmlspecialchars($drawing['TD_ID']); ?> | Drawing: <?= htmlspecialchars(isset($drawing['TD_DRAWING_NO']) ? $drawing['TD_DRAWING_NO'] : ''); ?></div>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_draw_engin'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tableHistory" class="table table-bordered table-striped table-sm w-100">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Revision</th>
                                        <th>Status</th>
                                        <th>Product</th>
                                        <th>Process</th>
                                        <th>Tool</th>
                                        <th>Material</th>
                                        <th>Effective Date</th>
                                        <th>Modified Date</th>
                                        <th>Modified By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (empty($history) || !is_array($history)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center text-muted">No revision history found.</td>
                                        </tr>
                                    <?php else:
                                        foreach ($history as $h): 
                                            // Status badge
                                            $statusBadge = '<span class="badge badge-success">Active</span>';
                                            if (isset($h['TD_STATUS'])) {
                                                $st = (int)$h['TD_STATUS'];
                                                if ($st === 0) {
                                                    $statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                                                } elseif ($st === 2) {
                                                    $statusBadge = '<span class="badge badge-warning">Pending</span>';
                                                }
                                            }
                                            
                                            // Data sudah di-resolve di query, langsung gunakan
                                            $product_name = isset($h['TD_PRODUCT_NAME']) ? $h['TD_PRODUCT_NAME'] : '';
                                            $process_name = isset($h['TD_OPERATION_NAME']) ? $h['TD_OPERATION_NAME'] : '';
                                            $tool_name = isset($h['TD_TOOL_NAME']) ? $h['TD_TOOL_NAME'] : '';
                                            $material_name = isset($h['TD_MATERIAL_NAME']) ? $h['TD_MATERIAL_NAME'] : '';
                                            $effective_date = isset($h['TD_EFFECTIVE_DATE']) ? $h['TD_EFFECTIVE_DATE'] : '';
                                            $modified_date = isset($h['TD_MODIFIED_DATE']) ? $h['TD_MODIFIED_DATE'] : '';
                                            $modified_by = isset($h['TD_MODIFIED_BY']) ? $h['TD_MODIFIED_BY'] : '';
                                            $revision = isset($h['TD_REVISION']) ? (int)$h['TD_REVISION'] : 0;
                                            $td_id = isset($h['TD_ID']) ? (int)$h['TD_ID'] : 0;
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= htmlspecialchars($td_id, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-center"><?= htmlspecialchars($revision, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= $statusBadge; ?></td>
                                            <td><?= htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($process_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($tool_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($material_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($effective_date, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($modified_date, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($modified_by, ENT_QUOTES, 'UTF-8'); ?></td>
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
            autoWidth: false
        });
    });
})(jQuery);
</script>
</body>
</html>

