<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model untuk Tool Scrap
 * Uses TMS_NEW database: TMS_TOOL_SCRAP
 */
class M_tool_scrap extends CI_Model
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
            0 => 'scrap.SCRAP_ID',
            1 => 'scrap.SCRAP_DATE',
            2 => 'scrap.SCRAP_ACC_DATE',
            3 => 'inv.INV_TOOL_ID',
            4 => 'ml.ML_TOOL_DRAW_NO',
            5 => 'tc.TC_NAME',
            6 => 'reason.REASON_NAME',
            7 => 'scrap.SCRAP_STATUS',
            8 => 'scrap.SCRAP_COUNTER_MEASURE',
            9 => 'ISNULL(pcs_produced.PCS_PRODUCED, 0)',
            10 => 'mac.MAC_NAME',
            11 => 'scrap.SCRAP_CAUSE_REMARK',
            12 => '\'Scrap\'' // Suggestion - always "Scrap" (not orderable)
        );

        $base_from = "
            FROM {$this->t('TMS_TOOL_SCRAP')} scrap
            LEFT JOIN {$this->t('TMS_TOOL_INVENTORY')} inv ON inv.INV_ID = scrap.SCRAP_INV_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
            LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
            LEFT JOIN {$this->t('MS_REASON')} reason ON reason.REASON_ID = scrap.SCRAP_REASON_ID
            LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = scrap.SCRAP_MAC_ID
            LEFT JOIN (
                SELECT ASSGN_INV_ID, SUM(ISNULL(ASSGN_QTY_PRODUCED, 0)) AS PCS_PRODUCED
                FROM {$this->t('TMS_ASSIGNED_TOOLS')}
                WHERE ASSGN_INV_ID IS NOT NULL
                GROUP BY ASSGN_INV_ID
            ) pcs_produced ON pcs_produced.ASSGN_INV_ID = scrap.SCRAP_INV_ID";

        $where = " WHERE 1=1";
        $params = array();

        // Global search
        if (!empty($search)) {
            $where .= " AND (
                CAST(scrap.SCRAP_ID AS VARCHAR) LIKE ? OR 
                CONVERT(VARCHAR(19), scrap.SCRAP_DATE, 120) LIKE ? OR 
                CONVERT(VARCHAR(19), scrap.SCRAP_ACC_DATE, 120) LIKE ? OR 
                inv.INV_TOOL_ID LIKE ? OR 
                ml.ML_TOOL_DRAW_NO LIKE ? OR 
                tc.TC_NAME LIKE ? OR 
                reason.REASON_NAME LIKE ? OR 
                CAST(scrap.SCRAP_STATUS AS VARCHAR) LIKE ? OR 
                scrap.SCRAP_COUNTER_MEASURE LIKE ? OR 
                CAST(ISNULL(pcs_produced.PCS_PRODUCED, 0) AS VARCHAR) LIKE ? OR 
                mac.MAC_NAME LIKE ? OR 
                scrap.SCRAP_CAUSE_REMARK LIKE ?
            )";
            $search_param = '%' . $search . '%';
            for ($i = 0; $i < 12; $i++) {
                $params[] = $search_param;
            }
        }

        // Per-column search
        $col_search_map = array(
            0 => 'CAST(scrap.SCRAP_ID AS VARCHAR)',
            1 => 'ISNULL(CONVERT(VARCHAR(19), scrap.SCRAP_DATE, 120), \'\')',
            2 => 'ISNULL(CONVERT(VARCHAR(19), scrap.SCRAP_ACC_DATE, 120), \'\')',
            3 => 'ISNULL(inv.INV_TOOL_ID, \'\')',
            4 => 'ISNULL(ml.ML_TOOL_DRAW_NO, \'\')',
            5 => 'ISNULL(tc.TC_NAME, \'\')',
            6 => 'ISNULL(reason.REASON_NAME, \'\')',
            7 => 'CAST(ISNULL(scrap.SCRAP_STATUS, 0) AS VARCHAR)',
            8 => 'ISNULL(scrap.SCRAP_COUNTER_MEASURE, \'\')',
            9 => 'CAST(ISNULL(pcs_produced.PCS_PRODUCED, 0) AS VARCHAR)',
            10 => 'ISNULL(mac.MAC_NAME, \'\')',
            11 => 'ISNULL(scrap.SCRAP_CAUSE_REMARK, \'\')',
            12 => '\'Scrap\'' // Suggestion - always "Scrap", search for "Scrap" text
        );
        
        foreach ($column_search as $col_idx => $col_val) {
            if (!empty($col_val) && isset($col_search_map[$col_idx])) {
                if ($col_idx == 12) {
                    // Special handling for Suggestion column - search for "Scrap"
                    if (stripos($col_val, 'Scrap') !== false || stripos($col_val, 'scrap') !== false) {
                        // Always match if searching for "Scrap"
                        // No WHERE clause needed as all rows will have 'Scrap'
                    } else {
                        // If searching for something else, no match
                        $where .= " AND 1=0";
                    }
                } else {
                    $where .= " AND " . $col_search_map[$col_idx] . " LIKE ?";
                    $params[] = '%' . $col_val . '%';
                }
            }
        }

        // Count total
        $count_total_sql = "SELECT COUNT(*) as cnt " . $base_from;
        $count_total_result = $this->db_tms->query($count_total_sql);
        $count_total = $count_total_result && $count_total_result->num_rows() > 0 ? $count_total_result->row()->cnt : 0;

        // Count filtered
        $count_filtered_sql = "SELECT COUNT(*) as cnt " . $base_from . $where;
        $count_filtered_result = $this->db_tms->query($count_filtered_sql, $params);
        $count_filtered = $count_filtered_result && $count_filtered_result->num_rows() > 0 ? $count_filtered_result->row()->cnt : 0;

        // Order
        // Special handling: column 12 (Suggestion) is always "Scrap", so order by ID instead
        if ($order_col == 12) {
            $order_column = 'scrap.SCRAP_ID';
        } else {
            $order_column = isset($columns[$order_col]) ? $columns[$order_col] : 'scrap.SCRAP_ID';
        }
        $order_direction = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';

        // Data query
        $data_sql = "SELECT 
                        scrap.SCRAP_ID,
                        CASE WHEN scrap.SCRAP_DATE IS NULL THEN '' 
                             ELSE CONVERT(VARCHAR(19), scrap.SCRAP_DATE, 120) END AS ISSUE_DATE,
                        CASE WHEN scrap.SCRAP_ACC_DATE IS NULL THEN '' 
                             ELSE CONVERT(VARCHAR(19), scrap.SCRAP_ACC_DATE, 120) END AS ACC_SCRAP_DATE,
                        ISNULL(inv.INV_TOOL_ID, '') AS TOOL_ID,
                        ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_DRAWING_NO,
                        ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                        ISNULL(reason.REASON_NAME, '') AS REASON,
                        scrap.SCRAP_STATUS,
                        ISNULL(scrap.SCRAP_COUNTER_MEASURE, '') AS COUNTER_MEASURE,
                        CAST(ISNULL(pcs_produced.PCS_PRODUCED, 0) AS VARCHAR) AS PCS_PRODUCED,
                        ISNULL(mac.MAC_NAME, '') AS MACHINE,
                        ISNULL(scrap.SCRAP_CAUSE_REMARK, '') AS CAUSE_REMARK,
                        'Scrap' AS SUGGESTION,
                        scrap.SCRAP_NO
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
                    scrap.SCRAP_ID,
                    scrap.SCRAP_NO,
                    scrap.SCRAP_DATE,
                    scrap.SCRAP_ACC_DATE,
                    scrap.SCRAP_INV_ID,
                    inv.INV_TOOL_ID,
                    ml.ML_TOOL_DRAW_NO,
                    tc.TC_NAME AS TOOL_NAME,
                    scrap.SCRAP_REASON_ID,
                    reason.REASON_NAME AS REASON,
                    scrap.SCRAP_STATUS,
                    scrap.SCRAP_COUNTER_MEASURE,
                    scrap.SCRAP_CURRENT_QTY_THIS,
                    scrap.SCRAP_STD_QTY_THIS,
                    scrap.SCRAP_NRCV_QTY_THIS,
                    scrap.SCRAP_MAC_ID,
                    mac.MAC_NAME AS MACHINE,
                    scrap.SCRAP_CAUSE_REMARK,
                    scrap.SCRAP_OPERATOR,
                    op_user.USR_NAME AS OPERATOR_NAME,
                    scrap.SCRAP_REQUESTED_BY,
                    req_user.USR_NAME AS REQUESTED_BY_NAME,
                    scrap.SCRAP_APPROVED_BY,
                    app_user.USR_NAME AS APPROVED_BY_NAME,
                    scrap.SCRAP_APPROVED_DATE,
                    scrap.SCRAP_INVESTIGATED_BY,
                    inv_user.USR_NAME AS INVESTIGATED_BY_NAME,
                    scrap.SCRAP_DISPOSITION,
                    scrap.SCRAP_TO_ORDER,
                    scrap.SCRAP_CI_ID,
                    ci.CI_NAME AS CAUSE_NAME,
                    mlr.MLR_PRICE AS TOOL_PRICE,
                    mlr.MLR_SKETCH AS SKETCH,
                    ISNULL(pcs_produced.PCS_PRODUCED, 0) AS PCS_PRODUCED
                FROM {$this->t('TMS_TOOL_SCRAP')} scrap
                LEFT JOIN {$this->t('TMS_TOOL_INVENTORY')} inv ON inv.INV_ID = scrap.SCRAP_INV_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                LEFT JOIN {$this->t('MS_REASON')} reason ON reason.REASON_ID = scrap.SCRAP_REASON_ID
                LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = scrap.SCRAP_MAC_ID
                LEFT JOIN {$this->t('MS_CAUSE_ITEM')} ci ON ci.CI_ID = scrap.SCRAP_CI_ID
                LEFT JOIN {$this->t('MS_USERS')} op_user ON op_user.USR_ID = scrap.SCRAP_OPERATOR
                LEFT JOIN {$this->t('MS_USERS')} req_user ON req_user.USR_ID = scrap.SCRAP_REQUESTED_BY
                LEFT JOIN {$this->t('MS_USERS')} app_user ON app_user.USR_ID = scrap.SCRAP_APPROVED_BY
                LEFT JOIN {$this->t('MS_USERS')} inv_user ON inv_user.USR_ID = scrap.SCRAP_INVESTIGATED_BY
                LEFT JOIN (
                    SELECT ASSGN_INV_ID, SUM(ISNULL(ASSGN_QTY_PRODUCED, 0)) AS PCS_PRODUCED
                    FROM {$this->t('TMS_ASSIGNED_TOOLS')}
                    WHERE ASSGN_INV_ID IS NOT NULL
                    GROUP BY ASSGN_INV_ID
                ) pcs_produced ON pcs_produced.ASSGN_INV_ID = scrap.SCRAP_INV_ID
                WHERE scrap.SCRAP_ID = ?";

        $q = $this->db_tms->query($sql, array($id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
    }

    /**
     * Get status name
     * Tool Scrap Status enum (typical values):
     * 0=Pending, 1=Approved, 2=Closed, etc. (may vary by system)
     */
    public function get_status_name($status)
    {
        $status = (int)$status;
        $status_map = array(
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Closed',
            3 => 'Cancelled'
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
            case 0: // Pending
                $badge_class = 'badge-warning';
                break;
            case 1: // Approved
                $badge_class = 'badge-success';
                break;
            case 2: // Closed
                $badge_class = 'badge-secondary';
                break;
            case 3: // Cancelled
                $badge_class = 'badge-secondary';
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

        // Check if scrap has dependencies (e.g., related requisitions)
        // For now, allow delete - add checks if needed
        
        $sql = "DELETE FROM {$this->t('TMS_TOOL_SCRAP')} WHERE SCRAP_ID = ?";
        $ok = $this->db_tms->query($sql, array($id));

        if ($ok) {
            $this->messages = 'Tool Scrap berhasil dihapus.';
            return true;
        }
        $err = $this->db_tms->error();
        $this->messages = 'Gagal menghapus. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Get users for modal popup (ID, User, Position)
     */
    public function get_users_for_modal()
    {
        $sql = "SELECT TOP 500
                    usr.USR_ID AS ID,
                    usr.USR_NAME AS [USER],
                    ISNULL(pos.POS_NAME, '') AS POSITION
                FROM {$this->t('MS_USERS')} usr
                LEFT JOIN {$this->t('MS_POSITION')} pos ON pos.POS_ID = usr.USR_POS_ID
                ORDER BY usr.USR_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get machines for modal popup (ID, Machine, Operation)
     */
    public function get_machines_for_modal()
    {
        $sql = "SELECT TOP 500
                    mac.MAC_ID AS ID,
                    mac.MAC_NAME AS MACHINE,
                    ISNULL(op.OP_NAME, '') AS OPERATION
                FROM {$this->t('MS_MACHINES')} mac
                LEFT JOIN {$this->t('MS_OPERATION')} op ON op.OP_ID = mac.MAC_OP_ID
                ORDER BY mac.MAC_ID ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get reasons for modal popup (ID, Name, Description)
     */
    public function get_reasons_for_modal()
    {
        $sql = "SELECT TOP 500
                    REASON_ID AS ID,
                    REASON_NAME AS NAME,
                    ISNULL(REASON_CODE, '') AS DESCRIPTION
                FROM {$this->t('MS_REASON')}
                ORDER BY REASON_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get causes for modal popup (ID, Name, Description)
     */
    public function get_causes_for_modal()
    {
        $sql = "SELECT TOP 500
                    CI_ID AS ID,
                    CI_NAME AS NAME,
                    ISNULL(CI_CODE, '') AS DESCRIPTION
                FROM {$this->t('MS_CAUSE_ITEM')}
                ORDER BY CI_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get tool inventory for modal popup (ID, Tool ID, Tool Drawing No, Revision, Tool Name, Tool Status, Remarks)
     */
    public function get_tool_inventory_for_modal()
    {
        $sql = "SELECT TOP 500
                    inv.INV_ID AS ID,
                    inv.INV_TOOL_ID AS TOOL_ID,
                    ml.ML_TOOL_DRAW_NO AS TOOL_DRAWING_NO,
                    mlr.MLR_REV AS REVISION,
                    ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                    CASE inv.INV_STATUS
                        WHEN 1 THEN 'New'
                        WHEN 2 THEN 'Allocated'
                        WHEN 3 THEN 'Available'
                        WHEN 4 THEN 'InUsed'
                        WHEN 5 THEN 'Onhold'
                        WHEN 6 THEN 'Scrapped'
                        WHEN 7 THEN 'Repairing'
                        WHEN 8 THEN 'Modifying'
                        WHEN 9 THEN 'DesignChange'
                        ELSE 'Unknown'
                    END AS TOOL_STATUS,
                    ISNULL(inv.INV_NOTES, '') AS REMARKS
                FROM {$this->t('TMS_TOOL_INVENTORY')} inv
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                WHERE inv.INV_TOOL_ID IS NOT NULL AND inv.INV_TOOL_ID <> ''
                ORDER BY inv.INV_TOOL_ID ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get tool inventory details by Tool ID for auto-fill
     */
    public function get_tool_inventory_details_by_tool_id($tool_id)
    {
        $tool_id = trim((string)$tool_id);
        if (empty($tool_id)) {
            log_message('debug', '[M_tool_scrap::get_tool_inventory_details_by_tool_id] Tool ID is empty');
            return null;
        }

        try {
            // Use escape() method which properly handles SQL Server string escaping
            $tool_id_escaped = $this->db_tms->escape($tool_id);
            
            // First, check if Tool ID exists at all (simple check)
            $check_sql = "SELECT TOP 1 INV_ID, INV_TOOL_ID FROM {$this->t('TMS_TOOL_INVENTORY')} WHERE INV_TOOL_ID = {$tool_id_escaped}";
            $check_q = $this->db_tms->query($check_sql);
            
            if (!$check_q || $check_q->num_rows() == 0) {
                // Tool ID doesn't exist at all
                log_message('debug', '[M_tool_scrap::get_tool_inventory_details_by_tool_id] Tool ID not found in TMS_TOOL_INVENTORY: [' . $tool_id . ']');
                
                // Try to find similar Tool IDs for debugging
                $similar_sql = "SELECT TOP 5 INV_TOOL_ID, LEN(INV_TOOL_ID) AS LEN_ID FROM {$this->t('TMS_TOOL_INVENTORY')} WHERE INV_TOOL_ID LIKE " . $this->db_tms->escape('%' . $tool_id . '%') . " ORDER BY INV_ID DESC";
                $similar_q = $this->db_tms->query($similar_sql);
                $similar_ids = array();
                if ($similar_q && $similar_q->num_rows() > 0) {
                    foreach ($similar_q->result_array() as $row) {
                        $similar_ids[] = $row['INV_TOOL_ID'];
                    }
                }
                log_message('debug', '[M_tool_scrap::get_tool_inventory_details_by_tool_id] Similar Tool IDs found: ' . implode(', ', $similar_ids));
                return null;
            }
            
            // Tool ID exists, now get full details with joins
            $sql = "SELECT TOP 1
                        inv.INV_ID,
                        inv.INV_TOOL_ID AS TOOL_ID,
                        inv.INV_MLR_ID,
                        ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_DRAWING_NO,
                        ISNULL(mlr.MLR_REV, 0) AS REVISION,
                        ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                        ISNULL(tc.TC_ID, 0) AS TOOL_NAME_ID,
                        ISNULL(mat.MAT_NAME, '') AS MATERIAL,
                        ISNULL(mat.MAT_ID, 0) AS MATERIAL_ID,
                        ISNULL(ISNULL(ord.ORD_RQ_NO, inv.INV_RQ_NO), '') AS RQ_NO,
                        ISNULL(mlr.MLR_PRICE, 0) AS TOOL_PRICE,
                        ISNULL(tasgn.TASGN_ASSIGN_NO, '') AS TOOL_ASSIGNMENT_NO,
                        ISNULL(pcs_produced.PCS_PRODUCED, 0) AS PCS_PRODUCED,
                        ISNULL(inv.INV_STATUS, 0) AS STATUS,
                        ISNULL(inv.INV_END_CYCLE, 0) AS END_CYCLE
                    FROM {$this->t('TMS_TOOL_INVENTORY')} inv
                    LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
                    LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                    LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                    LEFT JOIN {$this->t('MS_MATERIAL')} mat ON mat.MAT_ID = inv.INV_MAT_ID
                    LEFT JOIN {$this->t('TMS_ORDERING_ITEMS')} ordi ON ordi.ORDI_ID = inv.INV_ORDI_ID
                    LEFT JOIN {$this->t('TMS_ORDERING')} ord ON ord.ORD_ID = ordi.ORDI_ORD_ID
                    LEFT JOIN {$this->t('TMS_ASSIGNED_TOOLS')} assgn ON assgn.ASSGN_ID = inv.INV_ASSGN_ID
                    LEFT JOIN {$this->t('TMS_TOOL_ASSIGNMENT')} tasgn ON tasgn.TASGN_ID = assgn.ASSGN_TASGN_ID
                    LEFT JOIN (
                        SELECT ASSGN_INV_ID, SUM(ISNULL(ASSGN_QTY_PRODUCED, 0)) AS PCS_PRODUCED
                        FROM {$this->t('TMS_ASSIGNED_TOOLS')}
                        WHERE ASSGN_INV_ID IS NOT NULL
                        GROUP BY ASSGN_INV_ID
                    ) pcs_produced ON pcs_produced.ASSGN_INV_ID = inv.INV_ID
                    WHERE inv.INV_TOOL_ID = {$tool_id_escaped}
                    ORDER BY inv.INV_ID DESC";
            
            log_message('debug', '[M_tool_scrap::get_tool_inventory_details_by_tool_id] Querying Tool ID: [' . $tool_id . '] (length: ' . strlen($tool_id) . ', escaped: ' . $tool_id_escaped . ')');
            
            $q = $this->db_tms->query($sql);
            
            if (!$q) {
                $error = $this->db_tms->error();
                $error_msg = isset($error['message']) ? $error['message'] : 'Unknown error';
                log_message('error', '[M_tool_scrap::get_tool_inventory_details_by_tool_id] Query error: ' . $error_msg . ' - Tool ID: [' . $tool_id . ']');
                log_message('error', '[M_tool_scrap::get_tool_inventory_details_by_tool_id] SQL: ' . $sql);
                return null;
            }
            
            if ($q->num_rows() > 0) {
                $result = $q->row_array();
                log_message('debug', '[M_tool_scrap::get_tool_inventory_details_by_tool_id] Found data for Tool ID: [' . $tool_id . '] - Found TOOL_ID: [' . (isset($result['TOOL_ID']) ? $result['TOOL_ID'] : 'N/A') . ']');
                return $result;
            } else {
                log_message('debug', '[M_tool_scrap::get_tool_inventory_details_by_tool_id] Tool ID exists but no data returned from joined query: [' . $tool_id . ']');
                return null;
            }
        } catch (Exception $e) {
            log_message('error', '[M_tool_scrap::get_tool_inventory_details_by_tool_id] Exception: ' . $e->getMessage() . ' - Tool ID: [' . $tool_id . ']');
            return null;
        }
    }

    /**
     * Get materials for dropdown
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
     * Get RQ Numbers
     */
    public function get_rq_numbers()
    {
        $sql = "SELECT DISTINCT ORD_RQ_NO AS RQ_NO 
                FROM {$this->t('TMS_ORDERING')} 
                WHERE ORD_RQ_NO IS NOT NULL AND ORD_RQ_NO <> ''
                UNION
                SELECT DISTINCT RQ_INT_REQ_NO AS RQ_NO
                FROM {$this->t('TMS_REQUISITION')}
                WHERE RQ_INT_REQ_NO IS NOT NULL AND RQ_INT_REQ_NO <> ''
                ORDER BY RQ_NO DESC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get next Scrap No
     */
    public function get_next_scrap_no()
    {
        $sql = "SELECT MAX(SCRAP_ID) AS MAX_ID FROM {$this->t('TMS_TOOL_SCRAP')}";
        $q = $this->db_tms->query($sql);
        if ($q && $q->num_rows() > 0) {
            $row = $q->row_array();
            $max_id = isset($row['MAX_ID']) ? (int)$row['MAX_ID'] : 0;
            return 'SCRAP-' . str_pad($max_id + 1, 6, '0', STR_PAD_LEFT);
        }
        return 'SCRAP-000001';
    }
}

