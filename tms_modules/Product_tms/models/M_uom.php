<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_uom extends CI_Model
{
    private $table = 'MS_UOM';

    /** @var CI_DB_sqlsrv_driver */
    public $db_tms;

    public function __construct()
    {
        parent::__construct();
        $this->db_tms = $this->load->database('tms_NEW', TRUE);
    }

    public function get_active()
    {
        // Select kolom yang dibutuhkan saja
        $this->db_tms->select('UOM_ID, UOM_NAME, UOM_DESC');
        $this->db_tms->from($this->table);
        $this->db_tms->where('IS_DELETED', 0); // Hanya data aktif
        $this->db_tms->order_by('UOM_NAME', 'ASC');
        return $this->db_tms->get()->result_array();
    }

    public function get_by_id($id)
    {
        return $this->db_tms->get_where($this->table, [
            'UOM_ID' => (int)$id,
            'IS_DELETED' => 0 // Pastikan ID yang dicari belum dihapus
        ])->row_array();
    }

    public function is_duplicate($name, $exclude_id = null)
    {
        $name_norm = strtoupper(trim($name));
        $this->db_tms->where("UPPER(RTRIM(LTRIM(UOM_NAME)))", $name_norm);
        $this->db_tms->where('IS_DELETED', 0); // Hanya cek duplikasi di data aktif

        if (!empty($exclude_id)) {
            $this->db_tms->where('UOM_ID !=', (int)$exclude_id);
        }

        return $this->db_tms->count_all_results($this->table) > 0;
    }

    public function insert($data)
    {
        // Data yang akan diinsert
        $insert_data = [
            'UOM_NAME'   => strtoupper(trim($data['UOM_NAME'])),
            'UOM_DESC'   => isset($data['UOM_DESC']) ? $data['UOM_DESC'] : null,
            'IS_DELETED' => 0, // Explicitly set active
            'DELETED_AT' => null,
            'DELETED_BY' => null
        ];

        $this->db_tms->trans_begin();

        $this->db_tms->insert($this->table, $insert_data);

        if ($this->db_tms->trans_status() === FALSE) {
            $this->db_tms->trans_rollback();
            return 0;
        }

        // Ambil ID hasil IDENTITY
        $new_id = $this->db_tms->insert_id();

        $this->db_tms->trans_commit();
        return (int)$new_id;
    }

    public function update($id, $data)
    {
        $update_data = [
            'UOM_NAME' => strtoupper(trim($data['UOM_NAME'])),
            'UOM_DESC' => isset($data['UOM_DESC']) ? $data['UOM_DESC'] : null
        ];

        $this->db_tms->where('UOM_ID', (int)$id);
        $this->db_tms->where('IS_DELETED', 0);
        return $this->db_tms->update($this->table, $update_data);
    }

    public function soft_delete($id, $deleted_by = null)
    {
        $this->db_tms->set('IS_DELETED', 1);
        // FALSE di parameter ke-3 agar GETDATE() tidak di-escape sebagai string
        $this->db_tms->set('DELETED_AT', 'GETDATE()', FALSE);
        $this->db_tms->set('DELETED_BY', $deleted_by);

        $this->db_tms->where('UOM_ID', (int)$id);
        $this->db_tms->where('IS_DELETED', 0); // Safety check

        $this->db_tms->update($this->table);

        // Cek apakah ada baris yang terpengaruh (berarti sukses delete)
        return $this->db_tms->affected_rows() > 0;
    }
}
