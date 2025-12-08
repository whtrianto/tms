<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_supplier $supplier
 */
class Supplier extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->config->set_item('Blade_enable', FALSE);
        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));

        $this->load->model('M_supplier', 'supplier');
    }

    public function index()
    {
        $data = array();
        $data['list_data'] = $this->supplier->get_all();
        $this->view('index_supplier', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT Supplier (AJAX)
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('SUPPLIER_ID', TRUE);

        // validation rules
        $this->form_validation->set_rules('SUPPLIER_NAME', 'Supplier Name', 'required|trim');
        $this->form_validation->set_rules('SUPPLIER_ABBR', 'Abbreviation', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $name = $this->input->post('SUPPLIER_NAME', TRUE);
        $abbr = $this->input->post('SUPPLIER_ABBR', TRUE);

        if ($action === 'ADD') {
            // check duplicate active supplier name (IS_DELETED = 0)
            if ($this->supplier->exists_by_name($name)) {
                echo json_encode(array('success' => false, 'message' => 'Nama supplier sudah digunakan.'));
                return;
            }

            $ok = $this->supplier->add_data($name, $abbr);
            if ($ok) {
                // try fetch inserted id
                $row = $this->supplier->get_by_name($name);
                $new_id = $row ? (int)$row['SUPPLIER_ID'] : null;
                echo json_encode(array('success' => true, 'message' => $this->supplier->messages ?: 'Supplier berhasil ditambahkan.', 'new_id' => $new_id));
                return;
            } else {
                echo json_encode(array('success' => false, 'message' => $this->supplier->messages ?: 'Gagal menambahkan supplier.'));
                return;
            }
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->supplier->get_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data supplier tidak ditemukan.'));
                return;
            }

            // duplicate name check excluding current id
            $dup = $this->supplier->get_by_name($name);
            if ($dup && (int)$dup['SUPPLIER_ID'] !== $id) {
                echo json_encode(array('success' => false, 'message' => 'Nama supplier sudah digunakan oleh data lain.'));
                return;
            }

            $ok = $this->supplier->edit_data($id, $name, $abbr);
            if ($ok) {
                echo json_encode(array('success' => true, 'message' => $this->supplier->messages ?: 'Supplier berhasil diperbarui.'));
                return;
            } else {
                echo json_encode(array('success' => false, 'message' => $this->supplier->messages ?: 'Gagal memperbarui supplier.'));
                return;
            }
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    /**
     * delete_data: soft delete
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('SUPPLIER_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'SUPPLIER_ID tidak ditemukan.'));
            return;
        }

        $ok = $this->supplier->delete_data($id);
        if ($ok) {
            echo json_encode(array('success' => true, 'message' => $this->supplier->messages ?: 'Supplier berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->supplier->messages ?: 'Gagal menghapus supplier.'));
        }
    }

    /**
     * get_supplier_detail: ambil data by id (AJAX)
     */
    public function get_supplier_detail()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('SUPPLIER_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'SUPPLIER_ID tidak ditemukan.'));
            return;
        }

        $row = $this->supplier->get_by_id($id);
        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }
}
