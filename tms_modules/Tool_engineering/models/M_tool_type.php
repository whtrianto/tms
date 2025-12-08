<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_tool_type extends CI_Model
{
    private $table = 'TMS_DB.dbo.TMS_M_TOOL_TYPE';
    public $tms_db;
    public $messages = '';

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_db', TRUE);
    }

    public function get_active()
    {
        return $this->tms_db
            ->select('TOOL_TYPE_ID, TOOL_TYPE_NAME, TOOL_TYPE_DESC')
            ->from($this->table)
            ->where('IS_DELETED', 0)
            ->order_by('TOOL_TYPE_NAME', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Cek apakah kolom ada di tabel (SQL Server INFORMATION_SCHEMA)
     * @param string $col
     * @return bool
     */
    protected function has_column($col)
    {
        $col = trim((string)$col);
        if ($col === '') return false;

        // gunakan INFORMATION_SCHEMA untuk kompatibilitas SQL Server
        $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'TMS_M_TOOL_TYPE' AND COLUMN_NAME = ?";
        $q = $this->tms_db->query($sql, array($col));
        return ($q && $q->num_rows() > 0);
    }

    public function get_all()
    {
        return $this->tms_db
            ->select('TOOL_TYPE_ID, TOOL_TYPE_NAME, TOOL_TYPE_DESC')
            ->from($this->table)
            ->where('IS_DELETED', 0)
            ->order_by('TOOL_TYPE_NAME', 'ASC')
            ->get()
            ->result_array();
    }

    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        return $this->tms_db->where('TOOL_TYPE_ID', $id)->limit(1)->get($this->table)->row_array();
    }

    public function get_by_name($name)
    {
        $name = trim((string)$name);
        if ($name === '') return null;
        $sql = "SELECT TOP 1 * FROM {$this->table} WHERE UPPER(TOOL_TYPE_NAME) = ? AND IS_DELETED = 0";
        $q = $this->tms_db->query($sql, array(strtoupper($name)));
        return $q->row_array();
    }

    public function exists_by_name($name)
    {
        return (bool)$this->get_by_name($name);
    }

    public function get_new_sequence()
    {
        $row = $this->tms_db->select_max('TOOL_TYPE_ID')->get($this->table)->row_array();
        return isset($row['TOOL_TYPE_ID']) ? ((int)$row['TOOL_TYPE_ID'] + 1) : 1;
    }

    /* ========== MUTATORS ========== */

    public function add_data($name, $desc = null)
    {
        $name = trim((string)$name);
        $desc = $desc !== null ? trim((string)$desc) : null;

        if ($name === '') {
            $this->messages = 'Nama tool type tidak boleh kosong.';
            return false;
        }

        if ($this->exists_by_name($name)) {
            $this->messages = 'Tool type sudah ada.';
            return false;
        }

        $new_id = $this->get_new_sequence();

        $this->tms_db->trans_start();
        $ok = $this->tms_db->insert($this->table, array(
            'TOOL_TYPE_ID'   => $new_id,
            'TOOL_TYPE_NAME' => $name,
            'TOOL_TYPE_DESC' => $desc,
            'IS_DELETED'     => 0
        ));

        // set CREATED_AT only when column exists
        if ($this->has_column('CREATED_AT')) {
            // menggunakan parameter untuk safety
            $this->tms_db->query("UPDATE {$this->table} SET CREATED_AT = GETDATE() WHERE TOOL_TYPE_ID = ?", array($new_id));
        }

        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = 'Tool Type berhasil ditambahkan.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal menambahkan tool type. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    public function edit_data($id, $name, $desc = null)
    {
        $id = (int)$id;
        $name = trim((string)$name);
        $desc = $desc !== null ? trim((string)$desc) : null;

        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        if ($name === '') {
            $this->messages = 'Nama tool type tidak boleh kosong.';
            return false;
        }

        // cek duplikat pada baris lain
        $sql = "SELECT COUNT(1) AS cnt FROM {$this->table} WHERE UPPER(TOOL_TYPE_NAME) = ? AND TOOL_TYPE_ID <> ? AND IS_DELETED = 0";
        $r = $this->tms_db->query($sql, array(strtoupper($name), $id))->row_array();
        $dup = isset($r['cnt']) ? (int)$r['cnt'] : 0;
        if ($dup > 0) {
            $this->messages = 'Nama tool type sudah digunakan oleh data lain.';
            return false;
        }

        $ok = $this->tms_db->where('TOOL_TYPE_ID', $id)
            ->update($this->table, array('TOOL_TYPE_NAME' => $name, 'TOOL_TYPE_DESC' => $desc));

        if ($ok) {
            $this->messages = 'Tool Type berhasil diubah.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal mengubah tool type. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    public function delete_data($id)
    {
        $id = (int)$id;
        $row = $this->get_by_id($id);
        if (!$row) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }
        if (!empty($row['IS_DELETED'])) {
            $this->messages = 'Tool Type sudah dihapus.';
            return false;
        }

        $deletedBy = 'SYSTEM';
        if (isset($this->session) && method_exists($this->session, 'userdata')) {
            $u = $this->session->userdata('username');
            if (!empty($u)) $deletedBy = $u;
        }

        $this->tms_db->trans_begin();

        // build update array dynamically depending on available columns
        $updateData = array('IS_DELETED' => 1);

        // if DELETED_AT exists, we'll set it using raw GETDATE() (no escaping)
        $set_deleted_at_raw = false;
        if ($this->has_column('DELETED_AT')) {
            $set_deleted_at_raw = true;
        }
        if ($this->has_column('DELETED_BY')) {
            // include DELETED_BY in update array (will be escaped)
            $updateData['DELETED_BY'] = $deletedBy;
        }

        // perform update: if DELETED_AT needs raw GETDATE(), use query builder then raw query
        if ($set_deleted_at_raw) {
            // build where
            $this->tms_db->where('TOOL_TYPE_ID', $id);
            $ok = $this->tms_db->update($this->table, $updateData);
            if ($ok) {
                // run separate raw to set DELETED_AT to GETDATE()
                $ok2 = $this->tms_db->query("UPDATE {$this->table} SET DELETED_AT = GETDATE() WHERE TOOL_TYPE_ID = ?", array($id));
                if (!$ok2) $ok = false;
            }
        } else {
            // safe update without raw GETDATE()
            $this->tms_db->where('TOOL_TYPE_ID', $id);
            $ok = $this->tms_db->update($this->table, $updateData);
        }

        if (!$ok || $this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal menghapus tool type. ' . (isset($err['message']) ? $err['message'] : '');
            return false;
        }
        $this->tms_db->trans_commit();
        $this->messages = 'Tool Type berhasil dihapus (soft delete).';
        return true;
    }
}
