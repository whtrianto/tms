<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        /* simple styling consistent with other pages */
        .table td,
        .table th {
            color: #000 !important;
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
                            <h4 class="m-0 font-weight-bold text-primary">Tool Type</h4>
                            <button id="btn-new" class="btn btn-primary">New Tool Type</button>
                        </div>
                        <div class="card-body">
                            <table id="table-tool-type" class="table table-bordered table-striped w-100 text-center">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>NAME</th>
                                        <th>DESCRIPTION</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list_data as $row): ?>
                                        <tr>
                                            <td><?= (int)$row['TOOL_TYPE_ID']; ?></td>
                                            <td class="text-left"><?= htmlspecialchars($row['TOOL_TYPE_NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-left"><?= htmlspecialchars(isset($row['TOOL_TYPE_DESC']) ? $row['TOOL_TYPE_DESC'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div style="display:flex; justify-content:center; gap:8px;">
                                                    <button class="btn btn-secondary btn-sm btn-edit"
                                                        data-edit='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>Edit</button>
                                                    <button class="btn btn-danger btn-sm btn-delete"
                                                        data-id="<?= (int)$row['TOOL_TYPE_ID']; ?>"
                                                        data-name="<?= htmlspecialchars($row['TOOL_TYPE_NAME'], ENT_QUOTES, 'UTF-8'); ?>">Delete</button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Form Tool Type</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="formToolType" method="post" action="<?= base_url('tool_engineering/tool_type/submit_data'); ?>">
                                    <input type="hidden" name="action" value="">
                                    <input type="hidden" name="TOOL_TYPE_ID" value="">
                                    <div class="form-group">
                                        <label class="label-required">Tool Type Name</label>
                                        <input type="text" name="TOOL_TYPE_NAME" class="form-control">
                                        <div class="invalid-feedback">Tool Type name wajib diisi.</div>
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="TOOL_TYPE_DESC" class="form-control" rows="3"></textarea>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button id="btn-submit" class="btn btn-primary">Submit</button>
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
        (function($) {
            $(function() {
                var table = $('#table-tool-type').DataTable({
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "ALL"]
                    ],
                    pageLength: 25,
                    order: [
                        [0, 'asc']
                    ],
                    columnDefs: [{
                        orderable: false,
                        targets: [3]
                    }]
                });

                // attach per-column search standard (if you have _search_data helper)
                if (typeof _search_data === 'function') {
                    _search_data(table, '#table-tool-type', false, false);
                }

                // New
                $('#btn-new').on('click', function() {
                    $('#formToolType')[0].reset();
                    $('input[name="action"]').val('ADD');
                    $('input[name="TOOL_TYPE_ID"]').val('');
                    $('[name="TOOL_TYPE_NAME"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // Edit
                $('#table-tool-type').on('click', '.btn-edit', function() {
                    var raw = $(this).data('edit');
                    var d = raw;
                    if (!d) {
                        toastr.error('Data edit tidak valid.');
                        return;
                    }
                    $('#formToolType')[0].reset();
                    $('input[name="action"]').val('EDIT');
                    $('input[name="TOOL_TYPE_ID"]').val(d.TOOL_TYPE_ID);
                    $('[name="TOOL_TYPE_NAME"]').val(d.TOOL_TYPE_NAME);
                    $('[name="TOOL_TYPE_DESC"]').val(d.TOOL_TYPE_DESC || '');
                    $('[name="TOOL_TYPE_NAME"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // Submit
                $('#btn-submit').on('click', function(e) {
                    e.preventDefault();
                    $('#formToolType').submit();
                });

                $('#formToolType').on('submit', function(e) {
                    e.preventDefault();
                    var name = $.trim($('[name="TOOL_TYPE_NAME"]').val());
                    if (name === '') {
                        $('[name="TOOL_TYPE_NAME"]').addClass('is-invalid');
                        return;
                    }
                    $.ajax({
                        url: $(this).attr('action'),
                        type: 'POST',
                        data: $(this).serialize(),
                        dataType: 'json'
                    }).done(function(res) {
                        if (res && res.success) {
                            toastr.success(res.message || 'Success');
                            $('#modalForm').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 400);
                        } else {
                            toastr.warning(res && res.message ? res.message : 'Gagal menyimpan');
                        }
                    }).fail(function() {
                        toastr.error('Terjadi kesalahan pada server.');
                    });
                });

                // Delete
                $('#table-tool-type').on('click', '.btn-delete', function() {
                    var id = Number($(this).data('id')) || 0;
                    var name = $(this).data('name') || '';
                    if (id <= 0) {
                        toastr.error('ID tidak valid');
                        return;
                    }
                    if (!confirm('Hapus Tool Type "' + name + '"?')) return;
                    $.ajax({
                        url: '<?= base_url("tool_engineering/tool_type/delete_data"); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            TOOL_TYPE_ID: id
                        }
                    }).done(function(res) {
                        if (res && res.success) {
                            toastr.success(res.message || 'Terhapus');
                            setTimeout(function() {
                                location.reload();
                            }, 400);
                        } else {
                            toastr.error(res && res.message ? res.message : 'Gagal menghapus');
                        }
                    }).fail(function() {
                        toastr.error('Terjadi kesalahan');
                    });
                });

            });
        })(jQuery);
    </script>
</body>

</html>