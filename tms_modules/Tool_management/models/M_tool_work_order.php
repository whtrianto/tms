<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model untuk Tool Work Order
 */
class M_tool_work_order extends CI_Model
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
            0 => 'wo.WO_ID',
            1 => 'wo.WO_CREATED_DATE',
            2 => 'wo.WO_NO',
            3 => 'wo.WO_TYPE',
            4 => 'req_by.USR_NAME',
            5 => 'inv.INV_TOOL_ID',
            6 => 'ml.ML_TOOL_DRAW_NO',
            7 => 'wo.WO_TARGET_COM_DATE',
            8 => 'wo.WO_ACTUAL_COM_DATE',
            9 => 'wo.WO_STATUS',
            10 => 'wo.WO_REASON',
            11 => 'tool_making_ml.ML_TOOL_DRAW_NO',
            12 => 'ISNULL(ISNULL(rq.RQ_INT_REQ_NO, ord.ORD_RQ_NO), \'\')'
        );

        $base_from = "
            FROM {$this->t('TMS_WORKORDER')} wo
            LEFT JOIN {$this->t('TMS_TOOL_INVENTORY')} inv ON inv.INV_ID = wo.WO_INV_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = wo.WO_MLR_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
            LEFT JOIN {$this->t('MS_USERS')} req_by ON req_by.USR_ID = wo.WO_REQUESTED_BY
            LEFT JOIN {$this->t('TMS_ORDERING_ITEMS')} ordi ON ordi.ORDI_ID = wo.WO_ORDI_ID
            LEFT JOIN {$this->t('TMS_REQUISITION')} rq ON rq.RQ_ID = ordi.ORDI_RQ_ID
            LEFT JOIN {$this->t('TMS_ORDERING')} ord ON ord.ORD_ID = ordi.ORDI_ORD_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} tool_making_mlr ON tool_making_mlr.MLR_ID = wo.WO_MLR_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} tool_making_ml ON tool_making_ml.ML_ID = tool_making_mlr.MLR_ML_ID";

        $where = " WHERE 1=1";
        $params = array();

        // Global search
        if (!empty($search)) {
            $where .= " AND (wo.WO_NO LIKE ? OR req_by.USR_NAME LIKE ? OR inv.INV_TOOL_ID LIKE ? 
                        OR ml.ML_TOOL_DRAW_NO LIKE ? OR CAST(wo.WO_ID AS VARCHAR) LIKE ?)";
            $search_param = '%' . $search . '%';
            $params = array_merge($params, array($search_param, $search_param, $search_param, $search_param, $search_param));
        }

        // Per-column search
        $col_search_map = array(
            0 => 'wo.WO_ID',
            1 => 'wo.WO_CREATED_DATE',
            2 => 'wo.WO_NO',
            3 => 'wo.WO_TYPE',
            4 => 'req_by.USR_NAME',
            5 => 'inv.INV_TOOL_ID',
            6 => 'ml.ML_TOOL_DRAW_NO',
            7 => 'wo.WO_TARGET_COM_DATE',
            8 => 'wo.WO_ACTUAL_COM_DATE',
            9 => 'wo.WO_STATUS',
            10 => 'wo.WO_REASON',
            11 => 'tool_making_ml.ML_TOOL_DRAW_NO',
            12 => 'ISNULL(ISNULL(rq.RQ_INT_REQ_NO, ord.ORD_RQ_NO), \'\')'
        );

        foreach ($column_search as $idx => $val) {
            if (isset($col_search_map[$idx]) && $val !== '') {
                $where .= " AND " . $col_search_map[$idx] . " LIKE ?";
                $params[] = '%' . $val . '%';
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
        $order_column = isset($columns[$order_col]) ? $columns[$order_col] : 'wo.WO_ID';
        $order_direction = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';

        // Data query
        $data_sql = "SELECT 
                        wo.WO_ID,
                        CASE WHEN wo.WO_CREATED_DATE IS NULL THEN '' 
                             ELSE CONVERT(VARCHAR(19), wo.WO_CREATED_DATE, 120) END AS DATE,
                        wo.WO_NO,
                        wo.WO_TYPE,
                        ISNULL(req_by.USR_NAME, '') AS REQUESTED_BY,
                        ISNULL(inv.INV_TOOL_ID, '') AS TOOL_ID,
                        ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_DRAWING_NO,
                        CASE WHEN wo.WO_TARGET_COM_DATE IS NULL THEN '' 
                             ELSE CONVERT(VARCHAR(19), wo.WO_TARGET_COM_DATE, 120) END AS TARGET_COMPLETION_DATE,
                        CASE WHEN wo.WO_ACTUAL_COM_DATE IS NULL THEN '' 
                             ELSE CONVERT(VARCHAR(19), wo.WO_ACTUAL_COM_DATE, 120) END AS ACTUAL_COMPLETION_DATE,
                        wo.WO_STATUS,
                        wo.WO_REASON,
                        ISNULL(tool_making_ml.ML_TOOL_DRAW_NO, '') AS TOOL_MAKING_DRAW_NO,
                        ISNULL(ISNULL(rq.RQ_INT_REQ_NO, ord.ORD_RQ_NO), '') AS RQ_NO
                    " . $base_from . $where . "
                    ORDER BY " . $order_column . " " . $order_direction . "
                    OFFSET " . (int)$start . " ROWS FETCH NEXT " . (int)$length . " ROWS ONLY";

        $data = array();
        try {
            $result = $this->db_tms->query($data_sql, $params);
            
            if (!$result) {
                $error = $this->db_tms->error();
                log_message('error', '[M_tool_work_order::get_data_serverside] SQL Error: ' . (isset($error['message']) ? $error['message'] : 'Unknown error'));
                log_message('error', '[M_tool_work_order::get_data_serverside] SQL: ' . $data_sql);
                return array(
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => array()
                );
            }
            
            $data = $result->result_array();
        } catch (Exception $e) {
            log_message('error', '[M_tool_work_order::get_data_serverside] Exception: ' . $e->getMessage());
            log_message('error', '[M_tool_work_order::get_data_serverside] SQL: ' . $data_sql);
            return array(
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array()
            );
        }

        return array(
            'recordsTotal' => (int)$count_total,
            'recordsFiltered' => (int)$count_filtered,
            'data' => $data
        );
    }

    /**
     * Get WO Type name
     */
    public function get_wo_type_name($type)
    {
        $type = (int)$type;
        $type_map = array(
            1 => 'Repair',
            2 => 'Design Change',
            3 => 'Modification',
            4 => 'Tool Making',
            5 => 'Others'
        );
        return isset($type_map[$type]) ? $type_map[$type] : 'Unknown';
    }

    /**
     * Get WO Reason name
     */
    public function get_wo_reason_name($reason)
    {
        $reason = (int)$reason;
        $reason_map = array(
            1 => 'Accident',
            2 => 'Crack',
            3 => 'Chipping',
            4 => 'Dented',
            5 => 'Wear',
            6 => 'Scratch',
            7 => 'Others',
            8 => 'None'
        );
        return isset($reason_map[$reason]) ? $reason_map[$reason] : '';
    }

    /**
     * Get WO Status name
     */
    public function get_wo_status_name($status)
    {
        $status = (int)$status;
        // Common status values: 1=Open, 2=In Progress, 3=Completed, 4=Cancelled
        $status_map = array(
            1 => 'Open',
            2 => 'In Progress',
            3 => 'Completed',
            4 => 'Cancelled'
        );
        return isset($status_map[$status]) ? $status_map[$status] : 'Unknown';
    }

    /**
     * Get WO Status badge HTML
     */
    public function get_wo_status_badge($status)
    {
        $status = (int)$status;
        $status_name = $this->get_wo_status_name($status);
        
        $badge_class = 'badge-secondary'; // Default
        if ($status === 3) {
            $badge_class = 'badge-success'; // Completed
        } elseif ($status === 2) {
            $badge_class = 'badge-warning'; // In Progress
        } elseif ($status === 4) {
            $badge_class = 'badge-danger'; // Cancelled
        } elseif ($status === 1) {
            $badge_class = 'badge-info'; // Open
        }
        
        return '<span class="badge ' . $badge_class . '">' . htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8') . '</span>';
    }

    /**
     * Delete Work Order
     */
    public function delete_data($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            $this->messages = 'ID tidak valid.';
            return false;
        }

        try {
            $this->db_tms->where('WO_ID', $id);
            $this->db_tms->delete($this->t('TMS_WORKORDER'));
            
            if ($this->db_tms->affected_rows() > 0) {
                $this->messages = 'Work Order berhasil dihapus.';
                return true;
            } else {
                $this->messages = 'Work Order tidak ditemukan atau gagal dihapus.';
                return false;
            }
        } catch (Exception $e) {
            log_message('error', '[M_tool_work_order::delete_data] Exception: ' . $e->getMessage());
            $this->messages = 'Error: ' . $e->getMessage();
            return false;
        }
    }
}

