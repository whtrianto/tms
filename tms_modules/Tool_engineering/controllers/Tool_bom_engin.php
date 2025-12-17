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
                $status_badge = '<span class="badge badge-secondary">Inactive</span>';
                if (isset($row['TD_STATUS'])) {
                    $st = (int)$row['TD_STATUS'];
                    if ($st === 2) {
                        $status_badge = '<span class="badge badge-success">Active</span>';
                    } elseif ($st === 1) {
                        $status_badge = '<span class="badge badge-warning">Pending</span>';
                    }
                }

                $id = (int)$row['TD_ID'];
                $edit_url = base_url('Tool_engineering/tool_bom_engin/edit_page/' . $id);
                $history_url = base_url('Tool_engineering/tool_bom_engin/history_page/' . $id);
                $tool_bom_escaped = htmlspecialchars(isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '', ENT_QUOTES, 'UTF-8');
                
                $action_html = '<div class="action-buttons">' .
                    '<a href="' . $edit_url . '" class="btn btn-secondary btn-sm" title="Edit">Edit</a> ' .
                    '<a href="' . $history_url . '" class="btn btn-warning btn-sm" title="History">Hist</a> ' .
                    '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $id . '" data-name="' . $tool_bom_escaped . '">Del</button>' .
                    '</div>';

                $formatted_data[] = array(
                    $id,
                    $tool_bom_escaped,
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

        $data = array();
        $data['drawing'] = $row;
        $data['machines'] = $this->tool_bom_engin->get_machines();

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

        $data = array();
        $data['drawing'] = $row;
        $data['history'] = $this->tool_bom_engin->get_history($id);

        $this->view('history_tool_bom_engin', $data, FALSE);
    }
}

