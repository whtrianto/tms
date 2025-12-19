<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model untuk Tool Sets
 * Uses TMS_NEW database: TMS_TOOLSETS
 */
class M_tool_sets extends CI_Model
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
            0 => 'tset.TSET_ID',
            1 => 'tset.TSET_NAME',
            2 => 'ml.ML_TOOL_DRAW_NO',
            3 => 'ISNULL(part.PART_NAME, \'\')',
            4 => 'mlr.MLR_REV',
            5 => 'tset.TSET_STATUS'
        );

        $base_from = "
            FROM {$this->t('TMS_TOOLSETS')} tset
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = tset.TSET_BOM_MLR_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
            LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts ON mlparts.TMLP_ML_ID = ml.ML_ID
            LEFT JOIN {$this->t('MS_PARTS')} part ON part.PART_ID = mlparts.TMLP_PART_ID";

        $where = " WHERE 1=1";
        $params = array();

        // Global search
        if (!empty($search)) {
            $where .= " AND (
                CAST(tset.TSET_ID AS VARCHAR) LIKE ? OR 
                tset.TSET_NAME LIKE ? OR 
                ml.ML_TOOL_DRAW_NO LIKE ? OR 
                part.PART_NAME LIKE ? OR 
                CAST(mlr.MLR_REV AS VARCHAR) LIKE ? OR 
                CAST(tset.TSET_STATUS AS VARCHAR) LIKE ?
            )";
            $search_param = '%' . $search . '%';
            for ($i = 0; $i < 6; $i++) {
                $params[] = $search_param;
            }
        }

        // Per-column search
        $col_search_map = array(
            0 => 'CAST(tset.TSET_ID AS VARCHAR)',
            1 => 'ISNULL(tset.TSET_NAME, \'\')',
            2 => 'ISNULL(ml.ML_TOOL_DRAW_NO, \'\')',
            3 => 'ISNULL(part.PART_NAME, \'\')',
            4 => 'CAST(ISNULL(mlr.MLR_REV, 0) AS VARCHAR)',
            5 => 'CAST(ISNULL(tset.TSET_STATUS, 0) AS VARCHAR)'
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
        $order_column = isset($columns[$order_col]) ? $columns[$order_col] : 'tset.TSET_ID';
        $order_direction = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';

        // Data query
        $data_sql = "SELECT 
                        tset.TSET_ID,
                        tset.TSET_NAME,
                        ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_BOM,
                        ISNULL(part.PART_NAME, '') AS PRODUCT,
                        ISNULL(mlr.MLR_REV, 0) AS REVISION,
                        tset.TSET_STATUS
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
                    tset.TSET_ID,
                    tset.TSET_NAME,
                    tset.TSET_BOM_MLR_ID,
                    tset.TSET_STATUS,
                    ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_BOM,
                    ISNULL(part.PART_NAME, '') AS PRODUCT,
                    ISNULL(mlr.MLR_REV, 0) AS REVISION
                FROM {$this->t('TMS_TOOLSETS')} tset
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = tset.TSET_BOM_MLR_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts ON mlparts.TMLP_ML_ID = ml.ML_ID
                LEFT JOIN {$this->t('MS_PARTS')} part ON part.PART_ID = mlparts.TMLP_PART_ID
                WHERE tset.TSET_ID = ?";

        $q = $this->db_tms->query($sql, array($id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
    }

    /**
     * Get status name
     */
    public function get_status_name($status)
    {
        $status = (int)$status;
        $status_map = array(
            0 => 'Complete',
            1 => 'Incomplete'
        );
        return isset($status_map[$status]) ? $status_map[$status] : 'Complete';
    }

    /**
     * Get status badge HTML
     */
    public function get_status_badge($status)
    {
        $status = (int)$status;
        $status_name = $this->get_status_name($status);
        // Complete (0) -> badge-success (green), Incomplete (1) -> badge-warning (yellow)
        $badge_class = $status == 0 ? 'badge-success' : 'badge-warning';
        
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

        // Check if toolset is used in TMS_TOOL_ASSIGNMENT
        $check_sql = "SELECT COUNT(*) as cnt FROM {$this->t('TMS_TOOL_ASSIGNMENT')} WHERE TASGN_TSET_ID = ?";
        $check_result = $this->db_tms->query($check_sql, array($id));
        if ($check_result && $check_result->num_rows() > 0) {
            $count = (int)$check_result->row()->cnt;
            if ($count > 0) {
                $this->messages = 'Tool Set tidak dapat dihapus karena masih digunakan dalam Tool Assignment.';
                return false;
            }
        }

        // Check if toolset has compositions
        $check_comp_sql = "SELECT COUNT(*) as cnt FROM {$this->t('TMS_TOOLSET_COMPOSITIONS')} WHERE TSCOMP_TSET_ID = ?";
        $check_comp_result = $this->db_tms->query($check_comp_sql, array($id));
        if ($check_comp_result && $check_comp_result->num_rows() > 0) {
            $count = (int)$check_comp_result->row()->cnt;
            if ($count > 0) {
                // Delete compositions first
                $delete_comp_sql = "DELETE FROM {$this->t('TMS_TOOLSET_COMPOSITIONS')} WHERE TSCOMP_TSET_ID = ?";
                $this->db_tms->query($delete_comp_sql, array($id));
            }
        }
        
        $sql = "DELETE FROM {$this->t('TMS_TOOLSETS')} WHERE TSET_ID = ?";
        $ok = $this->db_tms->query($sql, array($id));

        if ($ok) {
            $this->messages = 'Tool Set berhasil dihapus.';
            return true;
        }
        $err = $this->db_tms->error();
        $this->messages = 'Gagal menghapus. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }
}

