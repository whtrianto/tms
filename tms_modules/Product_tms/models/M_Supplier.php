<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_supplier extends CI_Model
{
    private $table = 'MS_SUPPLIER';
    public $db_tms;
    public $messages = '';

    public function __construct()
    {
        parent::__construct();
        $this->db_tms = $this->load->database('tms_NEW', TRUE);
    }

    /**
     * Ambil semua supplier aktif (IS_DELETED = 0)
     */
    public function get_all()
    {
        return $this->db_tms
            ->select('SUP_ID, SUP_NAME, SUP_ABBR')
            ->from($this->table)
            ->where('IS_DELETED', 0)
            ->order_by('SUP_NAME', 'ASC')
            ->get()
            ->result_array();
    }

    public function get_active()
    {
        // alias for dropdown usage
        return $this->get_all();
    }

    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        return $this->db_tms->where('SUP_ID', $id)->limit(1)->get($this->table)->row_array();
    }

    public function get_by_name($name)
    {
        $name = trim((string)$name);
        if ($name === '') return null;
        // case-insensitive search
        $sql = "SELECT TOP 1 * FROM {$this->table} WHERE LOWER(SUP_NAME) = ? AND IS_DELETED = 0";
        $q = $this->db_tms->query($sql, array(strtolower($name)));
        return $q->row_array();
    }

    public function exists_by_name($name)
    {
        return (bool)$this->get_by_name($name);
    }

    public function get_new_sequence()
    {
        $row = $this->db_tms->select_max('SUP_ID')->get($this->table)->row_array();
        return isset($row['SUP_ID']) ? ((int)$row['SUP_ID'] + 1) : 1;
    }

    /* ========== MUTATORS ========== */

    public function add_data($name, $abbr = null)
    {
        $name = trim((string)$name);
        $abbr = $abbr !== null ? trim((string)$abbr) : null;

        if ($name === '') {
            $this->messages = 'Nama supplier tidak boleh kosong.';
            return false;
        }

        if ($this->exists_by_name($name)) {
            $this->messages = 'Supplier sudah ada.';
            return false;
        }

        // 1. JANGAN GENERATE ID MANUAL. Biarkan kosong agar SQL Server yang isi.
        $createdBy = 'SYSTEM';
        if (isset($this->session) && method_exists($this->session, 'userdata')) {
            $u = $this->session->userdata('username');
            if (!empty($u)) $createdBy = $u;
        }

        $this->db_tms->trans_start();

        // 2. Insert tanpa kolom SUP_ID
        $insert_data = array(
            'SUP_NAME'   => $name,
            'SUP_ABBR'   => $abbr,
            'IS_DELETED' => 0,
            'CREATED_BY' => $createdBy
            // 'CREATED_AT' => tidak perlu di array ini karena akan di-update query bawah
        );

        $this->db_tms->insert($this->table, $insert_data);

        // 3. AMBIL ID YANG BARU SAJA DIGENERATE OLEH SQL SERVER
        $new_id = $this->db_tms->insert_id();

        // 4. Update CREATED_AT menggunakan ID yang baru didapat
        if ($new_id) {
            $this->db_tms->query("UPDATE {$this->table} SET CREATED_BY = GETDATE() WHERE SUP_ID = ?", array($new_id));
        }

        $this->db_tms->trans_complete();

        if ($this->db_tms->trans_status()) {
            $this->messages = 'Supplier berhasil ditambahkan.';
            return true;
        }

        $err = $this->db_tms->error();
        $this->messages = 'Gagal menambahkan supplier. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    public function edit_data($id, $name, $abbr = null)
    {
        $id = (int)$id;
        $name = trim((string)$name);
        $abbr = $abbr !== null ? trim((string)$abbr) : null;

        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data supplier tidak ditemukan.';
            return false;
        }

        if ($name === '') {
            $this->messages = 'Nama supplier tidak boleh kosong.';
            return false;
        }

        // cek duplikat pada baris lain
        $sql = "SELECT COUNT(1) AS cnt FROM {$this->table} WHERE LOWER(SUP_NAME) = ? AND SUP_ID <> ? AND IS_DELETED = 0";
        $r = $this->db_tms->query($sql, array(strtolower($name), $id))->row_array();
        $dup = isset($r['cnt']) ? (int)$r['cnt'] : 0;
        if ($dup > 0) {
            $this->messages = 'Nama supplier sudah digunakan oleh data lain.';
            return false;
        }

        $ok = $this->db_tms->where('SUP_ID', $id)
            ->update($this->table, array('SUP_NAME' => $name, 'SUP_ABBR' => $abbr));

        if ($ok) {
            $this->messages = 'Supplier berhasil diubah.';
            return true;
        }
        $err = $this->db_tms->error();
        $this->messages = 'Gagal mengubah supplier. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    public function delete_data($id)
    {
        $id = (int)$id;
        $row = $this->get_by_id($id);
        if (!$row) {
            $this->messages = 'Data supplier tidak ditemukan.';
            return false;
        }
        if (!empty($row['IS_DELETED'])) {
            $this->messages = 'Supplier sudah dihapus.';
            return false;
        }

        $deletedBy = 'SYSTEM';
        if (isset($this->session) && method_exists($this->session, 'userdata')) {
            $u = $this->session->userdata('username');
            if (!empty($u)) $deletedBy = $u;
        }

        $this->db_tms->trans_begin();
        $ok = $this->db_tms
            ->set('IS_DELETED', 1)
            ->set('DELETED_AT', 'GETDATE()', false)
            ->set('DELETED_BY', $deletedBy)
            ->where('SUP_ID', $id)
            ->update($this->table);

        if (!$ok || $this->db_tms->trans_status() === FALSE) {
            $err = $this->db_tms->error();
            $this->db_tms->trans_rollback();
            $this->messages = 'Gagal menghapus supplier. ' . (isset($err['message']) ? $err['message'] : '');
            return false;
        }
        $this->db_tms->trans_commit();
        $this->messages = 'Supplier berhasil dihapus';
        return true;
    }
}
