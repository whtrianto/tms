<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model untuk Reports
 */
class M_reports extends CI_Model
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
     * Get list of available reports
     * Returns array of report information
     */
    public function get_reports_list()
    {
        $reports = array(
            array(
                'id' => 1,
                'name' => 'All Tool List',
                'type' => 'Report',
                'description' => 'Complete list of all tools',
                'url' => base_url('Reports/all_tool_list'),
                'icon' => 'fa-file-text'
            ),
            array(
                'id' => 2,
                'name' => 'All Tool List_Old',
                'type' => 'Report',
                'description' => 'Legacy tool list report',
                'url' => base_url('Reports/all_tool_list_old'),
                'icon' => 'fa-file-text'
            ),
            array(
                'id' => 3,
                'name' => 'ReOrder Point',
                'type' => 'Report',
                'description' => 'Reorder point analysis report',
                'url' => base_url('Reports/reorder_point'),
                'icon' => 'fa-file-text'
            ),
            array(
                'id' => 4,
                'name' => 'Tool Scrap Report',
                'type' => 'Report',
                'description' => 'Detailed tool scrap report',
                'url' => base_url('Tool_inventory/tool_scrap'),
                'icon' => 'fa-file-text'
            ),
            array(
                'id' => 5,
                'name' => 'Tool Scrap Summary Report',
                'type' => 'Report',
                'description' => 'Summary of tool scrap data',
                'url' => base_url('Reports/tool_scrap_summary'),
                'icon' => 'fa-file-text'
            ),
            array(
                'id' => 6,
                'name' => 'TOOLSET PM BM',
                'type' => 'Report',
                'description' => 'Toolset PM BM report',
                'url' => base_url('Reports/toolset_pm_bm'),
                'icon' => 'fa-file-text'
            )
        );

        return $reports;
    }

    /**
     * Search reports by name
     * @param string $search_term
     * @return array
     */
    public function search_reports($search_term = '')
    {
        $all_reports = $this->get_reports_list();
        
        if (empty($search_term)) {
            return $all_reports;
        }

        $search_term = strtolower(trim($search_term));
        $filtered = array();

        foreach ($all_reports as $report) {
            if (strpos(strtolower($report['name']), $search_term) !== false ||
                strpos(strtolower($report['description']), $search_term) !== false) {
                $filtered[] = $report;
            }
        }

        return $filtered;
    }

    /**
     * Get report by name
     * @param string $report_name
     * @return array|null
     */
    public function get_report_by_name($report_name)
    {
        $reports = $this->get_reports_list();
        
        foreach ($reports as $report) {
            if ($report['name'] === $report_name) {
                return $report;
            }
        }

        return null;
    }

    /**
     * Get all tool list data for report
     * Optimized version using LEFT JOIN instead of function calls
     * @param array $filters
     * @return array
     */
    public function get_all_tool_list($filters = array())
    {
        $sql = "SELECT 
                    ROW_NUMBER() OVER (ORDER BY ml.ML_TOOL_DRAW_NO, mlr.MLR_REV) AS ROW_NUM,
                    ml.ML_TOOL_DRAW_NO AS TOOL_DRAWING,
                    mlr.MLR_REV AS REVISION,
                    ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                    ISNULL(mlr.MLR_MIN_QTY, 0) AS STD_QTY,
                    ISNULL(qty_onhand.QTY_ON_HAND, 0) AS QTY_ON_HAND,
                    ISNULL(qty_onhold.QTY_ONHOLD, 0) AS QTY_ONHOLD,
                    (ISNULL(mlr.MLR_MIN_QTY, 0) - ISNULL(qty_onhand.QTY_ON_HAND, 0) - ISNULL(qty_onhold.QTY_ONHOLD, 0)) AS DIFFERENCE,
                    ISNULL(mlr.MLR_REPLENISH_QTY, 0) AS REPLENISH_QTY
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                LEFT JOIN (
                    SELECT 
                        INV_MLR_ID,
                        COUNT(INV_ID) AS QTY_ON_HAND
                    FROM {$this->t('TMS_TOOL_INVENTORY')}
                    WHERE INV_STATUS NOT IN (6, 2)
                    GROUP BY INV_MLR_ID
                ) qty_onhand ON qty_onhand.INV_MLR_ID = mlr.MLR_ID
                LEFT JOIN (
                    SELECT 
                        INV_MLR_ID,
                        COUNT(INV_ID) AS QTY_ONHOLD
                    FROM {$this->t('TMS_TOOL_INVENTORY')}
                    WHERE INV_STATUS = 5
                    GROUP BY INV_MLR_ID
                ) qty_onhold ON qty_onhold.INV_MLR_ID = mlr.MLR_ID
                WHERE ml.ML_TYPE = 1
                ORDER BY ml.ML_TOOL_DRAW_NO, mlr.MLR_REV";
        
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get all tool list data for old report (optimized version)
     * Uses returnInventoryActQty logic: COUNT where INV_STATUS <> 6 (excludes scrapped only)
     * @param array $filters
     * @return array
     */
    public function get_all_tool_list_old($filters = array())
    {
        $sql = "SELECT 
                    ROW_NUMBER() OVER (ORDER BY ml.ML_TOOL_DRAW_NO, mlr.MLR_REV) AS ROW_NUM,
                    ml.ML_TOOL_DRAW_NO AS TOOL_DRAWING,
                    mlr.MLR_REV AS REVISION,
                    ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                    COALESCE(usage_qty.MAX_REQUIRED, mlr.MLR_MIN_QTY, 0) AS STD_QTY,
                    ISNULL(qty_onhand.QTY_ON_HAND, 0) AS QTY_ON_HAND,
                    (ISNULL(qty_onhand.QTY_ON_HAND, 0) - COALESCE(usage_qty.MAX_REQUIRED, mlr.MLR_MIN_QTY, 0)) AS DIFFERENCE,
                    ISNULL(mlr.MLR_REPLENISH_QTY, 0) AS REPLENISH_QTY
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                LEFT JOIN (
                    SELECT 
                        INV_MLR_ID,
                        COUNT(INV_ID) AS QTY_ON_HAND
                    FROM {$this->t('TMS_TOOL_INVENTORY')}
                    WHERE INV_STATUS <> 6
                    GROUP BY INV_MLR_ID
                ) qty_onhand ON qty_onhand.INV_MLR_ID = mlr.MLR_ID
                LEFT JOIN (
                    SELECT 
                        TB_MLR_CHILD_ID,
                        MAX(TB_QTY) AS MAX_REQUIRED
                    FROM {$this->t('TMS_TOOL_MASTER_LIST_MEMBERS')}
                    GROUP BY TB_MLR_CHILD_ID
                ) usage_qty ON usage_qty.TB_MLR_CHILD_ID = mlr.MLR_ID
                WHERE ml.ML_TYPE = 1
                ORDER BY ml.ML_TOOL_DRAW_NO, mlr.MLR_REV";
        
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get reorder point data
     * Shows tools with their standard quantity, on-hand quantity, difference, and replenish quantity
     * Uses returnInventoryTACIQtyOnHand logic: COUNT where INV_STATUS NOT IN (6,2) - excludes scrapped and allocated
     * @param array $filters
     * @return array
     */
    public function get_reorder_point_data($filters = array())
    {
        $sql = "SELECT 
                    ROW_NUMBER() OVER (ORDER BY ml.ML_TOOL_DRAW_NO, mlr.MLR_REV) AS ROW_NUM,
                    ml.ML_TOOL_DRAW_NO AS TOOL_DRAWING,
                    mlr.MLR_REV AS REVISION,
                    ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                    ISNULL(mlr.MLR_MIN_QTY, 0) AS STD_QTY_OP,
                    ISNULL(qty_onhand.QTY_ON_HAND, 0) AS QTY_ON_HAND,
                    (ISNULL(mlr.MLR_MIN_QTY, 0) - ISNULL(qty_onhand.QTY_ON_HAND, 0)) AS DIFFERENCE,
                    ISNULL(mlr.MLR_REPLENISH_QTY, 0) AS REPLENISH_QTY_OQ
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                LEFT JOIN (
                    SELECT 
                        INV_MLR_ID,
                        COUNT(INV_ID) AS QTY_ON_HAND
                    FROM {$this->t('TMS_TOOL_INVENTORY')}
                    WHERE INV_STATUS NOT IN (6, 2)
                    GROUP BY INV_MLR_ID
                ) qty_onhand ON qty_onhand.INV_MLR_ID = mlr.MLR_ID
                WHERE ml.ML_TYPE = 1
                ORDER BY ml.ML_TOOL_DRAW_NO, mlr.MLR_REV";
        
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get tool scrap summary data
     * @param array $filters - Contains: date_from, date_to, reason_id
     * @return array
     */
    public function get_tool_scrap_summary($filters = array())
    {
        $date_from = isset($filters['date_from']) ? trim($filters['date_from']) : '';
        $date_to = isset($filters['date_to']) ? trim($filters['date_to']) : '';
        $reason_id = isset($filters['reason_id']) ? (int)$filters['reason_id'] : 0;
        
        $sql = "SELECT 
                    scrap.SCRAP_DATE,
                    dbo.fnGetToolMasterListParts(ml.ML_ID) AS PRODUCT,
                    ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                    ISNULL(inv.INV_TOOL_ID, '') AS TOOL_ID,
                    1 AS QTY,
                    ISNULL(reason.REASON_NAME, '') AS REASON,
                    ISNULL(mlr.MLR_PRICE, ISNULL(inv.INV_TOOL_COST, 0)) AS RM,
                    ISNULL(pcs_produced.PCS_PRODUCED, 0) AS PCS_PRODUCED
                FROM {$this->t('TMS_TOOL_SCRAP')} scrap
                LEFT JOIN {$this->t('TMS_TOOL_INVENTORY')} inv ON inv.INV_ID = scrap.SCRAP_INV_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                LEFT JOIN {$this->t('MS_REASON')} reason ON reason.REASON_ID = scrap.SCRAP_REASON_ID
                LEFT JOIN (
                    SELECT ASSGN_INV_ID, SUM(ISNULL(ASSGN_QTY_PRODUCED, 0)) AS PCS_PRODUCED
                    FROM {$this->t('TMS_ASSIGNED_TOOLS')}
                    WHERE ASSGN_INV_ID IS NOT NULL
                    GROUP BY ASSGN_INV_ID
                ) pcs_produced ON pcs_produced.ASSGN_INV_ID = scrap.SCRAP_INV_ID
                WHERE 1=1";
        
        $params = array();
        
        // Filter by date from
        if (!empty($date_from)) {
            $sql .= " AND CAST(scrap.SCRAP_DATE AS DATE) >= CAST(? AS DATE)";
            $params[] = $date_from;
        }
        
        // Filter by date to
        if (!empty($date_to)) {
            $sql .= " AND CAST(scrap.SCRAP_DATE AS DATE) <= CAST(? AS DATE)";
            $params[] = $date_to;
        }
        
        // Filter by reason
        if ($reason_id > 0) {
            $sql .= " AND scrap.SCRAP_REASON_ID = ?";
            $params[] = $reason_id;
        }
        
        $sql .= " ORDER BY scrap.SCRAP_DATE DESC, inv.INV_TOOL_ID ASC";
        
        $q = $this->db_tms->query($sql, $params);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }
    
    /**
     * Get all reasons for dropdown (only active/non-deleted)
     * Filters out deleted reasons based on IS_DELETED or IS_DELETE column
     * @return array
     */
    public function get_all_reasons()
    {
        // Check which deleted column exists (IS_DELETED or IS_DELETE)
        $check_sql = "SELECT COLUMN_NAME 
                      FROM INFORMATION_SCHEMA.COLUMNS 
                      WHERE TABLE_SCHEMA = 'dbo' 
                      AND TABLE_NAME = 'MS_REASON' 
                      AND COLUMN_NAME IN ('IS_DELETED', 'IS_DELETE')";
        
        $check_q = $this->db_tms->query($check_sql);
        $deleted_col = 'IS_DELETED'; // default
        if ($check_q && $check_q->num_rows() > 0) {
            $cols = $check_q->result_array();
            // Prefer IS_DELETED, fallback to IS_DELETE
            $found_is_deleted = false;
            foreach ($cols as $col) {
                if ($col['COLUMN_NAME'] === 'IS_DELETED') {
                    $found_is_deleted = true;
                    break;
                }
            }
            if (!$found_is_deleted && count($cols) > 0) {
                $deleted_col = $cols[0]['COLUMN_NAME'];
            }
        }
        
        $sql = "SELECT 
                    REASON_ID AS ID,
                    REASON_NAME AS NAME
                FROM {$this->t('MS_REASON')}
                WHERE {$deleted_col} = 0
                ORDER BY REASON_NAME ASC";
        
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get toolset PM BM data
     * @param array $filters
     * @return array
     */
    public function get_toolset_pm_bm_data($filters = array())
    {
        // TODO: Implement query to get toolset PM BM data
        return array();
    }

    /**
     * Get toolsets for dropdown (from TMS_TOOLSETS)
     * @return array
     */
    public function get_toolsets_dropdown()
    {
        $sql = "SELECT 
                    tset.TSET_ID,
                    tset.TSET_NAME,
                    ISNULL(ml.ML_TOOL_DRAW_NO, '') AS DRAWING_NO,
                    ISNULL(mlr.MLR_DESC, '') AS DESCRIPTION,
                    ISNULL(mac.MAC_NAME, '') AS MACHINE_NAME,
                    ISNULL(part.PART_NAME, '') AS PRODUCT_NAME
                FROM {$this->t('TMS_TOOLSETS')} tset
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = tset.TSET_BOM_MLR_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = mlr.MLR_MACG_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts ON mlparts.TMLP_ML_ID = ml.ML_ID
                LEFT JOIN {$this->t('MS_PARTS')} part ON part.PART_ID = mlparts.TMLP_PART_ID
                ORDER BY tset.TSET_NAME ASC";

        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get toolset parts composition for Parts Over Lifetime
     * @param int $tset_id
     * @return array
     */
    public function get_toolset_parts($tset_id)
    {
        $sql = "SELECT
                    ISNULL(inv.INV_TOOL_ID, '') AS TOOL_ID,
                    ISNULL(mlr.MLR_STD_TL_LIFE, '') AS STD_LIFE,
                    ISNULL(inv.INV_END_CYCLE, 0) AS END_CYCLE
                FROM {$this->t('TMS_TOOLSET_COMPOSITIONS')} tscomp
                LEFT JOIN {$this->t('TMS_TOOL_INVENTORY')} inv ON inv.INV_ID = tscomp.TSCOMP_INV_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = tscomp.TSCOMP_MLR_ID
                WHERE tscomp.TSCOMP_TSET_ID = ?
                ORDER BY tscomp.TSCOMP_ID ASC";
        
        $q = $this->db_tms->query($sql, array($tset_id));
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }
}

