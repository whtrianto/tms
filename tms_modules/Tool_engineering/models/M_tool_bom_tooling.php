<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model untuk Tool BOM (Tooling)
 * ML_TYPE = 2 untuk BOM
 */
class M_tool_bom_tooling extends CI_Model
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
            0 => 'rev.MLR_ID',
            1 => 'TD_PRODUCT_NAME',
            2 => 'ml.ML_TOOL_DRAW_NO',
            3 => 'op.OP_NAME',
            4 => 'mac.MAC_NAME',
            5 => 'rev.MLR_REV',
            6 => 'rev.MLR_STATUS',
            7 => 'rev.MLR_EFFECTIVE_DATE',
            8 => 'rev.MLR_MODIFIED_DATE',
            9 => 'usr.USR_NAME',
            10 => 'ml.ML_TRIAL'
        );

        $base_from = "
            FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
            INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = rev.MLR_ML_ID
            LEFT JOIN {$this->t('MS_OPERATION')} op ON op.OP_ID = rev.MLR_OP_ID
            LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = rev.MLR_MACG_ID
            LEFT JOIN {$this->t('MS_USERS')} usr ON usr.USR_ID = rev.MLR_MODIFIED_BY";

        $where = " WHERE ml.ML_TYPE = 2";
        $params = array();

        // Global search
        if (!empty($search)) {
            $where .= " AND (ml.ML_TOOL_DRAW_NO LIKE ? OR rev.MLR_DESC LIKE ? OR mac.MAC_NAME LIKE ? 
                        OR usr.USR_NAME LIKE ? OR op.OP_NAME LIKE ? OR CAST(rev.MLR_ID AS VARCHAR) LIKE ?)";
            $search_param = '%' . $search . '%';
            $params = array_merge($params, array($search_param, $search_param, $search_param, $search_param, $search_param, $search_param));
        }

        // Per-column search
        $col_search_map = array(
            0 => 'CAST(rev.MLR_ID AS VARCHAR)',
            1 => 'dbo.fnGetToolMasterListParts(ml.ML_ID)',
            2 => 'ml.ML_TOOL_DRAW_NO',
            3 => 'op.OP_NAME',
            4 => 'mac.MAC_NAME',
            5 => 'CAST(rev.MLR_REV AS VARCHAR)',
            6 => 'CAST(rev.MLR_STATUS AS VARCHAR)',
            7 => 'CONVERT(VARCHAR(19), rev.MLR_EFFECTIVE_DATE, 120)',
            8 => 'CONVERT(VARCHAR(19), rev.MLR_MODIFIED_DATE, 120)',
            9 => 'usr.USR_NAME',
            10 => 'CAST(ISNULL(ml.ML_TRIAL, 0) AS VARCHAR)'
        );
        foreach ($column_search as $col_idx => $col_val) {
            if (!empty($col_val) && isset($col_search_map[$col_idx])) {
                $where .= " AND " . $col_search_map[$col_idx] . " LIKE ?";
                $params[] = '%' . $col_val . '%';
            }
        }

        // Count total
        $count_total_sql = "SELECT COUNT(*) as cnt FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
                           INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = rev.MLR_ML_ID
                           WHERE ml.ML_TYPE = 2";
        $count_total = $this->db_tms->query($count_total_sql)->row()->cnt;

        // Count filtered
        $count_filtered_sql = "SELECT COUNT(*) as cnt " . $base_from . $where;
        $count_filtered = $this->db_tms->query($count_filtered_sql, $params)->row()->cnt;

        // Order
        $order_column = isset($columns[$order_col]) ? $columns[$order_col] : 'ml.ML_TOOL_DRAW_NO';
        $order_direction = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';

        // Data query - Administrator always at the end
        $data_sql = "SELECT 
                        rev.MLR_ID AS TD_ID,
                        ISNULL(dbo.fnGetToolMasterListParts(ml.ML_ID), '') AS TD_PRODUCT_NAME,
                        ml.ML_TOOL_DRAW_NO AS TD_TOOL_BOM,
                        ISNULL(op.OP_NAME, '') AS TD_PROCESS_NAME,
                        ISNULL(mac.MAC_NAME, '') AS TD_MACHINE_GROUP,
                        rev.MLR_REV AS TD_REVISION,
                        rev.MLR_STATUS AS TD_STATUS,
                        CASE WHEN rev.MLR_EFFECTIVE_DATE IS NULL THEN '' 
                             ELSE CONVERT(VARCHAR(19), rev.MLR_EFFECTIVE_DATE, 120) END AS TD_EFFECTIVE_DATE,
                        CASE WHEN rev.MLR_MODIFIED_DATE IS NULL THEN '' 
                             ELSE CONVERT(VARCHAR(19), rev.MLR_MODIFIED_DATE, 120) END AS TD_MODIFIED_DATE,
                        ISNULL(usr.USR_NAME, '') AS TD_MODIFIED_BY,
                        ISNULL(ml.ML_TRIAL, 0) AS ML_TRIAL
                    " . $base_from . $where . "
                    ORDER BY CASE WHEN usr.USR_NAME = 'Administrator' THEN 1 ELSE 0 END, " . $order_column . " " . $order_direction . "
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
     * Get all Tool BOM
     */
    public function get_all()
    {
        $sql = "SELECT
                    rev.MLR_ID AS TD_ID,
                    ml.ML_TOOL_DRAW_NO AS TD_TOOL_BOM,
                    ISNULL(rev.MLR_DESC, '') AS TD_DESCRIPTION,
                    ISNULL(dbo.fnGetToolMasterListParts(ml.ML_ID), '') AS TD_PRODUCT_NAME,
                    ISNULL(mac.MAC_NAME, '') AS TD_MACHINE_GROUP,
                    rev.MLR_REV AS TD_REVISION,
                    rev.MLR_STATUS AS TD_STATUS,
                    ISNULL(usr.USR_NAME, '') AS TD_MODIFIED_BY,
                    CASE WHEN rev.MLR_MODIFIED_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), rev.MLR_MODIFIED_DATE, 120) END AS TD_MODIFIED_DATE
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = rev.MLR_ML_ID
                LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = rev.MLR_MACG_ID
                LEFT JOIN {$this->t('MS_USERS')} usr ON usr.USR_ID = rev.MLR_MODIFIED_BY
                WHERE ml.ML_TYPE = 2
                ORDER BY rev.MLR_ID DESC";

        $q = $this->db_tms->query($sql);
        return $q ? $q->result_array() : array();
    }

    /**
     * Get by ID
     */
    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;

        $sql = "SELECT
                    rev.MLR_ID AS TD_ID,
                    rev.MLR_ML_ID,
                    rev.MLR_OP_ID,
                    rev.MLR_MACG_ID,
                    ml.ML_TOOL_DRAW_NO AS TD_TOOL_BOM,
                    ISNULL(rev.MLR_DESC, '') AS TD_DESCRIPTION,
                    ISNULL(dbo.fnGetToolMasterListParts(ml.ML_ID), '') AS TD_PRODUCT_NAME,
                    ISNULL(op.OP_NAME, '') AS TD_PROCESS_NAME,
                    ISNULL(mac.MAC_NAME, '') AS TD_MACHINE_GROUP,
                    rev.MLR_REV AS TD_REVISION,
                    rev.MLR_STATUS AS TD_STATUS,
                    ISNULL(usr.USR_NAME, '') AS TD_MODIFIED_BY,
                    CASE WHEN rev.MLR_MODIFIED_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), rev.MLR_MODIFIED_DATE, 120) END AS TD_MODIFIED_DATE,
                    CASE WHEN rev.MLR_EFFECTIVE_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), rev.MLR_EFFECTIVE_DATE, 120) END AS TD_EFFECTIVE_DATE,
                    rev.MLR_CHANGE_SUMMARY AS TD_CHANGE_SUMMARY,
                    ISNULL(rev.MLR_DRAWING, '') AS MLR_DRAWING
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = rev.MLR_ML_ID
                LEFT JOIN {$this->t('MS_OPERATION')} op ON op.OP_ID = rev.MLR_OP_ID
                LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = rev.MLR_MACG_ID
                LEFT JOIN {$this->t('MS_USERS')} usr ON usr.USR_ID = rev.MLR_MODIFIED_BY
                WHERE ml.ML_TYPE = 2 AND rev.MLR_ID = ?";

        $q = $this->db_tms->query($sql, array($id));
        $result = $q && $q->num_rows() > 0 ? $q->row_array() : null;
        
        if (!$result) {
            return null;
        }
        
        // Set default value for IS_TRIAL_BOM (field doesn't exist in table)
        $result['ML_IS_TRIAL_BOM'] = 0;
        
        // Get product ID from TMS_TOOL_MASTER_LIST_PARTS
        if (isset($result['MLR_ML_ID']) && (int)$result['MLR_ML_ID'] > 0) {
            $ml_id = (int)$result['MLR_ML_ID'];
            $part_sql = "SELECT TOP 1 TMLP_PART_ID FROM {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} WHERE TMLP_ML_ID = ?";
            $part_q = $this->db_tms->query($part_sql, array($ml_id));
            if ($part_q && $part_q->num_rows() > 0) {
                $part_row = $part_q->row_array();
                $result['PRODUCT_ID'] = isset($part_row['TMLP_PART_ID']) ? (int)$part_row['TMLP_PART_ID'] : 0;
            } else {
                $result['PRODUCT_ID'] = 0;
            }
        } else {
            $result['PRODUCT_ID'] = 0;
        }
        
        return $result;
    }

    /**
     * Get history
     */
    public function get_history($id)
    {
        $id = (int)$id;
        if ($id <= 0) return array();

        $current = $this->get_by_id($id);
        if (!$current) return array();

        $ml_id = isset($current['MLR_ML_ID']) ? (int)$current['MLR_ML_ID'] : 0;
        if ($ml_id <= 0) return array($current);

        $sql = "SELECT
                    rev.MLR_ID AS TD_ID,
                    ml.ML_TOOL_DRAW_NO AS TD_TOOL_BOM,
                    ISNULL(rev.MLR_DESC, '') AS TD_DESCRIPTION,
                    rev.MLR_REV AS TD_REVISION,
                    rev.MLR_STATUS AS TD_STATUS,
                    ISNULL(usr.USR_NAME, '') AS TD_MODIFIED_BY,
                    CASE WHEN rev.MLR_MODIFIED_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), rev.MLR_MODIFIED_DATE, 120) END AS TD_MODIFIED_DATE,
                    CASE WHEN rev.MLR_EFFECTIVE_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), rev.MLR_EFFECTIVE_DATE, 120) END AS TD_EFFECTIVE_DATE,
                    rev.MLR_CHANGE_SUMMARY AS TD_CHANGE_SUMMARY,
                    ISNULL(dbo.fnGetToolMasterListParts(ml.ML_ID), '') AS TD_PRODUCT_NAME,
                    ISNULL(op.OP_NAME, '') AS TD_PROCESS_NAME,
                    ISNULL(mac.MAC_NAME, '') AS TD_MACHINE_GROUP
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = rev.MLR_ML_ID
                LEFT JOIN {$this->t('MS_USERS')} usr ON usr.USR_ID = rev.MLR_MODIFIED_BY
                LEFT JOIN {$this->t('MS_OPERATION')} op ON op.OP_ID = rev.MLR_OP_ID
                LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = rev.MLR_MACG_ID
                WHERE rev.MLR_ML_ID = ?
                ORDER BY rev.MLR_REV DESC";

        $q = $this->db_tms->query($sql, array($ml_id));
        return $q && $q->num_rows() > 0 ? $q->result_array() : array($current);
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

        $sql = "DELETE FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} WHERE MLR_ID = ?";
        $ok = $this->db_tms->query($sql, array($id));

        if ($ok) {
            $this->messages = 'Tool BOM berhasil dihapus.';
            return true;
        }
        $err = $this->db_tms->error();
        $this->messages = 'Gagal menghapus. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Get machines
     */
    public function get_machines()
    {
        $sql = "SELECT MAC_ID, MAC_NAME FROM {$this->t('MS_MACHINES')} ORDER BY MAC_NAME";
        $q = $this->db_tms->query($sql);
        return $q ? $q->result_array() : array();
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
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
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
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
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
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
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
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
    }

    /**
     * Get all makers from MS_MAKER
     */
    public function get_makers()
    {
        $sql = "SELECT MAKER_ID, MAKER_NAME 
                FROM {$this->t('MS_MAKER')} 
                ORDER BY MAKER_NAME ASC";
        $q = $this->db_tms->query($sql);
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
    }

    /**
     * Get all machine groups from MS_MACHINES
     */
    public function get_machine_groups()
    {
        $sql = "SELECT MAC_ID AS MACHINE_ID, MAC_NAME AS MACHINE_NAME 
                FROM {$this->t('MS_MACHINES')} 
                ORDER BY MAC_NAME ASC";
        $q = $this->db_tms->query($sql);
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
    }

    /**
     * Get additional information (Tool Drawing Engineering members) for BOM
     * Returns Tool Drawing Engineering data that are children of this BOM
     */
    public function get_additional_info($bom_mlr_id)
    {
        $bom_mlr_id = (int)$bom_mlr_id;
        if ($bom_mlr_id <= 0) return array();

        $sql = "
            SELECT 
                child_rev.MLR_ID AS TD_ID,
                child_ml.ML_TOOL_DRAW_NO AS TD_DRAWING_NO,
                ISNULL(tc.TC_NAME, '') AS TD_TOOL_NAME,
                child_rev.MLR_REV AS TD_REVISION,
                child_rev.MLR_STATUS AS TD_STATUS,
                ISNULL(members.TB_QTY, 0) AS TD_MIN_QTY,
                ISNULL(members.TB_STD_QTY, 0) AS TD_REPLENISH_QTY,
                ISNULL(members.TB_SEQ, 0) AS TD_SEQUENCE,
                ISNULL(child_rev.MLR_DESC, '') AS TD_DESCRIPTION,
                ISNULL(part.PART_ID, 0) AS TD_PRODUCT_ID,
                ISNULL(op.OP_ID, 0) AS TD_PROCESS_ID,
                ISNULL(mat.MAT_ID, 0) AS TD_MATERIAL_ID,
                ISNULL(maker.MAKER_ID, 0) AS TD_MAKER_ID
            FROM {$this->t('TMS_TOOL_MASTER_LIST_MEMBERS')} members
            INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} child_rev 
                ON child_rev.MLR_ID = members.TB_MLR_CHILD_ID
            INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} child_ml 
                ON child_ml.ML_ID = child_rev.MLR_ML_ID
            LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc 
                ON tc.TC_ID = child_rev.MLR_TC_ID
            LEFT JOIN {$this->t('MS_OPERATION')} op 
                ON op.OP_ID = child_rev.MLR_OP_ID
            LEFT JOIN {$this->t('MS_MATERIAL')} mat 
                ON mat.MAT_ID = child_rev.MLR_MAT_ID
            LEFT JOIN {$this->t('MS_MAKER')} maker 
                ON maker.MAKER_ID = child_rev.MLR_MAKER_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts 
                ON mlparts.TMLP_ML_ID = child_ml.ML_ID
            LEFT JOIN {$this->t('MS_PARTS')} part 
                ON part.PART_ID = mlparts.TMLP_PART_ID
            WHERE members.TB_MLR_PARENT_ID = ?
              AND child_ml.ML_TYPE = 1
            ORDER BY members.TB_SEQ ASC, child_ml.ML_TOOL_DRAW_NO ASC
        ";

        $q = $this->db_tms->query($sql, array($bom_mlr_id));
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }
}

