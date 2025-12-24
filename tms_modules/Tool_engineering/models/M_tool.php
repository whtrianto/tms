<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_tool extends CI_Model
{
    private $table = 'MS_TOOL_CLASS';
    public $tms_db;
    public $messages = '';

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_NEW', TRUE);
    }

    // defensive: cek kolom ada atau tidak
    protected function has_column($col)
    {
        $col = trim((string)$col);
        if ($col === '') return false;
        $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'MS_TOOL_CLASS' AND COLUMN_NAME = ?";
        $q = $this->tms_db->query($sql, array($col));
        return ($q && $q->num_rows() > 0);
    }

    public function get_active()
    {
        return $this->tms_db
            ->select('TC_ID, TC_NAME, TC_DESC')
            ->from($this->table)
            ->where('IS_DELETED', 0)
            ->order_by('TC_NAME', 'ASC')
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
                T.TC_ID,
                T.TC_NAME,
                T.TC_DESC,
                T.TC_TYPE,
                TT.TT_NAME
            FROM {$this->table} T
            LEFT JOIN MS_TOOL_TYPE TT
                ON TT.TT_ID = T.TC_TYPE
                AND TT.IS_DELETED = 0
            WHERE T.IS_DELETED = 0
        ";

        if (!empty($search)) {
            $sql .= " AND (T.TC_NAME LIKE ? OR T.TC_DESC LIKE ? OR TT.TT_NAME LIKE ?)";
            $like = '%' . $search . '%';
            $params = array($like, $like, $like);
        }

        $sql .= " ORDER BY T.TC_NAME ASC";

        return $this->tms_db->query($sql, $params)->result_array();
    }

    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $sql = "SELECT TC_ID, TC_NAME, TC_DESC, TC_TYPE FROM {$this->table} WHERE IS_DELETED = 0 AND TC_ID = ?";
        return $this->tms_db->query($sql, array($id))->row_array();
    }

    public function get_by_name($name)
    {
        $name = trim((string)$name);
        if ($name === '') return null;
        $sql = "SELECT TOP 1 * FROM {$this->table} WHERE UPPER(RTRIM(LTRIM(TC_NAME))) = ? AND IS_DELETED = 0";
        $q = $this->tms_db->query($sql, array(strtoupper($name)));
        return $q->row_array();
    }

    public function exists_by_name($name)
    {
        return (bool)$this->get_by_name($name);
    }

    public function get_new_sequence()
    {
        $row = $this->tms_db->select_max('TC_ID')->get($this->table)->row_array();
        return isset($row['TC_ID']) ? ((int)$row['TC_ID'] + 1) : 1;
    }

    /* ===== MUTATORS ===== */

    public function add_data($data)
    {
        $name = isset($data['TC_NAME']) ? trim($data['TC_NAME']) : '';
        if ($name === '') {
            $this->messages = 'Tool name tidak boleh kosong.';
            return false;
        }

        $new_id = $this->get_new_sequence();

        $insert = array(
            // 'TC_ID'   => $new_id,
            'TC_NAME' => $name,
            'TC_DESC' => isset($data['TC_DESC']) ? $data['TC_DESC'] : null,
            'TC_TYPE' => (isset($data['TC_TYPE']) && $data['TC_TYPE'] !== '') ? (int)$data['TC_TYPE'] : null,
            'IS_DELETED' => 0
        );

        $this->tms_db->trans_start();
        $ok = $this->tms_db->insert($this->table, $insert);

        // set CREATED_AT/CREATED_BY jika kolom ada
        // if ($this->has_column('CREATED_AT')) {
        //     $this->tms_db->query("UPDATE {$this->table} SET CREATED_AT = GETDATE() WHERE TC_ID = ?", array($new_id));
        // }
        // if ($this->has_column('CREATED_BY') && isset($this->session)) {
        //     $u = $this->session->userdata('username') ?: null;
        //     if ($u !== null) {
        //         $this->tms_db->query("UPDATE {$this->table} SET CREATED_BY = ? WHERE TC_ID = ?", array($u, $new_id));
        //     }
        // }
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
            'TC_NAME' => isset($data['TC_NAME']) ? $data['TC_NAME'] : $current['TC_NAME'],
            'TC_DESC' => isset($data['TC_DESC']) ? $data['TC_DESC'] : $current['TC_DESC'],
            'TC_TYPE' => (isset($data['TC_TYPE']) && $data['TC_TYPE'] !== '') ? (int)$data['TC_TYPE'] : null,
        );

        $ok = $this->tms_db->where('TC_ID', $id)->update($this->table, $update);
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
        $this->tms_db->where('TC_ID', $id);
        $ok = $this->tms_db->update($this->table, $updateData);
        if ($ok && $this->has_column('DELETED_AT')) {
            $ok2 = $this->tms_db->query("UPDATE {$this->table} SET DELETED_AT = GETDATE() WHERE TC_ID = ?", array($id));
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
                  AND UPPER(RTRIM(LTRIM(TC_NAME))) = ?";
        if (!empty($exclude_id)) {
            $sql .= " AND TC_ID <> ?";
            $params[] = (int)$exclude_id;
        }
        $row = $this->tms_db->query($sql, $params)->row();
        return ($row && (int)$row->CNT > 0);
    }
}
