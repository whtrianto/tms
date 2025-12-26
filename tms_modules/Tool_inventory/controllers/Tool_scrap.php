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
            $order_column_raw = isset($order[0]['column']) ? (int)$order[0]['column'] : 1;
            $order_dir = isset($order[0]['dir']) ? $order[0]['dir'] : 'desc';
            
            // Adjust order_column: Action is now at index 0 (non-sortable), so subtract 1 for other columns
            // If order_column is 0 (Action), use default (0 = ID)
            $order_column = ($order_column_raw == 0) ? 0 : ($order_column_raw - 1);

            $columns = $this->input->post('columns');
            $column_search = array();
            if (is_array($columns)) {
                foreach ($columns as $idx => $col) {
                    if ($idx == 0) continue; // Skip Action column (index 0)
                    if (isset($col['search']['value']) && $col['search']['value'] !== '') {
                        $column_search[$idx - 1] = $col['search']['value']; // Adjust index
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
                    '<a href="' . $report_url . '" class="btn btn-info btn-sm" title="Report">Report</a>' .
                    '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $id . '" data-scrap-no="' . $scrap_no . '">Del</button> ' .
                    '</div>';

                $formatted_data[] = array(
                    $action_html,
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
                    htmlspecialchars(isset($row['SUGGESTION']) ? $row['SUGGESTION'] : '', ENT_QUOTES, 'UTF-8')
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
        $data['users_modal'] = $this->tool_scrap->get_users_for_modal();
        $data['machines_modal'] = $this->tool_scrap->get_machines_for_modal();
        $data['reasons_modal'] = $this->tool_scrap->get_reasons_for_modal();
        $data['causes_modal'] = $this->tool_scrap->get_causes_for_modal();
        $data['tool_inventory_modal'] = $this->tool_scrap->get_tool_inventory_for_modal();
        $data['materials'] = $this->tool_scrap->get_materials();
        $data['rq_numbers'] = $this->tool_scrap->get_rq_numbers();
        $data['next_scrap_no'] = $this->tool_scrap->get_next_scrap_no();
        $this->view('add_tool_scrap', $data, FALSE);
    }

    /**
     * Get tool inventory details by Tool ID (AJAX)
     */
    public function get_tool_inventory_details()
    {
        if (ob_get_level()) ob_clean();
        $this->output->set_content_type('application/json', 'UTF-8');

        try {
            $tool_id = $this->input->post('tool_id', TRUE);
            $tool_id = trim((string)$tool_id);
            
            if (empty($tool_id)) {
                echo json_encode(array('success' => false, 'message' => 'Tool ID tidak boleh kosong.'));
                return;
            }

            log_message('debug', '[Tool_scrap::get_tool_inventory_details] Requested Tool ID: [' . $tool_id . '] (length: ' . strlen($tool_id) . ', type: ' . gettype($tool_id) . ')');

            $details = $this->tool_scrap->get_tool_inventory_details_by_tool_id($tool_id);
            if ($details) {
                log_message('debug', '[Tool_scrap::get_tool_inventory_details] Data found for Tool ID: [' . $tool_id . ']');
                echo json_encode(array('success' => true, 'data' => $details), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } else {
                log_message('debug', '[Tool_scrap::get_tool_inventory_details] No data found for Tool ID: [' . $tool_id . ']');
                echo json_encode(array('success' => false, 'message' => 'Tool ID tidak ditemukan di database. Tool ID yang dicari: [' . $tool_id . ']'));
            }
        } catch (Exception $e) {
            log_message('error', '[Tool_scrap::get_tool_inventory_details] Exception: ' . $e->getMessage());
            echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
        }
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
        $allowed_formats = array('pdf', 'word', 'excel', 'csv', 'xml', 'html', 'tiff');
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
            case 'html':
                $this->export_html($data);
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
        
        // Generate HTML content directly (no template dependency)
        $html = $this->generate_html_content($data);
        
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
     * Export to Excel (XLS) - Using SpreadsheetML format
     */
    private function export_excel($data)
    {
        $scrap = $data['scrap'];
        $filename = 'Tool_Scrap_Report_' . (isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '') . '.xls';

        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Content-Transfer-Encoding: binary');

        // Output BOM for UTF-8 Excel compatibility
        echo "\xEF\xBB\xBF";

        // Start Excel XML output (SpreadsheetML format)
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        echo '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">' . "\n";
        echo '<Title>Tool Scrap Report</Title>' . "\n";
        echo '<Created>' . date('Y-m-d\TH:i:s\Z') . '</Created>' . "\n";
        echo '</DocumentProperties>' . "\n";
        
        // Styles must be defined before Worksheet
        echo '<Styles>' . "\n";
        echo '<Style ss:ID="Header">' . "\n";
        echo '<Font ss:Bold="1"/>' . "\n";
        echo '<Interior ss:Color="#D3D3D3" ss:Pattern="Solid"/>' . "\n";
        echo '<Borders>' . "\n";
        echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        echo '</Borders>' . "\n";
        echo '</Style>' . "\n";
        echo '<Style ss:ID="Label">' . "\n";
        echo '<Font ss:Bold="1"/>' . "\n";
        echo '<Interior ss:Color="#F5F5F5" ss:Pattern="Solid"/>' . "\n";
        echo '<Borders>' . "\n";
        echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        echo '</Borders>' . "\n";
        echo '</Style>' . "\n";
        echo '<Style ss:ID="Value">' . "\n";
        echo '<Alignment ss:Vertical="Top" ss:WrapText="1"/>' . "\n";
        echo '<Borders>' . "\n";
        echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>' . "\n";
        echo '</Borders>' . "\n";
        echo '</Style>' . "\n";
        echo '</Styles>' . "\n";
        
        echo '<Worksheet ss:Name="Tool Scrap Report">' . "\n";
        echo '<Table>' . "\n";
        // Set column widths (4 columns: Label, Value, Label, Value)
        echo '<Column ss:Width="120"/>' . "\n"; // Label column 1
        echo '<Column ss:Width="150"/>' . "\n"; // Value column 1
        echo '<Column ss:Width="120"/>' . "\n"; // Label column 2
        echo '<Column ss:Width="150"/>' . "\n"; // Value column 2

        // Header
        echo '<Row>' . "\n";
        echo '<Cell><Data ss:Type="String">PT. TD AUTOMOTIVE COMPRESSOR INDONESIA</Data></Cell>' . "\n";
        echo '</Row>' . "\n";
        echo '<Row>' . "\n";
        echo '<Cell><Data ss:Type="String">TOOL ACCIDENT/ SCRAP</Data></Cell>' . "\n";
        echo '</Row>' . "\n";
        echo '<Row>' . "\n";
        echo '<Cell><Data ss:Type="String">Printed at : ' . date('d-m-Y H:i') . '</Data></Cell>' . "\n";
        echo '</Row>' . "\n";
        echo '<Row></Row>' . "\n";

        // First table - same format as PDF (4 columns: Label | Value | Label | Value)
        $rows1 = array(
            array('Scrap No.', isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '', 'Acc/ Scrap Date', $this->format_date_report(isset($scrap['SCRAP_ACC_DATE']) ? $scrap['SCRAP_ACC_DATE'] : '')),
            array('Issue Date', $this->format_date_report(isset($scrap['SCRAP_DATE']) ? $scrap['SCRAP_DATE'] : ''), 'Machine', isset($scrap['MACHINE']) ? $scrap['MACHINE'] : ''),
            array('Request By', isset($scrap['REQUESTED_BY_NAME']) ? $scrap['REQUESTED_BY_NAME'] : '', 'Operator', isset($scrap['OPERATOR_NAME']) ? $scrap['OPERATOR_NAME'] : ''),
            array('Tool ID', isset($scrap['INV_TOOL_ID']) ? $scrap['INV_TOOL_ID'] : '', 'Std Qty', isset($scrap['SCRAP_STD_QTY_THIS']) ? number_format((int)$scrap['SCRAP_STD_QTY_THIS'], 0, ',', '.') : '0'),
            array('Tool Name', isset($scrap['TOOL_NAME']) ? $scrap['TOOL_NAME'] : '', 'Current Qty', isset($scrap['SCRAP_CURRENT_QTY_THIS']) ? number_format((int)$scrap['SCRAP_CURRENT_QTY_THIS'], 0, ',', '.') : '0'),
            array('Tool Price', isset($scrap['TOOL_PRICE']) && $scrap['TOOL_PRICE'] !== null ? number_format((float)$scrap['TOOL_PRICE'], 2, ',', '.') : '', 'Not Received', isset($scrap['SCRAP_NRCV_QTY_THIS']) ? number_format((int)$scrap['SCRAP_NRCV_QTY_THIS'], 0, ',', '.') : '0'),
            array('Tool Residue Value', '', 'Pcs Produced', isset($scrap['PCS_PRODUCED']) ? number_format((int)$scrap['PCS_PRODUCED'], 0, ',', '.') : '0'),
        );

        foreach ($rows1 as $row) {
            echo '<Row ss:Height="20">' . "\n";
            echo '<Cell ss:StyleID="Label"><Data ss:Type="String">' . htmlspecialchars($row[0], ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell ss:StyleID="Value"><Data ss:Type="String">' . htmlspecialchars($row[1], ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell ss:StyleID="Label"><Data ss:Type="String">' . htmlspecialchars($row[2], ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell ss:StyleID="Value"><Data ss:Type="String">' . htmlspecialchars($row[3], ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '</Row>' . "\n";
        }

        echo '<Row></Row>' . "\n";
        echo '<Row ss:Height="60">' . "\n";
        echo '<Cell ss:StyleID="Label"><Data ss:Type="String">Cause Remark</Data></Cell>' . "\n";
        echo '<Cell ss:MergeAcross="3" ss:StyleID="Value"><Data ss:Type="String">' . htmlspecialchars(isset($scrap['SCRAP_CAUSE_REMARK']) ? $scrap['SCRAP_CAUSE_REMARK'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
        echo '</Row>' . "\n";

        echo '<Row></Row>' . "\n";
        echo '<Row ss:Height="100">' . "\n";
        echo '<Cell ss:StyleID="Label"><Data ss:Type="String">Sketch</Data></Cell>' . "\n";
        echo '<Cell ss:MergeAcross="3" ss:StyleID="Value"></Cell>' . "\n";
        echo '</Row>' . "\n";

        echo '<Row></Row>' . "\n";

        // Second table - same format as PDF
        $rows2 = array(
            array('Suggestion', 'Scrap', 'Approve By', isset($scrap['APPROVED_BY_NAME']) ? $scrap['APPROVED_BY_NAME'] : ''),
            array('To Order', isset($scrap['SCRAP_TO_ORDER']) && $scrap['SCRAP_TO_ORDER'] ? 'Yes' : 'No', 'Approve Date', $this->format_date_report(isset($scrap['SCRAP_APPROVED_DATE']) ? $scrap['SCRAP_APPROVED_DATE'] : '')),
            array('Reason', isset($scrap['REASON']) ? $scrap['REASON'] : '', 'Cause', isset($scrap['CAUSE_NAME']) ? $scrap['CAUSE_NAME'] : ''),
        );

        foreach ($rows2 as $row) {
            echo '<Row ss:Height="20">' . "\n";
            echo '<Cell ss:StyleID="Label"><Data ss:Type="String">' . htmlspecialchars($row[0], ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell ss:StyleID="Value"><Data ss:Type="String">' . htmlspecialchars($row[1], ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell ss:StyleID="Label"><Data ss:Type="String">' . htmlspecialchars($row[2], ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell ss:StyleID="Value"><Data ss:Type="String">' . htmlspecialchars($row[3], ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '</Row>' . "\n";
        }

        echo '<Row></Row>' . "\n";
        echo '<Row ss:Height="60">' . "\n";
        echo '<Cell ss:StyleID="Label"><Data ss:Type="String">Counter Measure</Data></Cell>' . "\n";
        echo '<Cell ss:MergeAcross="3" ss:StyleID="Value"><Data ss:Type="String">' . htmlspecialchars(isset($scrap['SCRAP_COUNTER_MEASURE']) ? $scrap['SCRAP_COUNTER_MEASURE'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
        echo '</Row>' . "\n";

        echo '<Row></Row>' . "\n";
        echo '<Row ss:Height="30">' . "\n";
        echo '<Cell></Cell>' . "\n";
        echo '<Cell></Cell>' . "\n";
        echo '<Cell ss:StyleID="Label"><Data ss:Type="String">Investigated By</Data></Cell>' . "\n";
        echo '<Cell ss:StyleID="Value"><Data ss:Type="String">' . htmlspecialchars(isset($scrap['INVESTIGATED_BY_NAME']) ? $scrap['INVESTIGATED_BY_NAME'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
        echo '</Row>' . "\n";

        // Close Table and Worksheet
        echo '</Table>' . "\n";
        echo '</Worksheet>' . "\n";
        
        // Close Workbook
        echo '</Workbook>';
        exit;
    }

    /**
     * Export to CSV
     */
    private function export_csv($data)
    {
        $scrap = $data['scrap'];
        $filename = 'Tool_Scrap_Report_' . (isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '') . '.csv';
        
        // Header row
        $rows = array();
        $rows[] = array('Field', 'Value');
        
        // Data rows in organized order
        $fields = array(
            'Scrap No.' => isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '',
            'Issue Date' => $this->format_date_report(isset($scrap['SCRAP_DATE']) ? $scrap['SCRAP_DATE'] : ''),
            'Acc/ Scrap Date' => $this->format_date_report(isset($scrap['SCRAP_ACC_DATE']) ? $scrap['SCRAP_ACC_DATE'] : ''),
            'Machine' => isset($scrap['MACHINE']) ? $scrap['MACHINE'] : '',
            'Request By' => isset($scrap['REQUESTED_BY_NAME']) ? $scrap['REQUESTED_BY_NAME'] : '',
            'Operator' => isset($scrap['OPERATOR_NAME']) ? $scrap['OPERATOR_NAME'] : '',
            'Tool ID' => isset($scrap['INV_TOOL_ID']) ? $scrap['INV_TOOL_ID'] : '',
            'Tool Name' => isset($scrap['TOOL_NAME']) ? $scrap['TOOL_NAME'] : '',
            'Std Qty' => isset($scrap['SCRAP_STD_QTY_THIS']) ? number_format((int)$scrap['SCRAP_STD_QTY_THIS'], 0, ',', '.') : '0',
            'Current Qty' => isset($scrap['SCRAP_CURRENT_QTY_THIS']) ? number_format((int)$scrap['SCRAP_CURRENT_QTY_THIS'], 0, ',', '.') : '0',
            'Not Received' => isset($scrap['SCRAP_NRCV_QTY_THIS']) ? number_format((int)$scrap['SCRAP_NRCV_QTY_THIS'], 0, ',', '.') : '0',
            'Pcs Produced' => isset($scrap['PCS_PRODUCED']) ? number_format((int)$scrap['PCS_PRODUCED'], 0, ',', '.') : '0',
            'Tool Price' => isset($scrap['TOOL_PRICE']) && $scrap['TOOL_PRICE'] !== null ? number_format((float)$scrap['TOOL_PRICE'], 2, ',', '.') : '',
            'Tool Residue Value' => '',
            'Cause Remark' => isset($scrap['SCRAP_CAUSE_REMARK']) ? $scrap['SCRAP_CAUSE_REMARK'] : '',
            'Suggestion' => 'Scrap',
            'Approve By' => isset($scrap['APPROVED_BY_NAME']) ? $scrap['APPROVED_BY_NAME'] : '',
            'Approve Date' => $this->format_date_report(isset($scrap['SCRAP_APPROVED_DATE']) ? $scrap['SCRAP_APPROVED_DATE'] : ''),
            'To Order' => isset($scrap['SCRAP_TO_ORDER']) && $scrap['SCRAP_TO_ORDER'] ? 'Yes' : 'No',
            'Reason' => isset($scrap['REASON']) ? $scrap['REASON'] : '',
            'Cause' => isset($scrap['CAUSE_NAME']) ? $scrap['CAUSE_NAME'] : '',
            'Counter Measure' => isset($scrap['SCRAP_COUNTER_MEASURE']) ? $scrap['SCRAP_COUNTER_MEASURE'] : '',
            'Investigated By' => isset($scrap['INVESTIGATED_BY_NAME']) ? $scrap['INVESTIGATED_BY_NAME'] : '',
        );

        foreach ($fields as $label => $value) {
            $rows[] = array($label, $value);
        }

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
     * Export to HTML
     */
    private function export_html($data)
    {
        $scrap = $data['scrap'];
        $filename = 'Tool_Scrap_Report_' . (isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '') . '.html';

        // Generate HTML content directly (no template dependency)
        $html = $this->generate_html_content($data);

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo $html;
        exit;
    }

    /**
     * Export to TIFF (using HTML to image conversion - requires external service or library)
     */
    private function export_tiff($data)
    {
        $scrap = $data['scrap'];
        $filename = 'Tool_Scrap_Report_' . (isset($scrap['SCRAP_NO']) ? $scrap['SCRAP_NO'] : '') . '.tiff';

        // Generate HTML content directly (no template dependency)
        $html = $this->generate_html_content($data);
        
        // For TIFF, we'll provide HTML that can be converted via browser print to TIFF
        // Or use a service/library that converts HTML to TIFF
        // For now, export as HTML with instructions, or convert to PNG
        
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Export to TIFF</title>';
        echo '<style>';
        echo 'body { font-family: Arial, sans-serif; padding: 20px; }';
        echo '.instructions { background-color: #f0f0f0; padding: 15px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; }';
        echo '.instructions p { margin: 5px 0; }';
        echo '.instructions button { background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }';
        echo '.instructions button:hover { background-color: #0056b3; }';
        echo '</style>';
        echo '</head><body>';
        echo '<div class="instructions">';
        echo '<p><strong>TIFF Export Instructions:</strong></p>';
        echo '<p>TIFF export requires image conversion. You can:</p>';
        echo '<ul>';
        echo '<li>Use the browser print function (Ctrl+P / Cmd+P) and select "Save as PDF", then convert PDF to TIFF using a conversion tool</li>';
        echo '<li>Use an online HTML to TIFF converter</li>';
        echo '<li>Use specialized software like wkhtmltopdf or similar tools</li>';
        echo '</ul>';
        echo '<button onclick="window.print();">Print / Save as PDF</button>';
        echo '</div>';
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

    /**
     * Generate HTML content for export (PDF, MHTML, TIFF)
     * This method generates HTML without template dependencies
     */
    private function generate_html_content($data)
    {
        $scrap = $data['scrap'];
        
        // Format dates helper
        $formatDate = function($date) {
            if (empty($date) || $date === null) return '';
            try {
                $d = new DateTime($date);
                return $d->format('d-m-Y');
            } catch (Exception $e) {
                return $date;
            }
        };
        
        // Prepare data
        $scrap_no = isset($scrap['SCRAP_NO']) ? htmlspecialchars($scrap['SCRAP_NO'], ENT_QUOTES, 'UTF-8') : '';
        $issue_date = $formatDate(isset($scrap['SCRAP_DATE']) ? $scrap['SCRAP_DATE'] : '');
        $acc_scrap_date = $formatDate(isset($scrap['SCRAP_ACC_DATE']) ? $scrap['SCRAP_ACC_DATE'] : '');
        $machine = isset($scrap['MACHINE']) ? htmlspecialchars($scrap['MACHINE'], ENT_QUOTES, 'UTF-8') : '';
        $request_by = isset($scrap['REQUESTED_BY_NAME']) ? htmlspecialchars($scrap['REQUESTED_BY_NAME'], ENT_QUOTES, 'UTF-8') : '';
        $operator = isset($scrap['OPERATOR_NAME']) ? htmlspecialchars($scrap['OPERATOR_NAME'], ENT_QUOTES, 'UTF-8') : '';
        $tool_id = isset($scrap['INV_TOOL_ID']) ? htmlspecialchars($scrap['INV_TOOL_ID'], ENT_QUOTES, 'UTF-8') : '';
        $std_qty = isset($scrap['SCRAP_STD_QTY_THIS']) ? (int)$scrap['SCRAP_STD_QTY_THIS'] : 0;
        $current_qty = isset($scrap['SCRAP_CURRENT_QTY_THIS']) ? (int)$scrap['SCRAP_CURRENT_QTY_THIS'] : 0;
        $not_received = isset($scrap['SCRAP_NRCV_QTY_THIS']) ? (int)$scrap['SCRAP_NRCV_QTY_THIS'] : 0;
        $pcs_produced = isset($scrap['PCS_PRODUCED']) ? (int)$scrap['PCS_PRODUCED'] : 0;
        $tool_name = isset($scrap['TOOL_NAME']) ? htmlspecialchars($scrap['TOOL_NAME'], ENT_QUOTES, 'UTF-8') : '';
        $tool_price = isset($scrap['TOOL_PRICE']) && $scrap['TOOL_PRICE'] !== null ? number_format((float)$scrap['TOOL_PRICE'], 2, ',', '.') : '';
        $tool_residue_value = '';
        $cause_remark = isset($scrap['SCRAP_CAUSE_REMARK']) ? htmlspecialchars($scrap['SCRAP_CAUSE_REMARK'], ENT_QUOTES, 'UTF-8') : '';
        $sketch = isset($scrap['SKETCH']) ? htmlspecialchars($scrap['SKETCH'], ENT_QUOTES, 'UTF-8') : '';
        $suggestion = 'Scrap';
        $approve_by = isset($scrap['APPROVED_BY_NAME']) ? htmlspecialchars($scrap['APPROVED_BY_NAME'], ENT_QUOTES, 'UTF-8') : '';
        $approve_date = $formatDate(isset($scrap['SCRAP_APPROVED_DATE']) ? $scrap['SCRAP_APPROVED_DATE'] : '');
        $to_order = isset($scrap['SCRAP_TO_ORDER']) && $scrap['SCRAP_TO_ORDER'] ? 'Yes' : 'No';
        $reason = isset($scrap['REASON']) ? htmlspecialchars($scrap['REASON'], ENT_QUOTES, 'UTF-8') : '';
        $cause = isset($scrap['CAUSE_NAME']) ? htmlspecialchars($scrap['CAUSE_NAME'], ENT_QUOTES, 'UTF-8') : '';
        $counter_measure = isset($scrap['SCRAP_COUNTER_MEASURE']) ? htmlspecialchars($scrap['SCRAP_COUNTER_MEASURE'], ENT_QUOTES, 'UTF-8') : '';
        $investigated_by = isset($scrap['INVESTIGATED_BY_NAME']) ? htmlspecialchars($scrap['INVESTIGATED_BY_NAME'], ENT_QUOTES, 'UTF-8') : '';
        
        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Tool Scrap Report</title>';
        $html .= '<style>';
        $html .= '@media print { body { margin: 0; padding: 0; } .no-print { display: none !important; } .print-break { page-break-after: always; } }';
        $html .= 'body { font-family: Arial, sans-serif; font-size: 11pt; padding: 20px; }';
        $html .= '.report-header { text-align: center; margin-bottom: 20px; }';
        $html .= '.report-header h2 { font-size: 14pt; font-weight: bold; margin: 5px 0; }';
        $html .= '.report-header .printed-date { text-align: right; font-size: 9pt; margin-bottom: 10px; }';
        $html .= '.report-title { text-align: center; font-size: 12pt; font-weight: bold; margin: 20px 0; text-decoration: underline; }';
        $html .= '.report-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; border: 1px solid #000; }';
        $html .= '.report-table td { padding: 8px 12px; vertical-align: middle; border: 1px solid #000; min-height: 35px; }';
        $html .= '.report-table .label { font-weight: bold; width: 20%; background-color: #f5f5f5; text-align: left; padding-left: 15px; }';
        $html .= '.report-table .value { width: 30%; text-align: left; padding-left: 15px; word-wrap: break-word; }';
        $html .= '.report-table tr { height: 40px; }';
        $html .= '.report-table tr:nth-child(even) .label { background-color: #f9f9f9; }';
        $html .= '.report-table tr:nth-child(even) .value { background-color: #ffffff; }';
        $html .= '.report-section { margin-top: 15px; }';
        $html .= '.report-section-label { font-weight: bold; margin-bottom: 5px; }';
        $html .= '.text-area-box { border: 1px solid #000; min-height: 60px; padding: 5px; margin-top: 5px; background-color: #fff; }';
        $html .= '.sketch-box { border: 1px solid #000; min-height: 150px; padding: 5px; margin-top: 5px; background-color: #fff; }';
        $html .= '</style>';
        $html .= '</head><body>';
        
        // Header
        $html .= '<div class="report-header">';
        $html .= '<div class="printed-date">Printed at : ' . date('d-m-Y H:i') . '</div>';
        $html .= '<h2>PT. TD AUTOMOTIVE COMPRESSOR INDONESIA</h2>';
        $html .= '<div class="report-title">TOOL ACCIDENT/ SCRAP</div>';
        $html .= '</div>';
        
        // First table
        $html .= '<table class="report-table">';
        $html .= '<tr><td class="label">Scrap No.</td><td class="value"><strong>' . $scrap_no . '</strong></td><td class="label">Acc/ Scrap Date</td><td class="value">' . $acc_scrap_date . '</td></tr>';
        $html .= '<tr><td class="label">Issue Date</td><td class="value">' . $issue_date . '</td><td class="label">Machine</td><td class="value">' . $machine . '</td></tr>';
        $html .= '<tr><td class="label">Request By</td><td class="value">' . $request_by . '</td><td class="label">Operator</td><td class="value">' . $operator . '</td></tr>';
        $html .= '<tr><td class="label">Tool ID</td><td class="value">' . $tool_id . '</td><td class="label">Std Qty</td><td class="value">' . number_format($std_qty, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td class="label">Tool Name</td><td class="value">' . $tool_name . '</td><td class="label">Current Qty</td><td class="value">' . number_format($current_qty, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td class="label">Tool Price</td><td class="value">' . $tool_price . '</td><td class="label">Not Received</td><td class="value">' . number_format($not_received, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td class="label">Tool Residue Value</td><td class="value">' . $tool_residue_value . '</td><td class="label">Pcs Produced</td><td class="value"><strong>' . number_format($pcs_produced, 0, ',', '.') . '</strong></td></tr>';
        $html .= '</table>';
        
        // Cause Remark
        $html .= '<div class="report-section">';
        $html .= '<div class="report-section-label">Cause Remark</div>';
        $html .= '<div class="text-area-box">' . nl2br($cause_remark) . '</div>';
        $html .= '</div>';
        
        // Sketch
        $html .= '<div class="report-section">';
        $html .= '<div class="report-section-label">Sketch</div>';
        $html .= '<div class="sketch-box">';
        if (!empty($sketch)) {
            $html .= '<img src="' . base_url('uploads/sketch/' . $sketch) . '" alt="Sketch" style="max-width: 100%; height: auto;" />';
        }
        $html .= '</div>';
        $html .= '</div>';
        
        // Second table
        $html .= '<table class="report-table" style="margin-top: 20px;">';
        $html .= '<tr><td class="label">Suggestion</td><td class="value"><strong>' . $suggestion . '</strong></td><td class="label">Approve By</td><td class="value">' . $approve_by . '</td></tr>';
        $html .= '<tr><td class="label">To Order</td><td class="value">' . $to_order . '</td><td class="label">Approve Date</td><td class="value">' . $approve_date . '</td></tr>';
        $html .= '<tr><td class="label">Reason</td><td class="value">' . $reason . '</td><td class="label">Cause</td><td class="value">' . $cause . '</td></tr>';
        $html .= '</table>';
        
        // Counter Measure
        $html .= '<div class="report-section">';
        $html .= '<div class="report-section-label">Counter Measure</div>';
        $html .= '<div class="text-area-box">' . nl2br($counter_measure) . '</div>';
        $html .= '</div>';
        
        // Investigated By
        $html .= '<div class="report-section" style="margin-top: 30px;">';
        $html .= '<div style="text-align: right;">';
        $html .= '<div class="report-section-label" style="text-align: left; display: inline-block;">Investigated By</div>';
        $html .= '<div style="margin-top: 40px; display: inline-block; margin-left: 20px;">' . $investigated_by . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</body></html>';
        
        return $html;
    }
}