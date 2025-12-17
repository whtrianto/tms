<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tool Drawing Tooling Controller
 * Uses TMS_TACI_SITE database tables: TMS_TOOL_MASTER_LIST, TMS_TOOL_MASTER_LIST_REV
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

        // load tooling model (uses TMS_TACI_SITE database)
        $this->load->model('M_tool_draw_tooling', 'tool_draw_tooling');
        $this->tool_draw_tooling->uid = $this->uid;
        log_message('debug', '[Tool_draw_tooling::__construct] tooling model uid set to "' . $this->tool_draw_tooling->uid . '"');

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        // Use tooling model which reads from TMS_TACI_SITE tables
        $data['list_data'] = $this->tool_draw_tooling->get_all();

        // Provide master lookups from tooling model
        $data['tools'] = $this->tool_draw_tooling->get_tools();
        $data['materials'] = $this->tool_draw_tooling->get_materials();
        $data['makers'] = $this->tool_draw_tooling->get_makers();

        $this->view('index_tool_draw_tooling', $data, FALSE);
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

        $row = $this->tool_draw_tooling->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        $data = array();
        $data['drawing'] = $row;
        $data['tools'] = $this->tool_draw_tooling->get_tools();
        $data['makers'] = $this->tool_draw_tooling->get_makers();
        $data['materials'] = $this->tool_draw_tooling->get_materials();

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
