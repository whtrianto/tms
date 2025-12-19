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

    /**
     * Edit Composition page
     */
    public function edit_composition_page($comp_id = 0)
    {
        $comp_id = (int)$comp_id;
        if ($comp_id <= 0) {
            show_404();
            return;
        }

        $comp = $this->tool_sets->get_composition_by_id($comp_id);
        if (!$comp) {
            show_404();
            return;
        }

        // Get tool set info for back link
        $tset_id = isset($comp['TSCOMP_TSET_ID']) ? (int)$comp['TSCOMP_TSET_ID'] : 0;
        $tool_set = $this->tool_sets->get_by_id($tset_id);

        $data = array();
        $data['composition'] = $comp;
        $data['tool_set'] = $tool_set;
        $this->view('edit_tool_set_composition', $data, FALSE);
    }

    /**
     * Submit Composition data (AJAX)
     */
    public function submit_composition_data()
    {
        $this->output->set_content_type('application/json');

        $comp_id = (int)$this->input->post('TSCOMP_ID', TRUE);
        if ($comp_id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }

        $remarks = $this->input->post('TSCOMP_REMARKS', TRUE);
        $end_cycle = (int)$this->input->post('END_CYCLE', TRUE);

        $ok = $this->tool_sets->update_composition($comp_id, $remarks, $end_cycle);
        echo json_encode(array(
            'success' => $ok,
            'message' => $this->tool_sets->messages
        ));
    }

    /**
     * Replace Composition page
     */
    public function replace_composition_page($comp_id = 0)
    {
        $comp_id = (int)$comp_id;
        if ($comp_id <= 0) {
            show_404();
            return;
        }

        $comp = $this->tool_sets->get_composition_by_id($comp_id);
        if (!$comp) {
            show_404();
            return;
        }

        // Get available tools for replace
        $mlr_id = isset($comp['TSCOMP_MLR_ID']) ? (int)$comp['TSCOMP_MLR_ID'] : 0;
        $exclude_inv_id = isset($comp['TSCOMP_INV_ID']) ? (int)$comp['TSCOMP_INV_ID'] : 0;
        $available_tools = $this->tool_sets->get_available_tools_for_replace($mlr_id, $exclude_inv_id);

        // Get tool set info for back link
        $tset_id = isset($comp['TSCOMP_TSET_ID']) ? (int)$comp['TSCOMP_TSET_ID'] : 0;
        $tool_set = $this->tool_sets->get_by_id($tset_id);

        $data = array();
        $data['composition'] = $comp;
        $data['available_tools'] = $available_tools;
        $data['tool_set'] = $tool_set;
        $this->view('replace_tool_set_composition', $data, FALSE);
    }

    /**
     * Get available tools for replace (AJAX)
     */
    public function get_available_tools()
    {
        $this->output->set_content_type('application/json');

        $mlr_id = (int)$this->input->post('mlr_id', TRUE);
        $exclude_inv_id = (int)$this->input->post('exclude_inv_id', TRUE);

        if ($mlr_id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'MLR_ID tidak valid.', 'data' => array()));
            return;
        }

        $tools = $this->tool_sets->get_available_tools_for_replace($mlr_id, $exclude_inv_id);
        
        // Format data for display
        $formatted_tools = array();
        foreach ($tools as $tool) {
            $status = isset($tool['TOOL_STATUS']) ? (int)$tool['TOOL_STATUS'] : 0;
            $status_map = array(1 => 'New', 2 => 'Allocated', 3 => 'Available', 4 => 'InUsed', 5 => 'Onhold', 6 => 'Scrapped', 7 => 'Repairing', 8 => 'Modifying', 9 => 'DesignChange');
            $status_name = isset($status_map[$status]) ? $status_map[$status] : 'Unknown';

            $formatted_tools[] = array(
                'INV_ID' => (int)$tool['INV_ID'],
                'TOOL_ID' => isset($tool['INV_TOOL_ID']) ? $tool['INV_TOOL_ID'] : '',
                'TOOL_DRAWING_NO' => isset($tool['TOOL_DRAWING_NO']) ? $tool['TOOL_DRAWING_NO'] : '',
                'REVISION' => isset($tool['REVISION']) ? $tool['REVISION'] : 0,
                'STATUS' => $status_name,
                'END_CYCLE' => isset($tool['END_CYCLE']) ? $tool['END_CYCLE'] : 0,
                'STORAGE_LOCATION' => isset($tool['STORAGE_LOCATION']) ? $tool['STORAGE_LOCATION'] : ''
            );
        }

        echo json_encode(array(
            'success' => true,
            'data' => $formatted_tools
        ));
    }

    /**
     * Submit Replace Composition data (AJAX)
     */
    public function submit_replace_composition_data()
    {
        $this->output->set_content_type('application/json');

        $comp_id = (int)$this->input->post('TSCOMP_ID', TRUE);
        $new_inv_id = (int)$this->input->post('NEW_INV_ID', TRUE);
        if ($comp_id <= 0 || $new_inv_id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }

        $remarks = $this->input->post('TSCOMP_REMARKS', TRUE);

        $ok = $this->tool_sets->replace_composition($comp_id, $new_inv_id, $remarks);
        echo json_encode(array(
            'success' => $ok,
            'message' => $this->tool_sets->messages
        ));
    }
}

