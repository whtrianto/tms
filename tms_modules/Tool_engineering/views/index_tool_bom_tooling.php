<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        .table td, .table th {
            color: #000 !important;
            padding: 0.35rem 0.4rem !important;
            font-size: 0.85rem;
        }
        .table-fixed { table-layout: fixed; }
        .table-fixed th, .table-fixed td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 6px;
            flex-wrap: wrap;
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
                        <h4 class="m-0 font-weight-bold text-primary">Tool BOM Tooling</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-tool-bom-tooling" class="table table-bordered table-striped table-fixed w-100 text-center">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product</th>
                                        <th>Tool BOM</th>
                                        <th>Process</th>
                                        <th>Machine Group</th>
                                        <th>Revision</th>
                                        <th>Status</th>
                                        <th>Effective Date</th>
                                        <th>Modified Date</th>
                                        <th>Modified By</th>
                                        <th>Type Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list_data as $row):
                                        // Resolve product name
                                        $product_name = '';
                                        if (isset($row['PRODUCT_ID']) && $row['PRODUCT_ID'] > 0) {
                                            foreach ($products as $p) {
                                                if ((int)$p['PRODUCT_ID'] === (int)$row['PRODUCT_ID']) {
                                                    $product_name = $p['PRODUCT_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        if ($product_name === '' && isset($row['PRODUCT'])) {
                                            $product_name = $row['PRODUCT'];
                                        }
                                        // Resolve process name
                                        $process_name = '';
                                        if (isset($row['PROCESS_ID']) && $row['PROCESS_ID'] > 0) {
                                            foreach ($operations as $o) {
                                                if ((int)$o['OPERATION_ID'] === (int)$row['PROCESS_ID']) {
                                                    $process_name = $o['OPERATION_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        // Resolve machine group name
                                        $machine_group_name = '';
                                        if (isset($row['MACHINE_GROUP_ID']) && $row['MACHINE_GROUP_ID'] > 0) {
                                            foreach ($machine_groups as $mg) {
                                                if ((int)$mg['MACHINE_ID'] === (int)$row['MACHINE_GROUP_ID']) {
                                                    $machine_group_name = $mg['MACHINE_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        if ($machine_group_name === '' && isset($row['MACHINE_GROUP'])) {
                                            $machine_group_name = $row['MACHINE_GROUP'];
                                        }
                                        // Status badge
                                        $status_badge = '';
                                        if (isset($row['STATUS'])) {
                                            $st = strtoupper((string)$row['STATUS']);
                                            if ($st === 'ACTIVE' || $st === '1') {
                                                $status_badge = '<span class="badge badge-success">Active</span>';
                                            } elseif ($st === 'PENDING' || $st === '2') {
                                                $status_badge = '<span class="badge badge-warning">Pending</span>';
                                            } else {
                                                $status_badge = '<span class="badge badge-secondary">Inactive</span>';
                                            }
                                        }
                                        $effective = isset($row['EFFECTIVE_DATE']) ? $row['EFFECTIVE_DATE'] : '';
                                        $modified = isset($row['MODIFIED_DATE']) ? $row['MODIFIED_DATE'] : '';
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <a href="<?= base_url('Tool_engineering/tool_bom_engin/detail_page/' . (int)$row['ID']); ?>" 
                                               class="text-primary" 
                                               style="text-decoration: underline; cursor: pointer;"
                                               title="View Detail">
                                                <?= htmlspecialchars($row['TOOL_BOM'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($process_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($machine_group_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($row['REVISION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= $status_badge; ?></td>
                                        <td><?= htmlspecialchars($effective, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars($modified, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars(isset($row['MODIFIED_BY']) ? $row['MODIFIED_BY'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a class="btn btn-sm btn-warning" href="<?= base_url('Tool_engineering/tool_bom_tooling/detail_page/' . (int)$row['ID']); ?>" title="View Detail">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-info btn-history" data-id="<?= (int)$row['ID']; ?>" title="History">
                                                    <i class="fas fa-history"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?= isset($modal_logout) ? $modal_logout : ''; ?>
            </div>
            <?= isset($footer) ? $footer : ''; ?>
        </div>
    </div>
</div>

<?= isset($foot) ? $foot : ''; ?>
<script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js'); ?>"></script>
<script>
(function($){
    $(function(){
        var table = $('#table-tool-bom-tooling').DataTable({
            lengthMenu: [[10,25,50,-1],[10,25,50,"ALL"]],
            pageLength: 25,
            order: [[0,'desc']],
            autoWidth: false,
            scrollX: false,
            columnDefs: [
                { orderable:false, targets:[10] },
                { width:'70px', targets:0 },
                { width:'130px', targets:2 },
                { width:'120px', targets:3 },
                { width:'140px', targets:4 },
                { width:'80px', targets:5 },
                { width:'90px', targets:6 },
                { width:'110px', targets:7 },
                { width:'120px', targets:8 },
                { width:'110px', targets:9 },
                { width:'120px', targets:10 }
            ]
        });

        if (typeof _search_data === 'function') {
            _search_data(table, '#table-tool-bom-tooling', false, false);
        }

        $('#table-tool-bom-tooling').on('click', '.btn-history', function(){
            var id = Number($(this).data('id')) || 0;
            if (id <= 0) {
                toastr.error('ID tidak valid');
                return;
            }
            window.location.href = '<?= base_url('Tool_engineering/tool_bom_tooling/history_page/'); ?>' + id;
        });
    });
})(jQuery);
</script>
</body>
</html>
