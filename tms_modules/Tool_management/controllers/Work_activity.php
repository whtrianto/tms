<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Work_activity extends MY_Controller
{
    private $uid;

    public function __construct()
    {
        parent::__construct();
        $this->config->set_item('Blade_enable', FALSE);
        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));

        $this->load->model('M_work_activity', 'work_activity');
        $this ->uid = $this->session->userdata('username') ?: 'SYSTEM';
    }

    public function index()
    {
        $data = array();
        $data['list_data'] = $this->work_activity->get_data_master_work_activity();
        
        $this->view('index_work_activity', $data, FALSE);
    }


    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('work_activity_id', TRUE);

        $this->form_validation->set_rules('work_activity_name', 'Work Activity Name', 'required|trim');
        $this->form_validation->set_rules('work_activity_desc', 'Description', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $name = $this->input->post('work_activity_name', TRUE);

        if ($action === 'ADD') {
            if ($this->work_activity->is_duplicate($name)) {
                echo json_encode(array('success' => false, 'message' => 'Nama work activity tersebut sudah ada.'));
                return;
            }

            $ok = $this->work_activity->add_data($this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->work_activity->messages ?: 'Gagal menambahkan data.'));
                return;
            }
            echo json_encode(array('success' => true, 'message' => $this->work_activity->messages ?: 'Data berhasil ditambahkan.'));
            return;
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->work_activity->get_data_master_work_activity_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
                return;
            }
            if ($this->work_activity->is_duplicate($name, $id)) {
                echo json_encode(array('success' => false, 'message' => 'Nama work activity sudah digunakan oleh data lain.'));
                return;
            }

            $dataUpdate = [
                'WORK_ACTIVITY_NAME' => $name,
                'WORK_ACTIVITY_DESC' => trim((string)$this->input->post('work_activity_desc')) ?: NULL
            ];

            $ok = $this->work_activity->update_by_id($id, $dataUpdate, $this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->work_activity->messages ?: 'Gagal memperbarui data.'));
                return;
            }
            echo json_encode(array('success' => true, 'message' => $this->work_activity->messages ?: 'Data berhasil diperbarui.'));
            return;
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }


    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('work_activity_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Work Activity ID tidak ditemukan.'));
            return;
        }

        $ok = $this->work_activity->delete_data($id, $this->uid);
        echo json_encode(array('success' => (bool)$ok, 'message' => $this->work_activity->messages ?: ($ok ? 'Data berhasil dihapus.' : 'Gagal menghapus data.')));
    }
}