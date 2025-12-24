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
     * Tabel: TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_OPERATION, MS_MACHINES, MS_USERS
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
            10 => 'ml.ML_TOOL_DRAW_NO'
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
            10 => 'ml.ML_TOOL_DRAW_NO' // Type column - tidak perlu search karena semua "ToolBOM"
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
                        'ToolBOM' AS TD_TYPE
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
     * Tabel: TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_MACHINES, MS_USERS
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
     * Tabel: TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_OPERATION, MS_MACHINES, MS_USERS, TMS_TOOL_MASTER_LIST_PARTS
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
                    ISNULL(rev.MLR_DRAWING, '') AS MLR_DRAWING,
                    ISNULL(rev.MLR_SKETCH, '') AS MLR_SKETCH,
                    ISNULL(ml.ML_TRIAL, 0) AS ML_TRIAL
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
        
        // Set IS_TRIAL_BOM from ML_TRIAL
        $result['ML_IS_TRIAL_BOM'] = isset($result['ML_TRIAL']) ? (int)$result['ML_TRIAL'] : 0;
        
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
     * Tabel: TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_USERS, MS_OPERATION, MS_MACHINES
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
     * Tabel: TMS_TOOL_MASTER_LIST_REV
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
     * Tabel: MS_MACHINES
     */
    public function get_machines()
    {
        $sql = "SELECT MAC_ID, MAC_NAME FROM {$this->t('MS_MACHINES')} ORDER BY MAC_NAME";
        $q = $this->db_tms->query($sql);
        return $q ? $q->result_array() : array();
    }

    /**
     * Get all products from MS_PARTS
     * Tabel: MS_PARTS
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
     * Tabel: MS_OPERATION
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
     * Tabel: MS_TOOL_CLASS
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
     * Tabel: MS_MATERIAL
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
     * Tabel: MS_MAKER
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
     * Tabel: MS_MACHINES
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
     * Add new Tool BOM
     * Tabel: TMS_TOOL_MASTER_LIST, TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST_PARTS, MS_USERS
     */
    public function add_data($tool_bom, $product_id, $process_id, $machine_group_id, $revision, $status, $description, $effective_date, $change_summary, $is_trial_bom, $drawing_file = null, $sketch_file = null)
    {
        $tool_bom = trim((string)$tool_bom);
        $product_id = (int)$product_id;
        $process_id = (int)$process_id;
        $machine_group_id = ($machine_group_id > 0) ? (int)$machine_group_id : null;
        $revision = (int)$revision;
        $status = (int)$status;
        $description = trim((string)$description);
        $effective_date = !empty($effective_date) ? trim((string)$effective_date) : null;
        $change_summary = trim((string)$change_summary);
        $is_trial_bom = (int)$is_trial_bom;
        
        if ($tool_bom === '') {
            $this->messages = 'Tool BOM tidak boleh kosong.';
            return false;
        }

        if ($product_id <= 0 || $process_id <= 0) {
            $this->messages = 'Product ID dan Process ID harus lebih dari 0.';
            return false;
        }

        $this->db_tms->trans_start();

        // Check if ML_TOOL_DRAW_NO already exists
        $check_sql = "SELECT ML_ID FROM {$this->t('TMS_TOOL_MASTER_LIST')} WHERE ML_TOOL_DRAW_NO = ?";
        $check_q = $this->db_tms->query($check_sql, array($tool_bom));
        $ml_id = null;

        if ($check_q && $check_q->num_rows() > 0) {
            // Use existing ML_ID
            $ml_id = (int)$check_q->row()->ML_ID;
            // Update ML_TRIAL if needed
            if ($is_trial_bom > 0) {
                $this->db_tms->query("UPDATE {$this->t('TMS_TOOL_MASTER_LIST')} SET ML_TRIAL = 1 WHERE ML_ID = ?", array($ml_id));
            }
        } else {
            // Insert new TMS_TOOL_MASTER_LIST
            $ml_insert_sql = "INSERT INTO {$this->t('TMS_TOOL_MASTER_LIST')} (ML_TOOL_DRAW_NO, ML_TYPE, ML_TRIAL) VALUES (?, 2, ?)";
            $ml_insert_q = $this->db_tms->query($ml_insert_sql, array($tool_bom, $is_trial_bom > 0 ? 1 : 0));
            if (!$ml_insert_q) {
                $this->db_tms->trans_rollback();
                $err = $this->db_tms->error();
                $this->messages = 'Gagal menambahkan Tool BOM. ' . (isset($err['message']) ? $err['message'] : '');
                return false;
            }
            // Get the inserted ML_ID
            $ml_id_q = $this->db_tms->query("SELECT IDENT_CURRENT('TMS_TOOL_MASTER_LIST') AS ML_ID");
            if ($ml_id_q && $ml_id_q->num_rows() > 0) {
                $ml_id = (int)$ml_id_q->row()->ML_ID;
            } else {
                $this->db_tms->trans_rollback();
                $this->messages = 'Gagal mendapatkan ML_ID.';
                return false;
            }
        }

        // Insert into TMS_TOOL_MASTER_LIST_PARTS
        $part_check_sql = "SELECT TMLP_ID FROM {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} WHERE TMLP_ML_ID = ? AND TMLP_PART_ID = ?";
        $part_check_q = $this->db_tms->query($part_check_sql, array($ml_id, $product_id));
        if (!$part_check_q || $part_check_q->num_rows() === 0) {
            $part_insert_sql = "INSERT INTO {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} (TMLP_ID, TMLP_ML_ID, TMLP_PART_ID) VALUES (NEWID(), ?, ?)";
            $this->db_tms->query($part_insert_sql, array($ml_id, $product_id));
        }

        // Get user ID
        $user_id = 1; // Default
        if (!empty($this->uid) && $this->uid !== 'SYSTEM') {
            $user_sql = "SELECT USR_ID FROM {$this->t('MS_USERS')} WHERE USR_NAME = ?";
            $user_q = $this->db_tms->query($user_sql, array($this->uid));
            if ($user_q && $user_q->num_rows() > 0) {
                $user_id = (int)$user_q->row()->USR_ID;
            }
        }

        // Insert into TMS_TOOL_MASTER_LIST_REV
        $effective_date_sql = $effective_date ? "CONVERT(datetime, ?, 120)" : "GETDATE()";
        $effective_date_param = $effective_date ? array($effective_date) : array();
        
        $rev_insert_sql = "INSERT INTO {$this->t('TMS_TOOL_MASTER_LIST_REV')} 
            (MLR_ML_ID, MLR_OP_ID, MLR_MACG_ID, MLR_DESC, MLR_REV, MLR_STATUS, MLR_EFFECTIVE_DATE, MLR_MODIFIED_DATE, MLR_MODIFIED_BY, MLR_CHANGE_SUMMARY, MLR_DRAWING, MLR_SKETCH)
            VALUES (?, ?, ?, ?, ?, ?, " . $effective_date_sql . ", GETDATE(), ?, ?, ?, ?)";
        
        $rev_params = array($ml_id, $process_id, $machine_group_id, $description, $revision, $status);
        $rev_params = array_merge($rev_params, $effective_date_param);
        $rev_params = array_merge($rev_params, array($user_id, $change_summary, $drawing_file, $sketch_file));
        
        $rev_insert_q = $this->db_tms->query($rev_insert_sql, $rev_params);
        
        if (!$rev_insert_q) {
            $this->db_tms->trans_rollback();
            $err = $this->db_tms->error();
            $this->messages = 'Gagal menambahkan revision. ' . (isset($err['message']) ? $err['message'] : '');
            return false;
        }

        $this->db_tms->trans_complete();

        if ($this->db_tms->trans_status()) {
            $this->messages = 'Tool BOM berhasil ditambahkan.';
            return true;
        }
        
        $err = $this->db_tms->error();
        $this->messages = 'Gagal menambahkan Tool BOM. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Update Tool BOM
     * Tabel: TMS_TOOL_MASTER_LIST, TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST_PARTS, MS_USERS
     */
    public function update_data($id, $tool_bom, $product_id, $process_id, $machine_group_id, $revision, $status, $description, $effective_date, $change_summary, $is_trial_bom, $drawing_file = null, $sketch_file = null)
    {
        $id = (int)$id;
        $row = $this->get_by_id($id);
        if (!$row) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        $tool_bom = trim((string)$tool_bom);
        $product_id = (int)$product_id;
        $process_id = (int)$process_id;
        $machine_group_id = ($machine_group_id > 0) ? (int)$machine_group_id : null;
        $revision = (int)$revision;
        $status = (int)$status;
        $description = trim((string)$description);
        $effective_date = !empty($effective_date) ? trim((string)$effective_date) : null;
        $change_summary = trim((string)$change_summary);
        $is_trial_bom = (int)$is_trial_bom;

        if ($tool_bom === '') {
            $this->messages = 'Tool BOM tidak boleh kosong.';
            return false;
        }

        $ml_id = isset($row['MLR_ML_ID']) ? (int)$row['MLR_ML_ID'] : 0;
        if ($ml_id <= 0) {
            $this->messages = 'ML_ID tidak valid.';
            return false;
        }

        $this->db_tms->trans_start();

        // Update TMS_TOOL_MASTER_LIST
        $ml_update_sql = "UPDATE {$this->t('TMS_TOOL_MASTER_LIST')} SET ML_TOOL_DRAW_NO = ?, ML_TRIAL = ? WHERE ML_ID = ?";
        $this->db_tms->query($ml_update_sql, array($tool_bom, $is_trial_bom > 0 ? 1 : 0, $ml_id));

        // Update TMS_TOOL_MASTER_LIST_PARTS
        $part_check_sql = "SELECT TMLP_ID FROM {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} WHERE TMLP_ML_ID = ? AND TMLP_PART_ID = ?";
        $part_check_q = $this->db_tms->query($part_check_sql, array($ml_id, $product_id));
        if (!$part_check_q || $part_check_q->num_rows() === 0) {
            // Delete old parts
            $this->db_tms->query("DELETE FROM {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} WHERE TMLP_ML_ID = ?", array($ml_id));
            // Insert new part
            $part_insert_sql = "INSERT INTO {$this->t('TMS_TOOL_MASTER_LIST_PARTS')} (TMLP_ID, TMLP_ML_ID, TMLP_PART_ID) VALUES (NEWID(), ?, ?)";
            $this->db_tms->query($part_insert_sql, array($ml_id, $product_id));
        }

        // Get user ID
        $user_id = 1;
        if (!empty($this->uid) && $this->uid !== 'SYSTEM') {
            $user_sql = "SELECT USR_ID FROM {$this->t('MS_USERS')} WHERE USR_NAME = ?";
            $user_q = $this->db_tms->query($user_sql, array($this->uid));
            if ($user_q && $user_q->num_rows() > 0) {
                $user_id = (int)$user_q->row()->USR_ID;
            }
        }

        // Update TMS_TOOL_MASTER_LIST_REV
        $update_fields = array();
        $update_params = array();
        
        $update_fields[] = "MLR_OP_ID = ?";
        $update_params[] = $process_id;
        
        $update_fields[] = "MLR_MACG_ID = ?";
        $update_params[] = $machine_group_id;
        
        $update_fields[] = "MLR_DESC = ?";
        $update_params[] = $description;
        
        $update_fields[] = "MLR_REV = ?";
        $update_params[] = $revision;
        
        $update_fields[] = "MLR_STATUS = ?";
        $update_params[] = $status;
        
        if ($effective_date) {
            $update_fields[] = "MLR_EFFECTIVE_DATE = CONVERT(datetime, ?, 120)";
            $update_params[] = $effective_date;
        }
        
        $update_fields[] = "MLR_MODIFIED_DATE = GETDATE()";
        $update_fields[] = "MLR_MODIFIED_BY = ?";
        $update_params[] = $user_id;
        
        $update_fields[] = "MLR_CHANGE_SUMMARY = ?";
        $update_params[] = $change_summary;
        
        if ($drawing_file !== null) {
            $update_fields[] = "MLR_DRAWING = ?";
            $update_params[] = $drawing_file;
        }
        
        if ($sketch_file !== null) {
            $update_fields[] = "MLR_SKETCH = ?";
            $update_params[] = $sketch_file;
        }
        
        $update_params[] = $id;
        
        $rev_update_sql = "UPDATE {$this->t('TMS_TOOL_MASTER_LIST_REV')} SET " . implode(', ', $update_fields) . " WHERE MLR_ID = ?";
        $this->db_tms->query($rev_update_sql, $update_params);

        $this->db_tms->trans_complete();

        if ($this->db_tms->trans_status()) {
            $this->messages = 'Tool BOM berhasil diubah.';
            return true;
        }
        
        $err = $this->db_tms->error();
        $this->messages = 'Gagal mengubah Tool BOM. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Get additional information (Tool Drawing Engineering members) for BOM
     * Returns Tool Drawing Engineering data that are children of this BOM
     * Tabel: TMS_TOOL_MASTER_LIST_MEMBERS, TMS_TOOL_MASTER_LIST_REV, TMS_TOOL_MASTER_LIST, MS_TOOL_CLASS, MS_OPERATION, MS_MATERIAL, MS_MAKER, TMS_TOOL_MASTER_LIST_PARTS, MS_PARTS
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
                ISNULL(members.TB_REMARK, '') AS TD_REMARKS,
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