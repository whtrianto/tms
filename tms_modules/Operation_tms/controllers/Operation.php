<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_operation $operation
 */
class Operation extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->config->set_item('Blade_enable', FALSE);

        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));

        // Load model M_operation sebagai 'operation'
        $this->load->model('M_operation', 'operation');
    }

    /**
     * index: tampilkan list Operation (view)
     */
    public function index()
    {
        $data = array();
        $data['list_data'] = $this->operation->get_data_master_operation();
        // Load view 'index_operation.php'
        $this->view('index_operation', $data, FALSE); 
    }

    /**
     * submit_data: ADD / EDIT Operation (AJAX)
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('operation_id', TRUE);

        // rules
        $this->form_validation->set_rules('operation_name', 'Operation Name', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $operation_name = $this->input->post('operation_name', TRUE);

        if ($action === 'ADD') {
            // cek duplicate (case-insensitive) via model
            $exists = $this->operation->get_data_master_operation_by_name($operation_name);
            if ($exists) {
                echo json_encode(array('success' => false, 'message' => 'Operation dengan nama tersebut sudah ada.'));
                return;
            }

            $ok = $this->operation->add_data();
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->operation->messages ?: 'Gagal menambahkan Operation.'));
                return;
            }

            // ambil kembali row baru untuk kirim ID
            $newRow = $this->operation->get_data_master_operation_by_name($operation_name);
            $new_id = $newRow ? (int)$newRow['OPERATION_ID'] : null;

            echo json_encode(array('success' => true, 'message' => $this->operation->messages ?: 'Operation berhasil ditambahkan.', 'new_id' => $new_id));
            return;
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->operation->get_data_master_operation_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data Operation tidak ditemukan.'));
                return;
            }

            // cek duplicate pada baris lain
            if ($this->operation->is_duplicate($operation_name, $id)) {
                echo json_encode(array('success' => false, 'message' => 'Nama Operation sudah digunakan oleh data lain.'));
                return;
            }

            // data yang akan di-update
            $dataUpdate = [
                'OPERATION_NAME' => $operation_name,
            ];

            $ok = $this->operation->update_by_id($id, $dataUpdate);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->operation->messages ?: 'Gagal memperbarui Operation.'));
                return;
            }

            echo json_encode(array('success' => true, 'message' => $this->operation->messages ?: 'Operation berhasil diperbarui.'));
            return;
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    /**
     * delete_data: soft delete Operation (AJAX)
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('operation_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Operation ID tidak ditemukan.'));
            return;
        }

        $ok = $this->operation->delete_data($id);
        echo json_encode(array('success' => (bool)$ok, 'message' => $this->operation->messages ?: ($ok ? 'Operation berhasil dihapus.' : 'Gagal menghapus Operation.')));
    }
}