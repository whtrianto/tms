<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_storage_location $storage_location
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property CI_Output $output
 * @property CI_Input $input
 */
class Storage_location extends MY_Controller
{
    private $uid;

    public function __construct()
    {
        parent::__construct();
        $this->config->set_item('Blade_enable', FALSE);
        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));

        $this->load->model('M_storage_location', 'storage_location');
        $this->uid = $this->session->userdata('username') ?: 'SYSTEM';
    }

    /**
     * index: tampilkan list
     */
    public function index()
    {
        $data = array();
        $data['list_data'] = $this->storage_location->get_data_master_storage_locations();
        
        $this->view('index_storage_location', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT (AJAX)
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('storage_location_id', TRUE);

        // rules
        $this->form_validation->set_rules('storage_location_name', 'Storage Location Name', 'required|trim');
        $this->form_validation->set_rules('storage_location_desc', 'Description', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $name = $this->input->post('storage_location_name', TRUE);

        if ($action === 'ADD') {
            if ($this->storage_location->is_duplicate($name)) {
                echo json_encode(array('success' => false, 'message' => 'Nama lokasi tersebut sudah ada.'));
                return;
            }

            $ok = $this->storage_location->add_data($this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->storage_location->messages ?: 'Gagal menambahkan data.'));
                return;
            }
            echo json_encode(array('success' => true, 'message' => $this->storage_location->messages ?: 'Data berhasil ditambahkan.'));
            return;
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->storage_location->get_data_master_storage_location_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
                return;
            }
            if ($this->storage_location->is_duplicate($name, $id)) {
                echo json_encode(array('success' => false, 'message' => 'Nama lokasi sudah digunakan oleh data lain.'));
                return;
            }

            $dataUpdate = [
                'STORAGE_LOCATION_NAME' => $name,
                'STORAGE_LOCATION_DESC' => trim((string)$this->input->post('storage_location_desc')) ?: NULL
            ];

            $ok = $this->storage_location->update_by_id($id, $dataUpdate, $this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->storage_location->messages ?: 'Gagal memperbarui data.'));
                return;
            }
            echo json_encode(array('success' => true, 'message' => $this->storage_location->messages ?: 'Data berhasil diperbarui.'));
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

        $id = (int)$this->input->post('storage_location_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Storage Location ID tidak ditemukan.'));
            return;
        }

        $ok = $this->storage_location->delete_data($id, $this->uid);
        echo json_encode(array('success' => (bool)$ok, 'message' => $this->storage_location->messages ?: ($ok ? 'Data berhasil dihapus.' : 'Gagal menghapus data.')));
    }
}