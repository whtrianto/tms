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
            $selectCols = 'ID, TOOL_BOM, DESCRIPTION, PRODUCT, MACHINE_GROUP, REVISION, STATUS, MODIFIED_BY, MODIFIED_DATE';

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

        /* ========== MUTATORS ========== */

        public function add_data($tool_bom, $description, $product, $machine_group, $revision, $status)
        {
            $tool_bom = trim((string)$tool_bom);
            $description = trim((string)$description);
            $product = trim((string)$product);
            $machine_group = trim((string)$machine_group);
            $revision = (int)$revision;
            $status = (int)$status;

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
                'PRODUCT'       => $product !== '' ? $product : null,
                'MACHINE_GROUP' => $machine_group !== '' ? $machine_group : null,
                'REVISION'      => $revision,
                'STATUS'        => $status
            );

            if ($modifiedBy !== '') {
                $insertData['MODIFIED_BY'] = $modifiedBy;
            }

            $ok = $this->tms_db->insert($this->table, $insertData);

            if ($ok) {
                $this->messages = 'Tool BOM Engineering berhasil ditambahkan.';
                return true;
            }
            $err = $this->tms_db->error();
            $this->messages = 'Gagal menambahkan tool BOM engineering. ' . (isset($err['message']) ? $err['message'] : '');
            return false;
        }

        public function edit_data($id, $tool_bom, $description, $product, $machine_group, $revision, $status)
        {
            $id = (int)$id;
            $tool_bom = trim((string)$tool_bom);
            $description = trim((string)$description);
            $product = trim((string)$product);
            $machine_group = trim((string)$machine_group);
            $revision = (int)$revision;
            $status = (int)$status;

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
                'PRODUCT'       => $product !== '' ? $product : null,
                'MACHINE_GROUP' => $machine_group !== '' ? $machine_group : null,
                'REVISION'      => $revision,
                'STATUS'        => $status
            );

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

