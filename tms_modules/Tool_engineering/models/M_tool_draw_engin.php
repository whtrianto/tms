<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model untuk menampilkan Tool Drawing menggunakan struktur tabel di struktur-tms.sql
 * Sumber utama:
 * - TMS_TOOL_MASTER_LIST (ML_ID, ML_TOOL_DRAW_NO, ML_TYPE)
 * - TMS_TOOL_MASTER_LIST_REV (MLR_ID, MLR_ML_ID, MLR_OP_ID, MLR_TC_ID, MLR_MAKER_ID, MLR_MAT_ID, MLR_MACG_ID, MLR_REV, MLR_STATUS, MLR_EFFECTIVE_DATE, MLR_MODIFIED_DATE, MLR_MODIFIED_BY)
 * - MS_OPERATION (OP_NAME)
 * - MS_TOOL_CLASS (TC_NAME)  -> dipakai sebagai Tool Name
 * - MS_MAKER (MAKER_NAME)
 * - MS_MATERIAL (MAT_NAME)
 * - MS_MACHINES (MAC_NAME)   -> Machine Group
 * - MS_PARTS (PART_NAME)     -> Product (via TMS_TOOL_MASTER_LIST_PARTS)
 */
class M_tool_draw_engin extends CI_Model
{
    private $db_tms;

    public function __construct()
    {
        parent::__construct();
        $this->db_tms = $this->load->database('tms_NEW', true);
    }

    /**
     * Ambil list tool drawing (engineering) dari struktur SQL bawaan.
     * Hanya mengambil ML_TYPE = 1 (tool) dan status aktif/pending/inaktif berdasar MLR_STATUS.
     */
    public function get_all()
    {
        $sql = "
            SELECT
                rev.MLR_ID          AS TD_ID,
                ml.ML_TOOL_DRAW_NO  AS TD_DRAWING_NO,
                rev.MLR_REV         AS TD_REVISION,
                rev.MLR_STATUS      AS TD_STATUS,
                rev.MLR_EFFECTIVE_DATE AS TD_EFFECTIVE_DATE,
                rev.MLR_MODIFIED_DATE  AS TD_MODIFIED_DATE,
                rev.MLR_MODIFIED_BY    AS TD_MODIFIED_BY,
                op.OP_NAME          AS TD_OPERATION_NAME,
                tc.TC_NAME          AS TD_TOOL_NAME,
                mac.MAC_NAME        AS TD_MAC_NAME,
                maker.MAKER_NAME    AS TD_MAKER_NAME,
                mat.MAT_NAME        AS TD_MATERIAL_NAME,
                part.PART_NAME      AS TD_PRODUCT_NAME
            FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST revParent -- dummy alias to keep syntax compatible
            ";

        // Query lengkap dengan JOIN
        // Menggunakan fnGetToolMasterListParts untuk Product Name (seperti di VW_TOOL_MASTER_LIST)
        // JOIN dengan MS_USERS untuk mendapatkan nama user dari MLR_MODIFIED_BY
        // Format tanggal untuk konsistensi tampilan
        $sql = "
            SELECT
                rev.MLR_ID          AS TD_ID,
                ml.ML_TOOL_DRAW_NO  AS TD_DRAWING_NO,
                rev.MLR_REV         AS TD_REVISION,
                rev.MLR_STATUS      AS TD_STATUS,
                CASE 
                    WHEN rev.MLR_EFFECTIVE_DATE IS NULL THEN ''
                    ELSE CONVERT(VARCHAR(19), rev.MLR_EFFECTIVE_DATE, 120)
                END AS TD_EFFECTIVE_DATE,
                CASE 
                    WHEN rev.MLR_MODIFIED_DATE IS NULL THEN ''
                    ELSE CONVERT(VARCHAR(19), rev.MLR_MODIFIED_DATE, 120)
                END AS TD_MODIFIED_DATE,
                ISNULL(usr.USR_NAME, '') AS TD_MODIFIED_BY,
                ISNULL(op.OP_NAME, '') AS TD_OPERATION_NAME,
                ISNULL(tc.TC_NAME, '') AS TD_TOOL_NAME,
                ISNULL(mac.MAC_NAME, '') AS TD_MAC_NAME,
                ISNULL(maker.MAKER_NAME, '') AS TD_MAKER_NAME,
                ISNULL(mat.MAT_NAME, '') AS TD_MATERIAL_NAME,
                ISNULL(dbo.fnGetToolMasterListParts(ml.ML_ID), '') AS TD_PRODUCT_NAME,
                ISNULL(rev.MLR_DRAWING, '') AS TD_DRAWING_FILE,
                ISNULL(rev.MLR_SKETCH, '') AS TD_SKETCH_FILE
            FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
            INNER JOIN TMS_NEW.dbo.TMS_TOOL_MASTER_LIST ml
                ON ml.ML_ID = rev.MLR_ML_ID
            LEFT JOIN TMS_NEW.dbo.MS_OPERATION op
                ON op.OP_ID = rev.MLR_OP_ID
            LEFT JOIN TMS_NEW.dbo.MS_TOOL_CLASS tc
                ON tc.TC_ID = rev.MLR_TC_ID
            LEFT JOIN TMS_NEW.dbo.MS_MAKER maker
                ON maker.MAKER_ID = rev.MLR_MAKER_ID
            LEFT JOIN TMS_NEW.dbo.MS_MATERIAL mat
                ON mat.MAT_ID = rev.MLR_MAT_ID
            LEFT JOIN TMS_NEW.dbo.MS_MACHINES mac
                ON mac.MAC_ID = rev.MLR_MACG_ID
            LEFT JOIN TMS_NEW.dbo.MS_USERS usr
                ON usr.USR_ID = rev.MLR_MODIFIED_BY
            WHERE ml.ML_TYPE = 1
            ORDER BY rev.MLR_ID DESC
        ";

        $q = $this->db_tms->query($sql);
        if (!$q) return array();
        return $q->result_array();
    }

    /**
     * Get data by ID (MLR_ID)
     */
    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;

        $sql = "
            SELECT
                rev.MLR_ID          AS TD_ID,
                ml.ML_ID            AS TD_ML_ID,
                ml.ML_TOOL_DRAW_NO  AS TD_DRAWING_NO,
                rev.MLR_REV         AS TD_REVISION,
                rev.MLR_STATUS      AS TD_STATUS,
                rev.MLR_EFFECTIVE_DATE AS TD_EFFECTIVE_DATE,
                rev.MLR_MODIFIED_DATE  AS TD_MODIFIED_DATE,
                rev.MLR_MODIFIED_BY    AS TD_MODIFIED_BY,
                rev.MLR_OP_ID       AS TD_PROCESS_ID,
                op.OP_NAME          AS TD_OPERATION_NAME,
                rev.MLR_TC_ID       AS TD_TOOL_ID,
                tc.TC_NAME          AS TD_TOOL_NAME,
                rev.MLR_MAT_ID      AS TD_MATERIAL_ID,
                mat.MAT_NAME        AS TD_MATERIAL_NAME,
                rev.MLR_MAKER_ID    AS TD_MAKER_ID,
                maker.MAKER_NAME    AS TD_MAKER_NAME,
                rev.MLR_MACG_ID     AS TD_MACG_ID,
                mac.MAC_NAME        AS TD_MAC_NAME,
                part.PART_ID        AS TD_PRODUCT_ID,
                part.PART_NAME      AS TD_PRODUCT_NAME,
                ISNULL(rev.MLR_DRAWING, '') AS TD_DRAWING_FILE,
                ISNULL(rev.MLR_SKETCH, '') AS TD_SKETCH_FILE
            FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
            INNER JOIN TMS_NEW.dbo.TMS_TOOL_MASTER_LIST ml
                ON ml.ML_ID = rev.MLR_ML_ID
            LEFT JOIN TMS_NEW.dbo.MS_OPERATION op
                ON op.OP_ID = rev.MLR_OP_ID
            LEFT JOIN TMS_NEW.dbo.MS_TOOL_CLASS tc
                ON tc.TC_ID = rev.MLR_TC_ID
            LEFT JOIN TMS_NEW.dbo.MS_MAKER maker
                ON maker.MAKER_ID = rev.MLR_MAKER_ID
            LEFT JOIN TMS_NEW.dbo.MS_MATERIAL mat
                ON mat.MAT_ID = rev.MLR_MAT_ID
            LEFT JOIN TMS_NEW.dbo.MS_MACHINES mac
                ON mac.MAC_ID = rev.MLR_MACG_ID
            LEFT JOIN TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_PARTS mlparts
                ON mlparts.TMLP_ML_ID = ml.ML_ID
            LEFT JOIN TMS_NEW.dbo.MS_PARTS part
                ON part.PART_ID = mlparts.TMLP_PART_ID
            WHERE rev.MLR_ID = ? AND ml.ML_TYPE = 1
        ";

        $q = $this->db_tms->query($sql, array($id));
        if ($q && $q->num_rows() > 0) {
            return $q->row_array();
        }
        return null;
    }

    /**
     * Get all products from MS_PARTS
     */
    public function get_products()
    {
        $sql = "SELECT PART_ID AS PRODUCT_ID, PART_NAME AS PRODUCT_NAME 
                FROM TMS_NEW.dbo.MS_PARTS 
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
                FROM TMS_NEW.dbo.MS_OPERATION 
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
                FROM TMS_NEW.dbo.MS_TOOL_CLASS 
                ORDER BY TC_NAME ASC";
        $q = $this->db_tms->query($sql);
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
    }

    /**
     * Get tool by ID
     */
    public function get_tool_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $sql = "SELECT TC_ID AS TOOL_ID, TC_NAME AS TOOL_NAME 
                FROM TMS_NEW.dbo.MS_TOOL_CLASS 
                WHERE TC_ID = ?";
        $q = $this->db_tms->query($sql, array($id));
        if ($q && $q->num_rows() > 0) {
            return $q->row_array();
        }
        return null;
    }

    /**
     * Get all materials from MS_MATERIAL
     */
    public function get_materials()
    {
        $sql = "SELECT MAT_ID AS MATERIAL_ID, MAT_NAME AS MATERIAL_NAME 
                FROM TMS_NEW.dbo.MS_MATERIAL 
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
                FROM TMS_NEW.dbo.MS_MAKER 
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
                FROM TMS_NEW.dbo.MS_MACHINES 
                ORDER BY MAC_NAME ASC";
        $q = $this->db_tms->query($sql);
        if ($q && $q->num_rows() > 0) {
            return $q->result_array();
        }
        return array();
    }

    /**
     * Get Tool BOM by Product ID (placeholder - adjust based on actual BOM table structure)
     */
    public function get_tool_bom_by_product_id($product_id)
    {
        $product_id = (int)$product_id;
        if ($product_id <= 0) return array();
        
        // Placeholder - adjust based on actual BOM table structure
        // This is a simplified version, you may need to adjust based on your actual BOM table
        return array();
    }

    /**
     * Add new tool drawing
     */
    public function add_data($product_id, $process_id, $drawing_no, $tool_id, $revision, $status, $material_id, $maker_id = 0, $machine_group_id = null, $effective_date = null)
    {
        $product_id = (int)$product_id;
        $process_id = (int)$process_id;
        $drawing_no = trim((string)$drawing_no);
        $tool_id = (int)$tool_id;
        $revision = (int)$revision;
        $status = (int)$status;
        $material_id = ($material_id > 0) ? (int)$material_id : null;
        $maker_id = ($maker_id > 0) ? (int)$maker_id : null;
        $machine_group_id = ($machine_group_id > 0) ? (int)$machine_group_id : null;
        $effective_date = !empty($effective_date) ? trim((string)$effective_date) : null;

        if ($drawing_no === '') {
            $this->messages = 'Drawing No tidak boleh kosong.';
            return false;
        }

        if ($product_id <= 0 || $process_id <= 0) {
            $this->messages = 'Product ID dan Process ID harus lebih dari 0.';
            return false;
        }

        $this->db_tms->trans_start();

        // Check if ML_TOOL_DRAW_NO already exists
        $check_sql = "SELECT ML_ID FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST WHERE ML_TOOL_DRAW_NO = ?";
        $check_q = $this->db_tms->query($check_sql, array($drawing_no));
        $ml_id = null;

        if ($check_q && $check_q->num_rows() > 0) {
            // Use existing ML_ID
            $ml_id = (int)$check_q->row()->ML_ID;
        } else {
            // Insert new TMS_TOOL_MASTER_LIST
            $ml_insert_sql = "INSERT INTO TMS_NEW.dbo.TMS_TOOL_MASTER_LIST (ML_TOOL_DRAW_NO, ML_TYPE) VALUES (?, 1)";
            $this->db_tms->query($ml_insert_sql, array($drawing_no));
            $ml_id = (int)$this->db_tms->insert_id();
            if ($ml_id <= 0) {
                $ml_row = $this->db_tms->query("SELECT IDENT_CURRENT('TMS_TOOL_MASTER_LIST') AS last_id")->row_array();
                if ($ml_row && isset($ml_row['last_id'])) $ml_id = (int)$ml_row['last_id'];
            }

            // Insert product relationship
            if ($ml_id > 0 && $product_id > 0) {
                $parts_sql = "INSERT INTO TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_PARTS (TMLP_ML_ID, TMLP_PART_ID) VALUES (?, ?)";
                $this->db_tms->query($parts_sql, array($ml_id, $product_id));
            }
        }

        if ($ml_id <= 0) {
            $this->db_tms->trans_rollback();
            $this->messages = 'Gagal membuat master list.';
            return false;
        }

        // Insert TMS_TOOL_MASTER_LIST_REV
        $modified_by = isset($this->uid) && $this->uid !== '' ? (string)$this->uid : 'SYSTEM';
        
        // Use effective_date if provided, otherwise use GETDATE()
        if ($effective_date !== null) {
            $rev_sql = "INSERT INTO TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV 
                        (MLR_ML_ID, MLR_OP_ID, MLR_TC_ID, MLR_MAKER_ID, MLR_MAT_ID, MLR_MACG_ID, MLR_REV, MLR_STATUS, MLR_EFFECTIVE_DATE, MLR_MODIFIED_DATE, MLR_MODIFIED_BY) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), ?)";
            $this->db_tms->query($rev_sql, array(
                $ml_id, $process_id, $tool_id > 0 ? $tool_id : null, $maker_id, $material_id, $machine_group_id, 
                $revision, $status, $effective_date, $modified_by
            ));
        } else {
            $rev_sql = "INSERT INTO TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV 
                        (MLR_ML_ID, MLR_OP_ID, MLR_TC_ID, MLR_MAKER_ID, MLR_MAT_ID, MLR_MACG_ID, MLR_REV, MLR_STATUS, MLR_EFFECTIVE_DATE, MLR_MODIFIED_DATE, MLR_MODIFIED_BY) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), GETDATE(), ?)";
            $this->db_tms->query($rev_sql, array(
                $ml_id, $process_id, $tool_id > 0 ? $tool_id : null, $maker_id, $material_id, $machine_group_id, 
                $revision, $status, $modified_by
            ));
        }

        $this->db_tms->trans_complete();

        if ($this->db_tms->trans_status()) {
            $this->messages = 'Tool Drawing Engineering berhasil ditambahkan.';
            return true;
        }

        $this->messages = 'Gagal menambahkan tool drawing.';
        return false;
    }

    /**
     * Edit tool drawing
     */
    public function edit_data_engineering($id, $product_id, $process_id, $drawing_no, $tool_id, $status, $material_id, $maker_id = null, $machine_group_id = null, $effective_date = null, $revision = null)
    {
        $id = (int)$id;
        $product_id = (int)$product_id;
        $process_id = (int)$process_id;
        $drawing_no = trim((string)$drawing_no);
        $tool_id = ($tool_id > 0) ? (int)$tool_id : null;
        $status = (int)$status;
        $material_id = ($material_id > 0) ? (int)$material_id : null;
        $maker_id = ($maker_id > 0) ? (int)$maker_id : null;
        $machine_group_id = ($machine_group_id > 0) ? (int)$machine_group_id : null;
        $effective_date = !empty($effective_date) ? trim((string)$effective_date) : null;
        $revision = ($revision !== null) ? (int)$revision : null;

        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        $this->db_tms->trans_start();

        $ml_id = isset($current['TD_ML_ID']) ? (int)$current['TD_ML_ID'] : 0;
        // Use revision from form, or keep current revision if not provided
        if ($revision === null) {
            $revision = isset($current['TD_REVISION']) ? (int)$current['TD_REVISION'] : 0;
        }

        // Update TMS_TOOL_MASTER_LIST if drawing_no changed
        if ($ml_id > 0 && $drawing_no !== '') {
            $update_ml_sql = "UPDATE TMS_NEW.dbo.TMS_TOOL_MASTER_LIST SET ML_TOOL_DRAW_NO = ? WHERE ML_ID = ?";
            $this->db_tms->query($update_ml_sql, array($drawing_no, $ml_id));
        }

        // Update product relationship if changed
        if ($ml_id > 0 && $product_id > 0) {
            // Delete old relationship
            $del_parts_sql = "DELETE FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_PARTS WHERE TMLP_ML_ID = ?";
            $this->db_tms->query($del_parts_sql, array($ml_id));
            // Insert new relationship
            $parts_sql = "INSERT INTO TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_PARTS (TMLP_ML_ID, TMLP_PART_ID) VALUES (?, ?)";
            $this->db_tms->query($parts_sql, array($ml_id, $product_id));
        }

        // Update TMS_TOOL_MASTER_LIST_REV
        $modified_by = isset($this->uid) && $this->uid !== '' ? (string)$this->uid : 'SYSTEM';
        
        // Build effective date SQL
        $effective_date_sql = '';
        if ($effective_date !== null) {
            $effective_date_sql = ', MLR_EFFECTIVE_DATE = ?';
        }
        
        $update_rev_sql = "UPDATE TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV 
                          SET MLR_OP_ID = ?, MLR_TC_ID = ?, MLR_MAT_ID = ?, MLR_MAKER_ID = ?, MLR_MACG_ID = ?, MLR_STATUS = ?, 
                              MLR_REV = ?, MLR_MODIFIED_DATE = GETDATE(), MLR_MODIFIED_BY = ?" . $effective_date_sql . "
                          WHERE MLR_ID = ?";
        
        $params = array($process_id, $tool_id, $material_id, $maker_id, $machine_group_id, $status, $revision, $modified_by);
        if ($effective_date !== null) {
            $params[] = $effective_date;
        }
        $params[] = $id;
        
        $this->db_tms->query($update_rev_sql, $params);

        $this->db_tms->trans_complete();

        if ($this->db_tms->trans_status()) {
            $this->messages = 'Tool Drawing Engineering berhasil diperbarui.';
            return true;
        }

        $this->messages = 'Gagal memperbarui tool drawing.';
        return false;
    }

    /**
     * Delete tool drawing
     */
    public function delete_data($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            $this->messages = 'ID tidak valid.';
            return false;
        }

        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        $this->db_tms->trans_start();

        $ml_id = isset($current['TD_ML_ID']) ? (int)$current['TD_ML_ID'] : 0;

        // Delete revision
        $del_rev_sql = "DELETE FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV WHERE MLR_ID = ?";
        $this->db_tms->query($del_rev_sql, array($id));

        // Check if there are other revisions for this ML_ID
        if ($ml_id > 0) {
            $check_rev_sql = "SELECT COUNT(*) AS cnt FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV WHERE MLR_ML_ID = ?";
            $check_q = $this->db_tms->query($check_rev_sql, array($ml_id));
            if ($check_q && $check_q->num_rows() > 0) {
                $cnt = (int)$check_q->row()->cnt;
                if ($cnt == 0) {
                    // No more revisions, delete master list and parts
                    $del_parts_sql = "DELETE FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_PARTS WHERE TMLP_ML_ID = ?";
                    $this->db_tms->query($del_parts_sql, array($ml_id));
                    $del_ml_sql = "DELETE FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST WHERE ML_ID = ?";
                    $this->db_tms->query($del_ml_sql, array($ml_id));
                }
            }
        }

        $this->db_tms->trans_complete();

        if ($this->db_tms->trans_status()) {
            $this->messages = 'Tool Drawing Engineering berhasil dihapus.';
            return true;
        }

        $this->messages = 'Gagal menghapus tool drawing.';
        return false;
    }

    /**
     * Get history (placeholder - adjust based on actual history table)
     */
    public function get_history($id)
    {
        // Placeholder - return current record as history
        $current = $this->get_by_id($id);
        if ($current) {
            return array($current);
        }
        return array();
    }

    public $messages = '';
    public $uid = '';
}

