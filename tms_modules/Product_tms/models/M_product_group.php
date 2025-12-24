<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_product_group extends CI_Model
{
    private $table = 'MS_PART_MEMBERS'; // Tabel Relasi
    private $product_table = 'MS_PARTS'; // Tabel Master Part

    /** @var CI_DB_sqlsrv_driver */
    public $db_tms;

    public $messages = '';

    public function __construct()
    {
        parent::__construct();
        $this->db_tms = $this->load->database('tms_NEW', TRUE);
    }

    /**
     * Ambil relasi aktif (PARTM_DATE_END IS NULL)
     * Menggunakan Query Builder
     */
    public function get_all($search = null)
    {
        $this->db_tms->select('
            G.PARTM_ID,
            G.PARTM_PARENT_ID,
            G.PARTM_CHILD_ID,
            G.PARTM_DATE_START,
            G.PATRM_DATE_END,
            PARENT.PART_NAME AS PARENT_NAME,
            PARENT.PART_DESC AS PARENT_DESC,
            CHILD.PART_NAME  AS CHILD_NAME,
            CHILD.PART_DESC  AS CHILD_DESC
        ');
        $this->db_tms->from($this->table . ' G');

        // Join ke Parent
        $this->db_tms->join($this->product_table . ' PARENT', 'PARENT.PART_ID = G.PARTM_PARENT_ID', 'LEFT');

        // Join ke Child
        $this->db_tms->join($this->product_table . ' CHILD', 'CHILD.PART_ID = G.PARTM_CHILD_ID', 'LEFT');

        // Filter Aktif (Typo DB: PATRM_DATE_END)
        $this->db_tms->where('G.PATRM_DATE_END IS NULL', null, false);

        if (!empty($search)) {
            $this->db_tms->group_start();
            $this->db_tms->like('PARENT.PART_NAME', $search);
            $this->db_tms->or_like('CHILD.PART_NAME', $search);
            $this->db_tms->group_end();
        }

        $this->db_tms->order_by('PARENT.PART_NAME', 'ASC');
        $this->db_tms->order_by('CHILD.PART_NAME', 'ASC');

        return $this->db_tms->get()->result_array();
    }

    /**
     * Ambil list product untuk dropdown
     */
    public function get_products($only_groups = false)
    {
        $this->db_tms->select('PART_ID, PART_NAME, PART_IS_GROUP');
        $this->db_tms->from($this->product_table);
        $this->db_tms->where('IS_DELETED', 0);

        if ($only_groups) {
            $this->db_tms->where('PART_IS_GROUP', 1);
        }

        $this->db_tms->order_by('PART_NAME', 'ASC');

        return $this->db_tms->get()->result_array();
    }

    public function get_relation_by_child($child_id)
    {
        return $this->db_tms->get_where($this->table, [
            'PARTM_CHILD_ID' => (int)$child_id,
            'PARTM_DATE_END' => null // Active only
        ])->row_array();
    }

    public function insert_relation($parent_id, $child_id)
    {
        $parent_id = (int)$parent_id;
        $child_id  = (int)$child_id;

        // Cek relasi aktif
        $sql = "SELECT COUNT(1) AS CNT FROM MS_PART_MEMBERS 
            WHERE PARTM_PARENT_ID = ? AND PARTM_CHILD_ID = ? AND PATRM_DATE_END IS NULL";
        $row = $this->db_tms->query($sql, array($parent_id, $child_id))->row();

        if ($row && (int)$row->CNT > 0) {
            $this->messages = 'Relasi sudah ada.';
            return false;
        }

        // Insert tanpa PARTM_ID
        $this->db_tms->set('PARTM_PARENT_ID', $parent_id);
        $this->db_tms->set('PARTM_CHILD_ID', $child_id);
        $this->db_tms->set('PARTM_DATE_START', 'GETDATE()', FALSE);
        $this->db_tms->set('IS_DELETED', 0);

        $ok = $this->db_tms->insert('MS_PART_MEMBERS');

        if ($ok) {
            $res = $this->db_tms->query("SELECT SCOPE_IDENTITY() AS last_id")->row();
            return (isset($res->last_id)) ? (int)$res->last_id : true;
        }

        return false;
    }

    public function update_relation($id, $parent_id, $child_id)
    {
        $id = (int)$id;
        $parent_id = (int)$parent_id;
        $child_id = (int)$child_id;

        if ($id <= 0) {
            $this->messages = 'ID relasi tidak valid.';
            return false;
        }

        if ($parent_id === $child_id) {
            $this->messages = 'Parent dan Child tidak boleh sama.';
            return false;
        }

        // Cek duplicate selain diri sendiri
        $this->db_tms->where('PARTM_PARENT_ID', $parent_id);
        $this->db_tms->where('PARTM_CHILD_ID', $child_id);
        $this->db_tms->where('PARTM_ID !=', $id);
        $this->db_tms->where('PATRM_DATE_END IS NULL', null, false);
        $cnt = $this->db_tms->count_all_results($this->table);

        if ($cnt > 0) {
            $this->messages = 'Relasi yang sama sudah ada.';
            return false;
        }

        $data = [
            'PARTM_PARENT_ID' => $parent_id,
            'PARTM_CHILD_ID'  => $child_id
        ];

        $this->db_tms->where('PARTM_ID', $id);
        $ok = $this->db_tms->update($this->table, $data);

        if ($ok) {
            $this->messages = 'Relasi berhasil diubah.';
            return true;
        } else {
            $this->messages = 'Gagal mengubah relasi.';
            return false;
        }
    }

    public function soft_delete($id)
    {
        $id = (int)$id;
        if ($id <= 0) return false;

        $this->db_tms->set('PARTM_DATE_END', 'GETDATE()', FALSE);
        $this->db_tms->where('PARTM_ID', $id);
        $this->db_tms->where('PATRM_DATE_END IS NULL', null, false);

        $ok = $this->db_tms->update($this->table);

        if ($ok) {
            $this->messages = 'Relasi berhasil dinonaktifkan.';
            return true;
        }
        $this->messages = 'Gagal menonaktifkan relasi.';
        return false;
    }

    // Dipakai saat delete product
    public function end_relation_by_child($child_id)
    {
        $this->db_tms->set('PARTM_DATE_END', 'GETDATE()', FALSE);
        $this->db_tms->where('PARTM_CHILD_ID', (int)$child_id);
        $this->db_tms->where('PATRM_DATE_END IS NULL', null, false);
        return $this->db_tms->update($this->table);
    }
}
