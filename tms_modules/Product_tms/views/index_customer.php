<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/select2/dist/css/select2.min.css'); ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        .table,
        .card,
        .modal,
        .dataTables_wrapper,
        label {
            color: #000;
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
                                    <h4 class="m-0 font-weight-bold text-primary">Customer</h4>
                                    <div class="float-right">
                                        <button type="button" class="btn btn-primary" id="btn-tambah">New</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="table-customer" class="table table-bordered table-striped text-center w-100">
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
                                                    <td><?= htmlspecialchars($row['CUS_ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($row['CUS_NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($row['CUS_ABBR'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <div style="display:flex; justify-content:center; gap:8px;">
                                                            <button type="button" class="btn btn-secondary btn-sm btn-edit"
                                                                data-edit='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>
                                                                Edit
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                                data-id="<?= (int)$row['CUS_ID'] ?>"
                                                                data-name="<?= htmlspecialchars($row['CUS_NAME'], ENT_QUOTES, 'UTF-8') ?>">
                                                                Delete
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
                    </div>
                </div>

                <div class="modal fade" id="modalFormInput" tabindex="-1" aria-labelledby="modalFormInputLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalFormInputLabel">Form Input</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <form id="formInput" method="post" action="<?= base_url('product_tms/customer/submit_data') ?>">
                                    <input type="hidden" name="action">
                                    <input type="hidden" name="customer_id">
                                    <div class="form-group">
                                        <label>Customer Name</label>
                                        <input type="text" class="form-control" name="customer_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Customer Abbr</label>
                                        <input type="text"
                                            class="form-control"
                                            name="customer_abbr"
                                            maxlength="10"
                                            placeholder="Optional">
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary btn-submit">Submit</button>
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
    <script src="<?= base_url('assets/js/general.js'); ?>"></script>

    <script>
        $(function() {
            var table = $('#table-customer').DataTable({
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'ALL']
                ],
                pageLength: 25,
                columnDefs: [{
                    orderable: false,
                    targets: [3]
                }],
                order: [
                    [0, 'asc']
                ],
            });

            _search_data(table, '#table-customer', false, false);

            function resetForm() {
                $('#formInput')[0].reset();
                $("input[name='action']").val('');
                $("input[name='customer_id']").val('');
            }

            $('#btn-tambah').click(function(e) {
                e.preventDefault();
                resetForm();
                $('#modalFormInputLabel').text('New Customer');
                $("input[name='action']").val('ADD');
                $('#modalFormInput').modal('show');
            });

            $('#table-customer').on('click', '.btn-edit', function(e) {
                e.preventDefault();
                resetForm();
                var row = $(this).data('edit');
                $('#modalFormInputLabel').text('Edit Customer');

                $("input[name='action']").val('EDIT');

                // Mapping field dari JSON database ke Input Form
                $("input[name='customer_id']").val(row.CUS_ID);
                $("input[name='customer_name']").val(row.CUS_NAME);
                $("input[name='customer_abbr']").val(row.CUS_ABBR || '');

                $('#modalFormInput').modal('show');
            });

            $('.btn-submit').click(function(e) {
                e.preventDefault();
                $('#formInput').submit();
            });

            $('#formInput').on('submit', function(e) {
                e.preventDefault();
                var data = $(this).serialize();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(res) {
                        if (res && res.success) {
                            toastr.success(res.message || 'Success');
                            $('#modalFormInput').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 400);
                        } else {
                            toastr.warning(res && res.message ? res.message : 'Gagal menyimpan data.');
                        }
                    },
                    error: function() {
                        toastr.error('Terjadi kesalahan saat menyimpan data.');
                    }
                });
            });

            $('#table-customer').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = Number($(this).data('id')) || 0;
                var name = $(this).data('name');
                if (id <= 0) {
                    toastr.error('ID tidak valid');
                    return;
                }
                if (!confirm('Hapus Customer "' + name + '" ?')) return;
                $.ajax({
                    url: '<?= base_url("product_tms/customer/delete_data"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        customer_id: id
                    },
                    success: function(res) {
                        if (res && res.success) {
                            toastr.success(res.message || 'Customer dihapus');
                            setTimeout(function() {
                                location.reload();
                            }, 400);
                        } else {
                            toastr.error(res && res.message ? res.message : 'Gagal menghapus customer');
                        }
                    },
                    error: function() {
                        toastr.error('Terjadi kesalahan saat menghapus customer.');
                    }
                });
            });
        });
    </script>
</body>

</html>