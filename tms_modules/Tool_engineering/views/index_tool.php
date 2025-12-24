<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        .table td,
        .table th {
            color: #000 !important;
        }

        .label-required::after {
            content: " *";
            color: #dc3545;
            font-weight: 600;
        }

        .is-invalid+.invalid-feedback {
            display: block;
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
                            <h4 class="m-0 font-weight-bold text-primary">Tool</h4>
                            <button id="btn-new" class="btn btn-primary">New Tool</button>
                        </div>
                        <div class="card-body">
                            <table id="table-tool" class="table table-bordered table-striped w-100 text-center">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tool Name</th>
                                        <th>Tool Type</th>
                                        <th>Description</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list_data as $row): ?>
                                        <tr>
                                            <td><?= (int)$row['TC_ID']; ?></td>
                                            <td class="text-left">
                                                <a target="_blank" rel="noopener noreferrer"
                                                    href="<?= base_url('tool_engineering/tool/detail/' . (int)$row['TC_ID']); ?>">
                                                    <?= htmlspecialchars($row['TC_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars(isset($row['TT_NAME']) ? $row['TT_NAME'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-left"><?= htmlspecialchars(isset($row['TC_DESC']) ? $row['TC_DESC'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div style="display:flex; justify-content:center; gap:8px;">
                                                    <button class="btn btn-secondary btn-sm btn-edit"
                                                        data-edit='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>Edit</button>
                                                    <button class="btn btn-danger btn-sm btn-delete"
                                                        data-id="<?= (int)$row['TC_ID']; ?>"
                                                        data-name="<?= htmlspecialchars($row['TC_NAME'], ENT_QUOTES, 'UTF-8'); ?>">Delete</button>
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
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Form Tool</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="formTool" method="post" action="<?= base_url('tool_engineering/tool/submit_data'); ?>">
                                    <input type="hidden" name="action" value="">
                                    <input type="hidden" name="TC_ID" value="">

                                    <div class="form-row">
                                        <div class="form-group col-md-8">
                                            <label class="label-required">Tool Name</label>
                                            <input type="text" name="TC_NAME" class="form-control" required>
                                            <div class="invalid-feedback">Tool name wajib diisi.</div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Tool Type</label>
                                            <select name="TC_TYPE" class="form-control">
                                                <option value="">-- Pilih Type --</option>
                                                <?php foreach ($tool_types as $tt): ?>
                                                    <option value="<?= (int)$tt['TT_ID']; ?>"><?= htmlspecialchars($tt['TT_NAME']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="TC_DESC" class="form-control" rows="3"></textarea>
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
                var table = $('#table-tool').DataTable({
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
                        targets: [4]
                    }]
                });

                if (typeof _search_data === 'function') {
                    _search_data(table, '#table-tool', false, false);
                }

                // New
                $('#btn-new').on('click', function() {
                    $('#formTool')[0].reset();
                    $('input[name="action"]').val('ADD');
                    $('input[name="TC_ID"]').val('');
                    $('[name="TC_NAME"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // Edit
                $('#table-tool').on('click', '.btn-edit', function() {
                    var raw = $(this).data('edit');
                    var d = raw;
                    if (!d) {
                        toastr.error('Data edit tidak valid.');
                        return;
                    }
                    $('#formTool')[0].reset();
                    $('input[name="action"]').val('EDIT');
                    $('input[name="TC_ID"]').val(d.TC_ID);
                    $('[name="TC_NAME"]').val(d.TC_NAME || '');
                    $('[name="TC_TYPE"]').val(d.TC_TYPE || '');
                    $('[name="TC_DESC"]').val(d.TC_DESC || '');
                    $('[name="TC_NAME"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // Submit
                $('#btn-submit').on('click', function(e) {
                    e.preventDefault();
                    $('#formTool').submit();
                });

                $('#formTool').on('submit', function(e) {
                    e.preventDefault();
                    var name = $.trim($('[name="TC_NAME"]').val());
                    if (name === '') {
                        $('[name="TC_NAME"]').addClass('is-invalid');
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
                $('#table-tool').on('click', '.btn-delete', function() {
                    var id = Number($(this).data('id')) || 0;
                    var name = $(this).data('name') || '';
                    if (id <= 0) {
                        toastr.error('ID tidak valid');
                        return;
                    }
                    if (!confirm('Hapus tool "' + name + '"?')) return;
                    $.ajax({
                        url: '<?= base_url("tool_engineering/tool/delete_data"); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            TC_ID: id
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