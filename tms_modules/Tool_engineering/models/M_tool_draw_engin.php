<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_tool_draw_engin extends CI_Model
{
    private $table = 'TMS_DB.dbo.TMS_TC_TOOL_DRAWING_ENGIN';
    public $tms_db;
    public $messages = '';
    public $uid = ''; // will receive username from controller

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_db', TRUE);
    }

    /**
     * Cek apakah kolom ada di tabel (SQL Server INFORMATION_SCHEMA)
     * @param string $col
     * @return bool
     */
    protected function has_column($col)
    {
        $col = trim((string)$col);
        if ($col === '') return false;

        // gunakan INFORMATION_SCHEMA untuk kompatibilitas SQL Server
        $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'TMS_TC_TOOL_DRAWING_ENGIN' AND COLUMN_NAME = ?";
        $q = $this->tms_db->query($sql, array($col));
        return ($q && $q->num_rows() > 0);
    }

    public function get_all()
    {
        // Build select columns - include tooling columns if they exist
        $selectCols = 'TD_ID, TD_PRODUCT_ID, TD_PROCESS_ID, TD_DRAWING_NO, TD_TOOL_NAME, TD_REVISION, TD_STATUS, TD_EFFECTIVE_DATE, TD_MODIFIED_DATE, TD_MODIFIED_BY, TD_MATERIAL_ID';

        // Add tooling columns dynamically if they exist
        if ($this->has_column('TD_MAKER_ID')) {
            $selectCols .= ', TD_MAKER_ID';
        }
        if ($this->has_column('TD_MIN_QTY')) {
            $selectCols .= ', TD_MIN_QTY';
        }
        if ($this->has_column('TD_REPLENISH_QTY')) {
            $selectCols .= ', TD_REPLENISH_QTY';
        }
        if ($this->has_column('TD_PRICE')) {
            $selectCols .= ', TD_PRICE';
        }
        if ($this->has_column('TD_TOOL_LIFE')) {
            $selectCols .= ', TD_TOOL_LIFE';
        }
        if ($this->has_column('TD_DESCRIPTION')) {
            $selectCols .= ', TD_DESCRIPTION';
        }
        if ($this->has_column('TD_SEQUENCE')) {
            $selectCols .= ', TD_SEQUENCE';
        }

        $result = $this->tms_db
            ->select($selectCols)
            ->from($this->table)
            ->order_by('TD_ID', 'DESC')
            ->get();

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    /**
     * Get data by product + process (best-effort filter for Additional Information section)
     */
    public function get_by_product_process($product_id = 0, $process_id = 0)
    {
        $product_id = (int)$product_id;
        $process_id = (int)$process_id;

        $all = $this->get_all();
        if ($product_id <= 0 && $process_id <= 0) {
            return $all;
        }

        $filtered = array();
        foreach ($all as $row) {
            $matchProduct = ($product_id <= 0) || ((int)$row['TD_PRODUCT_ID'] === $product_id);
            $matchProcess = ($process_id <= 0) || ((int)$row['TD_PROCESS_ID'] === $process_id);
            if ($matchProduct && $matchProcess) {
                $filtered[] = $row;
            }
        }
        return $filtered;
    }

    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;

        $result = $this->tms_db->where('TD_ID', $id)->limit(1)->get($this->table);
        // select all columns (caller will use names)
        if ($result && $result->num_rows() > 0) {
            return $result->row_array();
        }
        return null;
    }

    public function get_new_sequence()
    {
        $row = $this->tms_db->select_max('TD_ID')->get($this->table)->row_array();
        return isset($row['TD_ID']) ? ((int)$row['TD_ID'] + 1) : 1;
    }

    /**
     * Get all products from TMS_M_PRODUCT
     */
    public function get_products()
    {
        $table = 'TMS_DB.dbo.TMS_M_PRODUCT';
        $result = $this->tms_db
            ->select('PRODUCT_ID, PRODUCT_NAME')
            ->from($table)
            ->where('IS_DELETED', 0)
            ->order_by('PRODUCT_NAME', 'ASC')
            ->get();

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    /**
     * Get all operations from TMS_M_OPERATION
     */
    public function get_operations()
    {
        $table = 'TMS_DB.dbo.TMS_M_OPERATION';
        $result = $this->tms_db
            ->select('OPERATION_ID, OPERATION_NAME')
            ->from($table)
            ->where('IS_DELETED', 0)
            ->order_by('OPERATION_NAME', 'ASC')
            ->get();

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    /**
     * Get all tools from TMS_M_TOOL
     */
    public function get_tools()
    {
        $table = 'TMS_DB.dbo.TMS_M_TOOL';
        $result = $this->tms_db
            ->select('TOOL_ID, TOOL_NAME')
            ->from($table)
            ->where('IS_DELETED', 0)
            ->order_by('TOOL_NAME', 'ASC')
            ->get();

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    /**
     * Get all makers from TMS_M_MAKER
     */
    public function get_makers()
    {
        $table = 'TMS_DB.dbo.TMS_M_MAKER';
        $result = $this->tms_db
            ->select('MAKER_ID, MAKER_NAME')
            ->from($table)
            ->where('IS_DELETED', 0)
            ->order_by('MAKER_NAME', 'ASC')
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
        $table = 'TMS_DB.dbo.TMS_M_TOOL';
        $result = $this->tms_db->select('TOOL_ID, TOOL_NAME')->from($table)->where('TOOL_ID', $id)->where('IS_DELETED', 0)->limit(1)->get();
        if ($result && $result->num_rows() > 0) return $result->row_array();
        return null;
    }


    /**
     * Get all materials from TMS_M_MATERIAL
     */
    public function get_materials()
    {
        $table = 'TMS_DB.dbo.TMS_M_MATERIAL';
        $result = $this->tms_db
            ->select('MATERIAL_ID, MATERIAL_NAME')
            ->from($table)
            ->where('IS_DELETED', 0)
            ->order_by('MATERIAL_NAME', 'ASC')
            ->get();

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    /**
     * Get material by id
     */
    public function get_material_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $table = 'TMS_DB.dbo.TMS_M_MATERIAL';
        $result = $this->tms_db->select('MATERIAL_ID, MATERIAL_NAME')->from($table)->where('MATERIAL_ID', $id)->where('IS_DELETED', 0)->limit(1)->get();
        if ($result && $result->num_rows() > 0) return $result->row_array();
        return null;
    }

    /**
     * Get Tool BOM by Product ID from TMS_TC_TOOL_BOM_ENGIN
     * Returns array of tool BOM records that match the product
     * @param int $product_id
     * @return array
     */
    public function get_tool_bom_by_product_id($product_id)
    {
        $product_id = (int)$product_id;
        if ($product_id <= 0) return array();

        $table = 'TMS_DB.dbo.TMS_TC_TOOL_BOM_ENGIN';
        
        // Check if PRODUCT_ID column exists (new FK column)
        $has_product_id_col = false;
        try {
            $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'TMS_TC_TOOL_BOM_ENGIN' AND COLUMN_NAME = 'PRODUCT_ID'";
            $q = $this->tms_db->query($sql);
            $has_product_id_col = ($q && $q->num_rows() > 0);
        } catch (Exception $e) {
            log_message('error', '[get_tool_bom_by_product_id] Error checking PRODUCT_ID column: ' . $e->getMessage());
        }

        // Check if PRODUCT column exists (old text column)
        $has_product_col = false;
        try {
            $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                    WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'TMS_TC_TOOL_BOM_ENGIN' AND COLUMN_NAME = 'PRODUCT'";
            $q = $this->tms_db->query($sql);
            $has_product_col = ($q && $q->num_rows() > 0);
        } catch (Exception $e) {
            log_message('error', '[get_tool_bom_by_product_id] Error checking PRODUCT column: ' . $e->getMessage());
        }

        // Get product name for matching with old PRODUCT column
        $product_name = '';
        if ($has_product_col) {
            try {
                $product_row = $this->tms_db->select('PRODUCT_NAME')
                    ->from('TMS_DB.dbo.TMS_M_PRODUCT')
                    ->where('PRODUCT_ID', $product_id)
                    ->where('IS_DELETED', 0)
                    ->limit(1)
                    ->get();
                if ($product_row && $product_row->num_rows() > 0) {
                    $product_name = $product_row->row()->PRODUCT_NAME;
                }
            } catch (Exception $e) {
                log_message('error', '[get_tool_bom_by_product_id] Error getting product name: ' . $e->getMessage());
            }
        }

        // Build query
        $this->tms_db->select('ID, TOOL_BOM');
        
        // Add PRODUCT or PRODUCT_ID to select if exists
        if ($has_product_id_col) {
            $this->tms_db->select('PRODUCT_ID');
        }
        if ($has_product_col) {
            $this->tms_db->select('PRODUCT');
        }

        $this->tms_db->from($table);

        // Build where clause - match by PRODUCT_ID if exists, otherwise match by PRODUCT text
        if ($has_product_id_col) {
            $this->tms_db->where('PRODUCT_ID', $product_id);
        } elseif ($has_product_col && $product_name !== '') {
            $this->tms_db->where('PRODUCT', $product_name);
        } else {
            // No matching column found, return empty array
            return array();
        }

        $result = $this->tms_db->order_by('ID', 'DESC')->get();

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    /* ========== MUTATORS ========== */

    public function add_data($product_id, $process_id, $drawing_no, $tool_name, $revision, $status, $material_id, $maker_id = 0, $min_qty = null, $replenish_qty = null, $price = null, $tool_life = null, $description = null, $sequence = null)
    {
        $product_id = (int)$product_id;
        $process_id = (int)$process_id;
        $drawing_no = trim((string)$drawing_no);
        $tool_name = trim((string)$tool_name);
        $revision = (int)$revision;
        $status = (int)$status;
        $material_id = (int)$material_id;
        $maker_id = (int)$maker_id;
        $min_qty = ($min_qty === null || $min_qty === '') ? null : (int)$min_qty;
        $replenish_qty = ($replenish_qty === null || $replenish_qty === '') ? null : (int)$replenish_qty;
        $price = ($price === null || $price === '') ? null : (float)$price;
        $tool_life = ($tool_life === null || $tool_life === '') ? null : (int)$tool_life;
        $sequence = ($sequence === null || $sequence === '') ? null : (int)$sequence;
        $description = trim((string)$description);

        if ($drawing_no === '') {
            $this->messages = 'Drawing No tidak boleh kosong.';
            return false;
        }

        if ($product_id <= 0) {
            $this->messages = 'Product ID harus lebih dari 0.';
            return false;
        }

        if ($process_id <= 0) {
            $this->messages = 'Process ID harus lebih dari 0.';
            return false;
        }

        $this->tms_db->trans_start();
        $insertData = array(
            'TD_PRODUCT_ID'   => $product_id,
            'TD_PROCESS_ID'   => $process_id,
            'TD_DRAWING_NO'   => $drawing_no,
            'TD_TOOL_NAME'    => $tool_name,
            'TD_REVISION'     => $revision,
            'TD_STATUS'       => $status
        );

        // only include MATERIAL_ID when valid (>0) to avoid FK constraint for 0
        if ($material_id > 0) {
            $insertData['TD_MATERIAL_ID'] = $material_id;
        } else {
            $insertData['TD_MATERIAL_ID'] = null;
        }
        if ($this->has_column('TD_MAKER_ID')) {
            $insertData['TD_MAKER_ID'] = $maker_id > 0 ? $maker_id : null;
        }
        if ($this->has_column('TD_MIN_QTY')) {
            $insertData['TD_MIN_QTY'] = $min_qty;
        }
        if ($this->has_column('TD_REPLENISH_QTY')) {
            $insertData['TD_REPLENISH_QTY'] = $replenish_qty;
        }
        if ($this->has_column('TD_PRICE')) {
            $insertData['TD_PRICE'] = $price;
        }
        if ($this->has_column('TD_TOOL_LIFE')) {
            $insertData['TD_TOOL_LIFE'] = $tool_life;
        }
        if ($this->has_column('TD_DESCRIPTION')) {
            $insertData['TD_DESCRIPTION'] = $description !== '' ? $description : null;
        }
        if ($this->has_column('TD_SEQUENCE')) {
            $insertData['TD_SEQUENCE'] = $sequence;
        }

        // set TD_MODIFIED_BY to the username from controller ($this->uid) for audit trail
        $modifiedBy = '';
        if (isset($this->uid) && $this->uid !== '') {
            $modifiedBy = (string)$this->uid;
        }
        log_message('debug', '[add_data] uid="' . var_export($this->uid, true) . '", modifiedBy="' . $modifiedBy . '"');
        if ($modifiedBy !== '') {
            $insertData['TD_MODIFIED_BY'] = $modifiedBy;
        }

        $ok = $this->tms_db->insert($this->table, $insertData);

        // try to obtain the inserted id and set EFFECTIVE_DATE if column exists
        $new_id = 0;
        if ($ok) {
            $new_id = (int)$this->tms_db->insert_id();
            if ($new_id <= 0) {
                // fallback: try to get IDENT_CURRENT (best-effort)
                $row = $this->tms_db->query("SELECT IDENT_CURRENT('TMS_TC_TOOL_DRAWING_ENGIN') AS last_id")->row_array();
                if ($row && isset($row['last_id'])) $new_id = (int)$row['last_id'];
            }
            if ($this->has_column('TD_EFFECTIVE_DATE') && $new_id > 0) {
                $this->tms_db->query("UPDATE {$this->table} SET TD_EFFECTIVE_DATE = GETDATE() WHERE TD_ID = ?", array($new_id));
            }
        }

        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = 'Tool Drawing Engineering berhasil ditambahkan.';
            // Insert initial history record (best-effort)
            if ($new_id > 0) {
                try {
                    $this->_insert_history_record($new_id, 'INSERT');
                } catch (Exception $e) {
                    log_message('error', '[add_data] history insert failed: ' . $e->getMessage());
                }
            }
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal menambahkan tool drawing. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    public function edit_data($id, $product_id, $process_id, $drawing_no, $tool_name, $revision, $status, $material_id, $maker_id = 0, $min_qty = null, $replenish_qty = null, $price = null, $tool_life = null, $description = null, $sequence = null)
    {
        $id = (int)$id;
        $product_id = (int)$product_id;
        $process_id = (int)$process_id;
        $drawing_no = trim((string)$drawing_no);
        $tool_name = trim((string)$tool_name);
        $revision = (int)$revision;
        $status = (int)$status;
        $material_id = (int)$material_id;
        $maker_id = (int)$maker_id;
        $min_qty = ($min_qty === null || $min_qty === '') ? null : (int)$min_qty;
        $replenish_qty = ($replenish_qty === null || $replenish_qty === '') ? null : (int)$replenish_qty;
        $price = ($price === null || $price === '') ? null : (float)$price;
        $tool_life = ($tool_life === null || $tool_life === '') ? null : (int)$tool_life;
        $sequence = ($sequence === null || $sequence === '') ? null : (int)$sequence;
        $description = trim((string)$description);

        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        if ($drawing_no === '') {
            $this->messages = 'Drawing No tidak boleh kosong.';
            return false;
        }

        if ($product_id <= 0) {
            $this->messages = 'Product ID harus lebih dari 0.';
            return false;
        }

        if ($process_id <= 0) {
            $this->messages = 'Process ID harus lebih dari 0.';
            return false;
        }

        // set TD_MODIFIED_BY to the username from controller ($this->uid) for audit trail
        $modifiedBy = '';
        if (isset($this->uid) && $this->uid !== '') {
            $modifiedBy = (string)$this->uid;
        }
        log_message('debug', '[edit_data] id=' . $id . ', uid="' . var_export($this->uid, true) . '", modifiedBy="' . $modifiedBy . '"');

        // Increment revision automatically on edit
        $oldRevision = isset($current['TD_REVISION']) ? (int)$current['TD_REVISION'] : 0;
        $newRevision = $oldRevision + 1;
        $updateData = array(
            'TD_PRODUCT_ID'  => $product_id,
            'TD_PROCESS_ID'  => $process_id,
            'TD_DRAWING_NO'  => $drawing_no,
            'TD_TOOL_NAME'   => $tool_name,
            'TD_REVISION'    => $newRevision,
            'TD_STATUS'      => $status
        );

        // Only set TD_MODIFIED_BY if we have a valid value
        if ($modifiedBy !== '') {
            $updateData['TD_MODIFIED_BY'] = $modifiedBy;
        }

        // handle MATERIAL_ID properly
        if ($material_id > 0) {
            $updateData['TD_MATERIAL_ID'] = $material_id;
        } else {
            $updateData['TD_MATERIAL_ID'] = null;
        }
        if ($this->has_column('TD_MAKER_ID')) {
            $updateData['TD_MAKER_ID'] = $maker_id > 0 ? $maker_id : null;
        }
        if ($this->has_column('TD_MIN_QTY')) {
            $updateData['TD_MIN_QTY'] = $min_qty;
        }
        if ($this->has_column('TD_REPLENISH_QTY')) {
            $updateData['TD_REPLENISH_QTY'] = $replenish_qty;
        }
        if ($this->has_column('TD_PRICE')) {
            $updateData['TD_PRICE'] = $price;
        }
        if ($this->has_column('TD_TOOL_LIFE')) {
            $updateData['TD_TOOL_LIFE'] = $tool_life;
        }
        if ($this->has_column('TD_DESCRIPTION')) {
            $updateData['TD_DESCRIPTION'] = $description !== '' ? $description : null;
        }
        if ($this->has_column('TD_SEQUENCE')) {
            $updateData['TD_SEQUENCE'] = $sequence;
        }

        $ok = $this->tms_db->where('TD_ID', $id)->update($this->table, $updateData);

        if ($ok) {
            // update TD_MODIFIED_DATE if column exists
            if ($this->has_column('TD_MODIFIED_DATE')) {
                $this->tms_db->query("UPDATE {$this->table} SET TD_MODIFIED_DATE = GETDATE() WHERE TD_ID = ?", array($id));
            }
            // Insert history record for this update (best-effort)
            try {
                $this->_insert_history_record($id, 'UPDATE');
            } catch (Exception $e) {
                log_message('error', '[edit_data] history insert failed: ' . $e->getMessage());
            }
            $this->messages = 'Tool Drawing Engineering berhasil diubah.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal mengubah tool drawing. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * edit_data_engineering: Edit dari layar Engineering saja.
     * Hanya update kolom-kolom utama (product, process, drawing, tool_name,
     * revision, status, material, modified_by/date) dan TIDAK menyentuh
     * kolom tooling (maker, min qty, replenish qty, price, tool life, description, sequence).
     *
     * Dipakai oleh:
     * - Tool_engineering/Tool_draw_engin::submit_data() untuk action EDIT/REVISION
     *   dari view index/edit/revision engineering.
     *
     * Dengan cara ini, jika di form engineering tidak ada input untuk kolom tooling,
     * nilainya di DB tidak akan berubah (tidak menjadi NULL/0).
     */
    public function edit_data_engineering($id, $product_id, $process_id, $drawing_no, $tool_name, $status, $material_id)
    {
        $id         = (int)$id;
        $product_id = (int)$product_id;
        $process_id = (int)$process_id;
        $drawing_no = trim((string)$drawing_no);
        $tool_name  = trim((string)$tool_name);
        $status     = (int)$status;
        $material_id = (int)$material_id;

        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        if ($drawing_no === '') {
            $this->messages = 'Drawing No tidak boleh kosong.';
            return false;
        }

        if ($product_id <= 0) {
            $this->messages = 'Product ID harus lebih dari 0.';
            return false;
        }

        if ($process_id <= 0) {
            $this->messages = 'Process ID harus lebih dari 0.';
            return false;
        }

        // set TD_MODIFIED_BY to the username from controller ($this->uid) for audit trail
        $modifiedBy = '';
        if (isset($this->uid) && $this->uid !== '') {
            $modifiedBy = (string)$this->uid;
        }
        log_message('debug', '[edit_data_engineering] id=' . $id . ', uid="' . var_export($this->uid, true) . '", modifiedBy="' . $modifiedBy . '"');

        // Increment revision automatically (sama seperti edit_data/edit_data_with_tooling)
        $oldRevision = isset($current['TD_REVISION']) ? (int)$current['TD_REVISION'] : 0;
        $newRevision = $oldRevision + 1;

        $updateData = array(
            'TD_PRODUCT_ID' => $product_id,
            'TD_PROCESS_ID' => $process_id,
            'TD_DRAWING_NO' => $drawing_no,
            'TD_TOOL_NAME'  => $tool_name,
            'TD_REVISION'   => $newRevision,
            'TD_STATUS'     => $status
        );

        // handle MATERIAL_ID properly
        if ($material_id > 0) {
            $updateData['TD_MATERIAL_ID'] = $material_id;
        } else {
            $updateData['TD_MATERIAL_ID'] = null;
        }

        // Only set TD_MODIFIED_BY if we have a valid value
        if ($modifiedBy !== '') {
            $updateData['TD_MODIFIED_BY'] = $modifiedBy;
        }

        // Penting: JANGAN set kolom tooling di sini (TD_MAKER_ID, TD_MIN_QTY, dll)
        // supaya nilainya tetap seperti di database.

        $ok = $this->tms_db->where('TD_ID', $id)->update($this->table, $updateData);

        if ($ok) {
            // update TD_MODIFIED_DATE if column exists
            if ($this->has_column('TD_MODIFIED_DATE')) {
                $this->tms_db->query("UPDATE {$this->table} SET TD_MODIFIED_DATE = GETDATE() WHERE TD_ID = ?", array($id));
            }
            // Insert history record for this update (best-effort)
            try {
                $this->_insert_history_record($id, 'UPDATE');
            } catch (Exception $e) {
                log_message('error', '[edit_data_engineering] history insert failed: ' . $e->getMessage());
            }
            $this->messages = 'Tool Drawing Engineering berhasil diubah.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal mengubah tool drawing. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * edit_data_with_tooling: Edit engineering record with full tooling specs
     * Used when editing from Tooling UI which has tool/maker/price/qty/etc.
     */
    public function edit_data_with_tooling($id, $product_id, $process_id, $drawing_no, $tool_name, $revision, $status, $material_id, $maker_id = 0, $min_qty = 0, $replenish_qty = 0, $price = 0.0, $tool_life = 0, $description = '', $sequence = null)
    {
        $id = (int)$id;
        $product_id = (int)$product_id;
        $process_id = (int)$process_id;
        $drawing_no = trim((string)$drawing_no);
        $tool_name = trim((string)$tool_name);
        $revision = (int)$revision;
        $status = (int)$status;
        $material_id = (int)$material_id;
        $maker_id = (int)$maker_id;
        $min_qty = (int)$min_qty;
        $replenish_qty = (int)$replenish_qty;
        $price = (float)$price;
        $tool_life = (int)$tool_life;
        $sequence = ($sequence === null || $sequence === '') ? null : (int)$sequence;
        $description = trim((string)$description);

        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        if ($drawing_no === '') {
            $this->messages = 'Drawing No tidak boleh kosong.';
            return false;
        }

        if ($product_id <= 0) {
            $this->messages = 'Product ID harus lebih dari 0.';
            return false;
        }

        if ($process_id <= 0) {
            $this->messages = 'Process ID harus lebih dari 0.';
            return false;
        }

        // set TD_MODIFIED_BY to the username from controller ($this->uid) for audit trail
        $modifiedBy = '';
        if (isset($this->uid) && $this->uid !== '') {
            $modifiedBy = (string)$this->uid;
        }
        log_message('debug', '[edit_data_with_tooling] id=' . $id . ', uid="' . var_export($this->uid, true) . '", modifiedBy="' . $modifiedBy . '"');

        // Increment revision automatically on tooling edit
        $oldRevision = isset($current['TD_REVISION']) ? (int)$current['TD_REVISION'] : 0;
        $newRevision = $oldRevision + 1;
        log_message('debug', '[edit_data_with_tooling] oldRevision=' . $oldRevision . ', newRevision=' . $newRevision);
        $updateData = array(
            'TD_PRODUCT_ID'  => $product_id,
            'TD_PROCESS_ID'  => $process_id,
            'TD_DRAWING_NO'  => $drawing_no,
            'TD_TOOL_NAME'   => $tool_name,
            'TD_REVISION'    => $newRevision,
            'TD_STATUS'      => $status
        );

        // handle MATERIAL_ID properly
        if ($material_id > 0) {
            $updateData['TD_MATERIAL_ID'] = $material_id;
        } else {
            $updateData['TD_MATERIAL_ID'] = null;
        }

        // Only set TD_MODIFIED_BY if we have a valid value
        if ($modifiedBy !== '') {
            $updateData['TD_MODIFIED_BY'] = $modifiedBy;
        }

        // Add tooling-specific columns if they exist
        if ($this->has_column('TD_MAKER_ID')) {
            $updateData['TD_MAKER_ID'] = ($maker_id > 0) ? $maker_id : null;
        }
        if ($this->has_column('TD_MIN_QTY')) {
            $updateData['TD_MIN_QTY'] = $min_qty;
        }
        if ($this->has_column('TD_REPLENISH_QTY')) {
            $updateData['TD_REPLENISH_QTY'] = $replenish_qty;
        }
        if ($this->has_column('TD_PRICE')) {
            $updateData['TD_PRICE'] = $price;
        }
        if ($this->has_column('TD_TOOL_LIFE')) {
            $updateData['TD_TOOL_LIFE'] = $tool_life;
        }
        if ($this->has_column('TD_DESCRIPTION')) {
            $updateData['TD_DESCRIPTION'] = $description !== '' ? $description : null;
        }
        if ($this->has_column('TD_SEQUENCE')) {
            $updateData['TD_SEQUENCE'] = $sequence;
        }

        $ok = $this->tms_db->where('TD_ID', $id)->update($this->table, $updateData);

        if ($ok) {
            // update TD_MODIFIED_DATE if column exists
            if ($this->has_column('TD_MODIFIED_DATE')) {
                $this->tms_db->query("UPDATE {$this->table} SET TD_MODIFIED_DATE = GETDATE() WHERE TD_ID = ?", array($id));
            }
            // Insert history record for this update (best-effort)
            try {
                $this->_insert_history_record($id, 'UPDATE');
            } catch (Exception $e) {
                log_message('error', '[edit_data_with_tooling] history insert failed: ' . $e->getMessage());
            }
            $this->messages = 'Tool Drawing Engineering berhasil diubah.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal mengubah tool drawing. ' . (isset($err['message']) ? $err['message'] : '');
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

        $ok = $this->tms_db->delete($this->table, array('TD_ID' => $id));

        if ($ok) {
            $this->messages = 'Tool Drawing Engineering berhasil dihapus.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal menghapus tool drawing. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Get revision history for a specific record (using JSON_EXTRACT on SQL Server)
     * Since we don't have a separate history table yet, we'll use a simpler approach:
     * Store history as JSON in a new column or create history records on-the-fly
     * 
     * For now, return current record as a pseudo-history
     */
    public function get_history($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            log_message('debug', '[M_tool_draw_engin::get_history] invalid id=' . var_export($id, true));
            return array();
        }
        // First try stored procedure sp_GetToolDrawingEnginHistory
        try {
            $sql = "EXEC sp_GetToolDrawingEnginHistory @TD_ID = ?";
            $q = $this->tms_db->query($sql, array($id));
            if ($q && $q->num_rows() > 0) {
                $rows = $q->result_array();
                $history = array();
                foreach ($rows as $r) {
                    $h = array();
                    $h['HISTORY_ID'] = isset($r['HISTORY_ID']) ? $r['HISTORY_ID'] : null;
                    $h['TD_ID'] = isset($r['TD_ID']) ? (int)$r['TD_ID'] : $id;
                    $h['TD_REVISION'] = isset($r['REVISION']) ? (int)$r['REVISION'] : (isset($r['TD_REVISION']) ? (int)$r['TD_REVISION'] : 0);
                    // map status (may be string 'Active'/'Inactive' or numeric)
                    if (isset($r['STATUS'])) {
                        $st = $r['STATUS'];
                        if (is_string($st)) {
                            $h['TD_STATUS'] = (strtolower($st) === 'active') ? 1 : 0;
                        } else {
                            $h['TD_STATUS'] = (int)$st;
                        }
                    } else {
                        $h['TD_STATUS'] = isset($r['TD_STATUS']) ? (int)$r['TD_STATUS'] : 0;
                    }
                    $h['TD_EFFECTIVE_DATE'] = isset($r['EFFECTIVE_DATE']) ? $r['EFFECTIVE_DATE'] : (isset($r['TD_EFFECTIVE_DATE']) ? $r['TD_EFFECTIVE_DATE'] : '');
                    $h['TD_MODIFIED_DATE'] = isset($r['MODIFIED_DATE']) ? $r['MODIFIED_DATE'] : (isset($r['TD_MODIFIED_DATE']) ? $r['TD_MODIFIED_DATE'] : (isset($r['HISTORY_CREATED_DATE']) ? $r['HISTORY_CREATED_DATE'] : ''));
                    $h['TD_MODIFIED_BY'] = isset($r['TD_MODIFIED_BY']) ? $r['TD_MODIFIED_BY'] : '';
                    // Get IDs from stored procedure result - handle both direct field names and aliases
                    $h['TD_PRODUCT_ID'] = 0;
                    if (isset($r['TD_PRODUCT_ID']) && $r['TD_PRODUCT_ID'] !== null && $r['TD_PRODUCT_ID'] !== '') {
                        $h['TD_PRODUCT_ID'] = (int)$r['TD_PRODUCT_ID'];
                    } elseif (isset($r['PRODUCT_ID']) && $r['PRODUCT_ID'] !== null && $r['PRODUCT_ID'] !== '') {
                        $h['TD_PRODUCT_ID'] = (int)$r['PRODUCT_ID'];
                    }
                    
                    $h['TD_PROCESS_ID'] = 0;
                    if (isset($r['TD_PROCESS_ID']) && $r['TD_PROCESS_ID'] !== null && $r['TD_PROCESS_ID'] !== '') {
                        $h['TD_PROCESS_ID'] = (int)$r['TD_PROCESS_ID'];
                    } elseif (isset($r['PROCESS_ID']) && $r['PROCESS_ID'] !== null && $r['PROCESS_ID'] !== '') {
                        $h['TD_PROCESS_ID'] = (int)$r['PROCESS_ID'];
                    } elseif (isset($r['OPERATION_ID']) && $r['OPERATION_ID'] !== null && $r['OPERATION_ID'] !== '') {
                        $h['TD_PROCESS_ID'] = (int)$r['OPERATION_ID'];
                    }
                    
                    $h['TD_DRAWING_NO'] = isset($r['TD_DRAWING_NO']) ? $r['TD_DRAWING_NO'] : (isset($r['DRAWING_NO']) ? $r['DRAWING_NO'] : '');
                    $h['TD_TOOL_NAME'] = isset($r['TOOL_NAME']) ? $r['TOOL_NAME'] : (isset($r['TD_TOOL_NAME']) ? $r['TD_TOOL_NAME'] : '');
                    
                    $h['TD_MATERIAL_ID'] = 0;
                    if (isset($r['TD_MATERIAL_ID']) && $r['TD_MATERIAL_ID'] !== null && $r['TD_MATERIAL_ID'] !== '') {
                        $h['TD_MATERIAL_ID'] = (int)$r['TD_MATERIAL_ID'];
                    } elseif (isset($r['MATERIAL_ID']) && $r['MATERIAL_ID'] !== null && $r['MATERIAL_ID'] !== '') {
                        $h['TD_MATERIAL_ID'] = (int)$r['MATERIAL_ID'];
                    }
                    // tooling fields from SP result (if SP returns them)
                    // Preserve actual values from database - don't default to 0 if value exists
                    $h['TD_MAKER_ID'] = isset($r['TD_MAKER_ID']) && $r['TD_MAKER_ID'] !== null && $r['TD_MAKER_ID'] !== '' ? (int)$r['TD_MAKER_ID'] : (isset($r['MAKER_ID']) && $r['MAKER_ID'] !== null && $r['MAKER_ID'] !== '' ? (int)$r['MAKER_ID'] : null);
                    $h['MAKER_NAME'] = isset($r['MAKER_NAME']) && $r['MAKER_NAME'] !== null && $r['MAKER_NAME'] !== '' ? $r['MAKER_NAME'] : (isset($r['MAKER']) && $r['MAKER'] !== null && $r['MAKER'] !== '' ? $r['MAKER'] : null);
                    // Preserve actual values including 0 - only default to 0 if truly null/not set
                    $h['TD_MIN_QTY'] = isset($r['TD_MIN_QTY']) && $r['TD_MIN_QTY'] !== null && $r['TD_MIN_QTY'] !== '' ? (int)$r['TD_MIN_QTY'] : (isset($r['MIN_QTY']) && $r['MIN_QTY'] !== null && $r['MIN_QTY'] !== '' ? (int)$r['MIN_QTY'] : null);
                    $h['TD_REPLENISH_QTY'] = isset($r['TD_REPLENISH_QTY']) && $r['TD_REPLENISH_QTY'] !== null && $r['TD_REPLENISH_QTY'] !== '' ? (int)$r['TD_REPLENISH_QTY'] : (isset($r['REPLENISH_QTY']) && $r['REPLENISH_QTY'] !== null && $r['REPLENISH_QTY'] !== '' ? (int)$r['REPLENISH_QTY'] : null);
                    $h['TD_PRICE'] = isset($r['TD_PRICE']) && $r['TD_PRICE'] !== null && $r['TD_PRICE'] !== '' ? (float)$r['TD_PRICE'] : (isset($r['PRICE']) && $r['PRICE'] !== null && $r['PRICE'] !== '' ? (float)$r['PRICE'] : null);
                    $h['TD_TOOL_LIFE'] = isset($r['TD_TOOL_LIFE']) && $r['TD_TOOL_LIFE'] !== null && $r['TD_TOOL_LIFE'] !== '' ? (int)$r['TD_TOOL_LIFE'] : (isset($r['TOOL_LIFE']) && $r['TOOL_LIFE'] !== null && $r['TOOL_LIFE'] !== '' ? (int)$r['TOOL_LIFE'] : null);
                    $h['TD_DESCRIPTION'] = isset($r['TD_DESCRIPTION']) && $r['TD_DESCRIPTION'] !== null && $r['TD_DESCRIPTION'] !== '' ? $r['TD_DESCRIPTION'] : (isset($r['DESCRIPTION']) && $r['DESCRIPTION'] !== null && $r['DESCRIPTION'] !== '' ? $r['DESCRIPTION'] : null);
                    // keep master snapshots too
                    $h['PRODUCT_NAME'] = isset($r['PRODUCT_NAME']) ? $r['PRODUCT_NAME'] : null;
                    $h['OPERATION_NAME'] = isset($r['OPERATION_NAME']) ? $r['OPERATION_NAME'] : null;
                    $h['TOOL_NAME'] = isset($r['TOOL_NAME']) ? $r['TOOL_NAME'] : (isset($r['TOOL_RESOLVED_NAME']) ? $r['TOOL_RESOLVED_NAME'] : null);
                    $h['MATERIAL_NAME'] = isset($r['MATERIAL_NAME']) ? $r['MATERIAL_NAME'] : null;
                    $history[] = $h;
                }
                log_message('debug', '[M_tool_draw_engin::get_history] returning ' . count($history) . ' rows from sp_GetToolDrawingEnginHistory for id=' . $id);
                return $history;
            }
        } catch (Exception $e) {
            log_message('warning', '[M_tool_draw_engin::get_history] sp_GetToolDrawingEnginHistory call failed: ' . $e->getMessage());
        }

        // Fallback: read directly from history table if exists
        try {
            $tblCheck = $this->tms_db->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'TMS_TC_TOOL_DRAWING_ENGIN_HISTORY' AND TABLE_SCHEMA = 'dbo'");
            if ($tblCheck && $tblCheck->num_rows() > 0) {
                $q2 = $this->tms_db->select('*')->from('TMS_DB.dbo.TMS_TC_TOOL_DRAWING_ENGIN_HISTORY')->where('TD_ID', $id)->order_by('HISTORY_SEQUENCE', 'DESC')->get();
                if ($q2 && $q2->num_rows() > 0) {
                    $rows = $q2->result_array();
                    $history = array();
                    foreach ($rows as $r) {
                        // Pass row as-is from DB and only ensure numeric types for tooling fields
                        $h = $r;
                        // Cast numeric fields to proper types
                        $h['TD_ID'] = (int)$h['TD_ID'];
                        $h['TD_REVISION'] = (int)$h['TD_REVISION'];
                        $h['TD_STATUS'] = (int)$h['TD_STATUS'];
                        $h['TD_PRODUCT_ID'] = (int)$h['TD_PRODUCT_ID'];
                        $h['TD_PROCESS_ID'] = (int)$h['TD_PROCESS_ID'];
                        $h['TD_MATERIAL_ID'] = (int)$h['TD_MATERIAL_ID'];
                        // Ensure tooling fields exist and have proper types - preserve actual values from DB
                        // Log raw values for debugging
                        $raw_min_qty = isset($h['TD_MIN_QTY']) ? $h['TD_MIN_QTY'] : 'NOT_SET';
                        $raw_replenish_qty = isset($h['TD_REPLENISH_QTY']) ? $h['TD_REPLENISH_QTY'] : 'NOT_SET';
                        $raw_price = isset($h['TD_PRICE']) ? $h['TD_PRICE'] : 'NOT_SET';
                        $raw_tool_life = isset($h['TD_TOOL_LIFE']) ? $h['TD_TOOL_LIFE'] : 'NOT_SET';
                        log_message('debug', '[M_tool_draw_engin::get_history] Raw from DB - TD_MIN_QTY=' . var_export($raw_min_qty, true) . ', TD_REPLENISH_QTY=' . var_export($raw_replenish_qty, true) . ', TD_PRICE=' . var_export($raw_price, true) . ', TD_TOOL_LIFE=' . var_export($raw_tool_life, true));
                        
                        $h['TD_MAKER_ID'] = isset($h['TD_MAKER_ID']) && $h['TD_MAKER_ID'] !== null && $h['TD_MAKER_ID'] !== '' ? (int)$h['TD_MAKER_ID'] : null;
                        // Preserve actual values from database - only convert null/empty to null (not 0), let controller handle default to 0
                        // This ensures we preserve actual database values including 0
                        if (isset($h['TD_MIN_QTY']) && $h['TD_MIN_QTY'] !== null && $h['TD_MIN_QTY'] !== '') {
                            $h['TD_MIN_QTY'] = (int)$h['TD_MIN_QTY'];
                        } else {
                            $h['TD_MIN_QTY'] = null; // Let controller decide default
                        }
                        if (isset($h['TD_REPLENISH_QTY']) && $h['TD_REPLENISH_QTY'] !== null && $h['TD_REPLENISH_QTY'] !== '') {
                            $h['TD_REPLENISH_QTY'] = (int)$h['TD_REPLENISH_QTY'];
                        } else {
                            $h['TD_REPLENISH_QTY'] = null; // Let controller decide default
                        }
                        if (isset($h['TD_PRICE']) && $h['TD_PRICE'] !== null && $h['TD_PRICE'] !== '') {
                            $h['TD_PRICE'] = (float)$h['TD_PRICE'];
                        } else {
                            $h['TD_PRICE'] = null; // Let controller decide default
                        }
                        if (isset($h['TD_TOOL_LIFE']) && $h['TD_TOOL_LIFE'] !== null && $h['TD_TOOL_LIFE'] !== '') {
                            $h['TD_TOOL_LIFE'] = (int)$h['TD_TOOL_LIFE'];
                        } else {
                            $h['TD_TOOL_LIFE'] = null; // Let controller decide default
                        }
                        $h['TD_DESCRIPTION'] = isset($h['TD_DESCRIPTION']) && $h['TD_DESCRIPTION'] !== null && $h['TD_DESCRIPTION'] !== '' ? $h['TD_DESCRIPTION'] : '';
                        $h['MAKER_NAME'] = isset($h['MAKER_NAME']) && $h['MAKER_NAME'] !== null && $h['MAKER_NAME'] !== '' ? $h['MAKER_NAME'] : '';
                        $history[] = $h;
                    }
                    log_message('debug', '[M_tool_draw_engin::get_history] returning ' . count($history) . ' rows from history table for id=' . $id);
                    return $history;
                }
            }
        } catch (Exception $e) {
            log_message('warning', '[M_tool_draw_engin::get_history] direct history table read failed: ' . $e->getMessage());
        }

        // If no history table or SP available, return current record as pseudo-history
        $row = $this->get_by_id($id);
        if (!$row) {
            log_message('debug', '[M_tool_draw_engin::get_history] no record found for id=' . $id);
            return array();
        }

        $history = array(
            array(
                'HISTORY_ID' => 1,
                'TD_ID' => (int)$row['TD_ID'],
                'TD_REVISION' => isset($row['TD_REVISION']) ? (int)$row['TD_REVISION'] : 0,
                'TD_STATUS' => isset($row['TD_STATUS']) ? (int)$row['TD_STATUS'] : 0,
                'TD_EFFECTIVE_DATE' => isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : '',
                'TD_MODIFIED_DATE' => isset($row['TD_MODIFIED_DATE']) ? $row['TD_MODIFIED_DATE'] : '',
                'TD_MODIFIED_BY' => isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : '',
                'TD_PRODUCT_ID' => isset($row['TD_PRODUCT_ID']) ? (int)$row['TD_PRODUCT_ID'] : 0,
                'TD_PROCESS_ID' => isset($row['TD_PROCESS_ID']) ? (int)$row['TD_PROCESS_ID'] : 0,
                'TD_DRAWING_NO' => isset($row['TD_DRAWING_NO']) ? $row['TD_DRAWING_NO'] : '',
                'TD_TOOL_NAME' => isset($row['TD_TOOL_NAME']) ? $row['TD_TOOL_NAME'] : '',
                'TD_MATERIAL_ID' => isset($row['TD_MATERIAL_ID']) ? (int)$row['TD_MATERIAL_ID'] : 0,
                // Tooling-specific fields: include if present in engineering table so UI can show them
                'TD_MAKER_ID' => isset($row['TD_MAKER_ID']) ? (int)$row['TD_MAKER_ID'] : (isset($row['MAKER_ID']) ? (int)$row['MAKER_ID'] : null),
                'MAKER_NAME' => null,
                'TD_MIN_QTY' => isset($row['TD_MIN_QTY']) ? (int)$row['TD_MIN_QTY'] : (isset($row['MIN_QTY']) ? (int)$row['MIN_QTY'] : 0),
                'TD_REPLENISH_QTY' => isset($row['TD_REPLENISH_QTY']) ? (int)$row['TD_REPLENISH_QTY'] : (isset($row['REPLENISH_QTY']) ? (int)$row['REPLENISH_QTY'] : 0),
                'TD_PRICE' => isset($row['TD_PRICE']) ? (float)$row['TD_PRICE'] : (isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0),
                'TD_TOOL_LIFE' => isset($row['TD_TOOL_LIFE']) ? (int)$row['TD_TOOL_LIFE'] : (isset($row['TOOL_LIFE']) ? (int)$row['TOOL_LIFE'] : 0),
                'TD_DESCRIPTION' => isset($row['TD_DESCRIPTION']) ? $row['TD_DESCRIPTION'] : (isset($row['DESCRIPTION']) ? $row['DESCRIPTION'] : null)
            )
        );
        log_message('debug', '[M_tool_draw_engin::get_history] returning pseudo-history count=' . count($history) . ' for id=' . $id);
        return $history;
    }

    /**
     * Insert a history snapshot for a given TD_ID.
     * Tries to call stored procedure sp_InsertToolDrawingEnginHistory if available,
     * otherwise attempts a direct insert into the history table.
     * @param int $td_id
     * @param string $action 'INSERT'|'UPDATE'|'DELETE'
     */
    protected function _insert_history_record($td_id, $action = 'UPDATE')
    {
        $td_id = (int)$td_id;
        if ($td_id <= 0) return false;

        $row = $this->get_by_id($td_id);
        if (!$row) return false;

        // Resolve master names (best-effort)
        $product_name = '';
        if (isset($row['TD_PRODUCT_ID']) && (int)$row['TD_PRODUCT_ID'] > 0) {
            $q = $this->tms_db->select('PRODUCT_NAME')->from('TMS_DB.dbo.TMS_M_PRODUCT')->where('PRODUCT_ID', (int)$row['TD_PRODUCT_ID'])->limit(1)->get();
            if ($q && $q->num_rows() > 0) $product_name = $q->row()->PRODUCT_NAME;
        }

        $operation_name = '';
        if (isset($row['TD_PROCESS_ID']) && (int)$row['TD_PROCESS_ID'] > 0) {
            $q = $this->tms_db->select('OPERATION_NAME')->from('TMS_DB.dbo.TMS_M_OPERATION')->where('OPERATION_ID', (int)$row['TD_PROCESS_ID'])->limit(1)->get();
            if ($q && $q->num_rows() > 0) $operation_name = $q->row()->OPERATION_NAME;
        }

        $material_name = '';
        if (isset($row['TD_MATERIAL_ID']) && (int)$row['TD_MATERIAL_ID'] > 0) {
            $q = $this->tms_db->select('MATERIAL_NAME')->from('TMS_DB.dbo.TMS_M_MATERIAL')->where('MATERIAL_ID', (int)$row['TD_MATERIAL_ID'])->limit(1)->get();
            if ($q && $q->num_rows() > 0) $material_name = $q->row()->MATERIAL_NAME;
        }

        // Resolve tool name: if TD_TOOL_NAME is numeric and matches tool id, get tool name
        $tool_resolved = '';
        if (isset($row['TD_TOOL_NAME']) && $row['TD_TOOL_NAME'] !== '') {
            $maybe = trim((string)$row['TD_TOOL_NAME']);
            if (ctype_digit($maybe)) {
                $q = $this->tms_db->select('TOOL_NAME')->from('TMS_DB.dbo.TMS_M_TOOL')->where('TOOL_ID', (int)$maybe)->limit(1)->get();
                if ($q && $q->num_rows() > 0) $tool_resolved = $q->row()->TOOL_NAME;
            }
            if ($tool_resolved === '') $tool_resolved = $row['TD_TOOL_NAME'];
        }

        // Collect tooling-specific fields (if present) so we can store them in history
        $maker_id = isset($row['TD_MAKER_ID']) ? (int)$row['TD_MAKER_ID'] : (isset($row['MAKER_ID']) ? (int)$row['MAKER_ID'] : null);
        $maker_name = '';
        if ($maker_id && $maker_id > 0) {
            $q = $this->tms_db->select('MAKER_NAME')->from('TMS_DB.dbo.TMS_M_MAKER')->where('MAKER_ID', $maker_id)->limit(1)->get();
            if ($q && $q->num_rows() > 0) $maker_name = $q->row()->MAKER_NAME;
        } elseif (isset($row['MAKER_NAME'])) {
            $maker_name = $row['MAKER_NAME'];
        }

        $min_qty = isset($row['TD_MIN_QTY']) ? (int)$row['TD_MIN_QTY'] : (isset($row['MIN_QTY']) ? (int)$row['MIN_QTY'] : 0);
        $replenish_qty = isset($row['TD_REPLENISH_QTY']) ? (int)$row['TD_REPLENISH_QTY'] : (isset($row['REPLENISH_QTY']) ? (int)$row['REPLENISH_QTY'] : 0);
        $price_val = isset($row['TD_PRICE']) ? (float)$row['TD_PRICE'] : (isset($row['PRICE']) ? (float)$row['PRICE'] : 0.0);
        $tool_life_val = isset($row['TD_TOOL_LIFE']) ? (int)$row['TD_TOOL_LIFE'] : (isset($row['TOOL_LIFE']) ? (int)$row['TOOL_LIFE'] : 0);
        $description_val = isset($row['TD_DESCRIPTION']) ? $row['TD_DESCRIPTION'] : (isset($row['DESCRIPTION']) ? $row['DESCRIPTION'] : null);

        $params = array(
            'TD_ID' => $td_id,
            'TD_PRODUCT_ID' => isset($row['TD_PRODUCT_ID']) ? (int)$row['TD_PRODUCT_ID'] : null,
            'PRODUCT_NAME' => $product_name,
            'TD_PROCESS_ID' => isset($row['TD_PROCESS_ID']) ? (int)$row['TD_PROCESS_ID'] : null,
            'OPERATION_NAME' => $operation_name,
            'TD_TOOL_NAME' => isset($row['TD_TOOL_NAME']) ? $row['TD_TOOL_NAME'] : null,
            'TOOL_RESOLVED_NAME' => $tool_resolved,
            'TD_DRAWING_NO' => isset($row['TD_DRAWING_NO']) ? $row['TD_DRAWING_NO'] : null,
            'TD_REVISION' => isset($row['TD_REVISION']) ? (int)$row['TD_REVISION'] : 0,
            'TD_STATUS' => isset($row['TD_STATUS']) ? (int)$row['TD_STATUS'] : 0,
            'TD_EFFECTIVE_DATE' => isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : null,
            'TD_MODIFIED_DATE' => isset($row['TD_MODIFIED_DATE']) ? $row['TD_MODIFIED_DATE'] : null,
            'TD_MODIFIED_BY' => isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : (isset($this->uid) ? $this->uid : null),
            'TD_MATERIAL_ID' => isset($row['TD_MATERIAL_ID']) ? (int)$row['TD_MATERIAL_ID'] : null,
            'MATERIAL_NAME' => $material_name,
            // tooling fields
            'TD_MAKER_ID' => $maker_id,
            'MAKER_NAME' => $maker_name,
            'TD_MIN_QTY' => $min_qty,
            'TD_REPLENISH_QTY' => $replenish_qty,
            'TD_PRICE' => $price_val,
            'TD_TOOL_LIFE' => $tool_life_val,
            'TD_DESCRIPTION' => $description_val,
            'HISTORY_ACTION' => $action
        );

        // Try calling stored procedure first
        try {
            // Call stored procedure including tooling columns if SP is updated accordingly
            $sql = "EXEC sp_InsertToolDrawingEnginHistory 
                @TD_ID = ?, @TD_PRODUCT_ID = ?, @PRODUCT_NAME = ?, @TD_PROCESS_ID = ?, @OPERATION_NAME = ?,
                @TD_TOOL_NAME = ?, @TOOL_RESOLVED_NAME = ?, @TD_DRAWING_NO = ?, @TD_REVISION = ?, @TD_STATUS = ?,
                @TD_EFFECTIVE_DATE = ?, @TD_MODIFIED_DATE = ?, @TD_MODIFIED_BY = ?, @TD_MATERIAL_ID = ?, @MATERIAL_NAME = ?,
                @TD_MAKER_ID = ?, @MAKER_NAME = ?, @TD_MIN_QTY = ?, @TD_REPLENISH_QTY = ?, @TD_PRICE = ?, @TD_TOOL_LIFE = ?, @TD_DESCRIPTION = ?, @HISTORY_ACTION = ?";
            $spParams = array(
                $params['TD_ID'],
                $params['TD_PRODUCT_ID'],
                $params['PRODUCT_NAME'],
                $params['TD_PROCESS_ID'],
                $params['OPERATION_NAME'],
                $params['TD_TOOL_NAME'],
                $params['TOOL_RESOLVED_NAME'],
                $params['TD_DRAWING_NO'],
                $params['TD_REVISION'],
                $params['TD_STATUS'],
                $params['TD_EFFECTIVE_DATE'],
                $params['TD_MODIFIED_DATE'],
                $params['TD_MODIFIED_BY'],
                $params['TD_MATERIAL_ID'],
                $params['MATERIAL_NAME'],
                $params['TD_MAKER_ID'],
                $params['MAKER_NAME'],
                $params['TD_MIN_QTY'],
                $params['TD_REPLENISH_QTY'],
                $params['TD_PRICE'],
                $params['TD_TOOL_LIFE'],
                $params['TD_DESCRIPTION'],
                $params['HISTORY_ACTION']
            );
            $this->tms_db->query($sql, $spParams);
            return true;
        } catch (Exception $e) {
            log_message('warning', 'sp_InsertToolDrawingEnginHistory failed or not present: ' . $e->getMessage());
        }

        // Fallback: direct insert into history table if it exists
        $hasHistTable = $this->tms_db->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'TMS_TC_TOOL_DRAWING_ENGIN_HISTORY' AND TABLE_SCHEMA = 'dbo'")->num_rows() > 0;
        if ($hasHistTable) {
            $insert = array(
                'TD_ID' => $params['TD_ID'],
                'TD_PRODUCT_ID' => $params['TD_PRODUCT_ID'],
                'PRODUCT_NAME' => $params['PRODUCT_NAME'],
                'TD_PROCESS_ID' => $params['TD_PROCESS_ID'],
                'OPERATION_NAME' => $params['OPERATION_NAME'],
                'TD_TOOL_NAME' => $params['TD_TOOL_NAME'],
                'TOOL_RESOLVED_NAME' => $params['TOOL_RESOLVED_NAME'],
                'TD_DRAWING_NO' => $params['TD_DRAWING_NO'],
                'TD_REVISION' => $params['TD_REVISION'],
                'TD_STATUS' => $params['TD_STATUS'],
                'TD_EFFECTIVE_DATE' => $params['TD_EFFECTIVE_DATE'],
                'TD_MODIFIED_DATE' => $params['TD_MODIFIED_DATE'],
                'TD_MODIFIED_BY' => $params['TD_MODIFIED_BY'],
                'TD_MATERIAL_ID' => $params['TD_MATERIAL_ID'],
                'MATERIAL_NAME' => $params['MATERIAL_NAME'],
                // tooling columns
                'TD_MAKER_ID' => $params['TD_MAKER_ID'],
                'MAKER_NAME' => $params['MAKER_NAME'],
                'TD_MIN_QTY' => $params['TD_MIN_QTY'],
                'TD_REPLENISH_QTY' => $params['TD_REPLENISH_QTY'],
                'TD_PRICE' => $params['TD_PRICE'],
                'TD_TOOL_LIFE' => $params['TD_TOOL_LIFE'],
                'TD_DESCRIPTION' => $params['TD_DESCRIPTION'],
                'HISTORY_ACTION' => $params['HISTORY_ACTION']
            );
            $this->tms_db->insert('TMS_TC_TOOL_DRAWING_ENGIN_HISTORY', $insert);
            return true;
        }

        return false;
    }
}
