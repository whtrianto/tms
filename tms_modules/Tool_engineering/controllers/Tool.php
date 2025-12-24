<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_tool $tool
 * @property M_tool_type $tool_type
 */
class Tool extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('M_tool', 'tool');

        // Candidate model paths relative to APPPATH
        $candidates = [
            'tms_modules/tool_engineering/models/M_tool_type',
            'tms_modules/tool_engineering/models/M_tool_type.php',
            'models/M_tool_type.php',
            'models/tool_engineering/M_tool_type.php',
            'M_tool_type'
        ];

        $loaded = false;
        foreach ($candidates as $c) {
            $try = str_replace('.php', '', $c);
            // Skip if already loaded successfully
            if ($loaded) break;
            try {
                // if path contains '/', give raw path to load->model; otherwise try name
                $this->load->model($try, 'tool_type');
                if (isset($this->tool_type) && is_object($this->tool_type) && method_exists($this->tool_type, 'get_active')) {
                    $loaded = true;
                    break;
                } else {
                    // unset incorrect instance
                    if (isset($this->tool_type)) unset($this->tool_type);
                }
            } catch (Exception $e) {
                if (isset($this->tool_type)) unset($this->tool_type);
            }
        }

        // Fallback: try direct include + instantiate (if file exists)
        if (!$loaded) {
            $possibleFiles = [
                APPPATH . 'tms_modules/tool_engineering/models/M_tool_type.php',
                APPPATH . 'models/M_tool_type.php',
                APPPATH . 'models/tool_engineering/M_tool_type.php'
            ];
            foreach ($possibleFiles as $f) {
                if (is_file($f) && is_readable($f)) {
                    try {
                        require_once($f);
                        if (class_exists('M_tool_type')) {
                            // instantiate (model can still use get_instance() to access CI)
                            $this->tool_type = new M_tool_type();
                            if (method_exists($this->tool_type, 'get_active')) {
                                $loaded = true;
                                break;
                            } else {
                                unset($this->tool_type);
                            }
                        }
                    } catch (Exception $e) {
                        if (isset($this->tool_type)) unset($this->tool_type);
                    }
                }
            }
        }

        // If still not loaded, leave tool_type unset and proceed (controller already checks isset before use)
        $this->load->library(['form_validation', 'session']);
        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $search = $this->input->get('search', true);
        $data = array();
        $data['list_data'] = $this->tool->get_all($search);

        // tool types for select (if model exists)
        $data['tool_types'] = (isset($this->tool_type) && method_exists($this->tool_type, 'get_active')) ? $this->tool_type->get_active() : array();

        $this->view('index_tool', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT Tool (AJAX)
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');
        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('TC_ID', TRUE);

        // validation
        $this->form_validation->set_rules('TC_NAME', 'Tool Name', 'required|trim');
        $this->form_validation->set_rules('TC_TYPE', 'Tool Type', 'trim');
        $this->form_validation->set_rules('TC_DESC', 'Description', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $name = $this->input->post('TC_NAME', TRUE);
        $type = $this->input->post('TC_TYPE', TRUE);
        $desc = $this->input->post('TC_DESC', TRUE);

        $tool_type_id = ($type === '' || $type === null) ? null : (int)$type;

        $data = array(
            'TC_NAME' => $name,
            'TC_TYPE' => $tool_type_id,
            'TC_DESC' => $desc
        );

        if ($action === 'ADD') {
            // duplicate check by name
            if ($this->tool->exists_by_name($data['TC_NAME'])) {
                echo json_encode(array('success' => false, 'message' => 'Tool name sudah digunakan.'));
                return;
            }

            $ok = $this->tool->add_data($data);
            if ($ok) {
                $row = $this->tool->get_by_name($data['TC_NAME']);
                $new_id = $row ? (int)$row['TC_ID'] : null;
                echo json_encode(array('success' => true, 'message' => $this->tool->messages ?: 'Tool berhasil ditambahkan.', 'new_id' => $new_id));
                return;
            } else {
                echo json_encode(array('success' => false, 'message' => $this->tool->messages ?: 'Gagal menambahkan tool.'));
                return;
            }
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->tool->get_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
                return;
            }

            // exclude current id on duplicate check
            if ($this->tool->is_duplicate($data['TC_NAME'], $id)) {
                echo json_encode(array('success' => false, 'message' => 'Tool name sudah digunakan oleh data lain.'));
                return;
            }

            $ok = $this->tool->edit_data($id, $data);
            if ($ok) {
                echo json_encode(array('success' => true, 'message' => $this->tool->messages ?: 'Tool berhasil diperbarui.'));
                return;
            } else {
                echo json_encode(array('success' => false, 'message' => $this->tool->messages ?: 'Gagal memperbarui tool.'));
                return;
            }
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    /**
     * Soft delete
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TC_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'TC_ID tidak ditemukan.'));
            return;
        }

        $ok = $this->tool->delete_data($id);
        if ($ok) {
            echo json_encode(array('success' => true, 'message' => $this->tool->messages ?: 'Tool berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->tool->messages ?: 'Gagal menghapus tool.'));
        }
    }

    /**
     * AJAX: ambil data by id
     */
    public function get_tool_detail()
    {
        $this->output->set_content_type('application/json');
        $id = (int)$this->input->post('TC_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'TC_ID tidak ditemukan.'));
            return;
        }
        $row = $this->tool->get_by_id($id);
        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }

    /**
     * Page detail tool
     */
    public function detail($id = null)
    {
        if ($id === null) show_404();
        $id = (int)$id;
        if ($id <= 0) show_404();

        $row = $this->tool->get_by_id($id);
        if (!$row) show_404();

        // jika tool_type model ada, ambil nama tipe
        $type_name = '';
        if (!empty($row['TC_TYPE']) && isset($this->tool_type) && method_exists($this->tool_type, 'get_by_id')) {
            $tt = $this->tool_type->get_by_id($row['TC_TYPE']);
            $type_name = $tt ? (isset($tt['TT_NAME']) ? $tt['TT_NAME'] : '') : '';
        }

        $data['tool'] = array(
            'TC_ID'   => $row['TC_ID'],
            'TC_NAME' => $row['TC_NAME'],
            'TC_DESC' => isset($row['TC_DESC']) ? $row['TC_DESC'] : '',
            'TC_TYPE' => $type_name
        );

        $this->view('detail_tool', $data, FALSE);
    }

    public function submit_tool_type()
    {
        $this->output->set_content_type('application/json');

        if (!isset($this->tool_type) || !method_exists($this->tool_type, 'add_data')) {
            echo json_encode(['success' => false, 'message' => 'Fitur Tool Type tidak tersedia.']);
            return;
        }

        $name = $this->input->post('TT_NAME', TRUE);
        $desc = $this->input->post('TT_DESC', TRUE);

        if (trim($name) === '') {
            echo json_encode(['success' => false, 'message' => 'Nama Tool Type wajib diisi.']);
            return;
        }

        $ok = $this->tool_type->add_data($name, $desc);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => $this->tool_type->messages]);
        } else {
            echo json_encode(['success' => false, 'message' => $this->tool_type->messages]);
        }
    }
}
