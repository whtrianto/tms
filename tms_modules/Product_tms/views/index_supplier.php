<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/select2/dist/css/select2.min.css'); ?>" rel="stylesheet" type="text/css">
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
                            <h4 class="m-0 font-weight-bold text-primary">Supplier</h4>
                            <button id="btn-new" class="btn btn-primary">New Supplier</button>
                        </div>
                        <div class="card-body">
                            <table id="table-supplier" class="table table-bordered table-striped w-100 text-center">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>NAME</th>
                                        <th>ABBR</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list_data as $row): ?>
                                        <tr>
                                            <td><?= (int)$row['SUPPLIER_ID']; ?></td>
                                            <td class="text-left"><?= htmlspecialchars($row['SUPPLIER_NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars(isset($row['SUPPLIER_ABBR']) ? $row['SUPPLIER_ABBR'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div style="display:flex; justify-content:center; gap:8px;">
                                                    <button class="btn btn-secondary btn-sm btn-edit"
                                                        data-edit='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>Edit</button>
                                                    <button class="btn btn-danger btn-sm btn-delete"
                                                        data-id="<?= (int)$row['SUPPLIER_ID']; ?>"
                                                        data-name="<?= htmlspecialchars($row['SUPPLIER_NAME'], ENT_QUOTES, 'UTF-8'); ?>">Delete</button>
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
                                <h5 class="modal-title">Form Supplier</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="formSupplier" method="post" action="<?= base_url('product_tms/supplier/submit_data'); ?>">
                                    <input type="hidden" name="action" value="">
                                    <input type="hidden" name="SUPPLIER_ID" value="">
                                    <div class="form-group">
                                        <label class="label-required">Supplier Name</label>
                                        <input type="text" name="SUPPLIER_NAME" class="form-control">
                                        <div class="invalid-feedback">Supplier name wajib diisi.</div>
                                    </div>
                                    <div class="form-group">
                                        <label>Abbreviation</label>
                                        <input type="text" name="SUPPLIER_ABBR" class="form-control">
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
    <script src="<?= base_url('assets/'); ?>js/general.js"></script>

    <script>
        (function($) {
            $(function() {
                var table = $('#table-supplier').DataTable({
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
                        targets: [3] // ACTION tidak bisa di-sort
                    }],
                    initComplete: function() {
                        $('#table-supplier_paginate').addClass('d-flex justify-content-end');
                    }
                });

                // === Terapkan per-column search sesuai standar UoM ===
                _search_data(table, '#table-supplier', false, false);

                // === New ===
                $('#btn-new').on('click', function() {
                    $('#formSupplier')[0].reset();
                    $('input[name="action"]').val('ADD');
                    $('input[name="SUPPLIER_ID"]').val('');
                    $('[name="SUPPLIER_NAME"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // === Edit ===
                $('#table-supplier').on('click', '.btn-edit', function() {
                    var d = $(this).data('edit');
                    if (!d) {
                        toastr.error('Data edit tidak valid.');
                        return;
                    }
                    $('#formSupplier')[0].reset();
                    $('input[name="action"]').val('EDIT');
                    $('input[name="SUPPLIER_ID"]').val(d.SUPPLIER_ID);
                    $('[name="SUPPLIER_NAME"]').val(d.SUPPLIER_NAME);
                    $('[name="SUPPLIER_ABBR"]').val(d.SUPPLIER_ABBR || '');
                    $('[name="SUPPLIER_NAME"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // === Submit ===
                $('#btn-submit').on('click', function(e) {
                    e.preventDefault();
                    $('#formSupplier').submit();
                });

                $('#formSupplier').on('submit', function(e) {
                    e.preventDefault();
                    var name = $.trim($('[name="SUPPLIER_NAME"]').val());
                    if (name === '') {
                        $('[name="SUPPLIER_NAME"]').addClass('is-invalid');
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

                // === Delete ===
                $('#table-supplier').on('click', '.btn-delete', function() {
                    var id = Number($(this).data('id')) || 0;
                    var name = $(this).data('name') || '';
                    if (id <= 0) {
                        toastr.error('ID tidak valid');
                        return;
                    }
                    if (!confirm('Hapus supplier "' + name + '"?')) return;
                    $.ajax({
                        url: '<?= base_url("product_tms/supplier/delete_data"); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            SUPPLIER_ID: id
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