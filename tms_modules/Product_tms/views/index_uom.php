<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/select2/dist/css/select2.min.css'); ?>" rel="stylesheet" type="text/css">
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">

    <style>
        /* === GLOBAL: jadikan semua teks hitam === */
        html,
        body,
        #content-wrapper {
            color: #000;
        }

        /* Teks default di komponen umum */
        .table,
        .card,
        .modal,
        .dropdown-menu,
        .form-text,
        .dataTables_wrapper,
        label,
        .invalid-feedback,
        .valid-feedback {
            color: #000;
        }

        /* Input, textarea, select & placeholder */
        .form-control,
        .custom-select,
        input,
        textarea,
        select {
            color: #000 !important;
        }

        ::placeholder {
            color: #000 !important;
        }

        /* Select2 */
        .select2-container--default .select2-selection--single .select2-selection__rendered,
        .select2-results__option {
            color: #000 !important;
        }

        table.dataTable thead .sorting:after,
        table.dataTable thead .sorting_asc:after,
        table.dataTable thead .sorting_desc:after {
            opacity: 0.6;
            display: inline-block;
            /* biarkan DataTables meng-set content; jika overwritten, bisa paksa content:
       content: "\f0dc"; font-family: "FontAwesome";  -- jangan paksa kecuali perlu */
        }
    </style>
</head>

<body id="page-top">
    <?= $loading; ?>
    <div id="wrapper">
        <!-- Sidebar -->
        <?= $sidebar; ?>
        <!-- Sidebar -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- TopBar -->
                <?= $topbar; ?>
                <!-- TopBar -->
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h4 class="m-0 font-weight-bold text-primary">UoM</h4>
                                    <div class="float-right">
                                        <div class="row">
                                            <div class="col text-nowrap">
                                                <button type="button" class="btn btn-primary text-nowrap" id="btn-tambah">New</button>
                                            </div>
                                            <!-- <div class="col" style="margin-right: 25px;">
                                                <button type="button" class="btn btn-secondary" id="btn-print">Print QR</button>
                                            </div> -->
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- <form id="form-print" action="<?= base_url('uom/print-qr'); ?>" method="post" target="_blank"> -->
                                    <table id="table-uom" class="table table-bordered table-striped text-center w-100">
                                        <thead>
                                            <tr>
                                                <!-- <th>NO</th> -->
                                                <th>ID</th>
                                                <th>NAME</th>
                                                <th>DESCRIPTION</th>
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($list_data as $key => $value) : ?>
                                                <tr>
                                                    <!-- <td><?= $key + 1; ?></td> -->
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
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!---Container Fluid-->
                <!-- Modal -->
                <div class="modal fade" id="modalFormInput" tabindex="-1" aria-labelledby="modalFormInputLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalFormInputLabel">Form Input</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <!-- Form di dalam modal -->
                                <form id="formInput" method="post" action="<?= base_url('product_tms/uom/submit_data') ?>">
                                    <input type="hidden" name="action">
                                    <input type="hidden" name="uom_id">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="uom_name" class="form-label">UOM NAME</label>
                                            <input type="text" class="form-control" id="uom_name" name="uom_name" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="uom_desc" class="form-label">DESCRIPTION</label>
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
                <!-- Footer -->
                <?= $footer; ?>
                <!-- Footer -->

                <?= $foot; ?>
                <script src="<?= base_url('assets/'); ?>vendor/datatables/jquery.dataTables.min.js"></script>
                <script src="<?= base_url('assets/'); ?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
                <script src="<?= base_url('assets/'); ?>js/general.js"></script>
                <!-- <script src="<?= base_url('assets/'); ?>vendor/select2/dist/js/select2.min.js"></script> -->

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
                                "targets": [2, 3] // hanya kolom ACTION yang tidak bisa di-sort
                            }],
                            order: [
                                [0, 'asc']
                            ], // Default sort by ID (kolom pertama sekarang)
                            "initComplete": function() {
                                $('#table-uom_paginate').addClass('d-flex justify-content-end');
                            }
                        });

                        _search_data(table, '#table-uom', false, false);

                        // --- form reset, tambah, edit, submit, delete tetap sama seperti sebelumnya ---
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
                            var inputData = $(this).serializeArray();

                            $.ajax({
                                url: $('#formInput').attr('action'),
                                type: 'POST',
                                data: inputData,
                                dataType: "json",
                                success: function(response) {
                                    if (response.success) {
                                        toastr.success(response.message, 'Success', {
                                            timeOut: 5000
                                        });
                                        $('#modalFormInput').modal('hide');
                                        location.reload();
                                    } else {
                                        toastr.warning((response.message || 'Gagal'), 'Warning', {
                                            timeOut: 5000
                                        });
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error submitting form:', error);
                                    toastr.error('Terjadi kesalahan saat mengirim data.', 'Error');
                                }
                            });
                        });

                        // Delete
                        $("tbody").on('click', '.btn-delete', function(e) {
                            e.preventDefault();
                            var uomId = Number($(this).data('id')) || 0;
                            var uomName = $(this).data('name');
                            if (uomId <= 0) {
                                toastr.error('ID UoM tidak valid.');
                                return;
                            }
                            if (!confirm('Apakah Anda yakin ingin menghapus UoM "' + uomName + '"?')) return;

                            $.ajax({
                                url: '<?= site_url("product_tms/uom/delete_data") ?>',
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
                                    }, 800);
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