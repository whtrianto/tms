<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/select2/dist/css/select2.min.css'); ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        /* Style tetap sama */
        html,
        body,
        #content-wrapper,
        .table,
        .card,
        .modal,
        .form-control {
            color: #000;
        }

        .form-control,
        input,
        textarea,
        select {
            color: #000 !important;
        }

        ::placeholder {
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
                                    <h4 class="m-0 font-weight-bold text-primary">UoM</h4>
                                    <div class="float-right">
                                        <button type="button" class="btn btn-primary text-nowrap" id="btn-tambah">New</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="table-uom" class="table table-bordered table-striped text-center w-100">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>NAME</th>
                                                <th>DESCRIPTION</th>
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($list_data as $key => $value) : ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($value['UOM_ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars($value['UOM_NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td><?= htmlspecialchars(isset($value['UOM_DESC']) ? $value['UOM_DESC'] : '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td>
                                                        <div style="display: flex; justify-content: center; gap: 8px;">
                                                            <button type="button" class="btn btn-secondary btn-sm btn-edit"
                                                                data-edit='<?= htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8') ?>'>
                                                                Edit
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                                data-id="<?= (int)$value['UOM_ID'] ?>"
                                                                data-name="<?= htmlspecialchars($value['UOM_NAME'], ENT_QUOTES, 'UTF-8') ?>">
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
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="formInput" method="post" action="<?= base_url('product_tms/uom/submit_data') ?>">
                                    <input type="hidden" name="action">
                                    <input type="hidden" name="uom_id">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="uom_name">UOM NAME</label>
                                            <input type="text" class="form-control" id="uom_name" name="uom_name" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="uom_desc">DESCRIPTION</label>
                                            <input type="text" class="form-control" id="uom_desc" name="uom_desc">
                                        </div>
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
                <?= $modal_logout; ?>
            </div>
            <?= $footer; ?>
        </div>
    </div>
    <?= $foot; ?>

    <script src="<?= base_url('assets/'); ?>vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url('assets/'); ?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url('assets/'); ?>js/general.js"></script>

    <script>
        $(function() {
            var table = $('#table-uom').DataTable({
                "lengthMenu": [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "ALL"]
                ],
                "pageLength": 25,
                "columnDefs": [{
                    "orderable": false,
                    "targets": [2, 3]
                }],
                "order": [
                    [0, 'asc']
                ]
            });

            if (typeof _search_data === 'function') {
                _search_data(table, '#table-uom', false, false);
            }

            function resetForm() {
                $('#formInput').trigger("reset");
                $("input[name='action']").val("");
                $("input[name='uom_id']").val("");
            }

            $("#btn-tambah").click(function(e) {
                e.preventDefault();
                resetForm();
                $("#modalFormInputLabel").text("New UOM");
                $("input[name='action']").val("ADD");
                $("#modalFormInput").modal('show');
            });

            // Edit
            $("tbody").on('click', '.btn-edit', function(e) {
                e.preventDefault();
                resetForm();
                var data = $(this).data('edit');
                $("#modalFormInputLabel").text("Form Edit UoM");
                $("input[name='action']").val("EDIT");

                // Pastikan key object JSON sesuai dengan nama kolom DB (Case Sensitive di JS)
                $("input[name=uom_id]").val(data.UOM_ID);
                $("input[name=uom_name]").val(data.UOM_NAME);
                $("input[name=uom_desc]").val(data.UOM_DESC);

                $("#modalFormInput").modal('show');
            });

            $(".btn-submit").click(function(e) {
                e.preventDefault();
                $("#formInput").submit();
            });

            $('#formInput').on('submit', function(event) {
                event.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message, 'Success');
                            $('#modalFormInput').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 500);
                        } else {
                            toastr.warning((response.message || 'Gagal'), 'Warning');
                        }
                    },
                    error: function() {
                        toastr.error('Terjadi kesalahan saat mengirim data.', 'Error');
                    }
                });
            });

            // Delete
            $("tbody").on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var uomId = Number($(this).data('id')) || 0;
                var uomName = $(this).data('name');
                if (uomId <= 0) return;

                if (!confirm('Apakah Anda yakin ingin menghapus UoM "' + uomName + '"?')) return;

                $.ajax({
                    url: '<?= base_url("product_tms/uom/delete_data") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        uom_id: uomId
                    }
                }).done(function(resp) {
                    if (resp && resp.success) {
                        toastr.success(resp.message || 'Data terhapus', 'Success!');
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        toastr.error((resp && resp.message) || 'Gagal menghapus.', 'Error!');
                    }
                }).fail(function() {
                    toastr.error('Terjadi kesalahan saat menghapus data.', 'Error!');
                });
            });
        });
    </script>
</body>

</html>