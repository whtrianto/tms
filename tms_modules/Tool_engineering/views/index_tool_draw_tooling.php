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
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Data dari TMS_NEW database
                                        // TMS_TOOL_MASTER_LIST_REV JOIN TMS_TOOL_MASTER_LIST
                                        // JOIN MS_TOOL_CLASS, MS_MAKER, MS_MATERIAL
                                        
                                        $list_data = isset($list_data) ? $list_data : array();

                                        foreach ($list_data as $row):
                                            // MLR_ID dari TMS_TOOL_MASTER_LIST_REV
                                            $row_id = isset($row['MLR_ID']) ? (int)$row['MLR_ID'] : 0;
                                            
                                            // Tool Drawing No. dari TMS_TOOL_MASTER_LIST.ML_TOOL_DRAW_NO
                                            $drawing_no = isset($row['ML_TOOL_DRAW_NO']) ? $row['ML_TOOL_DRAW_NO'] : '';
                                            
                                            // Tool Name dari MS_TOOL_CLASS.TC_NAME (sudah di-join di model)
                                            $tool_name = isset($row['TC_NAME']) ? $row['TC_NAME'] : '';
                                            
                                            // Min Quantity dari MLR_MIN_QTY
                                            $min_qty = isset($row['MLR_MIN_QTY']) ? (int)$row['MLR_MIN_QTY'] : 0;
                                            
                                            // Replenish Quantity dari MLR_REPLENISH_QTY
                                            $replenish_qty = isset($row['MLR_REPLENISH_QTY']) ? (int)$row['MLR_REPLENISH_QTY'] : 0;
                                            
                                            // Maker dari MS_MAKER.MAKER_NAME (sudah di-join di model)
                                            $maker_name = isset($row['MAKER_NAME']) ? $row['MAKER_NAME'] : '';
                                            
                                            // Price dari MLR_PRICE
                                            $price = isset($row['MLR_PRICE']) ? (float)$row['MLR_PRICE'] : 0;
                                            
                                            // Description dari MLR_DESC
                                            $description = isset($row['MLR_DESC']) ? $row['MLR_DESC'] : '';
                                            
                                            // Effective Date dari MLR_EFFECTIVE_DATE
                                            $effective_date = isset($row['MLR_EFFECTIVE_DATE']) ? $row['MLR_EFFECTIVE_DATE'] : '';
                                            
                                            // Material dari MS_MATERIAL.MAT_NAME (sudah di-join di model)
                                            $material_name = isset($row['MAT_NAME']) ? $row['MAT_NAME'] : '';
                                            
                                            // Standard Tool Life dari MLR_STD_TL_LIFE
                                            $tool_life = isset($row['MLR_STD_TL_LIFE']) ? $row['MLR_STD_TL_LIFE'] : '';
                                        ?>
                                            <tr>
                                                <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars($drawing_no, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars($tool_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-center"><?= $min_qty; ?></td>
                                                <td class="text-center"><?= $replenish_qty; ?></td>
                                                <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars($maker_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-right"><?= number_format($price, 2); ?></td>
                                                <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-center"><?= htmlspecialchars($effective_date, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-left"><span class="cell-ellipsis"><?= htmlspecialchars($material_name, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td class="text-center"><?= htmlspecialchars($tool_life, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="<?= base_url('Tool_engineering/tool_draw_tooling/edit_page/' . $row_id); ?>" 
                                                           class="btn btn-secondary btn-sm" title="Edit">Edit</a>
                                                        <a href="<?= base_url('Tool_engineering/tool_draw_tooling/history_page/' . $row_id); ?>" 
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
                    order: [[1, 'asc']],
                    autoWidth: false,
                    scrollX: false,
                    columnDefs: [
                        { orderable: false, targets: [10] },
                        { width: '120px', targets: 0 }, // Tool Drawing No.
                        { width: '120px', targets: 1 }, // Tool Name
                        { width: '80px', targets: 2 },  // Min Quantity
                        { width: '80px', targets: 3 },  // Replenish Quantity
                        { width: '100px', targets: 4 }, // Maker
                        { width: '80px', targets: 5 },  // Price
                        { width: '120px', targets: 6 }, // Description
                        { width: '100px', targets: 7 }, // Effective Date
                        { width: '100px', targets: 8 }, // Material
                        { width: '80px', targets: 9 },  // Standard Tool Life
                        { width: '100px', targets: 10 } // Action
                    ]
                });

                if (typeof _search_data === 'function') {
                    _search_data(table, '#table-tool-draw-tooling', false, false);
                }
            });
        })(jQuery);
    </script>
</body>

</html>
