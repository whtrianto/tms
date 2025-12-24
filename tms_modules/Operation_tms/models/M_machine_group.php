<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model ini HANYA untuk mengelola item GRUP (IS_GROUP = 1)
 * di tabel MAC_ID
 * @property M_machines $machines (helper)
 */
class M_machine_group extends CI_Model
{
    private $table = 'MS_MACHINES';
    private $primary_key = 'MAC_ID';
    private $tms_db;
    public $messages;

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_NEW', TRUE);
        // Kita butuh M_machines untuk 'get_new_sequence' dan 'is_name_duplicate'
        $this->load->model('M_machines', 'machines');
    }

    /**
     * Mengambil daftar item yang merupakan GRUP (IS_GROUP = 1)
     */
    public function get_data_master_groups()
    {
        $tbl_ops = 'MS_OPERATION';
        // Gunakan parameter FALSE di select agar CI tidak otomatis menambah backtick yang kadang merusak query SQL Server
        $this->tms_db->select("M.*, O.OP_NAME", FALSE);
        $this->tms_db->from("{$this->table} M");
        $this->tms_db->join("{$tbl_ops} O", "O.OP_ID = M.MAC_OP_ID", 'left');
        $this->tms_db->where('M.IS_DELETED', 0);
        $this->tms_db->where('M.MAC_IS_GROUP', 1);
        $this->tms_db->order_by('M.MAC_NAME', 'ASC');

        $query = $this->tms_db->get();
        return $query->result_array();
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
            ->where('MAC_IS_GROUP', 1)
            ->get($this->table)
            ->row_array();
    }

    /* ===================== MUTATORS ===================== */

    public function add_data($actor = 'SYSTEM')
    {
        $machine_name = trim((string)$this->input->post('machine_name'));
        $operation_id = (int)$this->input->post('operation_id');

        if ($machine_name === '') {
            $this->messages = "Group Name tidak boleh kosong.";
            return FALSE;
        }
        if ($this->machines->is_name_duplicate($machine_name)) {
            $this->messages = "Machine/Group dengan nama tersebut sudah ada (aktif).";
            return FALSE;
        }

        $data = [
            // 'MAC_ID'        => $this->machines->get_new_sequence(),
            'MAC_NAME'      => $machine_name,
            'MAC_OP_ID'      => $operation_id,
            'MAC_IS_GROUP'          => 1,
            'MAC_CHARGE_RATE'       => NULL,
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
            'MAC_NAME' => $machine_name,
            'MAC_OP_ID' => $operation_id,
            'MAC_IS_GROUP'     => 1, // Pastikan tetap grup           
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
