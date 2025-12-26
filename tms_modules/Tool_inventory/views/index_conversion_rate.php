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
                            <h4 class="m-0 font-weight-bold text-primary">Conversion Rate</h4>
                            <button id="btn-new" class="btn btn-primary">New Conversion</button>
                        </div>
                        <div class="card-body">
                            <table id="table-conversion" class="table table-bordered table-striped w-100 text-center">
                                <thead>
                                    <tr>
                                        <th>ACTION</th>
                                        <th>NO</th>
                                        <th>ID</th>
                                        <th>Currency</th>
                                        <th>Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    foreach ($list_data as $row): ?>
                                        <tr>
                                            <td>
                                                <div style="display:flex; justify-content:center; gap:8px;">
                                                    <button class="btn btn-secondary btn-sm btn-edit" data-edit='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>Edit</button>
                                                    <button class="btn btn-danger btn-sm btn-delete" data-id="<?= (int)$row['CON_ID']; ?>" data-currency="<?= htmlspecialchars($row['CON_CURRENCY'], ENT_QUOTES, 'UTF-8'); ?>">Delete</button>
                                                </div>
                                            </td>
                                            <td><?= (int)$no++; ?></td>
                                            <td><?= (int)$row['CON_ID']; ?></td>
                                            <td class="text-left"><?= htmlspecialchars($row['CON_CURRENCY'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($row['CON_RATE'], ENT_QUOTES, 'UTF-8'); ?></td>
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
                                <h5 class="modal-title">Form Conversion Rate</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="formConversion" method="post" action="<?= base_url('tool_inventory/conversion_rate/submit_data'); ?>">
                                    <input type="hidden" name="action" value="">
                                    <input type="hidden" name="CON_ID" value="">

                                    <div class="form-group">
                                        <label class="label-required">Currency</label>
                                        <input type="text" name="CON_CURRENCY" class="form-control" required>
                                        <div class="invalid-feedback">Currency wajib diisi.</div>
                                    </div>

                                    <div class="form-group">
                                        <label class="label-required">Rate</label>
                                        <input type="text" name="CON_RATE" class="form-control" required>
                                        <div class="invalid-feedback">Rate wajib diisi dan harus angka.</div>
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
                var table = $('#table-conversion').DataTable({
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "ALL"]
                    ],
                    pageLength: 25,
                    order: [
                        [2, 'asc']
                    ],
                    columnDefs: [{
                        orderable: false,
                        targets: [0, 1]
                    }]
                });

                _search_data(table, '#table-conversion', false, false);

                $('#btn-new').on('click', function() {
                    $('#formConversion')[0].reset();
                    $('input[name="action"]').val('ADD');
                    $('input[name="CON_ID"]').val('');
                    $('[name="CON_CURRENCY"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                $('#table-conversion').on('click', '.btn-edit', function() {
                    var raw = $(this).data('edit');
                    var d = raw;
                    if (!d) {
                        toastr.error('Data edit tidak valid.');
                        return;
                    }
                    $('#formConversion')[0].reset();
                    $('input[name="action"]').val('EDIT');
                    $('input[name="CON_ID"]').val(d.CON_ID);
                    $('[name="CON_CURRENCY"]').val(d.CON_CURRENCY || '');
                    $('[name="CON_RATE"]').val(d.CON_RATE || '');
                    $('[name="CON_CURRENCY"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                $('#btn-submit').on('click', function(e) {
                    e.preventDefault();
                    $('#formConversion').submit();
                });

                $('#formConversion').on('submit', function(e) {
                    e.preventDefault();
                    var currency = $.trim($('[name="CON_CURRENCY"]').val());
                    var rate = $.trim($('[name="CON_RATE"]').val());
                    if (currency === '') {
                        $('[name="CON_CURRENCY"]').addClass('is-invalid');
                        return;
                    }
                    if (rate === '' || isNaN(rate)) {
                        $('[name="CON_RATE"]').addClass('is-invalid');
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

                $('#table-conversion').on('click', '.btn-delete', function() {
                    var id = Number($(this).data('id')) || 0;
                    var currency = $(this).data('currency') || '';
                    if (id <= 0) {
                        toastr.error('ID tidak valid');
                        return;
                    }
                    if (!confirm('Hapus conversion "' + currency + '"?')) return;
                    $.ajax({
                        url: '<?= base_url("tool_inventory/conversion_rate/delete_data"); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            CON_ID: id
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