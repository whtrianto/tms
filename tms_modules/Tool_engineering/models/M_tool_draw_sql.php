<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Model alternatif (legacy) untuk menampilkan Tool Drawing menggunakan struktur tabel di struktur-tms.sql
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
class M_tool_draw_sql extends CI_Model
{
    private $db_tms;

    public function __construct()
    {
        parent::__construct();
        $this->db_tms = $this->load->database('tms_db', true);
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
            FROM TMS_DB.dbo.TMS_TOOL_MASTER_LIST revParent -- dummy alias to keep syntax compatible
            ";

        // Query lengkap dengan JOIN
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
            FROM TMS_DB.dbo.TMS_TOOL_MASTER_LIST_REV rev
            INNER JOIN TMS_DB.dbo.TMS_TOOL_MASTER_LIST ml
                ON ml.ML_ID = rev.MLR_ML_ID
            LEFT JOIN TMS_DB.dbo.MS_OPERATION op
                ON op.OP_ID = rev.MLR_OP_ID
            LEFT JOIN TMS_DB.dbo.MS_TOOL_CLASS tc
                ON tc.TC_ID = rev.MLR_TC_ID
            LEFT JOIN TMS_DB.dbo.MS_MAKER maker
                ON maker.MAKER_ID = rev.MLR_MAKER_ID
            LEFT JOIN TMS_DB.dbo.MS_MATERIAL mat
                ON mat.MAT_ID = rev.MLR_MAT_ID
            LEFT JOIN TMS_DB.dbo.MS_MACHINES mac
                ON mac.MAC_ID = rev.MLR_MACG_ID
            LEFT JOIN TMS_DB.dbo.TMS_TOOL_MASTER_LIST_PARTS mlparts
                ON mlparts.TMLP_ML_ID = ml.ML_ID
            LEFT JOIN TMS_DB.dbo.MS_PARTS part
                ON part.PART_ID = mlparts.TMLP_PART_ID
            WHERE ml.ML_TYPE = 1
            ORDER BY rev.MLR_ID DESC
        ";

        $q = $this->db_tms->query($sql);
        if (!$q) return array();
        return $q->result_array();
    }
}

