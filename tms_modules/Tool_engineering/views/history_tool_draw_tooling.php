<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        html, body, #content-wrapper { color: #000; }
        .card, .table, label, .form-text, .dataTables_wrapper { color: #000; }
        .table td, .table th { padding: 0.4rem 0.45rem !important; font-size: 0.88rem; }
        .history-row { cursor: pointer; }
        .history-row:hover { background-color: #f5f5f5; }
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
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="m-0 font-weight-bold text-primary">Tool Drawing Revision History</h4>
                            <small class="text-muted">
                                <div><strong>Product:</strong> <?= htmlspecialchars(isset($drawing['PRODUCT_NAME']) ? $drawing['PRODUCT_NAME'] : ''); ?></div>
                                <div><strong>Tool Name:</strong> <?= htmlspecialchars(isset($drawing['TOOL_NAME']) ? $drawing['TOOL_NAME'] : (isset($drawing['TD_TOOL_NAME']) ? $drawing['TD_TOOL_NAME'] : '')); ?></div>
                                <div><strong>Process:</strong> <?= htmlspecialchars(isset($drawing['OPERATION_NAME']) ? $drawing['OPERATION_NAME'] : ''); ?></div>
                                <div><strong>Tool Drawing No.:</strong> <?= htmlspecialchars(isset($drawing['TD_DRAWING_NO']) ? $drawing['TD_DRAWING_NO'] : ''); ?></div>
                            </small>
                        </div>
                        <div>
                            <a href="<?= base_url('Tool_engineering/tool_draw_tooling'); ?>" class="btn btn-sm btn-outline-primary shadow-sm">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tableHistory" class="table table-bordered table-striped table-sm w-100">
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
                                <tbody>
                                    <?php 
                                    <?php 
                                    $historyIndex = 0;
                                    foreach ($history as $h): 
                                        // Map status
                                        $statusVal = isset($h['TD_STATUS']) ? $h['TD_STATUS'] : (isset($h['TT_STATUS']) ? $h['TT_STATUS'] : 0);
                                        $status = 'Inactive';
                                        if (is_string($statusVal)) {
                                            $s = trim(strtolower($statusVal));
                                            if ($s === 'active' || $s === '1') $status = 'Active';
                                        } else {
                                            $n = (int)$statusVal;
                                            if ($n === 1) $status = 'Active';
                                        }
                                    ?>
                                        <tr class="history-row" data-index="<?= $historyIndex; ?>">
                                            <td><?= htmlspecialchars(isset($h['TD_ID']) ? $h['TD_ID'] : (isset($h['TT_ID']) ? $h['TT_ID'] : '')); ?></td>
                                            <td><?= htmlspecialchars(isset($h['TD_REVISION']) ? $h['TD_REVISION'] : (isset($h['TT_REVISION']) ? $h['TT_REVISION'] : 0)); ?></td>
                                            <td><?= htmlspecialchars($status); ?></td>
                                            <td><?= htmlspecialchars(isset($h['TD_EFFECTIVE_DATE']) ? $h['TD_EFFECTIVE_DATE'] : (isset($h['TT_EFFECTIVE_DATE']) ? $h['TT_EFFECTIVE_DATE'] : '')); ?></td>
                                            <td><?= htmlspecialchars(isset($h['TD_MODIFIED_DATE']) ? $h['TD_MODIFIED_DATE'] : (isset($h['TT_MODIFIED_DATE']) ? $h['TT_MODIFIED_DATE'] : '')); ?></td>
                                            <td><?= htmlspecialchars(isset($h['TD_MODIFIED_BY']) ? $h['TD_MODIFIED_BY'] : (isset($h['TT_MODIFIED_BY']) ? $h['TT_MODIFIED_BY'] : '')); ?></td>
                                        </tr>
                                    <?php 
                                        $historyIndex++;
                                    endforeach; ?>
                                </tbody>
                            </table>
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
(function($){
    $(function(){
        var historyData = <?= json_encode($history); ?>;
        
        $('#tableHistory').DataTable({
            lengthMenu: [[10,25,50,-1],[10,25,50,"ALL"]],
            pageLength: 25,
            order: [[1,'desc']],
            autoWidth: false
        });

        // History row click - show detail
        $(document).on('click', '.history-row', function(e) {
            e.stopPropagation();
            var row = $(this);
            var idx = parseInt(row.data('index'));
            if (isNaN(idx) || !historyData || !historyData[idx]) return;

            var h = historyData[idx];

            // Populate detail modal with full information
            $('#detailHistProduct').text(h.PRODUCT_NAME || '');
            $('#detailHistToolName').text(h.TOOL_NAME || h.TD_TOOL_NAME || '');
            $('#detailHistDrawingNo').text(h.TD_DRAWING_NO || '');
            $('#detailHistRevision').text((typeof h.TD_REVISION !== 'undefined' && h.TD_REVISION !== null) ? h.TD_REVISION : (typeof h.TT_REVISION !== 'undefined' && h.TT_REVISION !== null) ? h.TT_REVISION : '');

            // Maker: prefer MAKER_NAME when available
            $('#detailHistMaker').text((typeof h.MAKER_NAME !== 'undefined' && h.MAKER_NAME !== null && h.MAKER_NAME !== '') ? h.MAKER_NAME : '');

            // Min Quantity: prefer TD_MIN_QTY, fallback TT_MIN_QTY, MIN_QTY, default to 0
            var minQtyVal = 0;
            if (typeof h.TD_MIN_QTY !== 'undefined' && h.TD_MIN_QTY !== null && h.TD_MIN_QTY !== '') {
                minQtyVal = parseInt(h.TD_MIN_QTY, 10) || 0;
            } else if (typeof h.TT_MIN_QTY !== 'undefined' && h.TT_MIN_QTY !== null && h.TT_MIN_QTY !== '') {
                minQtyVal = parseInt(h.TT_MIN_QTY, 10) || 0;
            } else if (typeof h.MIN_QTY !== 'undefined' && h.MIN_QTY !== null && h.MIN_QTY !== '') {
                minQtyVal = parseInt(h.MIN_QTY, 10) || 0;
            }
            $('#detailHistMinQty').text(minQtyVal);

            // Replenish Quantity: prefer TD_REPLENISH_QTY, fallback TT_REPLENISH_QTY, REPLENISH_QTY, default to 0
            var replenishQtyVal = 0;
            if (typeof h.TD_REPLENISH_QTY !== 'undefined' && h.TD_REPLENISH_QTY !== null && h.TD_REPLENISH_QTY !== '') {
                replenishQtyVal = parseInt(h.TD_REPLENISH_QTY, 10) || 0;
            } else if (typeof h.TT_REPLENISH_QTY !== 'undefined' && h.TT_REPLENISH_QTY !== null && h.TT_REPLENISH_QTY !== '') {
                replenishQtyVal = parseInt(h.TT_REPLENISH_QTY, 10) || 0;
            } else if (typeof h.REPLENISH_QTY !== 'undefined' && h.REPLENISH_QTY !== null && h.REPLENISH_QTY !== '') {
                replenishQtyVal = parseInt(h.REPLENISH_QTY, 10) || 0;
            }
            $('#detailHistReplenishQty').text(replenishQtyVal);

            $('#detailHistProcess').text(h.OPERATION_NAME || '');

            // Price: prefer TD_PRICE, fallback TT_PRICE, PRICE, default to 0.00
            var priceVal = 0.0;
            if (typeof h.TD_PRICE !== 'undefined' && h.TD_PRICE !== null && h.TD_PRICE !== '') {
                priceVal = parseFloat(h.TD_PRICE) || 0.0;
            } else if (typeof h.TT_PRICE !== 'undefined' && h.TT_PRICE !== null && h.TT_PRICE !== '') {
                priceVal = parseFloat(h.TT_PRICE) || 0.0;
            } else if (typeof h.PRICE !== 'undefined' && h.PRICE !== null && h.PRICE !== '') {
                priceVal = parseFloat(h.PRICE) || 0.0;
            }
            $('#detailHistPrice').text(isNaN(priceVal) ? '0.00' : parseFloat(priceVal).toFixed(2));

            // Tool Life: prefer TD_TOOL_LIFE, fallback TT_TOOL_LIFE, TOOL_LIFE, default to 0
            var toolLifeVal = 0;
            if (typeof h.TD_TOOL_LIFE !== 'undefined' && h.TD_TOOL_LIFE !== null && h.TD_TOOL_LIFE !== '') {
                toolLifeVal = parseInt(h.TD_TOOL_LIFE, 10) || 0;
            } else if (typeof h.TT_TOOL_LIFE !== 'undefined' && h.TT_TOOL_LIFE !== null && h.TT_TOOL_LIFE !== '') {
                toolLifeVal = parseInt(h.TT_TOOL_LIFE, 10) || 0;
            } else if (typeof h.TOOL_LIFE !== 'undefined' && h.TOOL_LIFE !== null && h.TOOL_LIFE !== '') {
                toolLifeVal = parseInt(h.TOOL_LIFE, 10) || 0;
            }
            $('#detailHistToolLife').text(toolLifeVal);

            // Description: prefer TD_DESCRIPTION, fallback TT_DESCRIPTION, DESCRIPTION, default to ''
            var descVal = '';
            if (typeof h.TD_DESCRIPTION !== 'undefined' && h.TD_DESCRIPTION !== null && h.TD_DESCRIPTION !== '') {
                descVal = String(h.TD_DESCRIPTION);
            } else if (typeof h.TT_DESCRIPTION !== 'undefined' && h.TT_DESCRIPTION !== null && h.TT_DESCRIPTION !== '') {
                descVal = String(h.TT_DESCRIPTION);
            } else if (typeof h.DESCRIPTION !== 'undefined' && h.DESCRIPTION !== null && h.DESCRIPTION !== '') {
                descVal = String(h.DESCRIPTION);
            }
            $('#detailHistDescription').text(descVal);

            function mapStatus(val) {
                if (val === undefined || val === null) return 'Inactive';
                if (typeof val === 'string') {
                    var s = val.trim().toLowerCase();
                    if (s === 'active' || s === '1') return 'Active';
                    return 'Inactive';
                }
                var n = parseInt(val, 10);
                return n === 1 ? 'Active' : 'Inactive';
            }
            
            $('#detailHistStatus').text(mapStatus(h.TD_STATUS || h.TT_STATUS));
            $('#detailHistEffective').text(h.TD_EFFECTIVE_DATE || h.TT_EFFECTIVE_DATE || '');
            $('#detailHistMaterial').text(h.MATERIAL_NAME || '');
            $('#detailHistDrawingFile').text(h.TD_DRAWING_NO || '');
            $('#detailHistModified').text(h.TD_MODIFIED_DATE || h.TT_MODIFIED_DATE || '');
            $('#detailHistModifiedBy').text(h.TD_MODIFIED_BY || h.TT_MODIFIED_BY || '');

            $('#modalHistoryDetail').modal('show');
        });
    });
})(jQuery);
</script>
</body>
</html>

