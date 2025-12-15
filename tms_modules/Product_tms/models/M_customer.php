<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_customer extends CI_Model
{
    private $table = 'TMS_NEW.dbo.TMS_M_CUSTOMER';
    private $primary_key = 'CUSTOMER_ID';
    /** @var CI_DB_sqlsrv_driver */
    public $tms_db;
    public $messages;

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_db', TRUE);
    }

    public function get_active()
    {
        return $this->tms_db
            ->select('CUSTOMER_ID, CUSTOMER_NAME')
            ->from($this->table)
            ->where('IS_DELETED', 0)
            ->order_by('CUSTOMER_NAME')
            ->get()
            ->result_array();
    }

    public function get_data_master_customer()
    {
        return $this->tms_db
            ->where('IS_DELETED', 0)
            ->order_by('CUSTOMER_ID', 'ASC')
            ->get($this->table)
            ->result_array();
    }

    public function get_data_master_customer_by_id($id)
    {
        $id = (int)$id;
        $this->tms_db->reset_query();
        return $this->tms_db
            ->where('CUSTOMER_ID', $id)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    public function get_data_master_customer_by_name($name)
    {
        $name = trim((string)$name);
        if ($name === '') return null;

        // case-insensitive match, only active (IS_DELETED = 0)
        $sql = "SELECT TOP 1 * FROM {$this->table} WHERE LOWER(CUSTOMER_NAME) = ? AND IS_DELETED = 0";
        $query = $this->tms_db->query($sql, [strtolower($name)]);
        return $query->row_array();
    }

    public function get_new_sequence()
    {
        $row = $this->tms_db->select_max('CUSTOMER_ID')->get($this->table)->row_array();
        return isset($row['CUSTOMER_ID']) ? ((int)$row['CUSTOMER_ID'] + 1) : 1;
    }

    /* MUTATORS */

    public function add_data()
    {
        $name = trim((string)$this->input->post('customer_name'));
        if ($name === '') {
            $this->messages = "Nama Customer tidak boleh kosong.";
            return FALSE;
        }

        // unique only among active (IS_DELETED = 0)
        if ($this->get_data_master_customer_by_name($name)) {
            $this->messages = "Customer dengan nama tersebut sudah ada.";
            return FALSE;
        }

        $data = [
            'CUSTOMER_ID'   => $this->get_new_sequence(),
            'CUSTOMER_NAME' => $name,
            'IS_DELETED'    => 0
        ];

        $this->tms_db->trans_start();
        $this->tms_db->insert($this->table, $data);
        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = "Data Customer berhasil ditambahkan.";
            return TRUE;
        }
        $err = $this->tms_db->error();
        $this->messages = "Gagal menambahkan data Customer. {$err['message']}";
        return FALSE;
    }

    public function edit_data()
    {
        $id   = (int)$this->input->post('customer_id');
        $name = trim((string)$this->input->post('customer_name'));

        $current = $this->get_data_master_customer_by_id($id);
        if (!$current) {
            $this->messages = 'Data Customer tidak ditemukan';
            return FALSE;
        }

        if ($name === '') {
            $this->messages = "Nama Customer tidak boleh kosong.";
            return FALSE;
        }

        // check duplicate among active rows excluding current (case-insensitive)
        $sql = "SELECT COUNT(1) AS cnt FROM {$this->table} WHERE LOWER(CUSTOMER_NAME) = ? AND CUSTOMER_ID <> ? AND IS_DELETED = 0";
        $r = $this->tms_db->query($sql, [strtolower($name), $id])->row_array();
        $dup = isset($r['cnt']) ? (int)$r['cnt'] : 0;

        if ($dup > 0) {
            $this->messages = 'Nama Customer sudah digunakan oleh data lain';
            return FALSE;
        }

        $data = ['CUSTOMER_NAME' => $name];

        $this->tms_db->where('CUSTOMER_ID', $id)->update($this->table, $data);
        if ($this->tms_db->affected_rows() >= 0) {
            $this->messages = 'Data Customer berhasil diubah';
            return TRUE;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal mengubah data Customer. ' . $err['message'];
        return FALSE;
    }

    public function delete_data($id)
    {
        $id = (int)$id;
        $row = $this->get_data_master_customer_by_id($id);
        if (!$row) {
            $this->messages = 'Data Customer tidak ditemukan';
            return FALSE;
        }
        if (!empty($row['IS_DELETED'])) {
            $this->messages = 'Data Customer sudah dihapus.';
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
            ->set('DELETED_AT', 'GETDATE()', false)
            ->set('DELETED_BY', $deletedBy)
            ->where('CUSTOMER_ID', $id)
            ->update($this->table);

        if (!$ok || $this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal menghapus data Customer' . ($err['message'] ? ': ' . $err['message'] : '');
            return FALSE;
        }

        $this->tms_db->trans_commit();
        $this->messages = 'Data Customer berhasil dihapus';
        return TRUE;
    }

    public function get_by_id($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE IS_DELETED = 0 AND " . $this->primary_key . " = ?";
        return $this->tms_db->query($sql, array((int)$id))->row_array();
    }
}
