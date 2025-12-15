<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_product extends CI_Model
{
    private $table = 'TMS_NEW.dbo.TMS_M_PRODUCT';

    /** @var CI_DB_sqlsrv_driver */
    public $tms_db;

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_db', TRUE);
    }

    /**
     * Ambil daftar product untuk feature "Product".
     * Sekarang hanya mengambil PRODUCT_IS_GROUP = 0 (child / bukan group).
     *
     * @param string|null $search
     * @return array
     */
    public function get_all($search = null)
    {
        $params = array();

        $sql = "
            SELECT
                P.PRODUCT_ID,
                P.PRODUCT_NAME,
                P.PRODUCT_IS_GROUP,
                P.PRODUCT_DESC,
                P.PRODUCT_CUSTOMER_CODE,
                P.UOM_ID,
                P.CUSTOMER_ID,
                P.PRODUCT_DRW_NO,
                P.PRODUCT_TYPE,
                U.UOM_NAME,
                C.CUSTOMER_NAME
            FROM {$this->table} P
            LEFT JOIN TMS_NEW.dbo.TMS_M_UOM U
                   ON U.UOM_ID = P.UOM_ID
                  AND U.IS_DELETED = 0
            LEFT JOIN TMS_NEW.dbo.TMS_M_CUSTOMER C
                   ON C.CUSTOMER_ID = P.CUSTOMER_ID
                  AND C.IS_DELETED = 0
            WHERE P.IS_DELETED = 0
              AND P.PRODUCT_IS_GROUP = 0  -- hanya produk bukan group
        ";

        if (!empty($search)) {
            $sql .= "
                AND (
                    P.PRODUCT_NAME LIKE ?
                    OR P.PRODUCT_CUSTOMER_CODE LIKE ?
                    OR P.PRODUCT_DRW_NO LIKE ?
                    OR P.PRODUCT_TYPE LIKE ?
                    OR U.UOM_NAME LIKE ?
                    OR C.CUSTOMER_NAME LIKE ?
                )
            ";
            $like   = '%' . $search . '%';
            $params = array($like, $like, $like, $like, $like, $like);
        }

        $sql .= " ORDER BY P.PRODUCT_NAME ASC";

        return $this->tms_db->query($sql, $params)->result_array();
    }

    /**
     * Ambil semua product yang bertindak sebagai group (PRODUCT_IS_GROUP = 1)
     * Digunakan untuk dropdown di form.
     */
    public function get_groups()
    {
        $sql = "SELECT PRODUCT_ID, PRODUCT_NAME, PRODUCT_DESC, PRODUCT_IS_GROUP
            FROM TMS_NEW.dbo.TMS_M_PRODUCT
            WHERE IS_DELETED = 0 AND PRODUCT_IS_GROUP = 1
            ORDER BY PRODUCT_NAME";
        return $this->tms_db->query($sql)->result_array();
    }

    /**
     * Ambil child products (optionally filter per group_id)
     */
    public function get_child($group_id = null)
    {
        if ($group_id === null) {
            $sql = "
            SELECT 
                p.PRODUCT_ID, 
                p.PRODUCT_NAME, 
                p.PRODUCT_DESC, 
                p.PRODUCT_IS_GROUP,
                pg.PRODUCT_GROUP_PARENT_ID
            FROM TMS_NEW.dbo.TMS_M_PRODUCT_GROUP pg
            JOIN TMS_NEW.dbo.TMS_M_PRODUCT p 
                ON pg.PRODUCT_GROUP_CHILD_ID = p.PRODUCT_ID
            WHERE p.IS_DELETED = 0
            ORDER BY p.PRODUCT_NAME
        ";
            return $this->tms_db->query($sql)->result_array();
        }

        $sql = "
        SELECT 
            p.PRODUCT_ID, 
            p.PRODUCT_NAME, 
            p.PRODUCT_DESC, 
            p.PRODUCT_IS_GROUP,
            pg.PRODUCT_GROUP_PARENT_ID
        FROM TMS_NEW.dbo.TMS_M_PRODUCT_GROUP pg
        JOIN TMS_NEW.dbo.TMS_M_PRODUCT p 
            ON pg.PRODUCT_GROUP_CHILD_ID = p.PRODUCT_ID
        WHERE pg.PRODUCT_GROUP_PARENT_ID = ?
          AND p.IS_DELETED = 0
        ORDER BY p.PRODUCT_NAME
    ";

        return $this->tms_db->query($sql, array($group_id))->result_array();
    }

    public function get_by_id($id)
    {
        $sql = "
            SELECT
                P.PRODUCT_ID,
                P.PRODUCT_NAME,
                P.PRODUCT_IS_GROUP,
                P.PRODUCT_DESC,
                P.PRODUCT_CUSTOMER_CODE,
                P.UOM_ID,
                P.CUSTOMER_ID,
                P.PRODUCT_DRW_NO,
                P.PRODUCT_TYPE
            FROM {$this->table} P
            WHERE P.IS_DELETED = 0
              AND P.PRODUCT_ID = ?
        ";
        return $this->tms_db->query($sql, array($id))->row_array();
    }

    public function insert($data, $use_transaction = true)
    {
        // normalisasi input
        $name = isset($data['PRODUCT_NAME']) ? strtoupper(trim($data['PRODUCT_NAME'])) : '';
        $is_group = isset($data['PRODUCT_IS_GROUP']) ? (int)$data['PRODUCT_IS_GROUP'] : 0;
        $desc = isset($data['PRODUCT_DESC']) ? $data['PRODUCT_DESC'] : null;
        $custcode = isset($data['PRODUCT_CUSTOMER_CODE']) ? $data['PRODUCT_CUSTOMER_CODE'] : null;
        $uom = ($data['UOM_ID'] !== '' && $data['UOM_ID'] !== null) ? (int)$data['UOM_ID'] : null;
        $cust = ($data['CUSTOMER_ID'] !== '' && $data['CUSTOMER_ID'] !== null) ? (int)$data['CUSTOMER_ID'] : null;
        $drw = isset($data['PRODUCT_DRW_NO']) ? $data['PRODUCT_DRW_NO'] : null;
        $type = isset($data['PRODUCT_TYPE']) ? $data['PRODUCT_TYPE'] : null;

        // mulai transaksi jika diminta
        if ($use_transaction) {
            $this->tms_db->trans_begin();
        }

        // Dapatkan next PRODUCT_ID secara eksklusif (mengunci tabel)
        $nextq = $this->tms_db->query("SELECT ISNULL(MAX(PRODUCT_ID),0) + 1 AS next_id FROM {$this->table} WITH (TABLOCKX)");
        if (!$nextq) {
            if ($use_transaction) $this->tms_db->trans_rollback();
            return 0;
        }
        $nextrow = $nextq->row();
        $next_id = isset($nextrow->next_id) ? (int)$nextrow->next_id : 0;
        if ($next_id <= 0) {
            if ($use_transaction) $this->tms_db->trans_rollback();
            return 0;
        }

        // Insert explicit PRODUCT_ID
        $sql = "
        INSERT INTO {$this->table}
        (
            PRODUCT_ID,
            PRODUCT_NAME,
            PRODUCT_IS_GROUP,
            PRODUCT_DESC,
            PRODUCT_CUSTOMER_CODE,
            UOM_ID,
            CUSTOMER_ID,
            IS_DELETED,
            PRODUCT_DRW_NO,
            PRODUCT_TYPE
        )
        VALUES (?,?,?,?,?,?,?,0,?,?)
    ";
        $params = array($next_id, $name, $is_group, $desc, $custcode, $uom, $cust, $drw, $type);
        $this->tms_db->query($sql, $params);

        if ($use_transaction) {
            if ($this->tms_db->trans_status() === FALSE) {
                $this->tms_db->trans_rollback();
                return 0;
            } else {
                $this->tms_db->trans_commit();
                return (int)$next_id;
            }
        } else {
            // jika tidak mengelola trans (controller akan commit/rollback), periksa status query
            if ($this->tms_db->affected_rows() >= 0) {
                return (int)$next_id;
            } else {
                return 0;
            }
        }
    }

    public function update($id, $data)
    {
        $sql = "
            UPDATE {$this->table}
               SET PRODUCT_NAME          = ?,
                   PRODUCT_IS_GROUP      = ?,
                   PRODUCT_DESC          = ?,
                   PRODUCT_CUSTOMER_CODE = ?,
                   UOM_ID                = ?,
                   CUSTOMER_ID           = ?,
                   PRODUCT_DRW_NO        = ?,
                   PRODUCT_TYPE          = ?
             WHERE PRODUCT_ID = ?
               AND IS_DELETED = 0
        ";

        $params = array(
            strtoupper(trim($data['PRODUCT_NAME'])),
            isset($data['PRODUCT_IS_GROUP']) ? (int)$data['PRODUCT_IS_GROUP'] : 0,
            isset($data['PRODUCT_DESC']) ? $data['PRODUCT_DESC'] : null,
            isset($data['PRODUCT_CUSTOMER_CODE']) ? $data['PRODUCT_CUSTOMER_CODE'] : null,
            ($data['UOM_ID'] !== '' && $data['UOM_ID'] !== null) ? (int)$data['UOM_ID'] : null,
            ($data['CUSTOMER_ID'] !== '' && $data['CUSTOMER_ID'] !== null) ? (int)$data['CUSTOMER_ID'] : null,
            isset($data['PRODUCT_DRW_NO']) ? $data['PRODUCT_DRW_NO'] : null,
            isset($data['PRODUCT_TYPE']) ? $data['PRODUCT_TYPE'] : null,
            (int)$id
        );

        return $this->tms_db->query($sql, $params);
    }

    public function soft_delete($id, $deleted_by = null)
    {
        $params = array();
        $sql = "
            UPDATE {$this->table}
               SET IS_DELETED = 1,
                   DELETED_AT = GETDATE()
        ";

        if ($deleted_by !== null && $deleted_by !== '') {
            $sql .= ", DELETED_BY = ? ";
            $params[] = $deleted_by;
        }

        $sql .= " WHERE PRODUCT_ID = ? AND IS_DELETED = 0";
        $params[] = (int)$id;

        return $this->tms_db->query($sql, $params);
    }

    public function is_duplicate($name, $exclude_id = null)
    {
        $name_norm = strtoupper(trim($name));
        $params = array($name_norm);

        // gunakan UPPER pada kolom agar case-insensitive
        $sql = "SELECT COUNT(1) AS CNT FROM {$this->table}
            WHERE IS_DELETED = 0
              AND UPPER(RTRIM(LTRIM(PRODUCT_NAME))) = ?";

        if (!empty($exclude_id)) {
            $sql .= " AND PRODUCT_ID <> ?";
            $params[] = (int)$exclude_id;
        }

        $row = $this->tms_db->query($sql, $params)->row();
        return ($row && (int)$row->CNT > 0);
    }
}
