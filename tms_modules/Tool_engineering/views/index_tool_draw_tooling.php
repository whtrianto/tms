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

        #table-tool-draw-tooling {
            min-width: 1200px !important;
        }

        #table-tool-draw-tooling th,
        #table-tool-draw-tooling td {
            white-space: nowrap;
        }

        .cell-ellipsis {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            max-width: 140px;
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

        .search-row th {
            padding: 0.25rem !important;
        }

        .search-row input {
            width: 100%;
            font-size: 0.75rem;
        }

        .dataTables_wrapper {
            padding-bottom: 2rem !important;
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
                                <table id="table-tool-draw-tooling" class="table table-bordered table-striped w-100 text-center">
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
                                        <tr class="search-row">
                                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="0" /></th>
                                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="1" /></th>
                                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="2" /></th>
                                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="3" /></th>
                                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="4" /></th>
                                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="5" /></th>
                                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="6" /></th>
                                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="7" /></th>
                                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="8" /></th>
                                            <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="9" /></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data loaded via AJAX -->
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
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '<?= base_url("Tool_engineering/tool_draw_tooling/get_data"); ?>',
                        type: 'POST'
                    },
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    pageLength: 25,
                    order: [[1, 'asc']],
                    autoWidth: false,
                    scrollX: true,
                    columnDefs: [
                        { orderable: false, targets: [10] },
                        { width: '120px', targets: 0 },
                        { width: '120px', targets: 1 },
                        { width: '80px', targets: 2 },
                        { width: '80px', targets: 3 },
                        { width: '100px', targets: 4 },
                        { width: '80px', targets: 5 },
                        { width: '120px', targets: 6 },
                        { width: '100px', targets: 7 },
                        { width: '100px', targets: 8 },
                        { width: '80px', targets: 9 },
                        { width: '100px', targets: 10 }
                    ],
                    language: {
                        processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div> Processing...',
                        emptyTable: 'No data available',
                        zeroRecords: 'No matching records found'
                    },
                    initComplete: function() {
                        setupColumnSearch();
                    }
                });

                // Per-column search - only on Enter key
                function setupColumnSearch() {
                    $('.column-search').off('keyup keydown input click');
                    
                    $('.column-search').on('click', function(e) {
                        e.stopPropagation();
                    });
                    
                    $('.column-search').on('keydown', function(e) {
                        e.stopPropagation();
                        
                        var $input = $(this);
                        var column = $input.data('column');
                        var value = $input.val();
                        
                        if (e.keyCode === 13) { // Enter
                            e.preventDefault();
                            table.column(column).search(value).draw();
                        } else if (e.keyCode === 27) { // ESC - clear
                            e.preventDefault();
                            $input.val('');
                            table.column(column).search('').draw();
                        }
                    });
                }
            });
        })(jQuery);
    </script>
</body>

</html>