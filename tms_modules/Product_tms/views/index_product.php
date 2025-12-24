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

        /* small styling to match UoM table */
        #table-product thead th,
        #table-product tbody td {
            vertical-align: middle;
        }

        #table-product td .btn {
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
    <?= isset($loading) ? $loading : ''; ?>
    <div id="wrapper">
        <?= isset($sidebar) ? $sidebar : ''; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?= isset($topbar) ? $topbar : ''; ?>

                <div class="container-fluid" id="container-wrapper">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h4 class="m-0 font-weight-bold text-primary">Product</h4>
                                    <button type="button" class="btn btn-primary" id="btn-tambah">New Product</button>
                                </div>
                                <div class="card-body">
                                    <!-- TABEL -->
                                    <table id="table-product" class="table table-bordered table-striped w-100 text-center">
                                        <thead>
                                            <tr>
                                                <!-- <th>NO</th> -->
                                                <th>ID</th>
                                                <th>Product Name</th>
                                                <th>Description</th>
                                                <th>Customer Code</th>
                                                <th>Type</th>
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($list_data as $k => $row): ?>
                                                <tr>
                                                    <!-- <td><?= $k + 1 ?></td> -->
                                                    <td><?= (int)$row['PART_ID']; ?></td>
                                                    <td class="text-left">
                                                        <a href="<?= base_url('product_tms/product/detail/' . (int)$row['PART_ID']); ?>" class="product-link">
                                                            <?= htmlspecialchars($row['PART_NAME']); ?>
                                                        </a>
                                                    </td>
                                                    <td class="text-left">
                                                        <?= htmlspecialchars(isset($row['PART_DESC']) ? $row['PART_DESC'] : ''); ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars(isset($row['PART_CUS_CODE']) ? $row['PART_CUS_CODE'] : ''); ?>
                                                    </td>
                                                    <td>
                                                        <?= htmlspecialchars(isset($row['PART_TYPE']) ? $row['PART_TYPE'] : ''); ?>
                                                    </td>
                                                    <td>
                                                        <div style="display:flex; justify-content:center; gap:8px;">
                                                            <button type="button" class="btn btn-secondary btn-sm btn-edit"
                                                                data-id="<?= (int)$row['PART_ID']; ?>">
                                                                Edit
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                                data-id="<?= (int)$row['PART_ID']; ?>"
                                                                data-name="<?= htmlspecialchars($row['PART_NAME'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <!-- end table -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal form -->
                <div class="modal fade" id="modalFormInput" tabindex="-1" aria-labelledby="modalFormInputLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalFormInputLabel">New Product</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="formInput" method="post" action="<?= base_url('product_tms/product/submit_data'); ?>">
                                    <input type="hidden" name="action" id="form-action" value="">
                                    <input type="hidden" name="PART_ID" id="form-part-id" value="">
                                    <div class="form-group">
                                        <label class="label-required">Product Name</label>
                                        <input type="text" class="form-control" name="PART_NAME" id="PART_NAME">
                                        <div class="invalid-feedback">Product name wajib diisi.</div>
                                    </div>

                                    <div class="form-group">
                                        <label>Product Group</label>
                                        <select name="PARTM_PARENT_ID" class="form-control select2">
                                            <option value="">-- Pilih Group --</option>
                                            <?php foreach ($product_groups as $g): ?>
                                                <option value="<?= (int)$g['PART_ID']; ?>"><?= htmlspecialchars($g['PART_NAME']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea class="form-control" name="PART_DESC" rows="2"></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Product Type</label>
                                        <input type="text" class="form-control" name="PART_TYPE">
                                    </div>

                                    <div class="form-group">
                                        <label>UOM</label>
                                        <select name="UOM_ID" class="form-control">
                                            <option value="">-- Pilih UOM --</option>
                                            <?php foreach ($uoms as $u): ?>
                                                <option value="<?= (int)$u['UOM_ID']; ?>"><?= htmlspecialchars($u['UOM_NAME']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Customer</label>
                                        <select name="CUS_ID" class="form-control">
                                            <option value="">-- Pilih Customer --</option>
                                            <?php foreach ($customers as $c): ?>
                                                <option value="<?= (int)$c['CUS_ID']; ?>"><?= htmlspecialchars($c['CUS_NAME']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Customer Code</label>
                                        <input type="text" class="form-control" name="PART_CUS_CODE">
                                    </div>

                                    <div class="form-group">
                                        <label>Drawing No</label>
                                        <input type="text" class="form-control" name="PART_DRW_NO">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Unit Price</label>
                                                <input type="number" step="0.01" class="form-control" name="PART_UNIT_PRICE" placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Weight (kg)</label>
                                                <input type="number" step="0.001" class="form-control" name="PART_WEIGHT" placeholder="0.000">
                                            </div>
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

                <?= isset($modal_logout) ? $modal_logout : ''; ?>
            </div>

            <?= isset($footer) ? $footer : ''; ?>
        </div>
    </div>

    <?= isset($foot) ? $foot : ''; ?>
    <script src="<?= base_url('assets/'); ?>vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="<?= base_url('assets/'); ?>vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="<?= base_url('assets/'); ?>vendor/select2/dist/js/select2.min.js"></script>
    <script src="<?= base_url('assets/'); ?>js/general.js"></script>

    <script>
        $(function() {
            var table = $('#table-product').DataTable({
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "ALL"]
                ],
                pageLength: 10,
                order: [
                    [1, 'asc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [2, 5]
                }]
            });

            if (typeof _search_data === 'function') {
                _search_data(table, '#table-product', false, false);
            }

            function setInvalid($el, msg) {
                $el.addClass('is-invalid');
                var $fb = $el.siblings('.invalid-feedback');
                if ($fb.length === 0) {
                    $fb = $('<div class="invalid-feedback"></div>').insertAfter($el);
                }
                if (msg) $fb.text(msg);
            }

            function setValid($el) {
                $el.removeClass('is-invalid');
            }

            function validateForm() {
                var ok = true;
                var $name = $('[name="PART_NAME"]');
                var $uom = $('[name="UOM_ID"]');

                if ($.trim($name.val()) === '') {
                    setInvalid($name, 'Product name wajib diisi.');
                    ok = false;
                } else {
                    setValid($name);
                }

                // if ($.trim($uom.val()) === '') {
                //     setInvalid($uom, 'UOM wajib dipilih.');
                //     ok = false;
                // } else {
                //     setValid($uom);
                // }

                return ok;
            }

            // New Product
            $('#btn-tambah').on('click', function(e) {
                e.preventDefault();
                $('#formInput')[0].reset();
                $('input[name="action"]').val('ADD');
                $('input[name="PART_ID"]').val('');
                $('input[name="PART_IS_GROUP"]').val('0');
                setValid($('[name="PART_NAME"]'));
                setValid($('[name="UOM_ID"]'));
                $('#modalFormInputLabel').text('New Product');
                $('#modalFormInput').modal('show');
                $('[name="PARTM_PARENT_ID"]').val('');
            });

            // helper parse JSON in data-edit (robust)
            function parseEditData(raw) {
                if (!raw) return null;
                try {
                    return JSON.parse(raw);
                } catch (e) {
                    var s = raw.replace(/&quot;/g, '"').replace(/&#39;/g, "'").replace(/&amp;/g, '&');
                    try {
                        return JSON.parse(s);
                    } catch (e2) {
                        return null;
                    }
                }
            }

            function fillForm(d) {
                if (!d) return;
                $('[name="PART_ID"]').val(d.PART_ID);
                $('[name="PART_NAME"]').val(d.PART_NAME);
                $('[name="PART_DESC"]').val(typeof d.PART_DESC !== 'undefined' ? d.PART_DESC : '');
                $('[name="PART_TYPE"]').val(typeof d.PART_TYPE !== 'undefined' ? d.PART_TYPE : '');
                $('[name="PART_UNIT_PRICE"]').val(d.PART_UNIT_PRICE || 0); // Mapping baru
                $('[name="PART_WEIGHT"]').val(d.PART_WEIGHT || 0);
                $('[name="UOM_ID"]').val(typeof d.UOM_ID !== 'undefined' && d.UOM_ID !== null ? d.UOM_ID : '');
                $('[name="CUS_ID"]').val(typeof d.CUS_ID !== 'undefined' && d.CUS_ID !== null ? d.CUS_ID : '');
                $('[name="PART_CUS_CODE"]').val(typeof d.PART_CUS_CODE !== 'undefined' && d.PART_CUS_CODE !== null ? d.PART_CUS_CODE : '');
                $('[name="PART_DRW_NO"]').val(typeof d.PART_DRW_NO !== 'undefined' && d.PART_DRW_NO !== null ? d.PART_DRW_NO : '');
                $('[name="PART_IS_GROUP"]').val((typeof d.PART_IS_GROUP !== 'undefined') ? d.PART_IS_GROUP : 0);
                $('[name="PARTM_PARENT_ID"]').val(typeof d.PARTM_PARENT_ID !== 'undefined' ? d.PARTM_PARENT_ID : '');
            }

            // Edit (button)
            $('#table-product').on('click', '.btn-edit', function(e) {
                e.preventDefault();

                const id = $(this).data('id');
                if (!id) {
                    toastr.error('ID tidak valid');
                    return;
                }

                $.ajax({
                    url: '<?= base_url("product_tms/product/get_product_detail"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        PART_ID: id
                    },
                    success: function(res) {
                        if (!res || !res.success) {
                            toastr.error(res.message || 'Gagal mengambil data');
                            return;
                        }

                        const d = res.data;

                        $('#formInput')[0].reset();
                        $('input[name="action"]').val('EDIT');
                        $('input[name="PART_ID"]').val(d.PART_ID);

                        $('[name="PART_NAME"]').val(d.PART_NAME || '');
                        $('[name="PART_DESC"]').val(d.PART_DESC || '');
                        $('[name="PART_TYPE"]').val(d.PART_TYPE || '');
                        $('[name="PART_CUS_CODE"]').val(d.PART_CUS_CODE || '');
                        $('[name="PART_DRW_NO"]').val(d.PART_DRW_NO || '');

                        // 🔥 INI YANG SEBELUMNYA KOSONG
                        $('[name="UOM_ID"]').val(d.PART_UNITS || '').trigger('change');
                        $('[name="CUS_ID"]').val(d.PART_CUS_ID || '').trigger('change');
                        $('[name="PARTM_PARENT_ID"]').val(d.PARTM_PARENT_ID || '').trigger('change');

                        $('[name="PART_UNIT_PRICE"]').val(d.PART_UNIT_PRICE || 0);
                        $('[name="PART_WEIGHT"]').val(d.PART_WEIGHT || 0);

                        $('#modalFormInputLabel').text('Edit Product');
                        $('#modalFormInput').modal('show');
                    },
                    error: function() {
                        toastr.error('Gagal mengambil data product');
                    }
                });
            });

            // Submit
            $('.btn-submit').on('click', function(e) {
                e.preventDefault();
                $('#formInput').submit();
            });

            $('#formInput').on('submit', function(e) {
                e.preventDefault();
                if (!validateForm()) return;

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(res) {
                        if (res && res.success) {
                            toastr.success(res.message || 'Success');
                            $('#modalFormInput').modal('hide');
                            setTimeout(function() {
                                location.reload();
                            }, 400);
                        } else {
                            toastr.warning((res && res.message) || 'Gagal menyimpan data.', 'Warning');
                        }
                    },
                    error: function() {
                        toastr.error('Terjadi kesalahan saat submit data.', 'Error');
                    }
                });
            });

            // Delete (button)
            $('#table-product').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                var id = Number($(this).data('id')) || 0;
                var name = $(this).data('name') || '';
                if (id <= 0) {
                    toastr.error('ID tidak valid');
                    return;
                }
                if (!confirm('Apakah Anda yakin ingin menghapus product "' + name + '"?')) return;

                $.ajax({
                    url: '<?= base_url("product_tms/product/delete_product"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        PART_ID: id
                    },
                    success: function(res) {
                        if (res && res.success) {
                            toastr.success(res.message || 'Data terhapus', 'Success');
                            setTimeout(function() {
                                location.reload();
                            }, 400);
                        } else {
                            toastr.error((res && res.message) || 'Gagal menghapus data.', 'Error');
                        }
                    },
                    error: function() {
                        toastr.error('Terjadi kesalahan saat menghapus data.', 'Error');
                    }
                });
            });

            $('#modalFormInput').on('hidden.bs.modal', function() {
                setValid($('[name="PART_NAME"]'));
                setValid($('[name="UOM_ID"]'));
            });
        });
    </script>

</body>

</html>