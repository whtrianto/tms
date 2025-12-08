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

    /**
     * index: tampilkan list UoM (view)
     */
    public function index()
    {
        $data = array();
        $data['list_data'] = $this->uom->get_data_master_uom();
        $this->view('index_uom', $data, FALSE);
    }

    /**
     * Compatibility wrapper: jaga URL lama agar view/JS tidak perlu diubah
     * POST /operation/uom/uom_submit_data --> delegasi ke submit_data()
     */
    // public function uom_submit_data()
    // {
    //     return $this->submit_data();
    // }

    /**
     * submit_data: ADD / EDIT UoM (AJAX)
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('uom_id', TRUE);

        // rules
        $this->form_validation->set_rules('uom_name', 'UoM Name', 'required|trim');
        $this->form_validation->set_rules('uom_desc', 'UoM Description', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $uom_name = $this->input->post('uom_name', TRUE);
        $uom_desc = $this->input->post('uom_desc', TRUE);

        if ($action === 'ADD') {
            // cek duplicate (case-insensitive) via model
            $exists = $this->uom->get_data_master_uom_by_name($uom_name);
            if ($exists) {
                echo json_encode(array('success' => false, 'message' => 'UoM dengan nama tersebut sudah ada.'));
                return;
            }

            $ok = $this->uom->add_data();
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->uom->messages ?: 'Gagal menambahkan UoM.'));
                return;
            }

            // ambil kembali row baru untuk kirim ID
            $newRow = $this->uom->get_data_master_uom_by_name($uom_name);
            $new_id = $newRow ? (int)$newRow['UOM_ID'] : null;

            echo json_encode(array('success' => true, 'message' => $this->uom->messages ?: 'UoM berhasil ditambahkan.', 'new_id' => $new_id));
            return;
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->uom->get_data_master_uom_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data UoM tidak ditemukan.'));
                return;
            }

            // cek duplicate pada baris lain menggunakan method yang tepat
            if ($this->uom->is_duplicate($uom_name, $id)) {
                echo json_encode(array('success' => false, 'message' => 'Nama UoM sudah digunakan oleh data lain.'));
                return;
            }

            // data yang akan di-update
            $dataUpdate = [
                'UOM_NAME' => $uom_name,
                'UOM_DESC' => $uom_desc
            ];

            $ok = $this->uom->update_by_id($id, $dataUpdate);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->uom->messages ?: 'Gagal memperbarui UoM.'));
                return;
            }

            echo json_encode(array('success' => true, 'message' => $this->uom->messages ?: 'UoM berhasil diperbarui.'));
            return;
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    /**
     * delete_data: soft delete UoM (AJAX)
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('uom_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'UOM ID tidak ditemukan.'));
            return;
        }

        $ok = $this->uom->delete_data($id);
        echo json_encode(array('success' => (bool)$ok, 'message' => $this->uom->messages ?: ($ok ? 'UoM berhasil dihapus.' : 'Gagal menghapus UoM.')));
    }

    /**
     * get_uom_detail: ambil data UoM by id (AJAX) — dipakai untuk edit prefilling bila perlu
     */
    public function get_uom_detail()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('uom_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'UOM ID tidak ditemukan.'));
            return;
        }

        $row = $this->uom->get_data_master_uom_by_id($id);
        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }
}
