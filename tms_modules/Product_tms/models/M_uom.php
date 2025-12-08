<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_uom extends CI_Model
{
    private $table = 'TMS_M_UOM';
    private $primary_key = 'UOM_ID';
    private $tms_db;
    public $messages;

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_db', TRUE);
    }

    public function get_active()
    {
        return $this->tms_db
            ->select('UOM_ID, UOM_NAME')
            ->from($this->table)
            ->where('IS_DELETED', 0)
            ->order_by('UOM_NAME')
            ->get()
            ->result_array();
    }

    public function get_data_master_uom()
    {
        return $this->tms_db
            ->where('IS_DELETED', 0)   // hanya aktif
            ->order_by('UOM_ID', 'ASC')
            ->get($this->table)
            ->result_array();
    }

    public function get_data_master_uom_by_id($uom_id)
    {
        $uom_id = (int)$uom_id;
        $this->tms_db->reset_query();

        return $this->tms_db
            ->where('UOM_ID', $uom_id)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    public function get_data_master_uom_by_name($uom_name, $only_active = true)
    {
        $uom_name = trim((string)$uom_name);
        if ($uom_name === '') return null;

        // Case-insensitive: gunakan LOWER
        if ($only_active) {
            $sql = "SELECT TOP 1 * FROM {$this->table} WHERE LOWER(UOM_NAME) = ? AND IS_DELETED = 0";
            $params = [strtolower($uom_name)];
        } else {
            $sql = "SELECT TOP 1 * FROM {$this->table} WHERE LOWER(UOM_NAME) = ?";
            $params = [strtolower($uom_name)];
        }

        $query = $this->tms_db->query($sql, $params);
        return $query->row_array();
    }

    public function is_duplicate($uom_name, $exclude_id = null)
    {
        $uom_name = trim((string)$uom_name);
        if ($uom_name === '') return false;

        if ($exclude_id === null) {
            $sql = "SELECT COUNT(1) AS cnt FROM {$this->table} WHERE LOWER(UOM_NAME) = ? AND IS_DELETED = 0";
            $r = $this->tms_db->query($sql, [strtolower($uom_name)])->row_array();
        } else {
            $sql = "SELECT COUNT(1) AS cnt FROM {$this->table} WHERE LOWER(UOM_NAME) = ? AND UOM_ID <> ? AND IS_DELETED = 0";
            $r = $this->tms_db->query($sql, [strtolower($uom_name), (int)$exclude_id])->row_array();
        }

        $cnt = isset($r['cnt']) ? (int)$r['cnt'] : 0;
        return $cnt > 0;
    }

    public function update_by_id($uom_id, array $data)
    {
        $uom_id = (int)$uom_id;
        if ($uom_id <= 0) {
            $this->messages = 'UOM ID tidak valid.';
            return false;
        }

        $this->tms_db->trans_begin();

        $ok = $this->tms_db->where('UOM_ID', $uom_id)
            ->update($this->table, $data);

        if ($this->tms_db->trans_status() === FALSE || $ok === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal mengubah data UoM' . ($err['message'] ? ': ' . $err['message'] : '');
            return false;
        }

        $this->tms_db->trans_commit();
        $this->messages = 'Data UoM berhasil diubah';
        return true;
    }

    public function get_data_master_uom_by_desc($line_desc)
    {
        return $this->tms_db->where('UOM_DESC', $line_desc)
            ->get($this->table)->row_array();
    }

    /** Ambil ID baru (karena kolom bukan identity). */
    public function get_new_sequence()
    {
        $row = $this->tms_db->select_max('UOM_ID')->get($this->table)->row_array();
        return isset($row['UOM_ID']) ? ((int)$row['UOM_ID'] + 1) : 1;
    }

    /* ===================== MUTATORS ===================== */

    public function add_data()
    {
        $uom_name = trim((string)$this->input->post('uom_name'));
        $uom_desc = trim((string)$this->input->post('uom_desc'));

        if ($uom_name === '') {
            $this->messages = "Nama UoM tidak boleh kosong.";
            return FALSE;
        }

        // Cek nama unik hanya pada baris aktif
        if ($this->is_duplicate($uom_name)) {
            $this->messages = "UoM dengan nama tersebut sudah ada (aktif).";
            return FALSE;
        }

        $data = [
            'UOM_ID'   => $this->get_new_sequence(),
            'UOM_NAME' => $uom_name,
            'UOM_DESC' => $uom_desc,
            'IS_DELETED' => 0
        ];

        $this->tms_db->trans_start();
        $this->tms_db->insert($this->table, $data);
        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = "Data UoM berhasil ditambahkan.";
            return TRUE;
        }
        $err = $this->tms_db->error();
        $this->messages = "Gagal menambahkan data UoM. {$err['message']}";
        return FALSE;
    }

    public function edit_data()
    {
        $uom_id   = (int)$this->input->post('uom_id');
        $uom_name = trim((string)$this->input->post('uom_name'));
        $uom_desc = trim((string)$this->input->post('uom_desc'));

        $current = $this->get_data_master_uom_by_id($uom_id);
        if (!$current) {
            $this->messages = 'Data UoM tidak ditemukan';
            return FALSE;
        }

        // Nama harus unik di baris lain yang aktif
        if ($this->is_duplicate($uom_name, $uom_id)) {
            $this->messages = 'Nama UoM sudah digunakan oleh data lain (aktif)';
            return FALSE;
        }

        if ($uom_name === '') {
            $this->messages = "Nama UoM tidak boleh kosong.";
            return FALSE;
        }

        // Nama harus unik di baris lain (case-insensitive)
        $sql = "SELECT COUNT(1) AS cnt FROM {$this->table} WHERE LOWER(UOM_NAME) = ? AND UOM_ID <> ?";
        $r = $this->tms_db->query($sql, [strtolower($uom_name), $uom_id])->row_array();

        if ($this->is_duplicate($uom_name, $uom_id)) {
            $this->messages = 'Nama UoM sudah digunakan oleh data lain (aktif)';
            return FALSE;
        }

        $data = ['UOM_NAME' => $uom_name, 'UOM_DESC' => $uom_desc];

        $this->tms_db->where('UOM_ID', $uom_id)->update($this->table, $data);
        if ($this->tms_db->affected_rows() >= 0) {
            $this->messages = 'Data UoM berhasil diubah';
            return TRUE;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal mengubah data UoM. ' . $err['message'];
        return FALSE;
    }

    public function delete_data($uom_id)
    {
        $uom_id = (int)$uom_id;

        $row = $this->get_data_master_uom_by_id($uom_id);
        if (!$row) {
            $this->messages = 'Data UoM tidak ditemukan';
            return FALSE;
        }
        if (!empty($row['IS_DELETED'])) {
            $this->messages = 'Data UoM sudah dihapus.';
            return FALSE;
        }

        $deletedBy = 'SYSTEM';
        if (isset($this->session) && method_exists($this->session, 'userdata')) {
            $u = $this->session->userdata('username');
            if (!empty($u)) $deletedBy = $u;
        }

        $this->tms_db->trans_begin();

        $ok = $this->tms_db
            ->set('IS_DELETED', 1)
            ->set('DELETED_AT', 'GETDATE()', false) // SQL Server
            ->set('DELETED_BY', $deletedBy)
            ->where('UOM_ID', $uom_id)
            ->update($this->table);

        if (!$ok || $this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal menghapus data UoM' . ($err['message'] ? ': ' . $err['message'] : '');
            return FALSE;
        }

        $this->tms_db->trans_commit();
        $this->messages = 'Data UoM berhasil dihapus';
        return TRUE;
    }

    public function get_by_id($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE IS_DELETED = 0 AND " . $this->primary_key . " = ?";
        return $this->tms_db->query($sql, array((int)$id))->row_array();
    }
}
