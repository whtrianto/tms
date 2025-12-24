<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_reason extends CI_Model
{
    private $table = 'MS_REASON';
    public $tms_db;
    public $messages = '';

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_NEW', TRUE);
    }

    // cek apakah kolom ada
    // Source: INFORMATION_SCHEMA.COLUMNS
    protected function has_column($col)
    {
        $col = trim((string)$col);
        if ($col === '') return false;
        $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'MS_REASON' AND COLUMN_NAME = ?";
        $q = $this->tms_db->query($sql, array($col));
        return ($q && $q->num_rows() > 0);
    }

    /**
     * Tentukan nama kolom flag deleted yang tersedia pada tabel.
     * Prioritas: IS_DELETED, lalu IS_DELETE. Jika tidak ada, kembalikan IS_DELETED (default)
     * @return string
     * Source: INFORMATION_SCHEMA.COLUMNS
     */
    protected function deleted_column()
    {
        if ($this->has_column('IS_DELETED')) return 'IS_DELETED';
        if ($this->has_column('IS_DELETE')) return 'IS_DELETE';
        return 'IS_DELETED';
    }

    /**
     * Source: MS_REASON
     */
    public function get_active()
    {
        $del = $this->deleted_column();
        return $this->tms_db
            ->select('REASON_ID, REASON_NAME, REASON_CODE')
            ->from($this->table)
            ->where($del, 0)
            ->order_by('REASON_NAME', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Source: MS_REASON
     */
    public function get_all($search = null)
    {
        $del = $this->deleted_column();
        $params = array();
        $sql = "
            SELECT REASON_ID, REASON_NAME, REASON_CODE
            FROM {$this->table}
            WHERE {$del} = 0
        ";
        if (!empty($search)) {
            $sql .= " AND (REASON_NAME LIKE ? OR REASON_CODE LIKE ?)";
            $like = '%' . $search . '%';
            $params = array($like, $like);
        }
        $sql .= " ORDER BY REASON_NAME ASC";
        return $this->tms_db->query($sql, $params)->result_array();
    }

    /**
     * Source: MS_REASON
     */
    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $del = $this->deleted_column();
        return $this->tms_db->where('REASON_ID', $id)->where($del, 0)->limit(1)->get($this->table)->row_array();
    }

    /**
     * Source: MS_REASON
     */
    public function get_by_name_or_code($value, $only_active = true)
    {
        $value = trim((string)$value);
        if ($value === '') return null;

        $del = $this->deleted_column();

        if ($only_active) {
            $sql = "SELECT TOP 1 * FROM {$this->table}
                WHERE ({$del} = 0) AND (UPPER(RTRIM(LTRIM(REASON_NAME))) = ? OR UPPER(RTRIM(LTRIM(REASON_CODE))) = ?)";
        } else {
            $sql = "SELECT TOP 1 * FROM {$this->table}
                WHERE (UPPER(RTRIM(LTRIM(REASON_NAME))) = ? OR UPPER(RTRIM(LTRIM(REASON_CODE))) = ?)";
        }
        $q = $this->tms_db->query($sql, array(strtoupper($value), strtoupper($value)));
        return $q->row_array();
    }

    /**
     * Cek eksistensi nama atau code (hanya baris non-deleted)
     * Source: MS_REASON
     */
    public function exists_by_name_or_code($name, $code = null, $only_active = true)
    {
        $name = trim((string)$name);
        if ($name === '') return false;
        $del = $this->deleted_column();

        // build base where
        if ($only_active) {
            // pastikan kondisi deleted apply ke kedua kondisi name/code
            $sql = "SELECT COUNT(1) AS CNT FROM {$this->table}
                WHERE {$del} = 0
                  AND (UPPER(RTRIM(LTRIM(REASON_NAME))) = ?";
            $params = [strtoupper($name)];

            if ($code !== null && trim((string)$code) !== '') {
                $sql .= " OR UPPER(RTRIM(LTRIM(REASON_CODE))) = ?";
                $params[] = strtoupper(trim((string)$code));
            }
            $sql .= ")";
        } else {
            $sql = "SELECT COUNT(1) AS CNT FROM {$this->table}
                WHERE (UPPER(RTRIM(LTRIM(REASON_NAME))) = ?";
            $params = [strtoupper($name)];
            if ($code !== null && trim((string)$code) !== '') {
                $sql .= " OR UPPER(RTRIM(LTRIM(REASON_CODE))) = ?";
                $params[] = strtoupper(trim((string)$code));
            }
            $sql .= ")";
        }

        $row = $this->tms_db->query($sql, $params)->row_array();
        return ($row && (int)$row['CNT'] > 0);
    }

    /**
     * Source: MS_REASON
     */
    public function get_new_sequence()
    {
        $row = $this->tms_db->select_max('REASON_ID')->get($this->table)->row_array();
        return isset($row['REASON_ID']) ? ((int)$row['REASON_ID'] + 1) : 1;
    }

    /* ===== MUTATORS ===== */

    /**
     * Source: MS_REASON
     */
    public function add_data($data)
    {
        $name = isset($data['REASON_NAME']) ? trim((string)$data['REASON_NAME']) : '';
        $code = isset($data['REASON_CODE']) ? trim((string)$data['REASON_CODE']) : null;

        if ($name === '') {
            $this->messages = 'Reason name tidak boleh kosong.';
            return false;
        }

        $delCol = $this->deleted_column();

        // 1) Cek apakah ada row aktif yang duplikat (cek hanya pada baris non-deleted)
        if ($this->exists_by_name_or_code($name, $code, true)) {
            $this->messages = 'Reason name atau code sudah ada (aktif).';
            return false;
        }

        // NOTE: sebelumnya kita re-activate row deleted jika ditemukan.
        // Sekarang: kita TIDAK melakukan re-activate — kita tetap INSERT baru
        // walau ada row deleted yang matching.

        // 2) Lakukan insert normal (beri REASON_ID baru)
        $new_id = $this->get_new_sequence();

        $insert = array(
            'REASON_NAME' => $name,
            'REASON_CODE' => $code,
            'IS_DELETED'  => 0,
        );

        if ($this->has_column($delCol)) $insert[$delCol] = 0;

        $this->tms_db->trans_start();
        $ok = $this->tms_db->insert($this->table, $insert);

        // set CREATED_AT / CREATED_BY bila kolom ada
        if ($ok && $this->has_column('CREATED_AT')) {
            $this->tms_db->query("UPDATE {$this->table} SET CREATED_AT = GETDATE() WHERE REASON_ID = ?", array($new_id));
        }
        if ($ok && $this->has_column('CREATED_BY') && isset($this->session)) {
            $u = $this->session->userdata('username') ?: null;
            if ($u !== null) {
                $this->tms_db->query("UPDATE {$this->table} SET CREATED_BY = ? WHERE REASON_ID = ?", array($u, $new_id));
            }
        }
        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = 'Reason berhasil ditambahkan.';
            return true;
        }

        $err = $this->tms_db->error();
        $this->messages = 'Gagal menambahkan reason. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Source: MS_REASON
     */
    public function edit_data($id, $data)
    {
        $id = (int)$id;
        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        $name = isset($data['REASON_NAME']) ? trim((string)$data['REASON_NAME']) : $current['REASON_NAME'];
        $code = isset($data['REASON_CODE']) ? trim((string)$data['REASON_CODE']) : $current['REASON_CODE'];

        if ($name === '') {
            $this->messages = 'Reason name tidak boleh kosong.';
            return false;
        }

        // cek duplikat pada baris non-deleted (exclude current id)
        $del = $this->deleted_column();
        $sql = "SELECT COUNT(1) AS CNT FROM {$this->table}
                WHERE {$del} = 0
                  AND (UPPER(RTRIM(LTRIM(REASON_NAME))) = ? OR (REASON_CODE IS NOT NULL AND UPPER(RTRIM(LTRIM(REASON_CODE))) = ?))
                  AND REASON_ID <> ?";
        $params = array(strtoupper($name), strtoupper($code), $id);
        $row = $this->tms_db->query($sql, $params)->row_array();
        if ($row && (int)$row['CNT'] > 0) {
            $this->messages = 'Reason name/code sudah digunakan oleh data lain.';
            return false;
        }

        $update = array(
            'REASON_NAME' => $name,
            'REASON_CODE' => $code
        );

        $ok = $this->tms_db->where('REASON_ID', $id)->update($this->table, $update);
        if ($ok) {
            $this->messages = 'Reason berhasil diubah.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal mengubah reason. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Source: MS_REASON
     */
    public function delete_data($id, $actor = null)
    {
        $id = (int)$id;
        $row = $this->get_by_id($id);

        if (!$row) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        $delCol = $this->deleted_column();

        // 1. Ambil Session Username secara akurat
        $CI = &get_instance();
        $sessionUser = $CI->session->userdata('username');

        // Prioritas: Parameter $actor -> Session -> Fallback 'SYSTEM'
        $deletedBy = $actor ?: ($sessionUser ?: 'SYSTEM');

        $this->tms_db->trans_start();

        $this->tms_db->where('REASON_ID', $id);

        // 2. Set data flag delete dan user
        $updateData = [
            $delCol      => 1,
            'DELETED_BY' => $deletedBy
        ];

        // 3. Set waktu delete menggunakan fungsi SQL Server GETDATE()
        if ($this->has_column('DELETED_AT')) {
            $this->tms_db->set('DELETED_AT', 'GETDATE()', FALSE);
        }

        $this->tms_db->update($this->table, $updateData);

        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->messages = 'Gagal menghapus reason: ' . (isset($err['message']) ? $err['message'] : 'Database Error');
            return false;
        }

        $this->messages = 'Reason berhasil dihapus';
        return true;
    }

    /**
     * Source: MS_REASON
     */
    public function is_duplicate($nameOrCode, $exclude_id = null)
    {
        $nameOrCode = trim((string)$nameOrCode);
        if ($nameOrCode === '') return false;

        $del = $this->deleted_column();
        $params = [strtoupper($nameOrCode), strtoupper($nameOrCode)];
        $sql = "SELECT COUNT(1) AS CNT FROM {$this->table}
            WHERE {$del} = 0
              AND (UPPER(RTRIM(LTRIM(REASON_NAME))) = ? OR UPPER(RTRIM(LTRIM(REASON_CODE))) = ?)";
        if (!empty($exclude_id)) {
            $sql .= " AND REASON_ID <> ?";
            $params[] = (int)$exclude_id;
        }
        $row = $this->tms_db->query($sql, $params)->row();
        return ($row && (int)$row->CNT > 0);
    }
}
