<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_supplier extends CI_Model
{
    private $table = 'TMS_DB.dbo.TMS_M_SUPPLIER';
    public $tms_db;
    public $messages = '';

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_db', TRUE);
    }

    /**
     * Ambil semua supplier aktif (IS_DELETED = 0)
     */
    public function get_all()
    {
        return $this->tms_db
            ->select('SUPPLIER_ID, SUPPLIER_NAME, SUPPLIER_ABBR')
            ->from($this->table)
            ->where('IS_DELETED', 0)
            ->order_by('SUPPLIER_NAME', 'ASC')
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
        return $this->tms_db->where('SUPPLIER_ID', $id)->limit(1)->get($this->table)->row_array();
    }

    public function get_by_name($name)
    {
        $name = trim((string)$name);
        if ($name === '') return null;
        // case-insensitive search
        $sql = "SELECT TOP 1 * FROM {$this->table} WHERE LOWER(SUPPLIER_NAME) = ? AND IS_DELETED = 0";
        $q = $this->tms_db->query($sql, array(strtolower($name)));
        return $q->row_array();
    }

    public function exists_by_name($name)
    {
        return (bool)$this->get_by_name($name);
    }

    public function get_new_sequence()
    {
        $row = $this->tms_db->select_max('SUPPLIER_ID')->get($this->table)->row_array();
        return isset($row['SUPPLIER_ID']) ? ((int)$row['SUPPLIER_ID'] + 1) : 1;
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

        $data = array(
            'SUPPLIER_ID'   => $this->get_new_sequence(),
            'SUPPLIER_NAME' => $name,
            'SUPPLIER_ABBR' => $abbr,
            'IS_DELETED'    => 0,
            'CREATED_AT'    => 'GETDATE()'
        );

        // jika driver tidak mendukung passing raw GETDATE(), kita set false pada escape
        $this->tms_db->trans_start();
        // gunakan query builder agar kompatibel: insert array, lalu jika ingin set GETDATE() gunakan query
        $createdBy = 'SYSTEM';
        if (isset($this->session) && method_exists($this->session, 'userdata')) {
            $u = $this->session->userdata('username');
            if (!empty($u)) $createdBy = $u;
        }

        $ok = $this->tms_db->insert($this->table, array(
            'SUPPLIER_ID'   => $data['SUPPLIER_ID'],
            'SUPPLIER_NAME' => $data['SUPPLIER_NAME'],
            'SUPPLIER_ABBR' => $data['SUPPLIER_ABBR'],
            'IS_DELETED'    => 0,
            'CREATED_BY'    => $createdBy
        ));
        // set CREATED_AT via query (SQL Server)
        $this->tms_db->query("UPDATE {$this->table} SET CREATED_AT = GETDATE() WHERE SUPPLIER_ID = ?", array($data['SUPPLIER_ID']));
        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = 'Supplier berhasil ditambahkan.';
            return true;
        }
        $err = $this->tms_db->error();
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
        $sql = "SELECT COUNT(1) AS cnt FROM {$this->table} WHERE LOWER(SUPPLIER_NAME) = ? AND SUPPLIER_ID <> ? AND IS_DELETED = 0";
        $r = $this->tms_db->query($sql, array(strtolower($name), $id))->row_array();
        $dup = isset($r['cnt']) ? (int)$r['cnt'] : 0;
        if ($dup > 0) {
            $this->messages = 'Nama supplier sudah digunakan oleh data lain.';
            return false;
        }

        $ok = $this->tms_db->where('SUPPLIER_ID', $id)
            ->update($this->table, array('SUPPLIER_NAME' => $name, 'SUPPLIER_ABBR' => $abbr));

        if ($ok) {
            $this->messages = 'Supplier berhasil diubah.';
            return true;
        }
        $err = $this->tms_db->error();
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

        $this->tms_db->trans_begin();
        $ok = $this->tms_db
            ->set('IS_DELETED', 1)
            ->set('DELETED_AT', 'GETDATE()', false)
            ->set('DELETED_BY', $deletedBy)
            ->where('SUPPLIER_ID', $id)
            ->update($this->table);

        if (!$ok || $this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal menghapus supplier. ' . (isset($err['message']) ? $err['message'] : '');
            return false;
        }
        $this->tms_db->trans_commit();
        $this->messages = 'Supplier berhasil dihapus (soft delete).';
        return true;
    }
}
