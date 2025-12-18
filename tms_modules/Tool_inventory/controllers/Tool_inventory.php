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
     * Add page (placeholder - to be implemented)
     */
    public function add_page()
    {
        $data = array();
        // TODO: Load dropdown data for form
        // $data['tools'] = ...
        // $data['storage_locations'] = ...
        // $data['materials'] = ...
        $this->view('add_tool_inventory', $data, FALSE);
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

