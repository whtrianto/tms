<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <style>
        html,
        body,
        #content-wrapper {
            color: #000;
        }

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
                                    <h4 class="m-0 font-weight-bold text-primary">Master Department</h4>
                                    <div class="float-right">
                                        <button type="button" class="btn btn-primary text-nowrap" id="btn-tambah">New</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="table-department" class="table table-bordered table-striped text-center w-100">
                                        <thead>
                                            <tr>
                                                <th>ACTION</th>
                                                <th>NO</th>
                                                <th>ID</th>
                                                <th>NAME</th>
                                                <th>DESCRIPTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($list_data as $key => $value) : ?>
                                                <tr>
                                                    <td>
                                                        <div style="display: flex; justify-content: center; gap: 8px;">
                                                            <button type="button" class="btn btn-secondary btn-sm btn-edit"
                                                                data-edit='<?= htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8') ?>'>
                                                                Edit
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                                data-id="<?= $value['DEPART_ID'] ?>"
                                                                data-name="<?= htmlspecialchars($value['DEPART_NAME'], ENT_QUOTES, 'UTF-8') ?>">
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td><?= $key + 1; ?></td>
                                                    <td><?= $value['DEPART_ID']; ?></td>
                                                    <td><?= $value['DEPART_NAME']; ?></td>
                                                    <td><?= $value['DEPART_DESC']; ?></td>
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
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalFormInputLabel">Form Input Department</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="formInput" method="post" action="<?= base_url('tool_management/department/submit_data') ?>">
                                    <input type="hidden" name="action">
                                    <input type="hidden" name="department_id">
                                    <div class="form-group">
                                        <label for="department_name" class="form-label">DEPART NAME <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="department_name" name="department_name" placeholder="Nama Department" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="department_desc" class="form-label">DESCRIPTION</label>
                                        <textarea class="form-control" id="department_desc" name="department_desc" rows="3" placeholder="Deskripsi (opsional)"></textarea>
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
            </div>
        </div>
    </div>
    <?= $foot; ?>
    <script src="<?= base_url('assets/'); ?>vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url('assets/'); ?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url('assets/'); ?>js/general.js"></script>

    <script>
        $(function() {
            var table = $('#table-department').DataTable({
                "lengthMenu": [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "ALL"]
                ],
                "pageLength": 25,
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 1]
                }],
                order: [
                    [2, 'asc']
                ],
                "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    $('td:eq(1)', nRow).html(iDisplayIndexFull + 1);
                },
                "initComplete": function() {
                    $('#table-department_paginate').addClass('d-flex justify-content-end');
                }
            });

            _search_data(table, '#table-department', false, false);

            function resetForm() {
                $('#formInput').trigger("reset");
                $("input[name='action']").val("");
                $("input[name='department_id']").val("");
            }

            $("#btn-tambah").click(function(e) {
                e.preventDefault();
                resetForm();
                $("#modalFormInputLabel").text("New Department");
                $("input[name='action']").val("ADD");
                $("#modalFormInput").modal('show');
            });

            $("tbody").on('click', '.btn-edit', function(e) {
                e.preventDefault();
                resetForm();
                var data = $(this).data('edit');

                $("#modalFormInputLabel").text("Form Edit Department");
                $("input[name='action']").val("EDIT");

                // Isi form dengan data
                $("input[name=department_id]").val(data.DEPART_ID);
                $("input[name=department_name]").val(data.DEPART_NAME);
                $("textarea[name=department_desc]").val(data.DEPART_DESC);

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
                                timeOut: 3000
                            });
                            $('#modalFormInput').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            toastr.warning(response.message.replace(/<p>/g, '').replace(/<\/p>/g, '<br>'), 'Warning', {
                                timeOut: 5000
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Terjadi kesalahan saat mengirim data.', 'Error');
                    }
                });
            });

            $("tbody").on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var departmentId = Number($(this).data('id')) || 0;
                var departmentName = $(this).data('name');

                if (departmentId <= 0) {
                    toastr.error('ID Department tidak valid.');
                    return;
                }
                if (!confirm('Apakah Anda yakin ingin menghapus Department "' + departmentName + '"?')) return;

                $.ajax({
                    url: '<?= site_url("tool_management/department/delete_data") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        department_id: departmentId
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