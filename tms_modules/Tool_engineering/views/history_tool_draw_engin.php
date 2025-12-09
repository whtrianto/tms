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
                            <a href="<?= base_url('tool_engineering/tool_draw_engin'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
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
                                    <?php foreach ($history as $h): 
                                        $statusBadge = '<span class="badge badge-success">Active</span>';
                                        if (isset($h['TD_STATUS'])) {
                                            $st = (int)$h['TD_STATUS'];
                                            if ($st === 0) $statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                                            elseif ($st === 2) $statusBadge = '<span class="badge badge-warning">Pending</span>';
                                        }
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($h['TD_ID']); ?></td>
                                            <td><?= htmlspecialchars(isset($h['TD_REVISION']) ? $h['TD_REVISION'] : 0); ?></td>
                                            <td><?= $statusBadge; ?></td>
                                            <td><?= htmlspecialchars(isset($h['PRODUCT_NAME']) ? $h['PRODUCT_NAME'] : ''); ?></td>
                                            <td><?= htmlspecialchars(isset($h['OPERATION_NAME']) ? $h['OPERATION_NAME'] : ''); ?></td>
                                            <td><?= htmlspecialchars(isset($h['TOOL_RESOLVED_NAME']) ? $h['TOOL_RESOLVED_NAME'] : (isset($h['TD_TOOL_NAME']) ? $h['TD_TOOL_NAME'] : '')); ?></td>
                                            <td><?= htmlspecialchars(isset($h['MATERIAL_NAME']) ? $h['MATERIAL_NAME'] : ''); ?></td>
                                            <td><?= htmlspecialchars(isset($h['TD_EFFECTIVE_DATE']) ? $h['TD_EFFECTIVE_DATE'] : ''); ?></td>
                                            <td><?= htmlspecialchars(isset($h['TD_MODIFIED_DATE']) ? $h['TD_MODIFIED_DATE'] : ''); ?></td>
                                            <td><?= htmlspecialchars(isset($h['TD_MODIFIED_BY']) ? $h['TD_MODIFIED_BY'] : ''); ?></td>
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
            autoWidth: false
        });
    });
})(jQuery);
</script>
</body>
</html>

