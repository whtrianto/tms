<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tool BOM Tooling Controller
 * @property M_tool_bom_tooling $tool_bom_tooling
 */
class Tool_bom_tooling extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('form_validation', 'session'));
        
        $username = $this->session->userdata('username');
        $this->uid = (string)($username ?: 'SYSTEM');

        $this->load->model('M_tool_bom_tooling', 'tool_bom_tooling');
        $this->tool_bom_tooling->uid = $this->uid;

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $this->view('index_tool_bom_tooling', $data, FALSE);
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

            $result = $this->tool_bom_tooling->get_data_serverside($start, $length, $search_value, $order_column, $order_dir, $column_search);

            $formatted_data = array();
            foreach ($result['data'] as $row) {
                $st = isset($row['TD_STATUS']) ? (int)$row['TD_STATUS'] : 0;
                // Status: 2=Active, 3=Pending, 5/lainnya=Inactive
                if ($st === 2) {
                    $status_badge = '<span class="badge badge-success">Active</span>';
                } elseif ($st === 3) {
                    $status_badge = '<span class="badge badge-warning">Pending</span>';
                } else {
                    $status_badge = '<span class="badge badge-secondary">Inactive</span>';
                }

                // Type: Selalu menampilkan "ToolBOM"
                $type_text = 'ToolBOM';

                $id = (int)$row['TD_ID'];
                $edit_url = base_url('Tool_engineering/tool_bom_tooling/edit_page/' . $id);
                $history_url = base_url('Tool_engineering/tool_bom_tooling/history_page/' . $id);
                
                $action_html = '<div class="action-buttons">' .
                    '<a href="' . $edit_url . '" class="btn btn-secondary btn-sm" title="Edit">Edit</a> ' .
                    '<a href="' . $history_url . '" class="btn btn-warning btn-sm" title="History">History</a>' .
                    '</div>';

                $formatted_data[] = array(
                    $id,
                    htmlspecialchars(isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_PROCESS_NAME']) ? $row['TD_PROCESS_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_MACHINE_GROUP']) ? $row['TD_MACHINE_GROUP'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_REVISION']) ? (string)$row['TD_REVISION'] : '0', ENT_QUOTES, 'UTF-8'),
                    $status_badge,
                    htmlspecialchars(isset($row['TD_EFFECTIVE_DATE']) && $row['TD_EFFECTIVE_DATE'] !== '' ? substr($row['TD_EFFECTIVE_DATE'], 0, 10) : '-', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_MODIFIED_DATE']) && $row['TD_MODIFIED_DATE'] !== '' ? $row['TD_MODIFIED_DATE'] : '-', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : '', ENT_QUOTES, 'UTF-8'),
                    $type_text,
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
            log_message('error', '[Tool_bom_tooling::get_data] Exception: ' . $e->getMessage());
            $this->output->set_output(json_encode(array(
                'draw' => isset($draw) ? $draw : 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'An error occurred while fetching data.'
            )));
        }
    }

    public function edit_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_bom_tooling->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        $data = array();
        $data['bom'] = $row;
        // TODO: Load master data if needed for edit form
        // $data['products'] = $this->tool_bom_tooling->get_products();
        // $data['operations'] = $this->tool_bom_tooling->get_operations();
        // $data['machine_groups'] = $this->tool_bom_tooling->get_machine_groups();

        $this->view('edit_tool_bom_tooling', $data, FALSE);
    }

    public function history_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_bom_tooling->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        $history = $this->tool_bom_tooling->get_history($id);
        
        // Resolve names for info section
        $product_name = isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '';
        $tool_bom = isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '';
        $process_name = isset($row['TD_PROCESS_NAME']) ? $row['TD_PROCESS_NAME'] : '';
        $machine_group_name = isset($row['TD_MACHINE_GROUP']) ? $row['TD_MACHINE_GROUP'] : '';

        $data = array();
        $data['bom'] = $row; // Renamed from 'drawing' to 'bom' for consistency
        $data['history'] = $history;
        $data['product_name'] = $product_name;
        $data['tool_bom'] = $tool_bom;
        $data['process_name'] = $process_name;
        $data['machine_group_name'] = $machine_group_name;

        $this->view('history_tool_bom_tooling', $data, FALSE);
    }
}

