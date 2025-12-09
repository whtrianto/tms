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

        // drawing model used on edit page for additional information section
        $this->load->model('M_tool_draw_engin', 'tool_draw_engin');
        $this->tool_draw_engin->uid = $this->uid;

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
     * Halaman detail Tool BOM Engineering (read-only)
     * @param int $id
     */
    public function detail_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $bom = $this->tool_bom_engin->get_by_id($id);
        if (!$bom) {
            show_404();
            return;
        }

        $productId = isset($bom['PRODUCT_ID']) ? (int)$bom['PRODUCT_ID'] : 0;
        $processId = isset($bom['PROCESS_ID']) ? (int)$bom['PROCESS_ID'] : 0;

        $data = array();
        $data['bom'] = $bom;
        $data['products'] = $this->tool_bom_engin->get_products();
        $data['operations'] = $this->tool_bom_engin->get_operations();
        $data['machine_groups'] = $this->tool_bom_engin->get_machine_groups();

        // Additional Information: Tool Drawing Engineering filtered by product/process (best-effort)
        $data['additional_info'] = $this->tool_draw_engin->get_by_product_process($productId, $processId);
        $data['materials'] = $this->tool_draw_engin->get_materials();
        $data['makers'] = $this->tool_draw_engin->get_makers();

        $this->view('detail_tool_bom_engin', $data, FALSE);
    }

    /**
     * Halaman edit Tool BOM Engineering + Additional Information (Tool Drawing Engin)
     * @param int $id
     */
    public function edit_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $bom = $this->tool_bom_engin->get_by_id($id);
        if (!$bom) {
            show_404();
            return;
        }

        $productId = isset($bom['PRODUCT_ID']) ? (int)$bom['PRODUCT_ID'] : 0;
        $processId = isset($bom['PROCESS_ID']) ? (int)$bom['PROCESS_ID'] : 0;

        $data = array();
        $data['bom'] = $bom;
        $data['products'] = $this->tool_bom_engin->get_products();
        $data['operations'] = $this->tool_bom_engin->get_operations();
        $data['machine_groups'] = $this->tool_bom_engin->get_machine_groups();

        // Additional Information: Tool Drawing Engineering filtered by product/process (best-effort)
        $data['additional_info'] = $this->tool_draw_engin->get_by_product_process($productId, $processId);
        $data['materials'] = $this->tool_draw_engin->get_materials();
        $data['makers'] = $this->tool_draw_engin->get_makers();
        $data['tools'] = $this->tool_draw_engin->get_tools();

        $this->view('edit_tool_bom_engin', $data, FALSE);
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
            $this->form_validation->set_rules('PRODUCT_ID', 'Product', 'trim');
            $this->form_validation->set_rules('PROCESS_ID', 'Process', 'trim');
            $this->form_validation->set_rules('MACHINE_GROUP_ID', 'Machine Group', 'trim');
            $this->form_validation->set_rules('REVISION', 'Revision', 'trim|numeric');
            $this->form_validation->set_rules('STATUS', 'Status', 'trim');
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
            
            // Handle optional integer fields
            $product_id_raw = $this->input->post('PRODUCT_ID', TRUE);
            $product_id = ($product_id_raw !== '' && $product_id_raw !== null) ? (int)$product_id_raw : 0;
            
            $process_id_raw = $this->input->post('PROCESS_ID', TRUE);
            $process_id = ($process_id_raw !== '' && $process_id_raw !== null) ? (int)$process_id_raw : 0;
            
            $machine_group_id_raw = $this->input->post('MACHINE_GROUP_ID', TRUE);
            $machine_group_id = ($machine_group_id_raw !== '' && $machine_group_id_raw !== null) ? (int)$machine_group_id_raw : 0;
            
            $revision_raw = $this->input->post('REVISION', TRUE);
            $revision = ($revision_raw !== '' && $revision_raw !== null) ? (int)$revision_raw : 0;
            
            // Map status value from dropdown (numeric) to DB string enum
            $status_raw = $this->input->post('STATUS', TRUE);
            $status = 'ACTIVE'; // default
            if ($status_raw === '0' || $status_raw === 0) {
                $status = 'INACTIVE';
            } elseif ($status_raw === '2' || $status_raw === 2) {
                $status = 'PENDING';
            } elseif ($status_raw === '1' || $status_raw === 1 || $status_raw === '' || $status_raw === null) {
                $status = 'ACTIVE';
            }
            
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
                'post' => $_POST,
                'files' => isset($_FILES) ? array_map(function($f){ return array('name'=>isset($f['name'])?$f['name']:null,'error'=>isset($f['error'])?$f['error']:null); }, $_FILES) : array()
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

