<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!class_exists('M_tool_bom_engin')) {
    class M_tool_bom_engin extends CI_Model
    {
        private $table = 'TMS_DB.dbo.TMS_TC_TOOL_BOM_ENGIN';
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
                    WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'TMS_TC_TOOL_BOM_ENGIN' AND COLUMN_NAME = ?";
            $q = $this->tms_db->query($sql, array($col));
            return ($q && $q->num_rows() > 0);
        }

        public function get_all()
        {
            // Build select columns - include new columns if they exist
            $selectCols = 'ID, TOOL_BOM, DESCRIPTION, REVISION, STATUS, MODIFIED_BY, MODIFIED_DATE';
            
            // Add FK columns if they exist
            if ($this->has_column('PRODUCT_ID')) {
                $selectCols .= ', PRODUCT_ID';
            }
            if ($this->has_column('PROCESS_ID')) {
                $selectCols .= ', PROCESS_ID';
            }
            if ($this->has_column('MACHINE_GROUP_ID')) {
                $selectCols .= ', MACHINE_GROUP_ID';
            }
            // Keep old text columns for backward compatibility
            if ($this->has_column('PRODUCT')) {
                $selectCols .= ', PRODUCT';
            }
            if ($this->has_column('MACHINE_GROUP')) {
                $selectCols .= ', MACHINE_GROUP';
            }
            // Add new columns
            if ($this->has_column('EFFECTIVE_DATE')) {
                $selectCols .= ', EFFECTIVE_DATE';
            }
            if ($this->has_column('CHANGE_SUMMARY')) {
                $selectCols .= ', CHANGE_SUMMARY';
            }
            if ($this->has_column('DRAWING')) {
                $selectCols .= ', DRAWING';
            }

            $result = $this->tms_db
                ->select($selectCols)
                ->from($this->table)
                ->order_by('ID', 'DESC')
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

            $result = $this->tms_db->where('ID', $id)->limit(1)->get($this->table);
            if ($result && $result->num_rows() > 0) {
                return $result->row_array();
            }
            return null;
        }

        public function get_new_sequence()
        {
            $row = $this->tms_db->select_max('ID')->get($this->table)->row_array();
            return isset($row['ID']) ? ((int)$row['ID'] + 1) : 1;
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
         * Get all machine groups from TMS_M_MACHINES (IS_GROUP = 1)
         */
        public function get_machine_groups()
        {
            $table = 'TMS_DB.dbo.TMS_M_MACHINES';
            $result = $this->tms_db
                ->select('MACHINE_ID, MACHINE_NAME')
                ->from($table)
                ->where('IS_DELETED', 0)
                ->where('IS_GROUP', 1)
                ->order_by('MACHINE_NAME', 'ASC')
                ->get();

            if ($result && $result->num_rows() > 0) {
                return $result->result_array();
            }
            return array();
        }

        /* ========== MUTATORS ========== */

        public function add_data($tool_bom, $description, $product_id, $process_id, $machine_group_id, $revision, $status, $effective_date, $change_summary, $drawing_filename)
        {
            $tool_bom = trim((string)$tool_bom);
            $description = trim((string)$description);
            $product_id = (int)$product_id;
            $process_id = (int)$process_id;
            $machine_group_id = (int)$machine_group_id;
            $revision = (int)$revision;
            $status = (int)$status;
            $effective_date = trim((string)$effective_date);
            $change_summary = trim((string)$change_summary);
            $drawing_filename = trim((string)$drawing_filename);

            if ($tool_bom === '') {
                $this->messages = 'Tool BOM tidak boleh kosong.';
                return false;
            }

            // set MODIFIED_BY to the username from controller ($this->uid)
            $modifiedBy = '';
            if (isset($this->uid) && $this->uid !== '') {
                $modifiedBy = (string)$this->uid;
            }
            log_message('debug', '[add_data] uid="' . var_export($this->uid, true) . '", modifiedBy="' . $modifiedBy . '"');

            $insertData = array(
                'TOOL_BOM'      => $tool_bom,
                'DESCRIPTION'   => $description !== '' ? $description : null,
                'REVISION'      => $revision,
                'STATUS'        => $status
            );

            // Add FK columns if they exist
            if ($this->has_column('PRODUCT_ID')) {
                $insertData['PRODUCT_ID'] = $product_id > 0 ? $product_id : null;
            }
            if ($this->has_column('PROCESS_ID')) {
                $insertData['PROCESS_ID'] = $process_id > 0 ? $process_id : null;
            }
            if ($this->has_column('MACHINE_GROUP_ID')) {
                $insertData['MACHINE_GROUP_ID'] = $machine_group_id > 0 ? $machine_group_id : null;
            }
            // Keep old text columns for backward compatibility
            if ($this->has_column('PRODUCT') && $product_id > 0) {
                // Get product name
                $product = $this->tms_db->select('PRODUCT_NAME')->from('TMS_DB.dbo.TMS_M_PRODUCT')->where('PRODUCT_ID', $product_id)->limit(1)->get()->row_array();
                $insertData['PRODUCT'] = $product ? $product['PRODUCT_NAME'] : null;
            }
            if ($this->has_column('MACHINE_GROUP') && $machine_group_id > 0) {
                // Get machine group name
                $mg = $this->tms_db->select('MACHINE_NAME')->from('TMS_DB.dbo.TMS_M_MACHINES')->where('MACHINE_ID', $machine_group_id)->limit(1)->get()->row_array();
                $insertData['MACHINE_GROUP'] = $mg ? $mg['MACHINE_NAME'] : null;
            }
            // Add new columns
            if ($this->has_column('EFFECTIVE_DATE')) {
                $insertData['EFFECTIVE_DATE'] = $effective_date !== '' ? $effective_date : null;
            }
            if ($this->has_column('CHANGE_SUMMARY')) {
                $insertData['CHANGE_SUMMARY'] = $change_summary !== '' ? $change_summary : null;
            }
            if ($this->has_column('DRAWING')) {
                $insertData['DRAWING'] = $drawing_filename !== '' ? $drawing_filename : null;
            }

            if ($modifiedBy !== '') {
                $insertData['MODIFIED_BY'] = $modifiedBy;
            }

            $ok = $this->tms_db->insert($this->table, $insertData);

            if ($ok) {
                // Set EFFECTIVE_DATE if column exists and not set
                if ($this->has_column('EFFECTIVE_DATE') && ($effective_date === '' || $effective_date === null)) {
                    $new_id = (int)$this->tms_db->insert_id();
                    if ($new_id > 0) {
                        $this->tms_db->query("UPDATE {$this->table} SET EFFECTIVE_DATE = GETDATE() WHERE ID = ?", array($new_id));
                    }
                }
                $this->messages = 'Tool BOM Engineering berhasil ditambahkan.';
                return true;
            }
            $err = $this->tms_db->error();
            $this->messages = 'Gagal menambahkan tool BOM engineering. ' . (isset($err['message']) ? $err['message'] : '');
            return false;
        }

        public function edit_data($id, $tool_bom, $description, $product_id, $process_id, $machine_group_id, $revision, $status, $effective_date, $change_summary, $drawing_filename)
        {
            $id = (int)$id;
            $tool_bom = trim((string)$tool_bom);
            $description = trim((string)$description);
            $product_id = (int)$product_id;
            $process_id = (int)$process_id;
            $machine_group_id = (int)$machine_group_id;
            $revision = (int)$revision;
            $status = (int)$status;
            $effective_date = trim((string)$effective_date);
            $change_summary = trim((string)$change_summary);
            $drawing_filename = trim((string)$drawing_filename);

            $current = $this->get_by_id($id);
            if (!$current) {
                $this->messages = 'Data tidak ditemukan.';
                return false;
            }

            if ($tool_bom === '') {
                $this->messages = 'Tool BOM tidak boleh kosong.';
                return false;
            }

            // set MODIFIED_BY to the username from controller ($this->uid)
            $modifiedBy = '';
            if (isset($this->uid) && $this->uid !== '') {
                $modifiedBy = (string)$this->uid;
            }
            log_message('debug', '[edit_data] id=' . $id . ', uid="' . var_export($this->uid, true) . '", modifiedBy="' . $modifiedBy . '"');

            $updateData = array(
                'TOOL_BOM'      => $tool_bom,
                'DESCRIPTION'   => $description !== '' ? $description : null,
                'REVISION'      => $revision,
                'STATUS'        => $status
            );

            // Add FK columns if they exist
            if ($this->has_column('PRODUCT_ID')) {
                $updateData['PRODUCT_ID'] = $product_id > 0 ? $product_id : null;
            }
            if ($this->has_column('PROCESS_ID')) {
                $updateData['PROCESS_ID'] = $process_id > 0 ? $process_id : null;
            }
            if ($this->has_column('MACHINE_GROUP_ID')) {
                $updateData['MACHINE_GROUP_ID'] = $machine_group_id > 0 ? $machine_group_id : null;
            }
            // Keep old text columns for backward compatibility
            if ($this->has_column('PRODUCT') && $product_id > 0) {
                // Get product name
                $product = $this->tms_db->select('PRODUCT_NAME')->from('TMS_DB.dbo.TMS_M_PRODUCT')->where('PRODUCT_ID', $product_id)->limit(1)->get()->row_array();
                $updateData['PRODUCT'] = $product ? $product['PRODUCT_NAME'] : null;
            }
            if ($this->has_column('MACHINE_GROUP') && $machine_group_id > 0) {
                // Get machine group name
                $mg = $this->tms_db->select('MACHINE_NAME')->from('TMS_DB.dbo.TMS_M_MACHINES')->where('MACHINE_ID', $machine_group_id)->limit(1)->get()->row_array();
                $updateData['MACHINE_GROUP'] = $mg ? $mg['MACHINE_NAME'] : null;
            }
            // Add new columns
            if ($this->has_column('EFFECTIVE_DATE')) {
                $updateData['EFFECTIVE_DATE'] = $effective_date !== '' ? $effective_date : null;
            }
            if ($this->has_column('CHANGE_SUMMARY')) {
                $updateData['CHANGE_SUMMARY'] = $change_summary !== '' ? $change_summary : null;
            }
            // Only update drawing if new file is provided
            if ($this->has_column('DRAWING') && $drawing_filename !== '') {
                $updateData['DRAWING'] = $drawing_filename;
            }

            // Only set MODIFIED_BY if we have a valid value
            if ($modifiedBy !== '') {
                $updateData['MODIFIED_BY'] = $modifiedBy;
            }

            $ok = $this->tms_db->where('ID', $id)->update($this->table, $updateData);

            if ($ok) {
                // update MODIFIED_DATE if column exists
                if ($this->has_column('MODIFIED_DATE')) {
                    $this->tms_db->query("UPDATE {$this->table} SET MODIFIED_DATE = GETDATE() WHERE ID = ?", array($id));
                }
                $this->messages = 'Tool BOM Engineering berhasil diubah.';
                return true;
            }
            $err = $this->tms_db->error();
            $this->messages = 'Gagal mengubah tool BOM engineering. ' . (isset($err['message']) ? $err['message'] : '');
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

            $ok = $this->tms_db->delete($this->table, array('ID' => $id));

            if ($ok) {
                $this->messages = 'Tool BOM Engineering berhasil dihapus.';
                return true;
            }
            $err = $this->tms_db->error();
            $this->messages = 'Gagal menghapus tool BOM engineering. ' . (isset($err['message']) ? $err['message'] : '');
            return false;
        }
    }
}

