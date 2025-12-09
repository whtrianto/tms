<!DOCTYPE html>
<html lang="en">

<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        .table td,
        .table th {
            color: #000 !important;
            padding: 0.35rem 0.4rem !important;
            font-size: 0.85rem;
        }

        .table-fixed {
            table-layout: fixed;
        }

        .table-fixed th, .table-fixed td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .label-required::after {
            content: " *";
            color: #dc3545;
            font-weight: 600;
        }

        .is-invalid+.invalid-feedback {
            display: block;
        }

        .table .btn-sm {
            padding: 0.25rem 0.4rem;
            font-size: 0.7rem;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 4px;
            flex-wrap: wrap;
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
                            <h4 class="m-0 font-weight-bold text-primary">Tool BOM Engineering</h4>
                            <button id="btn-new" class="btn btn-primary">New Tool BOM</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table id="table-tool-bom-engin" class="table table-bordered table-striped table-fixed w-100 text-center">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tool BOM</th>
                                        <th>Description</th>
                                        <th>Product</th>
                                        <th>Process</th>
                                        <th>Machine Group</th>
                                        <th>Revision</th>
                                        <th>Status</th>
                                        <th>Modified By</th>
                                        <th>Modified Date</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list_data as $row): 
                                        // Resolve product name
                                        $product_name = '';
                                        if (isset($row['PRODUCT_ID']) && $row['PRODUCT_ID'] > 0) {
                                            foreach ($products as $p) {
                                                if ((int)$p['PRODUCT_ID'] === (int)$row['PRODUCT_ID']) {
                                                    $product_name = $p['PRODUCT_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        if ($product_name === '' && isset($row['PRODUCT'])) {
                                            $product_name = $row['PRODUCT'];
                                        }
                                        
                                        // Resolve process name
                                        $process_name = '';
                                        if (isset($row['PROCESS_ID']) && $row['PROCESS_ID'] > 0) {
                                            foreach ($operations as $o) {
                                                if ((int)$o['OPERATION_ID'] === (int)$row['PROCESS_ID']) {
                                                    $process_name = $o['OPERATION_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        
                                        // Resolve machine group name
                                        $machine_group_name = '';
                                        if (isset($row['MACHINE_GROUP_ID']) && $row['MACHINE_GROUP_ID'] > 0) {
                                            foreach ($machine_groups as $mg) {
                                                if ((int)$mg['MACHINE_ID'] === (int)$row['MACHINE_GROUP_ID']) {
                                                    $machine_group_name = $mg['MACHINE_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        if ($machine_group_name === '' && isset($row['MACHINE_GROUP'])) {
                                            $machine_group_name = $row['MACHINE_GROUP'];
                                        }
                                        
                                        // Status badge
                                        $status_badge = '';
                                        if (isset($row['STATUS'])) {
                                            $st = strtoupper((string)$row['STATUS']);
                                            if ($st === 'ACTIVE' || $st === '1') {
                                                $status_badge = '<span class="badge badge-success">Active</span>';
                                            } elseif ($st === 'PENDING' || $st === '2') {
                                                $status_badge = '<span class="badge badge-warning">Pending</span>';
                                            } else {
                                                $status_badge = '<span class="badge badge-secondary">Inactive</span>';
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['ID'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($row['TOOL_BOM'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($row['DESCRIPTION'] ?: '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($process_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($machine_group_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($row['REVISION'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= $status_badge; ?></td>
                                            <td><?= htmlspecialchars($row['MODIFIED_BY'] ?: '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= htmlspecialchars($row['MODIFIED_DATE'] ?: '', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <!-- <button class="btn btn-sm btn-warning btn-edit" 
                                                            data-edit='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8'); ?>'
                                                            title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button> -->
                                                    <a class="btn btn-sm btn-info" 
                                                       href="<?= base_url('Tool_engineering/tool_bom_engin/edit_page/' . (int)$row['ID']); ?>"
                                                       title="Edit Page">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-danger btn-delete" 
                                                            data-id="<?= (int)$row['ID']; ?>"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
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

                <!-- Modal Form -->
                <div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Tool BOM Engineering Form</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="formToolBOM" method="post" action="<?= base_url('Tool_engineering/tool_bom_engin/submit_data'); ?>" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="">
                                    <input type="hidden" name="ID" value="">

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label class="label-required">Product</label>
                                            <select name="PRODUCT_ID" class="form-control">
                                                <option value="">-- Select Product --</option>
                                                <?php foreach ($products as $p): ?>
                                                    <option value="<?= (int)$p['PRODUCT_ID']; ?>"><?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label class="label-required">Tool BOM</label>
                                            <input type="text" name="TOOL_BOM" class="form-control" required>
                                            <div class="invalid-feedback">Tool BOM wajib diisi.</div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>Revision</label>
                                            <input type="number" name="REVISION" class="form-control" value="0">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>Process</label>
                                            <select name="PROCESS_ID" class="form-control">
                                                <option value="">-- Select Process --</option>
                                                <?php foreach ($operations as $o): ?>
                                                    <option value="<?= (int)$o['OPERATION_ID']; ?>"><?= htmlspecialchars($o['OPERATION_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>Machine Group</label>
                                            <select name="MACHINE_GROUP_ID" class="form-control">
                                                <option value="">-- Select Machine Group --</option>
                                                <?php foreach ($machine_groups as $mg): ?>
                                                    <option value="<?= (int)$mg['MACHINE_ID']; ?>"><?= htmlspecialchars($mg['MACHINE_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>Description</label>
                                            <textarea name="DESCRIPTION" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>Status</label>
                                            <select name="STATUS" class="form-control">
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                                <option value="2">Pending</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>Effective Date</label>
                                            <input type="date" name="EFFECTIVE_DATE" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>Change Summary</label>
                                            <textarea name="CHANGE_SUMMARY" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>Drawing</label>
                                            <input type="file" name="DRAWING_FILE" class="form-control" accept="*">
                                            <small class="form-text text-muted">Upload file gambar untuk drawing.</small>
                                        </div>
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

    <script>
        (function($) {
            $(function() {
                var table = $('#table-tool-bom-engin').DataTable({
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "ALL"]
                    ],
                    pageLength: 25,
                    order: [
                        [0, 'desc']
                    ],
                    autoWidth: false,
                    scrollX: false,
                    columnDefs: [
                        { orderable: false, targets: [10] },
                        { width: '50px', targets: 0 },      // ID
                        { width: '120px', targets: 1 },     // Tool BOM
                        { width: '150px', targets: 2 },     // Description
                        { width: '100px', targets: 3 },     // Product
                        { width: '100px', targets: 4 },     // Process
                        { width: '120px', targets: 5 },     // Machine Group
                        { width: '70px', targets: 6 },      // Revision
                        { width: '80px', targets: 7 },      // Status
                        { width: '100px', targets: 8 },     // Modified By
                        { width: '120px', targets: 9 },     // Modified Date
                        { width: '100px', targets: 10 }     // ACTION
                    ]
                });

                if (typeof _search_data === 'function') {
                    _search_data(table, '#table-tool-bom-engin', false, false);
                }

                // New
                $('#btn-new').on('click', function() {
                    $('#formToolBOM')[0].reset();
                    $('input[name="action"]').val('ADD');
                    $('input[name="ID"]').val('');
                    $('[name="TOOL_BOM"]').removeClass('is-invalid');
                    $('[name="REVISION"]').val(0);
                    $('[name="STATUS"]').val(1);
                    // Set default effective date to today
                    var today = new Date().toISOString().split('T')[0];
                    $('[name="EFFECTIVE_DATE"]').val(today);
                    $('#modalForm').modal('show');
                });

                // Edit
                $('#table-tool-bom-engin').on('click', '.btn-edit', function() {
                    var raw = $(this).data('edit');
                    var d = raw;
                    if (!d) {
                        toastr.error('Data edit tidak valid.');
                        return;
                    }
                    $('#formToolBOM')[0].reset();
                    $('input[name="action"]').val('EDIT');
                    $('input[name="ID"]').val(d.ID);
                    $('[name="TOOL_BOM"]').val(d.TOOL_BOM || '');
                    $('[name="DESCRIPTION"]').val(d.DESCRIPTION || '');
                    $('[name="PRODUCT_ID"]').val(d.PRODUCT_ID || '');
                    $('[name="PROCESS_ID"]').val(d.PROCESS_ID || '');
                    $('[name="MACHINE_GROUP_ID"]').val(d.MACHINE_GROUP_ID || '');
                    $('[name="REVISION"]').val(d.REVISION || 0);
                    $('[name="STATUS"]').val(d.STATUS !== undefined ? d.STATUS : 1);
                    $('[name="EFFECTIVE_DATE"]').val(d.EFFECTIVE_DATE || '');
                    $('[name="CHANGE_SUMMARY"]').val(d.CHANGE_SUMMARY || '');
                    $('[name="TOOL_BOM"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // Submit
                $('#btn-submit').on('click', function(e) {
                    e.preventDefault();
                    $('#formToolBOM').submit();
                });

                $('#formToolBOM').on('submit', function(e) {
                    e.preventDefault();
                    var toolBom = $.trim($('[name="TOOL_BOM"]').val());
                    
                    var isValid = true;
                    if (toolBom === '') {
                        $('[name="TOOL_BOM"]').addClass('is-invalid');
                        isValid = false;
                    } else {
                        $('[name="TOOL_BOM"]').removeClass('is-invalid');
                    }

                    if (!isValid) return;

                    var formEl = $(this)[0];
                    var fd = new FormData(formEl);
                    
                    $.ajax({
                        url: $(this).attr('action'),
                        type: 'POST',
                        data: fd,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        cache: false,
                        timeout: 30000
                    }).done(function(res) {
                        if (typeof res === 'object' && res !== null && res.hasOwnProperty('success')) {
                            if (res.success === true) {
                                toastr.success(res.message || 'Berhasil disimpan');
                                $('#modalForm').modal('hide');
                                setTimeout(function() {
                                    location.reload();
                                }, 600);
                            } else {
                                toastr.warning(res.message || 'Gagal menyimpan data');
                            }
                        } else {
                            toastr.error('Response tidak valid dari server');
                        }
                    }).fail(function(xhr, status, error) {
                        var msg = 'Terjadi kesalahan pada server';
                        if (status === 'timeout') msg = 'Request timeout';
                        else if (status === 'error') msg = 'HTTP Error ' + xhr.status;
                        else if (status === 'parsererror') {
                            msg = 'Response bukan JSON valid. Raw response: ' + xhr.responseText.substring(0, 200);
                        }
                        toastr.error(msg);
                    });
                });

                // Delete
                $('#table-tool-bom-engin').on('click', '.btn-delete', function() {
                    var id = Number($(this).data('id')) || 0;
                    if (id <= 0) {
                        toastr.error('ID tidak valid');
                        return;
                    }

                    if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                        return;
                    }

                    $.ajax({
                        url: '<?= base_url("Tool_engineering/tool_bom_engin/delete_data"); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: { ID: id }
                    }).done(function(res) {
                        if (res && res.success) {
                            toastr.success(res.message || 'Data berhasil dihapus');
                            setTimeout(function() {
                                location.reload();
                            }, 600);
                        } else {
                            toastr.warning(res.message || 'Gagal menghapus data');
                        }
                    }).fail(function(xhr, status, err) {
                        toastr.error('Gagal menghapus data');
                        console.error('Delete failed:', status, err);
                    });
                });
            });
        })(jQuery);
    </script>
</body>

</html>

