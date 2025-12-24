<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        /* konsisten: teks hitam */
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
                            <h4 class="m-0 font-weight-bold text-primary">Maker</h4>
                            <button id="btn-new" class="btn btn-primary">New Maker</button>
                        </div>
                        <div class="card-body">
                            <table id="table-maker" class="table table-bordered table-striped w-100 text-center">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Maker</th>
                                        <th>Maker Code</th>
                                        <th>Description</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list_data as $row): ?>
                                        <tr>
                                            <td><?= (int)$row['MAKER_ID']; ?></td>
                                            <td class="text-left">
                                                <a target="_blank" rel="noopener noreferrer"
                                                    href="<?= base_url('tool_engineering/maker/detail/' . (int)$row['MAKER_ID']); ?>"
                                                    title="Lihat detail <?= htmlspecialchars($row['MAKER_NAME'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?= htmlspecialchars($row['MAKER_NAME'], ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars(isset($row['MAKER_CODE']) ? $row['MAKER_CODE'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="text-left"><?= htmlspecialchars(isset($row['MAKER_DESC']) ? $row['MAKER_DESC'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div style="display:flex; justify-content:center; gap:8px;">
                                                    <button class="btn btn-secondary btn-sm btn-edit"
                                                        data-edit='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>Edit</button>
                                                    <button class="btn btn-danger btn-sm btn-delete"
                                                        data-id="<?= (int)$row['MAKER_ID']; ?>"
                                                        data-name="<?= htmlspecialchars($row['MAKER_NAME'], ENT_QUOTES, 'UTF-8'); ?>">Delete</button>
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
                                <h5 class="modal-title">Form Maker</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="formMaker" method="post" action="<?= base_url('tool_engineering/maker/submit_data'); ?>">
                                    <input type="hidden" name="action" value="">
                                    <input type="hidden" name="MAKER_ID" value="">
                                    <div class="form-row">
                                        <div class="form-group col-md-8">
                                            <label class="label-required">Name</label>
                                            <input type="text" name="MAKER_NAME" class="form-control" required>
                                            <div class="invalid-feedback">Maker name wajib diisi.</div>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Code</label>
                                            <input type="text" name="MAKER_CODE" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>Description</label>
                                            <input type="text" name="MAKER_DESC" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-8">
                                            <label>Address</label>
                                            <input type="text" name="MAKER_ADDR" class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>City</label>
                                            <input type="text" name="MAKER_CITY" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>State</label>
                                            <input type="text" name="MAKER_STATE" class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Country</label>
                                            <input type="text" name="MAKER_COUNTRY" class="form-control">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Zipcode</label>
                                            <input type="text" name="MAKER_ZIPCODE" class="form-control">
                                        </div>
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
                var table = $('#table-maker').DataTable({
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "ALL"]
                    ],
                    pageLength: 25,
                    order: [
                        [0, 'asc']
                    ], // sort by Maker name
                    columnDefs: [{
                        orderable: false,
                        targets: [4]
                    }] // ACTION tidak bisa di-sort
                });

                // per-column search helper jika tersedia
                if (typeof _search_data === 'function') {
                    _search_data(table, '#table-maker', false, false);
                }

                // New
                $('#btn-new').on('click', function() {
                    $('#formMaker')[0].reset();
                    $('input[name="action"]').val('ADD');
                    $('input[name="MAKER_ID"]').val('');
                    $('[name="MAKER_NAME"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // Edit
                $('#table-maker').on('click', '.btn-edit', function() {
                    var raw = $(this).data('edit');
                    var d = raw;
                    if (!d) {
                        toastr.error('Data edit tidak valid.');
                        return;
                    }
                    $('#formMaker')[0].reset();
                    $('input[name="action"]').val('EDIT');
                    $('input[name="MAKER_ID"]').val(d.MAKER_ID);
                    $('[name="MAKER_NAME"]').val(d.MAKER_NAME || '');
                    $('[name="MAKER_CODE"]').val(d.MAKER_CODE || '');
                    $('[name="MAKER_DESC"]').val(d.MAKER_DESC || '');
                    $('[name="MAKER_ADDR"]').val(d.MAKER_ADDR || '');
                    $('[name="MAKER_CITY"]').val(d.MAKER_CITY || '');
                    $('[name="MAKER_COUNTRY"]').val(d.MAKER_COUNTRY || '');
                    $('[name="MAKER_STATE"]').val(d.MAKER_STATE || '');
                    $('[name="MAKER_ZIPCODE"]').val(d.MAKER_ZIPCODE || '');
                    $('[name="MAKER_NAME"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // Submit
                $('#btn-submit').on('click', function(e) {
                    e.preventDefault();
                    $('#formMaker').submit();
                });

                $('#formMaker').on('submit', function(e) {
                    e.preventDefault();
                    var name = $.trim($('[name="MAKER_NAME"]').val());
                    if (name === '') {
                        $('[name="MAKER_NAME"]').addClass('is-invalid');
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
                $('#table-maker').on('click', '.btn-delete', function() {
                    var id = Number($(this).data('id')) || 0;
                    var name = $(this).data('name') || '';
                    if (id <= 0) {
                        toastr.error('ID tidak valid');
                        return;
                    }
                    if (!confirm('Hapus maker "' + name + '"?')) return;
                    $.ajax({
                        url: '<?= base_url("tool_engineering/maker/delete_data"); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            MAKER_ID: id
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