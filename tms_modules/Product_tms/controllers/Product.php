<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_product        $product
 * @property M_uom            $uom
 * @property M_customer       $customer
 * @property M_product_group  $pg
 */
class Product extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('M_product',  'product');
        $this->load->model('M_uom',      'uom');
        $this->load->model('M_customer', 'customer');
        $this->load->model('M_product_group', 'pg');

        $this->load->library('form_validation');
        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $search = $this->input->get('search', true);

        $data = array();
        $data['list_data'] = $this->product->get_all($search);
        // pastikan model uom & customer punya method get_active dan tms_db
        $data['uoms']      = method_exists($this->uom, 'get_active') ? $this->uom->get_active() : array();
        $data['customers'] = method_exists($this->customer, 'get_active') ? $this->customer->get_active() : array();
        $data['product_groups'] = $this->product->get_groups(); // untuk select group di form

        // render view
        $this->view('index_product', $data, FALSE);
    }

    // AJAX submit untuk add/edit product
    // ganti fungsi submit_data() lama dengan fungsi berikut
    public function submit_data()
    {
        $this->output->set_content_type('application/json');
        $action = strtoupper($this->input->post('action', true));
        $id     = $this->input->post('PART_ID', true);

        // Ambil & normalisasi PART_IS_GROUP dulu (penting utk validasi)
        $is_group_raw = $this->input->post('PART_IS_GROUP', true);
        $is_group = ($is_group_raw !== null && (string)$is_group_raw === '1') ? 1 : 0;

        // rules dasar
        $this->form_validation->set_rules('PART_NAME', 'Product Name', 'required|trim');
        $this->form_validation->set_rules('PART_IS_GROUP', 'Is Group', 'trim|in_list[0,1]');
        $this->form_validation->set_rules('PART_UNITS', 'UOM', 'trim');

        // optional fields
        $this->form_validation->set_rules('PART_DESC', 'Description', 'trim');
        $this->form_validation->set_rules('PART_CUS_CODE', 'Customer Code', 'trim');
        $this->form_validation->set_rules('PART_DRW_NO', 'Drawing No', 'trim');
        $this->form_validation->set_rules('PART_TYPE', 'Type', 'trim');
        $this->form_validation->set_rules('PART_UNITS', 'Customer', 'trim');
        $this->form_validation->set_rules('PART_UNIT_PRICE', 'Unit Price', 'numeric');
        $this->form_validation->set_rules('PART_WEIGHT', 'Weight', 'numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors()));
            return;
        }

        // Normalisasi nilai untuk dimasukkan ke DB
        $name = $this->input->post('PART_NAME', true);
        $desc = $this->input->post('PART_DESC', true);
        $drw  = $this->input->post('PART_DRW_NO', true);
        $type = $this->input->post('PART_TYPE', true);

        $customer_code_raw = $this->input->post('PART_CUS_CODE', true);
        $customer_code = ($customer_code_raw === '' || $customer_code_raw === null) ? null : $customer_code_raw;

        // UOM / CUS only for non-group products
        if ($is_group === 1) {
            $uom_id = null;
            $customer_id = null;
            $customer_code = null; // enforce null for groups
        } else {
            $uom_raw = $this->input->post('UOM_ID', true);
            $uom_id = ($uom_raw === '' || $uom_raw === null) ? null : (int)$uom_raw;

            $cust_raw = $this->input->post('CUS_ID', true);
            $customer_id = ($cust_raw === '' || $cust_raw === null) ? null : (int)$cust_raw;
        }

        $data = array(
            'PART_NAME'       => $this->input->post('PART_NAME', true),
            'PART_IS_GROUP'   => $is_group,
            'PART_DESC'       => $this->input->post('PART_DESC', true),
            'PART_CUS_CODE'   => $customer_code,
            'PART_DRW_NO'     => $this->input->post('PART_DRW_NO', true),
            'PART_TYPE'       => $this->input->post('PART_TYPE', true),
            'PART_UNITS'      => $uom_id,
            'PART_CUS_ID'     => $customer_id,
            'PART_UNIT_PRICE' => $this->input->post('PART_UNIT_PRICE', true) ?: 0,
            'PART_WEIGHT'     => $this->input->post('PART_WEIGHT', true) ?: 0,
        );

        // selected_parent (relasi) — bisa kosong/0
        $selected_parent = (int)$this->input->post('PARTM_PARENT_ID', true);

        // --------------- ADD ---------------
        if ($action === 'ADD') {

            if ($this->product->is_duplicate($data['PART_NAME'])) {
                echo json_encode(array('success' => false, 'message' => 'Product Name sudah digunakan.'));
                return;
            }

            // mulai transaksi tunggal (controller mengelola seluruh operasi)
            $this->product->tms_db->trans_begin();

            // insert product tanpa mengelola transaksi di dalam method insert
            $new_id = $this->product->insert($data, false);
            if (!$new_id) {
                $this->product->tms_db->trans_rollback();
                echo json_encode(array('success' => false, 'message' => 'Gagal menyimpan product.'));
                return;
            }

            // gunakan connection yang sama untuk operasi product_group (agar bagian ini ikut transaksi)
            $db = $this->product->tms_db;

            // cek apakah tabel product_group tersedia (defensive)
            $can_manage_pg = true;
            if (method_exists($db, 'table_exists')) {
                $can_manage_pg = $db->table_exists('MS_PART_MEMBERS');
            }

            // bila user memilih parent relasi -> buat record di MS_PART_MEMBERS
            if ($selected_parent > 0) {
                if (!$can_manage_pg) {
                    // skip relasi jika tabel tidak ada — jangan rollback create product
                    log_message('debug', 'MS_PART_MEMBERS not found — skipping relation creation for new product_id=' . $new_id);
                } else {
                    // cek apakah relasi aktif sudah ada?
                    $chk = $db->query(
                        "SELECT COUNT(1) AS CNT FROM MS_PART_MEMBERS
                     WHERE PARTM_PARENT_ID = ? AND PARTM_CHILD_ID = ? AND PARTM_DATE_END IS NULL",
                        array($selected_parent, $new_id)
                    )->row();

                    if ($chk && (int)$chk->CNT > 0) {
                        // sudah ada relasi; rollback dan return error
                        $this->product->tms_db->trans_rollback();
                        echo json_encode(array('success' => false, 'message' => 'Relasi sudah ada.'));
                        return;
                    }

                    // insert explicit PARTM_ID
                    $insSql = "
                        INSERT INTO MS_PART_MEMBERS
                        (PARTM_PARENT_ID, PARTM_CHILD_ID, PARTM_DATE_START, PARTM_DATE_END, IS_DELETED)
                        VALUES (?, ?, GETDATE(), NULL, 0)
                    ";
                    $ok = $db->query($insSql, [
                        (int)$selected_parent,
                        (int)$new_id
                    ]);
                    if (!$ok) {
                        $this->product->tms_db->trans_rollback();
                        echo json_encode(array('success' => false, 'message' => 'Gagal membuat relasi product (insert product_group gagal).'));
                        return;
                    }
                }
            }

            // commit transaksi keseluruhan
            if ($this->product->tms_db->trans_status() === FALSE) {
                $this->product->tms_db->trans_rollback();
                echo json_encode(array('success' => false, 'message' => 'Transaksi gagal.'));
                return;
            } else {
                $this->product->tms_db->trans_commit();
            }

            echo json_encode(array('success' => true, 'message' => 'Product berhasil ditambahkan.', 'new_id' => (int)$new_id));
            return;
        }

        // --------------- EDIT ---------------
        if ($action === 'EDIT' && !empty($id)) {

            // pastikan id numeric
            $id = (int)$id;
            if ($id <= 0) {
                echo json_encode(array('success' => false, 'message' => 'PART_ID tidak valid.'));
                return;
            }

            // passed $id as exclude_id so the current record won't be counted as duplicate
            if ($this->product->is_duplicate($data['PART_NAME'], $id)) {
                echo json_encode(array('success' => false, 'message' => 'Product Name sudah digunakan.'));
                return;
            }

            // mulai transaksi
            $this->product->tms_db->trans_begin();

            if ($data['PART_UNITS'] === null) {
                unset($data['PART_UNITS']);
            }
            if ($data['PART_CUS_ID'] === null) {
                unset($data['PART_CUS_ID']);
            }

            // update product (method update tidak mengelola trans)
            $this->product->update($id, $data);

            // gunakan $db connection yang sama untuk mengelola relasi
            $db = $this->product->tms_db;

            // cek apakah tabel product_group tersedia (defensive)
            $can_manage_pg = true;
            if (method_exists($db, 'table_exists')) {
                $can_manage_pg = $db->table_exists('MS_PART_MEMBERS');
            }

            if (!$can_manage_pg) {
                // tabel tidak ada -> skip relasi (update product tetap commit)
                log_message('debug', 'MS_PART_MEMBERS not found — skipping relation management for product_id=' . $id);
            } else {
                // ambil existing relation aktif (child = current product id)
                $existingRow = $db->query(
                    "SELECT PARTM_ID, PARTM_PARENT_ID FROM MS_PART_MEMBERS
                 WHERE PARTM_CHILD_ID = ? AND PARTM_DATE_END IS NULL",
                    array((int)$id)
                )->row_array();

                // jika user memilih parent > 0 => mau set/ubah relasi
                if ($selected_parent > 0) {

                    if ($existingRow) {
                        if ((int)$existingRow['PARTM_PARENT_ID'] !== (int)$selected_parent) {

                            // tutup relasi lama
                            $db->query(
                                "UPDATE MS_PART_MEMBERS
                                    SET PARTM_DATE_END = GETDATE()
                                    WHERE PARTM_ID = ?",
                                [(int)$existingRow['PARTM_ID']]
                            );

                            // buat relasi baru
                            $db->query(
                                "INSERT INTO MS_PART_MEMBERS
                                    (PARTM_PARENT_ID, PARTM_CHILD_ID, PARTM_DATE_START, PARTM_DATE_END)
                                    VALUES (?, ?, GETDATE(), NULL)",
                                [(int)$selected_parent, (int)$id]
                            );
                        }
                    } else {
                        // belum ada relasi → langsung insert
                        $db->query(
                            "INSERT INTO MS_PART_MEMBERS
                                (PARTM_PARENT_ID, PARTM_CHILD_ID, PARTM_DATE_START, PARTM_DATE_END)
                                VALUES (?, ?, GETDATE(), NULL)",
                            [(int)$selected_parent, (int)$id]
                        );
                    }
                }
            }

            // commit/rollback final
            if ($this->product->tms_db->trans_status() === FALSE) {
                $this->product->tms_db->trans_rollback();
                echo json_encode(array('success' => false, 'message' => 'Transaksi gagal.'));
                return;
            } else {
                $this->product->tms_db->trans_commit();
            }

            echo json_encode(array('success' => true, 'message' => 'Product berhasil diperbarui.'));
            return;
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    public function delete_product()
    {
        $this->output->set_content_type('application/json');

        $id = $this->input->post('PART_ID', true);
        if (!$id) {
            echo json_encode(array('success' => false, 'message' => 'PART_ID tidak ditemukan.'));
            return;
        }

        $deleted_by = $this->session->userdata('username');
        $this->product->soft_delete($id, $deleted_by);

        // jika ada relasi, akhiri
        if (isset($this->pg) && method_exists($this->pg, 'end_relation_by_child')) {
            $this->pg->end_relation_by_child($id);
        }

        echo json_encode(array('success' => true, 'message' => 'Product berhasil dihapus.'));
    }

    public function get_product_detail()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('PART_ID');
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
            return;
        }

        $row = $this->product->get_by_id($id);
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
            return;
        }

        // ambil relasi group
        $rel = $this->pg->get_relation_by_child($id);
        $row['PARTM_PARENT_ID'] = $rel ? $rel['PARTM_PARENT_ID'] : null;

        echo json_encode([
            'success' => true,
            'data' => $row
        ]);
    }


    public function detail($id = null)
    {
        if (!$id) show_404();

        $id = (int)$id;
        $data = array();

        // ambil product
        $row = $this->product->get_by_id($id);
        if (!$row) show_404();

        // ambil UOM & Customer Name
        $uom = '';
        if (!empty($row['PART_UNITS'])) {
            $uom_row = $this->uom->get_by_id($row['PART_UNITS']);
            $uom = $uom_row ? $uom_row['UOM_NAME'] : '';
        }

        $customer = '';
        if (!empty($row['PART_CUS_ID'])) {
            $cust_row = $this->customer->get_by_id($row['PART_CUS_ID']);
            $customer = $cust_row ? $cust_row['CUS_NAME'] : '';
        }

        // ambil parent group jika ada
        $rel = $this->pg->get_relation_by_child($id);
        $group_name = '';
        if ($rel) {
            $parent = $this->product->get_by_id($rel['PARTM_PARENT_ID']);
            $group_name = $parent ? $parent['PART_NAME'] : '';
        }

        $data['product'] = array(
            'PART_NAME' => $row['PART_NAME'],
            'PART_DESC' => $row['PART_DESC'],
            'PART_TYPE' => $row['PART_TYPE'],
            'PART_DRW_NO' => $row['PART_DRW_NO'],
            'PART_CUS_CODE' => $row['PART_CUS_CODE'],
            'UOM_NAME' => $uom,
            'CUS_NAME' => $customer,
            'PART_GROUP' => $group_name
        );

        $this->view('detail_product', $data, FALSE);
    }
}
