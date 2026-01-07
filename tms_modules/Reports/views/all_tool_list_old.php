<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #fff;
        }
        .report-container {
            max-width: 100%;
            margin: 0 auto;
        }
        .report-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .report-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .report-meta {
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        .report-table th {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        .report-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: right;
        }
        .report-table td:first-child,
        .report-table th:first-child {
            text-align: center;
        }
        .report-table td:nth-child(2),
        .report-table th:nth-child(2),
        .report-table td:nth-child(3),
        .report-table th:nth-child(3),
        .report-table td:nth-child(4),
        .report-table th:nth-child(4) {
            text-align: left;
        }
        .report-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .report-table tr:hover {
            background-color: #f5f5f5;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        @media print {
            body {
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
        .action-buttons {
            margin-bottom: 20px;
        }
        .btn {
            padding: 8px 16px;
            margin-right: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
    </style>
</head>
<body>
    <div class="report-container">
        <div class="action-buttons no-print">
            <a href="<?= base_url('Reports'); ?>" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Back to Reports
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fa fa-print"></i> Print
            </button>
        </div>

        <div class="report-header">
            <div class="report-title"><?= htmlspecialchars($report_title, ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="report-meta">Generated on: <?= htmlspecialchars($generated_on, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>TOOL Drawing</th>
                    <th>Revision</th>
                    <th>Tool Name</th>
                    <th>STD QTY</th>
                    <th>QTY ON-HAND</th>
                    <th>Difference</th>
                    <th>REPLENISH QTY</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tools)): ?>
                    <?php foreach ($tools as $tool): ?>
                        <tr>
                            <td class="text-center"><?= (int)$tool['ROW_NUM']; ?></td>
                            <td class="text-left"><?= htmlspecialchars($tool['TOOL_DRAWING'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-center"><?= (int)$tool['REVISION']; ?></td>
                            <td class="text-left"><?= htmlspecialchars($tool['TOOL_NAME'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-right"><?= number_format((int)$tool['STD_QTY'], 0); ?></td>
                            <td class="text-right"><?= number_format((int)$tool['QTY_ON_HAND'], 0); ?></td>
                            <td class="text-right"><?= number_format((int)$tool['DIFFERENCE'], 0); ?></td>
                            <td class="text-right"><?= number_format((int)$tool['REPLENISH_QTY'], 0); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

