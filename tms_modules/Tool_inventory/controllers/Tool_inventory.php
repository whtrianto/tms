<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tool Inventory Controller
 * @property M_tool_inventory $tool_inventory
 */
class Tool_inventory extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('form_validation', 'session'));
        
        $username = $this->session->userdata('username');
        $this->uid = (string)($username ?: 'SYSTEM');

        $this->load->model('M_tool_inventory', 'tool_inventory');
        $this->tool_inventory->uid = $this->uid;

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $this->view('index_tool_inventory', $data, FALSE);
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

            $result = $this->tool_inventory->get_data_serverside($start, $length, $search_value, $order_column, $order_dir, $column_search);

            $formatted_data = array();
            foreach ($result['data'] as $row) {
                $status_badge = $this->tool_inventory->get_status_badge(isset($row['INV_STATUS']) ? $row['INV_STATUS'] : 0);
                
                $tool_condition = isset($row['INV_TOOL_CONDITION']) && $row['INV_TOOL_CONDITION'] !== null 
                    ? (string)$row['INV_TOOL_CONDITION'] 
                    : '';
                
                $end_cycle = isset($row['END_CYCLE']) && $row['END_CYCLE'] !== null 
                    ? (string)$row['END_CYCLE'] 
                    : '0';

                $id = (int)$row['INV_ID'];
                $tool_tag = htmlspecialchars(isset($row['INV_TOOL_TAG']) ? $row['INV_TOOL_TAG'] : '', ENT_QUOTES, 'UTF-8');
                $edit_url = base_url('Tool_inventory/tool_inventory/edit_page/' . $id);
                
                $action_html = '<div class="action-buttons">' .
                    '<a href="' . $edit_url . '" class="btn btn-secondary btn-sm" title="Edit">Edit</a> ' .
                    '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $id . '" data-tool-tag="' . $tool_tag . '">Del</button>' .
                    '</div>';

                $formatted_data[] = array(
                    $id,
                    htmlspecialchars(isset($row['INV_TOOL_TAG']) ? $row['INV_TOOL_TAG'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['RQ_NO']) ? $row['RQ_NO'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['PRODUCT_NAME']) ? $row['PRODUCT_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TOOL_NAME']) ? $row['TOOL_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TOOL_DRAWING_NO']) ? $row['TOOL_DRAWING_NO'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['RECEIVED_DATE']) ? $row['RECEIVED_DATE'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['DO_NO']) ? $row['DO_NO'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['INV_TOOL_ID']) ? $row['INV_TOOL_ID'] : '', ENT_QUOTES, 'UTF-8'),
                    $status_badge,
                    htmlspecialchars(isset($row['NOTES']) ? $row['NOTES'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['STORAGE_LOCATION']) ? $row['STORAGE_LOCATION'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['MATERIAL']) ? $row['MATERIAL'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($tool_condition, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($end_cycle, ENT_QUOTES, 'UTF-8'),
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
            log_message('error', '[Tool_inventory::get_data] Exception: ' . $e->getMessage());
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
     * Delete Tool Inventory
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('INV_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }

        $ok = $this->tool_inventory->delete_data($id);
        echo json_encode(array(
            'success' => $ok,
            'message' => $this->tool_inventory->messages
        ));
    }

    /**
     * Add page
     */
    public function add_page()
    {
        $data = array();
        $data['products'] = $this->tool_inventory->get_products();
        $data['operations'] = $this->tool_inventory->get_operations();
        $data['tools'] = $this->tool_inventory->get_tools();
        $data['storage_locations'] = $this->tool_inventory->get_storage_locations();
        $data['materials'] = $this->tool_inventory->get_materials();
        $data['makers'] = $this->tool_inventory->get_makers();
        $data['tool_drawing_nos'] = $this->tool_inventory->get_tool_drawing_nos();
        $data['rq_numbers'] = $this->tool_inventory->get_rq_numbers();
        $this->view('add_tool_inventory', $data, FALSE);
    }

    /**
     * Export to Excel
     */
    public function export_excel()
    {
        // Get all data
        $data = $this->tool_inventory->get_all_for_export();

        // Generate filename with timestamp
        $filename = 'Tool_Inventory_' . date('Y-m-d_H-i-s') . '.xls';

        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Content-Transfer-Encoding: binary');

        // Output BOM for UTF-8 Excel compatibility
        echo "\xEF\xBB\xBF";

        // Start Excel XML output
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        echo '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">' . "\n";
        echo '<Title>Tool Inventory Listing</Title>' . "\n";
        echo '<Created>' . date('Y-m-d\TH:i:s\Z') . '</Created>' . "\n";
        echo '</DocumentProperties>' . "\n";
        echo '<Worksheet ss:Name="Tool Inventory">' . "\n";
        echo '<Table>' . "\n";

        // Header Section
        echo '<Row>' . "\n";
        echo '<Cell><Data ss:Type="String">Tool Inventory Listing</Data></Cell>' . "\n";
        echo '</Row>' . "\n";
        echo '<Row></Row>' . "\n";

        // Reported on date
        echo '<Row>' . "\n";
        echo '<Cell><Data ss:Type="String">Reported on: ' . date('Y-m-d H:i:s') . '</Data></Cell>' . "\n";
        echo '</Row>' . "\n";
        echo '<Row></Row>' . "\n";

        // Status filter
        echo '<Row>' . "\n";
        echo '<Cell><Data ss:Type="String">Status</Data></Cell>' . "\n";
        echo '<Cell><Data ss:Type="String">InUsed, New, Available, DesignChange, Allocated, Onhold, Repairing, Modifying, Scrapped</Data></Cell>' . "\n";
        echo '</Row>' . "\n";
        echo '<Row></Row>' . "\n";

        // Column headers
        $headers = array('ID', 'Tool Tag', 'RQ No.', 'Product', 'Tool Name', 'Tool Drawing No.', 
                        'Received Date', 'Do No.', 'Tool ID', 'Status', 'Notes', 'Storage Location', 
                        'Material', 'Tool Condition', 'End Cycle');
        
        echo '<Row>' . "\n";
        foreach ($headers as $header) {
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($header, ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
        }
        echo '</Row>' . "\n";

        // Data rows
        foreach ($data as $row) {
            $status = isset($row['INV_STATUS']) ? (int)$row['INV_STATUS'] : 0;
            $status_name = $this->tool_inventory->get_status_name($status);
            
            $tool_condition = isset($row['INV_TOOL_CONDITION']) && $row['INV_TOOL_CONDITION'] !== null 
                ? (string)$row['INV_TOOL_CONDITION'] 
                : '';
            
            $end_cycle = isset($row['END_CYCLE']) && $row['END_CYCLE'] !== null 
                ? (string)$row['END_CYCLE'] 
                : '0';

            echo '<Row>' . "\n";
            echo '<Cell><Data ss:Type="Number">' . (int)$row['INV_ID'] . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars(isset($row['INV_TOOL_TAG']) ? $row['INV_TOOL_TAG'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars(isset($row['RQ_NO']) ? $row['RQ_NO'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars(isset($row['PRODUCT_NAME']) ? $row['PRODUCT_NAME'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars(isset($row['TOOL_NAME']) ? $row['TOOL_NAME'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars(isset($row['TOOL_DRAWING_NO']) ? $row['TOOL_DRAWING_NO'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars(isset($row['RECEIVED_DATE']) ? $row['RECEIVED_DATE'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars(isset($row['DO_NO']) ? $row['DO_NO'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars(isset($row['INV_TOOL_ID']) ? $row['INV_TOOL_ID'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($status_name, ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars(isset($row['NOTES']) ? $row['NOTES'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars(isset($row['STORAGE_LOCATION']) ? $row['STORAGE_LOCATION'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars(isset($row['MATERIAL']) ? $row['MATERIAL'] : '', ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="String">' . htmlspecialchars($tool_condition, ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '<Cell><Data ss:Type="Number">' . htmlspecialchars($end_cycle, ENT_XML1, 'UTF-8') . '</Data></Cell>' . "\n";
            echo '</Row>' . "\n";
        }

        // Close XML tags
        echo '</Table>' . "\n";
        echo '</Worksheet>' . "\n";
        echo '</Workbook>';
        exit;
    }

    /**
     * Get revisions by ML_ID (AJAX)
     */
    public function get_revisions()
    {
        if (ob_get_level()) ob_clean();
        $this->output->set_content_type('application/json', 'UTF-8');

        $ml_id = (int)$this->input->post('ml_id');
        if ($ml_id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Invalid ML_ID', 'data' => array()));
            return;
        }

        $revisions = $this->tool_inventory->get_revisions_by_ml_id($ml_id);
        echo json_encode(array(
            'success' => true,
            'data' => $revisions
        ));
    }

    /**
     * Submit data (ADD/EDIT)
     */
    public function submit_data()
    {
        if (ob_get_level()) ob_clean();
        $this->output->set_content_type('application/json', 'UTF-8');
        
        $result = array('success' => false, 'message' => '');
        
        try {
            $action = strtoupper($this->input->post('action'));
            
            // Validate required fields
            $this->form_validation->set_rules('tool_drawing_no', 'Tool Drawing No', 'required|integer');
            $this->form_validation->set_rules('tool_id', 'Tool ID', 'required|trim');
            $this->form_validation->set_rules('product_id', 'Product', 'required|integer');
            $this->form_validation->set_rules('process_id', 'Process', 'required|integer');
            $this->form_validation->set_rules('tool_name', 'Tool Name', 'required|integer');
            $this->form_validation->set_rules('tool_tag', 'Tool Tag', 'required|trim');
            $this->form_validation->set_rules('tool_status', 'Tool Status', 'required|integer');

            if ($this->form_validation->run() == FALSE) {
                $this->form_validation->set_error_delimiters('', '');
                $result['message'] = validation_errors() ?: 'Data tidak valid.';
                echo json_encode($result);
                return;
            }

            // TODO: Implement add_data method in model
            // For now, return placeholder
            $result['success'] = false;
            $result['message'] = 'Save functionality will be implemented in model.';
            echo json_encode($result);
            
        } catch (Exception $e) {
            log_message('error', '[Tool_inventory::submit_data] Exception: ' . $e->getMessage());
            $result['message'] = 'Server error. Cek log untuk detail.';
            echo json_encode($result);
        }
    }

    /**
     * Edit page (placeholder - to be implemented)
     */
    public function edit_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_inventory->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        $data = array();
        $data['inventory'] = $row;
        // TODO: Load dropdown data for form
        // $data['tools'] = ...
        // $data['storage_locations'] = ...
        // $data['materials'] = ...
        $this->view('edit_tool_inventory', $data, FALSE);
    }
}

