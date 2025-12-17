<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model untuk Tool BOM (Engineering)
 * ML_TYPE = 2 untuk BOM
 */
class M_tool_bom_engin extends CI_Model
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
            1 => 'ml.ML_TOOL_DRAW_NO',
            2 => 'rev.MLR_DESC',
            3 => 'TD_PRODUCT_NAME',
            4 => 'mac.MAC_NAME',
            5 => 'rev.MLR_REV',
            6 => 'rev.MLR_STATUS',
            7 => 'usr.USR_NAME'
        );

        $base_from = "
            FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
            INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = rev.MLR_ML_ID
            LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = rev.MLR_MACG_ID
            LEFT JOIN {$this->t('MS_USERS')} usr ON usr.USR_ID = rev.MLR_MODIFIED_BY";

        $where = " WHERE ml.ML_TYPE = 2";
        $params = array();

        // Global search
        if (!empty($search)) {
            $where .= " AND (ml.ML_TOOL_DRAW_NO LIKE ? OR rev.MLR_DESC LIKE ? OR mac.MAC_NAME LIKE ? 
                        OR usr.USR_NAME LIKE ? OR CAST(rev.MLR_ID AS VARCHAR) LIKE ?)";
            $search_param = '%' . $search . '%';
            $params = array_merge($params, array($search_param, $search_param, $search_param, $search_param, $search_param));
        }

        // Per-column search
        $col_search_map = array(
            0 => 'CAST(rev.MLR_ID AS VARCHAR)',
            1 => 'ml.ML_TOOL_DRAW_NO',
            2 => 'rev.MLR_DESC',
            4 => 'mac.MAC_NAME',
            5 => 'CAST(rev.MLR_REV AS VARCHAR)',
            7 => 'usr.USR_NAME'
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
                        ml.ML_TOOL_DRAW_NO AS TD_TOOL_BOM,
                        ISNULL(rev.MLR_DESC, '') AS TD_DESCRIPTION,
                        ISNULL(dbo.fnGetToolMasterListParts(ml.ML_ID), '') AS TD_PRODUCT_NAME,
                        ISNULL(mac.MAC_NAME, '') AS TD_MACHINE_GROUP,
                        rev.MLR_REV AS TD_REVISION,
                        rev.MLR_STATUS AS TD_STATUS,
                        ISNULL(usr.USR_NAME, '') AS TD_MODIFIED_BY
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
                    ml.ML_TOOL_DRAW_NO AS TD_TOOL_BOM,
                    ISNULL(rev.MLR_DESC, '') AS TD_DESCRIPTION,
                    ISNULL(dbo.fnGetToolMasterListParts(ml.ML_ID), '') AS TD_PRODUCT_NAME,
                    ISNULL(mac.MAC_NAME, '') AS TD_MACHINE_GROUP,
                    rev.MLR_MACG_ID,
                    rev.MLR_REV AS TD_REVISION,
                    rev.MLR_STATUS AS TD_STATUS,
                    ISNULL(usr.USR_NAME, '') AS TD_MODIFIED_BY,
                    CASE WHEN rev.MLR_MODIFIED_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), rev.MLR_MODIFIED_DATE, 120) END AS TD_MODIFIED_DATE,
                    CASE WHEN rev.MLR_EFFECTIVE_DATE IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), rev.MLR_EFFECTIVE_DATE, 120) END AS TD_EFFECTIVE_DATE,
                    rev.MLR_CHANGE_SUMMARY AS TD_CHANGE_SUMMARY
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = rev.MLR_ML_ID
                LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = rev.MLR_MACG_ID
                LEFT JOIN {$this->t('MS_USERS')} usr ON usr.USR_ID = rev.MLR_MODIFIED_BY
                WHERE ml.ML_TYPE = 2 AND rev.MLR_ID = ?";

        $q = $this->db_tms->query($sql, array($id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
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
                    rev.MLR_CHANGE_SUMMARY AS TD_CHANGE_SUMMARY
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} rev
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = rev.MLR_ML_ID
                LEFT JOIN {$this->t('MS_USERS')} usr ON usr.USR_ID = rev.MLR_MODIFIED_BY
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
}

