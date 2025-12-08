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

        /* improved table layout: fixed widths with ellipsis and optional horizontal scrolling */
        .table-fixed {
            table-layout: fixed;
        }

        .table-fixed th, .table-fixed td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cell-ellipsis {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            max-width: 100%;
        }

        .label-required::after {
            content: " *";
            color: #dc3545;
            font-weight: 600;
        }

        .is-invalid+.invalid-feedback {
            display: block;
        }

        /* Compact button styling for table action buttons */
        .table .btn-sm {
            padding: 0.25rem 0.4rem;
            font-size: 0.7rem;
        }

        /* Tight action button layout */
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
                            <h4 class="m-0 font-weight-bold text-primary">Tool Drawing (Engineering)</h4>
                            <button id="btn-new" class="btn btn-primary">New Tool Drawing</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                            <table id="table-tool-draw-engin" class="table table-bordered table-striped table-fixed w-100 text-center">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product</th>
                                        <th>Process</th>
                                        <th>Drawing No</th>
                                        <th>Tool Name</th>
                                        <th>Revision</th>
                                        <th>Status</th>
                                        <th>Effective Date</th>
                                        <th>Modified Date</th>
                                        <th>Modified By</th>
                                        <th>ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($list_data as $row): 
                                        $product_name = '';
                                        foreach ($products as $p) {
                                            if ($p['PRODUCT_ID'] == $row['TD_PRODUCT_ID']) {
                                                $product_name = $p['PRODUCT_NAME'];
                                                break;
                                            }
                                        }
                                        
                                        $operation_name = '';
                                        foreach ($operations as $o) {
                                            if ($o['OPERATION_ID'] == $row['TD_PROCESS_ID']) {
                                                $operation_name = $o['OPERATION_NAME'];
                                                break;
                                            }
                                        }

                                        $tool_name = '';
                                        // try numeric TOOL_ID stored in TD_TOOL_NAME
                                        if (isset($row['TD_TOOL_NAME']) && is_numeric($row['TD_TOOL_NAME'])) {
                                            foreach ($tools as $t) {
                                                if ((int)$t['TOOL_ID'] == (int)$row['TD_TOOL_NAME']) {
                                                    $tool_name = $t['TOOL_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                        // fallback: match by name
                                        if ($tool_name === '' && isset($row['TD_TOOL_NAME'])) {
                                            foreach ($tools as $t) {
                                                if (strcasecmp(trim($t['TOOL_NAME']), trim($row['TD_TOOL_NAME'])) === 0) {
                                                    $tool_name = $t['TOOL_NAME'];
                                                    break;
                                                }
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td class="text-center"><?= (int)$row['TD_ID']; ?></td>
                                            <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars($operation_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td class="text-left">
                                                <?php if (!empty($row['TD_DRAWING_NO'])):
                                                    $fileUrl = base_url('tool_engineering/img/' . $row['TD_DRAWING_NO']);
                                                    $imgSrc = $fileUrl; ?>
                                                    <div style="display:flex; align-items:center; gap:8px;">
                                                        <!-- <a href="#" class="drawing-thumb-link" data-id="<?= (int)$row['TD_ID']; ?>" title="View details">
                                                            <img src="<?= $imgSrc; ?>" alt="drawing" class="drawing-thumb" style="width:64px; height:48px; object-fit:cover; border:1px solid #ddd;">
                                                        </a> -->
                                                            <div>
                                                                <?php
                                                                    // hide common image extension (e.g. .jpg) in UI display
                                                                    $display_name = preg_replace('/\.(jpe?g|png|gif|bmp|svg|webp)$/i', '', $row['TD_DRAWING_NO']);
                                                                ?>
                                                                <a href="<?= $fileUrl; ?>" class="drawing-name-link" data-id="<?= (int)$row['TD_ID']; ?>">
                                                                    <span class="cell-ellipsis" style="display:inline-block; max-width:180px; vertical-align:middle;"><?= htmlspecialchars($display_name, ENT_QUOTES, 'UTF-8'); ?></span>
                                                                </a>
                                                            </div>
                                                    </div>
                                                <?php else: ?>
                                                    &nbsp;
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars($tool_name !== '' ? $tool_name : (isset($row['TD_TOOL_NAME']) ? $row['TD_TOOL_NAME'] : ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td class="text-center"><?= (int)$row['TD_REVISION']; ?></td>
                                            <td>
                                                <?php if ($row['TD_STATUS'] == 1): ?>
                                                    <span class="badge badge-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="cell-ellipsis"><?= htmlspecialchars(isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><span class="cell-ellipsis"><?= htmlspecialchars(isset($row['TD_MODIFIED_DATE']) ? $row['TD_MODIFIED_DATE'] : '', ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><span class="cell-ellipsis"><?= htmlspecialchars((isset($row['TD_MODIFIED_BY']) && $row['TD_MODIFIED_BY'] !== '') ? $row['TD_MODIFIED_BY'] : (isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : ''), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn btn-secondary btn-sm btn-edit"
                                                        data-edit='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>Edit</button>
                                                    <button class="btn btn-info btn-sm btn-revision"
                                                        data-edit='<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>'>Rev</button>
                                                    <button class="btn btn-warning btn-sm btn-history"
                                                        data-id="<?= (int)$row['TD_ID']; ?>"
                                                        data-name="<?= htmlspecialchars($row['TD_DRAWING_NO'], ENT_QUOTES, 'UTF-8'); ?>">Hist</button>
                                                    <button class="btn btn-danger btn-sm btn-delete"
                                                        data-id="<?= (int)$row['TD_ID']; ?>"
                                                        data-name="<?= htmlspecialchars($row['TD_DRAWING_NO'], ENT_QUOTES, 'UTF-8'); ?>">Del</button>
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

                    <!-- Detail modal for drawing (opened when clicking thumbnail) -->
                    <div class="modal fade" id="modalDetailDrawing" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Drawing Detail</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <div id="drawingDetailContent">
                                        <div style="display:flex; gap:16px; align-items:flex-start;">
                                            <div id="drawingDetailImage" style="flex:0 0 220px;"></div>
                                            <div style="flex:1 1 auto;">
                                                <table class="table table-bordered table-sm">
                                                    <tr><th style="width:160px">Product</th><td id="detailProduct"></td></tr>
                                                    <tr><th>Process</th><td id="detailProcess"></td></tr>
                                                    <tr><th>Tool</th><td id="detailTool"></td></tr>
                                                    <tr><th>Filename</th><td id="detailFilename"></td></tr>
                                                    <tr><th>Revision</th><td id="detailRevision"></td></tr>
                                                    <tr><th>Status</th><td id="detailStatus"></td></tr>
                                                    <tr><th>Effective Date</th><td id="detailEffective"></td></tr>
                                                    <tr><th>Modified Date</th><td id="detailModified"></td></tr>
                                                    <tr><th>Modified By</th><td id="detailModifiedBy"></td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Modal -->
                <div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Tool Drawing Form</h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                    <form id="formToolDrawing" method="post" action="<?= base_url('Tool_engineering/tool_draw_engin/submit_data'); ?>" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="">
                                    <input type="hidden" name="TD_ID" value="">
                                        <input type="hidden" name="TD_DRAWING_NO_OLD" value="">

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="label-required">Product</label>
                                            <select name="TD_PRODUCT_ID" class="form-control" required>
                                                <option value="">-- Select Product --</option>
                                                <?php foreach ($products as $p): ?>
                                                    <option value="<?= (int)$p['PRODUCT_ID']; ?>"><?= htmlspecialchars($p['PRODUCT_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">Product wajib dipilih.</div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label class="label-required">Process</label>
                                            <select name="TD_PROCESS_ID" class="form-control" required>
                                                <option value="">-- Select Process --</option>
                                                <?php foreach ($operations as $o): ?>
                                                    <option value="<?= (int)$o['OPERATION_ID']; ?>"><?= htmlspecialchars($o['OPERATION_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="invalid-feedback">Process wajib dipilih.</div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label class="label-required">Drawing (upload image)</label>
                                            <input type="file" name="TD_DRAWING_FILE" class="form-control" accept="image/*">
                                            <small class="form-text text-muted">Upload file gambar untuk drawing. Jika tidak memilih file saat edit, gambar lama akan dipertahankan.</small>
                                            <div class="invalid-feedback">Drawing wajib diisi (upload gambar).</div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Tool</label>
                                            <select name="TD_TOOL_NAME" class="form-control">
                                                <option value="">-- Select Tool --</option>
                                                <?php foreach ($tools as $t): ?>
                                                    <option value="<?= (int)$t['TOOL_ID']; ?>"><?= htmlspecialchars($t['TOOL_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>Revision</label>
                                            <input type="number" name="TD_REVISION" class="form-control" value="0">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Status</label>
                                            <select name="TD_STATUS" class="form-control">
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Material ID</label>
                                            <select name="TD_MATERIAL_ID" class="form-control">
                                                <option value="">-- Select Material --</option>
                                                <?php foreach ($materials as $m): ?>
                                                    <option value="<?= (int)$m['MATERIAL_ID']; ?>"><?= htmlspecialchars($m['MATERIAL_NAME'], ENT_QUOTES, 'UTF-8'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
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
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Revision History - <span id="historyTitle"></span></h5>
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
                                                <th>Action</th>
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
                                        <table class="table table-bordered table-sm">
                                            <tr><th style="width:140px">Product</th><td id="detailHistProduct"></td></tr>
                                            <tr><th>Process</th><td id="detailHistProcess"></td></tr>
                                            <tr><th>Tool</th><td id="detailHistTool"></td></tr>
                                            <tr><th>Drawing</th><td id="detailHistDrawing"></td></tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-bordered table-sm">
                                            <tr><th style="width:140px">Revision</th><td id="detailHistRevision"></td></tr>
                                            <tr><th>Status</th><td id="detailHistStatus"></td></tr>
                                            <tr><th>Material</th><td id="detailHistMaterial"></td></tr>
                                            <tr><th>Modified By</th><td id="detailHistModifiedBy"></td></tr>
                                        </table>
                                    </div>
                                </div>
                                <hr>
                                <div>
                                    <strong>Dates:</strong>
                                    <br>Effective Date: <span id="detailHistEffective"></span>
                                    <br>Modified Date: <span id="detailHistModified"></span>
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
                var table = $('#table-tool-draw-engin').DataTable({
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
                        { width: '40px', targets: 0 },      // ID
                        { width: '90px', targets: 1 },      // Product
                        { width: '60px', targets: 2 },      // Process
                        { width: '100px', targets: 3 },     // Drawing No
                        { width: '80px', targets: 4 },      // Tool Name
                        { width: '50px', targets: 5 },      // Revision
                        { width: '70px', targets: 6 },      // Status
                        { width: '95px', targets: 7 },      // Effective Date
                        { width: '95px', targets: 8 },      // Modified Date
                        { width: '75px', targets: 9 },      // Modified By
                        { width: '115px', targets: 10 }     // ACTION
                    ]
                });

                if (typeof _search_data === 'function') {
                    _search_data(table, '#table-tool-draw-engin', false, false);
                }

                // history cache for loaded records
                var historyCache = {};
                var currentHistoryId = null;

                // New
                $('#btn-new').on('click', function() {
                    $('#formToolDrawing')[0].reset();
                    $('input[name="action"]').val('ADD');
                    $('input[name="TD_ID"]').val('');
                    $('[name="TD_PRODUCT_ID"]').removeClass('is-invalid');
                    $('[name="TD_PROCESS_ID"]').removeClass('is-invalid');
                    $('[name="TD_DRAWING_NO"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // Edit
                $('#table-tool-draw-engin').on('click', '.btn-edit', function() {
                    var raw = $(this).data('edit');
                    var d = raw;
                    if (!d) {
                        toastr.error('Data edit tidak valid.');
                        return;
                    }
                    $('#formToolDrawing')[0].reset();
                    $('input[name="action"]').val('EDIT');
                    $('input[name="TD_ID"]').val(d.TD_ID);
                    $('[name="TD_PRODUCT_ID"]').val(d.TD_PRODUCT_ID || '');
                    $('[name="TD_PROCESS_ID"]').val(d.TD_PROCESS_ID || '');
                    // keep reference to existing uploaded drawing filename
                    $('[name="TD_DRAWING_NO_OLD"]').val(d.TD_DRAWING_NO || '');
                    // Ensure the tool select displays the previously saved selection.
                    try {
                        var $toolSel = $('[name="TD_TOOL_NAME"]');
                        var toolIdVal = d.TD_TOOL_ID || '';
                        if (toolIdVal) {
                            if ($toolSel.find('option[value="' + toolIdVal + '"]').length === 0) {
                                // fallback label from payload
                                var toolLabel = (d.TOOL_NAME && String(d.TOOL_NAME).trim() !== '') ? d.TOOL_NAME : ((d.TD_TOOL_NAME && String(d.TD_TOOL_NAME).trim() !== '') ? d.TD_TOOL_NAME : ('Tool #' + toolIdVal));
                                $toolSel.append($('<option>', { value: toolIdVal, text: toolLabel }));
                            }
                            $toolSel.val(toolIdVal);
                        } else {
                            // no numeric ID — try to match by visible text
                            var toolName = (d.TOOL_NAME && String(d.TOOL_NAME).trim() !== '') ? d.TOOL_NAME : ((d.TD_TOOL_NAME && String(d.TD_TOOL_NAME).trim() !== '') ? d.TD_TOOL_NAME : '');
                            if (toolName) {
                                var foundVal = null;
                                $toolSel.find('option').each(function() {
                                    if (String($.trim($(this).text())).toLowerCase() === String(toolName).toLowerCase()) {
                                        foundVal = $(this).val();
                                        return false;
                                    }
                                });
                                if (foundVal) {
                                    $toolSel.val(foundVal);
                                } else {
                                    var safeVal = 'custom_tool_' + String(Math.random()).slice(2,8);
                                    $toolSel.append($('<option>', { value: safeVal, text: toolName }));
                                    $toolSel.val(safeVal);
                                }
                            } else {
                                $toolSel.val('');
                            }
                        }
                    } catch (e) {
                        console.warn('Could not ensure tool select option (engin edit):', e);
                        $('[name="TD_TOOL_NAME"]').val(d.TD_TOOL_ID || d.TD_TOOL_NAME || '');
                    }
                    $('[name="TD_REVISION"]').val(d.TD_REVISION || 0);
                    $('[name="TD_STATUS"]').val(d.TD_STATUS || 0);
                    // Use empty string as default so the "-- Select Material --" option matches
                    $('[name="TD_MATERIAL_ID"]').val(d.TD_MATERIAL_ID || '');
                    $('[name="TD_PRODUCT_ID"]').removeClass('is-invalid');
                    $('[name="TD_PROCESS_ID"]').removeClass('is-invalid');
                    $('[name="TD_DRAWING_NO"]').removeClass('is-invalid');
                    $('#modalForm').modal('show');
                });

                // Revision (same as Edit but TD_REVISION is auto-incremented by 1)
                $('#table-tool-draw-engin').on('click', '.btn-revision', function() {
                    var raw = $(this).data('edit');
                    var d = raw;
                    if (!d) {
                        toastr.error('Data revision tidak valid.');
                        return;
                    }
                    $('#formToolDrawing')[0].reset();
                    $('input[name="action"]').val('REVISION');
                    $('input[name="TD_ID"]').val(d.TD_ID);
                    $('[name="TD_PRODUCT_ID"]').val(d.TD_PRODUCT_ID || '');
                    $('[name="TD_PROCESS_ID"]').val(d.TD_PROCESS_ID || '');
                    // keep reference to existing uploaded drawing filename
                    $('[name="TD_DRAWING_NO_OLD"]').val(d.TD_DRAWING_NO || '');
                    // same logic for revision action: preserve previous tool selection
                    try {
                        var $toolSel2 = $('[name="TD_TOOL_NAME"]');
                        var toolIdVal2 = d.TD_TOOL_ID || '';
                        if (toolIdVal2) {
                            if ($toolSel2.find('option[value="' + toolIdVal2 + '"]').length === 0) {
                                var toolLabel2 = (d.TOOL_NAME && String(d.TOOL_NAME).trim() !== '') ? d.TOOL_NAME : ((d.TD_TOOL_NAME && String(d.TD_TOOL_NAME).trim() !== '') ? d.TD_TOOL_NAME : ('Tool #' + toolIdVal2));
                                $toolSel2.append($('<option>', { value: toolIdVal2, text: toolLabel2 }));
                            }
                            $toolSel2.val(toolIdVal2);
                        } else {
                            var toolName2 = (d.TOOL_NAME && String(d.TOOL_NAME).trim() !== '') ? d.TOOL_NAME : ((d.TD_TOOL_NAME && String(d.TD_TOOL_NAME).trim() !== '') ? d.TD_TOOL_NAME : '');
                            if (toolName2) {
                                var foundVal2 = null;
                                $toolSel2.find('option').each(function() {
                                    if (String($.trim($(this).text())).toLowerCase() === String(toolName2).toLowerCase()) {
                                        foundVal2 = $(this).val();
                                        return false;
                                    }
                                });
                                if (foundVal2) {
                                    $toolSel2.val(foundVal2);
                                } else {
                                    var safeVal2 = 'custom_tool_' + String(Math.random()).slice(2,8);
                                    $toolSel2.append($('<option>', { value: safeVal2, text: toolName2 }));
                                    $toolSel2.val(safeVal2);
                                }
                            } else {
                                $toolSel2.val('');
                            }
                        }
                    } catch (e) {
                        console.warn('Could not ensure tool select option (engin revision):', e);
                        $('[name="TD_TOOL_NAME"]').val(d.TD_TOOL_ID || d.TD_TOOL_NAME || '');
                    }
                    // Increment revision by 1
                    var currentRev = (d.TD_REVISION || 0);
                    $('[name="TD_REVISION"]').val(parseInt(currentRev) + 1);
                    $('[name="TD_STATUS"]').val(d.TD_STATUS || 0);
                    // Use empty string as default so the "-- Select Material --" option matches
                    $('[name="TD_MATERIAL_ID"]').val(d.TD_MATERIAL_ID || '');
                    $('[name="TD_PRODUCT_ID"]').removeClass('is-invalid');
                    $('[name="TD_PROCESS_ID"]').removeClass('is-invalid');
                    $('[name="TD_DRAWING_NO"]').removeClass('is-invalid');
                    // Optionally disable file input for Revision (user can still change if needed)
                    $('#modalForm').modal('show');
                });

                // Submit
                $('#btn-submit').on('click', function(e) {
                    e.preventDefault();
                    $('#formToolDrawing').submit();
                });

                $('#formToolDrawing').on('submit', function(e) {
                    e.preventDefault();
                    var productId = $.trim($('[name="TD_PRODUCT_ID"]').val());
                    var processId = $.trim($('[name="TD_PROCESS_ID"]').val());
                    var fileInput = $('[name="TD_DRAWING_FILE"]')[0];
                    var hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                    var oldDrawing = $.trim($('[name="TD_DRAWING_NO_OLD"]').val());
                    
                    var isValid = true;
                    if (productId === '' || productId <= 0) {
                        $('[name="TD_PRODUCT_ID"]').addClass('is-invalid');
                        isValid = false;
                    }
                    if (processId === '' || processId <= 0) {
                        $('[name="TD_PROCESS_ID"]').addClass('is-invalid');
                        isValid = false;
                    }
                    if (!hasFile && oldDrawing === '') {
                        $('[name="TD_DRAWING_FILE"]').addClass('is-invalid');
                        isValid = false;
                    } else {
                        $('[name="TD_DRAWING_FILE"]').removeClass('is-invalid');
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
                        // Ensure res is a valid object with boolean success
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
                        console.error('✗ Submit AJAX Error:', {status: status, error: error, statusCode: xhr.status, responseText: xhr.responseText});
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

                // History button - show revision history for this record
                $('#table-tool-draw-engin').on('click', '.btn-history', function() {
                    var id = Number($(this).data('id')) || 0;
                    var name = $(this).data('name') || '';
                    if (id <= 0) {
                        toastr.error('ID tidak valid');
                        return;
                    }

                    currentHistoryId = id;
                    // show ID only to avoid filename/special-char issues
                    $('#historyTitle').text('ID: ' + id);
                    $('#historyBody').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');

                    $.ajax({
                        url: '<?= base_url("tool_engineering/tool_draw_engin/get_history_by_id"); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: { TD_ID: id }
                    }).done(function(res) {
                        if (res && res.success && res.data && res.data.length > 0) {
                            historyCache[id] = res.data;
                            var html = '';
                            res.data.forEach(function(h, idx) {
                                var status_badge = (h.TD_STATUS == 1) ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>';
                                html += '<tr style="cursor:pointer;" class="history-row" data-index="' + idx + '">';
                                html += '<td>' + (h.TD_ID || '') + '</td>';
                                html += '<td><strong>' + (h.TD_REVISION || '') + '</strong></td>';
                                html += '<td>' + status_badge + '</td>';
                                html += '<td>' + (h.TD_EFFECTIVE_DATE || '') + '</td>';
                                html += '<td>' + (h.TD_MODIFIED_DATE || '') + '</td>';
                                html += '<td>' + (h.TD_MODIFIED_BY || '') + '</td>';
                                html += '<td><button class="btn btn-sm btn-primary btn-view-detail">View Detail</button></td>';
                                html += '</tr>';
                            });
                            $('#historyBody').html(html);
                        } else {
                            $('#historyBody').html('<tr><td colspan="7" class="text-center text-muted">Tidak ada history</td></tr>');
                        }
                    }).fail(function(xhr, status, err) {
                        $('#historyBody').html('<tr><td colspan="7" class="text-center text-danger">Gagal memuat history</td></tr>');
                        console.error('History load failed:', status, err, xhr && xhr.responseText);
                        toastr.error('Gagal memuat history');
                    });

                    $('#modalHistory').modal('show');
                });

                // History row click - show detail
                $(document).on('click', '.history-row, .btn-view-detail', function(e) {
                    e.stopPropagation();
                    var row = $(this).closest('.history-row');
                    var idx = Number(row.data('index'));
                    if (isNaN(idx)) return;
                    if (!currentHistoryId || !historyCache[currentHistoryId]) return;

                    var h = historyCache[currentHistoryId][idx];
                    if (!h) return;

                    // Populate detail modal
                    $('#detailHistProduct').text(h.PRODUCT_NAME || h.TD_PRODUCT_ID || '');
                    $('#detailHistProcess').text(h.OPERATION_NAME || h.TD_PROCESS_ID || '');
                    $('#detailHistTool').text(h.TOOL_RESOLVED_NAME || h.TD_TOOL_NAME || '');
                    $('#detailHistDrawing').text(h.TD_DRAWING_NO || '');
                    $('#detailHistRevision').text(h.TD_REVISION || 0);
                    $('#detailHistStatus').text(h.TD_STATUS == 1 ? 'Active' : 'Inactive');
                    // Prefer resolved material name from history payload, fallback to material id
                    $('#detailHistMaterial').text(h.MATERIAL_NAME || h.TD_MATERIAL_ID || '');
                    $('#detailHistModifiedBy').text(h.TD_MODIFIED_BY || '');
                    $('#detailHistEffective').text(h.TD_EFFECTIVE_DATE || '');
                    $('#detailHistModified').text(h.TD_MODIFIED_DATE || '');

                    $('#modalHistoryDetail').modal('show');
                });

                // Delete
                $('#table-tool-draw-engin').on('click', '.btn-delete', function() {
                    var id = Number($(this).data('id')) || 0;
                    var name = $(this).data('name') || '';
                    if (id <= 0) {
                        toastr.error('ID tidak valid');
                        return;
                    }
                    if (!confirm('Hapus Tool Drawing "' + name + '"?')) return;
                    $.ajax({
                        url: '<?= base_url("tool_engineering/tool_draw_engin/delete_data"); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            TD_ID: id
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

                // Click thumbnail or filename -> show detail modal (product/process/tool info)
                $('#table-tool-draw-engin').on('click', '.drawing-thumb-link, .drawing-name-link', function(e) {
                    e.preventDefault();
                    var id = Number($(this).data('id')) || 0;
                    if (id <= 0) return;
                    var url = '<?= base_url("tool_engineering/tool_draw_engin/get_tool_draw_engin_detail"); ?>';
                    $.post(url, { TD_ID: id }, function(res) {
                        if (res && res.success && res.data) {
                            var d = res.data;
                            // image
                            var imgHtml = '';
                            if (d.TD_DRAWING_NO) {
                                var imgUrl = '<?= base_url("tool_engineering/img/"); ?>' + d.TD_DRAWING_NO;
                                imgHtml = '<a href="' + imgUrl + '" target="_blank"><img src="' + imgUrl + '" style="max-width:100%; height:auto; border:1px solid #ddd;"></a>';
                            }
                            $('#drawingDetailImage').html(imgHtml);
                            $('#detailProduct').text(d.TD_PRODUCT_NAME || d.TD_PRODUCT_ID || '');
                            $('#detailProcess').text(d.TD_OPERATION_NAME || d.TD_PROCESS_ID || '');
                            $('#detailTool').text(d.TD_TOOL_RESOLVED_NAME || d.TD_TOOL_NAME || '');
                            if (d.TD_DRAWING_NO) {
                                var fileUrl = '<?= base_url("tool_engineering/img/"); ?>' + d.TD_DRAWING_NO;
                                var displayName = d.TD_DRAWING_NO.replace(/\.(jpe?g|png|gif|bmp|svg|webp)$/i, '');
                                $('#detailFilename').html('<a href="' + fileUrl + '" download="' + d.TD_DRAWING_NO + '">' + (displayName || d.TD_DRAWING_NO) + '</a>');
                            } else {
                                $('#detailFilename').text('');
                            }
                            $('#detailRevision').text(d.TD_REVISION || '');
                            $('#detailStatus').text(d.TD_STATUS == 1 ? 'Active' : 'Inactive');
                            $('#detailEffective').text(d.TD_EFFECTIVE_DATE || '');
                            $('#detailModified').text(d.TD_MODIFIED_DATE || '');
                            $('#detailModifiedBy').text(d.TD_MODIFIED_BY || '');
                            $('#modalDetailDrawing').modal('show');
                        } else {
                            toastr.error(res && res.message ? res.message : 'Tidak dapat mengambil detail');
                        }
                    }, 'json').fail(function() {
                        toastr.error('Terjadi kesalahan saat mengambil detail');
                    });
                });

            });
        })(jQuery);
    </script>
</body>

</html>

