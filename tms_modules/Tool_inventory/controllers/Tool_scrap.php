<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tool Scrap Controller
 * @property M_tool_scrap $tool_scrap
 */
class Tool_scrap extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('form_validation', 'session'));
        
        $username = $this->session->userdata('username');
        $this->uid = (string)($username ?: 'SYSTEM');

        $this->load->model('M_tool_scrap', 'tool_scrap');
        $this->tool_scrap->uid = $this->uid;

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $this->view('index_tool_scrap', $data, FALSE);
    }

    /**
     * Server-side DataTable AJAX handler
     */
    public function get_data()
    {
        if (ob_get_level()) ob_clean();
        $this->output->set_content_type('application/json', 'UTF-8');

        try {
            $draw = (int)$this->input->post('draw');
            $start = (int)$this->input->post('start');
            $length = (int)$this->input->post('length');
            $search = $this->input->post('search');
            $search_value = isset($search['value']) ? trim($search['value']) : '';
            $order = $this->input->post('order');
            $order_column = isset($order[0]['column']) ? (int)$order[0]['column'] : 0;
            $order_dir = isset($order[0]['dir']) ? $order[0]['dir'] : 'desc';

            $columns = $this->input->post('columns');
            $column_search = array();
            if (is_array($columns)) {
                foreach ($columns as $idx => $col) {
                    if (isset($col['search']['value']) && $col['search']['value'] !== '') {
                        $column_search[$idx] = $col['search']['value'];
                    }
                }
            }

            $result = $this->tool_scrap->get_data_serverside($start, $length, $search_value, $order_column, $order_dir, $column_search);

            $formatted_data = array();
            foreach ($result['data'] as $row) {
                $status_badge = $this->tool_scrap->get_status_badge(isset($row['SCRAP_STATUS']) ? $row['SCRAP_STATUS'] : 0);
                
                $id = (int)$row['SCRAP_ID'];
                $scrap_no = htmlspecialchars(isset($row['SCRAP_NO']) ? $row['SCRAP_NO'] : '', ENT_QUOTES, 'UTF-8');
                $report_url = base_url('Tool_inventory/tool_scrap/report_page/' . $id);
                
                $action_html = '<div class="action-buttons">' .
                    '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $id . '" data-scrap-no="' . $scrap_no . '">Del</button> ' .
                    '<a href="' . $report_url . '" class="btn btn-info btn-sm" title="Report">Report</a>' .
                    '</div>';

                $formatted_data[] = array(
                    $id,
                    htmlspecialchars(isset($row['ISSUE_DATE']) ? $row['ISSUE_DATE'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['ACC_SCRAP_DATE']) ? $row['ACC_SCRAP_DATE'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TOOL_ID']) ? $row['TOOL_ID'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TOOL_DRAWING_NO']) ? $row['TOOL_DRAWING_NO'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TOOL_NAME']) ? $row['TOOL_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['REASON']) ? $row['REASON'] : '', ENT_QUOTES, 'UTF-8'),
                    $status_badge,
                    htmlspecialchars(isset($row['COUNTER_MEASURE']) ? $row['COUNTER_MEASURE'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['PCS_PRODUCED']) ? $row['PCS_PRODUCED'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['MACHINE']) ? $row['MACHINE'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['CAUSE_REMARK']) ? $row['CAUSE_REMARK'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['SUGGESTION']) ? $row['SUGGESTION'] : '', ENT_QUOTES, 'UTF-8'),
                    $action_html
                );
            }

            $this->output->set_output(json_encode(array(
                'draw' => $draw,
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $formatted_data
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        } catch (Exception $e) {
            log_message('error', '[Tool_scrap::get_data] Exception: ' . $e->getMessage());
            $this->output->set_output(json_encode(array(
                'draw' => isset($draw) ? $draw : 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'Error loading data.'
            )));
        }
    }

    /**
     * Delete Tool Scrap
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('SCRAP_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }

        $ok = $this->tool_scrap->delete_data($id);
        echo json_encode(array(
            'success' => $ok,
            'message' => $this->tool_scrap->messages
        ));
    }

    /**
     * Add page
     */
    public function add_page()
    {
        $data = array();
        // TODO: Load dropdown data for add page
        $this->view('add_tool_scrap', $data, FALSE);
    }

    /**
     * Edit page
     */
    public function edit_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_scrap->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        $data = array();
        $data['scrap'] = $row;
        // TODO: Load dropdown data for edit page
        $this->view('edit_tool_scrap', $data, FALSE);
    }

    /**
     * Report page
     */
    public function report_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_scrap->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        $data = array();
        $data['scrap'] = $row;
        $this->view('report_tool_scrap', $data, FALSE);
    }

    /**
     * Export report in various formats
     */
    public function export_report($id = 0, $format = 'pdf')
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $format = strtolower(trim($format));
        $allowed_formats = array('pdf', 'word', 'excel', 'csv', 'xml', 'mhtml', 'tiff');
        if (!in_array($format, $allowed_formats)) {
            show_404();
            return;
        }

        $row = $this->tool_scrap->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        $data = array();
        $data['scrap'] = $row;

        switch ($format) {
            case 'pdf':
                $this->export_pdf($data);
                break;
            case 'word':
                $this->export_word($data);
                break;
            case 'excel':
                $this->export_excel($data);
                break;
            case 'csv':
                $this->export_csv($data);
                break;
            case 'xml':
                $this->export_xml($data);
                break;
            case 'mhtml':
                $this->export_mhtml($data);
                break;
            case 'tiff':
                $this->export_tiff($data);
                break;
        }
    }

    /**
     * Helper function to format date
     */
    private function format_date_report($date)
    {
        if (empty($date) || $date === null) return '';
        try {
            $d = new DateTime($date);
            return $d->format('d-m-Y');
        } catch (Exception $e) {
            return $date;
        }
    }

    /**
     * Export to PDF
     */
    private function export_pdf($data)
    {
        $scrap = $data['scrap'];
        $filename = 'Tool_Scrap_Report_' . (isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '') . '.pdf';
        
        // Load HTML content
        $html = $this->load->view('report_tool_scrap', $data, TRUE);
        
        // Remove print buttons and action buttons
        $html = preg_replace('/<div class="action-buttons[^"]*">.*?<\/div>/is', '', $html);
        $html = preg_replace('/<script>.*?<\/script>/is', '', $html);
        
        // Try to use TCPDF or mPDF if available
        if (file_exists(APPPATH . 'libraries/Pdf.php')) {
            $this->load->library('pdf');
            if (class_exists('TCPDF')) {
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $pdf->SetCreator('TMS System');
                $pdf->SetAuthor('TMS');
                $pdf->SetTitle('Tool Scrap Report');
                $pdf->SetSubject('Tool Scrap Report');
                $pdf->SetMargins(15, 15, 15);
                $pdf->SetAutoPageBreak(TRUE, 15);
                $pdf->AddPage();
                $pdf->writeHTML($html, true, false, true, false, '');
                $pdf->Output($filename, 'D');
                exit;
            }
        }
        
        // Fallback: Use HTML with inline CSS for PDF conversion
        // User can use browser print to PDF or other PDF tools
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Export PDF</title>';
        echo '<style>@media print { @page { margin: 1cm; } }</style></head><body>';
        echo $html;
        echo '<script>setTimeout(function(){ window.print(); }, 500);</script>';
        echo '</body></html>';
        exit;
    }

    /**
     * Export to Word (DOCX)
     */
    private function export_word($data)
    {
        $scrap = $data['scrap'];
        $filename = 'Tool_Scrap_Report_' . (isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '') . '.doc';

        $content = $this->generate_word_content($data);

        header('Content-Type: application/vnd.ms-word');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        
        echo $content;
        exit;
    }

    /**
     * Export to Excel (XLS)
     */
    private function export_excel($data)
    {
        $scrap = $data['scrap'];
        $filename = 'Tool_Scrap_Report_' . (isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '') . '.xls';

        $content = $this->generate_excel_content($data);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        
        echo $content;
        exit;
    }

    /**
     * Export to CSV
     */
    private function export_csv($data)
    {
        $scrap = $data['scrap'];
        $filename = 'Tool_Scrap_Report_' . (isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '') . '.csv';
        $rows = array();
        $rows[] = array('Field', 'Value');
        $rows[] = array('Scrap No.', isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '');
        $rows[] = array('Issue Date', $this->format_date_report(isset($scrap['SCRAP_DATE']) ? $scrap['SCRAP_DATE'] : ''));
        $rows[] = array('Acc/Scrap Date', $this->format_date_report(isset($scrap['SCRAP_ACC_DATE']) ? $scrap['SCRAP_ACC_DATE'] : ''));
        $rows[] = array('Machine', isset($scrap['MACHINE']) ? $scrap['MACHINE'] : '');
        $rows[] = array('Request By', isset($scrap['REQUESTED_BY_NAME']) ? $scrap['REQUESTED_BY_NAME'] : '');
        $rows[] = array('Operator', isset($scrap['OPERATOR_NAME']) ? $scrap['OPERATOR_NAME'] : '');
        $rows[] = array('Tool ID', isset($scrap['INV_TOOL_ID']) ? $scrap['INV_TOOL_ID'] : '');
        $rows[] = array('Tool Name', isset($scrap['TOOL_NAME']) ? $scrap['TOOL_NAME'] : '');
        $rows[] = array('Std Qty', isset($scrap['SCRAP_STD_QTY_THIS']) ? $scrap['SCRAP_STD_QTY_THIS'] : 0);
        $rows[] = array('Current Qty', isset($scrap['SCRAP_CURRENT_QTY_THIS']) ? $scrap['SCRAP_CURRENT_QTY_THIS'] : 0);
        $rows[] = array('Not Received', isset($scrap['SCRAP_NRCV_QTY_THIS']) ? $scrap['SCRAP_NRCV_QTY_THIS'] : 0);
        $rows[] = array('Pcs Produced', isset($scrap['PCS_PRODUCED']) ? $scrap['PCS_PRODUCED'] : 0);
        $rows[] = array('Tool Price', isset($scrap['TOOL_PRICE']) ? $scrap['TOOL_PRICE'] : '');
        $rows[] = array('Tool Residue Value', '');
        $rows[] = array('Cause Remark', isset($scrap['SCRAP_CAUSE_REMARK']) ? $scrap['SCRAP_CAUSE_REMARK'] : '');
        $rows[] = array('Reason', isset($scrap['REASON']) ? $scrap['REASON'] : '');
        $rows[] = array('Cause', isset($scrap['CAUSE_NAME']) ? $scrap['CAUSE_NAME'] : '');
        $rows[] = array('Counter Measure', isset($scrap['SCRAP_COUNTER_MEASURE']) ? $scrap['SCRAP_COUNTER_MEASURE'] : '');
        $rows[] = array('Suggestion', 'Scrap');
        $rows[] = array('Approve By', isset($scrap['APPROVED_BY_NAME']) ? $scrap['APPROVED_BY_NAME'] : '');
        $rows[] = array('Approve Date', $this->format_date_report(isset($scrap['SCRAP_APPROVED_DATE']) ? $scrap['SCRAP_APPROVED_DATE'] : ''));
        $rows[] = array('To Order', isset($scrap['SCRAP_TO_ORDER']) && $scrap['SCRAP_TO_ORDER'] ? 'Yes' : 'No');
        $rows[] = array('Investigated By', isset($scrap['INVESTIGATED_BY_NAME']) ? $scrap['INVESTIGATED_BY_NAME'] : '');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        // Add UTF-8 BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    /**
     * Export to XML
     */
    private function export_xml($data)
    {
        $scrap = $data['scrap'];
        $filename = 'Tool_Scrap_Report_' . (isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '') . '.xml';
        
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $xml = new SimpleXMLElement('<ToolScrapReport/>');
        $xml->addChild('Company', 'PT. TD AUTOMOTIVE COMPRESSOR INDONESIA');
        $xml->addChild('ReportTitle', 'TOOL ACCIDENT/ SCRAP');
        $xml->addChild('PrintedAt', date('d-m-Y H:i'));
        
        $record = $xml->addChild('ScrapRecord');
        $record->addChild('ScrapNo', htmlspecialchars(isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '', ENT_XML1, 'UTF-8'));
        $record->addChild('IssueDate', $this->format_date_report(isset($scrap['SCRAP_DATE']) ? $scrap['SCRAP_DATE'] : ''));
        $record->addChild('AccScrapDate', $this->format_date_report(isset($scrap['SCRAP_ACC_DATE']) ? $scrap['SCRAP_ACC_DATE'] : ''));
        $record->addChild('Machine', htmlspecialchars(isset($scrap['MACHINE']) ? $scrap['MACHINE'] : '', ENT_XML1, 'UTF-8'));
        $record->addChild('RequestBy', htmlspecialchars(isset($scrap['REQUESTED_BY_NAME']) ? $scrap['REQUESTED_BY_NAME'] : '', ENT_XML1, 'UTF-8'));
        $record->addChild('Operator', htmlspecialchars(isset($scrap['OPERATOR_NAME']) ? $scrap['OPERATOR_NAME'] : '', ENT_XML1, 'UTF-8'));
        $record->addChild('ToolID', htmlspecialchars(isset($scrap['INV_TOOL_ID']) ? $scrap['INV_TOOL_ID'] : '', ENT_XML1, 'UTF-8'));
        $record->addChild('ToolName', htmlspecialchars(isset($scrap['TOOL_NAME']) ? $scrap['TOOL_NAME'] : '', ENT_XML1, 'UTF-8'));
        $record->addChild('StdQty', isset($scrap['SCRAP_STD_QTY_THIS']) ? (int)$scrap['SCRAP_STD_QTY_THIS'] : 0);
        $record->addChild('CurrentQty', isset($scrap['SCRAP_CURRENT_QTY_THIS']) ? (int)$scrap['SCRAP_CURRENT_QTY_THIS'] : 0);
        $record->addChild('NotReceived', isset($scrap['SCRAP_NRCV_QTY_THIS']) ? (int)$scrap['SCRAP_NRCV_QTY_THIS'] : 0);
        $record->addChild('PcsProduced', isset($scrap['PCS_PRODUCED']) ? (int)$scrap['PCS_PRODUCED'] : 0);
        $record->addChild('ToolPrice', isset($scrap['TOOL_PRICE']) ? (float)$scrap['TOOL_PRICE'] : 0);
        $record->addChild('ToolResidueValue', '');
        $record->addChild('CauseRemark', htmlspecialchars(isset($scrap['SCRAP_CAUSE_REMARK']) ? $scrap['SCRAP_CAUSE_REMARK'] : '', ENT_XML1, 'UTF-8'));
        $record->addChild('Reason', htmlspecialchars(isset($scrap['REASON']) ? $scrap['REASON'] : '', ENT_XML1, 'UTF-8'));
        $record->addChild('Cause', htmlspecialchars(isset($scrap['CAUSE_NAME']) ? $scrap['CAUSE_NAME'] : '', ENT_XML1, 'UTF-8'));
        $record->addChild('CounterMeasure', htmlspecialchars(isset($scrap['SCRAP_COUNTER_MEASURE']) ? $scrap['SCRAP_COUNTER_MEASURE'] : '', ENT_XML1, 'UTF-8'));
        $record->addChild('Suggestion', 'Scrap');
        $record->addChild('ApproveBy', htmlspecialchars(isset($scrap['APPROVED_BY_NAME']) ? $scrap['APPROVED_BY_NAME'] : '', ENT_XML1, 'UTF-8'));
        $record->addChild('ApproveDate', $this->format_date_report(isset($scrap['SCRAP_APPROVED_DATE']) ? $scrap['SCRAP_APPROVED_DATE'] : ''));
        $record->addChild('ToOrder', isset($scrap['SCRAP_TO_ORDER']) && $scrap['SCRAP_TO_ORDER'] ? 'Yes' : 'No');
        $record->addChild('InvestigatedBy', htmlspecialchars(isset($scrap['INVESTIGATED_BY_NAME']) ? $scrap['INVESTIGATED_BY_NAME'] : '', ENT_XML1, 'UTF-8'));

        echo $xml->asXML();
        exit;
    }

    /**
     * Export to MHTML
     */
    private function export_mhtml($data)
    {
        $scrap = $data['scrap'];
        $filename = 'Tool_Scrap_Report_' . (isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '') . '.mhtml';

        $html = $this->load->view('report_tool_scrap', $data, TRUE);
        $html = preg_replace('/<div class="action-buttons[^"]*">.*?<\/div>/is', '', $html);
        $html = preg_replace('/<script>.*?<\/script>/is', '', $html);

        $boundary = '----=_NextPart_000_0000_01D' . uniqid();
        $mhtml = "MIME-Version: 1.0\n";
        $mhtml .= "Content-Type: multipart/related; boundary=\"$boundary\"\n\n";
        $mhtml .= "--$boundary\n";
        $mhtml .= "Content-Type: text/html; charset=utf-8\n";
        $mhtml .= "Content-Transfer-Encoding: 7bit\n\n";
        $mhtml .= $html . "\n";
        $mhtml .= "--$boundary--\n";

        header('Content-Type: message/rfc822');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo $mhtml;
        exit;
    }

    /**
     * Export to TIFF (using HTML to image conversion - requires external service or library)
     */
    private function export_tiff($data)
    {
        $scrap = $data['scrap'];
        $filename = 'Tool_Scrap_Report_' . (isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '') . '.tiff';

        // Note: TIFF conversion typically requires image processing library
        // For now, we'll export as PNG and suggest conversion, or use HTML canvas approach
        // Alternative: Use wkhtmltopdf or similar tool to convert HTML to image
        
        $html = $this->load->view('report_tool_scrap', $data, TRUE);
        $html = preg_replace('/<div class="action-buttons[^"]*">.*?<\/div>/is', '', $html);
        $html = preg_replace('/<script>.*?<\/script>/is', '', $html);
        
        // For TIFF, we'll provide HTML that can be converted via browser print to TIFF
        // Or use a service/library that converts HTML to TIFF
        // For now, export as HTML with instructions, or convert to PNG
        
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><title>Export to TIFF</title></head><body>';
        echo '<p>TIFF export requires image conversion. Please use Print to PDF and convert to TIFF, or use a conversion tool.</p>';
        echo '<p>Alternatively, you can use the browser print function and save as TIFF.</p>';
        echo '<button onclick="window.print();">Print to Save as TIFF</button>';
        echo $html;
        echo '</body></html>';
        exit;
    }

    /**
     * Generate Word content
     */
    private function generate_word_content($data)
    {
        $scrap = $data['scrap'];
        
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" ';
        $html .= 'xmlns:w="urn:schemas-microsoft-com:office:word" ';
        $html .= 'xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta charset="utf-8"><title>Tool Scrap Report</title></head>';
        $html .= '<body>';
        $html .= '<div style="text-align: center;"><h2>PT. TD AUTOMOTIVE COMPRESSOR INDONESIA</h2>';
        $html .= '<h3>TOOL ACCIDENT/ SCRAP</h3></div>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse;">';
        
        $rows = array(
            array('Scrap No.', isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '', 'Acc/ Scrap Date', $this->format_date_report(isset($scrap['SCRAP_ACC_DATE']) ? $scrap['SCRAP_ACC_DATE'] : '')),
            array('Issue Date', $this->format_date_report(isset($scrap['SCRAP_DATE']) ? $scrap['SCRAP_DATE'] : ''), 'Machine', isset($scrap['MACHINE']) ? $scrap['MACHINE'] : ''),
            array('Request By', isset($scrap['REQUESTED_BY_NAME']) ? $scrap['REQUESTED_BY_NAME'] : '', 'Operator', isset($scrap['OPERATOR_NAME']) ? $scrap['OPERATOR_NAME'] : ''),
            array('Tool ID', isset($scrap['INV_TOOL_ID']) ? $scrap['INV_TOOL_ID'] : '', 'Std Qty', isset($scrap['SCRAP_STD_QTY_THIS']) ? $scrap['SCRAP_STD_QTY_THIS'] : 0),
            array('Tool Name', isset($scrap['TOOL_NAME']) ? $scrap['TOOL_NAME'] : '', 'Current Qty', isset($scrap['SCRAP_CURRENT_QTY_THIS']) ? $scrap['SCRAP_CURRENT_QTY_THIS'] : 0),
            array('Tool Price', isset($scrap['TOOL_PRICE']) ? number_format((float)$scrap['TOOL_PRICE'], 2) : '', 'Not Received', isset($scrap['SCRAP_NRCV_QTY_THIS']) ? $scrap['SCRAP_NRCV_QTY_THIS'] : 0),
            array('Tool Residue Value', '', 'Pcs Produced', isset($scrap['PCS_PRODUCED']) ? $scrap['PCS_PRODUCED'] : 0),
        );
        
        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td style="font-weight:bold; background-color:#f5f5f5;">' . htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td style="font-weight:bold; background-color:#f5f5f5;">' . htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($row[3], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        $html .= '<p><strong>Cause Remark</strong></p>';
        $html .= '<div style="border:1px solid #000; padding:5px; min-height:60px;">' . nl2br(htmlspecialchars(isset($scrap['SCRAP_CAUSE_REMARK']) ? $scrap['SCRAP_CAUSE_REMARK'] : '', ENT_QUOTES, 'UTF-8')) . '</div>';
        
        $html .= '<p><strong>Sketch</strong></p>';
        $html .= '<div style="border:1px solid #000; padding:5px; min-height:150px;"></div>';
        
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:20px;">';
        $rows2 = array(
            array('Suggestion', 'Scrap', 'Approve By', isset($scrap['APPROVED_BY_NAME']) ? $scrap['APPROVED_BY_NAME'] : ''),
            array('To Order', isset($scrap['SCRAP_TO_ORDER']) && $scrap['SCRAP_TO_ORDER'] ? 'Yes' : 'No', 'Date', $this->format_date_report(isset($scrap['SCRAP_APPROVED_DATE']) ? $scrap['SCRAP_APPROVED_DATE'] : '')),
            array('Reason', isset($scrap['REASON']) ? $scrap['REASON'] : '', 'Cause', isset($scrap['CAUSE_NAME']) ? $scrap['CAUSE_NAME'] : ''),
        );
        
        foreach ($rows2 as $row) {
            $html .= '<tr>';
            $html .= '<td style="font-weight:bold; background-color:#f5f5f5;">' . htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td style="font-weight:bold; background-color:#f5f5f5;">' . htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($row[3], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        $html .= '<p><strong>Counter Measure</strong></p>';
        $html .= '<div style="border:1px solid #000; padding:5px; min-height:60px;">' . nl2br(htmlspecialchars(isset($scrap['SCRAP_COUNTER_MEASURE']) ? $scrap['SCRAP_COUNTER_MEASURE'] : '', ENT_QUOTES, 'UTF-8')) . '</div>';
        
        $html .= '<p style="margin-top:30px; text-align:right;"><strong>Investigated By:</strong> ' . htmlspecialchars(isset($scrap['INVESTIGATED_BY_NAME']) ? $scrap['INVESTIGATED_BY_NAME'] : '', ENT_QUOTES, 'UTF-8') . '</p>';
        
        $html .= '</body></html>';
        
        return $html;
    }

    /**
     * Generate Excel content
     */
    private function generate_excel_content($data)
    {
        $scrap = $data['scrap'];
        
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" ';
        $html .= 'xmlns:x="urn:schemas-microsoft-com:office:excel" ';
        $html .= 'xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta charset="utf-8"><title>Tool Scrap Report</title>';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Sheet1</x:Name></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $html .= '</head><body>';
        
        $html .= '<div style="text-align: center;"><h2>PT. TD AUTOMOTIVE COMPRESSOR INDONESIA</h2>';
        $html .= '<h3>TOOL ACCIDENT/ SCRAP</h3></div>';
        
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse;">';
        
        $rows = array(
            array('Scrap No.', isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '', 'Acc/ Scrap Date', $this->format_date_report(isset($scrap['SCRAP_ACC_DATE']) ? $scrap['SCRAP_ACC_DATE'] : '')),
            array('Issue Date', $this->format_date_report(isset($scrap['SCRAP_DATE']) ? $scrap['SCRAP_DATE'] : ''), 'Machine', isset($scrap['MACHINE']) ? $scrap['MACHINE'] : ''),
            array('Request By', isset($scrap['REQUESTED_BY_NAME']) ? $scrap['REQUESTED_BY_NAME'] : '', 'Operator', isset($scrap['OPERATOR_NAME']) ? $scrap['OPERATOR_NAME'] : ''),
            array('Tool ID', isset($scrap['INV_TOOL_ID']) ? $scrap['INV_TOOL_ID'] : '', 'Std Qty', isset($scrap['SCRAP_STD_QTY_THIS']) ? $scrap['SCRAP_STD_QTY_THIS'] : 0),
            array('Tool Name', isset($scrap['TOOL_NAME']) ? $scrap['TOOL_NAME'] : '', 'Current Qty', isset($scrap['SCRAP_CURRENT_QTY_THIS']) ? $scrap['SCRAP_CURRENT_QTY_THIS'] : 0),
            array('Tool Price', isset($scrap['TOOL_PRICE']) ? number_format((float)$scrap['TOOL_PRICE'], 2) : '', 'Not Received', isset($scrap['SCRAP_NRCV_QTY_THIS']) ? $scrap['SCRAP_NRCV_QTY_THIS'] : 0),
            array('Tool Residue Value', '', 'Pcs Produced', isset($scrap['PCS_PRODUCED']) ? $scrap['PCS_PRODUCED'] : 0),
        );
        
        foreach ($rows as $row) {
            $html .= '<tr>';
            $html .= '<td style="font-weight:bold; background-color:#f5f5f5;">' . htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td style="font-weight:bold; background-color:#f5f5f5;">' . htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($row[3], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        $html .= '<p><strong>Cause Remark</strong></p>';
        $html .= '<div style="border:1px solid #000; padding:5px; min-height:60px;">' . nl2br(htmlspecialchars(isset($scrap['SCRAP_CAUSE_REMARK']) ? $scrap['SCRAP_CAUSE_REMARK'] : '', ENT_QUOTES, 'UTF-8')) . '</div>';
        
        $html .= '<p><strong>Sketch</strong></p>';
        $html .= '<div style="border:1px solid #000; padding:5px; min-height:150px;"></div>';
        
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse; margin-top:20px;">';
        $rows2 = array(
            array('Suggestion', 'Scrap', 'Approve By', isset($scrap['APPROVED_BY_NAME']) ? $scrap['APPROVED_BY_NAME'] : ''),
            array('To Order', isset($scrap['SCRAP_TO_ORDER']) && $scrap['SCRAP_TO_ORDER'] ? 'Yes' : 'No', 'Date', $this->format_date_report(isset($scrap['SCRAP_APPROVED_DATE']) ? $scrap['SCRAP_APPROVED_DATE'] : '')),
            array('Reason', isset($scrap['REASON']) ? $scrap['REASON'] : '', 'Cause', isset($scrap['CAUSE_NAME']) ? $scrap['CAUSE_NAME'] : ''),
        );
        
        foreach ($rows2 as $row) {
            $html .= '<tr>';
            $html .= '<td style="font-weight:bold; background-color:#f5f5f5;">' . htmlspecialchars($row[0], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($row[1], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td style="font-weight:bold; background-color:#f5f5f5;">' . htmlspecialchars($row[2], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($row[3], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        $html .= '<p><strong>Counter Measure</strong></p>';
        $html .= '<div style="border:1px solid #000; padding:5px; min-height:60px;">' . nl2br(htmlspecialchars(isset($scrap['SCRAP_COUNTER_MEASURE']) ? $scrap['SCRAP_COUNTER_MEASURE'] : '', ENT_QUOTES, 'UTF-8')) . '</div>';
        
        $html .= '<p style="margin-top:30px; text-align:right;"><strong>Investigated By:</strong> ' . htmlspecialchars(isset($scrap['INVESTIGATED_BY_NAME']) ? $scrap['INVESTIGATED_BY_NAME'] : '', ENT_QUOTES, 'UTF-8') . '</p>';
        
        $html .= '</body></html>';
        
        return $html;
    }
}

