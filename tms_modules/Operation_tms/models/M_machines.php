<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_machines extends CI_Model
{
    private $table = 'MS_MACHINES';
    private $table_group = 'MS_MACHINE_MEMBERS';
    private $primary_key = 'MAC_ID';
    private $tms_db;
    public $messages;

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_NEW', TRUE);
    }

    /**
     * Helper untuk ID baru (digunakan oleh 2 tabel)
     */
    public function get_new_sequence($table_name, $pk_field)
    {
        $row = $this->tms_db->select_max($pk_field)->get($table_name)->row_array();
        return isset($row[$pk_field]) ? ((int)$row[$pk_field] + 1) : 1;
    }

    /**
     * Mengambil semua machine yang BUKAN GRUP (IS_GROUP = 0)
     * Ini adalah query yang GAGAL tadi, sekarang sudah diperbaiki.
     */
    public function get_data_master_machines()
    {
        $tbl_ops   = 'MS_OPERATION';

        return $this->tms_db
            ->select("
                M.*, 
                O.OP_NAME,
                /* Ambil nama Grup dari Parent */
                (SELECT TOP 1 G.MAC_NAME 
                 FROM {$this->table_group} MG 
                 JOIN {$this->table} G ON G.MAC_ID = MG.MACM_PARENT_ID
                 WHERE MG.MACM_CHILD_ID = M.MAC_ID AND MG.IS_DELETED = 0
                ) AS MACHINES_GROUP_NAME
            ")
            ->from("{$this->table} M")
            ->join("{$tbl_ops} O", "O.OP_ID = M.MAC_OP_ID", 'left')
            ->where('M.IS_DELETED', 0)
            ->where('M.MAC_IS_GROUP', 0) // <-- Sesuai permintaan Anda
            ->order_by('M.MAC_NAME', 'ASC')
            ->get()
            ->result_array();
    }

    /**
     * Mengambil satu baris data (bisa machine atau group) berdasarkan ID
     */
    public function get_data_master_machine_by_id($machine_id)
    {
        $machine_id = (int)$machine_id;
        return $this->tms_db
            ->where($this->primary_key, $machine_id)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    /**
     * Helper untuk form EDIT: Mencari tahu siapa PARENT dari machine ini
     */
    public function get_parent_id_for_machine($machine_id)
    {
        $row = $this->tms_db
            ->select('MACM_PARENT_ID')
            ->where('MACM_CHILD_ID', (int)$machine_id)
            ->where('IS_DELETED', 0)
            ->limit(1)
            ->get($this->table_group)
            ->row_array();
        return $row ? (int)$row['MACM_PARENT_ID'] : null;
    }

    /**
     * Cek duplikat NAMA (case-insensitive)
     */
    public function is_name_duplicate($machine_name, $exclude_id = null)
    {
        $this->tms_db->where('LOWER(MAC_NAME)', strtolower(trim($machine_name)));
        $this->tms_db->where('IS_DELETED', 0);
        if ($exclude_id !== null) {
            $this->tms_db->where($this->primary_key . ' <>', (int)$exclude_id);
        }
        $count = $this->tms_db->count_all_results($this->table);
        return $count > 0;
    }

    /* ===================== DATA UNTUK DROPDOWN ===================== */

    /**
     * Mengambil semua item yang merupakan GRUP (IS_GROUP = 1)
     */
    public function get_all_machine_groups()
    {
        return $this->tms_db
            ->select('MAC_ID, MAC_NAME')
            ->where('IS_DELETED', 0)
            ->where('MAC_IS_GROUP', 1) // <-- PENTING
            ->order_by('MAC_NAME', 'ASC')
            ->get($this->table)
            ->result_array();
    }

    public function get_all_operations()
    {
        return $this->tms_db->where('IS_DELETED', 0)->order_by('OP_NAME', 'ASC')->get('MS_OPERATION')->result_array();
    }

    public function is_parent($machine_id)
    {
        $count = $this->tms_db
            ->where('MACM_PARENT_ID', (int)$machine_id)
            ->where('IS_DELETED', 0) // Hanya cek member yg masih aktif
            ->count_all_results($this->table_group); // $this->table_group adalah 'TMS_M_MACHINES_GROUP'

        return $count > 0;
    }
    /* ===================== MUTATORS (CREATE, UPDATE, DELETE) ===================== */

    public function add_data($actor = 'SYSTEM')
    {
        $machine_name = trim((string)$this->input->post('machine_name'));
        $is_group = (int)$this->input->post('is_group') === 1 ? 1 : 0;
        $parent_id = (int)$this->input->post('parent_id') ?: NULL;
        // $charge_rate = $this->input->post('charge_rate');

        if ($machine_name === '') {
            $this->messages = "Machine Name tidak boleh kosong.";
            return FALSE;
        }
        if ($this->is_name_duplicate($machine_name)) {
            $this->messages = "Machine/Group dengan nama tersebut sudah ada (aktif).";
            return FALSE;
        }

        $this->tms_db->trans_start();

        // 1. Insert ke tabel Master        
        $data_machine = [
            'MAC_NAME'       => $machine_name,
            'MAC_OP_ID'      => (int)$this->input->post('operation_id'),
            'MAC_IS_GROUP'   => $is_group,
            'IS_DELETED'     => 0,
            'IS_ACTIVE'      => 1,
            'CREATED_AT'     => date('Y-m-d H:i:s'),
            'CREATED_BY'     => $actor
        ];

        $this->tms_db->insert($this->table, $data_machine);
        $new_machine_id = $this->tms_db->insert_id();

        // 2. Jika ini MESIN (bukan grup), insert ke tabel mapping
        if ($is_group === 0 && $parent_id !== NULL) {
            // $new_map_id = $this->get_new_sequence($this->table_group, 'MACM_ID');
            $data_map = [
                // 'MACM_ID'  => $new_map_id,
                'MACM_PARENT_ID' => $parent_id,
                'MACM_CHILD_ID' => $new_machine_id,
                'IS_DELETED'         => 0
                // (kolom audit lain jika ada di tabel group)
            ];
            $this->tms_db->insert($this->table_group, $data_map);
        }

        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = "Data Machine berhasil ditambahkan.";
            return TRUE;
        }
        $err = $this->tms_db->error();
        $this->messages = "Gagal menambahkan data Machine. {$err['message']}";
        return FALSE;
    }

    public function update_by_id($machine_id, $data, $actor = 'SYSTEM')
    {
        $machine_id = (int)$machine_id;
        $is_group = (int)$this->input->post('is_group') === 1 ? 1 : 0;
        $parent_id = (int)$this->input->post('parent_id') ?: NULL;

        if ($machine_id <= 0) {
            $this->messages = 'Machine ID tidak valid.';
            return false;
        }

        // Tambahkan data audit ke data update
        $data['UPDATED_AT'] = date('Y-m-d H:i:s');
        $data['UPDATED_BY'] = $actor;
        $data['MAC_IS_GROUP']   = $is_group; // Pastikan IS_GROUP terupdate

        $this->tms_db->trans_begin();

        // 1. Update tabel Master
        $this->tms_db->where($this->primary_key, $machine_id)
            ->where('IS_DELETED', 0)
            ->update($this->table, $data);

        // 2. Update tabel Mapping (Hapus-lalu-Sisip ulang)

        // Hapus mapping lama untuk member ini
        $this->tms_db
            ->where('MACM_CHILD_ID', $machine_id)
            ->update($this->table_group, [
                'IS_DELETED' => 1,
                'DELETED_AT' => date('Y-m-d H:i:s'),
                'DELETED_BY' => $actor
            ]);

        // Jika ini MESIN (bukan grup) DAN parent-nya dipilih
        if ($is_group === 0 && $parent_id !== NULL) {
            // Sisipkan mapping baru
            // $new_map_id = $this->get_new_sequence($this->table_group, 'MACM_ID');
            $data_map = [
                //  
                'MACM_PARENT_ID' => $parent_id,
                'MACM_CHILD_ID' => $machine_id,
                'IS_DELETED'         => 0
            ];
            $this->tms_db->insert($this->table_group, $data_map);
        }

        if ($this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal mengubah data Machine' . ($err['message'] ? ': ' . $err['message'] : '');
            return false;
        }

        $this->tms_db->trans_commit();
        $this->messages = 'Data Machine berhasil diubah';
        return true;
    }

    public function delete_data($machine_id, $actor = 'SYSTEM')
    {
        $machine_id = (int)$machine_id;
        $row = $this->get_data_master_machine_by_id($machine_id);
        if (!$row) {
            $this->messages = 'Data Machine tidak ditemukan';
            return FALSE;
        }
        if (!empty($row['IS_DELETED'])) {
            $this->messages = 'Data Machine sudah dihapus.';
            return FALSE;
        }

        $this->tms_db->trans_begin();

        // 1. Soft delete dari tabel Master
        $ok_master = $this->tms_db
            ->set('IS_DELETED', 1)
            ->set('DELETED_AT', date('Y-m-d H:i:s'))
            ->set('DELETED_BY', $actor)
            ->where($this->primary_key, $machine_id)
            ->update($this->table);

        // 2. Soft delete dari tabel Group (baik sbg parent maupun member)
        // (Asumsi tabel group juga punya kolom audit)
        $audit_data = [
            'IS_DELETED' => 1,
            'DELETED_AT' => date('Y-m-d H:i:s'),
            'DELETED_BY' => $actor
        ];
        // Hapus jika dia adalah MEMBER
        $this->tms_db->where('MACM_CHILD_ID', $machine_id)->update($this->table_group, $audit_data);
        // Hapus jika dia adalah PARENT
        $this->tms_db->where('MACM_PARENT_ID', $machine_id)->update($this->table_group, $audit_data);

        if (!$ok_master || $this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal menghapus data Machine' . ($err['message'] ? ': ' . $err['message'] : '');
            return FALSE;
        }

        $this->tms_db->trans_commit();
        $this->messages = 'Data Machine berhasil dihapus';
        return TRUE;
    }
}
