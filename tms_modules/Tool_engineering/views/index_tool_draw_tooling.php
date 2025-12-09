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
            padding: 0.25rem 0.3rem !important;
            font-size: 0.80rem;
        }

        .table-fixed {
            table-layout: fixed;
        }

        .table-fixed th,
        .table-fixed td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cell-ellipsis {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            max-width: 140px;
            /* limit width so columns fit */
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
                            <h4 class="m-0 font-weight-bold text-primary">Tool Drawing (Tooling)</h4>
                            <!-- <button id="btn-new" class="btn btn-primary">New Tool Drawing</button> -->
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table-tool-draw-tooling" class="table table-bordered table-striped table-fixed w-100 text-center">
                                    <thead>
                                        <tr>
                                            <th>Tool Drawing No.</th>
                                            <th>Tool Name</th>
                                            <th>Min Quantity</th>
                                            <th>Replenish Quantity</th>
                                            <th>Maker</th>
                                            <th>Price</th>
                                            <th>Description</th>
                                            <th>Effective Date</th>
                                            <th>Material</th>
                                            <th>Standard Tool Life</th>
                                            <th>ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Ensure master arrays exist to avoid warnings
                                        $tools = isset($tools) ? $tools : array();
                                        $makers = isset($makers) ? $makers : array();
                                        $materials = isset($materials) ? $materials : array();

                                        foreach ($list_data as $row):
                                            // Support both TT_* (tooling table) and TD_* (engineering table) keys
                                            $row_id = isset($row['TT_ID']) ? (int)$row['TT_ID'] : (isset($row['TD_ID']) ? (int)$row['TD_ID'] : 0);
                                            $drawing_no = isset($row['TD_DRAWING_NO']) ? $row['TD_DRAWING_NO'] : (isset($row['TT_DRAWING_NO']) ? $row['TT_DRAWING_NO'] : '');
                                            $drawing_label = $drawing_no ? pathinfo($drawing_no, PATHINFO_FILENAME) : '';
                                            $tool_key = isset($row['TT_TOOL_ID']) ? $row['TT_TOOL_ID'] : (isset($row['TD_TOOL_ID']) ? $row['TD_TOOL_ID'] : null);
                                            $maker_key = isset($row['TT_MAKER_ID']) ? $row['TT_MAKER_ID'] : (isset($row['TD_MAKER_ID']) ? $row['TD_MAKER_ID'] : null);
                                            $material_key = isset($row['TT_MATERIAL_ID']) ? $row['TT_MATERIAL_ID'] : (isset($row['TD_MATERIAL_ID']) ? $row['TD_MATERIAL_ID'] : null);

                                            $tool_name = '';
                                            foreach ($tools as $t) {
                                                if (isset($t['TOOL_ID']) && (int)$t['TOOL_ID'] == (int)$tool_key) {
                                                    $tool_name = $t['TOOL_NAME'];
                                                    break;
                                                }
                                            }
                                            // If engineering row provides TD_TOOL_NAME (string), use it as fallback
                                            if ($tool_name === '' && isset($row['TD_TOOL_NAME']) && $row['TD_TOOL_NAME'] !== '') {
                                                $tool_name = $row['TD_TOOL_NAME'];
                                            }

                                            $maker_name = '';
                                            foreach ($makers as $m) {
                                                if (isset($m['MAKER_ID']) && (int)$m['MAKER_ID'] == (int)$maker_key) {
                                                    $maker_name = $m['MAKER_NAME'];
                                                    break;
                                                }
                                            }

                                            $material_name = '';
                                            foreach ($materials as $mat) {
                                                if (isset($mat['MATERIAL_ID']) && (int)$mat['MATERIAL_ID'] == (int)$material_key) {
                                                    $material_name = $mat['MATERIAL_NAME'];
                                                    break;
                                                }
                                            }
                                        ?>
                                            <tr>
                                                <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars($drawing_label, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars($tool_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-center"><?= isset($row['TT_MIN_QTY']) ? (int)$row['TT_MIN_QTY'] : (isset($row['TD_MIN_QTY']) ? (int)$row['TD_MIN_QTY'] : 0); ?></td>
                                                <td class="text-center"><?= isset($row['TT_REPLENISH_QTY']) ? (int)$row['TT_REPLENISH_QTY'] : (isset($row['TD_REPLENISH_QTY']) ? (int)$row['TD_REPLENISH_QTY'] : 0); ?></td>
                                                <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars($maker_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-right"><?= number_format((float)(isset($row['TT_PRICE']) ? $row['TT_PRICE'] : (isset($row['TD_PRICE']) ? $row['TD_PRICE'] : 0)), 2); ?></td>
                                                <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars(isset($row['TT_DESCRIPTION']) ? $row['TT_DESCRIPTION'] : (isset($row['TD_DESCRIPTION']) ? $row['TD_DESCRIPTION'] : ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td><span class="cell-ellipsis"><?= htmlspecialchars(isset($row['TT_EFFECTIVE_DATE']) ? $row['TT_EFFECTIVE_DATE'] : (isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars($material_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-center"><?= isset($row['TT_TOOL_LIFE']) ? (int)$row['TT_TOOL_LIFE'] : (isset($row['TD_TOOL_LIFE']) ? (int)$row['TD_TOOL_LIFE'] : 0); ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <?php 
                                                        // Determine which ID to use for edit/history links
                                                        // Prefer TD_ID (engineering) since data comes from engineering table
                                                        $edit_id = isset($row['TD_ID']) ? (int)$row['TD_ID'] : $row_id;
                                                        ?>
                                                        <a href="<?= base_url('Tool_engineering/tool_draw_tooling/edit_page/' . $edit_id); ?>" 
                                                           class="btn btn-secondary btn-sm" title="Edit">Edit</a>
                                                        <a href="<?= base_url('Tool_engineering/tool_draw_tooling/history_page/' . $edit_id); ?>" 
                                                           class="btn btn-warning btn-sm" title="History">Hist</a>
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

                <!-- Detail Modal -->
                <div class="modal fade" id="modalDetailDrawing" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Drawing Detail</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered table-sm">
                                    <tr>
                                        <th style="width:160px">Tool</th>
                                        <td id="detailTool"></td>
                                    </tr>
                                    <tr>
                                        <th>Min Qty</th>
                                        <td id="detailMinQty"></td>
                                    </tr>
                                    <tr>
                                        <th>Replenish Qty</th>
                                        <td id="detailReplenishQty"></td>
                                    </tr>
                                    <tr>
                                        <th>Maker</th>
                                        <td id="detailMaker"></td>
                                    </tr>
                                    <tr>
                                        <th>Price</th>
                                        <td id="detailPrice"></td>
                                    </tr>
                                    <tr>
                                        <th>Description</th>
                                        <td id="detailDescription"></td>
                                    </tr>
                                    <tr>
                                        <th>Material</th>
                                        <td id="detailMaterial"></td>
                                    </tr>
                                    <tr>
                                        <th>Tool Life</th>
                                        <td id="detailToolLife"></td>
                                    </tr>
                                    <tr>
                                        <th>Effective Date</th>
                                        <td id="detailEffective"></td>
                                    </tr>
                                    <tr>
                                        <th>Modified Date</th>
                                        <td id="detailModified"></td>
                                    </tr>
                                    <tr>
                                        <th>Modified By</th>
                                        <td id="detailModifiedBy"></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Modal -->
                <div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Tool Drawing Tooling Form</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <form id="formToolDrawing" method="post" action="<?= base_url('Tool_engineering/tool_draw_tooling/submit_data'); ?>">
                                    <input type="hidden" name="action" value="">
                                    <input type="hidden" name="TT_ID" value="">

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="label-required">Tool</label>
                                            <select name="TT_TOOL_ID" class="form-control" required>
                                                <option value="">-- Select Tool --</option>
                                                <?php foreach ($tools as $t): ?>
                                                    <option value="<?= (int)$t['TOOL_ID']; ?>"><?= htmlspecialchars($t['TOOL_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">Tool wajib dipilih.</div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Maker</label>
                                            <select name="TT_MAKER_ID" class="form-control">
                                                <option value="">-- Select Maker --</option>
                                                <?php foreach ($makers as $m): ?>
                                                    <option value="<?= (int)$m['MAKER_ID']; ?>"><?= htmlspecialchars($m['MAKER_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>Min Quantity</label>
                                            <input type="number" name="TT_MIN_QTY" class="form-control" value="0">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Replenish Quantity</label>
                                            <input type="number" name="TT_REPLENISH_QTY" class="form-control" value="0">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Price</label>
                                            <input type="number" name="TT_PRICE" class="form-control" step="0.01" value="0">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Material</label>
                                            <select name="TT_MATERIAL_ID" class="form-control">
                                                <option value="">-- Select Material --</option>
                                                <?php foreach ($materials as $m): ?>
                                                    <option value="<?= (int)$m['MATERIAL_ID']; ?>"><?= htmlspecialchars($m['MATERIAL_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Tool Life</label>
                                            <input type="number" name="TT_TOOL_LIFE" class="form-control" value="0">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-12">
                                            <label>Description</label>
                                            <textarea name="TT_DESCRIPTION" class="form-control" rows="3"></textarea>
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

                <!-- History Modal -->
                <div class="modal fade" id="modalHistory" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title">Tool Drawing Revision History</h5>
                                    <small class="text-muted">
                                        <div><strong>Product:</strong> <span id="historyProduct"></span></div>
                                        <div><strong>Tool Name:</strong> <span id="historyToolName"></span></div>
                                        <div><strong>Process:</strong> <span id="historyProcess"></span></div>
                                        <div><strong>Tool Drawing No.:</strong> <span id="historyDrawingNo"></span></div>
                                    </small>
                                </div>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table id="tableHistory" class="table table-bordered table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Revision</th>
                                                <th>Status</th>
                                                <th>Effective Date</th>
                                                <th>Modified Date</th>
                                                <th>Modified By</th>
                                            </tr>
                                        </thead>
                                        <tbody id="historyBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Detail Modal -->
                <div class="modal fade" id="modalHistoryDetail" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Revision Detail</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th style="width:140px">Product</th>
                                                <td>: <span id="detailHistProduct"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Tool Name</th>
                                                <td>: <span id="detailHistToolName"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Tool Drawing No.</th>
                                                <td>: <span id="detailHistDrawingNo"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Revision</th>
                                                <td>: <span id="detailHistRevision"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Maker</th>
                                                <td>: <span id="detailHistMaker"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Min Quantity</th>
                                                <td>: <span id="detailHistMinQty"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Replenish Quantity</th>
                                                <td>: <span id="detailHistReplenishQty"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Process</th>
                                                <td>: <span id="detailHistProcess"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Price</th>
                                                <td>: <span id="detailHistPrice"></span></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th style="width:140px">Standard Tool Life</th>
                                                <td>: <span id="detailHistToolLife"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Description</th>
                                                <td>: <span id="detailHistDescription"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>: <span id="detailHistStatus"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Effective Date</th>
                                                <td>: <span id="detailHistEffective"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Material</th>
                                                <td>: <span id="detailHistMaterial"></span></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <hr>
                                <div>
                                    <strong>Drawing File:</strong> <span id="detailHistDrawingFile"></span>
                                    <br><strong>Modified Date:</strong> <span id="detailHistModified"></span>
                                    <br><strong>Modified By:</strong> <span id="detailHistModifiedBy"></span>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
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
                var table = $('#table-tool-draw-tooling').DataTable({
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
                    columnDefs: [{
                            orderable: false,
                            targets: [10]
                        },
                        {
                            width: '100px',
                            targets: 0
                        }, // Tool Drawing No.
                        {
                            width: '110px',
                            targets: 1
                        }, // Tool Name
                        {
                            width: '70px',
                            targets: 2
                        }, // Min Quantity
                        {
                            width: '80px',
                            targets: 3
                        }, // Replenish Quantity
                        {
                            width: '100px',
                            targets: 4
                        }, // Maker
                        {
                            width: '70px',
                            targets: 5
                        }, // Price
                        {
                            width: '120px',
                            targets: 6
                        }, // Description
                        {
                            width: '90px',
                            targets: 7
                        }, // Effective Date
                        {
                            width: '90px',
                            targets: 8
                        }, // Material
                        {
                            width: '80px',
                            targets: 9
                        }, // Standard Tool Life
                        {
                            width: '90px',
                            targets: 10
                        } // ACTION
                    ]
                });

                if (typeof _search_data === 'function') {
                    _search_data(table, '#table-tool-draw-tooling', false, false);
                }

                // Map numeric/status values to human-readable labels
                function mapStatus(val) {
                    if (val === undefined || val === null) return 'Inactive';
                    // if already a string like 'Active'/'Inactive'
                    if (typeof val === 'string') {
                        var s = val.trim().toLowerCase();
                        if (s === 'active' || s === '1') return 'Active';
                        return 'Inactive';
                    }
                    var n = parseInt(val, 10);
                    if (!isNaN(n)) return n === 1 ? 'Active' : 'Inactive';
                    return 'Inactive';
                }

                // New
                $('#btn-new').on('click', function() {
                    $('#formToolDrawing')[0].reset();
                    $('input[name="action"]').val('ADD');
                    $('input[name="TT_ID"]').val('');
                    $('[name="TT_TOOL_ID"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // Edit - now handled by direct link, no popup needed

                // Submit
                $('#btn-submit').on('click', function(e) {
                    e.preventDefault();
                    $('#formToolDrawing').submit();
                });

                $('#formToolDrawing').on('submit', function(e) {
                    e.preventDefault();
                    var toolId = $.trim($('[name="TT_TOOL_ID"]').val());

                    var isValid = true;
                    if (toolId === '' || toolId <= 0) {
                        $('[name="TT_TOOL_ID"]').addClass('is-invalid');
                        isValid = false;
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
                        console.log('✓ Submit response (JSON parsed):', res);
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
                            console.error('✗ Response structure invalid:', res);
                            toastr.error('Response tidak valid dari server');
                        }
                    }).fail(function(xhr, status, error) {
                        console.error('✗ Submit AJAX Error:', {
                            status: status,
                            error: error,
                            statusCode: xhr.status,
                            responseText: xhr.responseText
                        });
                        var msg = 'Terjadi kesalahan pada server';
                        if (status === 'timeout') msg = 'Request timeout (30s)';
                        else if (status === 'error') msg = 'HTTP Error ' + xhr.status;
                        else if (status === 'parsererror') {
                            msg = 'Response bukan JSON valid. Raw response: ' + xhr.responseText.substring(0, 200);
                            console.log('Full response text:', xhr.responseText);
                        }
                        toastr.error(msg);
                    });
                });

                // History - now handled by direct link, no popup needed

                // Delete
                $('#table-tool-draw-tooling').on('click', '.btn-delete', function() {
                    var id = Number($(this).data('id')) || 0;
                    var name = $(this).data('name') || '';
                    if (id <= 0) {
                        toastr.error('ID tidak valid');
                        return;
                    }
                    if (!confirm('Hapus Tool Drawing "' + name + '"?')) return;
                    $.ajax({
                        url: '<?= base_url("tool_engineering/tool_draw_tooling/delete_data"); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            TT_ID: id
                        }
                    }).done(function(res) {
                        if (res && res.success) {
                            toastr.success(res.message || 'Terhapus');
                            setTimeout(function() {
                                location.reload();
                            }, 400);
                        } else {
                            toastr.error(res && res.message ? res.message : 'Gagal menghapus');
                        }
                    }).fail(function() {
                        toastr.error('Terjadi kesalahan');
                    });
                });
            });
        })(jQuery);
    </script>
</body>

</html>