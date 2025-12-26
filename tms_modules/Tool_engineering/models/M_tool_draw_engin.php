<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model untuk menampilkan Tool Drawing menggunakan struktur tabel di struktur-tms.sql
 * Sumber utama:
 * - TMS_TOOL_MASTER_LIST (ML_ID, ML_TOOL_DRAW_NO, ML_TYPE)
 * - TMS_TOOL_MASTER_LIST_REV (MLR_ID, MLR_ML_ID, MLR_OP_ID, MLR_TC_ID, MLR_MAKER_ID, MLR_MAT_ID, MLR_MACG_ID, MLR_REV, MLR_STATUS, MLR_EFFECTIVE_DATE, MLR_MODIFIED_DATE, MLR_MODIFIED_BY)
 * - MS_OPERATION (OP_NAME)
 * - MS_TOOL_CLASS (TC_NAME)  -> dipakai sebagai Tool Name
 * - MS_MAKER (MAKER_NAME)
 * - MS_MATERIAL (MAT_NAME)
 * - MS_MACHINES (MAC_NAME)   -> Machine Group
 * - MS_PARTS (PART_NAME)     -> Product (via TMS_TOOL_MASTER_LIST_PARTS)
 * 
 * NOTE: Ubah $db_name jika ingin mengganti database
 */
class M_tool_draw_engin extends CI_Model
{
    private $db_tms;
    
    // Database name - ubah nilai ini jika ingin ganti database
    private $db_name = 'TMS_NEW';
    
    // Table prefix (akan di-set di constructor)
    private $tbl;
    
    public $messages = '';
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->db_tms = $this->load->database('tms_NEW', true);
        
        // Set table prefix dengan database name
        $this->tbl = $this->db_name . '.dbo.';
    }

    /**
     * Helper: Get table name with database prefix
     */
    private function t($table)
    {
        return $this->tbl . $table;
    }

    /**
     * Server-side DataTable processing - pagination di database
     * Tabel: TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_OPERATION, MS_TOOL_CLASS, MS_MAKER, MS_MATERIAL, MS_MACHINES, MS_USERS, TMS_TOOL_MASTER_LIST_PARTS, MS_PARTS
     */
    public function get_data_serverside($start, $length, $search, $order_col, $order_dir, $column_search = array())
    {
        // Column mapping for ordering
        $columns = array(
            0 => 'rev.MLR_ID',
            1 => 'TD_PRODUCT_NAME',
            2 => 'op.OP_NAME',
            3 => 'ml.ML_TOOL_DRAW_NO',
            4 => 'tc.TC_NAME',
            5 => 'rev.MLR_REV',
            6 => 'rev.MLR_STATUS',
            7 => 'rev.MLR_EFFECTIVE_DATE',
            8 => 'rev.MLR_MODIFIED_DATE',
            9 => 'usr.USR_NAME'
        );

        $base_from = "
            FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
            INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = rev.MLR_ML_ID
            LEFT JOIN {$this->t('MS_OPERATION')} op ON op.OP_ID = rev.MLR_OP_ID
            LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = rev.MLR_TC_ID
            LEFT JOIN {$this->t('MS_MAKER')} maker ON maker.MAKER_ID = rev.MLR_MAKER_ID
            LEFT JOIN {$this->t('MS_MATERIAL')} mat ON mat.MAT_ID = rev.MLR_MAT_ID
            LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = rev.MLR_MACG_ID
            LEFT JOIN {$this->t('MS_USERS')} usr ON usr.USR_ID = rev.MLR_MODIFIED_BY
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts ON mlparts.TMLP_ML_ID = ml.ML_ID
            LEFT JOIN {$this->t('MS_PARTS')} part ON part.PART_ID = mlparts.TMLP_PART_ID";

        $where = " WHERE ml.ML_TYPE = 1";
        $params = array();

        // Global search
        if (!empty($search)) {
            $where .= " AND (ml.ML_TOOL_DRAW_NO LIKE ? OR tc.TC_NAME LIKE ? OR op.OP_NAME LIKE ? 
                        OR usr.USR_NAME LIKE ? OR CAST(rev.MLR_ID AS VARCHAR) LIKE ? OR part.PART_NAME LIKE ?)";
            $search_param = '%' . $search . '%';
            $params = array_merge($params, array($search_param, $search_param, $search_param, $search_param, $search_param, $search_param));
        }

        // Per-column search
        $col_search_map = array(
            0 => 'CAST(rev.MLR_ID AS VARCHAR)',
            1 => 'ISNULL(part.PART_NAME, \'\')',
            2 => 'op.OP_NAME',
            3 => 'ml.ML_TOOL_DRAW_NO',
            4 => 'tc.TC_NAME',
            5 => 'CAST(rev.MLR_REV AS VARCHAR)',
            9 => 'usr.USR_NAME'
        );
        foreach ($column_search as $col_idx => $col_val) {
            if (!empty($col_val) && isset($col_search_map[$col_idx])) {
                $where .= " AND " . $col_search_map[$col_idx] . " LIKE ?";
                $params[] = '%' . $col_val . '%';
            }
        }

        // Count total (without filter)
        $count_total_sql = "SELECT COUNT(*) as cnt FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
                           INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = rev.MLR_ML_ID
                           WHERE ml.ML_TYPE = 1";
        $count_total = $this->db_tms->query($count_total_sql)->row()->cnt;

        // Count filtered
        $count_filtered_sql = "SELECT COUNT(*) as cnt " . $base_from . $where;
        $count_filtered = $this->db_tms->query($count_filtered_sql, $params)->row()->cnt;

        // Order
        $order_column = isset($columns[$order_col]) ? $columns[$order_col] : 'rev.MLR_ID';
        $order_direction = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';

        // Data query with pagination (SQL Server syntax)
        $data_sql = "SELECT 
                        rev.MLR_ID AS TD_ID,
                        ml.ML_TOOL_DRAW_NO AS TD_DRAWING_NO,
                        rev.MLR_REV AS TD_REVISION,
                        rev.MLR_STATUS AS TD_STATUS,
                        CASE WHEN rev.MLR_EFFECTIVE_DATE IS NULL THEN '' ELSE CONVERT(VARCHAR(19), rev.MLR_EFFECTIVE_DATE, 120) END AS TD_EFFECTIVE_DATE,
                        CASE WHEN rev.MLR_MODIFIED_DATE IS NULL THEN '' ELSE CONVERT(VARCHAR(19), rev.MLR_MODIFIED_DATE, 120) END AS TD_MODIFIED_DATE,
                        ISNULL(usr.USR_NAME, '') AS TD_MODIFIED_BY,
                        ISNULL(op.OP_NAME, '') AS TD_OPERATION_NAME,
                        ISNULL(tc.TC_NAME, '') AS TD_TOOL_NAME,
                        ISNULL(dbo.fnGetToolMasterListParts(ml.ML_ID), '') AS TD_PRODUCT_NAME
                    " . $base_from . $where . "
                    ORDER BY " . $order_column . " " . $order_direction . "
                    OFFSET " . (int)$start . " ROWS FETCH NEXT " . (int)$length . " ROWS ONLY";

        $result = $this->db_tms->query($data_sql, $params);
        $data = $result ? $result->result_array() : array();

        return array(
            'recordsTotal' => (int)$count_total,
            'recordsFiltered' => (int)$count_filtered,
            'data' => $data
        );
    }

    /**
     * Ambil list tool drawing (engineering) dari struktur SQL bawaan.
     * Hanya mengambil ML_TYPE = 1 (tool) dan status aktif/pending/inaktif berdasar MLR_STATUS.
     * Tabel: TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_OPERATION, MS_TOOL_CLASS, MS_MAKER, MS_MATERIAL, MS_MACHINES, MS_USERS
     */
    public function get_all()
    {
        $sql = "
            SELECT
                rev.MLR_ID          AS TD_ID,
                ml.ML_TOOL_DRAW_NO  AS TD_DRAWING_NO,
                rev.MLR_REV         AS TD_REVISION,
                rev.MLR_STATUS      AS TD_STATUS,
                CASE 
                    WHEN rev.MLR_EFFECTIVE_DATE IS NULL THEN ''
                    ELSE CONVERT(VARCHAR(19), rev.MLR_EFFECTIVE_DATE, 120)
                END AS TD_EFFECTIVE_DATE,
                CASE 
                    WHEN rev.MLR_MODIFIED_DATE IS NULL THEN ''
                    ELSE CONVERT(VARCHAR(19), rev.MLR_MODIFIED_DATE, 120)
                END AS TD_MODIFIED_DATE,
                ISNULL(usr.USR_NAME, '') AS TD_MODIFIED_BY,
                ISNULL(op.OP_NAME, '') AS TD_OPERATION_NAME,
                ISNULL(tc.TC_NAME, '') AS TD_TOOL_NAME,
                ISNULL(mac.MAC_NAME, '') AS TD_MAC_NAME,
                ISNULL(maker.MAKER_NAME, '') AS TD_MAKER_NAME,
                ISNULL(mat.MAT_NAME, '') AS TD_MATERIAL_NAME,
                ISNULL(dbo.fnGetToolMasterListParts(ml.ML_ID), '') AS TD_PRODUCT_NAME,
                ISNULL(rev.MLR_DRAWING, '') AS TD_DRAWING_FILE,
                ISNULL(rev.MLR_SKETCH, '') AS TD_SKETCH_FILE
            FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
            INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml
                ON ml.ML_ID = rev.MLR_ML_ID
            LEFT JOIN {$this->t('MS_OPERATION')} op
                ON op.OP_ID = rev.MLR_OP_ID
            LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc
                ON tc.TC_ID = rev.MLR_TC_ID
            LEFT JOIN {$this->t('MS_MAKER')} maker
                ON maker.MAKER_ID = rev.MLR_MAKER_ID
            LEFT JOIN {$this->t('MS_MATERIAL')} mat
                ON mat.MAT_ID = rev.MLR_MAT_ID
            LEFT JOIN {$this->t('MS_MACHINES')} mac
                ON mac.MAC_ID = rev.MLR_MACG_ID
            LEFT JOIN {$this->t('MS_USERS')} usr
                ON usr.USR_ID = rev.MLR_MODIFIED_BY
            WHERE ml.ML_TYPE = 1
            ORDER BY rev.MLR_ID DESC
        ";

        $q = $this->db_tms->query($sql);
        if (!$q) return array();
        return $q->result_array();
    }

    /**
     * Get data by ID (MLR_ID)
     * Tabel: TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_OPERATION, MS_TOOL_CLASS, MS_MAKER, MS_MATERIAL, MS_MACHINES, TMS_TOOL_MASTER_LIST_PARTS, MS_PARTS
     */
    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;

        $sql = "
            SELECT
                rev.MLR_ID          AS TD_ID,
                ml.ML_ID            AS TD_ML_ID,
                ml.ML_TOOL_DRAW_NO  AS TD_DRAWING_NO,
                rev.MLR_REV         AS TD_REVISION,
                rev.MLR_STATUS      AS TD_STATUS,
                rev.MLR_EFFECTIVE_DATE AS TD_EFFECTIVE_DATE,
                rev.MLR_MODIFIED_DATE  AS TD_MODIFIED_DATE,
                rev.MLR_MODIFIED_BY    AS TD_MODIFIED_BY,
                rev.MLR_OP_ID       AS TD_PROCESS_ID,
                op.OP_NAME          AS TD_OPERATION_NAME,
                rev.MLR_TC_ID       AS TD_TOOL_ID,
                tc.TC_NAME          AS TD_TOOL_NAME,
                rev.MLR_MAT_ID      AS TD_MATERIAL_ID,
                mat.MAT_NAME        AS TD_MATERIAL_NAME,
                rev.MLR_MAKER_ID    AS TD_MAKER_ID,
                maker.MAKER_NAME    AS TD_MAKER_NAME,
                rev.MLR_MACG_ID     AS TD_MACG_ID,
                mac.MAC_NAME        AS TD_MAC_NAME,
                part.PART_ID        AS TD_PRODUCT_ID,
                part.PART_NAME      AS TD_PRODUCT_NAME,
                ISNULL(rev.MLR_DRAWING, '') AS TD_DRAWING_FILE,
                ISNULL(rev.MLR_SKETCH, '') AS TD_SKETCH_FILE
            FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
            INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml
                ON ml.ML_ID = rev.MLR_ML_ID
            LEFT JOIN {$this->t('MS_OPERATION')} op
                ON op.OP_ID = rev.MLR_OP_ID
            LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc
                ON tc.TC_ID = rev.MLR_TC_ID
            LEFT JOIN {$this->t('MS_MAKER')} maker
                ON maker.MAKER_ID = rev.MLR_MAKER_ID
            LEFT JOIN {$this->t('MS_MATERIAL')} mat
                ON mat.MAT_ID = rev.MLR_MAT_ID
            LEFT JOIN {$this->t('MS_MACHINES')} mac
                ON mac.MAC_ID = rev.MLR_MACG_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts
                ON mlparts.TMLP_ML_ID = ml.ML_ID
            LEFT JOIN {$this->t('MS_PARTS')} part
                ON part.PART_ID = mlparts.TMLP_PART_ID
            WHERE rev.MLR_ID = ? AND ml.ML_TYPE = 1
        ";

        $q = $this->db_tms->query($sql, array($id));
        if ($q && $q->num_rows() > 0) {
            return $q->row_array();
        }
        return null;
    }

    /**
     * Get all products from MS_PARTS
     * Tabel: MS_PARTS
     */
    public function get_products()
    {
        $sql = "SELECT PART_ID AS PRODUCT_ID, PART_NAME AS PRODUCT_NAME 
                FROM {$this->t('MS_PARTS')} 
                WHERE IS_DELETED = 0
                ORDER BY PART_NAME ASC";
        $q = $this->db_tms->query($sql);
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
    }

    /**
     * Get all operations from MS_OPERATION
     * Tabel: MS_OPERATION
     */
    public function get_operations()
    {
        $sql = "SELECT OP_ID AS OPERATION_ID, OP_NAME AS OPERATION_NAME 
                FROM {$this->t('MS_OPERATION')} 
                WHERE (IS_DELETED = 0 OR IS_DELETED IS NULL)
                ORDER BY OP_NAME ASC";
        $q = $this->db_tms->query($sql);
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
    }

    /**
     * Get all tools from MS_TOOL_CLASS
     * Tabel: MS_TOOL_CLASS
     */
    public function get_tools()
    {
        $sql = "SELECT TC_ID AS TOOL_ID, TC_NAME AS TOOL_NAME 
                FROM {$this->t('MS_TOOL_CLASS')} 
                WHERE (IS_DELETED = 0 OR IS_DELETED IS NULL)
                ORDER BY TC_NAME ASC";
        $q = $this->db_tms->query($sql);
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
    }

    /**
     * Get tool by ID
     * Tabel: MS_TOOL_CLASS
     */
    public function get_tool_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $sql = "SELECT TC_ID AS TOOL_ID, TC_NAME AS TOOL_NAME 
                FROM {$this->t('MS_TOOL_CLASS')} 
                WHERE TC_ID = ? AND (IS_DELETED = 0 OR IS_DELETED IS NULL)";
        $q = $this->db_tms->query($sql, array($id));
        if ($q && $q->num_rows() > 0) {
            return $q->row_array();
        }
        return null;
    }

    /**
     * Get all materials from MS_MATERIAL
     * Tabel: MS_MATERIAL
     */
    public function get_materials()
    {
        $sql = "SELECT MAT_ID AS MATERIAL_ID, MAT_NAME AS MATERIAL_NAME 
                FROM {$this->t('MS_MATERIAL')} 
                WHERE (IS_DELETED = 0 OR IS_DELETED IS NULL)
                ORDER BY MAT_NAME ASC";
        $q = $this->db_tms->query($sql);
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
    }

    /**
     * Get all makers from MS_MAKER
     * Tabel: MS_MAKER
     */
    public function get_makers()
    {
        $sql = "SELECT MAKER_ID, MAKER_NAME 
                FROM {$this->t('MS_MAKER')} 
                WHERE (IS_DELETED = 0 OR IS_DELETED IS NULL)
                ORDER BY MAKER_NAME ASC";
        $q = $this->db_tms->query($sql);
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
    }

    /**
     * Get all machine groups from MS_MACHINES
     * Tabel: MS_MACHINES
     */
    public function get_machine_groups()
    {
        $sql = "
            SELECT
                M.MAC_ID   AS MACHINE_ID,
                M.MAC_NAME AS MACHINE_NAME
            FROM {$this->t('MS_MACHINES')} M
            WHERE M.IS_DELETED = 0
            AND M.MAC_IS_GROUP = 1
            ORDER BY M.MAC_NAME ASC
        ";

        $q = $this->db_tms->query($sql);
        return ($q && $q->num_rows() > 0) ? $q->result_array() : [];

    }

    /**
     * Get Tool BOM by Product ID (placeholder - adjust based on actual BOM table structure)
     * Tabel: (placeholder - tidak digunakan)
     */
    public function get_tool_bom_by_product_id($product_id)
    {
        $product_id = (int)$product_id;
        if ($product_id <= 0) return array();
        
        // Placeholder - kept for backward compatibility
        return array();
    }

    /**
     * Get Tool BOM list by MLR_ID (child)
     * Mengambil semua Tool BOM yang menggunakan tool drawing ini
     * Tabel: TMS_TOOL_MASTER_LIST_MEMBERS, TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, TMS_TOOL_MASTER_LIST_PARTS, MS_PARTS
     */
    public function get_tool_bom_by_mlr_id($mlr_id)
    {
        $mlr_id = (int)$mlr_id;
        if ($mlr_id <= 0) return array();

        $sql = "
            SELECT 
                members.TB_ID AS ID,
                members.TB_QTY AS QTY,
                members.TB_SEQ AS SEQ,
                parent_ml.ML_ID AS BOM_ML_ID,
                parent_ml.ML_TOOL_DRAW_NO AS TOOL_BOM,
                parent_rev.MLR_REV AS BOM_REV,
                parent_rev.MLR_STATUS AS BOM_STATUS,
                ISNULL(part.PART_NAME, '') AS PRODUCT
            FROM {$this->t('TMS_TOOL_MASTER_LIST_MEMBERS')} members
            INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} parent_rev 
                ON parent_rev.MLR_ID = members.TB_MLR_PARENT_ID
            INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} parent_ml 
                ON parent_ml.ML_ID = parent_rev.MLR_ML_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts 
                ON mlparts.TMLP_ML_ID = parent_ml.ML_ID
            LEFT JOIN {$this->t('MS_PARTS')} part 
                ON part.PART_ID = mlparts.TMLP_PART_ID
            WHERE members.TB_MLR_CHILD_ID = ?
              AND parent_ml.ML_TYPE = 2
              AND parent_rev.MLR_STATUS = 2
            ORDER BY parent_ml.ML_TOOL_DRAW_NO, parent_rev.MLR_REV DESC
        ";

        $q = $this->db_tms->query($sql, array($mlr_id));
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
    }

    /**
     * Add new tool drawing
     * Tabel: TMS_TOOL_MASTER_LIST, TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST_PARTS
     */
    public function add_data($product_id, $process_id, $drawing_no, $tool_id, $revision, $status, $material_id, $maker_id = 0, $machine_group_id = null, $effective_date = null)
    {
        $product_id = (int)$product_id;
        $process_id = (int)$process_id;
        $drawing_no = trim((string)$drawing_no);
        $tool_id = (int)$tool_id;
        $revision = (int)$revision;
        $status = (int)$status;
        $material_id = ($material_id > 0) ? (int)$material_id : null;
        $maker_id = ($maker_id > 0) ? (int)$maker_id : null;
        $machine_group_id = ($machine_group_id > 0) ? (int)$machine_group_id : null;
        $effective_date = !empty($effective_date) ? trim((string)$effective_date) : null;

        if ($drawing_no === '') {
            $this->messages = 'Drawing No tidak boleh kosong.';
            return false;
        }

        if ($product_id <= 0 || $process_id <= 0) {
            $this->messages = 'Product ID dan Process ID harus lebih dari 0.';
            return false;
        }

        $this->db_tms->trans_start();

        // Check if ML_TOOL_DRAW_NO already exists
        $check_sql = "SELECT ML_ID FROM {$this->t('TMS_TOOL_MASTER_LIST')} WHERE ML_TOOL_DRAW_NO = ?";
        $check_q = $this->db_tms->query($check_sql, array($drawing_no));
        $ml_id = null;

        if ($check_q && $check_q->num_rows() > 0) {
            // Use existing ML_ID
            $ml_id = (int)$check_q->row()->ML_ID;
        } else {
            // Insert new TMS_TOOL_MASTER_LIST
            $ml_insert_sql = "INSERT INTO {$this->t('TMS_TOOL_MASTER_LIST')} (ML_TOOL_DRAW_NO, ML_TYPE) VALUES (?, 1)";
            $this->db_tms->query($ml_insert_sql, array($drawing_no));
            $ml_id = (int)$this->db_tms->insert_id();
            if ($ml_id <= 0) {
                $ml_row = $this->db_tms->query("SELECT IDENT_CURRENT('TMS_TOOL_MASTER_LIST') AS last_id")->row_array();
                if ($ml_row && isset($ml_row['last_id'])) $ml_id = (int)$ml_row['last_id'];
            }

            // Insert product relationship
            if ($ml_id > 0 && $product_id > 0) {
                $parts_sql = "INSERT INTO {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} (TMLP_ML_ID, TMLP_PART_ID) VALUES (?, ?)";
                $this->db_tms->query($parts_sql, array($ml_id, $product_id));
            }
        }

        if ($ml_id <= 0) {
            $this->db_tms->trans_rollback();
            $this->messages = 'Gagal membuat master list.';
            return false;
        }

        // Insert TMS_TOOL_MASTER_LIST_REV
        $modified_by = isset($this->uid) && $this->uid !== '' ? (string)$this->uid : 'SYSTEM';
        
        // Use effective_date if provided, otherwise use GETDATE()
        if ($effective_date !== null) {
            $rev_sql = "INSERT INTO {$this->t('TMS_TOOL_MASTER_LIST_REV')} 
                        (MLR_ML_ID, MLR_OP_ID, MLR_TC_ID, MLR_MAKER_ID, MLR_MAT_ID, MLR_MACG_ID, MLR_REV, MLR_STATUS, MLR_EFFECTIVE_DATE, MLR_MODIFIED_DATE, MLR_MODIFIED_BY) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), ?)";
            $this->db_tms->query($rev_sql, array(
                $ml_id, $process_id, $tool_id > 0 ? $tool_id : null, $maker_id, $material_id, $machine_group_id, 
                $revision, $status, $effective_date, $modified_by
            ));
        } else {
            $rev_sql = "INSERT INTO {$this->t('TMS_TOOL_MASTER_LIST_REV')} 
                        (MLR_ML_ID, MLR_OP_ID, MLR_TC_ID, MLR_MAKER_ID, MLR_MAT_ID, MLR_MACG_ID, MLR_REV, MLR_STATUS, MLR_EFFECTIVE_DATE, MLR_MODIFIED_DATE, MLR_MODIFIED_BY) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), GETDATE(), ?)";
            $this->db_tms->query($rev_sql, array(
                $ml_id, $process_id, $tool_id > 0 ? $tool_id : null, $maker_id, $material_id, $machine_group_id, 
                $revision, $status, $modified_by
            ));
        }

        $this->db_tms->trans_complete();

        if ($this->db_tms->trans_status()) {
            $this->messages = 'Tool Drawing Engineering berhasil ditambahkan.';
            return true;
        }

        $this->messages = 'Gagal menambahkan tool drawing.';
        return false;
    }

    /**
     * Edit tool drawing
     * Tabel: TMS_TOOL_MASTER_LIST, TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST_PARTS
     */
    public function edit_data_engineering($id, $product_id, $process_id, $drawing_no, $tool_id, $status, $material_id, $maker_id = null, $machine_group_id = null, $effective_date = null, $revision = null)
    {
        $id = (int)$id;
        $product_id = (int)$product_id;
        $process_id = (int)$process_id;
        $drawing_no = trim((string)$drawing_no);
        $tool_id = ($tool_id > 0) ? (int)$tool_id : null;
        $status = (int)$status;
        $material_id = ($material_id > 0) ? (int)$material_id : null;
        $maker_id = ($maker_id > 0) ? (int)$maker_id : null;
        $machine_group_id = ($machine_group_id > 0) ? (int)$machine_group_id : null;
        $effective_date = !empty($effective_date) ? trim((string)$effective_date) : null;
        $revision = ($revision !== null) ? (int)$revision : null;

        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        $this->db_tms->trans_start();

        $ml_id = isset($current['TD_ML_ID']) ? (int)$current['TD_ML_ID'] : 0;
        // Use revision from form, or keep current revision if not provided
        if ($revision === null) {
            $revision = isset($current['TD_REVISION']) ? (int)$current['TD_REVISION'] : 0;
        }

        // Update TMS_TOOL_MASTER_LIST if drawing_no changed
        if ($ml_id > 0 && $drawing_no !== '') {
            $update_ml_sql = "UPDATE {$this->t('TMS_TOOL_MASTER_LIST')} SET ML_TOOL_DRAW_NO = ? WHERE ML_ID = ?";
            $this->db_tms->query($update_ml_sql, array($drawing_no, $ml_id));
        }

        // Update product relationship if changed
        if ($ml_id > 0 && $product_id > 0) {
            // Delete old relationship
            $del_parts_sql = "DELETE FROM {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} WHERE TMLP_ML_ID = ?";
            $this->db_tms->query($del_parts_sql, array($ml_id));
            // Insert new relationship
            $parts_sql = "INSERT INTO {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} (TMLP_ML_ID, TMLP_PART_ID) VALUES (?, ?)";
            $this->db_tms->query($parts_sql, array($ml_id, $product_id));
        }

        // Update TMS_TOOL_MASTER_LIST_REV
        $modified_by = isset($this->uid) && $this->uid !== '' ? (string)$this->uid : 'SYSTEM';
        
        // Get current values to preserve if new values are not provided
        $current_tool_id = isset($current['TD_TOOL_ID']) ? (int)$current['TD_TOOL_ID'] : (isset($current['MLR_TC_ID']) ? (int)$current['MLR_TC_ID'] : null);
        $current_material_id = isset($current['TD_MATERIAL_ID']) ? (int)$current['TD_MATERIAL_ID'] : (isset($current['MLR_MAT_ID']) ? (int)$current['MLR_MAT_ID'] : null);
        $current_maker_id = isset($current['TD_MAKER_ID']) ? (int)$current['TD_MAKER_ID'] : (isset($current['MLR_MAKER_ID']) ? (int)$current['MLR_MAKER_ID'] : null);
        $current_machine_group_id = isset($current['TD_MACHINE_GROUP_ID']) ? (int)$current['TD_MACHINE_GROUP_ID'] : (isset($current['MLR_MACG_ID']) ? (int)$current['MLR_MACG_ID'] : null);
        $current_effective_date = isset($current['TD_EFFECTIVE_DATE']) ? $current['TD_EFFECTIVE_DATE'] : (isset($current['MLR_EFFECTIVE_DATE']) ? $current['MLR_EFFECTIVE_DATE'] : null);
        
        // Use provided values, or keep current values if not provided
        $final_tool_id = ($tool_id !== null && $tool_id > 0) ? $tool_id : $current_tool_id;
        $final_material_id = ($material_id !== null && $material_id > 0) ? $material_id : $current_material_id;
        $final_maker_id = ($maker_id !== null && $maker_id > 0) ? $maker_id : $current_maker_id;
        $final_machine_group_id = ($machine_group_id !== null && $machine_group_id > 0) ? $machine_group_id : $current_machine_group_id;
        $final_effective_date = ($effective_date !== null && $effective_date !== '') ? $effective_date : $current_effective_date;
        
        // Build effective date SQL
        $effective_date_sql = '';
        if ($final_effective_date !== null && $final_effective_date !== '') {
            $effective_date_sql = ', MLR_EFFECTIVE_DATE = CONVERT(datetime, ?, 120)';
        }
        
        $update_rev_sql = "UPDATE {$this->t('TMS_TOOL_MASTER_LIST_REV')} 
                          SET MLR_OP_ID = ?, MLR_TC_ID = ?, MLR_MAT_ID = ?, MLR_MAKER_ID = ?, MLR_MACG_ID = ?, MLR_STATUS = ?, 
                              MLR_REV = ?, MLR_MODIFIED_DATE = GETDATE(), MLR_MODIFIED_BY = ?" . $effective_date_sql . "
                          WHERE MLR_ID = ?";
        
        $params = array($process_id, $final_tool_id, $final_material_id, $final_maker_id, $final_machine_group_id, $status, $revision, $modified_by);
        if ($final_effective_date !== null && $final_effective_date !== '') {
            $params[] = $final_effective_date;
        }
        $params[] = $id;
        
        $this->db_tms->query($update_rev_sql, $params);

        $this->db_tms->trans_complete();

        if ($this->db_tms->trans_status()) {
            $this->messages = 'Tool Drawing Engineering berhasil diperbarui.';
            return true;
        }

        $this->messages = 'Gagal memperbarui tool drawing.';
        return false;
    }

    /**
     * Save or update TMS_TOOL_MASTER_LIST_MEMBERS (relationship between BOM and Tool Drawing)
     * Tabel: TMS_TOOL_MASTER_LIST_MEMBERS
     */
    public function save_bom_member($parent_mlr_id, $child_mlr_id, $qty, $std_qty, $seq, $remark, $tb_id = null)
    {
        $parent_mlr_id = (int)$parent_mlr_id;
        $child_mlr_id = (int)$child_mlr_id;
        $qty = (int)$qty;
        $std_qty = ($std_qty !== null && $std_qty !== '') ? (int)$std_qty : null;
        $seq = (int)$seq;
        $remark = trim((string)$remark);
        $tb_id = ($tb_id > 0) ? (int)$tb_id : null;

        if ($parent_mlr_id <= 0 || $child_mlr_id <= 0) {
            $this->messages = 'Parent ID dan Child ID harus lebih dari 0.';
            return false;
        }

        // Check if relationship already exists
        $check_sql = "SELECT TB_ID FROM {$this->t('TMS_TOOL_MASTER_LIST_MEMBERS')} WHERE TB_MLR_PARENT_ID = ? AND TB_MLR_CHILD_ID = ?";
        $check_q = $this->db_tms->query($check_sql, array($parent_mlr_id, $child_mlr_id));
        
        if ($check_q && $check_q->num_rows() > 0) {
            // Update existing
            $existing_tb_id = (int)$check_q->row()->TB_ID;
            $update_sql = "UPDATE {$this->t('TMS_TOOL_MASTER_LIST_MEMBERS')} 
                          SET TB_QTY = ?, TB_STD_QTY = ?, TB_SEQ = ?, TB_REMARK = ? 
                          WHERE TB_ID = ?";
            $update_params = array($qty, $std_qty, $seq, $remark, $existing_tb_id);
            $ok = $this->db_tms->query($update_sql, $update_params);
            if ($ok) {
                $this->messages = 'Relasi BOM member berhasil diperbarui.';
                return true;
            }
        } else {
            // Insert new
            $insert_sql = "INSERT INTO {$this->t('TMS_TOOL_MASTER_LIST_MEMBERS')} 
                          (TB_MLR_PARENT_ID, TB_MLR_CHILD_ID, TB_QTY, TB_STD_QTY, TB_SEQ, TB_REMARK) 
                          VALUES (?, ?, ?, ?, ?, ?)";
            $insert_params = array($parent_mlr_id, $child_mlr_id, $qty, $std_qty, $seq, $remark);
            $ok = $this->db_tms->query($insert_sql, $insert_params);
            if ($ok) {
                $this->messages = 'Relasi BOM member berhasil ditambahkan.';
                return true;
            }
        }

        $err = $this->db_tms->error();
        $this->messages = 'Gagal menyimpan relasi BOM member. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Get MLR_ID by drawing_no and revision
     * Tabel: TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST
     */
    public function get_mlr_id_by_drawing_no($drawing_no, $revision = 0)
    {
        $drawing_no = trim((string)$drawing_no);
        $revision = (int)$revision;
        
        if ($drawing_no === '') {
            return 0;
        }

        $sql = "SELECT TOP 1 rev.MLR_ID 
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = rev.MLR_ML_ID
                WHERE ml.ML_TOOL_DRAW_NO = ? AND ml.ML_TYPE = 1";
        $params = array($drawing_no);
        
        if ($revision > 0) {
            $sql .= " AND rev.MLR_REV = ?";
            $params[] = $revision;
        }
        
        $sql .= " ORDER BY rev.MLR_REV DESC";
        
        $q = $this->db_tms->query($sql, $params);
        if ($q && $q->num_rows() > 0) {
            return (int)$q->row()->MLR_ID;
        }
        return 0;
    }

    /**
     * Delete tool drawing
     * Tabel: TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, TMS_TOOL_MASTER_LIST_PARTS
     */
    public function delete_data($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            $this->messages = 'ID tidak valid.';
            return false;
        }

        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        $this->db_tms->trans_start();

        $ml_id = isset($current['TD_ML_ID']) ? (int)$current['TD_ML_ID'] : 0;

        // Delete revision
        $del_rev_sql = "DELETE FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} WHERE MLR_ID = ?";
        $this->db_tms->query($del_rev_sql, array($id));

        // Check if there are other revisions for this ML_ID
        if ($ml_id > 0) {
            $check_rev_sql = "SELECT COUNT(*) AS cnt FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} WHERE MLR_ML_ID = ?";
            $check_q = $this->db_tms->query($check_rev_sql, array($ml_id));
            if ($check_q && $check_q->num_rows() > 0) {
                $cnt = (int)$check_q->row()->cnt;
                if ($cnt == 0) {
                    // No more revisions, delete master list and parts
                    $del_parts_sql = "DELETE FROM {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} WHERE TMLP_ML_ID = ?";
                    $this->db_tms->query($del_parts_sql, array($ml_id));
                    $del_ml_sql = "DELETE FROM {$this->t('TMS_TOOL_MASTER_LIST')} WHERE ML_ID = ?";
                    $this->db_tms->query($del_ml_sql, array($ml_id));
                }
            }
        }

        $this->db_tms->trans_complete();

        if ($this->db_tms->trans_status()) {
            $this->messages = 'Tool Drawing Engineering berhasil dihapus.';
            return true;
        }

        $this->messages = 'Gagal menghapus tool drawing.';
        return false;
    }

    /**
     * Get history - semua revision dari MLR_ML_ID yang sama
     * @param int $id MLR_ID dari revision tertentu
     * @return array History records
     * Tabel: TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_OPERATION, MS_TOOL_CLASS, MS_MAKER, MS_MATERIAL, MS_MACHINES, MS_USERS, TMS_TOOL_MASTER_LIST_PARTS, MS_PARTS
     */
    public function get_history($id)
    {
        $id = (int)$id;
        if ($id <= 0) return array();

        // Ambil MLR_ML_ID dari revision yang diberikan
        $current = $this->get_by_id($id);
        if (!$current || !isset($current['TD_ML_ID'])) {
            return array();
        }

        $ml_id = (int)$current['TD_ML_ID'];

        // Ambil semua revision dengan MLR_ML_ID yang sama
        $sql = "
            SELECT
                rev.MLR_ID          AS TD_ID,
                ml.ML_ID            AS TD_ML_ID,
                ml.ML_TOOL_DRAW_NO  AS TD_DRAWING_NO,
                rev.MLR_REV         AS TD_REVISION,
                rev.MLR_STATUS      AS TD_STATUS,
                CASE 
                    WHEN rev.MLR_EFFECTIVE_DATE IS NULL THEN ''
                    ELSE CONVERT(VARCHAR(19), rev.MLR_EFFECTIVE_DATE, 120)
                END AS TD_EFFECTIVE_DATE,
                CASE 
                    WHEN rev.MLR_MODIFIED_DATE IS NULL THEN ''
                    ELSE CONVERT(VARCHAR(19), rev.MLR_MODIFIED_DATE, 120)
                END AS TD_MODIFIED_DATE,
                ISNULL(usr.USR_NAME, '') AS TD_MODIFIED_BY,
                rev.MLR_OP_ID       AS TD_PROCESS_ID,
                ISNULL(op.OP_NAME, '') AS TD_OPERATION_NAME,
                rev.MLR_TC_ID       AS TD_TOOL_ID,
                ISNULL(tc.TC_NAME, '') AS TD_TOOL_NAME,
                rev.MLR_MAT_ID      AS TD_MATERIAL_ID,
                ISNULL(mat.MAT_NAME, '') AS TD_MATERIAL_NAME,
                rev.MLR_MAKER_ID    AS TD_MAKER_ID,
                ISNULL(maker.MAKER_NAME, '') AS TD_MAKER_NAME,
                rev.MLR_MACG_ID     AS TD_MACG_ID,
                ISNULL(mac.MAC_NAME, '') AS TD_MAC_NAME,
                part.PART_ID        AS TD_PRODUCT_ID,
                ISNULL(part.PART_NAME, '') AS TD_PRODUCT_NAME
            FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
            INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml
                ON ml.ML_ID = rev.MLR_ML_ID
            LEFT JOIN {$this->t('MS_OPERATION')} op
                ON op.OP_ID = rev.MLR_OP_ID
            LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc
                ON tc.TC_ID = rev.MLR_TC_ID
            LEFT JOIN {$this->t('MS_MAKER')} maker
                ON maker.MAKER_ID = rev.MLR_MAKER_ID
            LEFT JOIN {$this->t('MS_MATERIAL')} mat
                ON mat.MAT_ID = rev.MLR_MAT_ID
            LEFT JOIN {$this->t('MS_MACHINES')} mac
                ON mac.MAC_ID = rev.MLR_MACG_ID
            LEFT JOIN {$this->t('MS_USERS')} usr
                ON usr.USR_ID = rev.MLR_MODIFIED_BY
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts
                ON mlparts.TMLP_ML_ID = ml.ML_ID
            LEFT JOIN {$this->t('MS_PARTS')} part
                ON part.PART_ID = mlparts.TMLP_PART_ID
            WHERE rev.MLR_ML_ID = ? AND ml.ML_TYPE = 1
            ORDER BY rev.MLR_REV DESC, rev.MLR_MODIFIED_DATE DESC
        ";

        $q = $this->db_tms->query($sql, array($ml_id));
        if (!$q || $q->num_rows() === 0) {
            return array();
        }
        return $q->result_array();
    }
}