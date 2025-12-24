<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_uom $uom
 */
class Uom extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->config->set_item('Blade_enable', FALSE);

        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));

        $this->load->model('M_uom', 'uom');
    }

    public function index()
    {
        $data = array();
        // Menggunakan method get_active yang sudah diperbarui
        $data['list_data'] = $this->uom->get_active();
        $this->view('index_uom', $data, FALSE);
    }

    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('uom_id', TRUE);

        // Rules Validasi
        $this->form_validation->set_rules('uom_name', 'UoM Name', 'required|trim');
        $this->form_validation->set_rules('uom_desc', 'UoM Description', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors()));
            return;
        }

        // Ambil Input
        $uom_name = $this->input->post('uom_name', TRUE);
        $uom_desc = $this->input->post('uom_desc', TRUE);

        $data = [
            'UOM_NAME' => $uom_name,
            'UOM_DESC' => $uom_desc
        ];

        // --- ADD ---
        if ($action === 'ADD') {
            // Cek Duplicate
            if ($this->uom->is_duplicate($uom_name)) {
                echo json_encode(array('success' => false, 'message' => 'UoM dengan nama tersebut sudah ada.'));
                return;
            }

            // Insert via Model
            $new_id = $this->uom->insert($data);

            if ($new_id > 0) {
                echo json_encode(array('success' => true, 'message' => 'UoM berhasil ditambahkan.', 'new_id' => $new_id));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Gagal menambahkan UoM.'));
            }
            return;
        }

        // --- EDIT ---
        if ($action === 'EDIT' && $id > 0) {
            // Cek Duplicate (exclude ID sendiri)
            if ($this->uom->is_duplicate($uom_name, $id)) {
                echo json_encode(array('success' => false, 'message' => 'Nama UoM sudah digunakan oleh data lain.'));
                return;
            }

            // Update via Model
            $ok = $this->uom->update($id, $data);

            if ($ok) {
                echo json_encode(array('success' => true, 'message' => 'UoM berhasil diperbarui.'));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Gagal memperbarui UoM.'));
            }
            return;
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('uom_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'UOM ID tidak ditemukan.'));
            return;
        }

        $deleted_by = $this->session->userdata('username') ?: 'SYSTEM';

        $ok = $this->uom->soft_delete($id, $deleted_by);

        if ($ok) {
            echo json_encode(array('success' => true, 'message' => 'UoM berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Gagal menghapus UoM.'));
        }
    }

    // Dipakai untuk Get Data saat tombol Edit diklik (Javascript)
    public function get_uom_detail()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('uom_id', TRUE);
        $row = $this->uom->get_by_id($id);

        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }
}
