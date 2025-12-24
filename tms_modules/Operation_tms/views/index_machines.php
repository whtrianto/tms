<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/select2/dist/css/select2.min.css'); ?>" rel="stylesheet" type="text/css">
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

        .select2-container--default .select2-selection--single .select2-selection__rendered,
        .select2-results__option {
            color: #000 !important;
        }

        .select2-container {
            z-index: 1051;
            width: 100% !important;
        }

        /* Sembunyikan field group secara default */
        #group_dropdown_wrapper {
            display: none;
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
                                    <h4 class="m-0 font-weight-bold text-primary">Master Machines</h4>
                                    <div class="float-right">
                                        <button type="button" class="btn btn-primary text-nowrap" id="btn-tambah">New</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table id="table-machines" class="table table-bordered table-striped text-center w-100">
                                        <thead>
                                            <tr>
                                                <th>NO</th>
                                                <th>ID</th>
                                                <th>NAME</th>
                                                <th>OPERATION</th>
                                                <th>GROUP</th>
                                                <!-- <th>CHARGE RATE</th> -->
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($list_data as $key => $value) : ?>
                                                <tr>
                                                    <td><?= $key + 1; ?></td>
                                                    <td><?= $value['MAC_ID']; ?></td>
                                                    <td><?= $value['MAC_NAME']; ?></td>
                                                    <td><?= $value['OP_NAME']; ?></td>
                                                    <td><?= $value['MACHINES_GROUP_NAME']; ?></td>
                                                    <td>
                                                        <div style="display: flex; justify-content: center; gap: 8px;">
                                                            <button type="button" class="btn btn-secondary btn-sm btn-edit"
                                                                data-edit='<?= htmlspecialchars(json_encode($value), ENT_QUOTES, 'UTF-8') ?>'>
                                                                Edit
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                                data-id="<?= $value['MAC_ID'] ?>"
                                                                data-name="<?= htmlspecialchars($value['MAC_NAME'], ENT_QUOTES, 'UTF-8') ?>">
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
                                <h5 class="modal-title" id="modalFormInputLabel">Form Input Machine</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="formInput" method="post" action="<?= base_url('operation_tms/machines/submit_data') ?>">
                                    <input type="hidden" name="action">
                                    <input type="hidden" name="machine_id">

                                    <div class="form-group">
                                        <label for="machine_name" class="form-label">Name (Machine / Group) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="machine_name" name="machine_name" placeholder="Nama Mesin atau Grup Mesin" required>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-6">
                                            <label for="operation_id" class="form-label">OPERATION <span class="text-danger">*</span></label>
                                            <select class="form-control select2-modal" id="operation_id" name="operation_id" required>
                                                <option value="" selected disabled>-- Pilih Operation --</option>
                                                <?php foreach ($dropdown_operations as $o): ?>
                                                    <option value="<?= $o['OP_ID'] ?>"><?= $o['OP_NAME'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <!-- <div class="form-group col-md-6">
                                            <label for="charge_rate" class="form-label">CHARGE RATE</label>
                                            <input type="number" step="0.01" class="form-control" id="charge_rate" name="charge_rate" placeholder="Contoh: 1500.50">
                                        </div> -->
                                    </div>

                                    <hr>

                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="is_group" name="is_group" value="1">
                                            <label class="custom-control-label" for="is_group">Is Machine Group?</label>
                                        </div>
                                    </div>

                                    <div class="form-group" id="group_dropdown_wrapper">
                                        <label for="parent_id" class="form-label">MACHINE GROUP (Induk) <span class="text-danger">*</span></label>
                                        <select class="form-control select2-modal" id="parent_id" name="parent_id">
                                            <option value="" selected>-- Pilih Grup (Wajib untuk Mesin) --</option>
                                            <?php foreach ($dropdown_groups as $g): ?>
                                                <option value="<?= $g['MAC_ID'] ?>"><?= $g['MAC_NAME'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
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

            var table = $('#table-machines').DataTable({
                "lengthMenu": [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "ALL"]
                ],
                "pageLength": 25,
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 5]
                }],
                order: [
                    [1, 'asc']
                ],
                "fnRowCallback": function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                    $('td:eq(0)', nRow).html(iDisplayIndexFull + 1);
                },
                "initComplete": function() {
                    $('#table-machines_paginate').addClass('d-flex justify-content-end');
                }
            });

            _search_data(table, '#table-machines', false, false);

            function resetForm() {
                $('#formInput').trigger("reset");
                $("input[name='action']").val("");
                $("input[name='machine_id']").val("");
                $('#is_group').prop('checked', false); // Uncheck
                $('.select2-modal').val(null).trigger('change');
                $('#group_dropdown_wrapper').show(); // Tampilkan lagi
            }

            // Logika untuk checkbox "Is Group"
            $('#is_group').on('change', function() {
                if (this.checked) {
                    // Jika ini ADALAH GRUP, sembunyikan pilihan grup
                    $('#group_dropdown_wrapper').slideUp();
                    $('#parent_id').val(null).trigger('change').prop('required', false);
                } else {
                    // Jika ini MESIN, tampilkan pilihan grup
                    $('#group_dropdown_wrapper').slideDown();
                    $('#parent_id').prop('required', true);
                }
            });

            $("#btn-tambah").click(function(e) {
                e.preventDefault();
                resetForm();
                $("#modalFormInputLabel").text("New Machine / Group");
                $("input[name='action']").val("ADD");
                $('#is_group').prop('checked', false).trigger('change'); // Pastikan defaultnya mesin
                $("#modalFormInput").modal('show');
            });

            $("tbody").on('click', '.btn-edit', function(e) {
                e.preventDefault();
                resetForm();
                var data = $(this).data('edit');

                // Ambil data lengkap (termasuk IS_GROUP dan PARENT_ID)
                // karena data di tabel adalah data 'anak' (IS_GROUP=0)
                // kita perlu AJAX call untuk data edit yang akurat
                $.ajax({
                    url: "<?= base_url('operation_tms/machines/get_machine_detail') ?>", // Anda perlu buat method ini
                    type: "POST",
                    dataType: 'json',
                    data: {
                        machine_id: data.MAC_ID
                    },
                    success: function(response) {
                        if (response.success) {
                            var editData = response.data;

                            $("#modalFormInputLabel").text("Form Edit Machine");
                            $("input[name='action']").val("EDIT");

                            // Isi form dengan data
                            $("input[name=machine_id]").val(editData.MAC_ID);
                            $("input[name=machine_name]").val(editData.MAC_NAME);
                            // $("input[name=charge_rate]").val(editData.CHARGE_RATE);

                            // Set checkbox
                            var isGroup = (editData.IS_GROUP == 1);
                            $('#is_group').prop('checked', isGroup).trigger('change');

                            // Set value untuk select2
                            $("#operation_id").val(editData.MAC_OP_ID).trigger('change');
                            $("#parent_id").val(editData.MACM_PARENT_ID).trigger('change');

                            $("#modalFormInput").modal('show');
                        } else {
                            toastr.error('Gagal mengambil data detail.');
                        }
                    },
                    error: function() {
                        toastr.error('Gagal mengambil data detail.');
                    }
                });
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
                var machineId = Number($(this).data('id')) || 0;
                var machineName = $(this).data('name');

                if (machineId <= 0) {
                    toastr.error('ID Machine tidak valid.');
                    return;
                }
                if (!confirm('Apakah Anda yakin ingin menghapus Machine "' + machineName + '"?')) return;

                $.ajax({
                    url: '<?= site_url("operation_tms/machines/delete_data") ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        machine_id: machineId
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