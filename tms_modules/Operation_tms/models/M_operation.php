<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_operation extends CI_Model
{
    private $table = 'MS_OPERATION';
    private $primary_key = 'OP_ID';
    private $tms_db;
    public $messages;

    public function __construct()
    {
        parent::__construct();
        // Asumsi DB Anda bernama 'tms_db' di config/database.php
        $this->tms_db = $this->load->database('tms_NEW', TRUE);
    }

    /**
     * Mengambil semua operation yang aktif (IS_DELETED = 0)
     */
    public function get_data_master_operation()
    {
        return $this->tms_db
            ->where('IS_DELETED', 0) // hanya yang aktif
            ->order_by('OP_NAME', 'ASC') // Urutkan berdasarkan nama
            ->get($this->table)
            ->result_array();
    }

    /**
     * Mengambil satu baris operation berdasarkan ID
     */
    public function get_data_master_operation_by_id($operation_id)
    {
        $operation_id = (int)$operation_id;
        $this->tms_db->reset_query();

        return $this->tms_db
            ->where($this->primary_key, $operation_id)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    /**
     * Mengambil satu baris operation berdasarkan NAMA (case-insensitive)
     */
    public function get_data_master_operation_by_name($operation_name, $only_active = true)
    {
        $operation_name = trim((string)$operation_name);
        if ($operation_name === '') return null;

        // Case-insensitive: gunakan LOWER
        if ($only_active) {
            $sql = "SELECT TOP 1 * FROM {$this->table} WHERE LOWER(OP_NAME) = ? AND IS_DELETED = 0";
            $params = [strtolower($operation_name)];
        } else {
            $sql = "SELECT TOP 1 * FROM {$this->table} WHERE LOWER(OP_NAME) = ?";
            $params = [strtolower($operation_name)];
        }

        $query = $this->tms_db->query($sql, $params);
        return $query->row_array();
    }

    /**
     * Cek duplikat nama operation (case-insensitive)
     */
    public function is_duplicate($operation_name, $exclude_id = null)
    {
        $operation_name = trim((string)$operation_name);
        if ($operation_name === '') return false;

        if ($exclude_id === null) {
            $sql = "SELECT COUNT(1) AS cnt FROM {$this->table} WHERE LOWER(OP_NAME) = ? AND IS_DELETED = 0";
            $r = $this->tms_db->query($sql, [strtolower($operation_name)])->row_array();
        } else {
            $sql = "SELECT COUNT(1) AS cnt FROM {$this->table} WHERE LOWER(OP_NAME) = ? AND {$this->primary_key} <> ? AND IS_DELETED = 0";
            $r = $this->tms_db->query($sql, [strtolower($operation_name), (int)$exclude_id])->row_array();
        }

        $cnt = isset($r['cnt']) ? (int)$r['cnt'] : 0;
        return $cnt > 0;
    }

    /* ===================== MUTATORS (CREATE, UPDATE, DELETE) ===================== */

    /**
     * Tambah data baru
     */
    public function add_data()
    {
        $operation_name = trim((string)$this->input->post('operation_name'));

        if ($operation_name === '') {
            $this->messages = "Operation Name tidak boleh kosong.";
            return FALSE;
        }

        // Cek nama unik hanya pada baris aktif
        if ($this->is_duplicate($operation_name)) {
            $this->messages = "Operation dengan nama tersebut sudah ada (aktif).";
            return FALSE;
        }

        $data = [
            'OP_NAME' => $operation_name,
            'IS_DELETED'     => 0
        ];

        $this->tms_db->trans_start();
        $this->tms_db->insert($this->table, $data);
        $new_id = $this->tms_db->insert_id();
        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = "Data Operation berhasil ditambahkan.";
            return $new_id;
        }
        $err = $this->tms_db->error();
        $this->messages = "Gagal menambahkan data Operation. {$err['message']}";
        return FALSE;
    }

    /**
     * Update data berdasarkan ID
     */
    public function update_by_id($operation_id, array $data)
    {
        $operation_id = (int)$operation_id;
        if ($operation_id <= 0) {
            $this->messages = 'Operation ID tidak valid.';
            return false;
        }

        $this->tms_db->trans_begin();

        $ok = $this->tms_db->where($this->primary_key, $operation_id)
            ->update($this->table, $data);

        if ($this->tms_db->trans_status() === FALSE || $ok === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal mengubah data Operation' . ($err['message'] ? ': ' . $err['message'] : '');
            return false;
        }

        $this->tms_db->trans_commit();
        $this->messages = 'Data Operation berhasil diubah';
        return true;
    }

    /**
     * Soft delete data
     */
    public function delete_data($operation_id)
    {
        $operation_id = (int)$operation_id;

        $row = $this->get_data_master_operation_by_id($operation_id);
        if (!$row) {
            $this->messages = 'Data Operation tidak ditemukan';
            return FALSE;
        }
        if (!empty($row['IS_DELETED'])) {
            $this->messages = 'Data Operation sudah dihapus.';
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
            ->set('DELETED_AT', 'GETDATE()', false) // SQL Server function
            ->set('DELETED_BY', $deletedBy)
            ->where($this->primary_key, $operation_id)
            ->update($this->table);

        if (!$ok || $this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal menghapus data Operation' . ($err['message'] ? ': ' . $err['message'] : '');
            return FALSE;
        }

        $this->tms_db->trans_commit();
        $this->messages = 'Data Operation berhasil dihapus';
        return TRUE;
    }
}
