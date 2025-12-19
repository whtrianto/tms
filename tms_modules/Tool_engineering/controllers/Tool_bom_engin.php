<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tool BOM Engineering Controller
 * @property M_tool_bom_engin $tool_bom_engin
 */
class Tool_bom_engin extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('form_validation', 'session'));
        
        $username = $this->session->userdata('username');
        $this->uid = (string)($username ?: 'SYSTEM');

        $this->load->model('M_tool_bom_engin', 'tool_bom_engin');
        $this->tool_bom_engin->uid = $this->uid;

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $this->view('index_tool_bom_engin', $data, FALSE);
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

            $result = $this->tool_bom_engin->get_data_serverside($start, $length, $search_value, $order_column, $order_dir, $column_search);

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

                $id = (int)$row['TD_ID'];
                $edit_url = base_url('Tool_engineering/tool_bom_engin/edit_page/' . $id);
                $history_url = base_url('Tool_engineering/tool_bom_engin/history_page/' . $id);
                $detail_url = base_url('Tool_engineering/tool_bom_engin/detail_page/' . $id);
                $tool_bom_escaped = htmlspecialchars(isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '', ENT_QUOTES, 'UTF-8');
                $tool_bom_link = '<a href="' . $detail_url . '" class="text-primary" style="text-decoration: underline;">' . $tool_bom_escaped . '</a>';
                
                $action_html = '<div class="action-buttons">' .
                    '<a href="' . $edit_url . '" class="btn btn-secondary btn-sm" title="Edit">Edit</a> ' .
                    '<a href="' . $history_url . '" class="btn btn-warning btn-sm" title="History">Hist</a> ' .
                    '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $id . '" data-name="' . $tool_bom_escaped . '">Del</button>' .
                    '</div>';

                $formatted_data[] = array(
                    $id,
                    $tool_bom_link,
                    htmlspecialchars(isset($row['TD_DESCRIPTION']) ? $row['TD_DESCRIPTION'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_MACHINE_GROUP']) ? $row['TD_MACHINE_GROUP'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_REVISION']) ? (string)$row['TD_REVISION'] : '0', ENT_QUOTES, 'UTF-8'),
                    $status_badge,
                    htmlspecialchars(isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : '', ENT_QUOTES, 'UTF-8'),
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
            log_message('error', '[Tool_bom_engin::get_data] Exception: ' . $e->getMessage());
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
     * Delete Tool BOM
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TD_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }

        $ok = $this->tool_bom_engin->delete_data($id);
        echo json_encode(array(
            'success' => $ok,
            'message' => $this->tool_bom_engin->messages
        ));
    }

    /**
     * Add page
     */
    public function add_page()
    {
        $data = array();
        $data['products'] = $this->tool_bom_engin->get_products();
        $data['operations'] = $this->tool_bom_engin->get_operations();
        $data['machine_groups'] = $this->tool_bom_engin->get_machine_groups();
        
        $this->view('add_tool_bom_engin', $data, FALSE);
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

        $row = $this->tool_bom_engin->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        // Map data to view format
        $bom = array(
            'ID' => isset($row['TD_ID']) ? (int)$row['TD_ID'] : 0,
            'TOOL_BOM' => isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '',
            'DESCRIPTION' => isset($row['TD_DESCRIPTION']) ? $row['TD_DESCRIPTION'] : '',
            'PRODUCT_ID' => isset($row['PRODUCT_ID']) ? (int)$row['PRODUCT_ID'] : 0,
            'PROCESS_ID' => isset($row['MLR_OP_ID']) ? (int)$row['MLR_OP_ID'] : 0,
            'MACHINE_GROUP_ID' => isset($row['MLR_MACG_ID']) ? (int)$row['MLR_MACG_ID'] : 0,
            'REVISION' => isset($row['TD_REVISION']) ? (int)$row['TD_REVISION'] : 0,
            'STATUS' => isset($row['TD_STATUS']) ? (int)$row['TD_STATUS'] : 1,
            'EFFECTIVE_DATE' => isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : '',
            'CHANGE_SUMMARY' => isset($row['TD_CHANGE_SUMMARY']) ? $row['TD_CHANGE_SUMMARY'] : '',
            'DRAWING' => isset($row['MLR_DRAWING']) ? $row['MLR_DRAWING'] : '',
            'IS_TRIAL_BOM' => isset($row['ML_IS_TRIAL_BOM']) ? (int)$row['ML_IS_TRIAL_BOM'] : (isset($row['ML_TRIAL']) ? (int)$row['ML_TRIAL'] : 0)
        );

        $data = array();
        $data['bom'] = $bom;
        $data['products'] = $this->tool_bom_engin->get_products();
        $data['operations'] = $this->tool_bom_engin->get_operations();
        $data['machine_groups'] = $this->tool_bom_engin->get_machine_groups();
        $data['tools'] = $this->tool_bom_engin->get_tools();
        $data['materials'] = $this->tool_bom_engin->get_materials();
        $data['makers'] = $this->tool_bom_engin->get_makers();
        $data['additional_info'] = $this->tool_bom_engin->get_additional_info($id);

        $this->view('edit_tool_bom_engin', $data, FALSE);
    }

    /**
     * History page
     */
    public function history_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_bom_engin->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        // Resolve names for info section
        $product_name = isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '';
        $tool_bom = isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '';
        
        // Get process name
        $process_name = '';
        if (isset($row['MLR_OP_ID']) && (int)$row['MLR_OP_ID'] > 0) {
            $operations = $this->tool_bom_engin->get_operations();
            foreach ($operations as $op) {
                if ((int)$op['OPERATION_ID'] === (int)$row['MLR_OP_ID']) {
                    $process_name = $op['OPERATION_NAME'];
                    break;
                }
            }
        }
        
        // Get machine group name
        $machine_group_name = isset($row['TD_MACHINE_GROUP']) ? $row['TD_MACHINE_GROUP'] : '';
        if (empty($machine_group_name) && isset($row['MLR_MACG_ID']) && (int)$row['MLR_MACG_ID'] > 0) {
            $machines = $this->tool_bom_engin->get_machine_groups();
            foreach ($machines as $m) {
                if ((int)$m['MACHINE_ID'] === (int)$row['MLR_MACG_ID']) {
                    $machine_group_name = $m['MACHINE_NAME'];
                    break;
                }
            }
        }

        $data = array();
        $data['drawing'] = $row;
        $data['history'] = $this->tool_bom_engin->get_history($id);
        $data['product_name'] = $product_name;
        $data['tool_bom'] = $tool_bom;
        $data['process_name'] = $process_name;
        $data['machine_group_name'] = $machine_group_name;

        $this->view('history_tool_bom_engin', $data, FALSE);
    }

    /**
     * Detail page
     */
    public function detail_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_bom_engin->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        // Map data to view format
        $bom = array(
            'ID' => isset($row['TD_ID']) ? (int)$row['TD_ID'] : 0,
            'TOOL_BOM' => isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '',
            'DESCRIPTION' => isset($row['TD_DESCRIPTION']) ? $row['TD_DESCRIPTION'] : '',
            'PRODUCT_ID' => isset($row['PRODUCT_ID']) ? (int)$row['PRODUCT_ID'] : 0,
            'PRODUCT' => isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '',
            'PROCESS_ID' => isset($row['MLR_OP_ID']) ? (int)$row['MLR_OP_ID'] : 0,
            'MACHINE_GROUP_ID' => isset($row['MLR_MACG_ID']) ? (int)$row['MLR_MACG_ID'] : 0,
            'MACHINE_GROUP' => isset($row['TD_MACHINE_GROUP']) ? $row['TD_MACHINE_GROUP'] : '',
            'REVISION' => isset($row['TD_REVISION']) ? (int)$row['TD_REVISION'] : 0,
            'STATUS' => isset($row['TD_STATUS']) ? (int)$row['TD_STATUS'] : 1,
            'EFFECTIVE_DATE' => isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : '',
            'CHANGE_SUMMARY' => isset($row['TD_CHANGE_SUMMARY']) ? $row['TD_CHANGE_SUMMARY'] : '',
            'DRAWING' => isset($row['MLR_DRAWING']) ? $row['MLR_DRAWING'] : '',
            'MODIFIED_DATE' => isset($row['TD_MODIFIED_DATE']) ? $row['TD_MODIFIED_DATE'] : '',
            'MODIFIED_BY' => isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : '',
            'IS_TRIAL_BOM' => isset($row['ML_IS_TRIAL_BOM']) ? (int)$row['ML_IS_TRIAL_BOM'] : 0
        );

        $data = array();
        $data['bom'] = $bom;
        $data['products'] = $this->tool_bom_engin->get_products();
        $data['operations'] = $this->tool_bom_engin->get_operations();
        $data['machine_groups'] = $this->tool_bom_engin->get_machine_groups();
        $data['tools'] = $this->tool_bom_engin->get_tools();
        $data['additional_info'] = $this->tool_bom_engin->get_additional_info($id);

        $this->view('detail_tool_bom_engin', $data, FALSE);
    }
}

