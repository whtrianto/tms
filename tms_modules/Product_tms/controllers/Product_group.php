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

        $this->load->model('M_product_group');
        $this->load->model('M_product');
    }

    public function index()
    {
        // View ini menampilkan LIST GROUP (Master Data), bukan list relasi.
        // Data diambil dari MS_PARTS where PART_IS_GROUP = 1
        $data['list_data'] = $this->M_product->get_groups();

        // Untuk dropdown jika nanti diperlukan
        $data['products'] = $this->M_product_group->get_products();
        $data['product_groups_only'] = $this->M_product->get_groups();

        $this->view('index_product_group', $data, FALSE);
    }

    /**
     * submit_data untuk RELASI (ADD/EDIT Members)
     * Note: Form ini biasanya dipanggil dari menu yang mengatur anggota group,
     * bukan dari halaman index utama yang hanya create Master Group.
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action    = strtoupper($this->input->post('action', TRUE));
        // Sesuaikan parameter ID Relasi
        $id        = (int)$this->input->post('PARTM_ID', TRUE);
        $parent_id = (int)$this->input->post('PARTM_PARENT_ID', TRUE);
        $child_id  = (int)$this->input->post('PARTM_CHILD_ID', TRUE);

        if (!in_array($action, array('ADD', 'EDIT'), true)) {
            echo json_encode(['success' => false, 'message' => 'Action tidak dikenali.']);
            return;
        }

        if ($action === 'ADD') {
            if ($parent_id <= 0 || $child_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Parent/Child tidak valid.']);
                return;
            }
            $new_id = $this->M_product_group->insert_relation($parent_id, $child_id);
            if ($new_id === false) {
                echo json_encode(['success' => false, 'message' => $this->M_product_group->messages ?: 'Gagal menambahkan relasi.']);
            } else {
                echo json_encode(['success' => true, 'message' => $this->M_product_group->messages ?: 'Relasi berhasil ditambahkan.', 'new_id' => (int)$new_id]);
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

            $ok = $this->M_product_group->update_relation($id, $parent_id, $child_id);
            if ($ok) {
                echo json_encode(['success' => true, 'message' => $this->M_product_group->messages ?: 'Relasi berhasil diubah.']);
            } else {
                echo json_encode(['success' => false, 'message' => $this->M_product_group->messages ?: 'Gagal mengubah relasi.']);
            }
            return;
        }
    }

    /**
     * delete_data: soft-delete RELASI
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('PARTM_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID relasi tidak ditemukan.']);
            return;
        }

        $ok = $this->M_product_group->soft_delete($id);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => $this->M_product_group->messages ?: 'Relasi berhasil dinonaktifkan.']);
        } else {
            echo json_encode(['success' => false, 'message' => $this->M_product_group->messages ?: 'Gagal menonaktifkan relasi.']);
        }
    }

    /**
     * create_group: Membuat MASTER GROUP BARU (Header) di tabel MS_PARTS
     */
    public function create_group()
    {
        $this->output->set_content_type('application/json');

        $name = $this->input->post('PART_NAME', TRUE);
        $desc = $this->input->post('PART_DESC', TRUE);

        if (empty(trim($name))) {
            echo json_encode(['success' => false, 'message' => 'Product Group name wajib diisi.']);
            return;
        }

        // Cek duplicate di MS_PARTS
        if ($this->M_product->is_duplicate($name, null)) {
            echo json_encode(['success' => false, 'message' => 'Product Group sudah ada (aktif).']);
            return;
        }

        // Siapkan data untuk MS_PARTS
        $data = [
            'PART_NAME'     => $name,
            'PART_IS_GROUP' => 1,       // Tandai sebagai Group
            'PART_DESC'     => $desc,
            'PART_CUS_CODE' => null,
            'PART_UNITS'    => null,
            'PART_CUS_ID'   => null,
            'PART_DRW_NO'   => null,
            'PART_TYPE'     => null,
            'PART_UNIT_PRICE' => 0,
            'PART_WEIGHT'   => 0
        ];

        $new_id = $this->M_product->insert($data);
        if ($new_id) {
            echo json_encode(['success' => true, 'message' => 'Product Group berhasil dibuat.', 'new_id' => (int)$new_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal membuat Product Group.']);
        }
    }
}
