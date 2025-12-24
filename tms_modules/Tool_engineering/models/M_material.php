<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_uom $uom
 */
class M_material extends CI_Model
{
    private $table = 'MS_MATERIAL';
    private $primary_key = 'MAT_ID';
    private $tms_db;
    public $messages;

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_NEW', TRUE);
        $this->load->model('Product_tms/M_uom', 'uom');
    }

    public function get_data_master_materials()
    {
        return $this->tms_db
            ->select("MAT.*, UOM.UOM_NAME")
            ->from("{$this->table} MAT")
            ->join("MS_UOM UOM", "UOM.UOM_ID = MAT_UNIT", 'left')
            ->where('MAT.IS_DELETED', 0)
            ->order_by('MAT.MAT_NAME', 'ASC')
            ->get()
            ->result_array();
    }

    public function get_data_master_material_by_id($material_id)
    {
        $material_id = (int)$material_id;
        return $this->tms_db
            ->where($this->primary_key, $material_id)
            ->limit(1)
            ->get($this->table)
            ->row_array();
    }

    public function get_data_master_material_by_name($material_name, $only_active = true)
    {
        $material_name = trim((string)$material_name);
        if ($material_name === '') return null;

        if ($only_active) {
            $sql = "SELECT TOP 1 * FROM {$this->table} WHERE LOWER(MAT_NAME) = ? AND IS_DELETED = 0";
            $params = [strtolower($material_name)];
        } else {
            $sql = "SELECT TOP 1 * FROM {$this->table} WHERE LOWER(MAT_NAME) = ?";
            $params = [strtolower($material_name)];
        }
        $query = $this->tms_db->query($sql, $params);
        return $query->row_array();
    }

    public function is_duplicate($material_name, $exclude_id = null)
    {
        $material_name = trim((string)$material_name);
        if ($material_name === '') return false;

        $this->tms_db->where('LOWER(MAT_NAME)', strtolower($material_name));
        $this->tms_db->where('IS_DELETED', 0);
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


    public function get_all_uoms()
    {
        return $this->uom->get_active();
    }

    public function add_data($actor = 'SYSTEM')
    {
        $material_name = trim((string)$this->input->post('material_name'));
        $uom_id = (!empty($uom_id)) ? (int)$uom_id : NULL;

        if ($material_name === '') {
            $this->messages = "Material Name tidak boleh kosong.";
            return FALSE;
        }
        if ($this->is_duplicate($material_name)) {
            $this->messages = "Material dengan nama tersebut sudah ada (aktif).";
            return FALSE;
        }

        $data = [
            'MAT_NAME' => $material_name,
            'MAT_UNIT'        => $uom_id,
            'IS_DELETED'    => 0
        ];

        $this->tms_db->trans_start();
        $this->tms_db->insert($this->table, $data);
        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = "Data Material berhasil ditambahkan.";
            return TRUE;
        }
        $err = $this->tms_db->error();
        $this->messages = "Gagal menambahkan data Material. {$err['message']}";
        return FALSE;
    }

    public function update_by_id($material_id, array $data, $actor = 'SYSTEM')
    {
        $material_id = (int)$material_id;
        if ($material_id <= 0) {
            $this->messages = 'Material ID tidak valid.';
            return false;
        }

        $this->tms_db->trans_begin();
        $ok = $this->tms_db->where($this->primary_key, $material_id)
            ->update($this->table, $data);

        if ($this->tms_db->trans_status() === FALSE || $ok === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal mengubah data Material' . ($err['message'] ? ': ' . $err['message'] : '');
            return false;
        }

        $this->tms_db->trans_commit();
        $this->messages = 'Data Material berhasil diubah';
        return true;
    }

    public function delete_data($material_id, $actor = 'SYSTEM')
    {
        $material_id = (int)$material_id;

        $row = $this->get_data_master_material_by_id($material_id);
        if (!$row) {
            $this->messages = 'Data Material tidak ditemukan';
            return FALSE;
        }
        if (!empty($row['IS_DELETED'])) {
            $this->messages = 'Data Material sudah dihapus.';
            return FALSE;
        }

        $this->tms_db->trans_begin();
        $ok = $this->tms_db
            ->set('IS_DELETED', 1)
            ->set('DELETED_AT', 'GETDATE()', false)
            ->set('DELETED_BY', $actor)
            ->where($this->primary_key, $material_id)
            ->update($this->table);

        if (!$ok || $this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal menghapus data Material' . ($err['message'] ? ': ' . $err['message'] : '');
            return FALSE;
        }

        $this->tms_db->trans_commit();
        $this->messages = 'Data Material berhasil dihapus';
        return TRUE;
    }
}
