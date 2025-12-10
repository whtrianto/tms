<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_tool_draw_engin $tool_draw_engin
 */
class Tool_draw_engin extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('form_validation', 'session'));
        
        // capture current user id/username for later use (fallback to SYSTEM)
        $username_from_session = $this->session->userdata('username');
        $this->uid = (string) ($username_from_session ?: 'SYSTEM');
        log_message('debug', '[Tool_draw_engin::__construct] username_from_session=' . var_export($username_from_session, true) . ', uid="' . $this->uid . '"');
        
        // load model AFTER setting uid, then assign uid to model
        $this->load->model('M_tool_draw_engin', 'tool_draw_engin');
        $this->tool_draw_engin->uid = $this->uid;
        log_message('debug', '[Tool_draw_engin::__construct] model uid set to "' . $this->tool_draw_engin->uid . '"');
        
        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $data['list_data'] = $this->tool_draw_engin->get_all();
        $data['products'] = $this->tool_draw_engin->get_products();
        $data['operations'] = $this->tool_draw_engin->get_operations();
        $data['tools'] = $this->tool_draw_engin->get_tools();
        $data['materials'] = $this->tool_draw_engin->get_materials();

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

        // Resolve tool ID from TD_TOOL_NAME (can be numeric ID or tool name)
        $row['TD_TOOL_ID'] = null;
        $tools = $this->tool_draw_engin->get_tools();
        if (isset($row['TD_TOOL_NAME']) && $row['TD_TOOL_NAME'] !== '') {
            // First try: if TD_TOOL_NAME is numeric, treat it as TOOL_ID
            if (is_numeric($row['TD_TOOL_NAME'])) {
                $tid = (int)$row['TD_TOOL_NAME'];
                foreach ($tools as $t) {
                    if ((int)$t['TOOL_ID'] === $tid) {
                        $row['TD_TOOL_ID'] = $tid;
                        break;
                    }
                }
            } else {
                // Second try: match by tool name
                $tool_name = trim($row['TD_TOOL_NAME']);
                foreach ($tools as $t) {
                    if (strcasecmp(trim($t['TOOL_NAME']), $tool_name) === 0) {
                        $row['TD_TOOL_ID'] = (int)$t['TOOL_ID'];
                        break;
                    }
                }
            }
        }

        // Get Tool BOM by Product ID
        $tool_bom_list = array();
        if (isset($row['TD_PRODUCT_ID']) && (int)$row['TD_PRODUCT_ID'] > 0) {
            $tool_bom_list = $this->tool_draw_engin->get_tool_bom_by_product_id((int)$row['TD_PRODUCT_ID']);
        }

        $data = array();
        $data['drawing'] = $row;
        $data['products'] = $this->tool_draw_engin->get_products();
        $data['operations'] = $this->tool_draw_engin->get_operations();
        $data['tools'] = $tools;
        $data['materials'] = $this->tool_draw_engin->get_materials();
        $data['tool_bom_list'] = $tool_bom_list;

        $this->view('edit_tool_draw_engin', $data, FALSE);
    }

    /**
     * Halaman revision Tool Drawing Engineering
     * @param int $id
     */
    public function revision_page($id = 0)
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

        // Resolve tool ID from TD_TOOL_NAME (can be numeric ID or tool name)
        $row['TD_TOOL_ID'] = null;
        $tools = $this->tool_draw_engin->get_tools();
        if (isset($row['TD_TOOL_NAME']) && $row['TD_TOOL_NAME'] !== '') {
            // First try: if TD_TOOL_NAME is numeric, treat it as TOOL_ID
            if (is_numeric($row['TD_TOOL_NAME'])) {
                $tid = (int)$row['TD_TOOL_NAME'];
                foreach ($tools as $t) {
                    if ((int)$t['TOOL_ID'] === $tid) {
                        $row['TD_TOOL_ID'] = $tid;
                        break;
                    }
                }
            } else {
                // Second try: match by tool name
                $tool_name = trim($row['TD_TOOL_NAME']);
                foreach ($tools as $t) {
                    if (strcasecmp(trim($t['TOOL_NAME']), $tool_name) === 0) {
                        $row['TD_TOOL_ID'] = (int)$t['TOOL_ID'];
                        break;
                    }
                }
            }
        }

        // Increment revision
        $row['TD_REVISION'] = (isset($row['TD_REVISION']) ? (int)$row['TD_REVISION'] : 0) + 1;

        // Get Tool BOM by Product ID
        $tool_bom_list = array();
        if (isset($row['TD_PRODUCT_ID']) && (int)$row['TD_PRODUCT_ID'] > 0) {
            $tool_bom_list = $this->tool_draw_engin->get_tool_bom_by_product_id((int)$row['TD_PRODUCT_ID']);
        }

        $data = array();
        $data['drawing'] = $row;
        $data['products'] = $this->tool_draw_engin->get_products();
        $data['operations'] = $this->tool_draw_engin->get_operations();
        $data['tools'] = $tools;
        $data['materials'] = $this->tool_draw_engin->get_materials();
        $data['tool_bom_list'] = $tool_bom_list;

        $this->view('revision_tool_draw_engin', $data, FALSE);
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
        
        // Enrich history with resolved names
        $products = $this->tool_draw_engin->get_products();
        $operations = $this->tool_draw_engin->get_operations();
        $tools = $this->tool_draw_engin->get_tools();
        $materials = $this->tool_draw_engin->get_materials();
        $makers = $this->tool_draw_engin->get_makers();

        foreach ($history as &$h) {
            // Resolve product name - use history data first, fallback to current record
            $h['PRODUCT_NAME'] = '';
            $product_id_to_resolve = isset($h['TD_PRODUCT_ID']) ? (int)$h['TD_PRODUCT_ID'] : 0;
            
            // If history doesn't have valid product ID, use current record's product ID
            if ($product_id_to_resolve <= 0 && isset($row['TD_PRODUCT_ID']) && (int)$row['TD_PRODUCT_ID'] > 0) {
                $product_id_to_resolve = (int)$row['TD_PRODUCT_ID'];
            }
            
            if ($product_id_to_resolve > 0) {
                foreach ($products as $p) {
                    if ((int)$p['PRODUCT_ID'] === $product_id_to_resolve) {
                        $h['PRODUCT_NAME'] = $p['PRODUCT_NAME'];
                        break;
                    }
                }
            }
            
            // Resolve operation/process name - use history data first, fallback to current record
            $h['OPERATION_NAME'] = '';
            $process_id_to_resolve = isset($h['TD_PROCESS_ID']) ? (int)$h['TD_PROCESS_ID'] : 0;
            
            // If history doesn't have valid process ID, use current record's process ID
            if ($process_id_to_resolve <= 0 && isset($row['TD_PROCESS_ID']) && (int)$row['TD_PROCESS_ID'] > 0) {
                $process_id_to_resolve = (int)$row['TD_PROCESS_ID'];
            }
            
            if ($process_id_to_resolve > 0) {
                foreach ($operations as $o) {
                    if ((int)$o['OPERATION_ID'] === $process_id_to_resolve) {
                        $h['OPERATION_NAME'] = $o['OPERATION_NAME'];
                        break;
                    }
                }
            }
            
            // Resolve tool name
            $h['TOOL_RESOLVED_NAME'] = isset($h['TD_TOOL_NAME']) ? $h['TD_TOOL_NAME'] : '';
            if (is_numeric($h['TD_TOOL_NAME'])) {
                $trow = $this->tool_draw_engin->get_tool_by_id((int)$h['TD_TOOL_NAME']);
                if ($trow) $h['TOOL_RESOLVED_NAME'] = $trow['TOOL_NAME'];
            } elseif (empty($h['TOOL_RESOLVED_NAME']) && isset($row['TD_TOOL_NAME']) && $row['TD_TOOL_NAME'] !== '') {
                // Fallback to current record's tool name
                $h['TOOL_RESOLVED_NAME'] = $row['TD_TOOL_NAME'];
                if (is_numeric($h['TOOL_RESOLVED_NAME'])) {
                    $trow = $this->tool_draw_engin->get_tool_by_id((int)$h['TOOL_RESOLVED_NAME']);
                    if ($trow) $h['TOOL_RESOLVED_NAME'] = $trow['TOOL_NAME'];
                }
            }

            // Resolve material name - use history data first, fallback to current record
            $h['MATERIAL_NAME'] = '';
            $material_id_to_resolve = isset($h['TD_MATERIAL_ID']) ? (int)$h['TD_MATERIAL_ID'] : 0;
            
            // If history doesn't have valid material ID, use current record's material ID
            if ($material_id_to_resolve <= 0 && isset($row['TD_MATERIAL_ID']) && (int)$row['TD_MATERIAL_ID'] > 0) {
                $material_id_to_resolve = (int)$row['TD_MATERIAL_ID'];
            }
            
            if ($material_id_to_resolve > 0) {
                foreach ($materials as $mat) {
                    if ((int)$mat['MATERIAL_ID'] === $material_id_to_resolve) {
                        $h['MATERIAL_NAME'] = $mat['MATERIAL_NAME'];
                        break;
                    }
                }
            }

            // Resolve maker name
            $h['MAKER_NAME'] = '';
            $maker_id_to_resolve = isset($h['TD_MAKER_ID']) ? (int)$h['TD_MAKER_ID'] : 0;
            if ($maker_id_to_resolve <= 0 && isset($row['TD_MAKER_ID']) && (int)$row['TD_MAKER_ID'] > 0) {
                $maker_id_to_resolve = (int)$row['TD_MAKER_ID'];
            }
            if ($maker_id_to_resolve > 0) {
                foreach ($makers as $m) {
                    if ((int)$m['MAKER_ID'] === $maker_id_to_resolve) {
                        $h['MAKER_NAME'] = $m['MAKER_NAME'];
                        break;
                    }
                }
            }
        }

        $data = array();
        $data['drawing'] = $row;
        $data['history'] = $history;
        $data['products'] = $products;
        $data['operations'] = $operations;
        $data['materials'] = $materials;

        $this->view('history_tool_draw_engin', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT Tool Drawing Engineering (AJAX)
     */
    public function submit_data()
    {
        // Clear output buffers to ensure clean JSON response
        if (ob_get_level()) ob_clean();
        
        $this->output->set_content_type('application/json');
        $result = array('success' => false, 'message' => '');
        
        try {
            $action = strtoupper($this->input->post('action', TRUE));
            $id     = (int)$this->input->post('TD_ID', TRUE);

            // validation rules
            $this->form_validation->set_rules('TD_PRODUCT_ID', 'Product ID', 'required|integer');
            $this->form_validation->set_rules('TD_PROCESS_ID', 'Process ID', 'required|integer');
            // Drawing now can be uploaded as an image file. We validate presence later (file or old filename).
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
            // handle uploaded file (TD_DRAWING_FILE) or use old filename (TD_DRAWING_NO_OLD)
            $drawing_no = '';
            if (!empty($_FILES) && isset($_FILES['TD_DRAWING_FILE']) && !empty($_FILES['TD_DRAWING_FILE']['name'])) {
                // save drawing under project/tool_engineering/img/
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
            // TD_TOOL_NAME from form now contains TOOL_ID (select). Resolve to tool name if possible.
            $tool_field = $this->input->post('TD_TOOL_NAME', TRUE);
            $tool_name = '';
            if (is_numeric($tool_field) && (int)$tool_field > 0) {
                $toolRow = $this->tool_draw_engin->get_tool_by_id((int)$tool_field);
                if ($toolRow) $tool_name = $toolRow['TOOL_NAME'];
            } else {
                $tool_name = $tool_field;
            }
            $revision   = (int)$this->input->post('TD_REVISION', TRUE);
            $status     = (int)$this->input->post('TD_STATUS', TRUE);
            $material_id = (int)$this->input->post('TD_MATERIAL_ID', TRUE);
            $maker_id   = (int)$this->input->post('TD_MAKER_ID', TRUE);
            // Kolom tooling (maker, qty, price, tool life, description, sequence) HANYA
            // diisi dari UI Tooling (bukan dari UI Engineering).
            // Di UI Engineering (edit_tool_draw_engin & revision_tool_draw_engin) kolom-kolom
            // ini tidak ada di form, jadi di sini kita JANGAN pakai nilai POST-nya
            // untuk action EDIT/REVISION agar tidak mengosongkan data lama.
            //
            // Untuk action ADD (tambah data baru) kita tetap baca dari POST
            // jika memang dikirim (misalnya integrasi dari tooling).
            $min_qty        = $this->input->post('TD_MIN_QTY', TRUE);
            $replenish_qty  = $this->input->post('TD_REPLENISH_QTY', TRUE);
            $price_val      = $this->input->post('TD_PRICE', TRUE);
            $tool_life      = $this->input->post('TD_TOOL_LIFE', TRUE);
            $sequence       = $this->input->post('TD_SEQUENCE', TRUE);
            $description    = $this->input->post('TD_DESCRIPTION', TRUE);
            $min_qty        = ($min_qty === '' || $min_qty === null) ? null : (int)$min_qty;
            $replenish_qty  = ($replenish_qty === '' || $replenish_qty === null) ? null : (int)$replenish_qty;
            $price_val      = ($price_val === '' || $price_val === null) ? null : (float)$price_val;
            $tool_life      = ($tool_life === '' || $tool_life === null) ? null : (int)$tool_life;
            $sequence       = ($sequence === '' || $sequence === null) ? null : (int)$sequence;

            if ($action === 'ADD') {
                if (empty($drawing_no)) {
                    $result['message'] = 'Drawing wajib diunggah.';
                    $json = json_encode($result);
                    log_message('debug', '[submit_data ADD] response: ' . $json);
                    echo $json;
                    return;
                }
                $ok = $this->tool_draw_engin->add_data($product_id, $process_id, $drawing_no, $tool_name, $revision, $status, $material_id, $maker_id, $min_qty, $replenish_qty, $price_val, $tool_life, $description, $sequence);
                if ($ok === true) {
                    $result['success'] = true;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Tool Drawing Engineering berhasil ditambahkan.';
                } else {
                    $result['success'] = false;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Gagal menambahkan tool drawing.';
                }
                $json = json_encode($result);
                log_message('debug', '[submit_data ADD] response: ' . $json);
                echo $json;
                return;
            }

            if ($action === 'EDIT' && $id > 0) {
                $current = $this->tool_draw_engin->get_by_id($id);
                if (!$current) {
                    $result['message'] = 'Data tidak ditemukan.';
                    $json = json_encode($result);
                    log_message('debug', '[submit_data EDIT] response: ' . $json);
                    echo $json;
                    return;
                }

                if (empty($drawing_no)) {
                    // if no drawing provided and no old drawing, reject
                    $result['message'] = 'Drawing wajib ada.';
                    $json = json_encode($result);
                    log_message('debug', '[submit_data EDIT] response: ' . $json);
                    echo $json;
                    return;
                }

                // EDIT dari layar Engineering:
                // - hanya update kolom utama (product, process, drawing, tool, status, material)
                // - kolom tooling (maker, qty, price, dll) tetap memakai nilai lama di DB
                $ok = $this->tool_draw_engin->edit_data_engineering(
                    $id,
                    $product_id,
                    $process_id,
                    $drawing_no,
                    $tool_name,
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
                $json = json_encode($result);
                log_message('debug', '[submit_data EDIT] response: ' . $json);
                echo $json;
                return;
            }

            if ($action === 'REVISION' && $id > 0) {
                $current = $this->tool_draw_engin->get_by_id($id);
                if (!$current) {
                    $result['message'] = 'Data tidak ditemukan.';
                    $json = json_encode($result);
                    log_message('debug', '[submit_data REVISION] response: ' . $json);
                    echo $json;
                    return;
                }

                // For REVISION: keep old drawing if no new file uploaded
                if (empty($drawing_no)) {
                    $drawing_no = $current['TD_DRAWING_NO'];
                }

                if (empty($drawing_no)) {
                    $result['message'] = 'Drawing tidak ada untuk di-revisi.';
                    $json = json_encode($result);
                    log_message('debug', '[submit_data REVISION] response: ' . $json);
                    echo $json;
                    return;
                }

                // REVISION dari layar Engineering:
                // - sama seperti EDIT, hanya kolom utama yang di-update.
                // - revision number akan otomatis naik di dalam model.
                $ok = $this->tool_draw_engin->edit_data_engineering(
                    $id,
                    $product_id,
                    $process_id,
                    $drawing_no,
                    $tool_name,
                    $status,
                    $material_id
                );
                if ($ok === true) {
                    $result['success'] = true;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Revision berhasil ditambahkan (v' . $revision . ').';
                } else {
                    $result['success'] = false;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Gagal menambahkan revision.';
                }
                $json = json_encode($result);
                log_message('debug', '[submit_data REVISION] response: ' . $json);
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
            log_message('error', '[Tool_draw_engin::submit_data] Exception: ' . $e->getMessage() . ' | Context: ' . json_encode($ctx));
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
     * get_tool_draw_engin_detail: ambil data by id (AJAX)
     */
    public function get_tool_draw_engin_detail()
    {
        // Clear output buffers to ensure clean JSON response
        if (ob_get_level()) ob_clean();
        
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TD_ID', TRUE);
        if ($id <= 0) {
            $result = array('success' => false, 'message' => 'TD_ID tidak ditemukan.');
            $this->output->set_output(json_encode($result));
            return;
        }

        $row = $this->tool_draw_engin->get_by_id($id);
        if ($row) {
            // try to resolve TD_TOOL_NAME to tool id if possible
            $row['TD_TOOL_ID'] = null;
            $tools = $this->tool_draw_engin->get_tools();
            // if TD_TOOL_NAME is numeric and matches TOOL_ID
            if (isset($row['TD_TOOL_NAME']) && is_numeric($row['TD_TOOL_NAME'])) {
                $tid = (int)$row['TD_TOOL_NAME'];
                foreach ($tools as $t) {
                    if ((int)$t['TOOL_ID'] === $tid) {
                        $row['TD_TOOL_ID'] = $tid;
                        break;
                    }
                }
            }
            // else try match by name
            if ($row['TD_TOOL_ID'] === null && isset($row['TD_TOOL_NAME'])) {
                $name = trim($row['TD_TOOL_NAME']);
                foreach ($tools as $t) {
                    if (strcasecmp(trim($t['TOOL_NAME']), $name) === 0) {
                        $row['TD_TOOL_ID'] = (int)$t['TOOL_ID'];
                        break;
                    }
                }
            }

            // resolve product and operation names for convenience in detail response
            $row['TD_PRODUCT_NAME'] = '';
            $row['TD_OPERATION_NAME'] = '';
            foreach ($this->tool_draw_engin->get_products() as $p) {
                if (isset($row['TD_PRODUCT_ID']) && (int)$p['PRODUCT_ID'] === (int)$row['TD_PRODUCT_ID']) {
                    $row['TD_PRODUCT_NAME'] = $p['PRODUCT_NAME'];
                    break;
                }
            }
            foreach ($this->tool_draw_engin->get_operations() as $o) {
                if (isset($row['TD_PROCESS_ID']) && (int)$o['OPERATION_ID'] === (int)$row['TD_PROCESS_ID']) {
                    $row['TD_OPERATION_NAME'] = $o['OPERATION_NAME'];
                    break;
                }
            }
            // resolve tool name
            $row['TD_TOOL_RESOLVED_NAME'] = isset($row['TD_TOOL_NAME']) ? $row['TD_TOOL_NAME'] : '';
            if ($row['TD_TOOL_ID'] !== null) {
                $trow = $this->tool_draw_engin->get_tool_by_id($row['TD_TOOL_ID']);
                if ($trow) $row['TD_TOOL_RESOLVED_NAME'] = $trow['TOOL_NAME'];
            }

            $result = array('success' => true, 'data' => $row);
            $this->output->set_output(json_encode($result));
        } else {
            $result = array('success' => false, 'message' => 'Data tidak ditemukan.');
            $this->output->set_output(json_encode($result));
        }
    }

    /**
     * get_tool_bom_by_product: Get Tool BOM by Product ID (AJAX)
     */
    public function get_tool_bom_by_product()
    {
        // Clear output buffers to ensure clean JSON response
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

    /**
     * get_history_by_id: Get revision history for a specific tool drawing
     */
    public function get_history_by_id()
    {
        // Clear output buffers to ensure clean JSON response
        if (ob_get_level()) ob_clean();
        
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TD_ID', TRUE);
        log_message('debug', '[get_history_by_id] received TD_ID=' . var_export($id, true));
        if ($id <= 0) {
            $result = array('success' => false, 'message' => 'TD_ID tidak ditemukan.');
            $this->output->set_output(json_encode($result));
            return;
        }

        $history = $this->tool_draw_engin->get_history($id);
        log_message('debug', '[get_history_by_id] model returned ' . var_export(is_array($history) ? count($history) : $history, true) . ' history rows');
        if ($history && count($history) > 0) {
            // Enrich history with resolved names (product, operation, tool, etc)
            $products = $this->tool_draw_engin->get_products();
            $operations = $this->tool_draw_engin->get_operations();
            $tools = $this->tool_draw_engin->get_tools();
            $materials = $this->tool_draw_engin->get_materials();
            $makers = $this->tool_draw_engin->get_makers();

            foreach ($history as &$h) {
                // Resolve product name
                $h['PRODUCT_NAME'] = '';
                foreach ($products as $p) {
                    if ((int)$p['PRODUCT_ID'] === (int)$h['TD_PRODUCT_ID']) {
                        $h['PRODUCT_NAME'] = $p['PRODUCT_NAME'];
                        break;
                    }
                }
                
                // Resolve operation name
                $h['OPERATION_NAME'] = '';
                foreach ($operations as $o) {
                    if ((int)$o['OPERATION_ID'] === (int)$h['TD_PROCESS_ID']) {
                        $h['OPERATION_NAME'] = $o['OPERATION_NAME'];
                        break;
                    }
                }
                
                // Resolve tool name
                $h['TOOL_RESOLVED_NAME'] = isset($h['TD_TOOL_NAME']) ? $h['TD_TOOL_NAME'] : '';
                if (is_numeric($h['TD_TOOL_NAME'])) {
                    $trow = $this->tool_draw_engin->get_tool_by_id((int)$h['TD_TOOL_NAME']);
                    if ($trow) $h['TOOL_RESOLVED_NAME'] = $trow['TOOL_NAME'];
                }

                // Resolve material name
                $h['MATERIAL_NAME'] = '';
                foreach ($materials as $mat) {
                    if ((int)$mat['MATERIAL_ID'] === (int)(isset($h['TD_MATERIAL_ID']) ? $h['TD_MATERIAL_ID'] : 0)) {
                        $h['MATERIAL_NAME'] = $mat['MATERIAL_NAME'];
                        break;
                    }
                }

                // Resolve maker name
                $h['MAKER_NAME'] = '';
                foreach ($makers as $m) {
                    if ((int)$m['MAKER_ID'] === (int)(isset($h['TD_MAKER_ID']) ? $h['TD_MAKER_ID'] : 0)) {
                        $h['MAKER_NAME'] = $m['MAKER_NAME'];
                        break;
                    }
                }

                // Fallback: if still missing, try current TD record
                if ((empty($h['PRODUCT_NAME']) || empty($h['MATERIAL_NAME']) || empty($h['MAKER_NAME'])) && isset($h['TD_ID']) && (int)$h['TD_ID'] > 0) {
                    $current = $this->tool_draw_engin->get_by_id((int)$h['TD_ID']);
                    if ($current) {
                        if (empty($h['PRODUCT_NAME']) && isset($current['TD_PRODUCT_ID'])) {
                            foreach ($products as $p) { if ((int)$p['PRODUCT_ID'] === (int)$current['TD_PRODUCT_ID']) { $h['PRODUCT_NAME'] = $p['PRODUCT_NAME']; break; } }
                        }
                        if (empty($h['MATERIAL_NAME']) && isset($current['TD_MATERIAL_ID'])) {
                            foreach ($materials as $mat) { if ((int)$mat['MATERIAL_ID'] === (int)$current['TD_MATERIAL_ID']) { $h['MATERIAL_NAME'] = $mat['MATERIAL_NAME']; break; } }
                        }
                        // Also attempt to resolve operation name if missing in history
                        if (empty($h['OPERATION_NAME']) && isset($current['TD_PROCESS_ID'])) {
                            foreach ($operations as $op) { if ((int)$op['OPERATION_ID'] === (int)$current['TD_PROCESS_ID']) { $h['OPERATION_NAME'] = $op['OPERATION_NAME']; break; } }
                        }
                        if (empty($h['MAKER_NAME']) && isset($current['TD_MAKER_ID'])) {
                            foreach ($makers as $m2) { if ((int)$m2['MAKER_ID'] === (int)$current['TD_MAKER_ID']) { $h['MAKER_NAME'] = $m2['MAKER_NAME']; break; } }
                        }
                    }
                }
            }
            log_message('debug', '[get_history_by_id] returning history payload for TD_ID=' . $id);
            $result = array('success' => true, 'data' => $history);
            $this->output->set_output(json_encode($result));
        } else {
            log_message('debug', '[get_history_by_id] no history for TD_ID=' . $id);
            $result = array('success' => false, 'message' => 'Tidak ada history untuk record ini.');
            $this->output->set_output(json_encode($result));
        }
    }
}

