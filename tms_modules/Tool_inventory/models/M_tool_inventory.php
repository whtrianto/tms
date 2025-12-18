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
            14 => 'inv.INV_END_CYCLE'
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
                    ISNULL(inv.INV_END_CYCLE, 0) AS END_CYCLE,
                    inv.INV_MLR_ID,
                    inv.INV_SL_ID,
                    inv.INV_MAT_ID,
                    inv.INV_MAKER_ID,
                    inv.INV_BEGIN_CYCLE
                FROM {$this->t('TMS_TOOL_INVENTORY')} inv
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                LEFT JOIN {$this->t('MS_MATERIAL')} mat ON mat.MAT_ID = inv.INV_MAT_ID
                LEFT JOIN {$this->t('MS_STORAGE_LOCATION')} sl ON sl.SL_ID = inv.INV_SL_ID
                LEFT JOIN {$this->t('TMS_ORDERING_ITEMS')} ordi ON ordi.ORDI_ID = inv.INV_ORDI_ID
                LEFT JOIN {$this->t('TMS_ORDERING')} ord ON ord.ORD_ID = ordi.ORDI_ORD_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts ON mlparts.TMLP_ML_ID = ml.ML_ID
                LEFT JOIN {$this->t('MS_PARTS')} part ON part.PART_ID = mlparts.TMLP_PART_ID
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

