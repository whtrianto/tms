<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_work_activity extends CI_Model
{
    private $table = 'TMS_M_WORK_ACTIVITY';
    private $primary_key = 'WORK_ACTIVITY_ID';
    private $tms_db;
    public $messages;

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_db', TRUE);
    }

    public function get_data_master_work_activity()
    {
        return $this->tms_db
            ->where('IS_DELETED', 0)
            ->or_where('IS_DELETED', NULL)
            ->order_by('WORK_ACTIVITY_NAME', 'ASC')
            ->get($this->table)
            ->result_array();
    }

    public function get_data_master_work_activity_by_id($id)
    {
        $id = (int)$id;
        return $this->tms_db
            ->where($this->primary_key, $id)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }


    public function is_duplicate($name, $exclude_id = null)
    {
        $name = trim((string)$name);
        if ($name === '') return false;

        $this->tms_db->where('LOWER(WORK_ACTIVITY_NAME)', strtolower($name));
        $this->tms_db->where_in('IS_DELETED', [0, NULL]); 
        if ($exclude_id !== null) {
            $this->tms_db->where($this->primary_key . ' <>', (int)$exclude_id);
        }
        $count = $this->tms_db->count_all_results($this->table);
        return $count > 0;
    }

    public function get_new_sequence()
    {
        $row = $this->tms_db->select_max($this->primary_key)->get($this->table)->row_array();
        return isset($row[$this->primary_key]) ? ((int)$row[$this->primary_key] + 1) : 1;
    }


    public function add_data($actor = 'SYSTEM')
    {
        $name = trim((string)$this->input->post('work_activity_name'));
        $desc = trim((string)$this->input->post('work_activity_desc'));

        if ($name === '') {
            $this->messages = "Work Activity Name tidak boleh kosong.";
            return FALSE;
        }
        if ($this->is_duplicate($name)) {
            $this->messages = "Nama Work AActivity tersebut sudah ada (aktif).";
            return FALSE;
        }

        $data = [
            'WORK_ACTIVITY_ID'      => $this->get_new_sequence(),
            'WORK_ACTIVITY_NAME'    => $name,
            'WORK_ACTIVITY_DESC'    => $desc ?: NULL,
            'IS_DELETED'            => 0
        ];

        $this->tms_db->trans_start();
        $this->tms_db->insert($this->table, $data);
        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this ->messages = "Data Work Activity berhasil ditambahkan.";
            return TRUE;
        }
        $err = $this->tms_db->error();
        $this ->messages = "Gagal menambahkan data. {$err['message']}";
        return FALSE;
    }

    public function update_by_id($id, array $data, $actor = 'SYSTEM')
    {
        $id = (int)$id;
        if ($id <= 0) {
            $this->messages = 'Work Activity id tidak valid.';
            return false;
        }
        
        $this->tms_db->trans_begin();
        $ok = $this->tms_db->where($this->primary_key, $id)
            ->update($this->table, $data);

        if ($this->tms_db->trans_status() === FALSE || $ok === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this ->messages = 'Gagal mengubah data' . ($err['message'] ? ': ' . $err['message'] : '');
            return false;
        }

        $this ->tms_db->trans_commit();
        $this ->messages = 'Data berhasil diubah';
        return true;
    }

    public function delete_data($id, $actor = 'SYSTEM')
    {
        $id = (int)$id;

        $row = $this->get_data_master_work_activity_by_id($id);
        if (!$row) {
            $this ->messages = 'Data tidak ditemukan';
            return FALSE;
        }
        if (!empty($row['IS_DELETED'])) {
            $this ->messages = 'Data sudah dihapus.';
            return FALSE;
        }

        $this->tms_db->trans_begin();
        $ok = $this->tms_db
            ->set('IS_DELETED', 1)
            ->set('DELETED_AT', 'GETDATE()', false) // SQL Server
            ->set('DELETED_BY', $actor)
            ->where($this ->primary_key, $id)
            ->update($this->table);

        if (!$ok || $this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this ->messages = 'Gagal menghapus data' . ($err['message'] ? ': ' . $err['message'] : '');
            return FALSE;
        }

        $this->tms_db->trans_commit();
        $this ->messages = 'Data berhasil dihapus';
        return TRUE;
    }
}