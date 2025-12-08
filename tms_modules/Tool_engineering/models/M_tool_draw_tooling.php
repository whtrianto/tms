<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!class_exists('M_tool_draw_tooling')) {
    class M_tool_draw_tooling extends CI_Model
    {
    private $table = 'TMS_DB.dbo.TMS_TC_TOOL_DRAWING_TOOLING';
    public $tms_db;
    public $messages = '';
    public $uid = ''; // will receive username from controller

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_db', TRUE);
    }

    /**
     * Check if column exists in table (SQL Server INFORMATION_SCHEMA)
     * @param string $col
     * @return bool
     */
    protected function has_column($col)
    {
        $col = trim((string)$col);
        if ($col === '') return false;

        // use INFORMATION_SCHEMA for SQL Server compatibility
        $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'TMS_TC_TOOL_DRAWING_TOOLING' AND COLUMN_NAME = ?";
        $q = $this->tms_db->query($sql, array($col));
        return ($q && $q->num_rows() > 0);
    }

    public function get_all()
    {
        $selectCols = 'TT_ID, TT_TOOL_ID, TT_MIN_QTY, TT_REPLENISH_QTY, TT_MAKER_ID, TT_PRICE, TT_DESCRIPTION, TT_EFFECTIVE_DATE, TT_MATERIAL_ID, TT_TOOL_LIFE, TT_MODIFIED_DATE, TT_MODIFIED_BY';

        $result = $this->tms_db
            ->select($selectCols)
            ->from($this->table)
            ->order_by('TT_ID', 'DESC')
            ->get();

        if ($result && $result->num_rows() > 0) {
            return $result->result_array();
        }
        return array();
    }

    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;

        $result = $this->tms_db->where('TT_ID', $id)->limit(1)->get($this->table);
        if ($result && $result->num_rows() > 0) {
            return $result->row_array();
        }
        return null;
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

    public function get_maker_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $table = 'TMS_DB.dbo.TMS_M_MAKER';
        $result = $this->tms_db->select('MAKER_ID, MAKER_NAME')->from($table)->where('MAKER_ID', $id)->where('IS_DELETED', 0)->limit(1)->get();
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

    public function get_material_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        $table = 'TMS_DB.dbo.TMS_M_MATERIAL';
        $result = $this->tms_db->select('MATERIAL_ID, MATERIAL_NAME')->from($table)->where('MATERIAL_ID', $id)->where('IS_DELETED', 0)->limit(1)->get();
        if ($result && $result->num_rows() > 0) return $result->row_array();
        return null;
    }

    /* ========== MUTATORS ========== */

    public function add_data($tool_id, $min_qty, $replenish_qty, $maker_id, $price, $description, $material_id, $tool_life)
    {
        $tool_id = (int)$tool_id;
        $min_qty = (int)$min_qty;
        $replenish_qty = (int)$replenish_qty;
        $maker_id = (int)$maker_id;
        $price = (float)$price;
        $description = trim((string)$description);
        $material_id = (int)$material_id;
        $tool_life = (int)$tool_life;

        if ($tool_id <= 0) {
            $this->messages = 'Tool ID harus lebih dari 0.';
            return false;
        }

        $this->tms_db->trans_start();
        $insertData = array(
            'TT_TOOL_ID'        => $tool_id,
            'TT_MIN_QTY'        => $min_qty,
            'TT_REPLENISH_QTY'  => $replenish_qty,
            'TT_PRICE'          => $price,
            'TT_DESCRIPTION'    => $description,
            'TT_TOOL_LIFE'      => $tool_life
        );

        // only include MAKER_ID when valid (>0)
        if ($maker_id > 0) {
            $insertData['TT_MAKER_ID'] = $maker_id;
        } else {
            $insertData['TT_MAKER_ID'] = null;
        }

        // only include MATERIAL_ID when valid (>0)
        if ($material_id > 0) {
            $insertData['TT_MATERIAL_ID'] = $material_id;
        } else {
            $insertData['TT_MATERIAL_ID'] = null;
        }

        // set TT_MODIFIED_BY to the username from controller ($this->uid)
        $modifiedBy = '';
        if (isset($this->uid) && $this->uid !== '') {
            $modifiedBy = (string)$this->uid;
        }
        log_message('debug', '[add_data] uid="' . var_export($this->uid, true) . '", modifiedBy="' . $modifiedBy . '"');
        if ($modifiedBy !== '') {
            $insertData['TT_MODIFIED_BY'] = $modifiedBy;
        }

        $ok = $this->tms_db->insert($this->table, $insertData);

        // try to obtain the inserted id and set EFFECTIVE_DATE if column exists
        $new_id = 0;
        if ($ok) {
            $new_id = (int)$this->tms_db->insert_id();
            if ($new_id <= 0) {
                // fallback: try to get IDENT_CURRENT (best-effort)
                $row = $this->tms_db->query("SELECT IDENT_CURRENT('TMS_TC_TOOL_DRAWING_TOOLING') AS last_id")->row_array();
                if ($row && isset($row['last_id'])) $new_id = (int)$row['last_id'];
            }
            if ($this->has_column('TT_EFFECTIVE_DATE') && $new_id > 0) {
                $this->tms_db->query("UPDATE {$this->table} SET TT_EFFECTIVE_DATE = GETDATE() WHERE TT_ID = ?", array($new_id));
            }
        }

        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = 'Tool Drawing Tooling berhasil ditambahkan.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal menambahkan tool drawing tooling. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    public function edit_data($id, $tool_id, $min_qty, $replenish_qty, $maker_id, $price, $description, $material_id, $tool_life)
    {
        $id = (int)$id;
        $tool_id = (int)$tool_id;
        $min_qty = (int)$min_qty;
        $replenish_qty = (int)$replenish_qty;
        $maker_id = (int)$maker_id;
        $price = (float)$price;
        $description = trim((string)$description);
        $material_id = (int)$material_id;
        $tool_life = (int)$tool_life;

        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        if ($tool_id <= 0) {
            $this->messages = 'Tool ID harus lebih dari 0.';
            return false;
        }

        // set TT_MODIFIED_BY to the username from controller ($this->uid)
        $modifiedBy = '';
        if (isset($this->uid) && $this->uid !== '') {
            $modifiedBy = (string)$this->uid;
        }
        log_message('debug', '[edit_data] id=' . $id . ', uid="' . var_export($this->uid, true) . '", modifiedBy="' . $modifiedBy . '"');

        $updateData = array(
            'TT_TOOL_ID'        => $tool_id,
            'TT_MIN_QTY'        => $min_qty,
            'TT_REPLENISH_QTY'  => $replenish_qty,
            'TT_PRICE'          => $price,
            'TT_DESCRIPTION'    => $description,
            'TT_TOOL_LIFE'      => $tool_life
        );

        // Only set TT_MODIFIED_BY if we have a valid value
        if ($modifiedBy !== '') {
            $updateData['TT_MODIFIED_BY'] = $modifiedBy;
        }

        // handle MAKER_ID properly
        if ($maker_id > 0) {
            $updateData['TT_MAKER_ID'] = $maker_id;
        } else {
            $updateData['TT_MAKER_ID'] = null;
        }

        // handle MATERIAL_ID properly
        if ($material_id > 0) {
            $updateData['TT_MATERIAL_ID'] = $material_id;
        } else {
            $updateData['TT_MATERIAL_ID'] = null;
        }

        $ok = $this->tms_db->where('TT_ID', $id)->update($this->table, $updateData);

        if ($ok) {
            // update TT_MODIFIED_DATE if column exists
            if ($this->has_column('TT_MODIFIED_DATE')) {
                $this->tms_db->query("UPDATE {$this->table} SET TT_MODIFIED_DATE = GETDATE() WHERE TT_ID = ?", array($id));
            }
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

        $ok = $this->tms_db->delete($this->table, array('TT_ID' => $id));

        if ($ok) {
            $this->messages = 'Tool Drawing Tooling berhasil dihapus.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal menghapus tool drawing tooling. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    /**
     * Get revision history for a specific record
     * Return current record as pseudo-history for now
     */
    public function get_history($id)
    {
        $id = (int)$id;
        if ($id <= 0) {
            log_message('debug', '[M_tool_draw_tooling::get_history] invalid id=' . var_export($id, true));
            return array();
        }

        $row = $this->get_by_id($id);
        if (!$row) {
            log_message('debug', '[M_tool_draw_tooling::get_history] no record found for id=' . $id);
            return array();
        }

        // Return array with at least one history entry (current record)
        $history = array(
            array(
                'HISTORY_ID' => 1,
                'TT_ID' => (int)$row['TT_ID'],
                'TT_TOOL_ID' => isset($row['TT_TOOL_ID']) ? (int)$row['TT_TOOL_ID'] : 0,
                'TT_MIN_QTY' => isset($row['TT_MIN_QTY']) ? (int)$row['TT_MIN_QTY'] : 0,
                'TT_REPLENISH_QTY' => isset($row['TT_REPLENISH_QTY']) ? (int)$row['TT_REPLENISH_QTY'] : 0,
                'TT_MAKER_ID' => isset($row['TT_MAKER_ID']) ? (int)$row['TT_MAKER_ID'] : 0,
                'TT_PRICE' => isset($row['TT_PRICE']) ? (float)$row['TT_PRICE'] : 0,
                'TT_DESCRIPTION' => isset($row['TT_DESCRIPTION']) ? $row['TT_DESCRIPTION'] : '',
                'TT_EFFECTIVE_DATE' => isset($row['TT_EFFECTIVE_DATE']) ? $row['TT_EFFECTIVE_DATE'] : '',
                'TT_MATERIAL_ID' => isset($row['TT_MATERIAL_ID']) ? (int)$row['TT_MATERIAL_ID'] : 0,
                'TT_TOOL_LIFE' => isset($row['TT_TOOL_LIFE']) ? (int)$row['TT_TOOL_LIFE'] : 0,
                'TT_MODIFIED_DATE' => isset($row['TT_MODIFIED_DATE']) ? $row['TT_MODIFIED_DATE'] : '',
                'TT_MODIFIED_BY' => isset($row['TT_MODIFIED_BY']) ? $row['TT_MODIFIED_BY'] : ''
            )
        );
        log_message('debug', '[M_tool_draw_tooling::get_history] returning history count=' . count($history) . ' for id=' . $id);
        return $history;
    }
} // end class M_tool_draw_tooling
} // end if !class_exists
