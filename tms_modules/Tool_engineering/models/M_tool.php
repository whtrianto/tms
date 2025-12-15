<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_tool extends CI_Model
{
    private $table = 'TMS_NEW.dbo.TMS_M_TOOL';
    public $tms_db;
    public $messages = '';

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_db', TRUE);
    }

    // defensive: cek kolom ada atau tidak
    protected function has_column($col)
    {
        $col = trim((string)$col);
        if ($col === '') return false;
        $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'TMS_M_TOOL' AND COLUMN_NAME = ?";
        $q = $this->tms_db->query($sql, array($col));
        return ($q && $q->num_rows() > 0);
    }

    public function get_active()
    {
        return $this->tms_db
            ->select('TOOL_ID, TOOL_NAME, TOOL_DESC')
            ->from($this->table)
            ->where('IS_DELETED', 0)
            ->order_by('TOOL_NAME', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * get_all dengan optional search (per-column searching done clientside)
     */
    public function get_all($search = null)
    {
        $params = array();

        $sql = "
            SELECT
                T.TOOL_ID,
                T.TOOL_NAME,
                T.TOOL_DESC,
                T.TOOL_TYPE,
                TT.TOOL_TYPE_NAME
            FROM {$this->table} T
            LEFT JOIN TMS_NEW.dbo.TMS_M_TOOL_TYPE TT
                ON TT.TOOL_TYPE_ID = T.TOOL_TYPE
                AND TT.IS_DELETED = 0
            WHERE T.IS_DELETED = 0
        ";

        if (!empty($search)) {
            $sql .= " AND (T.TOOL_NAME LIKE ? OR T.TOOL_DESC LIKE ? OR TT.TOOL_TYPE_NAME LIKE ?)";
            $like = '%' . $search . '%';
            $params = array($like, $like, $like);
        }

        $sql .= " ORDER BY T.TOOL_NAME ASC";

        return $this->tms_db->query($sql, $params)->result_array();
    }

    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $sql = "SELECT TOOL_ID, TOOL_NAME, TOOL_DESC, TOOL_TYPE FROM {$this->table} WHERE IS_DELETED = 0 AND TOOL_ID = ?";
        return $this->tms_db->query($sql, array($id))->row_array();
    }

    public function get_by_name($name)
    {
        $name = trim((string)$name);
        if ($name === '') return null;
        $sql = "SELECT TOP 1 * FROM {$this->table} WHERE UPPER(RTRIM(LTRIM(TOOL_NAME))) = ? AND IS_DELETED = 0";
        $q = $this->tms_db->query($sql, array(strtoupper($name)));
        return $q->row_array();
    }

    public function exists_by_name($name)
    {
        return (bool)$this->get_by_name($name);
    }

    public function get_new_sequence()
    {
        $row = $this->tms_db->select_max('TOOL_ID')->get($this->table)->row_array();
        return isset($row['TOOL_ID']) ? ((int)$row['TOOL_ID'] + 1) : 1;
    }

    /* ===== MUTATORS ===== */

    public function add_data($data)
    {
        $name = isset($data['TOOL_NAME']) ? trim($data['TOOL_NAME']) : '';
        if ($name === '') {
            $this->messages = 'Tool name tidak boleh kosong.';
            return false;
        }

        $new_id = $this->get_new_sequence();

        $insert = array(
            'TOOL_ID'   => $new_id,
            'TOOL_NAME' => $name,
            'TOOL_DESC' => isset($data['TOOL_DESC']) ? $data['TOOL_DESC'] : null,
            'TOOL_TYPE' => (isset($data['TOOL_TYPE']) && $data['TOOL_TYPE'] !== '') ? (int)$data['TOOL_TYPE'] : null,
            'IS_DELETED' => 0
        );

        $this->tms_db->trans_start();
        $ok = $this->tms_db->insert($this->table, $insert);

        // set CREATED_AT/CREATED_BY jika kolom ada
        if ($this->has_column('CREATED_AT')) {
            $this->tms_db->query("UPDATE {$this->table} SET CREATED_AT = GETDATE() WHERE TOOL_ID = ?", array($new_id));
        }
        if ($this->has_column('CREATED_BY') && isset($this->session)) {
            $u = $this->session->userdata('username') ?: null;
            if ($u !== null) {
                $this->tms_db->query("UPDATE {$this->table} SET CREATED_BY = ? WHERE TOOL_ID = ?", array($u, $new_id));
            }
        }
        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = 'Tool berhasil ditambahkan.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal menambahkan tool. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    public function edit_data($id, $data)
    {
        $id = (int)$id;
        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        $update = array(
            'TOOL_NAME' => isset($data['TOOL_NAME']) ? $data['TOOL_NAME'] : $current['TOOL_NAME'],
            'TOOL_DESC' => isset($data['TOOL_DESC']) ? $data['TOOL_DESC'] : $current['TOOL_DESC'],
            'TOOL_TYPE' => (isset($data['TOOL_TYPE']) && $data['TOOL_TYPE'] !== '') ? (int)$data['TOOL_TYPE'] : null,
        );

        $ok = $this->tms_db->where('TOOL_ID', $id)->update($this->table, $update);
        if ($ok) {
            $this->messages = 'Tool berhasil diubah.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal mengubah tool. ' . (isset($err['message']) ? $err['message'] : '');
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
            $this->messages = 'Tool sudah dihapus.';
            return false;
        }

        $deletedBy = 'SYSTEM';
        if (isset($this->session) && method_exists($this->session, 'userdata')) {
            $u = $this->session->userdata('username');
            if (!empty($u)) $deletedBy = $u;
        }

        $this->tms_db->trans_begin();

        $updateData = array('IS_DELETED' => 1);
        // set DELETED_BY if exists
        if ($this->has_column('DELETED_BY')) $updateData['DELETED_BY'] = $deletedBy;

        // update and set DELETED_AT if exists
        $this->tms_db->where('TOOL_ID', $id);
        $ok = $this->tms_db->update($this->table, $updateData);
        if ($ok && $this->has_column('DELETED_AT')) {
            $ok2 = $this->tms_db->query("UPDATE {$this->table} SET DELETED_AT = GETDATE() WHERE TOOL_ID = ?", array($id));
            if (!$ok2) $ok = false;
        }

        if (!$ok || $this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal menghapus tool. ' . (isset($err['message']) ? $err['message'] : '');
            return false;
        }
        $this->tms_db->trans_commit();
        $this->messages = 'Tool berhasil dihapus (soft delete).';
        return true;
    }

    /**
     * duplicate check excluding given id (optional)
     */
    public function is_duplicate($name, $exclude_id = null)
    {
        $name_norm = strtoupper(trim($name));
        $params = array($name_norm);

        $sql = "SELECT COUNT(1) AS CNT FROM {$this->table}
                WHERE IS_DELETED = 0
                  AND UPPER(RTRIM(LTRIM(TOOL_NAME))) = ?";
        if (!empty($exclude_id)) {
            $sql .= " AND TOOL_ID <> ?";
            $params[] = (int)$exclude_id;
        }
        $row = $this->tms_db->query($sql, $params)->row();
        return ($row && (int)$row->CNT > 0);
    }
}
