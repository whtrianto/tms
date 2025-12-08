<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model ini HANYA untuk mengelola item GRUP (IS_GROUP = 1)
 * di tabel TMS_M_MACHINES
 * @property M_machines $machines (helper)
 */
class M_machine_group extends CI_Model
{
    private $table = 'TMS_M_MACHINES';
    private $primary_key = 'MACHINE_ID';
    private $tms_db;
    public $messages;

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_db', TRUE);
        // Kita butuh M_machines untuk 'get_new_sequence' dan 'is_name_duplicate'
        $this->load->model('M_machines', 'machines');
    }

    /**
     * Mengambil daftar item yang merupakan GRUP (IS_GROUP = 1)
     */
    public function get_data_master_groups()
    {
        $tbl_ops = 'TMS_M_OPERATION';
        return $this->tms_db
            ->select("M.*, O.OPERATION_NAME")
            ->from("{$this->table} M")
            ->join("{$tbl_ops} O", "O.OPERATION_ID = M.OPERATION_ID", 'left')
            ->where('M.IS_DELETED', 0)
            ->where('M.IS_GROUP', 1) // <-- HANYA GRUP
            ->order_by('M.MACHINE_NAME', 'ASC')
            ->get()
            ->result_array();
    }
    
    /**
     * Ambil data dropdown operation
     */
    public function get_all_operations()
    {
        // Panggil ulang fungsi dari M_machines
        if (!method_exists($this->machines, 'get_all_operations')) {
            $this->load->model('M_operation', 'operation');
            return $this->operation->get_data_master_operation();
        }
        return $this->machines->get_all_operations();
    }
    
    /**
     * Ambil detail 1 grup (untuk edit)
     */
    public function get_data_master_group_by_id($id)
    {
        return $this->tms_db
            ->where($this->primary_key, (int)$id)
            ->where('IS_GROUP', 1)
            ->get($this->table)
            ->row_array();
    }

    /* ===================== MUTATORS ===================== */

    public function add_data($actor = 'SYSTEM')
    {
        $machine_name = trim((string)$this->input->post('machine_name'));
        $operation_id = (int)$this->input->post('operation_id') ?: NULL;

        if ($machine_name === '') {
            $this->messages = "Group Name tidak boleh kosong.";
            return FALSE;
        }
        if ($this->machines->is_name_duplicate($machine_name)) {
            $this->messages = "Machine/Group dengan nama tersebut sudah ada (aktif).";
            return FALSE;
        }

        $data = [
            'MACHINE_ID'        => $this->machines->get_new_sequence(),
            'MACHINE_NAME'      => $machine_name,
            'OPERATION_ID'      => $operation_id,
            'IS_GROUP'          => 1, 
            'PARENT_ID'         => NULL, 
            'CHARGE_RATE'       => NULL, 
            'IS_DELETED'        => 0,
            'IS_ACTIVE'         => 1,
            'CREATED_AT'        => date('Y-m-d H:i:s'),
            'CREATED_BY'        => $actor
        ];

        $this->tms_db->trans_start();
        $this->tms_db->insert($this->table, $data);
        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = "Data Machine Group berhasil ditambahkan.";
            return TRUE;
        }
        $err = $this->tms_db->error();
        $this->messages = "Gagal menambahkan data Group. {$err['message']}";
        return FALSE;
    }

    public function update_data($machine_id, $actor = 'SYSTEM')
    {
        $machine_id   = (int)$machine_id;
        $machine_name = trim((string)$this->input->post('machine_name'));
        $operation_id = (int)$this->input->post('operation_id') ?: NULL;

        if ($machine_name === '') {
            $this->messages = "Group Name tidak boleh kosong.";
            return FALSE;
        }
        if ($this->machines->is_name_duplicate($machine_name, $machine_id)) {
            $this->messages = "Machine/Group dengan nama tersebut sudah ada (aktif).";
            return FALSE;
        }

        $dataUpdate = [
            'MACHINE_NAME' => $machine_name,
            'OPERATION_ID' => $operation_id,
            'IS_GROUP'     => 1, // Pastikan tetap grup
            'PARENT_ID'    => NULL,
            'UPDATED_AT'   => date('Y-m-d H:i:s'),
            'UPDATED_BY'   => $actor
        ];

        $this->tms_db->trans_start();
        $this->tms_db->where($this->primary_key, $machine_id)->update($this->table, $dataUpdate);
        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = "Data Machine Group berhasil diubah.";
            return TRUE;
        }
        $err = $this->tms_db->error();
        $this->messages = "Gagal mengubah data Group. {$err['message']}";
        return FALSE;
    }

    public function delete_data($machine_id, $actor = 'SYSTEM')
    {
        return $this->machines->delete_data($machine_id, $actor);
    }
    

    public function is_parent($machine_id)
    {
        return $this->machines->is_parent($machine_id);
    }
}