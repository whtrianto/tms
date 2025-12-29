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
     * Source: TMS_TOOLSETS, TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, TMS_TOOL_MASTER_LIST_PARTS, MS_PARTS
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
        $order_column = isset($columns[$order_col]) ? $columns[$order_col] : 'tset.TSET_NAME';
        $order_direction = strtoupper($order_dir) === 'ASC' ? 'ASC' : (strtoupper($order_dir) === 'DESC' ? 'DESC' : 'ASC');

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
     * Source: TMS_TOOLSETS, TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_OPERATION, TMS_TOOL_MASTER_LIST_PARTS, MS_PARTS
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
                    ISNULL(mlr.MLR_DESC, '') AS TOOL_BOM_DESC,
                    ISNULL(part.PART_NAME, '') AS PRODUCT,
                    ISNULL(mlr.MLR_REV, 0) AS REVISION,
                    ISNULL(op.OP_NAME, '') AS PROCESS
                FROM {$this->t('TMS_TOOLSETS')} tset
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = tset.TSET_BOM_MLR_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_OPERATION')} op ON op.OP_ID = mlr.MLR_OP_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts ON mlparts.TMLP_ML_ID = ml.ML_ID
                LEFT JOIN {$this->t('MS_PARTS')} part ON part.PART_ID = mlparts.TMLP_PART_ID
                WHERE tset.TSET_ID = ?";

        $q = $this->db_tms->query($sql, array($id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
    }

    /**
     * Get Toolset Compositions
     * Source: TMS_TOOLSET_COMPOSITIONS, TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_TOOL_CLASS, TMS_TOOL_INVENTORY
     */
    public function get_compositions($tset_id)
    {
        $tset_id = (int)$tset_id;
        if ($tset_id <= 0) return array();

        $sql = "SELECT 
                    tscomp.TSCOMP_ID,
                    tscomp.TSCOMP_INV_ID,
                    tscomp.TSCOMP_MLR_ID,
                    tscomp.TSCOMP_STD_REQ,
                    tscomp.TSCOMP_REMARKS,
                    ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_DRAWING_NO,
                    ISNULL(mlr.MLR_REV, 0) AS REVISION,
                    ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                    ISNULL(inv.INV_TOOL_ID, '') AS TOOL_ID,
                    ISNULL(mlr.MLR_STD_TL_LIFE, '') AS STANDARD_TOOL_LIFE,
                    ISNULL(inv.INV_END_CYCLE, 0) AS END_CYCLE,
                    inv.INV_STATUS AS TOOL_STATUS
                FROM {$this->t('TMS_TOOLSET_COMPOSITIONS')} tscomp
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = tscomp.TSCOMP_MLR_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                LEFT JOIN {$this->t('TMS_TOOL_INVENTORY')} inv ON inv.INV_ID = tscomp.TSCOMP_INV_ID
                WHERE tscomp.TSCOMP_TSET_ID = ?
                ORDER BY tscomp.TSCOMP_ID ASC";

        $q = $this->db_tms->query($sql, array($tset_id));
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get Usage Assignments
     * Source: TMS_TOOL_ASSIGNMENT, MS_OPERATION, MS_MACHINES, MS_PARTS
     */
    public function get_usage_assignments($tset_id)
    {
        $tset_id = (int)$tset_id;
        if ($tset_id <= 0) return array();

        $sql = "SELECT 
                    tasgn.TASGN_ID,
                    ISNULL(op.OP_NAME, '') AS OPERATION_NAME,
                    ISNULL(mac.MAC_NAME, '') AS MACHINE_NAME,
                    ISNULL(part.PART_NAME, '') AS PRODUCT_NAME,
                    CASE WHEN tasgn.TASGN_PROD_START IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), tasgn.TASGN_PROD_START, 120) END AS PRODUCTION_START,
                    CASE WHEN tasgn.TASGN_PROD_END IS NULL THEN '' 
                         ELSE CONVERT(VARCHAR(19), tasgn.TASGN_PROD_END, 120) END AS PRODUCTION_END,
                    tasgn.TASGN_LOT_PRODUCED AS USAGE,
                    ISNULL(tasgn.TASGN_REMARKS, '') AS REMARKS
                FROM {$this->t('TMS_TOOL_ASSIGNMENT')} tasgn
                LEFT JOIN {$this->t('MS_OPERATION')} op ON op.OP_ID = tasgn.TASGN_OP_ID
                LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = tasgn.TASGN_MAC_ID
                LEFT JOIN {$this->t('MS_PARTS')} part ON part.PART_ID = tasgn.TASGN_PART_ID
                WHERE tasgn.TASGN_TSET_ID = ?
                ORDER BY tasgn.TASGN_ID DESC";

        $q = $this->db_tms->query($sql, array($tset_id));
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get status name
     * Source: (No database query - returns static mapping)
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
     * Source: (No database query - returns HTML string)
     */
    public function get_status_badge($status)
    {
        $status = (int)$status;
        $status_name = $this->get_status_name($status);
        
        // Complete -> badge-success (green), Incomplete -> badge-warning (yellow)
        if ($status_name === 'Complete') {
            $badge_class = 'badge-success'; // Hijau untuk Complete
        } else {
            $badge_class = 'badge-warning'; // Kuning untuk Incomplete
        }
        
        return '<span class="badge ' . $badge_class . '">' . htmlspecialchars($status_name, ENT_QUOTES, 'UTF-8') . '</span>';
    }

    /**
     * Add new Tool Set
     * Source: TMS_TOOLSETS, TMS_TOOL_MASTER_LIST_REV
     */
    public function add_data($data)
    {
        try {
            $this->db_tms->trans_start();

            // Validate required fields
            if (empty($data['TSET_NAME'])) {
                $this->messages = 'Toolset Name tidak boleh kosong.';
                return false;
            }

            if (empty($data['TSET_BOM_MLR_ID']) || (int)$data['TSET_BOM_MLR_ID'] <= 0) {
                $this->messages = 'Tool BOM MLR ID tidak valid.';
                return false;
            }

            // Check if Tool BOM MLR_ID exists
            $check_sql = "SELECT COUNT(*) as cnt FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} WHERE MLR_ID = ?";
            $check_result = $this->db_tms->query($check_sql, array((int)$data['TSET_BOM_MLR_ID']));
            if (!$check_result || $check_result->num_rows() == 0 || (int)$check_result->row()->cnt == 0) {
                $this->messages = 'Tool BOM tidak ditemukan.';
                return false;
            }

            // Insert data
            $insert_data = array(
                'TSET_NAME' => trim($data['TSET_NAME']),
                'TSET_BOM_MLR_ID' => (int)$data['TSET_BOM_MLR_ID'],
                'TSET_STATUS' => isset($data['TSET_STATUS']) ? (int)$data['TSET_STATUS'] : 1 // Default: 1 (Incomplete)
            );

            $this->db_tms->insert($this->t('TMS_TOOLSETS'), $insert_data);

            $this->db_tms->trans_complete();

            if ($this->db_tms->trans_status() === FALSE) {
                $this->messages = 'Gagal menambahkan Tool Set.';
                return false;
            }

            $this->messages = 'Tool Set berhasil ditambahkan.';
            return true;
        } catch (Exception $e) {
            log_message('error', '[M_tool_sets::add_data] Exception: ' . $e->getMessage());
            $this->messages = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Update Tool Set
     * Source: TMS_TOOLSETS
     */
    public function update_data($id, $data)
    {
        try {
            $id = (int)$id;
            if ($id <= 0) {
                $this->messages = 'Tool Set ID tidak valid.';
                return false;
            }

            // Check if Tool Set exists
            $existing = $this->get_by_id($id);
            if (!$existing) {
                $this->messages = 'Tool Set tidak ditemukan.';
                return false;
            }

            $this->db_tms->trans_start();

            // Prepare update data
            $update_data = array();
            
            if (isset($data['TSET_NAME'])) {
                $update_data['TSET_NAME'] = trim($data['TSET_NAME']);
                if (empty($update_data['TSET_NAME'])) {
                    $this->messages = 'Toolset Name tidak boleh kosong.';
                    $this->db_tms->trans_rollback();
                    return false;
                }
            }

            if (isset($data['TSET_STATUS'])) {
                $update_data['TSET_STATUS'] = (int)$data['TSET_STATUS'];
            }

            if (empty($update_data)) {
                $this->messages = 'Tidak ada data yang diupdate.';
                $this->db_tms->trans_rollback();
                return false;
            }

            // Update data
            $this->db_tms->where('TSET_ID', $id);
            $this->db_tms->update($this->t('TMS_TOOLSETS'), $update_data);

            $this->db_tms->trans_complete();

            if ($this->db_tms->trans_status() === FALSE) {
                $this->messages = 'Gagal mengupdate Tool Set.';
                return false;
            }

            $this->messages = 'Tool Set berhasil diupdate.';
            return true;
        } catch (Exception $e) {
            log_message('error', '[M_tool_sets::update_data] Exception: ' . $e->getMessage());
            $this->messages = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Delete
     * Source: TMS_TOOLSETS, TMS_TOOL_ASSIGNMENT, TMS_TOOLSET_COMPOSITIONS
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

    /**
     * Get Composition by ID
     * Source: TMS_TOOLSET_COMPOSITIONS, TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_TOOL_CLASS, TMS_TOOL_INVENTORY
     */
    public function get_composition_by_id($comp_id)
    {
        $comp_id = (int)$comp_id;
        if ($comp_id <= 0) return null;

        $sql = "SELECT 
                    tscomp.TSCOMP_ID,
                    tscomp.TSCOMP_TSET_ID,
                    tscomp.TSCOMP_INV_ID,
                    tscomp.TSCOMP_MLR_ID,
                    tscomp.TSCOMP_STD_REQ,
                    tscomp.TSCOMP_REMARKS,
                    ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_DRAWING_NO,
                    ISNULL(mlr.MLR_REV, 0) AS REVISION,
                    ISNULL(tc.TC_NAME, '') AS TOOL_NAME,
                    ISNULL(inv.INV_TOOL_ID, '') AS TOOL_ID,
                    ISNULL(mlr.MLR_STD_TL_LIFE, '') AS STANDARD_TOOL_LIFE,
                    ISNULL(inv.INV_END_CYCLE, 0) AS END_CYCLE,
                    inv.INV_STATUS AS TOOL_STATUS
                FROM {$this->t('TMS_TOOLSET_COMPOSITIONS')} tscomp
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = tscomp.TSCOMP_MLR_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON tc.TC_ID = mlr.MLR_TC_ID
                LEFT JOIN {$this->t('TMS_TOOL_INVENTORY')} inv ON inv.INV_ID = tscomp.TSCOMP_INV_ID
                WHERE tscomp.TSCOMP_ID = ?";

        $q = $this->db_tms->query($sql, array($comp_id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
    }

    /**
     * Update Composition (Remarks and End Cycle only)
     * Source: TMS_TOOLSET_COMPOSITIONS, TMS_TOOL_INVENTORY
     */
    public function update_composition($comp_id, $remarks, $end_cycle)
    {
        $comp_id = (int)$comp_id;
        if ($comp_id <= 0) {
            $this->messages = 'ID tidak valid.';
            return false;
        }

        try {
            // Get composition to get INV_ID
            $comp = $this->get_composition_by_id($comp_id);
            if (!$comp) {
                $this->messages = 'Composition tidak ditemukan.';
                return false;
            }

            $this->db_tms->trans_start();

            // Update TSCOMP_REMARKS
            $this->db_tms->where('TSCOMP_ID', $comp_id);
            $this->db_tms->update($this->t('TMS_TOOLSET_COMPOSITIONS'), array(
                'TSCOMP_REMARKS' => $remarks
            ));

            // Update INV_END_CYCLE if INV_ID exists
            if (isset($comp['TSCOMP_INV_ID']) && (int)$comp['TSCOMP_INV_ID'] > 0) {
                $end_cycle_int = (int)$end_cycle;
                $this->db_tms->where('INV_ID', (int)$comp['TSCOMP_INV_ID']);
                $this->db_tms->update($this->t('TMS_TOOL_INVENTORY'), array(
                    'INV_END_CYCLE' => $end_cycle_int
                ));
            }

            $this->db_tms->trans_complete();

            if ($this->db_tms->trans_status() === FALSE) {
                $this->messages = 'Gagal mengupdate composition.';
                return false;
            }

            $this->messages = 'Composition berhasil diupdate.';
            return true;
        } catch (Exception $e) {
            log_message('error', '[M_tool_sets::update_composition] Exception: ' . $e->getMessage());
            $this->messages = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get available tools for replace (same MLR_ID, different INV_ID)
     * Source: TMS_TOOL_INVENTORY, TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_STORAGE_LOCATION
     */
    public function get_available_tools_for_replace($mlr_id, $exclude_inv_id = 0)
    {
        $mlr_id = (int)$mlr_id;
        $exclude_inv_id = (int)$exclude_inv_id;
        if ($mlr_id <= 0) return array();

        $sql = "SELECT 
                    inv.INV_ID,
                    inv.INV_TOOL_ID,
                    ISNULL(ml.ML_TOOL_DRAW_NO, '') AS TOOL_DRAWING_NO,
                    ISNULL(mlr.MLR_REV, 0) AS REVISION,
                    inv.INV_STATUS AS TOOL_STATUS,
                    ISNULL(inv.INV_END_CYCLE, 0) AS END_CYCLE,
                    ISNULL(sl.SL_NAME, '') AS STORAGE_LOCATION
                FROM {$this->t('TMS_TOOL_INVENTORY')} inv
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr ON mlr.MLR_ID = inv.INV_MLR_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_STORAGE_LOCATION')} sl ON sl.SL_ID = inv.INV_SL_ID
                WHERE inv.INV_MLR_ID = ? 
                  AND inv.INV_STATUS <> 6 
                  AND inv.INV_ID <> ?
                ORDER BY inv.INV_TOOL_ID ASC";

        $q = $this->db_tms->query($sql, array($mlr_id, $exclude_inv_id));
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Replace Composition (update INV_ID)
     * Source: TMS_TOOLSET_COMPOSITIONS, TMS_TOOL_INVENTORY
     */
    public function replace_composition($comp_id, $new_inv_id, $remarks)
    {
        $comp_id = (int)$comp_id;
        $new_inv_id = (int)$new_inv_id;
        if ($comp_id <= 0 || $new_inv_id <= 0) {
            $this->messages = 'ID tidak valid.';
            return false;
        }

        try {
            // Get composition to verify
            $comp = $this->get_composition_by_id($comp_id);
            if (!$comp) {
                $this->messages = 'Composition tidak ditemukan.';
                return false;
            }

            // Verify new inventory exists and has same MLR_ID
            $new_inv_sql = "SELECT INV_ID, INV_MLR_ID, INV_TOOL_ID FROM {$this->t('TMS_TOOL_INVENTORY')} WHERE INV_ID = ?";
            $new_inv_result = $this->db_tms->query($new_inv_sql, array($new_inv_id));
            if (!$new_inv_result || $new_inv_result->num_rows() === 0) {
                $this->messages = 'Tool Inventory tidak ditemukan.';
                return false;
            }
            $new_inv = $new_inv_result->row_array();

            // Verify MLR_ID matches
            if (isset($comp['TSCOMP_MLR_ID']) && (int)$comp['TSCOMP_MLR_ID'] !== (int)$new_inv['INV_MLR_ID']) {
                $this->messages = 'Tool Inventory harus memiliki Tool Drawing yang sama.';
                return false;
            }

            $this->db_tms->trans_start();

            // Update TSCOMP_INV_ID and TSCOMP_REMARKS
            $this->db_tms->where('TSCOMP_ID', $comp_id);
            $this->db_tms->update($this->t('TMS_TOOLSET_COMPOSITIONS'), array(
                'TSCOMP_INV_ID' => $new_inv_id,
                'TSCOMP_REMARKS' => $remarks
            ));

            $this->db_tms->trans_complete();

            if ($this->db_tms->trans_status() === FALSE) {
                $this->messages = 'Gagal mengganti composition.';
                return false;
            }

            $this->messages = 'Composition berhasil diganti.';
            return true;
        } catch (Exception $e) {
            log_message('error', '[M_tool_sets::replace_composition] Exception: ' . $e->getMessage());
            $this->messages = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Get Tool BOM data for popup modal
     * Returns: BOM No., Machine Group, BOM Description, BOM Revision
     * Source: TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_MACHINES, TMS_TOOL_MASTER_LIST_PARTS
     */
    public function get_tool_bom_for_modal()
    {
        $sql = "SELECT TOP 500
                    mlr.MLR_ID AS ID,
                    ml.ML_TOOL_DRAW_NO AS BOM_NO,
                    ISNULL(mac.MAC_NAME, '') AS MACHINE_GROUP,
                    ISNULL(mlr.MLR_DESC, '') AS BOM_DESCRIPTION,
                    ISNULL(mlr.MLR_REV, 0) AS BOM_REVISION,
                    mlr.MLR_ID AS MLR_ID,
                    (SELECT TOP 1 TMLP_PART_ID FROM {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} WHERE TMLP_ML_ID = ml.ML_ID) AS PRODUCT_ID,
                    mlr.MLR_OP_ID AS PROCESS_ID
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = mlr.MLR_MACG_ID
                WHERE ml.ML_TYPE = 2
                ORDER BY ml.ML_TOOL_DRAW_NO ASC, mlr.MLR_REV DESC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get Tool BOM details by MLR_ID for auto-fill
     * Source: TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_MACHINES, TMS_TOOL_MASTER_LIST_PARTS, MS_PARTS, MS_OPERATION
     */
    public function get_tool_bom_details_by_mlr_id($mlr_id)
    {
        $mlr_id = (int)$mlr_id;
        if ($mlr_id <= 0) return null;

        $sql = "SELECT 
                    mlr.MLR_ID,
                    ml.ML_TOOL_DRAW_NO AS BOM_NO,
                    ISNULL(mlr.MLR_DESC, '') AS BOM_DESCRIPTION,
                    ISNULL(mlr.MLR_REV, 0) AS BOM_REVISION,
                    ISNULL(mac.MAC_NAME, '') AS MACHINE_GROUP,
                    (SELECT TOP 1 TMLP_PART_ID FROM {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} WHERE TMLP_ML_ID = ml.ML_ID) AS PRODUCT_ID,
                    ISNULL(part.PART_NAME, '') AS PRODUCT_NAME,
                    mlr.MLR_OP_ID AS PROCESS_ID,
                    ISNULL(op.OP_NAME, '') AS PROCESS_NAME
                FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} mlr
                INNER JOIN {$this->t('TMS_TOOL_MASTER_LIST')} ml ON ml.ML_ID = mlr.MLR_ML_ID
                LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = mlr.MLR_MACG_ID
                LEFT JOIN {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} mlparts ON mlparts.TMLP_ML_ID = ml.ML_ID
                LEFT JOIN {$this->t('MS_PARTS')} part ON part.PART_ID = mlparts.TMLP_PART_ID
                LEFT JOIN {$this->t('MS_OPERATION')} op ON op.OP_ID = mlr.MLR_OP_ID
                WHERE mlr.MLR_ID = ? AND ml.ML_TYPE = 2";
        $q = $this->db_tms->query($sql, array($mlr_id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
    }

    /**
     * Get machines list
     * Source: MS_MACHINES
     */
    public function get_machines()
    {
        $sql = "SELECT MAC_ID, MAC_NAME 
                FROM {$this->t('MS_MACHINES')} 
                WHERE (IS_DELETED = 0 OR IS_DELETED IS NULL)
                ORDER BY MAC_NAME ASC";
        $q = $this->db_tms->query($sql);
        return $q && $q->num_rows() > 0 ? $q->result_array() : array();
    }

    /**
     * Get Assignment by ID
     * Source: TMS_TOOL_ASSIGNMENT, MS_OPERATION, MS_MACHINES, MS_PARTS, TMS_TOOLSETS
     */
    public function get_assignment_by_id($tasgn_id)
    {
        $tasgn_id = (int)$tasgn_id;
        if ($tasgn_id <= 0) return null;

        $sql = "SELECT 
                    tasgn.TASGN_ID,
                    tasgn.TASGN_TSET_ID,
                    tasgn.TASGN_OP_ID,
                    tasgn.TASGN_MAC_ID,
                    tasgn.TASGN_PART_ID,
                    tasgn.TASGN_PROD_START AS PRODUCTION_START,
                    tasgn.TASGN_PROD_END AS PRODUCTION_END,
                    tasgn.TASGN_LOT_PRODUCED AS USAGE,
                    ISNULL(tasgn.TASGN_REMARKS, '') AS REMARKS,
                    ISNULL(op.OP_NAME, '') AS OPERATION_NAME,
                    ISNULL(mac.MAC_NAME, '') AS MACHINE_NAME,
                    ISNULL(part.PART_NAME, '') AS PRODUCT_NAME
                FROM {$this->t('TMS_TOOL_ASSIGNMENT')} tasgn
                LEFT JOIN {$this->t('MS_OPERATION')} op ON op.OP_ID = tasgn.TASGN_OP_ID
                LEFT JOIN {$this->t('MS_MACHINES')} mac ON mac.MAC_ID = tasgn.TASGN_MAC_ID
                LEFT JOIN {$this->t('MS_PARTS')} part ON part.PART_ID = tasgn.TASGN_PART_ID
                WHERE tasgn.TASGN_ID = ?";

        $q = $this->db_tms->query($sql, array($tasgn_id));
        return $q && $q->num_rows() > 0 ? $q->row_array() : null;
    }

    /**
     * Add Assignment
     * Source: TMS_TOOL_ASSIGNMENT, TMS_TOOLSETS
     */
    public function add_assignment($data)
    {
        try {
            $this->db_tms->trans_start();

            // Validate required fields
            if (empty($data['TASGN_TSET_ID']) || (int)$data['TASGN_TSET_ID'] <= 0) {
                $this->messages = 'Tool Set ID tidak valid.';
                return false;
            }

            if (empty($data['TASGN_MAC_ID']) || (int)$data['TASGN_MAC_ID'] <= 0) {
                $this->messages = 'Machine harus dipilih.';
                return false;
            }

            // Verify tool set exists
            $tool_set = $this->get_by_id((int)$data['TASGN_TSET_ID']);
            if (!$tool_set) {
                $this->messages = 'Tool Set tidak ditemukan.';
                return false;
            }

            // Get operation ID and part ID from MLR_ID
            $op_id = null;
            $part_id = null;
            
            if (isset($tool_set['TSET_BOM_MLR_ID']) && (int)$tool_set['TSET_BOM_MLR_ID'] > 0) {
                $mlr_id = (int)$tool_set['TSET_BOM_MLR_ID'];
                
                // Get operation ID from MLR
                $mlr_sql = "SELECT MLR_OP_ID, MLR_ML_ID FROM {$this->t('TMS_TOOL_MASTER_LIST_REV')} WHERE MLR_ID = ?";
                $mlr_result = $this->db_tms->query($mlr_sql, array($mlr_id));
                if ($mlr_result && $mlr_result->num_rows() > 0) {
                    $mlr_row = $mlr_result->row();
                    $op_id = isset($mlr_row->MLR_OP_ID) ? (int)$mlr_row->MLR_OP_ID : null;
                    $ml_id = isset($mlr_row->MLR_ML_ID) ? (int)$mlr_row->MLR_ML_ID : null;
                    
                    // Get part ID from ML
                    if ($ml_id) {
                        $ml_sql = "SELECT TOP 1 TMLP_PART_ID 
                                  FROM {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} 
                                  WHERE TMLP_ML_ID = ?";
                        $ml_result = $this->db_tms->query($ml_sql, array($ml_id));
                        if ($ml_result && $ml_result->num_rows() > 0) {
                            $part_id = isset($ml_result->row()->TMLP_PART_ID) ? (int)$ml_result->row()->TMLP_PART_ID : null;
                        }
                    }
                }
            }

            // Prepare insert data
            $insert_data = array(
                'TASGN_TSET_ID' => (int)$data['TASGN_TSET_ID'],
                'TASGN_OP_ID' => $op_id,
                'TASGN_MAC_ID' => (int)$data['TASGN_MAC_ID'],
                'TASGN_PART_ID' => $part_id,
                'TASGN_PROD_START' => isset($data['TASGN_PROD_START']) && !empty($data['TASGN_PROD_START']) 
                    ? date('Y-m-d H:i:s', strtotime($data['TASGN_PROD_START'])) 
                    : date('Y-m-d H:i:s'),
                'TASGN_PROD_END' => isset($data['TASGN_PROD_END']) && !empty($data['TASGN_PROD_END']) 
                    ? date('Y-m-d H:i:s', strtotime($data['TASGN_PROD_END'])) 
                    : date('Y-m-d H:i:s'),
                'TASGN_LOT_PRODUCED' => isset($data['TASGN_LOT_PRODUCED']) ? (int)$data['TASGN_LOT_PRODUCED'] : 0,
                'TASGN_REMARKS' => isset($data['TASGN_REMARKS']) ? trim($data['TASGN_REMARKS']) : ''
            );

            $this->db_tms->insert($this->t('TMS_TOOL_ASSIGNMENT'), $insert_data);

            $this->db_tms->trans_complete();

            if ($this->db_tms->trans_status() === FALSE) {
                $this->messages = 'Gagal menambahkan assignment.';
                return false;
            }

            $this->messages = 'Assignment berhasil ditambahkan.';
            return true;
        } catch (Exception $e) {
            log_message('error', '[M_tool_sets::add_assignment] Exception: ' . $e->getMessage());
            $this->messages = 'Error: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Update Assignment
     * Source: TMS_TOOL_ASSIGNMENT
     */
    public function update_assignment($tasgn_id, $data)
    {
        try {
            $tasgn_id = (int)$tasgn_id;
            if ($tasgn_id <= 0) {
                $this->messages = 'Assignment ID tidak valid.';
                return false;
            }

            // Check if assignment exists
            $existing = $this->get_assignment_by_id($tasgn_id);
            if (!$existing) {
                $this->messages = 'Assignment tidak ditemukan.';
                return false;
            }

            $this->db_tms->trans_start();

            // Prepare update data
            $update_data = array();

            if (isset($data['TASGN_PROD_START']) && !empty($data['TASGN_PROD_START'])) {
                $update_data['TASGN_PROD_START'] = date('Y-m-d H:i:s', strtotime($data['TASGN_PROD_START']));
            }

            if (isset($data['TASGN_PROD_END']) && !empty($data['TASGN_PROD_END'])) {
                $update_data['TASGN_PROD_END'] = date('Y-m-d H:i:s', strtotime($data['TASGN_PROD_END']));
            }

            if (isset($data['TASGN_LOT_PRODUCED'])) {
                $update_data['TASGN_LOT_PRODUCED'] = (int)$data['TASGN_LOT_PRODUCED'];
            }

            if (isset($data['TASGN_REMARKS'])) {
                $update_data['TASGN_REMARKS'] = trim($data['TASGN_REMARKS']);
            }

            if (empty($update_data)) {
                $this->messages = 'Tidak ada data yang diupdate.';
                $this->db_tms->trans_rollback();
                return false;
            }

            // Update data
            $this->db_tms->where('TASGN_ID', $tasgn_id);
            $this->db_tms->update($this->t('TMS_TOOL_ASSIGNMENT'), $update_data);

            $this->db_tms->trans_complete();

            if ($this->db_tms->trans_status() === FALSE) {
                $this->messages = 'Gagal mengupdate assignment.';
                return false;
            }

            $this->messages = 'Assignment berhasil diupdate.';
            return true;
        } catch (Exception $e) {
            log_message('error', '[M_tool_sets::update_assignment] Exception: ' . $e->getMessage());
            $this->messages = 'Error: ' . $e->getMessage();
            return false;
        }
    }
}