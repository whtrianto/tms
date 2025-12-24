<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_product extends CI_Model
{
    private $table = 'MS_PARTS';

    /** @var CI_DB_sqlsrv_driver */
    public $tms_db;

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_NEW', TRUE);
    }

    /**
     * Ambil daftar product untuk feature "Product".
     * Sekarang hanya mengambil PART_IS_GROUP = 0 (child / bukan group).
     *
     * @param string|null $search
     * @return array
     */
    public function get_all($search = null)
    {
        $params = array();

        $sql = "
            SELECT
                P.PART_ID,
                P.PART_NAME,
                P.PART_IS_GROUP,
                P.PART_DESC,
                P.PART_CUS_CODE,
                P.PART_UNITS    ,
                P.PART_CUS_ID,
                P.PART_DRW_NO,
                P.PART_UNIT_PRICE,
                P.PART_WEIGHT,
                P.PART_TYPE,
                U.UOM_NAME,
                C.CUS_NAME
            FROM {$this->table} P
            LEFT JOIN MS_UOM U
                   ON U.UOM_ID = P.PART_UNITS
                  AND U.IS_DELETED = 0
            LEFT JOIN MS_CUSTOMER C
                   ON C.CUS_ID = P.PART_CUS_ID
                  AND C.IS_DELETED = 0
            WHERE P.IS_DELETED = 0
              AND P.PART_IS_GROUP = 0  -- hanya produk bukan group
        ";

        if (!empty($search)) {
            $sql .= "
                AND (
                    P.PART_NAME LIKE ?
                    OR P.PART_CUS_CODE LIKE ?
                    OR P.PART_DRW_NO LIKE ?
                    OR P.PART_TYPE LIKE ?
                    OR U.UOM_NAME LIKE ?
                    OR C.CUS_NAME LIKE ?
                )
            ";
            $like   = '%' . $search . '%';
            $params = array($like, $like, $like, $like, $like, $like);
        }

        $sql .= " ORDER BY P.PART_NAME ASC";

        return $this->tms_db->query($sql, $params)->result_array();
    }

    /**
     * Ambil semua product yang bertindak sebagai group (PART_IS_GROUP = 1)
     * Digunakan untuk dropdown di form.
     */
    public function get_groups()
    {
        $sql = "SELECT PART_ID, PART_NAME, PART_DESC, PART_IS_GROUP
            FROM MS_PARTS
            WHERE IS_DELETED = 0 AND PART_IS_GROUP = 1
            ORDER BY PART_NAME";
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
                p.PART_ID, 
                p.PART_NAME, 
                p.PART_DESC, 
                p.PART_IS_GROUP,
                pg.PART_GROUP_PARENT_ID
            FROM MS_PART_MEMBERS pg
            JOIN MS_PARTS p 
                ON pg.PART_GROUP_CHILD_ID = p.PART_ID
            WHERE p.IS_DELETED = 0
            ORDER BY p.PART_NAME
        ";
            return $this->tms_db->query($sql)->result_array();
        }

        $sql = "
        SELECT 
            p.PART_ID, 
            p.PART_NAME, 
            p.PART_DESC, 
            p.PART_IS_GROUP,
            pg.PART_GROUP_PARENT_ID
        FROM MS_PART_MEMBERS pg
        JOIN MS_PARTS p 
            ON pg.PART_GROUP_CHILD_ID = p.PART_ID
        WHERE pg.PART_GROUP_PARENT_ID = ?
          AND p.IS_DELETED = 0
        ORDER BY p.PART_NAME
    ";

        return $this->tms_db->query($sql, array($group_id))->result_array();
    }

    public function get_by_id($id)
    {
        $sql = "
            SELECT
                P.PART_ID,
                P.PART_NAME,
                P.PART_IS_GROUP,
                P.PART_DESC,
                P.PART_CUS_CODE,
                P.PART_UNITS,
                P.PART_CUS_ID,
                P.PART_DRW_NO,
                P.PART_TYPE,
                P.PART_UNIT_PRICE,
                P.PART_WEIGHT
            FROM {$this->table} P
            WHERE P.IS_DELETED = 0
              AND P.PART_ID = ?
        ";
        return $this->tms_db->query($sql, array($id))->row_array();
    }

    public function insert($data, $use_transaction = true)
    {
        if ($use_transaction) {
            $this->tms_db->trans_begin();
        }

        // Persiapkan data sesuai nama kolom di database MS_PARTS
        $insert_data = array(
            'PART_NAME'       => strtoupper(trim($data['PART_NAME'])),
            'PART_IS_GROUP'   => isset($data['PART_IS_GROUP']) ? (int)$data['PART_IS_GROUP'] : 0,
            'PART_DESC'       => !empty($data['PART_DESC']) ? $data['PART_DESC'] : null,
            'PART_CUS_CODE'   => !empty($data['PART_CUS_CODE']) ? $data['PART_CUS_CODE'] : null,
            'PART_UNITS'      => (!empty($data['PART_UNITS'])) ? (int)$data['PART_UNITS'] : null,
            'PART_CUS_ID'     => (!empty($data['PART_CUS_ID'])) ? (int)$data['PART_CUS_ID'] : null,
            'PART_DRW_NO'     => !empty($data['PART_DRW_NO']) ? $data['PART_DRW_NO'] : null,
            'PART_TYPE'       => !empty($data['PART_TYPE']) ? $data['PART_TYPE'] : null,
            'PART_UNIT_PRICE' => isset($data['PART_UNIT_PRICE']) ? (float)$data['PART_UNIT_PRICE'] : 0,
            'PART_WEIGHT'     => isset($data['PART_WEIGHT']) ? (float)$data['PART_WEIGHT'] : 0,
            'IS_DELETED'      => 0
        );

        // Gunakan Query Builder agar CI menangani escaping karakter otomatis
        $execute = $this->tms_db->insert($this->table, $insert_data);

        if (!$execute) {
            // Jika gagal, log errornya
            $db_error = $this->tms_db->error();
            log_message('error', 'Database Insert Error: ' . $db_error['message']);
            if ($use_transaction) $this->tms_db->trans_rollback();
            return 0;
        }

        // Ambil ID Identity yang baru saja dibuat
        // Pada driver sqlsrv CI3, ini akan menjalankan SELECT SCOPE_IDENTITY() secara otomatis
        $new_id = $this->tms_db->insert_id();

        // Fallback: Jika insert_id() tetap 0 padahal baris bertambah
        if ($new_id == 0) {
            $res = $this->tms_db->query("SELECT SCOPE_IDENTITY() AS last_id")->row();
            $new_id = (isset($res->last_id)) ? (int)$res->last_id : 0;
        }

        if ($use_transaction) {
            if ($this->tms_db->trans_status() === FALSE || $new_id == 0) {
                $this->tms_db->trans_rollback();
                return 0;
            } else {
                $this->tms_db->trans_commit();
                return $new_id;
            }
        }

        return $new_id;
    }

    public function update($id, $data)
    {
        $sql = "
            UPDATE {$this->table}
               SET PART_NAME          = ?,
                   PART_IS_GROUP      = ?,
                   PART_DESC          = ?,
                   PART_CUS_CODE      = ?,
                   PART_UNITS         = ?,
                   PART_CUS_ID        = ?,
                   PART_DRW_NO        = ?,
                   PART_TYPE          = ?,
                   PART_UNIT_PRICE = ?, 
                    PART_WEIGHT = ?
             WHERE PART_ID = ?
               AND IS_DELETED = 0
        ";

        $params = array(
            strtoupper(trim($data['PART_NAME'])),
            isset($data['PART_IS_GROUP']) ? (int)$data['PART_IS_GROUP'] : 0,
            isset($data['PART_DESC']) ? $data['PART_DESC'] : null,
            isset($data['PART_CUS_CODE']) ? $data['PART_CUS_CODE'] : null,
            isset($data['PART_UNITS']) ? (int)$data['PART_UNITS'] : null,
            isset($data['PART_CUS_ID']) ? (int)$data['PART_CUS_ID'] : null,
            isset($data['PART_DRW_NO']) ? $data['PART_DRW_NO'] : null,
            isset($data['PART_TYPE']) ? $data['PART_TYPE'] : null,
            isset($data['PART_UNIT_PRICE']) ? (int)$data['PART_UNIT_PRICE'] : null,
            isset($data['PART_WEIGHT']) ? (int)$data['PART_WEIGHT'] : null,
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

        $sql .= " WHERE PART_ID = ? AND IS_DELETED = 0";
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
              AND UPPER(RTRIM(LTRIM(PART_NAME))) = ?";

        if (!empty($exclude_id)) {
            $sql .= " AND PART_ID <> ?";
            $params[] = (int)$exclude_id;
        }

        $row = $this->tms_db->query($sql, $params)->row();
        return ($row && (int)$row->CNT > 0);
    }
}
