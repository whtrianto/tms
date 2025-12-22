<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model untuk Tool Inventory
 * Uses TMS_NEW database: TMS_TOOL_INVENTORY
 */
class M_tool_inventory extends CI_Model
{
    private $db_tms;
    private $db_name = 'TMS_NEW';
    private $tbl;
    
    public $messages = '';
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->db_tms = $this->load->database('tms_NEW', true);
        $this->tbl = $this->db_name . '.dbo.';
    }

    private function t($table)
    {
        return $this->tbl . $table;
    }

    /**
     * Server-side DataTable processing
     */
    public function get_data_serverside($start, $length, $search, $order_col, $order_dir, $column_search = array())
    {
        $columns = array(
            0 => 'inv.INV_ID',
            1 => 'inv.INV_TOOL_TAG',
            2 => 'ISNULL(inv.INV_RQ_NO, ord.ORD_RQ_NO)',
            3 => 'part.PART_NAME',
            4 => 'tc.TC_NAME',
            5 => 'ml.ML_TOOL_DRAW_NO',
            6 => 'inv.INV_RECEIVED_DATE',
            7 => 'inv.INV_DO_NO',
            8 => 'inv.INV_TOOL_ID',
            9 => 'inv.INV_STATUS',
            10 => 'inv.INV_NOTES',
            11 => 'sl.SL_NAME',
            12 => 'mat.MAT_NAME',
            13 => 'inv.INV_TOOL_CONDITION',
            14 => 'CAST(ISNULL(inv.INV_END_CYCLE, 0) AS VARCHAR)'
        );

        $base_from = "
            FROM {$this->t('TMS_TOOL_INVENTORY')} inv
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
            LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
            LEFT JOIN {$this->t('MS_MATERIAL')} mat ON mat.MAT_ID = inv.INV_MAT_ID
            LEFT JOIN {$this->t('MS_STORAGE_LOCATION')} sl ON sl.SL_ID = inv.INV_SL_ID
            LEFT JOIN {$this->t('TMS_ORDERING_ITEMS')} ordi ON ordi.ORDI_ID = inv.INV_ORDI_ID
            LEFT JOIN {$this->t('TMS_ORDERING')} ord ON ord.ORD_ID = ordi.ORDI_ORD_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts ON mlparts.TMLP_ML_ID = ml.ML_ID
            LEFT JOIN {$this->t('MS_PARTS')} part ON part.PART_ID = mlparts.TMLP_PART_ID";

        $where = " WHERE (ml.ML_TYPE = 1 OR ml.ML_TYPE IS NULL OR inv.INV_MLR_ID IS NULL)";
        $params = array();

        // Global search
        if (!empty($search)) {
            $where .= " AND (
                CAST(inv.INV_ID AS VARCHAR) LIKE ? OR 
                inv.INV_TOOL_TAG LIKE ? OR 
                ISNULL(inv.INV_RQ_NO, ord.ORD_RQ_NO) LIKE ? OR 
                part.PART_NAME LIKE ? OR 
                tc.TC_NAME LIKE ? OR 
                ml.ML_TOOL_DRAW_NO LIKE ? OR 
                CONVERT(VARCHAR(19), inv.INV_RECEIVED_DATE, 120) LIKE ? OR 
                inv.INV_DO_NO LIKE ? OR 
                inv.INV_TOOL_ID LIKE ? OR 
                CAST(inv.INV_STATUS AS VARCHAR) LIKE ? OR 
                inv.INV_NOTES LIKE ? OR 
                sl.SL_NAME LIKE ? OR 
                mat.MAT_NAME LIKE ? OR 
                CAST(inv.INV_TOOL_CONDITION AS VARCHAR) LIKE ? OR 
                CAST(inv.INV_END_CYCLE AS VARCHAR) LIKE ?
            )";
            $search_param = '%' . $search . '%';
            for ($i = 0; $i < 15; $i++) {
                $params[] = $search_param;
            }
        }

        // Per-column search
        $col_search_map = array(
            0 => 'CAST(inv.INV_ID AS VARCHAR)',
            1 => 'ISNULL(inv.INV_TOOL_TAG, \'\')',
            2 => 'ISNULL(ISNULL(inv.INV_RQ_NO, ord.ORD_RQ_NO), \'\')',
            3 => 'ISNULL(part.PART_NAME, \'\')',
            4 => 'ISNULL(tc.TC_NAME, \'\')',
            5 => 'ISNULL(ml.ML_TOOL_DRAW_NO, \'\')',
            6 => 'ISNULL(CONVERT(VARCHAR(19), inv.INV_RECEIVED_DATE, 120), \'\')',
            7 => 'ISNULL(inv.INV_DO_NO, \'\')',
            8 => 'ISNULL(inv.INV_TOOL_ID, \'\')',
            9 => 'CAST(ISNULL(inv.INV_STATUS, 0) AS VARCHAR)',
            10 => 'ISNULL(inv.INV_NOTES, \'\')',
            11 => 'ISNULL(sl.SL_NAME, \'\')',
            12 => 'ISNULL(mat.MAT_NAME, \'\')',
            13 => 'CAST(ISNULL(inv.INV_TOOL_CONDITION, 0) AS VARCHAR)',
            14 => 'CAST(ISNULL(inv.INV_END_CYCLE, 0) AS VARCHAR)'
        );
        
        foreach ($column_search as $col_idx => $col_val) {
            if (!empty($col_val) && isset($col_search_map[$col_idx])) {
                $where .= " AND " . $col_search_map[$col_idx] . " LIKE ?";
                $params[] = '%' . $col_val . '%';
            }
        }

        // Count total - use same joins as base_from
        $count_total_where = " WHERE (ml.ML_TYPE = 1 OR ml.ML_TYPE IS NULL OR inv.INV_MLR_ID IS NULL)";
        $count_total_sql = "SELECT COUNT(*) as cnt " . $base_from . $count_total_where;
        $count_total_result = $this->db_tms->query($count_total_sql);
        $count_total = $count_total_result && $count_total_result->num_rows() > 0 ? $count_total_result->row()->cnt : 0;

        // Count filtered
        $count_filtered_sql = "SELECT COUNT(*) as cnt " . $base_from . $where;
        $count_filtered_result = $this->db_tms->query($count_filtered_sql, $params);
        $count_filtered = $count_filtered_result && $count_filtered_result->num_rows() > 0 ? $count_filtered_result->row()->cnt : 0;

        // Order
        $order_column = isset($columns[$order_col]) ? $columns[$order_col] : 'inv.INV_ID';
        $order_direction = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';

        // Data query
        $data_sql = "SELECT 
                        inv.INV_ID,
                        inv.INV_TOOL_TAG,
                        ISNULL(inv.INV_RQ_NO, ord.ORD_RQ_NO) AS RQ_NO,
                        ISNULL(part.PART_NAME, '') AS PRODUCT_NAME,
                        ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                        ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_DRAWING_NO,
                        CASE WHEN inv.INV_RECEIVED_DATE IS NULL THEN '' 
                             ELSE CONVERT(VARCHAR(19), inv.INV_RECEIVED_DATE, 120) END AS RECEIVED_DATE,
                        ISNULL(inv.INV_DO_NO, '') AS DO_NO,
                        inv.INV_TOOL_ID,
                        inv.INV_STATUS,
                        ISNULL(inv.INV_NOTES, '') AS NOTES,
                        ISNULL(sl.SL_NAME, '') AS STORAGE_LOCATION,
                        ISNULL(mat.MAT_NAME, '') AS MATERIAL,
                        inv.INV_TOOL_CONDITION,
                        ISNULL(inv.INV_END_CYCLE, 0) AS END_CYCLE
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
     * Get by ID
     */
    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;

        $sql = "SELECT 
                    inv.INV_ID,
                    inv.INV_TOOL_TAG,
                    ISNULL(inv.INV_RQ_NO, ord.ORD_RQ_NO) AS RQ_NO,
                    ISNULL(part.PART_NAME, '') AS PRODUCT_NAME,
                    part.PART_ID AS PRODUCT_ID,
                    ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                    tc.TC_ID AS TOOL_NAME_ID,
                    ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_DRAWING_NO,
                    ml.ML_ID AS TOOL_DRAWING_ML_ID,
                    mlr.MLR_OP_ID AS PROCESS_ID,
                    op.OP_NAME AS PROCESS_NAME,
                    mlr.MLR_REV AS REVISION,
                    CASE WHEN inv.INV_RECEIVED_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), inv.INV_RECEIVED_DATE, 120) END AS RECEIVED_DATE,
                    ISNULL(inv.INV_DO_NO, '') AS DO_NO,
                    inv.INV_TOOL_ID,
                    inv.INV_STATUS,
                    ISNULL(inv.INV_NOTES, '') AS NOTES,
                    ISNULL(sl.SL_NAME, '') AS STORAGE_LOCATION,
                    ISNULL(sl.SL_NAME, '') AS STORAGE_LOCATION_NAME,
                    inv.INV_SL_ID AS STORAGE_LOCATION_ID,
                    ISNULL(mat.MAT_NAME, '') AS MATERIAL,
                    inv.INV_MAT_ID AS MATERIAL_ID,
                    inv.INV_TOOL_CONDITION,
                    ISNULL(inv.INV_END_CYCLE, 0) AS END_CYCLE,
                    inv.INV_MLR_ID,
                    inv.INV_MAKER_ID,
                    inv.INV_BEGIN_CYCLE,
                    inv.INV_IN_TOOL_SET,
                    inv.INV_ASSETIZED,
                    inv.INV_PURCHASE_TYPE,
                    maker.MAKER_CODE AS MAKER_CODE,
                    maker.MAKER_NAME AS MAKER_NAME
                FROM {$this->t('TMS_TOOL_INVENTORY')} inv
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_OPERATION')} op ON op.OP_ID = mlr.MLR_OP_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                LEFT JOIN {$this->t('MS_MATERIAL')} mat ON mat.MAT_ID = inv.INV_MAT_ID
                LEFT JOIN {$this->t('MS_STORAGE_LOCATION')} sl ON sl.SL_ID = inv.INV_SL_ID
                LEFT JOIN {$this->t('TMS_ORDERING_ITEMS')} ordi ON ordi.ORDI_ID = inv.INV_ORDI_ID
                LEFT JOIN {$this->t('TMS_ORDERING')} ord ON ord.ORD_ID = ordi.ORDI_ORD_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts ON mlparts.TMLP_ML_ID = ml.ML_ID
                LEFT JOIN {$this->t('MS_PARTS')} part ON part.PART_ID = mlparts.TMLP_PART_ID
                LEFT JOIN {$this->t('MS_MAKER')} maker ON maker.MAKER_ID = inv.INV_MAKER_ID
                WHERE inv.INV_ID = ?";

        $q = $this->db_tms->query($sql, array($id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
    }

    /**
     * Get status name
     * ToolInventoryStatus enum:
     * 1=New, 2=Allocated, 3=Available, 4=InUsed, 5=Onhold, 6=Scrapped, 7=Repairing, 8=Modifying, 9=DesignChange
     */
    public function get_status_name($status)
    {
        $status = (int)$status;
        $status_map = array(
            1 => 'New',
            2 => 'Allocated',
            3 => 'Available',
            4 => 'InUsed',
            5 => 'Onhold',
            6 => 'Scrapped',
            7 => 'Repairing',
            8 => 'Modifying',
            9 => 'DesignChange'
        );
        return isset($status_map[$status]) ? $status_map[$status] : 'Unknown';
    }

    /**
     * Get status badge HTML
     */
    public function get_status_badge($status)
    {
        $status = (int)$status;
        $status_name = $this->get_status_name($status);
        $badge_class = 'badge-secondary';
        
        switch ($status) {
            case 1: // New
                $badge_class = 'badge-info';
                break;
            case 2: // Allocated
            case 4: // InUsed
                $badge_class = 'badge-warning';
                break;
            case 3: // Available
                $badge_class = 'badge-success';
                break;
            case 5: // Onhold
                $badge_class = 'badge-danger';
                break;
            case 6: // Scrapped
                $badge_class = 'badge-dark';
                break;
            case 7: // Repairing
            case 8: // Modifying
            case 9: // DesignChange
                $badge_class = 'badge-primary';
                break;
        }
        
        return '<span class="badge ' . $badge_class . '">' . htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8') . '</span>';
    }

    /**
     * Get all data for export (no pagination)
     */
    public function get_all_for_export()
    {
        $base_from = "
            FROM {$this->t('TMS_TOOL_INVENTORY')} inv
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
            LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
            LEFT JOIN {$this->t('MS_MATERIAL')} mat ON mat.MAT_ID = inv.INV_MAT_ID
            LEFT JOIN {$this->t('MS_STORAGE_LOCATION')} sl ON sl.SL_ID = inv.INV_SL_ID
            LEFT JOIN {$this->t('TMS_ORDERING_ITEMS')} ordi ON ordi.ORDI_ID = inv.INV_ORDI_ID
            LEFT JOIN {$this->t('TMS_ORDERING')} ord ON ord.ORD_ID = ordi.ORDI_ORD_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts ON mlparts.TMLP_ML_ID = ml.ML_ID
            LEFT JOIN {$this->t('MS_PARTS')} part ON part.PART_ID = mlparts.TMLP_PART_ID";

        $where = " WHERE (ml.ML_TYPE = 1 OR ml.ML_TYPE IS NULL OR inv.INV_MLR_ID IS NULL)";

        // Data query - order by ID DESC (terbesar ke terkecil)
        $data_sql = "SELECT 
                        inv.INV_ID,
                        inv.INV_TOOL_TAG,
                        ISNULL(inv.INV_RQ_NO, ord.ORD_RQ_NO) AS RQ_NO,
                        ISNULL(part.PART_NAME, '') AS PRODUCT_NAME,
                        ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                        ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_DRAWING_NO,
                        CASE WHEN inv.INV_RECEIVED_DATE IS NULL THEN '' 
                             ELSE CONVERT(VARCHAR(19), inv.INV_RECEIVED_DATE, 120) END AS RECEIVED_DATE,
                        ISNULL(inv.INV_DO_NO, '') AS DO_NO,
                        inv.INV_TOOL_ID,
                        inv.INV_STATUS,
                        ISNULL(inv.INV_NOTES, '') AS NOTES,
                        ISNULL(sl.SL_NAME, '') AS STORAGE_LOCATION,
                        ISNULL(mat.MAT_NAME, '') AS MATERIAL,
                        inv.INV_TOOL_CONDITION,
                        ISNULL(inv.INV_END_CYCLE, 0) AS END_CYCLE
                    " . $base_from . $where . "
                    ORDER BY inv.INV_ID DESC";

        $result = $this->db_tms->query($data_sql);
        return $result ? $result->result_array() : array();
    }

    /**
     * Get all products from MS_PARTS
     */
    public function get_products()
    {
        $sql = "SELECT PART_ID AS PRODUCT_ID, PART_NAME AS PRODUCT_NAME 
                FROM {$this->t('MS_PARTS')} 
                ORDER BY PART_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get all operations from MS_OPERATION
     */
    public function get_operations()
    {
        $sql = "SELECT OP_ID AS OPERATION_ID, OP_NAME AS OPERATION_NAME 
                FROM {$this->t('MS_OPERATION')} 
                ORDER BY OP_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get all tools from MS_TOOL_CLASS
     */
    public function get_tools()
    {
        $sql = "SELECT TC_ID AS TOOL_ID, TC_NAME AS TOOL_NAME 
                FROM {$this->t('MS_TOOL_CLASS')} 
                ORDER BY TC_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get all storage locations from MS_STORAGE_LOCATION
     */
    public function get_storage_locations()
    {
        $sql = "SELECT SL_ID AS STORAGE_LOCATION_ID, SL_NAME AS STORAGE_LOCATION_NAME 
                FROM {$this->t('MS_STORAGE_LOCATION')} 
                ORDER BY SL_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get storage locations for modal popup (with ID, Name, Description)
     */
    public function get_storage_locations_for_modal()
    {
        $sql = "SELECT 
                    SL_ID AS ID,
                    SL_NAME AS NAME,
                    ISNULL(SL_DESC, '') AS DESCRIPTION
                FROM {$this->t('MS_STORAGE_LOCATION')} 
                ORDER BY SL_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get all materials from MS_MATERIAL
     */
    public function get_materials()
    {
        $sql = "SELECT MAT_ID AS MATERIAL_ID, MAT_NAME AS MATERIAL_NAME 
                FROM {$this->t('MS_MATERIAL')} 
                ORDER BY MAT_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get all makers from MS_MAKER
     */
    public function get_makers()
    {
        $sql = "SELECT MAKER_ID, MAKER_NAME, MAKER_CODE 
                FROM {$this->t('MS_MAKER')} 
                ORDER BY MAKER_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get makers for modal popup (with ID, Name, Description)
     */
    public function get_makers_for_modal()
    {
        $sql = "SELECT 
                    MAKER_ID AS ID,
                    MAKER_NAME AS NAME,
                    ISNULL(MAKER_DESC, '') AS DESCRIPTION,
                    ISNULL(MAKER_CODE, '') AS MAKER_CODE
                FROM {$this->t('MS_MAKER')} 
                ORDER BY MAKER_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get tool drawing numbers from TMS_TOOL_MASTER_LIST (ML_TYPE = 1 for Engineering)
     */
    public function get_tool_drawing_nos()
    {
        $sql = "SELECT DISTINCT ml.ML_ID, ml.ML_TOOL_DRAW_NO, 
                MAX(mlr.MLR_REV) AS MAX_REV,
                mlr.MLR_ID AS LATEST_MLR_ID
                FROM {$this->t('TMS_TOOL_MASTER_LIST')} ml
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ML_ID = ml.ML_ID
                WHERE ml.ML_TYPE = 1
                GROUP BY ml.ML_ID, ml.ML_TOOL_DRAW_NO, mlr.MLR_ID
                ORDER BY ml.ML_TOOL_DRAW_NO ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get tool drawing data for modal popup (with ID, Drawing No., Tool Name, Revision, Description)
     */
    public function get_tool_drawings_for_modal()
    {
        $sql = "SELECT 
                    mlr.MLR_ID AS ID,
                    ml.ML_TOOL_DRAW_NO AS DRAWING_NO,
                    ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                    mlr.MLR_REV AS REVISION,
                    ISNULL(mlr.MLR_DESC, '') AS DESCRIPTION,
                    ml.ML_ID AS ML_ID
                FROM {$this->t('TMS_TOOL_MASTER_LIST')} ml
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ML_ID = ml.ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                WHERE ml.ML_TYPE = 1
                ORDER BY ml.ML_TOOL_DRAW_NO ASC, mlr.MLR_REV DESC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get revisions for a specific Tool Drawing (ML_ID)
     */
    public function get_revisions_by_ml_id($ml_id)
    {
        $ml_id = (int)$ml_id;
        if ($ml_id <= 0) return array();
        
        $sql = "SELECT mlr.MLR_ID, mlr.MLR_REV, mlr.MLR_STATUS
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr
                WHERE mlr.MLR_ML_ID = ?
                ORDER BY mlr.MLR_REV DESC";
        $q = $this->db_tms->query($sql, array($ml_id));
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get Tool Drawing details by MLR_ID (for auto-fill Product, Process, Tool Name, Revision)
     */
    public function get_tool_drawing_details_by_mlr_id($mlr_id)
    {
        $mlr_id = (int)$mlr_id;
        if ($mlr_id <= 0) return null;

        $sql = "SELECT 
                    mlr.MLR_ID,
                    mlr.MLR_REV AS REVISION,
                    mlr.MLR_OP_ID AS PROCESS_ID,
                    mlr.MLR_TC_ID AS TOOL_NAME_ID,
                    (SELECT TOP 1 TMLP_PART_ID FROM {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} WHERE TMLP_ML_ID = ml.ML_ID) AS PRODUCT_ID
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                WHERE mlr.MLR_ID = ?";
        $q = $this->db_tms->query($sql, array($mlr_id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
    }

    /**
     * Get RQ Numbers from TMS_ORDERING or TMS_REQUISITION
     */
    public function get_rq_numbers()
    {
        // Get from TMS_ORDERING
        $sql = "SELECT DISTINCT ORD_RQ_NO AS RQ_NO 
                FROM {$this->t('TMS_ORDERING')} 
                WHERE ORD_RQ_NO IS NOT NULL AND ORD_RQ_NO <> ''
                ORDER BY ORD_RQ_NO DESC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get next Tool Tag number (auto-increment based on INV_ID)
     */
    public function get_next_tool_tag()
    {
        // Get max INV_ID and generate next Tool Tag
        $sql = "SELECT MAX(INV_ID) AS MAX_ID FROM {$this->t('TMS_TOOL_INVENTORY')}";
        $q = $this->db_tms->query($sql);
        if ($q && $q->num_rows() > 0) {
            $row = $q->row_array();
            $max_id = isset($row['MAX_ID']) ? (int)$row['MAX_ID'] : 0;
            return $max_id + 1;
        }
        return 1;
    }

    /**
     * Get existing Tool IDs for dropdown (optimized with limit and latest records first)
     */
    public function get_existing_tool_ids($limit = 500)
    {
        $limit = (int)$limit;
        if ($limit <= 0) $limit = 500;
        
        // Optimized query: Get latest record per Tool ID using subquery
        // This gets the most recent INV_ID for each Tool ID, then joins to get full data
        $sql = "SELECT TOP {$limit}
                    inv.INV_TOOL_ID,
                    inv.INV_MLR_ID,
                    mlr.MLR_OP_ID AS PROCESS_ID,
                    mlr.MLR_TC_ID AS TOOL_NAME_ID,
                    mlr.MLR_REV AS REVISION,
                    ml.ML_ID AS TOOL_DRAWING_ML_ID,
                    (SELECT TOP 1 TMLP_PART_ID FROM {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} WHERE TMLP_ML_ID = ml.ML_ID) AS PRODUCT_ID
                FROM {$this->t('TMS_TOOL_INVENTORY')} inv
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                INNER JOIN (
                    SELECT INV_TOOL_ID, MAX(INV_ID) AS MAX_INV_ID
                    FROM {$this->t('TMS_TOOL_INVENTORY')}
                    WHERE INV_TOOL_ID IS NOT NULL AND INV_TOOL_ID <> ''
                    GROUP BY INV_TOOL_ID
                ) latest ON latest.INV_TOOL_ID = inv.INV_TOOL_ID AND latest.MAX_INV_ID = inv.INV_ID
                WHERE inv.INV_TOOL_ID IS NOT NULL AND inv.INV_TOOL_ID <> ''
                ORDER BY inv.INV_ID DESC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get Tool ID details by Tool ID string
     */
    public function get_tool_id_details($tool_id)
    {
        $tool_id = trim((string)$tool_id);
        if (empty($tool_id)) return null;

        $sql = "SELECT TOP 1
                    inv.INV_TOOL_ID,
                    inv.INV_MLR_ID,
                    mlr.MLR_OP_ID AS PROCESS_ID,
                    mlr.MLR_TC_ID AS TOOL_NAME_ID,
                    mlr.MLR_REV AS REVISION,
                    ml.ML_ID AS TOOL_DRAWING_ML_ID,
                    (SELECT TOP 1 TMLP_PART_ID FROM {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} WHERE TMLP_ML_ID = ml.ML_ID) AS PRODUCT_ID
                FROM {$this->t('TMS_TOOL_INVENTORY')} inv
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                WHERE inv.INV_TOOL_ID = ?
                ORDER BY inv.INV_ID DESC";
        $q = $this->db_tms->query($sql, array($tool_id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
    }

    /**
     * Add new Tool Inventory
     */
    public function add_data($data)
    {
        $this->db_tms->trans_start();

        // Prepare data
        $mlr_id = isset($data['mlr_id']) ? (int)$data['mlr_id'] : 0;
        $tool_id = isset($data['tool_id']) ? trim((string)$data['tool_id']) : '';
        $tool_tag = isset($data['tool_tag']) ? trim((string)$data['tool_tag']) : '';
        $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
        $process_id = isset($data['process_id']) ? (int)$data['process_id'] : 0;
        $tool_name = isset($data['tool_name']) ? (int)$data['tool_name'] : 0;
        $revision = isset($data['revision']) ? (int)$data['revision'] : 0;
        $tool_status = isset($data['tool_status']) ? (int)$data['tool_status'] : 1;
        $storage_location_id = isset($data['storage_location_id']) && $data['storage_location_id'] > 0 ? (int)$data['storage_location_id'] : null;
        $notes = isset($data['notes']) ? trim((string)$data['notes']) : null;
        $rq_no = isset($data['rq_no']) && !empty($data['rq_no']) ? trim((string)$data['rq_no']) : null;
        $maker_id = isset($data['maker_id']) && $data['maker_id'] > 0 ? (int)$data['maker_id'] : null;
        $material_id = isset($data['material_id']) && $data['material_id'] > 0 ? (int)$data['material_id'] : null;
        // INV_PURCHASE_TYPE is bigint in database, but form sends string like 'Local', 'Overseas'
        // Skip this field for now since column type is bigint (likely needs a lookup table)
        $purchase_type = null;
        $do_no = isset($data['do_no']) && !empty($data['do_no']) ? trim((string)$data['do_no']) : null;
        $received_date = isset($data['received_date']) && !empty($data['received_date']) ? trim((string)$data['received_date']) : null;
        $tool_condition = isset($data['tool_condition']) && $data['tool_condition'] !== '' ? (int)$data['tool_condition'] : null;
        $begin_cycle = isset($data['begin_cycle']) && $data['begin_cycle'] !== '' ? (int)$data['begin_cycle'] : null;
        $end_cycle = isset($data['end_cycle']) && $data['end_cycle'] !== '' ? (int)$data['end_cycle'] : null;
        $in_tool_set = isset($data['in_tool_set']) && $data['in_tool_set'] !== '' ? (int)$data['in_tool_set'] : null;
        $assetized = isset($data['assetized']) && $data['assetized'] == '1' ? 1 : 0;

        // Validation
        if ($mlr_id <= 0) {
            $this->messages = 'MLR ID tidak valid.';
            $this->db_tms->trans_rollback();
            return false;
        }

        if (empty($tool_id)) {
            $this->messages = 'Tool ID tidak boleh kosong.';
            $this->db_tms->trans_rollback();
            return false;
        }

        if (empty($tool_tag)) {
            $this->messages = 'Tool Tag tidak boleh kosong.';
            $this->db_tms->trans_rollback();
            return false;
        }

        // Check if Tool ID already exists
        $check_sql = "SELECT INV_ID FROM {$this->t('TMS_TOOL_INVENTORY')} WHERE INV_TOOL_ID = ?";
        $check_q = $this->db_tms->query($check_sql, array($tool_id));
        if ($check_q && $check_q->num_rows() > 0) {
            $this->messages = 'Tool ID sudah digunakan.';
            $this->db_tms->trans_rollback();
            return false;
        }

        // Insert
        $insert_fields = array('INV_TOOL_ID', 'INV_MLR_ID', 'INV_TOOL_TAG', 'INV_STATUS');
        $insert_values = array('?', '?', '?', '?');
        $insert_params = array($tool_id, $mlr_id, $tool_tag, $tool_status);

        if ($storage_location_id !== null) {
            $insert_fields[] = 'INV_SL_ID';
            $insert_values[] = '?';
            $insert_params[] = $storage_location_id;
        }

        if ($notes !== null) {
            $insert_fields[] = 'INV_NOTES';
            $insert_values[] = '?';
            $insert_params[] = $notes;
        }

        if ($rq_no !== null) {
            $insert_fields[] = 'INV_RQ_NO';
            $insert_values[] = '?';
            $insert_params[] = $rq_no;
        }

        if ($maker_id !== null) {
            $insert_fields[] = 'INV_MAKER_ID';
            $insert_values[] = '?';
            $insert_params[] = $maker_id;
        }

        if ($material_id !== null) {
            $insert_fields[] = 'INV_MAT_ID';
            $insert_values[] = '?';
            $insert_params[] = $material_id;
        }

        // INV_PURCHASE_TYPE is bigint in database, so skip if value is string
        // Only insert if purchase_type is numeric (bigint ID)
        if ($purchase_type !== null && is_numeric($purchase_type)) {
            $insert_fields[] = 'INV_PURCHASE_TYPE';
            $insert_values[] = '?';
            $insert_params[] = (int)$purchase_type;
        }

        if ($do_no !== null) {
            $insert_fields[] = 'INV_DO_NO';
            $insert_values[] = '?';
            $insert_params[] = $do_no;
        }

        if ($received_date !== null) {
            $insert_fields[] = 'INV_RECEIVED_DATE';
            $insert_values[] = 'CONVERT(datetime, ?, 120)';
            $insert_params[] = $received_date;
        }

        if ($tool_condition !== null) {
            $insert_fields[] = 'INV_TOOL_CONDITION';
            $insert_values[] = '?';
            $insert_params[] = $tool_condition;
        }

        if ($begin_cycle !== null) {
            $insert_fields[] = 'INV_BEGIN_CYCLE';
            $insert_values[] = '?';
            $insert_params[] = $begin_cycle;
        }

        if ($end_cycle !== null) {
            $insert_fields[] = 'INV_END_CYCLE';
            $insert_values[] = '?';
            $insert_params[] = $end_cycle;
        }

        if ($in_tool_set !== null) {
            $insert_fields[] = 'INV_IN_TOOL_SET';
            $insert_values[] = '?';
            $insert_params[] = $in_tool_set;
        }

        $insert_fields[] = 'INV_ASSETIZED';
        $insert_values[] = '?';
        $insert_params[] = $assetized;

        $insert_sql = "INSERT INTO {$this->t('TMS_TOOL_INVENTORY')} (" . implode(', ', $insert_fields) . ") VALUES (" . implode(', ', $insert_values) . ")";
        $insert_q = $this->db_tms->query($insert_sql, $insert_params);

        if (!$insert_q) {
            $this->db_tms->trans_rollback();
            $err = $this->db_tms->error();
            $this->messages = 'Gagal menambahkan Tool Inventory. ' . (isset($err['message']) ? $err['message'] : '');
            return false;
        }

        $this->db_tms->trans_complete();

        if ($this->db_tms->trans_status()) {
            $this->messages = 'Tool Inventory berhasil ditambahkan.';
            return true;
        }

        $err = $this->db_tms->error();
        $this->messages = 'Gagal menambahkan Tool Inventory. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Delete
     */
    public function delete_data($id)
    {
        $id = (int)$id;
        $row = $this->get_by_id($id);
        if (!$row) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        // Check if tool is in use or has dependencies
        // For now, allow delete - add checks if needed
        
        $sql = "DELETE FROM {$this->t('TMS_TOOL_INVENTORY')} WHERE INV_ID = ?";
        $ok = $this->db_tms->query($sql, array($id));

        if ($ok) {
            $this->messages = 'Tool Inventory berhasil dihapus.';
            return true;
        }
        $err = $this->db_tms->error();
        $this->messages = 'Gagal menghapus. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }
}

