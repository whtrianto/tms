<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_product_group extends CI_Model
{
    private $table = 'TMS_DB.dbo.TMS_M_PRODUCT_GROUP';
    private $product_table = 'TMS_DB.dbo.TMS_M_PRODUCT';

    /** @var CI_DB_sqlsrv_driver */
    public $tms_db;

    // pesan terakhir
    public $messages = '';

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_db', TRUE);
    }

    /**
     * Ambil relasi aktif (end_date IS NULL)
     */
    public function get_all($search = null)
    {
        $params = array();

        $sql = "
        SELECT
            G.PRODUCT_GROUP_ID,
            G.PRODUCT_GROUP_PARENT_ID,
            G.PRODUCT_GROUP_CHILD_ID,
            G.PRODUCT_GROUP_START_DATE,
            G.PRODUCT_GROUP_END_DATE,
            PARENT.PRODUCT_NAME AS PARENT_NAME,
            PARENT.PRODUCT_DESC AS PARENT_DESC,
            CHILD.PRODUCT_NAME  AS CHILD_NAME,
            CHILD.PRODUCT_DESC  AS CHILD_DESC
        FROM {$this->table} G
        LEFT JOIN {$this->product_table} PARENT
               ON PARENT.PRODUCT_ID = G.PRODUCT_GROUP_PARENT_ID
        LEFT JOIN {$this->product_table} CHILD
               ON CHILD.PRODUCT_ID = G.PRODUCT_GROUP_CHILD_ID
        WHERE G.PRODUCT_GROUP_END_DATE IS NULL
    ";

        if (!empty($search)) {
            $sql .= " AND (PARENT.PRODUCT_NAME LIKE ? OR CHILD.PRODUCT_NAME LIKE ?)";
            $like = '%' . $search . '%';
            $params = array($like, $like);
        }

        $sql .= " ORDER BY PARENT.PRODUCT_NAME, CHILD.PRODUCT_NAME";

        return $this->tms_db->query($sql, $params)->result_array();
    }

    public function get_products($only_groups = false)
    {
        $sql = "SELECT PRODUCT_ID, PRODUCT_NAME, PRODUCT_IS_GROUP FROM {$this->product_table}
                WHERE IS_DELETED = 0";

        if ($only_groups) {
            $sql .= " AND PRODUCT_IS_GROUP = 1";
        }

        $sql .= " ORDER BY PRODUCT_NAME";

        return $this->tms_db->query($sql)->result_array();
    }

    public function get_relation_by_child($child_id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE PRODUCT_GROUP_CHILD_ID = ? AND PRODUCT_GROUP_END_DATE IS NULL";
        return $this->tms_db->query($sql, array((int)$child_id))->row_array();
    }

    public function insert_relation($parent_id, $child_id)
    {
        $parent_id = (int)$parent_id;
        $child_id  = (int)$child_id;

        if ($parent_id <= 0 || $child_id <= 0) {
            $this->messages = 'Parent/Child tidak valid.';
            return false;
        }
        if ($parent_id === $child_id) {
            $this->messages = 'Parent dan Child tidak boleh sama.';
            return false;
        }

        // cek apakah relation aktif sudah ada
        $sql = "SELECT COUNT(1) AS CNT FROM {$this->table}
            WHERE PRODUCT_GROUP_PARENT_ID = ? AND PRODUCT_GROUP_CHILD_ID = ? AND PRODUCT_GROUP_END_DATE IS NULL";
        $row = $this->tms_db->query($sql, array($parent_id, $child_id))->row();
        if ($row && (int)$row->CNT > 0) {
            $this->messages = 'Relasi sudah ada.';
            return false;
        }

        $sql = "INSERT INTO {$this->table}
            (PRODUCT_GROUP_PARENT_ID, PRODUCT_GROUP_CHILD_ID, PRODUCT_GROUP_START_DATE, PRODUCT_GROUP_END_DATE)
            VALUES (?, ?, GETDATE(), NULL)";
        $this->tms_db->query($sql, array($parent_id, $child_id));

        $insert_id = $this->tms_db->insert_id();
        if (empty($insert_id)) {
            $r = $this->tms_db->query("SELECT SCOPE_IDENTITY() AS id")->row();
            $insert_id = isset($r->id) ? (int)$r->id : 0;
        }

        if ($insert_id) {
            $this->messages = 'Relasi berhasil ditambahkan.';
            return (int)$insert_id;
        }

        // fallback: jika driver tms_db tidak mengembalikan insert_id, kembalikan true dan pesan
        $this->messages = 'Relasi berhasil ditambahkan.';
        return true;
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

        // Validasi sederhana: parent != child
        if ($parent_id === $child_id) {
            $this->messages = 'Parent dan Child tidak boleh sama.';
            return false;
        }

        // Bisa tambah cek duplicate relasi aktif yang sama (kecuali id sendiri)
        $sql = "SELECT COUNT(1) AS CNT FROM {$this->table}
            WHERE PRODUCT_GROUP_PARENT_ID = ? AND PRODUCT_GROUP_CHILD_ID = ? AND PRODUCT_GROUP_ID <> ? AND PRODUCT_GROUP_END_DATE IS NULL";
        $r = $this->tms_db->query($sql, array($parent_id, $child_id, $id))->row();
        if ($r && (int)$r->CNT > 0) {
            $this->messages = 'Relasi yang sama sudah ada.';
            return false;
        }

        $sql = "UPDATE {$this->table}
               SET PRODUCT_GROUP_PARENT_ID = ?, PRODUCT_GROUP_CHILD_ID = ?
             WHERE PRODUCT_GROUP_ID = ?";
        $ok = $this->tms_db->query($sql, array($parent_id, $child_id, $id));

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
        if ($id <= 0) {
            $this->messages = 'ID relasi tidak valid.';
            return false;
        }
        $sql = "UPDATE {$this->table}
               SET PRODUCT_GROUP_END_DATE = GETDATE()
             WHERE PRODUCT_GROUP_ID = ? AND PRODUCT_GROUP_END_DATE IS NULL";
        $ok = $this->tms_db->query($sql, array($id));
        if ($ok) {
            $this->messages = 'Relasi berhasil dinonaktifkan.';
            return true;
        } else {
            $this->messages = 'Gagal menonaktifkan relasi.';
            return false;
        }
    }

    public function end_relation_by_child($child_id)
    {
        $child_id = (int)$child_id;
        $sql = "UPDATE {$this->table}
                   SET PRODUCT_GROUP_END_DATE = GETDATE()
                 WHERE PRODUCT_GROUP_CHILD_ID = ? AND PRODUCT_GROUP_END_DATE IS NULL";
        return $this->tms_db->query($sql, array($child_id));
    }

    public function get_child($group_id)
    {
        $sql = "SELECT p.PRODUCT_ID, p.PRODUCT_NAME, p.PRODUCT_DESC, p.PRODUCT_IS_GROUP
            FROM TMS_DB.dbo.TMS_M_PRODUCT_GROUP pg
            JOIN TMS_DB.dbo.TMS_M_PRODUCT p 
              ON pg.PRODUCT_GROUP_CHILD_ID = p.PRODUCT_ID
            WHERE pg.PRODUCT_GROUP_PARENT_ID = ?
              AND p.IS_DELETED = 0
            ORDER BY p.PRODUCT_NAME";

        return $this->tms_db->query($sql, array($group_id))->result_array();
    }
}
