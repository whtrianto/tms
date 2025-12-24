<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!class_exists('M_tool_draw_tooling')) {
    class M_tool_draw_tooling extends CI_Model
    {
    // Database name - ubah nilai ini jika ingin ganti database
    private $db_name = 'TMS_NEW';
    
    // Table prefix (akan di-set di constructor)
    private $tbl;
    
    // Table names (tanpa prefix)
    private $table_rev = 'TMS_TOOL_MASTER_LIST_REV';
    private $table_ml = 'TMS_TOOL_MASTER_LIST';
    
    public $tms_NEW;
    public $messages = '';
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->tms_NEW = $this->load->database('tms_NEW', TRUE);
        
        // Set table prefix dengan database name
        $this->tbl = $this->db_name . '.dbo.';
    }

    /**
     * Helper: Get table name with database prefix
     */
    private function t($table)
    {
        return $this->tbl . $table;
    }

    public function get_all()
    {
        $sql = "SELECT 
                    mlr.MLR_ID, mlr.MLR_ML_ID, mlr.MLR_OP_ID, mlr.MLR_TC_ID, mlr.MLR_MAKER_ID,
                    mlr.MLR_MIN_QTY, mlr.MLR_REPLENISH_QTY, mlr.MLR_PRICE, mlr.MLR_STD_TL_LIFE,
                    mlr.MLR_STD_REWORK, mlr.MLR_DESC, mlr.MLR_DRAWING, mlr.MLR_MAT_ID,
                    mlr.MLR_REV, mlr.MLR_STATUS, mlr.MLR_EFFECTIVE_DATE, mlr.MLR_MODIFIED_DATE,
                    mlr.MLR_MODIFIED_BY, mlr.MLR_MACG_ID, mlr.MLR_CHANGE_SUMMARY, mlr.MLR_SKETCH,
                    ml.ML_TOOL_DRAW_NO, ml.ML_TYPE, ml.ML_TRIAL,
                    tc.TC_NAME, tc.TC_DESC AS TC_DESCRIPTION,
                    mk.MAKER_NAME,
                    mt.MAT_NAME
                FROM {$this->t($this->table_rev)} mlr
                LEFT JOIN {$this->t($this->table_ml)} ml ON mlr.MLR_ML_ID = ml.ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON mlr.MLR_TC_ID = tc.TC_ID
                LEFT JOIN {$this->t('MS_MAKER')} mk ON mlr.MLR_MAKER_ID = mk.MAKER_ID
                LEFT JOIN {$this->t('MS_MATERIAL')} mt ON mlr.MLR_MAT_ID = mt.MAT_ID
                ORDER BY mlr.MLR_ID DESC";

        $result = $this->tms_NEW->query($sql);

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;

        $sql = "SELECT 
                    mlr.MLR_ID, mlr.MLR_ML_ID, mlr.MLR_OP_ID, mlr.MLR_TC_ID, mlr.MLR_MAKER_ID,
                    mlr.MLR_MIN_QTY, mlr.MLR_REPLENISH_QTY, mlr.MLR_PRICE, mlr.MLR_STD_TL_LIFE,
                    mlr.MLR_STD_REWORK, mlr.MLR_DESC, mlr.MLR_DRAWING, mlr.MLR_MAT_ID,
                    mlr.MLR_REV, mlr.MLR_STATUS, mlr.MLR_EFFECTIVE_DATE, mlr.MLR_MODIFIED_DATE,
                    mlr.MLR_MODIFIED_BY, mlr.MLR_MACG_ID, mlr.MLR_CHANGE_SUMMARY, mlr.MLR_SKETCH,
                    ml.ML_TOOL_DRAW_NO, ml.ML_TYPE, ml.ML_TRIAL,
                    tc.TC_NAME, tc.TC_DESC AS TC_DESCRIPTION,
                    mk.MAKER_NAME,
                    mt.MAT_NAME,
                    op.OP_NAME AS OPERATION_NAME
                FROM {$this->t($this->table_rev)} mlr
                LEFT JOIN {$this->t($this->table_ml)} ml ON mlr.MLR_ML_ID = ml.ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON mlr.MLR_TC_ID = tc.TC_ID
                LEFT JOIN {$this->t('MS_MAKER')} mk ON mlr.MLR_MAKER_ID = mk.MAKER_ID
                LEFT JOIN {$this->t('MS_MATERIAL')} mt ON mlr.MLR_MAT_ID = mt.MAT_ID
                LEFT JOIN {$this->t('MS_OPERATION')} op ON mlr.MLR_OP_ID = op.OP_ID
                WHERE mlr.MLR_ID = ?";

        $result = $this->tms_NEW->query($sql, array($id));
        if ($result && $result->num_rows() > 0) {
            return $result->row_array();
        }
        return null;
    }

    /**
     * Get by ID with Parts (Product) name
     */
    public function get_by_id_with_parts($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;

        $sql = "SELECT 
                    mlr.MLR_ID, mlr.MLR_ML_ID, mlr.MLR_OP_ID, mlr.MLR_TC_ID, mlr.MLR_MAKER_ID,
                    mlr.MLR_MIN_QTY, mlr.MLR_REPLENISH_QTY, mlr.MLR_PRICE, mlr.MLR_STD_TL_LIFE,
                    mlr.MLR_STD_REWORK, mlr.MLR_DESC, mlr.MLR_DRAWING, mlr.MLR_MAT_ID,
                    mlr.MLR_REV, mlr.MLR_STATUS, mlr.MLR_EFFECTIVE_DATE, mlr.MLR_MODIFIED_DATE,
                    mlr.MLR_MODIFIED_BY, mlr.MLR_MACG_ID, mlr.MLR_CHANGE_SUMMARY, mlr.MLR_SKETCH,
                    ml.ML_TOOL_DRAW_NO, ml.ML_TYPE, ml.ML_TRIAL,
                    tc.TC_NAME, tc.TC_DESC AS TC_DESCRIPTION,
                    mk.MAKER_NAME,
                    mt.MAT_NAME,
                    op.OP_NAME AS OPERATION_NAME,
                    dbo.fnGetToolMasterListParts(ml.ML_ID) AS PART_NAME
                FROM {$this->t($this->table_rev)} mlr
                LEFT JOIN {$this->t($this->table_ml)} ml ON mlr.MLR_ML_ID = ml.ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON mlr.MLR_TC_ID = tc.TC_ID
                LEFT JOIN {$this->t('MS_MAKER')} mk ON mlr.MLR_MAKER_ID = mk.MAKER_ID
                LEFT JOIN {$this->t('MS_MATERIAL')} mt ON mlr.MLR_MAT_ID = mt.MAT_ID
                LEFT JOIN {$this->t('MS_OPERATION')} op ON mlr.MLR_OP_ID = op.OP_ID
                WHERE mlr.MLR_ID = ?";

        $result = $this->tms_NEW->query($sql, array($id));
        if ($result && $result->num_rows() > 0) {
            return $result->row_array();
        }
        return null;
    }

    /**
     * Get all tools from MS_TOOL_CLASS
     */
    public function get_tools()
    {
        $sql = "SELECT TC_ID, TC_NAME, TC_DESC 
                FROM {$this->t('MS_TOOL_CLASS')} 
                ORDER BY TC_NAME ASC";
        $result = $this->tms_NEW->query($sql);

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    public function get_tool_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        
        $sql = "SELECT TC_ID, TC_NAME FROM {$this->t('MS_TOOL_CLASS')} WHERE TC_ID = ?";
        $result = $this->tms_NEW->query($sql, array($id));
        if ($result && $result->num_rows() > 0) return $result->row_array();
        return null;
    }

    /**
     * Get all makers from MS_MAKER
     */
    public function get_makers()
    {
        $sql = "SELECT MAKER_ID, MAKER_NAME, MAKER_CODE, MAKER_DESC 
                FROM {$this->t('MS_MAKER')} 
                ORDER BY MAKER_NAME ASC";
        $result = $this->tms_NEW->query($sql);

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    public function get_maker_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        
        $sql = "SELECT MAKER_ID, MAKER_NAME FROM {$this->t('MS_MAKER')} WHERE MAKER_ID = ?";
        $result = $this->tms_NEW->query($sql, array($id));
        if ($result && $result->num_rows() > 0) return $result->row_array();
        return null;
    }

    /**
     * Get all materials from MS_MATERIAL
     */
    public function get_materials()
    {
        $sql = "SELECT MAT_ID, MAT_NAME, MAT_DESC, MAT_CODE 
                FROM {$this->t('MS_MATERIAL')} 
                ORDER BY MAT_NAME ASC";
        $result = $this->tms_NEW->query($sql);

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    public function get_material_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        
        $sql = "SELECT MAT_ID, MAT_NAME FROM {$this->t('MS_MATERIAL')} WHERE MAT_ID = ?";
        $result = $this->tms_NEW->query($sql, array($id));
        if ($result && $result->num_rows() > 0) return $result->row_array();
        return null;
    }

    /* ========== MUTATORS ========== */

    public function add_data($tc_id, $min_qty, $replenish_qty, $maker_id, $price, $description, $mat_id, $tool_life)
    {
        $tc_id = (int)$tc_id;
        $min_qty = (int)$min_qty;
        $replenish_qty = (int)$replenish_qty;
        $maker_id = (int)$maker_id;
        $price = (float)$price;
        $description = trim((string)$description);
        $mat_id = (int)$mat_id;
        $tool_life = trim((string)$tool_life);

        if ($tc_id <= 0) {
            $this->messages = 'Tool Class ID harus lebih dari 0.';
            return false;
        }

        $this->tms_NEW->trans_start();

        // First insert into TMS_TOOL_MASTER_LIST
        $ml_insert_sql = "INSERT INTO {$this->t($this->table_ml)} (ML_TOOL_DRAW_NO, ML_TYPE, ML_TRIAL) VALUES (?, 1, 0)";
        $this->tms_NEW->query($ml_insert_sql, array('NEW-' . date('YmdHis')));
        $ml_id = (int)$this->tms_NEW->insert_id();
        
        if ($ml_id <= 0) {
            $row = $this->tms_NEW->query("SELECT IDENT_CURRENT('{$this->table_ml}') AS last_id")->row_array();
            if ($row && isset($row['last_id'])) $ml_id = (int)$row['last_id'];
        }

        // Then insert into TMS_TOOL_MASTER_LIST_REV
        $modifiedBy = isset($this->uid) && $this->uid !== '' ? (string)$this->uid : '';
        
        $rev_sql = "INSERT INTO {$this->t($this->table_rev)} 
                    (MLR_ML_ID, MLR_OP_ID, MLR_TC_ID, MLR_MIN_QTY, MLR_REPLENISH_QTY, MLR_PRICE, MLR_DESC, MLR_STD_TL_LIFE, 
                     MLR_REV, MLR_STATUS, MLR_EFFECTIVE_DATE, MLR_MODIFIED_DATE, MLR_MAKER_ID, MLR_MAT_ID, MLR_MODIFIED_BY) 
                    VALUES (?, 0, ?, ?, ?, ?, ?, ?, 0, 1, GETDATE(), GETDATE(), ?, ?, ?)";
        
        $this->tms_NEW->query($rev_sql, array(
            $ml_id, $tc_id, $min_qty, $replenish_qty, $price, $description, $tool_life,
            $maker_id > 0 ? $maker_id : null,
            $mat_id > 0 ? $mat_id : null,
            $modifiedBy !== '' ? $modifiedBy : null
        ));

        $this->tms_NEW->trans_complete();

        if ($this->tms_NEW->trans_status()) {
            $this->messages = 'Tool Drawing Tooling berhasil ditambahkan.';
            return true;
        }
        $err = $this->tms_NEW->error();
        $this->messages = 'Gagal menambahkan tool drawing tooling. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    public function edit_data($id, $tc_id, $min_qty, $replenish_qty, $maker_id, $price, $description, $mat_id, $tool_life)
    {
        $id = (int)$id;
        $tc_id = (int)$tc_id;
        $min_qty = (int)$min_qty;
        $replenish_qty = (int)$replenish_qty;
        $maker_id = (int)$maker_id;
        $price = (float)$price;
        $description = trim((string)$description);
        $mat_id = (int)$mat_id;
        $tool_life = trim((string)$tool_life);

        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        if ($tc_id <= 0) {
            $this->messages = 'Tool Class ID harus lebih dari 0.';
            return false;
        }

        $modifiedBy = isset($this->uid) && $this->uid !== '' ? (string)$this->uid : '';

        $update_sql = "UPDATE {$this->t($this->table_rev)} 
                       SET MLR_TC_ID = ?, MLR_MIN_QTY = ?, MLR_REPLENISH_QTY = ?, MLR_PRICE = ?, 
                           MLR_DESC = ?, MLR_STD_TL_LIFE = ?, MLR_MODIFIED_DATE = GETDATE(),
                           MLR_MAKER_ID = ?, MLR_MAT_ID = ?, MLR_MODIFIED_BY = ?
                       WHERE MLR_ID = ?";
        
        $ok = $this->tms_NEW->query($update_sql, array(
            $tc_id, $min_qty, $replenish_qty, $price, $description, $tool_life,
            $maker_id > 0 ? $maker_id : null,
            $mat_id > 0 ? $mat_id : null,
            $modifiedBy !== '' ? $modifiedBy : null,
            $id
        ));

        if ($ok) {
            $this->messages = 'Tool Drawing Tooling berhasil diubah.';
            return true;
        }
        $err = $this->tms_NEW->error();
        $this->messages = 'Gagal mengubah tool drawing tooling. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    public function delete_data($id)
    {
        $id = (int)$id;
        $row = $this->get_by_id($id);
        if (!$row) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        $del_sql = "DELETE FROM {$this->t($this->table_rev)} WHERE MLR_ID = ?";
        $ok = $this->tms_NEW->query($del_sql, array($id));

        if ($ok) {
            $this->messages = 'Tool Drawing Tooling berhasil dihapus.';
            return true;
        }
        $err = $this->tms_NEW->error();
        $this->messages = 'Gagal menghapus tool drawing tooling. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Get revision history for a specific record (based on ML_ID)
     */
    public function get_history($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return array();
        }

        $current = $this->get_by_id($id);
        if (!$current) {
            return array();
        }

        $ml_id = isset($current['MLR_ML_ID']) ? (int)$current['MLR_ML_ID'] : 0;
        if ($ml_id <= 0) {
            return array($current);
        }

        $sql = "SELECT 
                    mlr.MLR_ID, mlr.MLR_ML_ID, mlr.MLR_OP_ID, mlr.MLR_TC_ID, mlr.MLR_MAKER_ID,
                    mlr.MLR_MIN_QTY, mlr.MLR_REPLENISH_QTY, mlr.MLR_PRICE, mlr.MLR_STD_TL_LIFE,
                    mlr.MLR_DESC, mlr.MLR_DRAWING, mlr.MLR_MAT_ID,
                    mlr.MLR_REV, mlr.MLR_STATUS, mlr.MLR_EFFECTIVE_DATE, mlr.MLR_MODIFIED_DATE,
                    mlr.MLR_MODIFIED_BY,
                    ml.ML_TOOL_DRAW_NO,
                    tc.TC_NAME,
                    mk.MAKER_NAME,
                    mt.MAT_NAME,
                    op.OP_NAME AS OPERATION_NAME
                FROM {$this->t($this->table_rev)} mlr
                LEFT JOIN {$this->t($this->table_ml)} ml ON mlr.MLR_ML_ID = ml.ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON mlr.MLR_TC_ID = tc.TC_ID
                LEFT JOIN {$this->t('MS_MAKER')} mk ON mlr.MLR_MAKER_ID = mk.MAKER_ID
                LEFT JOIN {$this->t('MS_MATERIAL')} mt ON mlr.MLR_MAT_ID = mt.MAT_ID
                LEFT JOIN {$this->t('MS_OPERATION')} op ON mlr.MLR_OP_ID = op.OP_ID
                WHERE mlr.MLR_ML_ID = ?
                ORDER BY mlr.MLR_REV DESC";

        $result = $this->tms_NEW->query($sql, array($ml_id));
        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        
        return array($current);
    }

    /**
     * Get all parts from MS_PARTS (used as Products)
     */
    public function get_products()
    {
        $sql = "SELECT PART_ID AS PRODUCT_ID, PART_NAME AS PRODUCT_NAME 
                FROM {$this->t('MS_PARTS')} 
                ORDER BY PART_NAME";
        $query = $this->tms_NEW->query($sql);
        return $query ? $query->result_array() : array();
    }

    /**
     * Get all operations from MS_OPERATION
     */
    public function get_operations()
    {
        $sql = "SELECT OP_ID, OP_NAME 
                FROM {$this->t('MS_OPERATION')} 
                ORDER BY OP_NAME";
        $query = $this->tms_NEW->query($sql);
        return $query ? $query->result_array() : array();
    }

    /**
     * Server-side DataTable processing
     */
    public function get_data_serverside($start, $length, $search, $order_col, $order_dir, $column_search = array())
    {
        // Column mapping for ordering
        $columns = array(
            0 => 'ml.ML_TOOL_DRAW_NO',
            1 => 'tc.TC_NAME',
            2 => 'mlr.MLR_MIN_QTY',
            3 => 'mlr.MLR_REPLENISH_QTY',
            4 => 'mk.MAKER_NAME',
            5 => 'mlr.MLR_PRICE',
            6 => 'mlr.MLR_DESC',
            7 => 'mlr.MLR_EFFECTIVE_DATE',
            8 => 'mt.MAT_NAME',
            9 => 'mlr.MLR_STD_TL_LIFE'
        );

        $base_sql = "FROM {$this->t($this->table_rev)} mlr
                LEFT JOIN {$this->t($this->table_ml)} ml ON mlr.MLR_ML_ID = ml.ML_ID
                LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON mlr.MLR_TC_ID = tc.TC_ID
                LEFT JOIN {$this->t('MS_MAKER')} mk ON mlr.MLR_MAKER_ID = mk.MAKER_ID
                LEFT JOIN {$this->t('MS_MATERIAL')} mt ON mlr.MLR_MAT_ID = mt.MAT_ID";

        $where = " WHERE (tc.TC_NAME IS NOT NULL AND tc.TC_NAME <> '')";
        $params = array();

        // Global search
        if (!empty($search)) {
            $where .= " AND (ml.ML_TOOL_DRAW_NO LIKE ? OR tc.TC_NAME LIKE ? OR mk.MAKER_NAME LIKE ? 
                        OR mlr.MLR_DESC LIKE ? OR mt.MAT_NAME LIKE ?)";
            $search_param = '%' . $search . '%';
            $params = array_merge($params, array($search_param, $search_param, $search_param, $search_param, $search_param));
        }

        // Per-column search
        foreach ($column_search as $col_idx => $col_val) {
            if (!empty($col_val) && isset($columns[$col_idx])) {
                $where .= " AND " . $columns[$col_idx] . " LIKE ?";
                $params[] = '%' . $col_val . '%';
            }
        }

        // Count total (without filter)
        $count_total_sql = "SELECT COUNT(*) as cnt FROM {$this->t($this->table_rev)} mlr
                           LEFT JOIN {$this->t('MS_TOOL_CLASS')} tc ON mlr.MLR_TC_ID = tc.TC_ID
                           WHERE (tc.TC_NAME IS NOT NULL AND tc.TC_NAME <> '')";
        $count_total = $this->tms_NEW->query($count_total_sql)->row()->cnt;

        // Count filtered
        $count_filtered_sql = "SELECT COUNT(*) as cnt " . $base_sql . $where;
        $count_filtered = $this->tms_NEW->query($count_filtered_sql, $params)->row()->cnt;

        // Order
        $order_column = isset($columns[$order_col]) ? $columns[$order_col] : 'tc.TC_NAME';
        $order_direction = strtoupper($order_dir) === 'DESC' ? 'DESC' : 'ASC';

        // Data query with pagination (SQL Server syntax)
        $data_sql = "SELECT 
                        mlr.MLR_ID, mlr.MLR_MIN_QTY, mlr.MLR_REPLENISH_QTY, mlr.MLR_PRICE,
                        mlr.MLR_STD_TL_LIFE, mlr.MLR_DESC, mlr.MLR_EFFECTIVE_DATE,
                        ml.ML_TOOL_DRAW_NO,
                        tc.TC_NAME,
                        mk.MAKER_NAME,
                        mt.MAT_NAME
                    " . $base_sql . $where . "
                    ORDER BY " . $order_column . " " . $order_direction . "
                    OFFSET " . (int)$start . " ROWS FETCH NEXT " . (int)$length . " ROWS ONLY";

        $result = $this->tms_NEW->query($data_sql, $params);
        $data = $result ? $result->result_array() : array();

        return array(
            'recordsTotal' => (int)$count_total,
            'recordsFiltered' => (int)$count_filtered,
            'data' => $data
        );
    }

    /**
     * Get Tool BOM list by ML_ID (master list id)
     * Uses TMS_TOOL_MASTER_LIST_MEMBERS (parent-child relationship)
     */
    public function get_tool_bom_by_ml_id($mlr_id)
    {
        // First get the ML_ID from MLR_ID
        $sql = "SELECT MLR_ML_ID FROM {$this->t($this->table_rev)} WHERE MLR_ID = ?";
        $query = $this->tms_NEW->query($sql, array($mlr_id));
        if (!$query || $query->num_rows() == 0) {
            return array();
        }
        $row = $query->row_array();
        $ml_id = $row['MLR_ML_ID'];

        // Get Tool BOM entries (parent tools) that use this tool as child
        $sql = "SELECT 
                    tbm.TB_ID,
                    ml_parent.ML_TOOL_DRAW_NO AS TOOL_BOM,
                    dbo.fnGetToolMasterListParts(ml_parent.ML_ID) AS PRODUCT,
                    mlr_parent.MLR_REV AS BOM_REV,
                    tbm.TB_QTY AS QTY
                FROM {$this->t('TMS_TOOL_MASTER_LIST_MEMBERS')} tbm
                LEFT JOIN {$this->t($this->table_rev)} mlr_child ON tbm.TB_MLR_CHILD_ID = mlr_child.MLR_ID
                LEFT JOIN {$this->t($this->table_rev)} mlr_parent ON tbm.TB_MLR_PARENT_ID = mlr_parent.MLR_ID
                LEFT JOIN {$this->t($this->table_ml)} ml_parent ON mlr_parent.MLR_ML_ID = ml_parent.ML_ID
                WHERE mlr_child.MLR_ML_ID = ?
                ORDER BY ml_parent.ML_TOOL_DRAW_NO";
        $query = $this->tms_NEW->query($sql, array($ml_id));
        return $query ? $query->result_array() : array();
    }
} // end class M_tool_draw_tooling
} // end if !class_exists