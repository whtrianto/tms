<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller alternatif untuk list Tool Drawing menggunakan struktur DB lama (struktur-tms.sql)
 * View: index_tool_draw_engin.php
 */
class Tool_draw_engin extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        
        // Capture current user id/username for later use (fallback to SYSTEM)
        $username_from_session = $this->session->userdata('username');
        $this->uid = (string) ($username_from_session ?: 'SYSTEM');
        
        $this->load->model('M_tool_draw_engin', 'tool_draw_engin');
        $this->tool_draw_engin->uid = $this->uid;
        
        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $data['list_data'] = $this->tool_draw_engin->get_all();

        $this->view('index_tool_draw_engin', $data, FALSE);
    }

    /**
     * Halaman add Tool Drawing Engineering
     */
    public function add_page()
    {
        $data = array();
        $data['products'] = $this->tool_draw_engin->get_products();
        $data['operations'] = $this->tool_draw_engin->get_operations();
        $data['tools'] = $this->tool_draw_engin->get_tools();
        $data['materials'] = $this->tool_draw_engin->get_materials();

        $this->view('add_tool_draw_engin', $data, FALSE);
    }

    /**
     * Halaman edit Tool Drawing Engineering
     * @param int $id
     */
    public function edit_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_draw_engin->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        // Resolve tool ID
        $row['TD_TOOL_ID'] = isset($row['TD_TOOL_ID']) ? (int)$row['TD_TOOL_ID'] : null;

        // Get Tool BOM by Product ID
        $tool_bom_list = array();
        if (isset($row['TD_PRODUCT_ID']) && (int)$row['TD_PRODUCT_ID'] > 0) {
            $tool_bom_list = $this->tool_draw_engin->get_tool_bom_by_product_id((int)$row['TD_PRODUCT_ID']);
        }

        $data = array();
        $data['drawing'] = $row;
        $data['products'] = $this->tool_draw_engin->get_products();
        $data['operations'] = $this->tool_draw_engin->get_operations();
        $data['tools'] = $this->tool_draw_engin->get_tools();
        $data['materials'] = $this->tool_draw_engin->get_materials();
        $data['tool_bom_list'] = $tool_bom_list;

        $this->view('edit_tool_draw_engin', $data, FALSE);
    }

    /**
     * Halaman history Tool Drawing Engineering
     * @param int $id
     */
    public function history_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_draw_engin->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        $history = $this->tool_draw_engin->get_history($id);

        $data = array();
        $data['drawing'] = $row;
        $data['history'] = $history;

        $this->view('history_tool_draw_engin', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT Tool Drawing Engineering (AJAX)
     */
    public function submit_data()
    {
        if (ob_get_level()) ob_clean();
        
        $this->output->set_content_type('application/json');
        $result = array('success' => false, 'message' => '');
        
        try {
            $action = strtoupper($this->input->post('action', TRUE));
            $id     = (int)$this->input->post('TD_ID', TRUE);

            // Validation rules
            $this->form_validation->set_rules('TD_PRODUCT_ID', 'Product ID', 'required|integer');
            $this->form_validation->set_rules('TD_PROCESS_ID', 'Process ID', 'required|integer');
            $this->form_validation->set_rules('TD_TOOL_NAME', 'Tool Name', 'trim');
            $this->form_validation->set_rules('TD_REVISION', 'Revision', 'integer');
            $this->form_validation->set_rules('TD_STATUS', 'Status', 'integer');
            $this->form_validation->set_rules('TD_MATERIAL_ID', 'Material ID', 'integer');

            if ($this->form_validation->run() == FALSE) {
                $this->form_validation->set_error_delimiters('', '');
                $result['message'] = validation_errors() ?: 'Data tidak valid.';
                echo json_encode($result);
                return;
            }

            $product_id = (int)$this->input->post('TD_PRODUCT_ID', TRUE);
            $process_id = (int)$this->input->post('TD_PROCESS_ID', TRUE);
            
            // Handle uploaded file (TD_DRAWING_FILE) or use old filename (TD_DRAWING_NO_OLD)
            $drawing_no = '';
            if (!empty($_FILES) && isset($_FILES['TD_DRAWING_FILE']) && !empty($_FILES['TD_DRAWING_FILE']['name'])) {
                $uploadDir = FCPATH . 'tool_engineering/img/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $origName = $_FILES['TD_DRAWING_FILE']['name'];
                $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($origName));
                $fileName = 'TD_' . time() . '_' . $safeName;
                $target = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['TD_DRAWING_FILE']['tmp_name'], $target)) {
                    $drawing_no = $fileName;
                } else {
                    $result['message'] = 'Gagal mengunggah file drawing.';
                    echo json_encode($result);
                    return;
                }
            } else {
                $drawing_no = $this->input->post('TD_DRAWING_NO_OLD', TRUE);
                if ($drawing_no === '' || $drawing_no === null) {
                    $drawing_no = $this->input->post('TD_DRAWING_NO', TRUE);
                }
            }

            // TD_TOOL_NAME from form contains TOOL_ID (select)
            $tool_id = (int)$this->input->post('TD_TOOL_NAME', TRUE);
            $revision = (int)$this->input->post('TD_REVISION', TRUE);
            $status = (int)$this->input->post('TD_STATUS', TRUE);
            $material_id = (int)$this->input->post('TD_MATERIAL_ID', TRUE);
            $maker_id = (int)$this->input->post('TD_MAKER_ID', TRUE);

            if ($action === 'ADD') {
                if (empty($drawing_no)) {
                    $result['message'] = 'Drawing wajib diunggah.';
                    echo json_encode($result);
                    return;
                }
                $ok = $this->tool_draw_engin->add_data($product_id, $process_id, $drawing_no, $tool_id, $revision, $status, $material_id, $maker_id);
                if ($ok === true) {
                    $result['success'] = true;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Tool Drawing Engineering berhasil ditambahkan.';
                } else {
                    $result['success'] = false;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Gagal menambahkan tool drawing.';
                }
                echo json_encode($result);
                return;
            }

            if ($action === 'EDIT' && $id > 0) {
                $current = $this->tool_draw_engin->get_by_id($id);
                if (!$current) {
                    $result['message'] = 'Data tidak ditemukan.';
                    echo json_encode($result);
                    return;
                }

                if (empty($drawing_no)) {
                    $result['message'] = 'Drawing wajib ada.';
                    echo json_encode($result);
                    return;
                }

                $ok = $this->tool_draw_engin->edit_data_engineering(
                    $id,
                    $product_id,
                    $process_id,
                    $drawing_no,
                    $tool_id,
                    $status,
                    $material_id
                );
                if ($ok === true) {
                    $result['success'] = true;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Tool Drawing Engineering berhasil diperbarui.';
                } else {
                    $result['success'] = false;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Gagal memperbarui tool drawing.';
                }
                echo json_encode($result);
                return;
            }

            $result['message'] = 'Parameter action/ID tidak valid.';
            echo json_encode($result);
            return;
            
        } catch (Exception $e) {
            log_message('error', '[Tool_draw_engin::submit_data] Exception: ' . $e->getMessage());
            $result['success'] = false;
            $result['message'] = 'Server error. Cek log untuk detail.';
            echo json_encode($result);
            return;
        }
    }

    /**
     * delete_data: delete tool drawing
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TD_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'TD_ID tidak ditemukan.'));
            return;
        }

        $ok = $this->tool_draw_engin->delete_data($id);
        if ($ok) {
            echo json_encode(array('success' => true, 'message' => $this->tool_draw_engin->messages ?: 'Tool Drawing Engineering berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->tool_draw_engin->messages ?: 'Gagal menghapus tool drawing.'));
        }
    }

    /**
     * get_tool_bom_by_product: Get Tool BOM by Product ID (AJAX)
     */
    public function get_tool_bom_by_product()
    {
        if (ob_get_level()) ob_clean();
        
        $this->output->set_content_type('application/json');

        $product_id = (int)$this->input->post('PRODUCT_ID', TRUE);
        if ($product_id <= 0) {
            $result = array('success' => false, 'message' => 'PRODUCT_ID tidak ditemukan.');
            $this->output->set_output(json_encode($result));
            return;
        }

        $tool_bom_list = $this->tool_draw_engin->get_tool_bom_by_product_id($product_id);
        
        // Get product name for display
        $product_name = '';
        $products = $this->tool_draw_engin->get_products();
        foreach ($products as $p) {
            if ((int)$p['PRODUCT_ID'] === $product_id) {
                $product_name = $p['PRODUCT_NAME'];
                break;
            }
        }

        $result = array(
            'success' => true,
            'data' => $tool_bom_list,
            'product_name' => $product_name
        );
        $this->output->set_output(json_encode($result));
    }
}

