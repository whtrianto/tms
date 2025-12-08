<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_tool_bom_engin $tool_bom_engin
 */
class Tool_bom_engin extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('form_validation', 'session'));

        // capture current user id/username for later use (fallback to SYSTEM)
        $username_from_session = $this->session->userdata('username');
        $this->uid = (string) ($username_from_session ?: 'SYSTEM');
        log_message('debug', '[Tool_bom_engin::__construct] username_from_session=' . var_export($username_from_session, true) . ', uid="' . $this->uid . '"');

        // load model AFTER setting uid, then assign uid to model
        $this->load->model('M_tool_bom_engin', 'tool_bom_engin');
        $this->tool_bom_engin->uid = $this->uid;
        log_message('debug', '[Tool_bom_engin::__construct] model uid set to "' . $this->tool_bom_engin->uid . '"');

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $data['list_data'] = $this->tool_bom_engin->get_all();
        $data['products'] = $this->tool_bom_engin->get_products();
        $data['operations'] = $this->tool_bom_engin->get_operations();
        $data['machine_groups'] = $this->tool_bom_engin->get_machine_groups();

        $this->view('index_tool_bom_engin', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT Tool BOM Engineering (AJAX)
     */
    public function submit_data()
    {
        // Clear output buffers to ensure clean JSON response
        if (ob_get_level()) ob_clean();

        $this->output->set_content_type('application/json');
        $result = array('success' => false, 'message' => '');

        try {
            $action = strtoupper($this->input->post('action', TRUE));
            $id     = (int)$this->input->post('ID', TRUE);

            // validation rules
            $this->form_validation->set_rules('TOOL_BOM', 'Tool BOM', 'required|trim');
            $this->form_validation->set_rules('DESCRIPTION', 'Description', 'trim');
            $this->form_validation->set_rules('PRODUCT_ID', 'Product', 'integer');
            $this->form_validation->set_rules('PROCESS_ID', 'Process', 'integer');
            $this->form_validation->set_rules('MACHINE_GROUP_ID', 'Machine Group', 'integer');
            $this->form_validation->set_rules('REVISION', 'Revision', 'integer');
            $this->form_validation->set_rules('STATUS', 'Status', 'integer');
            $this->form_validation->set_rules('EFFECTIVE_DATE', 'Effective Date', 'trim');
            $this->form_validation->set_rules('CHANGE_SUMMARY', 'Change Summary', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $this->form_validation->set_error_delimiters('', '');
                $result['message'] = validation_errors() ?: 'Data tidak valid.';
                echo json_encode($result);
                return;
            }

            $tool_bom = trim($this->input->post('TOOL_BOM', TRUE));
            $description = trim($this->input->post('DESCRIPTION', TRUE));
            $product_id = (int)$this->input->post('PRODUCT_ID', TRUE);
            $process_id = (int)$this->input->post('PROCESS_ID', TRUE);
            $machine_group_id = (int)$this->input->post('MACHINE_GROUP_ID', TRUE);
            $revision = (int)$this->input->post('REVISION', TRUE);
            $status = (int)$this->input->post('STATUS', TRUE);
            $effective_date = trim($this->input->post('EFFECTIVE_DATE', TRUE));
            $change_summary = trim($this->input->post('CHANGE_SUMMARY', TRUE));

            // Handle file upload for drawing
            $drawing_filename = '';
            if (!empty($_FILES) && isset($_FILES['DRAWING_FILE']) && !empty($_FILES['DRAWING_FILE']['name'])) {
                // save drawing under project/tool_engineering/img/
                $uploadDir = FCPATH . 'tool_engineering/img/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $origName = $_FILES['DRAWING_FILE']['name'];
                $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($origName));
                $fileName = 'BOM_' . time() . '_' . $safeName;
                $target = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['DRAWING_FILE']['tmp_name'], $target)) {
                    $drawing_filename = $fileName;
                } else {
                    $result['message'] = 'Gagal mengunggah file drawing.';
                    echo json_encode($result);
                    return;
                }
            } else {
                // Keep old filename if editing and no new file uploaded
                if ($action === 'EDIT' && $id > 0) {
                    $current = $this->tool_bom_engin->get_by_id($id);
                    if ($current && isset($current['DRAWING']) && $current['DRAWING'] !== '') {
                        $drawing_filename = $current['DRAWING'];
                    }
                }
            }

            if ($action === 'ADD') {
                $ok = $this->tool_bom_engin->add_data($tool_bom, $description, $product_id, $process_id, $machine_group_id, $revision, $status, $effective_date, $change_summary, $drawing_filename);
                if ($ok === true) {
                    $result['success'] = true;
                    $result['message'] = $this->tool_bom_engin->messages ?: 'Tool BOM Engineering berhasil ditambahkan.';
                } else {
                    $result['success'] = false;
                    $result['message'] = $this->tool_bom_engin->messages ?: 'Gagal menambahkan tool BOM engineering.';
                }
                $json = json_encode($result);
                log_message('debug', '[submit_data ADD] response: ' . $json);
                echo $json;
                return;
            }

            if ($action === 'EDIT' && $id > 0) {
                $ok = $this->tool_bom_engin->edit_data($id, $tool_bom, $description, $product_id, $process_id, $machine_group_id, $revision, $status, $effective_date, $change_summary, $drawing_filename);
                if ($ok === true) {
                    $result['success'] = true;
                    $result['message'] = $this->tool_bom_engin->messages ?: 'Tool BOM Engineering berhasil diperbarui.';
                } else {
                    $result['success'] = false;
                    $result['message'] = $this->tool_bom_engin->messages ?: 'Gagal memperbarui tool BOM engineering.';
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
            // log full context for debugging
            $ctx = array(
                'msg' => $e->getMessage(),
                'post' => $_POST
            );
            log_message('error', '[Tool_bom_engin::submit_data] Exception: ' . $e->getMessage() . ' | Context: ' . json_encode($ctx));
            $result['success'] = false;
            $result['message'] = 'Server error. Cek log untuk detail.';
            echo json_encode($result);
            return;
        }
    }

    /**
     * delete_data: delete tool BOM engineering
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak ditemukan.'));
            return;
        }

        $ok = $this->tool_bom_engin->delete_data($id);
        if ($ok) {
            echo json_encode(array('success' => true, 'message' => $this->tool_bom_engin->messages ?: 'Tool BOM Engineering berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->tool_bom_engin->messages ?: 'Gagal menghapus tool BOM engineering.'));
        }
    }

    /**
     * get_tool_bom_engin_detail: ambil data by id (AJAX)
     */
    public function get_tool_bom_engin_detail()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak ditemukan.'));
            return;
        }

        $row = $this->tool_bom_engin->get_by_id($id);
        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }
}

