<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/select2/dist/css/select2.min.css'); ?>" rel="stylesheet" type="text/css">
    <style>
        html, body, #content-wrapper { color: #000; }
        .table, .card, .modal, .dropdown-menu, .form-text, .dataTables_wrapper, label, .invalid-feedback, .valid-feedback { color: #000; }
        .form-control, .custom-select, input, textarea, select { color: #000 !important; }
        ::placeholder { color: #000 !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered, .select2-results__option { color: #000 !important; }
        /* Agar Select2 di dalam modal bisa di-search */
        .select2-container { z-index: 1051; width: 100% !important; } 
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
                                    <h4 class="m-0 font-weight-bold text-primary">Master Material</h4>
                                    <div class="float-right">
                                        <button type="button" class="btn btn-primary text-nowrap" id="btn-tambah">New</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="table-material" class="table table-bordered table-striped text-center w-100">
                                        <thead>
                                            <tr>
                                                <th>NO</th>
                                                <th>ID</th>
                                                <th>NAME</th>
                                                <th>UOM</th>
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($list_data as $key => $value) : ?>
                                                <tr>
                                                    <td><?= $key + 1; ?></td>
                                                    <td><?= $value['MATERIAL_ID']; ?></td>
                                                    <td><?= $value['MATERIAL_NAME']; ?></td>
                                                    <td><?= $value['UOM_NAME']; ?></td>
                                                    <td>
                                                        <div style="display: flex; justify-content: center; gap: 8px;">
                                                            <button type="button" class="btn btn-secondary btn-sm btn-edit"
                                                                data-edit='<?= htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8') ?>'>
                                                                Edit
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                                data-id="<?= $value['MATERIAL_ID'] ?>"
                                                                data-name="<?= htmlspecialchars($value['MATERIAL_NAME'], ENT_QUOTES, 'UTF-8') ?>">
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
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalFormInputLabel">Form Input Material</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="formInput" method="post" action="<?= base_url('tool_engineering/material/submit_data') ?>">
                                    <input type="hidden" name="action">
                                    <input type="hidden" name="material_id">
                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="material_name" class="form-label">MATERIAL NAME <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="material_name" name="material_name" placeholder="MATERIAL NAME" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label for="uom_id" class="form-label">UOM <span class="text-danger">*</span></label>
                                            <select class="form-control select2-modal" id="uom_id" name="uom_id" required>
                                                <option value="" selected disabled>-- Pilih UoM --</option>
                                                <?php foreach($dropdown_uoms as $u): ?>
                                                <option value="<?= $u['UOM_ID'] ?>"><?= $u['UOM_NAME'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
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
            </div>
        </div>
    </div>
    <?= $foot; ?>
    <script src="<?= base_url('assets/'); ?>vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url('assets/'); ?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url('assets/'); ?>vendor/select2/dist/js/select2.min.js"></script>
    <script src="<?= base_url('assets/'); ?>js/general.js"></script>

    <script>
        $(function() {
            // Inisialisasi Select2 di dalam modal
            $('.select2-modal').select2({
                dropdownParent: $('#modalFormInput')
            });

            var table = $('#table-material').DataTable({
                "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "ALL"] ],
                "pageLength": 25,
                "columnDefs": [{ "orderable": false, "targets": [0, 4] }], 
                order: [ [1, 'asc'] ], 
                "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    $('td:eq(0)', nRow).html(iDisplayIndexFull + 1);
                },
                "initComplete": function() {
                    $('#table-material_paginate').addClass('d-flex justify-content-end');
                }
            });

            _search_data(table, '#table-material', false, false);

            function resetForm() {
                $('#formInput').trigger("reset");
                $("input[name='action']").val("");
                $("input[name='material_id']").val("");
                $('.select2-modal').val(null).trigger('change');
            }

            $("#btn-tambah").click(function(e) {
                e.preventDefault();
                resetForm();
                $("#modalFormInputLabel").text("New Material");
                $("input[name='action']").val("ADD");
                $("#modalFormInput").modal('show');
            });

            $("tbody").on('click', '.btn-edit', function(e) {
                e.preventDefault();
                resetForm();
                var data = $(this).data('edit');

                $("#modalFormInputLabel").text("Form Edit Material");
                $("input[name='action']").val("EDIT");

                // Isi form dengan data
                $("input[name=material_id]").val(data.MATERIAL_ID);
                $("input[name=material_name]").val(data.MATERIAL_NAME);
                
                // Set value untuk select2
                $("#uom_id").val(data.UOM_ID).trigger('change');

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
                            toastr.success(response.message, 'Success', { timeOut: 3000 });
                            $('#modalFormInput').modal('hide'); 
                            setTimeout(function(){ location.reload(); }, 1000); 
                        } else {
                            toastr.warning(response.message.replace(/<p>/g, '').replace(/<\/p>/g, '<br>'), 'Warning', { timeOut: 5000 });
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Terjadi kesalahan saat mengirim data.', 'Error');
                    }
                });
            });

            $("tbody").on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var materialId = Number($(this).data('id')) || 0;
                var materialName = $(this).data('name');

                if (materialId <= 0) {
                    toastr.error('ID Material tidak valid.');
                    return;
                }
                if (!confirm('Apakah Anda yakin ingin menghapus Material "' + materialName + '"?')) return;

                $.ajax({
                    url: '<?= site_url("Tool_engineering/material/delete_data") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: { material_id: materialId }
                }).done(function(resp) {
                    if (resp && resp.success) {
                        toastr.success(resp.message || 'Data terhapus', 'Success!');
                        setTimeout(function() { location.reload(); }, 800);
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