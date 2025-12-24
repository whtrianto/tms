<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_operation $M_operation
 */
class Operation extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->config->set_item('Blade_enable', FALSE);

        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));

        // Load model M_operation
        $this->load->model('M_operation');
    }

    /**
     * index: tampilkan list Operation (view)
     */
    public function index()
    {
        $data = array();
        $data['list_data'] = $this->M_operation->get_data_master_operation();
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

            $new_id = $this->M_operation->add_data();
            if (!$new_id) {
                echo json_encode([
                    'success' => false,
                    'message' => $this->M_operation->messages ?: 'Gagal menambahkan Operation.'
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'message' => $this->M_operation->messages,
                'new_id'  => (int)$new_id
            ]);
            return;
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->M_operation->get_data_master_operation_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data Operation tidak ditemukan.'));
                return;
            }

            // cek duplicate pada baris lain
            if ($this->M_operation->is_duplicate($operation_name, $id)) {
                echo json_encode(array('success' => false, 'message' => 'Nama Operation sudah digunakan oleh data lain.'));
                return;
            }

            // data yang akan di-update
            $dataUpdate = [
                'OP_NAME' => $operation_name,
            ];

            $ok = $this->M_operation->update_by_id($id, $dataUpdate);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->M_operation->messages ?: 'Gagal memperbarui Operation.'));
                return;
            }

            echo json_encode(array('success' => true, 'message' => $this->M_operation->messages ?: 'Operation berhasil diperbarui.'));
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

        $ok = $this->M_operation->delete_data($id);
        echo json_encode(array('success' => (bool)$ok, 'message' => $this->M_operation->messages ?: ($ok ? 'Operation berhasil dihapus.' : 'Gagal menghapus Operation.')));
    }
}
