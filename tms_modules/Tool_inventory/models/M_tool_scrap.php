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
            9 => 'scrap.SCRAP_CURRENT_QTY_THIS',
            10 => 'mac.MAC_NAME',
            11 => 'scrap.SCRAP_CAUSE_REMARK',
            12 => 'scrap.SCRAP_COUNTER_MEASURE' // Suggestion - using Counter Measure for now
        );

        $base_from = "
            FROM {$this->t('TMS_TOOL_SCRAP')} scrap
            LEFT JOIN {$this->t('TMS_TOOL_INVENTORY')} inv ON inv.INV_ID = scrap.SCRAP_INV_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
            LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
            LEFT JOIN {$this->t('MS_REASON')} reason ON reason.REASON_ID = scrap.SCRAP_REASON_ID
            LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = scrap.SCRAP_MAC_ID";

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
                CAST(scrap.SCRAP_CURRENT_QTY_THIS AS VARCHAR) LIKE ? OR 
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
            9 => 'CAST(ISNULL(scrap.SCRAP_CURRENT_QTY_THIS, 0) AS VARCHAR)',
            10 => 'ISNULL(mac.MAC_NAME, \'\')',
            11 => 'ISNULL(scrap.SCRAP_CAUSE_REMARK, \'\')',
            12 => 'ISNULL(scrap.SCRAP_COUNTER_MEASURE, \'\')' // Suggestion
        );
        
        foreach ($column_search as $col_idx => $col_val) {
            if (!empty($col_val) && isset($col_search_map[$col_idx])) {
                $where .= " AND " . $col_search_map[$col_idx] . " LIKE ?";
                $params[] = '%' . $col_val . '%';
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
        $order_column = isset($columns[$order_col]) ? $columns[$order_col] : 'scrap.SCRAP_ID';
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
                        CAST(ISNULL(scrap.SCRAP_CURRENT_QTY_THIS, 0) AS VARCHAR) AS PCS_PRODUCED,
                        ISNULL(mac.MAC_NAME, '') AS MACHINE,
                        ISNULL(scrap.SCRAP_CAUSE_REMARK, '') AS CAUSE_REMARK,
                        ISNULL(scrap.SCRAP_COUNTER_MEASURE, '') AS SUGGESTION,
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
                    scrap.SCRAP_MAC_ID,
                    mac.MAC_NAME AS MACHINE,
                    scrap.SCRAP_CAUSE_REMARK,
                    scrap.SCRAP_OPERATOR,
                    scrap.SCRAP_REQUESTED_BY,
                    scrap.SCRAP_APPROVED_BY,
                    scrap.SCRAP_APPROVED_DATE,
                    scrap.SCRAP_DISPOSITION,
                    scrap.SCRAP_TO_ORDER
                FROM {$this->t('TMS_TOOL_SCRAP')} scrap
                LEFT JOIN {$this->t('TMS_TOOL_INVENTORY')} inv ON inv.INV_ID = scrap.SCRAP_INV_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                LEFT JOIN {$this->t('MS_REASON')} reason ON reason.REASON_ID = scrap.SCRAP_REASON_ID
                LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = scrap.SCRAP_MAC_ID
                WHERE scrap.SCRAP_ID = ?";

        $q = $this->db_tms->query($sql, array($id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
    }

    /**
     * Get status name
     * Tool Scrap Status enum (typical values):
     * 0=Pending, 1=Approved, 2=Rejected, etc. (may vary by system)
     */
    public function get_status_name($status)
    {
        $status = (int)$status;
        $status_map = array(
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Rejected',
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
            case 2: // Rejected
                $badge_class = 'badge-danger';
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
}

