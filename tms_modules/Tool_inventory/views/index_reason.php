<!DOCTYPE html>
<html lang="en">

<head>
    <?= isset($head) ? $head : ''; ?>
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
                            <h4 class="m-0 font-weight-bold text-primary">Reason</h4>
                            <button id="btn-new" class="btn btn-primary">New Reason</button>
                        </div>
                        <div class="card-body">
                            <table id="table-reason" class="table table-bordered table-striped w-100 text-center">
                                <thead>
                                    <tr>
                                        <th>NO</th>
                                        <th>ID</th>
                                        <th>Reason Name</th>
                                        <th>Reason Code</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    foreach ($list_data as $row): ?>
                                        <tr>
                                            <td><?= (int)$no++; ?></td>
                                            <td><?= (int)$row['REASON_ID']; ?></td>
                                            <td class="text-left">
                                                <?= htmlspecialchars($row['REASON_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td><?= htmlspecialchars(isset($row['REASON_CODE']) ? $row['REASON_CODE'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div style="display:flex; justify-content:center; gap:8px;">
                                                    <button class="btn btn-secondary btn-sm btn-edit"
                                                        data-edit='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>Edit</button>
                                                    <button class="btn btn-danger btn-sm btn-delete"
                                                        data-id="<?= (int)$row['REASON_ID']; ?>"
                                                        data-name="<?= htmlspecialchars($row['REASON_NAME'], ENT_QUOTES, 'UTF-8'); ?>">Delete</button>
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
                    <div class="modal-dialog modal-md">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Form Reason</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="formReason" method="post" action="<?= base_url('tool_inventory/reason/submit_data'); ?>">
                                    <input type="hidden" name="action" value="">
                                    <input type="hidden" name="REASON_ID" value="">

                                    <div class="form-group">
                                        <label class="label-required">Reason Name</label>
                                        <input type="text" name="REASON_NAME" class="form-control" required>
                                        <div class="invalid-feedback">Reason name wajib diisi.</div>
                                    </div>

                                    <div class="form-group">
                                        <label>Reason Code</label>
                                        <input type="text" name="REASON_CODE" class="form-control">
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
                var table = $('#table-reason').DataTable({
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "ALL"]
                    ],
                    pageLength: 25,
                    // default order by ID (kolom index 1, karena NO berada di index 0)
                    order: [
                        [1, 'asc']
                    ],
                    columnDefs: [{
                        orderable: false,
                        targets: [4] // ACTION column sekarang di index 4
                    }]
                });

                _search_data(table, '#table-reason', false, false);

                // New
                $('#btn-new').on('click', function() {
                    $('#formReason')[0].reset();
                    $('input[name="action"]').val('ADD');
                    $('input[name="REASON_ID"]').val('');
                    $('[name="REASON_NAME"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // Edit
                $('#table-reason').on('click', '.btn-edit', function() {
                    var raw = $(this).data('edit');
                    var d = raw;
                    if (!d) {
                        toastr.error('Data edit tidak valid.');
                        return;
                    }
                    $('#formReason')[0].reset();
                    $('input[name="action"]').val('EDIT');
                    $('input[name="REASON_ID"]').val(d.REASON_ID);
                    $('[name="REASON_NAME"]').val(d.REASON_NAME || '');
                    $('[name="REASON_CODE"]').val(d.REASON_CODE || '');
                    $('[name="REASON_NAME"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // Submit
                $('#btn-submit').on('click', function(e) {
                    e.preventDefault();
                    $('#formReason').submit();
                });

                $('#formReason').on('submit', function(e) {
                    e.preventDefault();
                    var name = $.trim($('[name="REASON_NAME"]').val());
                    if (name === '') {
                        $('[name="REASON_NAME"]').addClass('is-invalid');
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
                            }, 300);
                        } else {
                            toastr.warning(res && res.message ? res.message : 'Gagal menyimpan');
                        }
                    }).fail(function() {
                        toastr.error('Terjadi kesalahan pada server.');
                    });
                });

                // Delete
                $('#table-reason').on('click', '.btn-delete', function() {
                    var id = Number($(this).data('id')) || 0;
                    var name = $(this).data('name') || '';
                    if (id <= 0) {
                        toastr.error('ID tidak valid');
                        return;
                    }
                    if (!confirm('Hapus reason "' + name + '"?')) return;
                    $.ajax({
                        url: '<?= base_url("tool_inventory/reason/delete_data"); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            REASON_ID: id
                        }
                    }).done(function(res) {
                        if (res && res.success) {
                            toastr.success(res.message || 'Terhapus');
                            setTimeout(function() {
                                location.reload();
                            }, 300);
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