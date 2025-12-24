<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_customer $customer
 */
class Customer extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->config->set_item('Blade_enable', FALSE);
        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));

        $this->load->model('M_customer', 'customer');
    }

    public function index()
    {
        $data = array();
        // Menggunakan method baru get_active
        $data['list_data'] = $this->customer->get_active();
        $this->view('index_customer', $data, FALSE);
    }

    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('customer_id', TRUE);

        // Rules
        $this->form_validation->set_rules('customer_name', 'Customer Name', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(['success' => false, 'message' => validation_errors() ?: 'Data tidak valid.']);
            return;
        }

        $name = $this->input->post('customer_name', TRUE);
        $abbr = $this->input->post('customer_abbr', TRUE);

        // Mapping ke kolom database
        $data = [
            'CUS_NAME' => $name,
            'CUS_ABBR' => $abbr
            // 'CUS_ABBR' => ... (bisa ditambahkan jika ada inputnya di view)
        ];

        // --- ADD ---
        if ($action === 'ADD') {
            if ($this->customer->is_duplicate($name)) {
                echo json_encode(['success' => false, 'message' => 'Customer dengan nama tersebut sudah ada.']);
                return;
            }

            $new_id = $this->customer->insert($data);

            if ($new_id > 0) {
                echo json_encode(['success' => true, 'message' => 'Customer berhasil ditambahkan.', 'new_id' => $new_id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menambahkan customer.']);
            }
            return;
        }

        // --- EDIT ---
        if ($action === 'EDIT' && $id > 0) {
            // Cek Duplicate exclude self
            if ($this->customer->is_duplicate($name, $id)) {
                echo json_encode(['success' => false, 'message' => 'Nama Customer sudah digunakan oleh data lain.']);
                return;
            }

            $ok = $this->customer->update($id, $data);

            if ($ok) {
                echo json_encode(['success' => true, 'message' => 'Customer berhasil diperbarui.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui Customer.']);
            }
            return;
        }

        echo json_encode(['success' => false, 'message' => 'Parameter action/ID tidak valid.']);
    }

    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('customer_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'CUSTOMER ID tidak ditemukan.'));
            return;
        }

        $deleted_by = $this->session->userdata('username') ?: 'SYSTEM';
        $ok = $this->customer->soft_delete($id, $deleted_by);

        if ($ok) {
            echo json_encode(array('success' => true, 'message' => 'Customer berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Gagal menghapus Customer.'));
        }
    }

    public function get_customer_detail()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('customer_id', TRUE);
        $row = $this->customer->get_by_id($id);

        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }
}
