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
        $id     = $this->input->post('PRODUCT_ID', true);

        // Ambil & normalisasi PRODUCT_IS_GROUP dulu (penting utk validasi)
        $is_group_raw = $this->input->post('PRODUCT_IS_GROUP', true);
        $is_group = ($is_group_raw !== null && (string)$is_group_raw === '1') ? 1 : 0;

        // rules dasar
        $this->form_validation->set_rules('PRODUCT_NAME', 'Product Name', 'required|trim');
        $this->form_validation->set_rules('PRODUCT_IS_GROUP', 'Is Group', 'required|in_list[0,1]');
        $this->form_validation->set_rules('UOM_ID', 'UOM', 'trim');

        // optional fields
        $this->form_validation->set_rules('PRODUCT_DESC', 'Description', 'trim');
        $this->form_validation->set_rules('PRODUCT_CUSTOMER_CODE', 'Customer Code', 'trim');
        $this->form_validation->set_rules('PRODUCT_DRW_NO', 'Drawing No', 'trim');
        $this->form_validation->set_rules('PRODUCT_TYPE', 'Type', 'trim');
        $this->form_validation->set_rules('CUSTOMER_ID', 'Customer', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors()));
            return;
        }

        // Normalisasi nilai untuk dimasukkan ke DB
        $name = $this->input->post('PRODUCT_NAME', true);
        $desc = $this->input->post('PRODUCT_DESC', true);
        $drw  = $this->input->post('PRODUCT_DRW_NO', true);
        $type = $this->input->post('PRODUCT_TYPE', true);

        $customer_code_raw = $this->input->post('PRODUCT_CUSTOMER_CODE', true);
        $customer_code = ($customer_code_raw === '' || $customer_code_raw === null) ? null : $customer_code_raw;

        // UOM / CUSTOMER only for non-group products
        if ($is_group === 1) {
            $uom_id = null;
            $customer_id = null;
            $customer_code = null; // enforce null for groups
        } else {
            $uom_raw = $this->input->post('UOM_ID', true);
            $uom_id = ($uom_raw === '' || $uom_raw === null) ? null : (int)$uom_raw;

            $cust_raw = $this->input->post('CUSTOMER_ID', true);
            $customer_id = ($cust_raw === '' || $cust_raw === null) ? null : (int)$cust_raw;
        }

        $data = array(
            'PRODUCT_NAME'          => $name,
            'PRODUCT_IS_GROUP'      => $is_group,
            'PRODUCT_DESC'          => $desc,
            'PRODUCT_CUSTOMER_CODE' => $customer_code,
            'PRODUCT_DRW_NO'        => $drw,
            'PRODUCT_TYPE'          => $type,
            'UOM_ID'                => $uom_id,
            'CUSTOMER_ID'           => $customer_id,
        );

        // selected_parent (relasi) — bisa kosong/0
        $selected_parent = (int)$this->input->post('PRODUCT_GROUP_PARENT_ID', true);

        // --------------- ADD ---------------
        if ($action === 'ADD') {

            if ($this->product->is_duplicate($data['PRODUCT_NAME'])) {
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
                $can_manage_pg = $db->table_exists('TMS_M_PRODUCT_GROUP');
            }

            // bila user memilih parent relasi -> buat record di TMS_M_PRODUCT_GROUP
            if ($selected_parent > 0) {
                if (!$can_manage_pg) {
                    // skip relasi jika tabel tidak ada — jangan rollback create product
                    log_message('debug', 'TMS_M_PRODUCT_GROUP not found — skipping relation creation for new product_id=' . $new_id);
                } else {
                    // cek apakah relasi aktif sudah ada?
                    $chk = $db->query(
                        "SELECT COUNT(1) AS CNT FROM TMS_NEW.dbo.TMS_M_PRODUCT_GROUP
                     WHERE PRODUCT_GROUP_PARENT_ID = ? AND PRODUCT_GROUP_CHILD_ID = ? AND PRODUCT_GROUP_END_DATE IS NULL",
                        array($selected_parent, $new_id)
                    )->row();

                    if ($chk && (int)$chk->CNT > 0) {
                        // sudah ada relasi; rollback dan return error
                        $this->product->tms_db->trans_rollback();
                        echo json_encode(array('success' => false, 'message' => 'Relasi sudah ada.'));
                        return;
                    }

                    // hitung explicit PRODUCT_GROUP_ID untuk table yang tidak auto-increment
                    $nextQ = $db->query("SELECT ISNULL(MAX(PRODUCT_GROUP_ID),0) + 1 AS next_id FROM TMS_NEW.dbo.TMS_M_PRODUCT_GROUP WITH (TABLOCKX)");
                    if (!$nextQ) {
                        $this->product->tms_db->trans_rollback();
                        echo json_encode(array('success' => false, 'message' => 'Gagal menghitung PRODUCT_GROUP_ID.'));
                        return;
                    }
                    $nextRow = $nextQ->row();
                    $next_pg_id = isset($nextRow->next_id) ? (int)$nextRow->next_id : 0;
                    if ($next_pg_id <= 0) {
                        $this->product->tms_db->trans_rollback();
                        echo json_encode(array('success' => false, 'message' => 'PRODUCT_GROUP_ID tidak valid.'));
                        return;
                    }

                    // insert explicit PRODUCT_GROUP_ID
                    $insSql = "
                    INSERT INTO TMS_NEW.dbo.TMS_M_PRODUCT_GROUP
                    (PRODUCT_GROUP_ID, PRODUCT_GROUP_PARENT_ID, PRODUCT_GROUP_CHILD_ID, PRODUCT_GROUP_START_DATE, PRODUCT_GROUP_END_DATE)
                    VALUES (?, ?, ?, GETDATE(), NULL)
                ";
                    $ok = $db->query($insSql, array($next_pg_id, (int)$selected_parent, (int)$new_id));
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
                echo json_encode(array('success' => false, 'message' => 'PRODUCT_ID tidak valid.'));
                return;
            }

            // passed $id as exclude_id so the current record won't be counted as duplicate
            if ($this->product->is_duplicate($data['PRODUCT_NAME'], $id)) {
                echo json_encode(array('success' => false, 'message' => 'Product Name sudah digunakan.'));
                return;
            }

            // mulai transaksi
            $this->product->tms_db->trans_begin();

            // update product (method update tidak mengelola trans)
            $this->product->update($id, $data);

            // gunakan $db connection yang sama untuk mengelola relasi
            $db = $this->product->tms_db;

            // cek apakah tabel product_group tersedia (defensive)
            $can_manage_pg = true;
            if (method_exists($db, 'table_exists')) {
                $can_manage_pg = $db->table_exists('TMS_M_PRODUCT_GROUP');
            }

            if (!$can_manage_pg) {
                // tabel tidak ada -> skip relasi (update product tetap commit)
                log_message('debug', 'TMS_M_PRODUCT_GROUP not found — skipping relation management for product_id=' . $id);
            } else {
                // ambil existing relation aktif (child = current product id)
                $existingRow = $db->query(
                    "SELECT PRODUCT_GROUP_ID, PRODUCT_GROUP_PARENT_ID FROM TMS_NEW.dbo.TMS_M_PRODUCT_GROUP
                 WHERE PRODUCT_GROUP_CHILD_ID = ? AND PRODUCT_GROUP_END_DATE IS NULL",
                    array((int)$id)
                )->row_array();

                // jika user memilih parent > 0 => mau set/ubah relasi
                if ($selected_parent > 0) {

                    if ($selected_parent === (int)$id) {
                        $this->product->tms_db->trans_rollback();
                        echo json_encode(array('success' => false, 'message' => 'Parent dan Child tidak boleh sama.'));
                        return;
                    }

                    if ($existingRow) {
                        // ada relasi aktif
                        if ((int)$existingRow['PRODUCT_GROUP_PARENT_ID'] !== (int)$selected_parent) {
                            // 1) akhiri relasi lama (set end date)
                            $ok1 = $db->query(
                                "UPDATE TMS_NEW.dbo.TMS_M_PRODUCT_GROUP SET PRODUCT_GROUP_END_DATE = GETDATE() WHERE PRODUCT_GROUP_ID = ?",
                                array((int)$existingRow['PRODUCT_GROUP_ID'])
                            );

                            if ($ok1 === false) {
                                $this->product->tms_db->trans_rollback();
                                echo json_encode(array('success' => false, 'message' => 'Gagal menutup relasi lama.'));
                                return;
                            }

                            // 2) insert relasi baru (hitung next id)
                            $nextRow = $db->query("SELECT ISNULL(MAX(PRODUCT_GROUP_ID),0) + 1 AS next_id FROM TMS_NEW.dbo.TMS_M_PRODUCT_GROUP WITH (TABLOCKX)")->row_array();
                            $next_id = isset($nextRow['next_id']) ? (int)$nextRow['next_id'] : 0;
                            if ($next_id <= 0) {
                                $this->product->tms_db->trans_rollback();
                                echo json_encode(array('success' => false, 'message' => 'PRODUCT_GROUP_ID tidak valid.'));
                                return;
                            }

                            $ins = $db->query(
                                "INSERT INTO TMS_NEW.dbo.TMS_M_PRODUCT_GROUP (PRODUCT_GROUP_ID, PRODUCT_GROUP_PARENT_ID, PRODUCT_GROUP_CHILD_ID, PRODUCT_GROUP_START_DATE, PRODUCT_GROUP_END_DATE)
                             VALUES (?, ?, ?, GETDATE(), NULL)",
                                array($next_id, (int)$selected_parent, (int)$id)
                            );

                            if ($ins === false) {
                                $this->product->tms_db->trans_rollback();
                                echo json_encode(array('success' => false, 'message' => 'Gagal membuat relasi baru.'));
                                return;
                            }
                        }
                        // jika parent sama, tidak perlu apa-apa
                    } else {
                        // tidak ada existing -> buat relasi baru langsung
                        $nextRow = $db->query("SELECT ISNULL(MAX(PRODUCT_GROUP_ID),0) + 1 AS next_id FROM TMS_NEW.dbo.TMS_M_PRODUCT_GROUP WITH (TABLOCKX)")->row_array();
                        $next_id = isset($nextRow['next_id']) ? (int)$nextRow['next_id'] : 0;
                        if ($next_id <= 0) {
                            $this->product->tms_db->trans_rollback();
                            echo json_encode(array('success' => false, 'message' => 'PRODUCT_GROUP_ID tidak valid.'));
                            return;
                        }

                        $ins = $db->query(
                            "INSERT INTO TMS_NEW.dbo.TMS_M_PRODUCT_GROUP (PRODUCT_GROUP_ID, PRODUCT_GROUP_PARENT_ID, PRODUCT_GROUP_CHILD_ID, PRODUCT_GROUP_START_DATE, PRODUCT_GROUP_END_DATE)
                         VALUES (?, ?, ?, GETDATE(), NULL)",
                            array($next_id, (int)$selected_parent, (int)$id)
                        );

                        if ($ins === false) {
                            $this->product->tms_db->trans_rollback();
                            echo json_encode(array('success' => false, 'message' => 'Gagal membuat relasi baru.'));
                            return;
                        }
                    }
                } else {
                    // selected_parent == 0 -> akhiri relasi lama jika ada
                    if ($existingRow) {
                        $ok = $db->query(
                            "UPDATE TMS_NEW.dbo.TMS_M_PRODUCT_GROUP SET PRODUCT_GROUP_END_DATE = GETDATE() WHERE PRODUCT_GROUP_ID = ?",
                            array((int)$existingRow['PRODUCT_GROUP_ID'])
                        );
                        if ($ok === false) {
                            $this->product->tms_db->trans_rollback();
                            echo json_encode(array('success' => false, 'message' => 'Gagal mengakhiri relasi lama.'));
                            return;
                        }
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

        $id = $this->input->post('PRODUCT_ID', true);
        if (!$id) {
            echo json_encode(array('success' => false, 'message' => 'PRODUCT_ID tidak ditemukan.'));
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

        $id = $this->input->post('PRODUCT_ID', true);
        $row = $this->product->get_by_id($id);

        $rel = null;
        if (isset($this->pg) && method_exists($this->pg, 'get_relation_by_child')) {
            $rel = $this->pg->get_relation_by_child($id);
        }
        if ($rel) {
            $row['PRODUCT_GROUP_PARENT_ID'] = $rel['PRODUCT_GROUP_PARENT_ID'];
        } else {
            $row['PRODUCT_GROUP_PARENT_ID'] = null;
        }

        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
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
        if (!empty($row['UOM_ID'])) {
            $uom_row = $this->uom->get_by_id($row['UOM_ID']);
            $uom = $uom_row ? $uom_row['UOM_NAME'] : '';
        }

        $customer = '';
        if (!empty($row['CUSTOMER_ID'])) {
            $cust_row = $this->customer->get_by_id($row['CUSTOMER_ID']);
            $customer = $cust_row ? $cust_row['CUSTOMER_NAME'] : '';
        }

        // ambil parent group jika ada
        $rel = $this->pg->get_relation_by_child($id);
        $group_name = '';
        if ($rel) {
            $parent = $this->product->get_by_id($rel['PRODUCT_GROUP_PARENT_ID']);
            $group_name = $parent ? $parent['PRODUCT_NAME'] : '';
        }

        $data['product'] = array(
            'PRODUCT_NAME' => $row['PRODUCT_NAME'],
            'PRODUCT_DESC' => $row['PRODUCT_DESC'],
            'PRODUCT_TYPE' => $row['PRODUCT_TYPE'],
            'PRODUCT_DRW_NO' => $row['PRODUCT_DRW_NO'],
            'PRODUCT_CUSTOMER_CODE' => $row['PRODUCT_CUSTOMER_CODE'],
            'UOM_NAME' => $uom,
            'CUSTOMER_NAME' => $customer,
            'PRODUCT_GROUP' => $group_name
        );

        $this->view('detail_product', $data, FALSE);
    }
}
