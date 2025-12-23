<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        .table td, .table th {
            color: #000 !important;
            padding: 0.35rem 0.4rem !important;
            font-size: 0.85rem;
        }
        #table-tool-inventory { 
            min-width: 1400px !important; 
        }
        #table-tool-inventory th, #table-tool-inventory td {
            white-space: nowrap;
        }
        .cell-ellipsis {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            max-width: 100%;
        }
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 4px;
            flex-wrap: wrap;
        }
        .navbar { position: sticky; top: 0; z-index: 1030; }
        #content-wrapper { min-height: calc(100vh - 56px); }
        #container-wrapper { padding-bottom: 4rem; margin-bottom: 2rem; }
        .card { margin-bottom: 2rem; }
        .dataTables_wrapper { 
            padding-bottom: 4rem !important; 
            margin-bottom: 2rem !important; 
        }
        .dataTables_paginate {
            margin-top: 1rem;
            margin-bottom: 2rem !important;
            padding-bottom: 1rem;
        }
        .search-row th {
            padding: 0.25rem !important;
        }
        .search-row input {
            width: 100%;
            font-size: 0.8rem;
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
                        <h4 class="m-0 font-weight-bold text-primary">Tool Inventory</h4>
                        <div>
                            <a href="<?= base_url('Tool_inventory/tool_inventory/export_excel'); ?>" class="btn btn-sm btn-success shadow-sm mr-2" target="_blank">
                                <i class="fa fa-file-excel"></i> Export to Excel
                            </a>
                            <a href="<?= base_url('Tool_inventory/tool_inventory/add_page'); ?>" class="btn btn-sm btn-primary shadow-sm">
                                <i class="fa fa-plus"></i> Add New
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-tool-inventory" class="table table-bordered table-striped w-100 text-left">
                                <thead>
                                    <tr class="text-center">
                                        <th>ID</th>
                                        <th>Tool Tag</th>
                                        <th>RQ No.</th>
                                        <th>Product</th>
                                        <th>Tool Name</th>
                                        <th>Tool Drawing No.</th>
                                        <th>Received Date</th>
                                        <th>Do No.</th>
                                        <th>Tool ID</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                        <th>Storage Location</th>
                                        <th>Material</th>
                                        <th>Tool Condition</th>
                                        <th>End Cycle</th>
                                        <th>Action</th>
                                    </tr>
                                    <tr class="search-row">
                                        <th></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="1" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="2" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="3" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="4" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="5" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="6" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="7" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="8" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="9" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="10" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="11" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="12" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="13" /></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Search..." data-column="14" /></th>
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
(function($){
    $(function(){
        var table = $('#table-tool-inventory').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url("Tool_inventory/tool_inventory/get_data"); ?>',
                type: 'POST',
                error: function(xhr, error, thrown) {
                    console.error('DataTables AJAX error:', error, thrown);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Error loading data. Please try again.');
                    } else {
                        alert('Error loading data. Please try again.');
                    }
                }
            },
            lengthMenu: [[10,25,50,100],[10,25,50,100]],
            pageLength: 25,
            order: [[0,'desc']],
            autoWidth: false,
            scrollX: true,
            searchDelay: 400, // Add delay to prevent too many requests
            columnDefs: [
                { orderable:false, targets:[15] },
                { width:'50px', targets:0 },
                { width:'100px', targets:1 },
                { width:'100px', targets:2 },
                { width:'120px', targets:3 },
                { width:'120px', targets:4 },
                { width:'120px', targets:5 },
                { width:'110px', targets:6 },
                { width:'100px', targets:7 },
                { width:'100px', targets:8 },
                { width:'80px', targets:9 },
                { width:'100px', targets:10 },
                { width:'120px', targets:11 },
                { width:'100px', targets:12 },
                { width:'100px', targets:13 },
                { width:'80px', targets:14 },
                { width:'120px', targets:15 }
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div> Processing...',
                emptyTable: 'No data available',
                zeroRecords: 'No matching records found'
            },
            drawCallback: function() {
                attachDeleteHandler();
            },
            initComplete: function() {
                setupColumnSearch();
            }
        });

        // Per-column search - only on Enter key
        function setupColumnSearch() {
            // Clear any existing handlers
            $('.column-search').off('keyup keydown input click');
            
            // Prevent click from bubbling to table
            $('.column-search').on('click', function(e) {
                e.stopPropagation();
            });
            
            // Handle keydown for per-column search
            $('.column-search').on('keydown', function(e) {
                e.stopPropagation();
                
                var $input = $(this);
                var column = $input.data('column');
                var value = $input.val();
                
                if (e.keyCode === 13) { // Enter
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    // Clear global search first to avoid conflicts
                    table.search('').draw();
                    // Then apply column search
                    table.column(column).search(value).draw();
                } else if (e.keyCode === 27) { // ESC - clear
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $input.val('');
                    table.column(column).search('').draw();
                }
            });
            
            // Prevent input event from triggering global search
            $('.column-search').on('input', function(e) {
                e.stopPropagation();
            });
        }

        // Delete handler
        function attachDeleteHandler() {
            $('#table-tool-inventory').off('click', '.btn-delete').on('click', '.btn-delete', function() {
                var id = Number($(this).data('id')) || 0;
                var toolTag = $(this).data('tool-tag') || '';
                if (id <= 0) {
                    alert('ID tidak valid');
                    return;
                }
                if (!confirm('Hapus Tool Inventory "' + toolTag + '"?')) return;
                
                $.ajax({
                    url: '<?= base_url("Tool_inventory/tool_inventory/delete_data"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: { INV_ID: id }
                }).done(function(res) {
                    if (res && res.success) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(res.message || 'Terhapus');
                        } else {
                            alert(res.message || 'Data berhasil dihapus');
                        }
                        table.ajax.reload(null, false);
                    } else {
                        alert(res && res.message ? res.message : 'Gagal menghapus');
                    }
                }).fail(function() {
                    alert('Terjadi kesalahan');
                });
            });
        }

        attachDeleteHandler();
    });
})(jQuery);
</script>
</body>
</html>

