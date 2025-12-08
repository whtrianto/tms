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
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers first
        $this->output->set_content_type('application/json');
        $this->output->set_header('Cache-Control: no-cache, must-revalidate');
        $this->output->set_header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        
        $result = array('success' => false, 'message' => '');

        try {
            $action = strtoupper($this->input->post('action', TRUE));
            $id     = (int)$this->input->post('ID', TRUE);

            // validation rules
            $this->form_validation->set_rules('TOOL_BOM', 'Tool BOM', 'required|trim');
            $this->form_validation->set_rules('DESCRIPTION', 'Description', 'trim');
            $this->form_validation->set_rules('PRODUCT_ID', 'Product', 'trim');
            $this->form_validation->set_rules('PROCESS_ID', 'Process', 'trim');
            $this->form_validation->set_rules('MACHINE_GROUP_ID', 'Machine Group', 'trim');
            $this->form_validation->set_rules('REVISION', 'Revision', 'trim|numeric');
            $this->form_validation->set_rules('STATUS', 'Status', 'trim|numeric');
            $this->form_validation->set_rules('EFFECTIVE_DATE', 'Effective Date', 'trim');
            $this->form_validation->set_rules('CHANGE_SUMMARY', 'Change Summary', 'trim');

            if ($this->form_validation->run() == FALSE) {
                $this->form_validation->set_error_delimiters('', '');
                $result['message'] = validation_errors() ?: 'Data tidak valid.';
                $this->output->set_output(json_encode($result));
                return;
            }

            $tool_bom = trim($this->input->post('TOOL_BOM', TRUE));
            $description = trim($this->input->post('DESCRIPTION', TRUE));
            
            // Handle optional integer fields
            $product_id_raw = $this->input->post('PRODUCT_ID', TRUE);
            $product_id = ($product_id_raw !== '' && $product_id_raw !== null) ? (int)$product_id_raw : 0;
            
            $process_id_raw = $this->input->post('PROCESS_ID', TRUE);
            $process_id = ($process_id_raw !== '' && $process_id_raw !== null) ? (int)$process_id_raw : 0;
            
            $machine_group_id_raw = $this->input->post('MACHINE_GROUP_ID', TRUE);
            $machine_group_id = ($machine_group_id_raw !== '' && $machine_group_id_raw !== null) ? (int)$machine_group_id_raw : 0;
            
            $revision_raw = $this->input->post('REVISION', TRUE);
            $revision = ($revision_raw !== '' && $revision_raw !== null) ? (int)$revision_raw : 0;
            
            $status_raw = $this->input->post('STATUS', TRUE);
            $status = ($status_raw !== '' && $status_raw !== null) ? (int)$status_raw : 1; // default to Active (1)
            
            $effective_date = trim($this->input->post('EFFECTIVE_DATE', TRUE));
            $change_summary = trim($this->input->post('CHANGE_SUMMARY', TRUE));
            
            // Log input for debugging
            log_message('debug', '[submit_data] Input: tool_bom=' . $tool_bom . ', product_id=' . $product_id . ', process_id=' . $process_id . ', machine_group_id=' . $machine_group_id . ', revision=' . $revision . ', status=' . $status);

            // Handle file upload for drawing
            $drawing_filename = '';
            if (!empty($_FILES) && isset($_FILES['DRAWING_FILE']) && !empty($_FILES['DRAWING_FILE']['name'])) {
                // save drawing under project/tool_engineering/img/
                $uploadDir = FCPATH . 'tool_engineering/img/';
                if (!is_dir($uploadDir)) {
                    if (!@mkdir($uploadDir, 0755, true)) {
                        $result['message'] = 'Gagal membuat direktori upload.';
                        $this->output->set_output(json_encode($result));
                        return;
                    }
                }
                // Check if directory is writable
                if (!is_writable($uploadDir)) {
                    $result['message'] = 'Direktori upload tidak dapat ditulis.';
                    $this->output->set_output(json_encode($result));
                    return;
                }
                $origName = $_FILES['DRAWING_FILE']['name'];
                $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($origName));
                $fileName = 'BOM_' . time() . '_' . $safeName;
                $target = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['DRAWING_FILE']['tmp_name'], $target)) {
                    $drawing_filename = $fileName;
                } else {
                    $result['message'] = 'Gagal mengunggah file drawing.';
                    $this->output->set_output(json_encode($result));
                    return;
                }
            } else {
                // Keep old filename if editing and no new file uploaded
                if ($action === 'EDIT' && $id > 0) {
                    try {
                        $current = $this->tool_bom_engin->get_by_id($id);
                        if ($current && isset($current['DRAWING']) && $current['DRAWING'] !== '') {
                            $drawing_filename = $current['DRAWING'];
                        }
                    } catch (Exception $e) {
                        log_message('error', '[submit_data] Error getting current drawing: ' . $e->getMessage());
                        // Continue without drawing filename if error occurs
                    }
                }
            }

            if ($action === 'ADD') {
                try {
                    $ok = $this->tool_bom_engin->add_data($tool_bom, $description, $product_id, $process_id, $machine_group_id, $revision, $status, $effective_date, $change_summary, $drawing_filename);
                    if ($ok === true) {
                        $result['success'] = true;
                        $result['message'] = $this->tool_bom_engin->messages ?: 'Tool BOM Engineering berhasil ditambahkan.';
                    } else {
                        $result['success'] = false;
                        $result['message'] = $this->tool_bom_engin->messages ?: 'Gagal menambahkan tool BOM engineering.';
                    }
                } catch (Exception $e) {
                    log_message('error', '[submit_data ADD] Exception in add_data: ' . $e->getMessage());
                    $result['success'] = false;
                    $result['message'] = 'Error: ' . $e->getMessage();
                } catch (Error $e) {
                    log_message('error', '[submit_data ADD] Fatal Error in add_data: ' . $e->getMessage());
                    $result['success'] = false;
                    $result['message'] = 'Fatal Error: ' . $e->getMessage();
                }
                $json = json_encode($result);
                log_message('debug', '[submit_data ADD] response: ' . $json);
                $this->output->set_output($json);
                return;
            }

            if ($action === 'EDIT' && $id > 0) {
                try {
                    $ok = $this->tool_bom_engin->edit_data($id, $tool_bom, $description, $product_id, $process_id, $machine_group_id, $revision, $status, $effective_date, $change_summary, $drawing_filename);
                    if ($ok === true) {
                        $result['success'] = true;
                        $result['message'] = $this->tool_bom_engin->messages ?: 'Tool BOM Engineering berhasil diperbarui.';
                    } else {
                        $result['success'] = false;
                        $result['message'] = $this->tool_bom_engin->messages ?: 'Gagal memperbarui tool BOM engineering.';
                    }
                } catch (Exception $e) {
                    log_message('error', '[submit_data EDIT] Exception in edit_data: ' . $e->getMessage());
                    $result['success'] = false;
                    $result['message'] = 'Error: ' . $e->getMessage();
                } catch (Error $e) {
                    log_message('error', '[submit_data EDIT] Fatal Error in edit_data: ' . $e->getMessage());
                    $result['success'] = false;
                    $result['message'] = 'Fatal Error: ' . $e->getMessage();
                }
                $json = json_encode($result);
                log_message('debug', '[submit_data EDIT] response: ' . $json);
                $this->output->set_output($json);
                return;
            }

            $result['message'] = 'Parameter action/ID tidak valid.';
            $json = json_encode($result);
            log_message('debug', '[submit_data] invalid action/id response: ' . $json);
            $this->output->set_output($json);
            return;
        } catch (Exception $e) {
            // log full context for debugging
            $ctx = array(
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post' => $_POST,
                'files' => isset($_FILES) ? array_keys($_FILES) : array()
            );
            log_message('error', '[Tool_bom_engin::submit_data] Exception: ' . $e->getMessage() . ' | Context: ' . json_encode($ctx));
            $result['success'] = false;
            $result['message'] = 'Server error: ' . $e->getMessage() . '. Cek log untuk detail.';
            $this->output->set_output(json_encode($result));
            return;
        } catch (Error $e) {
            // Catch PHP 7+ errors
            log_message('error', '[Tool_bom_engin::submit_data] Fatal Error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());
            $result['success'] = false;
            $result['message'] = 'Fatal error: ' . $e->getMessage();
            $this->output->set_output(json_encode($result));
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

