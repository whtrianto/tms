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
        // Common status values: 1=Open, 2=In Progress, 3=Completed/Closed, 4=Cancelled
        $status_map = array(
            1 => 'Open',
            2 => 'In Progress',
            3 => 'Closed',
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
            $badge_class = 'badge-success'; // Closed
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
     * Get Work Order by ID
     */
    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;

        $sql = "SELECT 
                    wo.WO_ID,
                    wo.WO_INV_ID,
                    wo.WO_MLR_ID,
                    wo.WO_NO,
                    wo.WO_TYPE,
                    CASE WHEN wo.WO_CREATED_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), wo.WO_CREATED_DATE, 120) END AS DATE,
                    wo.WO_CREATED_BY,
                    wo.WO_REQUESTED_BY,
                    wo.WO_DEPARTMENT,
                    wo.WO_REASON,
                    CASE WHEN wo.WO_TARGET_COM_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), wo.WO_TARGET_COM_DATE, 120) END AS TARGET_COMPLETION_DATE,
                    CASE WHEN wo.WO_ACTUAL_COM_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), wo.WO_ACTUAL_COM_DATE, 120) END AS ACTUAL_COMPLETION_DATE,
                    wo.WO_STATUS,
                    ISNULL(wo.WO_CONDITION, 0) AS WO_CONDITION,
                    wo.WO_QTY,
                    wo.WO_URGENCY,
                    wo.WO_REMARKS,
                    ISNULL(created_by.USR_NAME, '') AS CREATED_BY_NAME,
                    ISNULL(req_by.USR_NAME, '') AS REQUESTED_BY_NAME,
                    ISNULL(inv.INV_TOOL_ID, '') AS TOOL_ID,
                    ISNULL(inv.INV_TOOL_TAG, '') AS TOOL_TAG,
                    ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_DRAWING_NO,
                    ISNULL(mlr.MLR_REV, 0) AS REVISION,
                    ISNULL(tc.TC_NAME, '') AS TOOL_NAME
                FROM {$this->t('TMS_WORKORDER')} wo
                LEFT JOIN {$this->t('TMS_TOOL_INVENTORY')} inv ON inv.INV_ID = wo.WO_INV_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = wo.WO_MLR_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                LEFT JOIN {$this->t('MS_USERS')} created_by ON created_by.USR_ID = wo.WO_CREATED_BY
                LEFT JOIN {$this->t('MS_USERS')} req_by ON req_by.USR_ID = wo.WO_REQUESTED_BY
                WHERE wo.WO_ID = ?";

        $q = $this->db_tms->query($sql, array($id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
    }

    /**
     * Get External Costs for Work Order
     */
    public function get_external_costs($wo_id)
    {
        $wo_id = (int)$wo_id;
        if ($wo_id <= 0) return array();

        $sql = "SELECT 
                    ext.EXTCOST_ID,
                    ext.EXTCOST_WO_ID,
                    ext.EXTCOST_WA_ID,
                    ext.EXTCOST_SUP_ID,
                    ext.EXTCOST_SUP_UNIT_PRICE,
                    ext.EXTCOST_SUP_QTY,
                    CASE WHEN ext.EXTCOST_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), ext.EXTCOST_DATE, 120) END AS DATE,
                    ISNULL(ext.EXTCOST_PO_NO, '') AS PO_NO,
                    ISNULL(ext.EXTCOST_INVOICE_NO, '') AS INVOICE_NO,
                    ISNULL(ext.EXTCOST_RF_NO, '') AS RF_NO,
                    CASE WHEN ext.EXTCOST_GRN_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), ext.EXTCOST_GRN_DATE, 120) END AS GRN_DATE,
                    ISNULL(ext.EXTCOST_GRN_NO, '') AS GRN_NO,
                    ISNULL(wa.WA_NAME, '') AS ACTIVITY,
                    ISNULL(sup.SUP_NAME, '') AS SUPPLIER,
                    (ISNULL(ext.EXTCOST_SUP_UNIT_PRICE, 0) * ISNULL(ext.EXTCOST_SUP_QTY, 0)) AS SUB_TOTAL
                FROM {$this->t('TMS_WO_EXTERNAL_COSTS')} ext
                LEFT JOIN {$this->t('TMS_WORK_ACTIVITIES')} wa ON wa.WA_ID = ext.EXTCOST_WA_ID
                LEFT JOIN {$this->t('MS_SUPPLIER')} sup ON sup.SUP_ID = ext.EXTCOST_SUP_ID
                WHERE ext.EXTCOST_WO_ID = ?
                ORDER BY ext.EXTCOST_ID ASC";

        $q = $this->db_tms->query($sql, array($wo_id));
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get Work Activities
     */
    public function get_work_activities()
    {
        $sql = "SELECT WA_ID, WA_NAME, WA_DESC 
                FROM {$this->t('TMS_WORK_ACTIVITIES')} 
                ORDER BY WA_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get Suppliers
     */
    public function get_suppliers()
    {
        $sql = "SELECT SUP_ID, SUP_NAME 
                FROM {$this->t('MS_SUPPLIER')} 
                ORDER BY SUP_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get Users with Position
     */
    public function get_users()
    {
        $sql = "SELECT 
                    usr.USR_ID,
                    usr.USR_NAME,
                    ISNULL(pos.POS_NAME, '') AS POSITION
                FROM {$this->t('MS_USERS')} usr
                LEFT JOIN {$this->t('MS_POSITION')} pos ON pos.POS_ID = usr.USR_POS_ID
                ORDER BY usr.USR_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get Departments
     */
    public function get_departments()
    {
        $sql = "SELECT 
                    DEPART_ID,
                    DEPART_NAME,
                    ISNULL(DEPART_DESC, '') AS DESCRIPTION
                FROM {$this->t('MS_DEPARTMENT')} 
                ORDER BY DEPART_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get External Cost by ID
     */
    public function get_external_cost_by_id($extcost_id)
    {
        $extcost_id = (int)$extcost_id;
        if ($extcost_id <= 0) return null;

        $sql = "SELECT 
                    ext.EXTCOST_ID,
                    ext.EXTCOST_WO_ID,
                    ext.EXTCOST_WA_ID,
                    ext.EXTCOST_SUP_ID,
                    ext.EXTCOST_SUP_UNIT_PRICE,
                    ext.EXTCOST_SUP_QTY,
                    CASE WHEN ext.EXTCOST_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), ext.EXTCOST_DATE, 120) END AS DATE,
                    ISNULL(ext.EXTCOST_PO_NO, '') AS PO_NO,
                    ISNULL(ext.EXTCOST_INVOICE_NO, '') AS INVOICE_NO,
                    ISNULL(ext.EXTCOST_RF_NO, '') AS RF_NO,
                    CASE WHEN ext.EXTCOST_GRN_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), ext.EXTCOST_GRN_DATE, 120) END AS GRN_DATE,
                    ISNULL(ext.EXTCOST_GRN_NO, '') AS GRN_NO,
                    ISNULL(wa.WA_NAME, '') AS ACTIVITY,
                    ISNULL(sup.SUP_NAME, '') AS SUPPLIER
                FROM {$this->t('TMS_WO_EXTERNAL_COSTS')} ext
                LEFT JOIN {$this->t('TMS_WORK_ACTIVITIES')} wa ON wa.WA_ID = ext.EXTCOST_WA_ID
                LEFT JOIN {$this->t('MS_SUPPLIER')} sup ON sup.SUP_ID = ext.EXTCOST_SUP_ID
                WHERE ext.EXTCOST_ID = ?";

        $q = $this->db_tms->query($sql, array($extcost_id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
    }

    /**
     * Save External Cost (Add/Edit)
     */
    public function save_external_cost($action, $extcost_id, $wo_id, $wa_id, $sup_id, $unit_price, $qty, $date, $po_no, $invoice_no, $rf_no, $grn_date, $grn_no)
    {
        $wo_id = (int)$wo_id;
        $wa_id = (int)$wa_id;
        if ($wo_id <= 0 || $wa_id <= 0) {
            $this->messages = 'WO_ID dan Activity harus diisi.';
            return false;
        }

        try {
            $data = array(
                'EXTCOST_WO_ID' => $wo_id,
                'EXTCOST_WA_ID' => $wa_id,
                'EXTCOST_SUP_ID' => $sup_id > 0 ? $sup_id : null,
                'EXTCOST_SUP_UNIT_PRICE' => $unit_price > 0 ? $unit_price : null,
                'EXTCOST_SUP_QTY' => $qty > 0 ? $qty : null,
                'EXTCOST_PO_NO' => $po_no ?: null,
                'EXTCOST_INVOICE_NO' => $invoice_no ?: null,
                'EXTCOST_RF_NO' => $rf_no ?: null,
                'EXTCOST_GRN_NO' => $grn_no ?: null
            );

            if (!empty($date)) {
                $data['EXTCOST_DATE'] = $date;
            } else {
                $data['EXTCOST_DATE'] = null;
            }

            if (!empty($grn_date)) {
                $data['EXTCOST_GRN_DATE'] = $grn_date;
            } else {
                $data['EXTCOST_GRN_DATE'] = null;
            }

            if ($action === 'EDIT' && $extcost_id > 0) {
                $this->db_tms->where('EXTCOST_ID', $extcost_id);
                $this->db_tms->update($this->t('TMS_WO_EXTERNAL_COSTS'), $data);

                if ($this->db_tms->affected_rows() >= 0) {
                    $this->messages = 'External Cost berhasil diupdate.';
                    return true;
                } else {
                    $this->messages = 'Gagal mengupdate External Cost.';
                    return false;
                }
            } else {
                $this->db_tms->insert($this->t('TMS_WO_EXTERNAL_COSTS'), $data);

                if ($this->db_tms->affected_rows() > 0) {
                    $this->messages = 'External Cost berhasil ditambahkan.';
                    return true;
                } else {
                    $this->messages = 'Gagal menambahkan External Cost.';
                    return false;
                }
            }
        } catch (Exception $e) {
            log_message('error', '[M_tool_work_order::save_external_cost] Exception: ' . $e->getMessage());
            $this->messages = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Delete External Cost
     */
    public function delete_external_cost($extcost_id)
    {
        $extcost_id = (int)$extcost_id;
        if ($extcost_id <= 0) {
            $this->messages = 'ID tidak valid.';
            return false;
        }

        try {
            $this->db_tms->where('EXTCOST_ID', $extcost_id);
            $this->db_tms->delete($this->t('TMS_WO_EXTERNAL_COSTS'));

            if ($this->db_tms->affected_rows() > 0) {
                $this->messages = 'External Cost berhasil dihapus.';
                return true;
            } else {
                $this->messages = 'External Cost tidak ditemukan atau gagal dihapus.';
                return false;
            }
        } catch (Exception $e) {
            log_message('error', '[M_tool_work_order::delete_external_cost] Exception: ' . $e->getMessage());
            $this->messages = 'Error: ' . $e->getMessage();
            return false;
        }
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

    /**
     * Add new Work Order
     */
    public function add_data($data)
    {
        try {
            $this->db_tms->trans_start();

            // Generate WO_NO
            $wo_no = $this->generate_wo_no();

            // Map WO_CONDITION from string to integer
            $condition_map = array(
                '' => null,
                'None' => 0,
                'O K Modified' => 1,
                'N G Needs Repair' => 2,
                'N G Not Repairable' => 3,
                'O K Repaired' => 4
            );
            $wo_condition = null;
            if (isset($data['WO_CONDITION']) && !empty($data['WO_CONDITION'])) {
                $condition_str = trim($data['WO_CONDITION']);
                if (isset($condition_map[$condition_str])) {
                    $wo_condition = $condition_map[$condition_str];
                } elseif (is_numeric($condition_str)) {
                    $wo_condition = (int)$condition_str;
                }
            }

            // Validate required fields
            if (!isset($data['WO_REQUESTED_BY']) || $data['WO_REQUESTED_BY'] <= 0) {
                $this->messages = 'WO_REQUESTED_BY is required and cannot be NULL.';
                return false;
            }
            
            // Prepare insert data
            $insert_data = array(
                'WO_NO' => $wo_no,
                'WO_TYPE' => isset($data['WO_TYPE']) ? (int)$data['WO_TYPE'] : 1, // Default: 1 (Repair)
                'WO_CREATED_DATE' => isset($data['WO_CREATED_DATE']) && !empty($data['WO_CREATED_DATE']) ? $data['WO_CREATED_DATE'] : date('Y-m-d'),
                'WO_REQUESTED_BY' => (int)$data['WO_REQUESTED_BY'], // Required, already validated above
                'WO_DEPARTMENT' => isset($data['WO_DEPARTMENT']) && !empty($data['WO_DEPARTMENT']) ? trim($data['WO_DEPARTMENT']) : null,
                'WO_REASON' => isset($data['WO_REASON']) && $data['WO_REASON'] > 0 ? (int)$data['WO_REASON'] : null,
                'WO_REMARKS' => isset($data['WO_REMARKS']) && !empty($data['WO_REMARKS']) ? trim($data['WO_REMARKS']) : null,
                'WO_QTY' => isset($data['WO_QTY']) && $data['WO_QTY'] > 0 ? (int)$data['WO_QTY'] : 1,
                'WO_TARGET_COM_DATE' => isset($data['WO_TARGET_COM_DATE']) && !empty($data['WO_TARGET_COM_DATE']) ? $data['WO_TARGET_COM_DATE'] : null,
                'WO_ACTUAL_COM_DATE' => isset($data['WO_ACTUAL_COM_DATE']) && !empty($data['WO_ACTUAL_COM_DATE']) ? $data['WO_ACTUAL_COM_DATE'] : null,
                'WO_STATUS' => isset($data['WO_STATUS']) && $data['WO_STATUS'] > 0 ? (int)$data['WO_STATUS'] : 1, // Default: 1 (Open)
                'WO_CONDITION' => $wo_condition,
                'WO_URGENCY' => isset($data['WO_URGENCY']) && !empty($data['WO_URGENCY']) ? trim($data['WO_URGENCY']) : null,
                'WO_INV_ID' => isset($data['WO_INV_ID']) && $data['WO_INV_ID'] > 0 ? (int)$data['WO_INV_ID'] : null,
                'WO_CREATED_BY' => ($this->uid && is_numeric($this->uid)) ? (int)$this->uid : 1 // Default to user ID 1 if not set
            );

            $this->db_tms->insert($this->t('TMS_WORKORDER'), $insert_data);

            $this->db_tms->trans_complete();

            if ($this->db_tms->trans_status() === FALSE) {
                $this->messages = 'Gagal menambahkan Work Order.';
                return false;
            }

            $this->messages = 'Work Order berhasil ditambahkan.';
            return true;
        } catch (Exception $e) {
            log_message('error', '[M_tool_work_order::add_data] Exception: ' . $e->getMessage());
            $this->messages = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Update Work Order
     */
    public function update_data($id, $data)
    {
        try {
            $id = (int)$id;
            if ($id <= 0) {
                $this->messages = 'Work Order ID tidak valid.';
                return false;
            }

            // Check if Work Order exists
            $existing = $this->get_by_id($id);
            if (!$existing) {
                $this->messages = 'Work Order tidak ditemukan.';
                return false;
            }

            $this->db_tms->trans_start();

            // Prepare update data
            $update_data = array();

            if (isset($data['WO_CREATED_DATE']) && !empty($data['WO_CREATED_DATE'])) {
                $update_data['WO_CREATED_DATE'] = $data['WO_CREATED_DATE'];
            }

            if (isset($data['WO_REQUESTED_BY'])) {
                $update_data['WO_REQUESTED_BY'] = $data['WO_REQUESTED_BY'] > 0 ? (int)$data['WO_REQUESTED_BY'] : null;
            }

            if (isset($data['WO_DEPARTMENT'])) {
                $update_data['WO_DEPARTMENT'] = !empty($data['WO_DEPARTMENT']) ? trim($data['WO_DEPARTMENT']) : null;
            }

            if (isset($data['WO_REMARKS'])) {
                $update_data['WO_REMARKS'] = !empty($data['WO_REMARKS']) ? trim($data['WO_REMARKS']) : null;
            }

            if (isset($data['WO_QTY']) && $data['WO_QTY'] > 0) {
                $update_data['WO_QTY'] = (int)$data['WO_QTY'];
            }

            if (isset($data['WO_TARGET_COM_DATE'])) {
                $update_data['WO_TARGET_COM_DATE'] = !empty($data['WO_TARGET_COM_DATE']) ? $data['WO_TARGET_COM_DATE'] : null;
            }

            if (isset($data['WO_ACTUAL_COM_DATE'])) {
                $update_data['WO_ACTUAL_COM_DATE'] = !empty($data['WO_ACTUAL_COM_DATE']) ? $data['WO_ACTUAL_COM_DATE'] : null;
            }

            if (isset($data['WO_CONDITION'])) {
                // Map WO_CONDITION from string to integer
                $condition_map = array(
                    '' => null,
                    'None' => 0,
                    'O K Modified' => 1,
                    'N G Needs Repair' => 2,
                    'N G Not Repairable' => 3,
                    'O K Repaired' => 4
                );
                $condition_str = !empty($data['WO_CONDITION']) ? trim($data['WO_CONDITION']) : '';
                if (isset($condition_map[$condition_str])) {
                    $update_data['WO_CONDITION'] = $condition_map[$condition_str];
                } elseif (is_numeric($condition_str)) {
                    $update_data['WO_CONDITION'] = (int)$condition_str;
                } else {
                    $update_data['WO_CONDITION'] = null;
                }
            }

            if (isset($data['WO_URGENCY'])) {
                $update_data['WO_URGENCY'] = !empty($data['WO_URGENCY']) ? trim($data['WO_URGENCY']) : null;
            }

            if (empty($update_data)) {
                $this->messages = 'Tidak ada data yang diupdate.';
                $this->db_tms->trans_rollback();
                return false;
            }

            // Update data
            $this->db_tms->where('WO_ID', $id);
            $this->db_tms->update($this->t('TMS_WORKORDER'), $update_data);

            $this->db_tms->trans_complete();

            if ($this->db_tms->trans_status() === FALSE) {
                $this->messages = 'Gagal mengupdate Work Order.';
                return false;
            }

            $this->messages = 'Work Order berhasil diupdate.';
            return true;
        } catch (Exception $e) {
            log_message('error', '[M_tool_work_order::update_data] Exception: ' . $e->getMessage());
            $this->messages = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Generate Work Order Number
     */
    private function generate_wo_no()
    {
        $date = date('dmy');
        $sql = "SELECT TOP 1 WO_NO FROM {$this->t('TMS_WORKORDER')} WHERE WO_NO LIKE ? ORDER BY WO_ID DESC";
        $q = $this->db_tms->query($sql, array('W-' . $date . '-%'));

        $seq = 1;
        if ($q && $q->num_rows() > 0) {
            $last_no = $q->row()->WO_NO;
            $parts = explode('-', $last_no);
            if (count($parts) >= 3) {
                $last_seq = (int)$parts[2];
                $seq = $last_seq + 1;
            }
        }

        return 'W-' . $date . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
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
                        ISNULL(inv.INV_NOTES, '') AS REMARKS,
                        ISNULL(inv.INV_TOOL_TAG, '') AS TOOL_TAG,
                        inv.INV_MLR_ID AS MLR_ID
                    FROM {$this->t('TMS_TOOL_INVENTORY')} inv
                    INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
                    INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                    LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                    WHERE inv.INV_TOOL_ID IS NOT NULL AND LTRIM(RTRIM(inv.INV_TOOL_ID)) <> ''
                    ORDER BY inv.INV_TOOL_ID ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get tool inventory details by Tool ID for auto-fill
     * PHP 5.6.36 & CI3 compatible
     * Using direct query from TMS_TOOL_INVENTORY for better compatibility
     */
    public function get_tool_inventory_details_by_tool_id($tool_id)
    {
        $tool_id = trim((string)$tool_id);
        if (empty($tool_id)) {
            log_message('debug', '[M_tool_work_order::get_tool_inventory_details_by_tool_id] Tool ID is empty');
            return null;
        }

        // Escape tool_id for SQL Server
        $escaped_tool_id = $this->db_tms->escape($tool_id);

        try {
            // Direct query from TMS_TOOL_INVENTORY (more reliable for PHP 5.6.36)
            $sql = "SELECT TOP 1
                        inv.INV_ID,
                        inv.INV_TOOL_ID AS TOOL_ID,
                        ISNULL(inv.INV_TOOL_TAG, '') AS TOOL_TAG,
                        ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_DRAWING_NO,
                        ISNULL(mlr.MLR_REV, 0) AS REVISION,
                        ISNULL(tc.TC_NAME, '') AS TOOL_NAME
                    FROM {$this->t('TMS_TOOL_INVENTORY')} inv
                    INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
                    INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                    LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                    WHERE LTRIM(RTRIM(inv.INV_TOOL_ID)) = " . $escaped_tool_id . "
                    ORDER BY inv.INV_ID DESC";

            log_message('debug', '[M_tool_work_order::get_tool_inventory_details_by_tool_id] Querying Tool ID: [' . $tool_id . ']');
            log_message('debug', '[M_tool_work_order::get_tool_inventory_details_by_tool_id] SQL: ' . $sql);
            $q = $this->db_tms->query($sql);

            if (!$q) {
                $error = $this->db_tms->error();
                $error_msg = (isset($error['message']) && $error['message']) ? $error['message'] : 'Unknown error';
                log_message('error', '[M_tool_work_order::get_tool_inventory_details_by_tool_id] Query error: ' . $error_msg . ' - Tool ID: [' . $tool_id . ']');
                return null;
            }

            if ($q->num_rows() > 0) {
                $result = $q->row_array();

                // Ensure all fields are set with default values (PHP 5.6.36 compatible)
                if (!isset($result['TOOL_TAG']) || $result['TOOL_TAG'] === null) {
                    $result['TOOL_TAG'] = '';
                }
                if (!isset($result['TOOL_DRAWING_NO']) || $result['TOOL_DRAWING_NO'] === null) {
                    $result['TOOL_DRAWING_NO'] = '';
                }
                if (!isset($result['REVISION']) || $result['REVISION'] === null) {
                    $result['REVISION'] = '0';
                }
                if (!isset($result['TOOL_NAME']) || $result['TOOL_NAME'] === null) {
                    $result['TOOL_NAME'] = '';
                }

                log_message('debug', '[M_tool_work_order::get_tool_inventory_details_by_tool_id] Found Tool ID: [' . $tool_id . ']');
                log_message('debug', '[M_tool_work_order::get_tool_inventory_details_by_tool_id] TOOL_TAG: [' . $result['TOOL_TAG'] . '], TOOL_DRAWING_NO: [' . $result['TOOL_DRAWING_NO'] . '], REVISION: [' . $result['REVISION'] . '], TOOL_NAME: [' . $result['TOOL_NAME'] . ']');

                return $result;
            } else {
                log_message('debug', '[M_tool_work_order::get_tool_inventory_details_by_tool_id] Tool ID not found: [' . $tool_id . ']');
                return null;
            }
        } catch (Exception $e) {
            log_message('error', '[M_tool_work_order::get_tool_inventory_details_by_tool_id] Exception: ' . $e->getMessage());
            return null;
        }
    }
}
