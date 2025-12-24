<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_conversion_rate extends CI_Model
{
    private $table = 'MS_CONVERSION_RATE';
    public $tms_db;
    public $messages = '';

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_NEW', TRUE);
    }

    protected function has_column($col)
    {
        // Source: INFORMATION_SCHEMA.COLUMNS
        $col = trim((string)$col);
        if ($col === '') return false;
        $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'MS_CONVERSION_RATE' AND COLUMN_NAME = ?";
        $q = $this->tms_db->query($sql, array($col));
        return ($q && $q->num_rows() > 0);
    }

    protected function deleted_column()
    {
        // Source: INFORMATION_SCHEMA.COLUMNS
        if ($this->has_column('IS_DELETED')) return 'IS_DELETED';
        if ($this->has_column('IS_DELETE')) return 'IS_DELETE';
        return 'IS_DELETED';
    }

    /**
     * Source: MS_CONVERSION_RATE
     */
    public function get_all($search = null)
    {
        $del = $this->deleted_column();
        $params = array();
        $sql = "
            SELECT CON_ID, CON_CURRENCY, CON_RATE
            FROM {$this->table}
            WHERE {$del} = 0
        ";
        if (!empty($search)) {
            $sql .= " AND (CON_CURRENCY LIKE ? OR CAST(CON_RATE AS nvarchar(50)) LIKE ?)";
            $like = '%' . $search . '%';
            $params = array($like, $like);
        }
        $sql .= " ORDER BY CON_CURRENCY ASC";
        return $this->tms_db->query($sql, $params)->result_array();
    }

    /**
     * Source: MS_CONVERSION_RATE
     */
    public function get_active()
    {
        $del = $this->deleted_column();
        return $this->tms_db
            ->select('CON_ID, CON_CURRENCY, CON_RATE')
            ->from($this->table)
            ->where($del, 0)
            ->order_by('CON_CURRENCY', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Source: MS_CONVERSION_RATE
     */
    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $del = $this->deleted_column();
        return $this->tms_db->where('CON_ID', $id)->where($del, 0)->limit(1)->get($this->table)->row_array();
    }

    /**
     * Source: MS_CONVERSION_RATE
     */
    public function get_by_currency($currency, $only_active = true)
    {
        $currency = trim((string)$currency);
        if ($currency === '') return null;
        $del = $this->deleted_column();
        if ($only_active) {
            $sql = "SELECT TOP 1 * FROM {$this->table}
                WHERE ({$del} = 0) AND (UPPER(RTRIM(LTRIM(CON_CURRENCY))) = ?)";
        } else {
            $sql = "SELECT TOP 1 * FROM {$this->table}
                WHERE (UPPER(RTRIM(LTRIM(CON_CURRENCY))) = ?)";
        }
        $q = $this->tms_db->query($sql, array(strtoupper($currency)));
        return $q->row_array();
    }

    /**
     * Source: MS_CONVERSION_RATE
     */
    public function exists_by_currency($currency, $only_active = true)
    {
        $currency = trim((string)$currency);
        if ($currency === '') return false;
        $del = $this->deleted_column();

        if ($only_active) {
            $sql = "SELECT COUNT(1) AS CNT FROM {$this->table}
                WHERE {$del} = 0 AND UPPER(RTRIM(LTRIM(CON_CURRENCY))) = ?";
            $params = [strtoupper($currency)];
        } else {
            $sql = "SELECT COUNT(1) AS CNT FROM {$this->table}
                WHERE UPPER(RTRIM(LTRIM(CON_CURRENCY))) = ?";
            $params = [strtoupper($currency)];
        }

        $row = $this->tms_db->query($sql, $params)->row_array();
        return ($row && (int)$row['CNT'] > 0);
    }

    /**
     * Source: MS_CONVERSION_RATE
     */
    public function get_new_sequence()
    {
        $row = $this->tms_db->select_max('CON_ID')->get($this->table)->row_array();
        return isset($row['CON_ID']) ? ((int)$row['CON_ID'] + 1) : 1;
    }

    /**
     * Source: MS_CONVERSION_RATE
     */
    public function add_data($data)
    {
        $currency = isset($data['CON_CURRENCY']) ? trim((string)$data['CON_CURRENCY']) : '';
        $rate = isset($data['CON_RATE']) ? $data['CON_RATE'] : null;

        if ($currency === '') {
            $this->messages = 'Currency tidak boleh kosong.';
            return false;
        }
        if ($rate === null || $rate === '') {
            $this->messages = 'Rate tidak boleh kosong.';
            return false;
        }

        $delCol = $this->deleted_column();

        // cek duplikat pada baris aktif
        if ($this->exists_by_currency($currency, true)) {
            $this->messages = 'Currency sudah ada (aktif).';
            return false;
        }

        // Insert baru (jangan re-activate)
        $new_id = $this->get_new_sequence();
        $insert = array(
            // 'CON_ID'       => $new_id,
            'CON_CURRENCY' => $currency,
            'CON_RATE'     => $rate,
            'IS_DELETED'   => 0,
        );
        if ($this->has_column($delCol)) $insert[$delCol] = 0;

        $this->tms_db->trans_start();
        $ok = $this->tms_db->insert($this->table, $insert);

        if ($this->has_column('CREATED_AT')) {
            $this->tms_db->query("UPDATE {$this->table} SET CREATED_AT = GETDATE() WHERE CON_ID = ?", array($new_id));
        }
        if ($this->has_column('CREATED_BY') && isset($this->session)) {
            $u = $this->session->userdata('username') ?: null;
            if ($u !== null) {
                $this->tms_db->query("UPDATE {$this->table} SET CREATED_BY = ? WHERE CON_ID = ?", array($u, $new_id));
            }
        }
        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = 'Conversion rate berhasil ditambahkan.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal menambahkan conversion rate. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Source: MS_CONVERSION_RATE
     */
    public function edit_data($id, $data)
    {
        $id = (int)$id;
        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        $currency = isset($data['CON_CURRENCY']) ? trim((string)$data['CON_CURRENCY']) : $current['CON_CURRENCY'];
        $rate = isset($data['CON_RATE']) ? $data['CON_RATE'] : $current['CON_RATE'];

        if ($currency === '') {
            $this->messages = 'Currency tidak boleh kosong.';
            return false;
        }

        // cek duplicate pada baris non-deleted (exclude current id)
        $del = $this->deleted_column();
        $sql = "SELECT COUNT(1) AS CNT FROM {$this->table}
                WHERE {$del} = 0
                  AND UPPER(RTRIM(LTRIM(CON_CURRENCY))) = ?
                  AND CON_ID <> ?";
        $params = array(strtoupper($currency), $id);
        $row = $this->tms_db->query($sql, $params)->row_array();
        if ($row && (int)$row['CNT'] > 0) {
            $this->messages = 'Currency sudah digunakan oleh data lain.';
            return false;
        }

        $update = array(
            'CON_CURRENCY' => $currency,
            'CON_RATE'     => $rate
        );

        $ok = $this->tms_db->where('CON_ID', $id)->update($this->table, $update);
        if ($ok) {
            $this->messages = 'Conversion rate berhasil diubah.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal mengubah conversion rate. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Source: MS_CONVERSION_RATE
     */
    public function delete_data($id, $actor = 'SYSTEM')
    {
        $id = (int)$id;
        // Ambil data untuk memastikan ID ada dan belum dihapus
        $row = $this->get_by_id($id);

        if (!$row) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        // 1. Tentukan nama kolom delete (IS_DELETED atau IS_DELETE)
        $delCol = $this->deleted_column();

        // 2. Ambil Username dari Session secara akurat
        // Menggunakan get_instance() adalah cara paling aman di CI3 Model
        $CI = &get_instance();
        $sessionUser = $CI->session->userdata('username');
        $deletedBy = (!empty($sessionUser)) ? $sessionUser : 'SYSTEM';

        $this->tms_db->trans_start();

        // 3. Susun Update
        $this->tms_db->where('CON_ID', $id);

        // Set kolom IS_DELETED dan DELETED_BY
        $updateData = [
            $delCol      => 1,
            'DELETED_BY' => $deletedBy
        ];

        // 4. Set DELETED_AT menggunakan fungsi SQL Server GETDATE()
        // Parameter FALSE agar CI tidak membungkus GETDATE() dengan tanda kutip
        if ($this->has_column('DELETED_AT')) {
            $this->tms_db->set('DELETED_AT', 'GETDATE()', FALSE);
        }

        $this->tms_db->update($this->table, $updateData);

        $this->tms_db->trans_complete();

        // 5. Cek status transaksi
        if ($this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->messages = 'Gagal menghapus data: ' . (isset($err['message']) ? $err['message'] : 'Database Error');
            return false;
        }

        $this->messages = 'Data berhasil dihapus';
        return true;
    }

    /**
     * Source: MS_CONVERSION_RATE
     */
    public function is_duplicate_currency($currency, $exclude_id = null)
    {
        $currency = trim((string)$currency);
        if ($currency === '') return false;

        $del = $this->deleted_column();
        $params = [strtoupper($currency)];
        $sql = "SELECT COUNT(1) AS CNT FROM {$this->table}
            WHERE {$del} = 0
              AND UPPER(RTRIM(LTRIM(CON_CURRENCY))) = ?";
        if (!empty($exclude_id)) {
            $sql .= " AND CON_ID <> ?";
            $params[] = (int)$exclude_id;
        }
        $row = $this->tms_db->query($sql, $params)->row();
        return ($row && (int)$row->CNT > 0);
    }
}
