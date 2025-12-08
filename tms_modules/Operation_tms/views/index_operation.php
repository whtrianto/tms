<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/select2/dist/css/select2.min.css'); ?>" rel="stylesheet" type="text/css">
    <style>
        /* (Style teks hitam Anda) */
        html, body, #content-wrapper { color: #000; }
        .table, .card, .modal, .dropdown-menu, .form-text, .dataTables_wrapper, label, .invalid-feedback, .valid-feedback { color: #000; }
        .form-control, .custom-select, input, textarea, select { color: #000 !important; }
        ::placeholder { color: #000 !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered, .select2-results__option { color: #000 !important; }
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
                                    <h4 class="m-0 font-weight-bold text-primary">Master Operation</h4>
                                    <div class="float-right">
                                        <div class="row">
                                            <div class="col text-nowrap">
                                                <button type="button" class="btn btn-primary text-nowrap" id="btn-tambah">New</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="table-operation" class="table table-bordered table-striped text-center w-100">
                                        <thead>
                                            <tr>
                                                <th>NO</th>
                                                <th>ID</th>
                                                <th>NAME</th>
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($list_data as $key => $value) : ?>
                                                <tr>
                                                    <td><?= $key + 1; ?></td>
                                                    <td><?= $value['OPERATION_ID']; ?></td>
                                                    <td><?= $value['OPERATION_NAME']; ?></td>
                                                    <td>
                                                        <div style="display: flex; justify-content: center; gap: 8px;">
                                                            <button type="button" class="btn btn-secondary btn-sm btn-edit"
                                                                data-edit='<?= htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8') ?>'>
                                                                Edit
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                                data-id="<?= $value['OPERATION_ID'] ?>"
                                                                data-name="<?= htmlspecialchars($value['OPERATION_NAME'], ENT_QUOTES, 'UTF-8') ?>">
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
                                <h5 class="modal-title" id="modalFormInputLabel">Form Input Operation</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="formInput" method="post" action="<?= base_url('operation_tms/operation/submit_data') ?>">
                                    <input type="hidden" name="action">
                                    <input type="hidden" name="operation_id">
                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <label for="operation_name" class="form-label">OPERATION NAME</label>
                                            <input type="text" class="form-control" id="operation_name" name="operation_name" placeholder="OPERATION NAME" required>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary btn-submit">Submit</button>
                            </div>
                        </div>

                    </div>
                </div>

                <?= $modal_logout; ?>
                <?= $footer; ?>
                <?= $foot; ?>
                <script src="<?= base_url('assets/'); ?>vendor/datatables/jquery.dataTables.min.js"></script>
                <script src="<?= base_url('assets/'); ?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
                <script src="<?= base_url('assets/'); ?>js/general.js"></script>

                <script>
                    $(function() {
                        var action;
                        var table = $('#table-operation').DataTable({
                            "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "ALL"] ],
                            "pageLength": 25,
                            "columnDefs": [{ "orderable": false, "targets": [0, 3] }], // Kolom No dan Action
                            order: [ [1, 'asc'] ], // Default sort by ID
                            "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                                $('td:eq(0)', nRow).html(iDisplayIndexFull + 1);
                            },
                            "initComplete": function() {
                                $('#table-operation_paginate').addClass('d-flex justify-content-end');
                            }
                        });

                        _search_data(table, '#table-operation', false, false);

                        // Fungsi untuk membersihkan form
                        function resetForm() {
                            $('#formInput').trigger("reset");
                            $("input[name='action']").val("");
                            $("input[name='operation_id']").val("");
                        }

                        // Tombol "Tambah Data"
                        $("#btn-tambah").click(function(e) {
                            e.preventDefault();
                            resetForm();
                            $("#modalFormInputLabel").text("New Operation");
                            $("input[name='action']").val("ADD");
                            $("#modalFormInput").modal('show');
                        });

                        // Tombol "Edit" di setiap baris
                        $("tbody").on('click', '.btn-edit', function(e) {
                            e.preventDefault();
                            resetForm();
                            var data = $(this).data('edit');

                            $("#modalFormInputLabel").text("Form Edit Operation");
                            $("input[name='action']").val("EDIT");

                            // Isi form dengan data
                            $("input[name=operation_id]").val(data.OPERATION_ID);
                            $("input[name=operation_name]").val(data.OPERATION_NAME);

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
                                        toastr.success(response.message, 'Success', { timeOut: 5000 });
                                        $('#modalFormInput').modal('hide'); 
                                        location.reload();
                                    } else {
                                        toastr.warning(response.message.replace(/<font[^>]*>/g, '<p>').replace(/<\/font>/g, '</p>'), 'Warning', { timeOut: 5000 });
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error submitting form:', error);
                                    toastr.error('Terjadi kesalahan saat mengirim data.', 'Error');
                                }
                            });
                        });

                        // Tombol "Delete"
                        $("tbody").on('click', '.btn-delete', function(e) {
                            e.preventDefault();
                            var operationId = Number($(this).data('id')) || 0;
                            var operationName = $(this).data('name');

                            if (operationId <= 0) {
                                toastr.error('ID Operation tidak valid.');
                                return;
                            }

                            if (!confirm('Apakah Anda yakin ingin menghapus Operation "' + operationName + '"?')) return;

                            $.ajax({
                                url: '<?= site_url("operation_tms/operation/delete_data") ?>',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    operation_id: operationId
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