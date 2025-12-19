<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tool Drawing Tooling Controller
 * Uses TMS_NEW database tables: TMS_TOOL_MASTER_LIST, TMS_TOOL_MASTER_LIST_REV
 * 
 * @property M_tool_draw_tooling $tool_draw_tooling
 */
class Tool_draw_tooling extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('form_validation', 'session'));

        // capture current user id/username for later use (fallback to SYSTEM)
        $username_from_session = $this->session->userdata('username');
        $this->uid = (string) ($username_from_session ?: 'SYSTEM');
        log_message('debug', '[Tool_draw_tooling::__construct] username_from_session=' . var_export($username_from_session, true) . ', uid="' . $this->uid . '"');

        // load tooling model (uses TMS_NEW database)
        $this->load->model('M_tool_draw_tooling', 'tool_draw_tooling');
        $this->tool_draw_tooling->uid = $this->uid;
        log_message('debug', '[Tool_draw_tooling::__construct] tooling model uid set to "' . $this->tool_draw_tooling->uid . '"');

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $this->view('index_tool_draw_tooling', $data, FALSE);
    }

    /**
     * Server-side DataTable AJAX handler
     */
    public function get_data()
    {
        $this->output->set_content_type('application/json');

        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        
        $search_arr = $this->input->post('search');
        $search = isset($search_arr['value']) ? trim($search_arr['value']) : '';
        
        $order_arr = $this->input->post('order');
        $order_col = isset($order_arr[0]['column']) ? (int)$order_arr[0]['column'] : 1;
        $order_dir = isset($order_arr[0]['dir']) ? $order_arr[0]['dir'] : 'asc';

        // Per-column search
        $columns_post = $this->input->post('columns');
        $column_search = array();
        if (is_array($columns_post)) {
            foreach ($columns_post as $idx => $col) {
                if (isset($col['search']['value']) && $col['search']['value'] !== '') {
                    $column_search[$idx] = $col['search']['value'];
                }
            }
        }

        $result = $this->tool_draw_tooling->get_data_serverside($start, $length, $search, $order_col, $order_dir, $column_search);

        $data = array();
        foreach ($result['data'] as $row) {
            $id = $row['MLR_ID'];
            $drawing_no = htmlspecialchars(isset($row['ML_TOOL_DRAW_NO']) ? $row['ML_TOOL_DRAW_NO'] : '', ENT_QUOTES, 'UTF-8');
            $tool_name = htmlspecialchars(isset($row['TC_NAME']) ? $row['TC_NAME'] : '', ENT_QUOTES, 'UTF-8');
            $min_qty = (int)(isset($row['MLR_MIN_QTY']) ? $row['MLR_MIN_QTY'] : 0);
            $replenish_qty = (int)(isset($row['MLR_REPLENISH_QTY']) ? $row['MLR_REPLENISH_QTY'] : 0);
            $maker = htmlspecialchars(isset($row['MAKER_NAME']) ? $row['MAKER_NAME'] : '', ENT_QUOTES, 'UTF-8');
            $price = number_format((float)(isset($row['MLR_PRICE']) ? $row['MLR_PRICE'] : 0), 2);
            $desc = htmlspecialchars(isset($row['MLR_DESC']) ? $row['MLR_DESC'] : '', ENT_QUOTES, 'UTF-8');
            $eff_date = htmlspecialchars(isset($row['MLR_EFFECTIVE_DATE']) ? $row['MLR_EFFECTIVE_DATE'] : '', ENT_QUOTES, 'UTF-8');
            $material = htmlspecialchars(isset($row['MAT_NAME']) ? $row['MAT_NAME'] : '', ENT_QUOTES, 'UTF-8');
            $tool_life = htmlspecialchars(isset($row['MLR_STD_TL_LIFE']) ? $row['MLR_STD_TL_LIFE'] : '', ENT_QUOTES, 'UTF-8');

            $detail_url = base_url('Tool_engineering/tool_draw_tooling/detail_page/' . $id);
            $edit_url = base_url('Tool_engineering/tool_draw_tooling/edit_page/' . $id);
            $hist_url = base_url('Tool_engineering/tool_draw_tooling/history_page/' . $id);

            $data[] = array(
                '<a href="' . $detail_url . '" class="cell-ellipsis" title="View Detail">' . $drawing_no . '</a>',
                '<span class="cell-ellipsis">' . $tool_name . '</span>',
                $min_qty,
                $replenish_qty,
                '<span class="cell-ellipsis">' . $maker . '</span>',
                $price,
                '<span class="cell-ellipsis">' . $desc . '</span>',
                $eff_date,
                '<span class="cell-ellipsis">' . $material . '</span>',
                $tool_life,
                '<div class="action-buttons">
                    <a href="' . $edit_url . '" class="btn btn-secondary btn-sm" title="Edit">Edit</a>
                    <a href="' . $hist_url . '" class="btn btn-warning btn-sm" title="History">Hist</a>
                </div>'
            );
        }

        echo json_encode(array(
            'draw' => $draw,
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $data
        ));
    }

    /**
     * submit_data: ADD / EDIT Tool Drawing Tooling (AJAX)
     */
    public function submit_data()
    {
        // Clear output buffers to ensure clean JSON response
        if (ob_get_level()) ob_clean();

        $this->output->set_content_type('application/json');
        $result = array('success' => false, 'message' => '');

        try {
            $action = strtoupper($this->input->post('action', TRUE));
            $id     = (int)$this->input->post('MLR_ID', TRUE);

            // validation rules using new column names
            $this->form_validation->set_rules('MLR_TC_ID', 'Tool Class ID', 'required|integer');
            $this->form_validation->set_rules('MLR_MIN_QTY', 'Min Quantity', 'integer');
            $this->form_validation->set_rules('MLR_REPLENISH_QTY', 'Replenish Quantity', 'integer');
            $this->form_validation->set_rules('MLR_MAKER_ID', 'Maker ID', 'integer');
            $this->form_validation->set_rules('MLR_PRICE', 'Price', 'numeric');

            if ($this->form_validation->run() == FALSE) {
                $this->form_validation->set_error_delimiters('', '');
                $result['message'] = validation_errors() ?: 'Data tidak valid.';
                echo json_encode($result);
                return;
            }

            $tc_id = (int)$this->input->post('MLR_TC_ID', TRUE);
            $min_qty = (int)$this->input->post('MLR_MIN_QTY', TRUE);
            $replenish_qty = (int)$this->input->post('MLR_REPLENISH_QTY', TRUE);
            $maker_id = (int)$this->input->post('MLR_MAKER_ID', TRUE);
            $price = (float)$this->input->post('MLR_PRICE', TRUE);
            $description = trim($this->input->post('MLR_DESC', TRUE));
            $mat_id = (int)$this->input->post('MLR_MAT_ID', TRUE);
            $tool_life = trim($this->input->post('MLR_STD_TL_LIFE', TRUE));

            if ($action === 'ADD') {
                $ok = $this->tool_draw_tooling->add_data($tc_id, $min_qty, $replenish_qty, $maker_id, $price, $description, $mat_id, $tool_life);
                if ($ok === true) {
                    $result['success'] = true;
                    $result['message'] = $this->tool_draw_tooling->messages ?: 'Tool Drawing Tooling berhasil ditambahkan.';
                } else {
                    $result['success'] = false;
                    $result['message'] = $this->tool_draw_tooling->messages ?: 'Gagal menambahkan tool drawing tooling.';
                }
                $json = json_encode($result);
                log_message('debug', '[submit_data ADD] response: ' . $json);
                echo $json;
                return;
            }

            if ($action === 'EDIT' && $id > 0) {
                $ok = $this->tool_draw_tooling->edit_data($id, $tc_id, $min_qty, $replenish_qty, $maker_id, $price, $description, $mat_id, $tool_life);
                    if ($ok === true) {
                        $result['success'] = true;
                        $result['message'] = $this->tool_draw_tooling->messages ?: 'Tool Drawing Tooling berhasil diperbarui.';
                    } else {
                        $result['success'] = false;
                        $result['message'] = $this->tool_draw_tooling->messages ?: 'Gagal memperbarui tool drawing tooling.';
                    }
                    $json = json_encode($result);
                log_message('debug', '[submit_data EDIT] response: ' . $json);
                echo $json;
                return;
            }

            $result['message'] = 'Parameter action/ID tidak valid.';
            $json = json_encode($result);
            log_message('debug', '[submit_data] invalid action/id response: ' . $json);
            echo $json;
            return;
        } catch (Exception $e) {
            log_message('error', '[Tool_draw_tooling::submit_data] Exception: ' . $e->getMessage());
            $result['success'] = false;
            $result['message'] = 'Server error. Cek log untuk detail.';
            echo json_encode($result);
            return;
        }
    }

    /**
     * delete_data: delete tool drawing tooling
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('MLR_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'MLR_ID tidak ditemukan.'));
            return;
        }

        $ok = $this->tool_draw_tooling->delete_data($id);
        if ($ok) {
            echo json_encode(array('success' => true, 'message' => $this->tool_draw_tooling->messages ?: 'Tool Drawing Tooling berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->tool_draw_tooling->messages ?: 'Gagal menghapus tool drawing tooling.'));
        }
    }

    /**
     * Halaman detail Tool Drawing Tooling
     * @param int $id
     */
    public function detail_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_draw_tooling->get_by_id_with_parts($id);
        if (!$row) {
            show_404();
            return;
        }

        $data = array();
        $data['drawing'] = $row;
        $data['tool_bom_list'] = $this->tool_draw_tooling->get_tool_bom_by_ml_id($id);

        $this->view('detail_tool_draw_tooling', $data, FALSE);
    }

    /**
     * Halaman edit Tool Drawing Tooling
     * @param int $id
     */
    public function edit_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_draw_tooling->get_by_id_with_parts($id);
        if (!$row) {
            show_404();
            return;
        }

        $data = array();
        $data['drawing'] = $row;
        $data['tools'] = $this->tool_draw_tooling->get_tools();
        $data['makers'] = $this->tool_draw_tooling->get_makers();
        $data['materials'] = $this->tool_draw_tooling->get_materials();
        $data['operations'] = $this->tool_draw_tooling->get_operations();
        $data['tool_bom_list'] = $this->tool_draw_tooling->get_tool_bom_by_ml_id($id);

        $this->view('edit_tool_draw_tooling', $data, FALSE);
    }

    /**
     * Halaman history Tool Drawing Tooling
     * @param int $id
     */
    public function history_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_draw_tooling->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        // Get history (all revisions for the same ML_ID)
        $history = $this->tool_draw_tooling->get_history($id);

        $data = array();
        $data['drawing'] = $row;
        $data['history'] = $history;

        $this->view('history_tool_draw_tooling', $data, FALSE);
    }

    /**
     * get_tool_draw_tooling_detail: ambil data by id (AJAX)
     */
    public function get_tool_draw_tooling_detail()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('MLR_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'MLR_ID tidak ditemukan.'));
            return;
        }

        $row = $this->tool_draw_tooling->get_by_id($id);
        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }

    /**
     * get_history_by_id: Get revision history for a specific tool drawing tooling
     */
    public function get_history_by_id()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('MLR_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'MLR_ID tidak ditemukan.'));
            return;
        }

        $history = $this->tool_draw_tooling->get_history($id);
        if ($history && count($history) > 0) {
            echo json_encode(array('success' => true, 'data' => $history));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Tidak ada history untuk record ini.'));
        }
    }
}
