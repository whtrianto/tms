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
        .cell-ellipsis {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            max-width: 100%;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 4px;
            flex-wrap: wrap;
        }
        /* Keep navbar pinned */
        .navbar { position: sticky; top: 0; z-index: 1030; }
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
                        <h4 class="m-0 font-weight-bold text-primary">Tool Drawing (Engineering)</h4>
                        <a href="<?= base_url('Tool_engineering/tool_draw_engin/add_page'); ?>" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-tool-draw-sql" class="table table-bordered table-striped table-fixed w-100 text-center">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product</th>
                                        <th>Process</th>
                                        <th>Drawing No</th>
                                        <th>Tool Name</th>
                                        <th>Revision</th>
                                        <th>Status</th>
                                        <th>Effective Date</th>
                                        <th>Modified Date</th>
                                        <th>Modified By</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list_data as $row):
                                        $status_badge = '<span class="badge badge-secondary">Inactive</span>';
                                        if (isset($row['TD_STATUS'])) {
                                            $st = (int)$row['TD_STATUS'];
                                            if ($st === 2 || strtoupper((string)$row['TD_STATUS']) === 'ACTIVE') {
                                                $status_badge = '<span class="badge badge-success">Active</span>';
                                            } elseif ($st === 1) {
                                                $status_badge = '<span class="badge badge-warning">Pending</span>';
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= (int)$row['TD_ID']; ?></td>
                                            <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars(isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars(isset($row['TD_OPERATION_NAME']) ? $row['TD_OPERATION_NAME'] : '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars(isset($row['TD_DRAWING_NO']) ? $row['TD_DRAWING_NO'] : '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars(isset($row['TD_TOOL_NAME']) ? $row['TD_TOOL_NAME'] : '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td class="text-center"><?= htmlspecialchars(isset($row['TD_REVISION']) ? $row['TD_REVISION'] : 0, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= $status_badge; ?></td>
                                            <td><span class="cell-ellipsis"><?= htmlspecialchars(isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><span class="cell-ellipsis"><?= htmlspecialchars(isset($row['TD_MODIFIED_DATE']) ? $row['TD_MODIFIED_DATE'] : '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><span class="cell-ellipsis"><?= htmlspecialchars(isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="<?= base_url('Tool_engineering/tool_draw_engin/edit_page/' . (int)$row['TD_ID']); ?>" 
                                                       class="btn btn-secondary btn-sm" title="Edit">Edit</a>
                                                    <a href="<?= base_url('Tool_engineering/tool_draw_engin/history_page/' . (int)$row['TD_ID']); ?>" 
                                                       class="btn btn-warning btn-sm" title="History">Hist</a>
                                                    <button class="btn btn-danger btn-sm btn-delete"
                                                        data-id="<?= (int)$row['TD_ID']; ?>"
                                                        data-name="<?= htmlspecialchars(isset($row['TD_DRAWING_NO']) ? $row['TD_DRAWING_NO'] : '', ENT_QUOTES, 'UTF-8'); ?>">Del</button>
                                                </div>
                                            </td>
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
        var table = $('#table-tool-draw-sql').DataTable({
            lengthMenu: [[10,25,50,-1],[10,25,50,"ALL"]],
            pageLength: 25,
            order: [[0,'desc']],
            autoWidth: false,
            scrollX: true,
            columnDefs: [
                { orderable:false, targets:[10] },
                { width:'50px', targets:0 },      // ID
                { width:'120px', targets:1 },     // Product
                { width:'90px', targets:2 },      // Process
                { width:'140px', targets:3 },     // Drawing
                { width:'120px', targets:4 },     // Tool Name
                { width:'60px', targets:5 },      // Revision
                { width:'80px', targets:6 },      // Status
                { width:'110px', targets:7 },     // Effective
                { width:'120px', targets:8 },     // Modified Date
                { width:'110px', targets:9 },     // Modified By
                { width:'115px', targets:10 }     // Action
            ]
        });

        if (typeof _search_data === 'function') {
            _search_data(table, '#table-tool-draw-sql', false, false);
        }

        // Delete handler
        $('#table-tool-draw-sql').on('click', '.btn-delete', function() {
            var id = Number($(this).data('id')) || 0;
            var name = $(this).data('name') || '';
            if (id <= 0) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('ID tidak valid');
                } else {
                    alert('ID tidak valid');
                }
                return;
            }
            if (!confirm('Hapus Tool Drawing "' + name + '"?')) return;
            $.ajax({
                url: '<?= base_url("Tool_engineering/tool_draw_engin/delete_data"); ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    TD_ID: id
                }
            }).done(function(res) {
                if (res && res.success) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(res.message || 'Terhapus');
                    } else {
                        alert(res.message || 'Data berhasil dihapus');
                    }
                    setTimeout(function() {
                        location.reload();
                    }, 400);
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(res && res.message ? res.message : 'Gagal menghapus');
                    } else {
                        alert(res && res.message ? res.message : 'Gagal menghapus');
                    }
                }
            }).fail(function() {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Terjadi kesalahan');
                } else {
                    alert('Terjadi kesalahan');
                }
            });
        });
    });
})(jQuery);
</script>
</body>
</html>

