<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Tool ID</title>
    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.css'); ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.css'); ?>" rel="stylesheet">
    <style>
        body {
            padding: 15px;
            background-color: #f5f5f5;
        }
        .container-fluid {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .table-responsive {
            margin-top: 15px;
        }
        .clickable-row {
            cursor: pointer;
        }
        .clickable-row:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <h4 class="mb-3">Select Tool ID</h4>
        <div class="table-responsive">
            <table id="tableToolInventoryTab" class="table table-bordered table-striped table-hover w-100">
                <thead>
                    <tr class="text-center">
                        <th>ID</th>
                        <th>Tool ID</th>
                        <th>Tool Drawing No</th>
                        <th>Revision</th>
                        <th>Tool Name</th>
                        <th>Tool Status</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tool_inventory_modal)): ?>
                        <?php foreach ($tool_inventory_modal as $tool): ?>
                            <tr class="clickable-row" 
                                data-tool-id="<?= isset($tool['TOOL_ID']) ? htmlspecialchars($tool['TOOL_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                data-inv-id="<?= isset($tool['ID']) ? (int)$tool['ID'] : 0; ?>">
                                <td><?= isset($tool['ID']) ? htmlspecialchars($tool['ID'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                <td>
                                    <a href="#" class="select-tool-id-link text-primary" style="text-decoration: underline;">
                                        <?= isset($tool['TOOL_ID']) ? htmlspecialchars($tool['TOOL_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>
                                    </a>
                                </td>
                                <td><?= isset($tool['TOOL_DRAWING_NO']) ? htmlspecialchars($tool['TOOL_DRAWING_NO'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                <td><?= isset($tool['REVISION']) ? htmlspecialchars($tool['REVISION'], ENT_QUOTES, 'UTF-8') : '0'; ?></td>
                                <td><?= isset($tool['TOOL_NAME']) ? htmlspecialchars($tool['TOOL_NAME'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                <td><?= isset($tool['TOOL_STATUS']) ? htmlspecialchars($tool['TOOL_STATUS'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                <td><?= isset($tool['REMARKS']) ? htmlspecialchars($tool['REMARKS'], ENT_QUOTES, 'UTF-8') : ''; ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary btn-select-tool-id" 
                                            data-tool-id="<?= isset($tool['TOOL_ID']) ? htmlspecialchars($tool['TOOL_ID'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                            data-inv-id="<?= isset($tool['ID']) ? (int)$tool['ID'] : 0; ?>">
                                        Select
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No tool inventory found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js'); ?>"></script>
    <script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js'); ?>"></script>
    <script>
    (function($){
        $(function(){
            // Initialize DataTable with search
            var tableToolInventoryTab = $('#tableToolInventoryTab').DataTable({
                pageLength: 10,
                order: [[1, 'asc']], // Order by Tool ID
                autoWidth: false,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                language: {
                    search: "Search:",
                    searchPlaceholder: "Search Tool ID, Drawing No, Tool Name..."
                }
            });

            // Handle Tool ID Selection
            $('#tableToolInventoryTab tbody').on('click', 'tr.clickable-row, .btn-select-tool-id', function(e) {
                e.preventDefault();
                var $row = $(this).closest('tr');
                var toolId = $row.data('tool-id') || $(this).data('tool-id');
                var invId = $row.data('inv-id') || $(this).data('inv-id');
                
                if (!toolId || toolId === '') {
                    alert('Tool ID tidak valid');
                    return;
                }
                
                // Send message to parent window
                if (window.opener) {
                    window.opener.postMessage({
                        action: 'tool_id_selected',
                        tool_id: toolId,
                        inv_id: invId
                    }, '*');
                    window.close();
                } else {
                    alert('Selected Tool ID: ' + toolId);
                }
            });

            $('#tableToolInventoryTab tbody').on('click', 'a.select-tool-id-link', function(e) {
                e.preventDefault();
                $(this).closest('tr').trigger('click');
            });
        });
    })(jQuery);
    </script>
</body>
</html>

