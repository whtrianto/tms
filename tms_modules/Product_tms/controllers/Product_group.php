<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_group extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->config->set_item('Blade_enable', FALSE);

        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));

        $this->load->model('M_product_group', 'pg');
        $this->load->model('M_product', 'product');
    }

    public function index()
    {
        // ambil semua product yang ditandai sebagai group
        $data['list_data'] = $this->product->get_groups(); // hanya PRODUCT_IS_GROUP = 1 dan IS_DELETED = 0
        $data['products'] = $this->pg->get_products(); // untuk dropdown relasi
        $data['product_groups_only'] = $this->product->get_groups();

        $this->view('index_product_group', $data, FALSE);
    }

    /**
     * submit_data untuk relasi (ADD/EDIT)
     * Keep JSON response, tanpa permission checks
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action    = strtoupper($this->input->post('action', TRUE));
        $id        = (int)$this->input->post('PRODUCT_GROUP_ID', TRUE);
        $parent_id = (int)$this->input->post('PRODUCT_GROUP_PARENT_ID', TRUE);
        $child_id  = (int)$this->input->post('PRODUCT_GROUP_CHILD_ID', TRUE);

        if (!in_array($action, array('ADD', 'EDIT'), true)) {
            echo json_encode(['success' => false, 'message' => 'Action tidak dikenali.']);
            return;
        }

        if ($action === 'ADD') {
            // validation
            if ($parent_id <= 0 || $child_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Parent/Child tidak valid.']);
                return;
            }
            $new_id = $this->pg->insert_relation($parent_id, $child_id);
            if ($new_id === false) {
                echo json_encode(['success' => false, 'message' => $this->pg->messages ?: 'Gagal menambahkan relasi.']);
            } else {
                echo json_encode(['success' => true, 'message' => $this->pg->messages ?: 'Relasi berhasil ditambahkan.', 'new_id' => (int)$new_id]);
            }
            return;
        }

        if ($action === 'EDIT') {
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID relasi tidak ditemukan.']);
                return;
            }
            if ($parent_id <= 0 || $child_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Parent/Child tidak valid.']);
                return;
            }

            $ok = $this->pg->update_relation($id, $parent_id, $child_id);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => $this->pg->messages ?: 'Relasi berhasil diubah.']);
            } else {
                echo json_encode(['success' => false, 'message' => $this->pg->messages ?: 'Gagal mengubah relasi.']);
            }
            return;
        }
    }

    /**
     * delete_data: soft-delete relasi (set end_date)
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('PRODUCT_GROUP_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID relasi tidak ditemukan.']);
            return;
        }

        $ok = $this->pg->soft_delete($id);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => $this->pg->messages ?: 'Relasi berhasil dinonaktifkan.']);
        } else {
            echo json_encode(['success' => false, 'message' => $this->pg->messages ?: 'Gagal menonaktifkan relasi.']);
        }
    }

    /**
     * create_group: create new product entry where PRODUCT_IS_GROUP = 1
     * Mirip pola UoM: cek duplicate hanya pada product aktif (IS_DELETED = 0)
     */
    public function create_group()
    {
        $this->output->set_content_type('application/json');

        $name = $this->input->post('PRODUCT_NAME', TRUE);
        $desc = $this->input->post('PRODUCT_DESC', TRUE);

        if (empty(trim($name))) {
            echo json_encode(['success' => false, 'message' => 'Product Group name wajib diisi.']);
            return;
        }

        // product->is_duplicate harus mengecek IS_DELETED = 0 (implementasi di model Product)
        if ($this->product->is_duplicate($name, null)) {
            echo json_encode(['success' => false, 'message' => 'Product Group sudah ada (aktif).']);
            return;
        }

        $data = [
            'PRODUCT_NAME' => $name,
            'PRODUCT_IS_GROUP' => 1,
            'PRODUCT_DESC' => $desc,
            'PRODUCT_CUSTOMER_CODE' => null,
            'UOM_ID' => null,
            'CUSTOMER_ID' => null,
            'PRODUCT_DRW_NO' => null,
            'PRODUCT_TYPE' => null,
            'IS_DELETED' => 0
        ];

        $new_id = $this->product->insert($data);
        if ($new_id) {
            echo json_encode(['success' => true, 'message' => 'Product Group berhasil dibuat.', 'new_id' => (int)$new_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal membuat Product Group.']);
        }
    }
}
