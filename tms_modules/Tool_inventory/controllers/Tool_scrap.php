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
        // TODO: Load report data
        $this->view('report_tool_scrap', $data, FALSE);
    }
}

