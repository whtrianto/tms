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

/* Ensure content takes full height and does not overlap footer on resize/sidebar toggle */
#content-wrapper {
    min-height: 100vh;
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
                        <a href="<?= base_url('Tool_engineering/tool_draw_engin/add_page'); ?>" class="btn btn-primary">New Tool Drawing</a>
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
                                                    <a href="<?= base_url('Tool_engineering/tool_draw_engin/edit_page/' . (int)$row['TD_ID']); ?>" 
                                                       class="btn btn-secondary btn-sm" title="Edit">Edit</a>
                                                    <a href="<?= base_url('Tool_engineering/tool_draw_engin/revision_page/' . (int)$row['TD_ID']); ?>" 
                                                       class="btn btn-info btn-sm" title="Revision">Rev</a>
                                                    <a href="<?= base_url('Tool_engineering/tool_draw_engin/history_page/' . (int)$row['TD_ID']); ?>" 
                                                       class="btn btn-warning btn-sm" title="History">Hist</a>
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
                    scrollX: true, // allow horizontal scroll to avoid layout overlap with footer/sidebar
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

