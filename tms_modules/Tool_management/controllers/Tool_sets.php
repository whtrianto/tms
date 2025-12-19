<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tool Sets Controller
 * @property M_tool_sets $tool_sets
 */
class Tool_sets extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('form_validation', 'session'));
        
        $username = $this->session->userdata('username');
        $this->uid = (string)($username ?: 'SYSTEM');

        $this->load->model('M_tool_sets', 'tool_sets');
        $this->tool_sets->uid = $this->uid;

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $this->view('index_tool_sets', $data, FALSE);
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
            $order_column = isset($order[0]['column']) ? (int)$order[0]['column'] : 1;
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

            $result = $this->tool_sets->get_data_serverside($start, $length, $search_value, $order_column, $order_dir, $column_search);

            $formatted_data = array();
            foreach ($result['data'] as $row) {
                $status_badge = $this->tool_sets->get_status_badge(isset($row['TSET_STATUS']) ? $row['TSET_STATUS'] : 0);
                
                $id = (int)$row['TSET_ID'];
                $name = htmlspecialchars(isset($row['TSET_NAME']) ? $row['TSET_NAME'] : '', ENT_QUOTES, 'UTF-8');
                $edit_url = base_url('Tool_management/tool_sets/edit_page/' . $id);
                
                $action_html = '<div class="action-buttons">' .
                    '<a href="' . $edit_url . '" class="btn btn-secondary btn-sm" title="Edit">Edit</a> ' .
                    '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $id . '" data-name="' . $name . '">Del</button>' .
                    '</div>';

                $formatted_data[] = array(
                    $id,
                    htmlspecialchars(isset($row['TSET_NAME']) ? $row['TSET_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TOOL_BOM']) ? $row['TOOL_BOM'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['PRODUCT']) ? $row['PRODUCT'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['REVISION']) ? $row['REVISION'] : '0', ENT_QUOTES, 'UTF-8'),
                    $status_badge,
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
            log_message('error', '[Tool_sets::get_data] Exception: ' . $e->getMessage());
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
     * Delete Tool Set
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TSET_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }

        $ok = $this->tool_sets->delete_data($id);
        echo json_encode(array(
            'success' => $ok,
            'message' => $this->tool_sets->messages
        ));
    }

    /**
     * Add page
     */
    public function add_page()
    {
        $data = array();
        $this->view('add_tool_sets', $data, FALSE);
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

        $row = $this->tool_sets->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        $data = array();
        $data['tool_set'] = $row;
        $data['compositions'] = $this->tool_sets->get_compositions($id);
        $data['assignments'] = $this->tool_sets->get_usage_assignments($id);
        $this->view('edit_tool_sets', $data, FALSE);
    }
}

