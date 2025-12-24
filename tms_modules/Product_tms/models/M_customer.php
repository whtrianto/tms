<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_customer extends CI_Model
{
    // Sesuaikan nama tabel
    private $table = 'MS_CUSTOMER';

    /** @var CI_DB_sqlsrv_driver */
    public $db_tms;

    public function __construct()
    {
        parent::__construct();
        $this->db_tms = $this->load->database('tms_NEW', TRUE);
    }

    /**
     * Ambil data aktif untuk List
     */
    public function get_active()
    {
        $this->db_tms->select('CUS_ID, CUS_NAME, CUS_ABBR');
        $this->db_tms->from($this->table);
        $this->db_tms->where('IS_DELETED', 0);
        $this->db_tms->order_by('CUS_NAME', 'ASC');
        return $this->db_tms->get()->result_array();
    }

    /**
     * Ambil by ID
     */
    public function get_by_id($id)
    {
        return $this->db_tms->get_where($this->table, [
            'CUS_ID' => (int)$id,
            'IS_DELETED' => 0
        ])->row_array();
    }

    /**
     * Cek Duplikasi Nama (Query Builder)
     */
    public function is_duplicate($name, $exclude_id = null)
    {
        $this->db_tms->where('CUS_NAME', strtoupper(trim($name)));
        $this->db_tms->where('IS_DELETED', 0);

        if (!empty($exclude_id)) {
            $this->db_tms->where('CUS_ID !=', (int)$exclude_id);
        }

        return $this->db_tms->count_all_results($this->table) > 0;
    }


    /**
     * Insert Data
     */
    public function insert($data)
    {
        // 1. Sanitize & Map Data
        $insert_data = [
            'CUS_NAME'   => strtoupper(trim($data['CUS_NAME'])),
            // CUS_ABBR optional, jika ada di array data maka dimasukkan
            'CUS_ABBR'   => isset($data['CUS_ABBR']) ? strtoupper(trim($data['CUS_ABBR'])) : null,
            'IS_DELETED' => 0
        ];

        $this->db_tms->trans_begin();

        $this->db_tms->insert($this->table, $insert_data);

        if ($this->db_tms->trans_status() === FALSE) {
            $this->db_tms->trans_rollback();
            return 0;
        }

        // Ambil ID hasil identity
        $new_id = $this->db_tms->insert_id();

        $this->db_tms->trans_commit();
        return (int)$new_id;
    }

    /**
     * Update Data
     */
    public function update($id, $data)
    {
        $update_data = [
            'CUS_NAME' => strtoupper(trim($data['CUS_NAME']))
        ];

        // Update Abbr jika dikirim
        if (isset($data['CUS_ABBR'])) {
            $update_data['CUS_ABBR'] = strtoupper(trim($data['CUS_ABBR']));
        }

        $this->db_tms->where('CUS_ID', (int)$id);
        $this->db_tms->where('IS_DELETED', 0);
        return $this->db_tms->update($this->table, $update_data);
    }

    /**
     * Soft Delete
     */
    public function soft_delete($id, $deleted_by = null)
    {
        $this->db_tms->set('IS_DELETED', 1);
        $this->db_tms->set('DELETED_AT', 'GETDATE()', FALSE);

        if ($deleted_by) {
            $this->db_tms->set('DELETED_BY', $deleted_by);
        }

        $this->db_tms->where('CUS_ID', (int)$id);
        $this->db_tms->where('IS_DELETED', 0);

        return $this->db_tms->update($this->table);
    }
}
