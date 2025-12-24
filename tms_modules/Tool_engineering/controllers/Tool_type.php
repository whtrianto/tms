<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_tool_type $tool_type
 */
class Tool_type extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('M_tool_type', 'tool_type');
        $this->load->library(array('form_validation', 'session'));
        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $data['list_data'] = $this->tool_type->get_all();

        $this->view('index_tool_type', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT Tool Type (AJAX)
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');
        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('TT_ID', TRUE);

        // validation rules
        $this->form_validation->set_rules('TT_NAME', 'Tool Type Name', 'required|trim');
        $this->form_validation->set_rules('TT_DESC', 'Description', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $name = $this->input->post('TT_NAME', TRUE);
        $desc = $this->input->post('TT_DESC', TRUE);

        if ($action === 'ADD') {
            if ($this->tool_type->exists_by_name($name)) {
                echo json_encode(array('success' => false, 'message' => 'Nama tool type sudah digunakan.'));
                return;
            }

            $ok = $this->tool_type->add_data($name, $desc);
            if ($ok) {
                $row = $this->tool_type->get_by_name($name);
                $new_id = $row ? (int)$row['TT_ID'] : null;
                echo json_encode(array('success' => true, 'message' => $this->tool_type->messages ?: 'Tool Type berhasil ditambahkan.', 'new_id' => $new_id));
                return;
            } else {
                echo json_encode(array('success' => false, 'message' => $this->tool_type->messages ?: 'Gagal menambahkan tool type.'));
                return;
            }
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->tool_type->get_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
                return;
            }

            $dup = $this->tool_type->get_by_name($name);
            if ($dup && (int)$dup['TT_ID'] !== $id) {
                echo json_encode(array('success' => false, 'message' => 'Nama tool type sudah digunakan oleh data lain.'));
                return;
            }

            $ok = $this->tool_type->edit_data($id, $name, $desc);
            if ($ok) {
                echo json_encode(array('success' => true, 'message' => $this->tool_type->messages ?: 'Tool Type berhasil diperbarui.'));
                return;
            } else {
                echo json_encode(array('success' => false, 'message' => $this->tool_type->messages ?: 'Gagal memperbarui tool type.'));
                return;
            }
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    /**
     * delete_data: soft delete
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TT_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'TT_ID tidak ditemukan.'));
            return;
        }

        $ok = $this->tool_type->delete_data($id);
        if ($ok) {
            echo json_encode(array('success' => true, 'message' => $this->tool_type->messages ?: 'Tool Type berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->tool_type->messages ?: 'Gagal menghapus tool type.'));
        }
    }

    /**
     * get_tool_type_detail: ambil data by id (AJAX)
     */
    public function get_tool_type_detail()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TT_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'TT_ID tidak ditemukan.'));
            return;
        }

        $row = $this->tool_type->get_by_id($id);
        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }
}
