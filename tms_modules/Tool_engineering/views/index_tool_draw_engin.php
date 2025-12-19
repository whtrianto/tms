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
        /* Table with minimum widths to prevent shrinking */
        #table-tool-draw-sql { 
            min-width: 1200px !important; 
        }
        #table-tool-draw-sql th, #table-tool-draw-sql td {
            white-space: nowrap;
        }
        /* Column minimum widths */
        #table-tool-draw-sql th:nth-child(1), #table-tool-draw-sql td:nth-child(1) { min-width: 50px; }
        #table-tool-draw-sql th:nth-child(2), #table-tool-draw-sql td:nth-child(2) { min-width: 120px; }
        #table-tool-draw-sql th:nth-child(3), #table-tool-draw-sql td:nth-child(3) { min-width: 90px; }
        #table-tool-draw-sql th:nth-child(4), #table-tool-draw-sql td:nth-child(4) { min-width: 140px; }
        #table-tool-draw-sql th:nth-child(5), #table-tool-draw-sql td:nth-child(5) { min-width: 120px; }
        #table-tool-draw-sql th:nth-child(6), #table-tool-draw-sql td:nth-child(6) { min-width: 70px; }
        #table-tool-draw-sql th:nth-child(7), #table-tool-draw-sql td:nth-child(7) { min-width: 80px; }
        #table-tool-draw-sql th:nth-child(8), #table-tool-draw-sql td:nth-child(8) { min-width: 110px; }
        #table-tool-draw-sql th:nth-child(9), #table-tool-draw-sql td:nth-child(9) { min-width: 120px; }
        #table-tool-draw-sql th:nth-child(10), #table-tool-draw-sql td:nth-child(10) { min-width: 100px; }
        #table-tool-draw-sql th:nth-child(11), #table-tool-draw-sql td:nth-child(11) { min-width: 115px; }
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
        .dataTables_wrapper {
            padding-bottom: 2rem !important;
        }
        /* Search row styling */
        .search-row th {
            padding: 0.25rem !important;
        }
        .search-row input {
            width: 100%;
            font-size: 0.8rem;
        }
        /* Drawing No link styling */
        .drawing-no-link {
            color: #007bff !important;
            text-decoration: underline !important;
            cursor: pointer !important;
            font-weight: 500;
        }
        .drawing-no-link:hover {
            color: #0056b3 !important;
            text-decoration: underline !important;
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
                        <a href="<?= base_url('Tool_engineering/tool_draw_engin/add_page'); ?>" class="btn btn-sm btn-primary shadow-sm">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="table-tool-draw-sql" class="table table-bordered table-striped w-100 text-center">
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
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?= isset($modal_logout) ? $modal_logout : ''; ?>
            
            <!-- Detail modal for drawing (opened when clicking Drawing No) -->
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
                                    <div id="drawingDetailImage" style="flex:0 0 300px; max-width:300px;">
                                        <div id="drawingFileContainer" style="margin-bottom:16px;"></div>
                                    </div>
                                    <div style="flex:1 1 auto;">
                                        <table class="table table-bordered table-sm">
                                            <tr><th style="width:160px">Product</th><td id="detailProduct"></td></tr>
                                            <tr><th>Process</th><td id="detailProcess"></td></tr>
                                            <tr><th>Tool</th><td id="detailTool"></td></tr>
                                            <tr><th>Drawing No</th><td id="detailDrawingNo"></td></tr>
                                            <tr><th>Revision</th><td id="detailRevision"></td></tr>
                                            <tr><th>Status</th><td id="detailStatus"></td></tr>
                                            <tr><th>Material</th><td id="detailMaterial"></td></tr>
                                            <tr><th>Maker</th><td id="detailMaker"></td></tr>
                                            <tr><th>Machine Group</th><td id="detailMachineGroup"></td></tr>
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
        var table = $('#table-tool-draw-sql').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= base_url("Tool_engineering/tool_draw_engin/get_data"); ?>',
                type: 'POST'
            },
            lengthMenu: [[10,25,50,100],[10,25,50,100]],
            pageLength: 25,
            order: [[0,'desc']],
            autoWidth: false,
            scrollX: true,
            columnDefs: [
                { orderable:false, targets:[10] },
                { width:'50px', targets:0 },      // ID
                { width:'120px', targets:1 },     // Product
                { width:'90px', targets:2 },      // Process
                { width:'140px', targets:3 },     // Drawing
                { width:'120px', targets:4 },     // Tool Name
                { width:'60px', targets:5 },      // Revision
                { width:'80px', targets:6 },      // Status
                { width:'110px', targets:7 },     // Effective
                { width:'120px', targets:8 },     // Modified Date
                { width:'110px', targets:9 },     // Modified By
                { width:'115px', targets:10 }     // Action
            ],
            language: {
                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div> Processing...',
                emptyTable: 'No data available',
                zeroRecords: 'No matching records found'
            },
            drawCallback: function(settings) {
                // Re-attach handlers after table redraw
                attachDeleteHandler();
                attachDrawingNoHandler();
            },
            initComplete: function() {
                // Setup per-column search after table initialization
                setupColumnSearch();
            }
        });

        // Fix header alignment on window resize/zoom and sidebar toggle
        var resizeTimer;
        function adjustTableColumns() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                table.columns.adjust();
            }, 300);
        }
        
        $(window).on('resize', adjustTableColumns);
        
        // Handle sidebar toggle - watch for class changes on body or sidebar
        $(document).on('click', '#sidebarToggle, #sidebarToggleTop, .sidebar-toggled', adjustTableColumns);
        
        // Also observe sidebar class changes
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    adjustTableColumns();
                }
            });
        });
        
        // Observe body for sidebar-toggled class
        if (document.body) {
            observer.observe(document.body, { attributes: true });
        }
        // Observe sidebar for collapse
        var sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            observer.observe(sidebar, { attributes: true });
        }

        // Per-column search - only search on Enter key
        function setupColumnSearch() {
            // Remove all existing handlers first
            $('.column-search').off('keyup keydown input click');
            
            // Prevent click from propagating to table header (sorting)
            $('.column-search').on('click', function(e) {
                e.stopPropagation();
            });
            
            // Only search on Enter key
            $('.column-search').on('keydown', function(e) {
                e.stopPropagation(); // Prevent event from bubbling to header
                
                var $input = $(this);
                var column = $input.data('column');
                var value = $input.val();
                
                if (e.keyCode === 13) { // Enter key
                    e.preventDefault();
                    table.column(column).search(value).draw();
                } else if (e.keyCode === 27) { // ESC key - clear search
                    e.preventDefault();
                    $input.val('');
                    table.column(column).search('').draw();
                }
            });
        }

        // Delete handler function
        function attachDeleteHandler() {
            $('#table-tool-draw-sql').off('click', '.btn-delete').on('click', '.btn-delete', function() {
                var id = Number($(this).data('id')) || 0;
                var name = $(this).data('name') || '';
                if (id <= 0) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('ID tidak valid');
                    } else {
                        alert('ID tidak valid');
                    }
                    return;
                }
                if (!confirm('Hapus Tool Drawing "' + name + '"?')) return;
                $.ajax({
                    url: '<?= base_url("Tool_engineering/tool_draw_engin/delete_data"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        TD_ID: id
                    }
                }).done(function(res) {
                    if (res && res.success) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(res.message || 'Terhapus');
                        } else {
                            alert(res.message || 'Data berhasil dihapus');
                        }
                        // Reload table data instead of full page reload
                        table.ajax.reload(null, false);
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(res && res.message ? res.message : 'Gagal menghapus');
                        } else {
                            alert(res && res.message ? res.message : 'Gagal menghapus');
                        }
                    }
                }).fail(function() {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Terjadi kesalahan');
                    } else {
                        alert('Terjadi kesalahan');
                    }
                });
            });
        }

        // Drawing No click handler - show detail modal
        function attachDrawingNoHandler() {
            $('#table-tool-draw-sql').off('click', '.drawing-no-link').on('click', '.drawing-no-link', function(e) {
                e.preventDefault();
                var id = Number($(this).data('id')) || 0;
                if (id <= 0) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('ID tidak valid');
                    } else {
                        alert('ID tidak valid');
                    }
                    return;
                }

                // Show loading state
                $('#drawingFileContainer').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
                $('#detailProduct, #detailProcess, #detailTool, #detailDrawingNo, #detailRevision, #detailStatus, #detailMaterial, #detailMaker, #detailMachineGroup, #detailEffective, #detailModified, #detailModifiedBy').text('');

                // Fetch detail data
                $.ajax({
                    url: '<?= base_url("Tool_engineering/tool_draw_engin/get_detail"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        TD_ID: id
                    }
                }).done(function(res) {
                    if (res && res.success && res.data) {
                        var d = res.data;
                        
                        // Function to format file size
                        function formatFileSize(bytes) {
                            if (bytes >= 1073741824) {
                                return (bytes / 1073741824).toFixed(2) + ' GB';
                            } else if (bytes >= 1048576) {
                                return (bytes / 1048576).toFixed(2) + ' MB';
                            } else if (bytes >= 1024) {
                                return (bytes / 1024).toFixed(2) + ' KB';
                            } else {
                                return bytes + ' bytes';
                            }
                        }
                        
                        // Function to render multiple files from array - all files are clickable for download
                        function renderFilesFromArray(files, containerId, label) {
                            var html = '';
                            html += '<div style="margin-bottom:8px;"><strong>' + label + ':</strong></div>';
                            
                            if (!files || files.length === 0) {
                                html += '<div style="text-align:center; padding:8px; border:1px solid #ddd; background:#f5f5f5; color:#999;">No files available</div>';
                            } else {
                                html += '<div style="border:1px solid #ddd; padding:8px; background:#f9f9f9;">';
                                
                                files.forEach(function(file, index) {
                                    if (index > 0) {
                                        html += '<hr style="margin:8px 0;" />';
                                    }
                                    
                                    // All files are clickable links for download
                                    // Use href directly to file - if file is in web root, this bypasses PHP completely
                                    html += '<div style="margin-bottom:4px;">' +
                                            '<a href="' + file.url + '" target="_blank" style="display:block; padding:8px; border:1px solid #ccc; background:#fff; text-decoration:none; color:#007bff; cursor:pointer; border-radius:4px;" ' +
                                            'onmouseover="this.style.background=\'#f0f0f0\';" onmouseout="this.style.background=\'#fff\';" title="Click to download">' +
                                            '<i class="fa fa-download"></i> ' + file.name + ' (' + formatFileSize(file.size) + ')' +
                                            '</a></div>';
                                });
                                
                                html += '</div>';
                            }
                            
                            $(containerId).html(html);
                        }
                        
                        // Function to render single file (for backward compatibility) - clickable for download
                        function renderFileFromUrl(fileUrl, fileId, containerId, label) {
                            var html = '';
                            if (fileUrl && fileUrl.trim() !== '') {
                                var displayName = fileId || 'File';
                                
                                // All files are clickable links for download
                                // Use href directly to file - if file is in web root, this bypasses PHP completely
                                html = '<div style="margin-bottom:8px;"><strong>' + label + ':</strong></div>' +
                                       '<div style="border:1px solid #ddd; padding:8px; background:#f9f9f9;">' +
                                       '<a href="' + fileUrl + '" target="_blank" style="display:block; padding:8px; border:1px solid #ccc; background:#fff; text-decoration:none; color:#007bff; cursor:pointer; border-radius:4px;" ' +
                                       'onmouseover="this.style.background=\'#f0f0f0\';" onmouseout="this.style.background=\'#fff\';" title="Click to download">' +
                                       '<i class="fa fa-download"></i> ' + displayName +
                                       '</a></div>';
                            } else {
                                html = '<div style="margin-bottom:8px;"><strong>' + label + ':</strong></div>' +
                                       '<div style="text-align:center; padding:8px; border:1px solid #ddd; background:#f5f5f5; color:#999;">No file available</div>';
                            }
                            $(containerId).html(html);
                        }
                        
                        // Render all Drawing Files from folder - clickable for download
                        if (d.TD_DRAWING_FILES && d.TD_DRAWING_FILES.length > 0) {
                            renderFilesFromArray(d.TD_DRAWING_FILES, '#drawingFileContainer', 'Drawing File');
                        } else if (d.TD_DRAWING_FILE_URL) {
                            // Fallback to single file if array not available
                            renderFileFromUrl(d.TD_DRAWING_FILE_URL, d.TD_DRAWING_FILE, '#drawingFileContainer', 'Drawing File');
                        } else {
                            $('#drawingFileContainer').html('<div style="margin-bottom:8px;"><strong>Drawing File:</strong></div><div style="text-align:center; padding:8px; border:1px solid #ddd; background:#f5f5f5; color:#999;">No files available</div>');
                        }
                        
                        // Set detail fields
                        $('#detailProduct').text(d.TD_PRODUCT_NAME || '-');
                        $('#detailProcess').text(d.TD_OPERATION_NAME || '-');
                        $('#detailTool').text(d.TD_TOOL_NAME || '-');
                        $('#detailDrawingNo').text(d.TD_DRAWING_NO || '-');
                        $('#detailRevision').text(d.TD_REVISION || '0');
                        $('#detailStatus').text(d.TD_STATUS || 'Inactive');
                        $('#detailMaterial').text(d.TD_MATERIAL_NAME || '-');
                        $('#detailMaker').text(d.TD_MAKER_NAME || '-');
                        $('#detailMachineGroup').text(d.TD_MAC_NAME || '-');
                        $('#detailEffective').text(d.TD_EFFECTIVE_DATE || '-');
                        $('#detailModified').text(d.TD_MODIFIED_DATE || '-');
                        $('#detailModifiedBy').text(d.TD_MODIFIED_BY || '-');
                        
                        // Show modal
                        $('#modalDetailDrawing').modal('show');
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(res && res.message ? res.message : 'Tidak dapat mengambil detail');
                        } else {
                            alert(res && res.message ? res.message : 'Tidak dapat mengambil detail');
                        }
                    }
                }).fail(function() {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Terjadi kesalahan saat mengambil detail');
                    } else {
                        alert('Terjadi kesalahan saat mengambil detail');
                    }
                });
            });
        }

        // Initial attach
        attachDeleteHandler();
        attachDrawingNoHandler();
    });
})(jQuery);
</script>
</body>
</html>

