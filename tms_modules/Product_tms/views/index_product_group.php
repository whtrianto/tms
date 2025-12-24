<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/'); ?>vendor/select2/dist/css/select2.min.css" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/'); ?>vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css">
    <style>
        .label-required::after {
            content: " *";
            color: #dc3545;
            font-weight: 600;
        }

        .is-invalid+.invalid-feedback {
            display: block;
        }

        .invalid-feedback {
            font-size: .85rem;
        }

        /* table row height */
        #table-pg thead th,
        #table-pg tbody td {
            padding-top: .75rem;
            padding-bottom: .75rem;
            vertical-align: middle;
        }

        #table-pg tbody td {
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #table-pg td>.btn {
            margin-right: .25rem;
        }

        #content-wrapper .card,
        #content-wrapper .card-body,
        #content-wrapper .table,
        #content-wrapper .dataTables_wrapper,
        #content-wrapper label,
        #content-wrapper .form-text,
        #content-wrapper .invalid-feedback,
        #content-wrapper .valid-feedback,
        #content-wrapper .form-control,
        #content-wrapper .custom-select,
        #content-wrapper input,
        #content-wrapper textarea,
        #content-wrapper select,
        #content-wrapper ::placeholder,
        #content-wrapper .select2-container--default .select2-selection--single .select2-selection__rendered,
        #content-wrapper .select2-results__option {
            color: #000 !important;
        }

        /* Pastikan teks normal di cell table juga hitam */
        #content-wrapper table td,
        #content-wrapper table th {
            color: #000 !important;
        }
    </style>
</head>

<body id="page-top">
    <?= $loading; ?>
    <div id="wrapper">
        <?= $sidebar; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?= $topbar; ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h4 class="m-0 font-weight-bold text-primary">Product Groups</h4>
                                    <button type="button" class="btn btn-primary" id="btn-new-group">New Group</button>
                                </div>
                                <div class="card-body">
                                    <table id="table-pg" class="table table-bordered table-striped table-sm w-100 text-center">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>GROUP NAME</th>
                                                <th>DESCRIPTION</th>
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($list_data)): ?>
                                                <?php foreach ($list_data as $k => $row): ?>
                                                    <tr>
                                                        <td><?= (int)$row['PART_ID']; ?></td>
                                                        <td><?= htmlspecialchars($row['PART_NAME']); ?></td>
                                                        <td><?= htmlspecialchars(isset($row['PART_DESC']) ? $row['PART_DESC'] : ''); ?></td>
                                                        <td>
                                                            <div style="display:flex; justify-content:center; gap:8px;">
                                                                <button type="button" class="btn btn-secondary btn-sm btn-edit-group"
                                                                    data-edit='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>
                                                                    Edit
                                                                </button>
                                                                <button type="button" class="btn btn-danger btn-sm btn-delete-group"
                                                                    data-id="<?= (int)$row['PART_ID'] ?>"
                                                                    data-name="<?= htmlspecialchars($row['PART_NAME'], ENT_QUOTES, 'UTF-8') ?>">
                                                                    Delete
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalCreateGroup" tabindex="-1" role="dialog" aria-labelledby="modalCreateGroupLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalCreateGroupLabel">New Product Group</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <form id="formCreateGroup" method="post" action="<?php echo base_url('product_tms/product-group/create_group'); ?>">
                                    <input type="hidden" name="action" value="ADD">

                                    <input type="hidden" name="PART_ID" value="">

                                    <div class="form-group">
                                        <label class="label-required">Product Group Name</label>
                                        <input type="text" name="PART_NAME" class="form-control">
                                        <div class="invalid-feedback">Product group name wajib diisi.</div>
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="PART_DESC" class="form-control" rows="2"></textarea>
                                    </div>
                                </form>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button id="btn-create-group" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>

                <?= isset($modal_logout) ? $modal_logout : ''; ?>
            </div>

            <?= $footer; ?>
        </div>
    </div>

    <?= $foot; ?>
    <script src="<?= base_url('assets/'); ?>vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url('assets/'); ?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url('assets/'); ?>vendor/select2/dist/js/select2.min.js"></script>
    <script src="<?= base_url('assets/'); ?>js/general.js"></script>

    <script>
        (function($) {
            $(function() {

                var table = $('#table-pg').DataTable({
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "ALL"]
                    ],
                    pageLength: 25,
                    order: [
                        [0, 'asc']
                    ],
                    columnDefs: [{
                        orderable: false,
                        targets: [2, 3]
                    }]
                });

                if (typeof _search_data === 'function') {
                    _search_data(table, '#table-pg', false, false);
                }

                function resetForm() {
                    $('#formCreateGroup')[0].reset();
                    $('input[name="action"]').val('ADD');

                    // PERBAIKAN: Reset PART_ID
                    $('input[name="PART_ID"]').val('');

                    // remove invalid state
                    $('[name="PART_NAME"]').removeClass('is-invalid');
                }

                // New Group
                $('#btn-new-group').on('click', function(e) {
                    e.preventDefault();
                    resetForm();
                    $('#modalCreateGroupLabel').text('New Product Group');
                    // ensure form action is create endpoint
                    $('#formCreateGroup').attr('action', '<?= base_url("product_tms/product-group/create_group") ?>');
                    $('#modalCreateGroup').modal('show');
                });

                // Edit (prefill from data-edit)
                $('#table-pg').on('click', '.btn-edit-group', function(e) {
                    e.preventDefault();
                    resetForm();
                    var data = $(this).data('edit'); // parsed JSON object

                    $('#modalCreateGroupLabel').text('Edit Product Group');
                    $('input[name="action"]').val('EDIT');

                    // PERBAIKAN: Mapping data JSON dari database (PART_...) ke Input Form (PART_...)
                    $('input[name="PART_ID"]').val(data.PART_ID);
                    $('input[name="PART_NAME"]').val(data.PART_NAME);
                    $('textarea[name="PART_DESC"]').val(data.PART_DESC || '');

                    // set form action to product submit (edit product)
                    $('#formCreateGroup').attr('action', '<?= base_url("product_tms/product/submit_data") ?>');

                    $('#modalCreateGroup').modal('show');
                });

                // Submit create/edit
                $('#btn-create-group').on('click', function(e) {
                    e.preventDefault();
                    $('#formCreateGroup').submit();
                });

                $('#formCreateGroup').on('submit', function(e) {
                    e.preventDefault();

                    // simple client validation
                    var name = $.trim($('[name="PART_NAME"]').val());
                    if (name === '') {
                        $('[name="PART_NAME"]').addClass('is-invalid');
                        return;
                    }

                    var action = $('input[name="action"]').val();
                    var url = $(this).attr('action');
                    var data = $(this).serialize();

                    if (action === 'EDIT') {
                        // we must tell product controller this is a group
                        data = data + '&PART_IS_GROUP=1'; // Sesuaikan jika controller butuh flag ini
                    }

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: data,
                        dataType: 'json',
                        success: function(res) {
                            if (res && res.success) {
                                toastr.success(res.message || 'Success');
                                $('#modalCreateGroup').modal('hide');
                                setTimeout(function() {
                                    location.reload();
                                }, 400);
                            } else {
                                toastr.warning(res && res.message ? res.message : 'Gagal menyimpan data.');
                            }
                        },
                        error: function(xhr) {
                            toastr.error('Terjadi kesalahan saat menyimpan data.');
                        }
                    });
                });

                // Delete Group
                $('#table-pg').on('click', '.btn-delete-group', function(e) {
                    e.preventDefault();
                    var id = Number($(this).data('id')) || 0;
                    var name = $(this).data('name');
                    if (id <= 0) {
                        toastr.error('ID tidak valid');
                        return;
                    }
                    if (!confirm('Hapus Product Group "' + name + '" ?')) return;

                    $.ajax({
                        url: '<?= base_url("product_tms/product/delete_product") ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            // PERBAIKAN: Key yang dikirim harus PART_ID sesuai controller Product
                            PART_ID: id
                        },
                        success: function(res) {
                            if (res && res.success) {
                                toastr.success(res.message || 'Group dihapus');
                                setTimeout(function() {
                                    location.reload();
                                }, 400);
                            } else {
                                toastr.error(res && res.message ? res.message : 'Gagal menghapus group');
                            }
                        },
                        error: function() {
                            toastr.error('Terjadi kesalahan saat menghapus group.');
                        }
                    });
                });

            });
        })(jQuery);
    </script>

</body>

</html>