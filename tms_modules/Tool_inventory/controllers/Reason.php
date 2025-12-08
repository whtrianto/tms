<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_reason $reason
 */
class Reason extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('M_reason', 'reason');

        $this->load->library(array('form_validation', 'session'));
        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $search = $this->input->get('search', true);
        $data = array();
        $data['list_data'] = $this->reason->get_all($search);
        $this->view('index_reason', $data, FALSE);
    }

    public function submit_data()
    {
        $this->output->set_content_type('application/json');
        $action = strtoupper($this->input->post('action', TRUE));
        $id = (int)$this->input->post('REASON_ID', TRUE);

        // validation
        $this->form_validation->set_rules('REASON_NAME', 'Reason Name', 'required|trim');
        $this->form_validation->set_rules('REASON_CODE', 'Reason Code', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $name = $this->input->post('REASON_NAME', TRUE);
        $code = $this->input->post('REASON_CODE', TRUE);

        $data = array('REASON_NAME' => $name, 'REASON_CODE' => $code);

        if ($action === 'ADD') {
            // cek duplikat hanya pada baris aktif (IS_DELETED = 0)
            if ($this->reason->exists_by_name_or_code($name, $code, true)) {
                echo json_encode(['success' => false, 'message' => 'Reason name/code sudah digunakan.']);
                return;
            }

            $ok = $this->reason->add_data(['REASON_NAME' => $name, 'REASON_CODE' => $code]);
            if ($ok) {
                $row = $this->reason->get_by_name_or_code($name);
                $new_id = $row ? (int)$row['REASON_ID'] : null;
                echo json_encode(array('success' => true, 'message' => $this->reason->messages ?: 'Reason berhasil ditambahkan.', 'new_id' => $new_id));
                return;
            } else {
                echo json_encode(array('success' => false, 'message' => $this->reason->messages ?: 'Gagal menambahkan reason.'));
                return;
            }
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->reason->get_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
                return;
            }

            // check duplicate excluding current id
            if ($this->reason->is_duplicate($name, $id) || ($code && $this->reason->is_duplicate($code, $id))) {
                echo json_encode(array('success' => false, 'message' => 'Reason name/code sudah digunakan oleh data lain.'));
                return;
            }

            $ok = $this->reason->edit_data($id, $data);
            if ($ok) {
                echo json_encode(array('success' => true, 'message' => $this->reason->messages ?: 'Reason berhasil diperbarui.'));
                return;
            } else {
                echo json_encode(array('success' => false, 'message' => $this->reason->messages ?: 'Gagal memperbarui reason.'));
                return;
            }
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    public function delete_data()
    {
        $this->output->set_content_type('application/json');
        $id = (int)$this->input->post('REASON_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'REASON_ID tidak ditemukan.'));
            return;
        }

        $ok = $this->reason->delete_data($id);
        if ($ok) {
            echo json_encode(array('success' => true, 'message' => $this->reason->messages ?: 'Reason berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->reason->messages ?: 'Gagal menghapus reason.'));
        }
    }

    public function get_reason_detail()
    {
        $this->output->set_content_type('application/json');
        $id = (int)$this->input->post('REASON_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'REASON_ID tidak ditemukan.'));
            return;
        }
        $row = $this->reason->get_by_id($id);
        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }
}
