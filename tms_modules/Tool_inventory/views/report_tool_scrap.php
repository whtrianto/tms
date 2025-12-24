<!DOCTYPE html>
<html lang="en">
<head>
    <?= $head; ?>
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .print-break { page-break-after: always; }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            padding: 20px;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .report-header .printed-date {
            text-align: right;
            font-size: 9pt;
            margin-bottom: 10px;
        }
        .report-title {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 20px 0;
            text-decoration: underline;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 1px solid #000;
        }
        .report-table td {
            padding: 8px 12px;
            vertical-align: middle;
            border: 1px solid #000;
            min-height: 35px;
        }
        .report-table .label {
            font-weight: bold;
            width: 20%;
            background-color: #f5f5f5;
            text-align: left;
            padding-left: 15px;
        }
        .report-table .value {
            width: 30%;
            text-align: left;
            padding-left: 15px;
            word-wrap: break-word;
        }
        .report-table tr {
            height: 40px;
        }
        .report-table tr:nth-child(even) .label {
            background-color: #f9f9f9;
        }
        .report-table tr:nth-child(even) .value {
            background-color: #ffffff;
        }
        .report-section {
            margin-top: 15px;
        }
        .report-section-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .text-area-box {
            border: 1px solid #000;
            min-height: 60px;
            padding: 5px;
            margin-top: 5px;
            background-color: #fff;
        }
        .sketch-box {
            border: 1px solid #000;
            min-height: 150px;
            padding: 5px;
            margin-top: 5px;
            background-color: #fff;
        }
        .action-buttons {
            margin-bottom: 20px;
        }
        .btn-group {
            display: inline-block;
        }
        #export-format {
            display: inline-block;
            width: 150px;
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <?php
    $scrap = isset($scrap) ? $scrap : array();
    
    // Format dates
    function formatDate($date) {
        if (empty($date) || $date === null) return '';
        try {
            $d = new DateTime($date);
            return $d->format('d-m-Y');
        } catch (Exception $e) {
            return $date;
        }
    }
    
    function formatDateTime($date) {
        if (empty($date) || $date === null) return '';
        try {
            $d = new DateTime($date);
            return $d->format('d-m-Y H:i');
        } catch (Exception $e) {
            return $date;
        }
    }
    
    $scrap_no = isset($scrap['SCRAP_NO']) ? htmlspecialchars($scrap['SCRAP_NO'], ENT_QUOTES, 'UTF-8') : '';
    $issue_date = formatDate(isset($scrap['SCRAP_DATE']) ? $scrap['SCRAP_DATE'] : '');
    $acc_scrap_date = formatDate(isset($scrap['SCRAP_ACC_DATE']) ? $scrap['SCRAP_ACC_DATE'] : '');
    $machine = isset($scrap['MACHINE']) ? htmlspecialchars($scrap['MACHINE'], ENT_QUOTES, 'UTF-8') : '';
    $request_by = isset($scrap['REQUESTED_BY_NAME']) ? htmlspecialchars($scrap['REQUESTED_BY_NAME'], ENT_QUOTES, 'UTF-8') : '';
    $operator = isset($scrap['OPERATOR_NAME']) ? htmlspecialchars($scrap['OPERATOR_NAME'], ENT_QUOTES, 'UTF-8') : '';
    $tool_id = isset($scrap['INV_TOOL_ID']) ? htmlspecialchars($scrap['INV_TOOL_ID'], ENT_QUOTES, 'UTF-8') : '';
    $std_qty = isset($scrap['SCRAP_STD_QTY_THIS']) ? (int)$scrap['SCRAP_STD_QTY_THIS'] : 0;
    $current_qty = isset($scrap['SCRAP_CURRENT_QTY_THIS']) ? (int)$scrap['SCRAP_CURRENT_QTY_THIS'] : 0;
    $not_received = isset($scrap['SCRAP_NRCV_QTY_THIS']) ? (int)$scrap['SCRAP_NRCV_QTY_THIS'] : 0;
    $pcs_produced = isset($scrap['PCS_PRODUCED']) ? (int)$scrap['PCS_PRODUCED'] : 0;
    $tool_name = isset($scrap['TOOL_NAME']) ? htmlspecialchars($scrap['TOOL_NAME'], ENT_QUOTES, 'UTF-8') : '';
    $tool_price = isset($scrap['TOOL_PRICE']) && $scrap['TOOL_PRICE'] !== null ? number_format((float)$scrap['TOOL_PRICE'], 2) : '';
    $tool_residue_value = ''; // TODO: Calculate if needed
    $cause_remark = isset($scrap['SCRAP_CAUSE_REMARK']) ? htmlspecialchars($scrap['SCRAP_CAUSE_REMARK'], ENT_QUOTES, 'UTF-8') : '';
    $sketch = isset($scrap['SKETCH']) ? htmlspecialchars($scrap['SKETCH'], ENT_QUOTES, 'UTF-8') : '';
    $suggestion = 'Scrap';
    $approve_by = isset($scrap['APPROVED_BY_NAME']) ? htmlspecialchars($scrap['APPROVED_BY_NAME'], ENT_QUOTES, 'UTF-8') : '';
    $approve_date = formatDate(isset($scrap['SCRAP_APPROVED_DATE']) ? $scrap['SCRAP_APPROVED_DATE'] : '');
    $to_order = isset($scrap['SCRAP_TO_ORDER']) && $scrap['SCRAP_TO_ORDER'] ? 'Yes' : 'No';
    $reason = isset($scrap['REASON']) ? htmlspecialchars($scrap['REASON'], ENT_QUOTES, 'UTF-8') : '';
    $cause = isset($scrap['CAUSE_NAME']) ? htmlspecialchars($scrap['CAUSE_NAME'], ENT_QUOTES, 'UTF-8') : '';
    $counter_measure = isset($scrap['SCRAP_COUNTER_MEASURE']) ? htmlspecialchars($scrap['SCRAP_COUNTER_MEASURE'], ENT_QUOTES, 'UTF-8') : '';
    $investigated_by = isset($scrap['INVESTIGATED_BY_NAME']) ? htmlspecialchars($scrap['INVESTIGATED_BY_NAME'], ENT_QUOTES, 'UTF-8') : '';
    ?>

    <div class="action-buttons no-print">
        <a href="<?= base_url('Tool_inventory/tool_scrap'); ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back
        </a>
        <button onclick="window.print();" class="btn btn-primary btn-sm">
            <i class="fa fa-print"></i> Print
        </button>
        <div class="btn-group" style="margin-left: 10px;">
            <select id="export-format" class="form-control form-control-sm" style="display: inline-block; width: auto;">
                <option value="">Export Format...</option>
                <option value="pdf">PDF</option>
                <option value="word">Word</option>
                <option value="excel">Excel</option>
                <option value="csv">CSV</option>
                <option value="xml">XML</option>
                <option value="html">HTML</option>
                <option value="tiff">TIFF</option>
            </select>
            <button onclick="exportReport();" class="btn btn-success btn-sm">
                <i class="fa fa-download"></i> Export
            </button>
        </div>
    </div>

    <div class="report-header">
        <div class="printed-date">Printed at : <?= date('d-m-Y H:i'); ?></div>
        <h2>PT. TD AUTOMOTIVE COMPRESSOR INDONESIA</h2>
        <div class="report-title">TOOL ACCIDENT/ SCRAP</div>
    </div>

    <table class="report-table">
        <tr>
            <td class="label">Scrap No.</td>
            <td class="value"><strong><?= $scrap_no; ?></strong></td>
            <td class="label">Acc/ Scrap Date</td>
            <td class="value"><?= $acc_scrap_date; ?></td>
        </tr>
        <tr>
            <td class="label">Issue Date</td>
            <td class="value"><?= $issue_date; ?></td>
            <td class="label">Machine</td>
            <td class="value"><?= $machine; ?></td>
        </tr>
        <tr>
            <td class="label">Request By</td>
            <td class="value"><?= $request_by; ?></td>
            <td class="label">Operator</td>
            <td class="value"><?= $operator; ?></td>
        </tr>
        <tr>
            <td class="label">Tool ID</td>
            <td class="value"><?= $tool_id; ?></td>
            <td class="label">Std Qty</td>
            <td class="value"><?= number_format($std_qty, 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td class="label">Tool Name</td>
            <td class="value"><?= $tool_name; ?></td>
            <td class="label">Current Qty</td>
            <td class="value"><?= number_format($current_qty, 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td class="label">Tool Price</td>
            <td class="value"><?= !empty($tool_price) ? number_format((float)$tool_price, 2, ',', '.') : ''; ?></td>
            <td class="label">Not Received</td>
            <td class="value"><?= number_format($not_received, 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td class="label">Tool Residue Value</td>
            <td class="value"><?= $tool_residue_value; ?></td>
            <td class="label">Pcs Produced</td>
            <td class="value"><strong><?= number_format($pcs_produced, 0, ',', '.'); ?></strong></td>
        </tr>
    </table>

    <div class="report-section">
        <div class="report-section-label">Cause Remark</div>
        <div class="text-area-box"><?= nl2br($cause_remark); ?></div>
    </div>

    <div class="report-section">
        <div class="report-section-label">Sketch</div>
        <div class="sketch-box">
            <?php if (!empty($sketch)): ?>
                <img src="<?= base_url('uploads/sketch/' . $sketch); ?>" alt="Sketch" style="max-width: 100%; height: auto;" />
            <?php endif; ?>
        </div>
    </div>

    <table class="report-table" style="margin-top: 20px;">
        <tr>
            <td class="label">Suggestion</td>
            <td class="value"><strong><?= $suggestion; ?></strong></td>
            <td class="label">Approve By</td>
            <td class="value"><?= $approve_by; ?></td>
        </tr>
        <tr>
            <td class="label">To Order</td>
            <td class="value"><?= $to_order; ?></td>
            <td class="label">Approve Date</td>
            <td class="value"><?= $approve_date; ?></td>
        </tr>
        <tr>
            <td class="label">Reason</td>
            <td class="value"><?= $reason; ?></td>
            <td class="label">Cause</td>
            <td class="value"><?= $cause; ?></td>
        </tr>
    </table>

    <div class="report-section">
        <div class="report-section-label">Counter Measure</div>
        <div class="text-area-box"><?= nl2br($counter_measure); ?></div>
    </div>

    <div class="report-section" style="margin-top: 30px;">
        <div style="text-align: right;">
            <div class="report-section-label" style="text-align: left; display: inline-block;">Investigated By</div>
            <div style="margin-top: 40px; display: inline-block; margin-left: 20px;">
                <?= $investigated_by; ?>
            </div>
        </div>
    </div>

    <script>
    function exportReport() {
        var format = document.getElementById('export-format').value;
        if (!format) {
            alert('Please select export format');
            return;
        }
        
        var scrapId = <?= isset($scrap['SCRAP_ID']) ? (int)$scrap['SCRAP_ID'] : 0; ?>;
        if (scrapId <= 0) {
            alert('Invalid scrap ID');
            return;
        }
        
        var url = '<?= base_url("Tool_inventory/tool_scrap/export_report"); ?>' + '/' + scrapId + '/' + format;
        window.location.href = url;
    }
    </script>
</body>
</html>