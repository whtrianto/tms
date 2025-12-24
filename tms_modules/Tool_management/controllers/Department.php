<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_department $department
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property CI_Output $output
 * @property CI_Input $input
 */
class Department extends MY_Controller
{
    private $uid;

    public function __construct()
    {
        parent::__construct();
        $this->config->set_item('Blade_enable', FALSE);
        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));

        $this->load->model('M_department', 'department');
        $this->uid = $this->session->userdata('username') ?: 'SYSTEM';
    }

    /**
     * index: tampilkan list
     */
    public function index()
    {
        $data = array();
        $data['list_data'] = $this->department->get_data_master_departments();

        $this->view('index_department', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT (AJAX)
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('department_id', TRUE);

        // rules
        $this->form_validation->set_rules('department_name', 'Department Name', 'required|trim');
        $this->form_validation->set_rules('department_desc', 'Description', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $name = $this->input->post('department_name', TRUE);

        if ($action === 'ADD') {
            if ($this->department->is_duplicate($name)) {
                echo json_encode(array('success' => false, 'message' => 'Nama department tersebut sudah ada.'));
                return;
            }

            $ok = $this->department->add_data($this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->department->messages ?: 'Gagal menambahkan data.'));
                return;
            }
            echo json_encode(array('success' => true, 'message' => $this->department->messages ?: 'Data berhasil ditambahkan.'));
            return;
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->department->get_data_master_department_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
                return;
            }
            if ($this->department->is_duplicate($name, $id)) {
                echo json_encode(array('success' => false, 'message' => 'Nama department sudah digunakan oleh data lain.'));
                return;
            }

            $dataUpdate = [
                'DEPART_NAME' => $name,
                'DEPART_DESC' => trim((string)$this->input->post('department_desc')) ?: NULL
            ];

            $ok = $this->department->update_by_id($id, $dataUpdate, $this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->department->messages ?: 'Gagal memperbarui data.'));
                return;
            }
            echo json_encode(array('success' => true, 'message' => $this->department->messages ?: 'Data berhasil diperbarui.'));
            return;
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    /**
     * delete_data: soft delete (AJAX)
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('department_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Department ID tidak ditemukan.'));
            return;
        }

        $ok = $this->department->delete_data($id, $this->uid);
        echo json_encode(array('success' => (bool)$ok, 'message' => $this->department->messages ?: ($ok ? 'Data berhasil dihapus.' : 'Gagal menghapus data.')));
    }
}
