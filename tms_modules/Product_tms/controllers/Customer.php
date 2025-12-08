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
        $data['list_data'] = $this->customer->get_data_master_customer();
        $this->view('index_customer', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT Customer (AJAX)
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('customer_id', TRUE);

        // rules
        $this->form_validation->set_rules('customer_name', 'Customer Name', 'required|trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(['success' => false, 'message' => validation_errors() ?: 'Data tidak valid.']);
            return;
        }

        $customer_name = $this->input->post('customer_name', TRUE);

        if ($action === 'ADD') {
            $exists = $this->customer->get_data_master_customer_by_name($customer_name);
            if ($exists) {
                echo json_encode(['success' => false, 'message' => 'Customer dengan nama tersebut sudah ada.']);
                return;
            }

            $ok = $this->customer->add_data();
            if (!$ok) {
                echo json_encode(['success' => false, 'message' => $this->customer->messages ?: 'Gagal menambahkan customer.']);
                return;
            }

            $newRow = $this->customer->get_data_master_customer_by_name($customer_name);
            $new_id = $newRow ? (int)$newRow['CUSTOMER_ID'] : null;

            echo json_encode(['success' => true, 'message' => $this->customer->messages ?: 'Customer berhasil ditambahkan.', 'new_id' => $new_id]);
            return;
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->customer->get_data_master_customer_by_id($id);
            if (!$current) {
                echo json_encode(['success' => false, 'message' => 'Data Customer tidak ditemukan.']);
                return;
            }

            $dup = $this->customer->get_data_master_customer_by_name($customer_name);
            if ($dup && (int)$dup['CUSTOMER_ID'] !== $id) {
                echo json_encode(['success' => false, 'message' => 'Nama Customer sudah digunakan oleh data lain.']);
                return;
            }

            // === Fix: set $_POST so model.edit_data() can read (CI Input has no set_post)
            $_POST['customer_id']   = $id;
            $_POST['customer_name'] = $customer_name;

            $ok = $this->customer->edit_data();
            if (!$ok) {
                echo json_encode(['success' => false, 'message' => $this->customer->messages ?: 'Gagal memperbarui Customer.']);
                return;
            }

            echo json_encode(['success' => true, 'message' => $this->customer->messages ?: 'Customer berhasil diperbarui.']);
            return;
        }

        echo json_encode(['success' => false, 'message' => 'Parameter action/ID tidak valid.']);
    }

    /**
     * delete_data: soft delete (AJAX)
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('customer_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'CUSTOMER ID tidak ditemukan.'));
            return;
        }

        $ok = $this->customer->delete_data($id);
        echo json_encode(array('success' => (bool)$ok, 'message' => $this->customer->messages ?: ($ok ? 'Customer berhasil dihapus.' : 'Gagal menghapus Customer.')));
    }

    /**
     * get_customer_detail: ambil data customer by id (AJAX)
     */
    public function get_customer_detail()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('customer_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'CUSTOMER ID tidak ditemukan.'));
            return;
        }

        $row = $this->customer->get_data_master_customer_by_id($id);
        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }
}
