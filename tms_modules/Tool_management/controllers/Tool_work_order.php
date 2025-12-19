<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tool Work Order Controller
 * @property M_tool_work_order $tool_work_order
 */
class Tool_work_order extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('form_validation', 'session'));
        
        $username = $this->session->userdata('username');
        $this->uid = (string)($username ?: 'SYSTEM');

        $this->load->model('M_tool_work_order', 'tool_work_order');
        $this->tool_work_order->uid = $this->uid;

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $this->view('index_tool_work_order', $data, FALSE);
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
            $order_dir = isset($order[0]['dir']) ? $order[0]['dir'] : 'asc';

            $columns = $this->input->post('columns');
            $column_search = array();
            if (is_array($columns)) {
                foreach ($columns as $idx => $col) {
                    if (isset($col['search']['value']) && $col['search']['value'] !== '') {
                        $column_search[$idx] = $col['search']['value'];
                    }
                }
            }

            $result = $this->tool_work_order->get_data_serverside($start, $length, $search_value, $order_column, $order_dir, $column_search);

            $formatted_data = array();
            foreach ($result['data'] as $row) {
                $wo_type_name = $this->tool_work_order->get_wo_type_name(isset($row['WO_TYPE']) ? $row['WO_TYPE'] : 0);
                $wo_reason_name = $this->tool_work_order->get_wo_reason_name(isset($row['WO_REASON']) ? $row['WO_REASON'] : 0);
                $status_badge = $this->tool_work_order->get_wo_status_badge(isset($row['WO_STATUS']) ? $row['WO_STATUS'] : 0);
                
                $id = (int)$row['WO_ID'];
                $wo_no = htmlspecialchars(isset($row['WO_NO']) ? $row['WO_NO'] : '', ENT_QUOTES, 'UTF-8');
                $edit_url = base_url('Tool_management/tool_work_order/edit_page/' . $id);
                
                $action_html = '<div class="action-buttons">' .
                    '<a href="' . $edit_url . '" class="btn btn-secondary btn-sm" title="Edit">Edit</a> ' .
                    '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $id . '" data-wo-no="' . $wo_no . '">Del</button>' .
                    '</div>';

                // Format dates
                $date = isset($row['DATE']) ? $row['DATE'] : '';
                if (!empty($date) && strlen($date) >= 10) {
                    $date = substr($date, 0, 10);
                }
                $target_date = isset($row['TARGET_COMPLETION_DATE']) ? $row['TARGET_COMPLETION_DATE'] : '';
                if (!empty($target_date) && strlen($target_date) >= 10) {
                    $target_date = substr($target_date, 0, 10);
                }
                $actual_date = isset($row['ACTUAL_COMPLETION_DATE']) ? $row['ACTUAL_COMPLETION_DATE'] : '';
                if (!empty($actual_date) && strlen($actual_date) >= 10) {
                    $actual_date = substr($actual_date, 0, 10);
                }

                $formatted_data[] = array(
                    $id,
                    htmlspecialchars($date, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['WO_NO']) ? $row['WO_NO'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($wo_type_name, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['REQUESTED_BY']) ? $row['REQUESTED_BY'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TOOL_ID']) ? $row['TOOL_ID'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TOOL_DRAWING_NO']) ? $row['TOOL_DRAWING_NO'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($target_date, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($actual_date, ENT_QUOTES, 'UTF-8'),
                    $status_badge,
                    htmlspecialchars($wo_reason_name, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TOOL_MAKING_DRAW_NO']) ? $row['TOOL_MAKING_DRAW_NO'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['RQ_NO']) ? $row['RQ_NO'] : '', ENT_QUOTES, 'UTF-8'),
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
            log_message('error', '[Tool_work_order::get_data] Exception: ' . $e->getMessage());
            log_message('error', '[Tool_work_order::get_data] Trace: ' . $e->getTraceAsString());
            $this->output->set_output(json_encode(array(
                'draw' => isset($draw) ? $draw : 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'Error loading data: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Delete Work Order
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('WO_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }

        $ok = $this->tool_work_order->delete_data($id);
        echo json_encode(array(
            'success' => $ok,
            'message' => $this->tool_work_order->messages
        ));
    }

    /**
     * Add page (placeholder)
     */
    public function add_page()
    {
        $data = array();
        $this->view('add_tool_work_order', $data, FALSE);
    }

    /**
     * Edit page (placeholder)
     */
    public function edit_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $data = array();
        $data['wo_id'] = $id;
        $this->view('edit_tool_work_order', $data, FALSE);
    }
}

