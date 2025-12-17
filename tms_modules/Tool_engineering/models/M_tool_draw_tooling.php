<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!class_exists('M_tool_draw_tooling')) {
    class M_tool_draw_tooling extends CI_Model
    {
    // Using TMS_NEW database tables: TMS_TOOL_MASTER_LIST and TMS_TOOL_MASTER_LIST_REV
    private $table_rev = 'TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV';
    private $table_ml = 'TMS_NEW.dbo.TMS_TOOL_MASTER_LIST';
    public $tms_db;
    public $messages = '';
    public $uid = ''; // will receive username from controller

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_db', TRUE);
    }

    public function get_all()
    {
        // Get data from TMS_TOOL_MASTER_LIST_REV joined with TMS_TOOL_MASTER_LIST and related master tables
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
                FROM {$this->table_rev} mlr
                LEFT JOIN {$this->table_ml} ml ON mlr.MLR_ML_ID = ml.ML_ID
                LEFT JOIN TMS_NEW.dbo.MS_TOOL_CLASS tc ON mlr.MLR_TC_ID = tc.TC_ID
                LEFT JOIN TMS_NEW.dbo.MS_MAKER mk ON mlr.MLR_MAKER_ID = mk.MAKER_ID
                LEFT JOIN TMS_NEW.dbo.MS_MATERIAL mt ON mlr.MLR_MAT_ID = mt.MAT_ID
                ORDER BY mlr.MLR_ID DESC";

        $result = $this->tms_db->query($sql);

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
                FROM {$this->table_rev} mlr
                LEFT JOIN {$this->table_ml} ml ON mlr.MLR_ML_ID = ml.ML_ID
                LEFT JOIN TMS_NEW.dbo.MS_TOOL_CLASS tc ON mlr.MLR_TC_ID = tc.TC_ID
                LEFT JOIN TMS_NEW.dbo.MS_MAKER mk ON mlr.MLR_MAKER_ID = mk.MAKER_ID
                LEFT JOIN TMS_NEW.dbo.MS_MATERIAL mt ON mlr.MLR_MAT_ID = mt.MAT_ID
                LEFT JOIN TMS_NEW.dbo.MS_OPERATION op ON mlr.MLR_OP_ID = op.OP_ID
                WHERE mlr.MLR_ID = ?";

        $result = $this->tms_db->query($sql, array($id));
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
        $table = 'TMS_NEW.dbo.MS_TOOL_CLASS';
        $result = $this->tms_db
            ->select('TC_ID, TC_NAME, TC_DESC, TC_ABBR, TC_TYPE')
            ->from($table)
            ->order_by('TC_NAME', 'ASC')
            ->get();

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    public function get_tool_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $table = 'TMS_NEW.dbo.MS_TOOL_CLASS';
        $result = $this->tms_db->select('TC_ID, TC_NAME')->from($table)->where('TC_ID', $id)->limit(1)->get();
        if ($result && $result->num_rows() > 0) return $result->row_array();
        return null;
    }

    /**
     * Get all makers from MS_MAKER
     */
    public function get_makers()
    {
        $table = 'TMS_NEW.dbo.MS_MAKER';
        $result = $this->tms_db
            ->select('MAKER_ID, MAKER_NAME, MAKER_CODE, MAKER_DESC')
            ->from($table)
            ->order_by('MAKER_NAME', 'ASC')
            ->get();

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    public function get_maker_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $table = 'TMS_NEW.dbo.MS_MAKER';
        $result = $this->tms_db->select('MAKER_ID, MAKER_NAME')->from($table)->where('MAKER_ID', $id)->limit(1)->get();
        if ($result && $result->num_rows() > 0) return $result->row_array();
        return null;
    }

    /**
     * Get all materials from MS_MATERIAL
     */
    public function get_materials()
    {
        $table = 'TMS_NEW.dbo.MS_MATERIAL';
        $result = $this->tms_db
            ->select('MAT_ID, MAT_NAME, MAT_DESC, MAT_CODE')
            ->from($table)
            ->order_by('MAT_NAME', 'ASC')
            ->get();

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    public function get_material_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $table = 'TMS_NEW.dbo.MS_MATERIAL';
        $result = $this->tms_db->select('MAT_ID, MAT_NAME')->from($table)->where('MAT_ID', $id)->limit(1)->get();
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

        $this->tms_db->trans_start();

        // First insert into TMS_TOOL_MASTER_LIST
        $ml_data = array(
            'ML_TOOL_DRAW_NO' => 'NEW-' . date('YmdHis'),
            'ML_TYPE' => 1,
            'ML_TRIAL' => 0
        );
        $this->tms_db->insert($this->table_ml, $ml_data);
        $ml_id = (int)$this->tms_db->insert_id();
        
        if ($ml_id <= 0) {
            $row = $this->tms_db->query("SELECT IDENT_CURRENT('TMS_TOOL_MASTER_LIST') AS last_id")->row_array();
            if ($row && isset($row['last_id'])) $ml_id = (int)$row['last_id'];
        }

        // Then insert into TMS_TOOL_MASTER_LIST_REV
        $insertData = array(
            'MLR_ML_ID'          => $ml_id,
            'MLR_OP_ID'          => 0, // default operation
            'MLR_TC_ID'          => $tc_id,
            'MLR_MIN_QTY'        => $min_qty,
            'MLR_REPLENISH_QTY'  => $replenish_qty,
            'MLR_PRICE'          => $price,
            'MLR_DESC'           => $description,
            'MLR_STD_TL_LIFE'    => $tool_life,
            'MLR_REV'            => 0,
            'MLR_STATUS'         => 1,
            'MLR_EFFECTIVE_DATE' => date('Y-m-d H:i:s'),
            'MLR_MODIFIED_DATE'  => date('Y-m-d H:i:s')
        );

        if ($maker_id > 0) {
            $insertData['MLR_MAKER_ID'] = $maker_id;
        }

        if ($mat_id > 0) {
            $insertData['MLR_MAT_ID'] = $mat_id;
        }

        $modifiedBy = '';
        if (isset($this->uid) && $this->uid !== '') {
            $modifiedBy = (string)$this->uid;
        }
        if ($modifiedBy !== '') {
            $insertData['MLR_MODIFIED_BY'] = $modifiedBy;
        }

        $ok = $this->tms_db->insert($this->table_rev, $insertData);

        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = 'Tool Drawing Tooling berhasil ditambahkan.';
            return true;
        }
        $err = $this->tms_db->error();
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

        $modifiedBy = '';
        if (isset($this->uid) && $this->uid !== '') {
            $modifiedBy = (string)$this->uid;
        }

        $updateData = array(
            'MLR_TC_ID'          => $tc_id,
            'MLR_MIN_QTY'        => $min_qty,
            'MLR_REPLENISH_QTY'  => $replenish_qty,
            'MLR_PRICE'          => $price,
            'MLR_DESC'           => $description,
            'MLR_STD_TL_LIFE'    => $tool_life,
            'MLR_MODIFIED_DATE'  => date('Y-m-d H:i:s')
        );

        if ($modifiedBy !== '') {
            $updateData['MLR_MODIFIED_BY'] = $modifiedBy;
        }

        if ($maker_id > 0) {
            $updateData['MLR_MAKER_ID'] = $maker_id;
        } else {
            $updateData['MLR_MAKER_ID'] = null;
        }

        if ($mat_id > 0) {
            $updateData['MLR_MAT_ID'] = $mat_id;
        } else {
            $updateData['MLR_MAT_ID'] = null;
        }

        $ok = $this->tms_db->where('MLR_ID', $id)->update($this->table_rev, $updateData);

        if ($ok) {
            $this->messages = 'Tool Drawing Tooling berhasil diubah.';
            return true;
        }
        $err = $this->tms_db->error();
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

        $ok = $this->tms_db->delete($this->table_rev, array('MLR_ID' => $id));

        if ($ok) {
            $this->messages = 'Tool Drawing Tooling berhasil dihapus.';
            return true;
        }
        $err = $this->tms_db->error();
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

        // First get the current record to find ML_ID
        $current = $this->get_by_id($id);
        if (!$current) {
            return array();
        }

        $ml_id = isset($current['MLR_ML_ID']) ? (int)$current['MLR_ML_ID'] : 0;
        if ($ml_id <= 0) {
            // Return current record as single history entry
            return array($current);
        }

        // Get all revisions for this master list ID
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
                FROM {$this->table_rev} mlr
                LEFT JOIN {$this->table_ml} ml ON mlr.MLR_ML_ID = ml.ML_ID
                LEFT JOIN TMS_NEW.dbo.MS_TOOL_CLASS tc ON mlr.MLR_TC_ID = tc.TC_ID
                LEFT JOIN TMS_NEW.dbo.MS_MAKER mk ON mlr.MLR_MAKER_ID = mk.MAKER_ID
                LEFT JOIN TMS_NEW.dbo.MS_MATERIAL mt ON mlr.MLR_MAT_ID = mt.MAT_ID
                LEFT JOIN TMS_NEW.dbo.MS_OPERATION op ON mlr.MLR_OP_ID = op.OP_ID
                WHERE mlr.MLR_ML_ID = ?
                ORDER BY mlr.MLR_REV DESC";

        $result = $this->tms_db->query($sql, array($ml_id));
        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        
        // Fallback: return current record
        return array($current);
    }
} // end class M_tool_draw_tooling
} // end if !class_exists
