<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_maker extends CI_Model
{
    private $table = 'MS_MAKER';
    public $tms_db;
    public $messages = '';

    public function __construct()
    {
        parent::__construct();
        $this->tms_db = $this->load->database('tms_NEW', TRUE);
    }

    // check column exists (defensive)
    protected function has_column($col)
    {
        $col = trim((string)$col);
        if ($col === '') return false;
        $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'dbo' AND TABLE_NAME = 'MS_MAKER' AND COLUMN_NAME = ?";
        $q = $this->tms_db->query($sql, array($col));
        return ($q && $q->num_rows() > 0);
    }

    public function get_all()
    {
        return $this->tms_db
            ->select('MAKER_ID, MAKER_NAME, MAKER_CODE, MAKER_DESC, MAKER_ADDR, MAKER_CITY, MAKER_COUNTRY, MAKER_STATE, MAKER_ZIPCODE, ')
            ->from($this->table)
            ->where('IS_DELETED', 0)
            ->order_by('MAKER_ID', 'ASC')
            ->get()
            ->result_array();
    }

    public function get_by_id($id)
    {
        $id = (int)$id;
        if ($id <= 0) return null;
        return $this->tms_db->where('MAKER_ID', $id)->limit(1)->get($this->table)->row_array();
    }

    public function get_by_name($name)
    {
        $name = trim((string)$name);
        if ($name === '') return null;
        $sql = "SELECT TOP 1 * FROM {$this->table} WHERE UPPER(MAKER_NAME) = ? AND IS_DELETED = 0";
        $q = $this->tms_db->query($sql, array(strtoupper($name)));
        return $q->row_array();
    }

    public function get_by_code($code)
    {
        $code = trim((string)$code);
        if ($code === '') return null;
        $sql = "SELECT TOP 1 * FROM {$this->table} WHERE UPPER(MAKER_CODE) = ? AND IS_DELETED = 0";
        $q = $this->tms_db->query($sql, array(strtoupper($code)));
        return $q->row_array();
    }

    public function exists_by_name($name)
    {
        return (bool)$this->get_by_name($name);
    }

    public function exists_by_code($code)
    {
        return (bool)$this->get_by_code($code);
    }

    public function get_new_sequence()
    {
        $row = $this->tms_db->select_max('MAKER_ID')->get($this->table)->row_array();
        return isset($row['MAKER_ID']) ? ((int)$row['MAKER_ID'] + 1) : 1;
    }

    /* ========== MUTATORS ========== */

    public function add_data($data)
    {
        $name = isset($data['MAKER_NAME']) ? trim($data['MAKER_NAME']) : '';
        $code = isset($data['MAKER_CODE']) ? trim($data['MAKER_CODE']) : null;

        if ($name === '') {
            $this->messages = 'Nama maker tidak boleh kosong.';
            return false;
        }

        $new_id = $this->get_new_sequence();

        $insert = array(
            'MAKER_NAME'    => $name,
            'MAKER_CODE'    => $code,
            'MAKER_DESC'    => isset($data['MAKER_DESC']) ? $data['MAKER_DESC'] : null,
            'MAKER_ADDR' => isset($data['MAKER_ADDR']) ? $data['MAKER_ADDR'] : null,
            'MAKER_CITY'    => isset($data['MAKER_CITY']) ? $data['MAKER_CITY'] : null,
            'MAKER_COUNTRY' => isset($data['MAKER_COUNTRY']) ? $data['MAKER_COUNTRY'] : null,
            'MAKER_STATE'   => isset($data['MAKER_STATE']) ? $data['MAKER_STATE'] : null,
            'MAKER_ZIPCODE' => isset($data['MAKER_ZIPCODE']) ? $data['MAKER_ZIPCODE'] : null,
            'IS_DELETED'    => 0
        );

        $this->tms_db->trans_start();
        $ok = $this->tms_db->insert($this->table, $insert);

        if ($this->has_column('CREATED_AT')) {
            $this->tms_db->query("UPDATE {$this->table} SET CREATED_AT = GETDATE() WHERE MAKER_ID = ?", array($new_id));
        }

        if ($this->has_column('CREATED_BY') && isset($this->session)) {
            $user = $this->session->userdata('username') ?: null;
            if ($user !== null) {
                $this->tms_db->query("UPDATE {$this->table} SET CREATED_BY = ? WHERE MAKER_ID = ?", array($user, $new_id));
            }
        }

        $this->tms_db->trans_complete();

        if ($this->tms_db->trans_status()) {
            $this->messages = 'Maker berhasil ditambahkan.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal menambahkan maker. ' . (isset($err['message']) ? $err['message'] : '');
        return false;
    }

    public function edit_data($id, $data)
    {
        $id = (int)$id;
        $current = $this->get_by_id($id);
        if (!$current) {
            $this->messages = 'Data tidak ditemukan.';
            return false;
        }

        $update = array(
            'MAKER_NAME'    => isset($data['MAKER_NAME']) ? $data['MAKER_NAME'] : $current['MAKER_NAME'],
            'MAKER_CODE'    => isset($data['MAKER_CODE']) ? $data['MAKER_CODE'] : $current['MAKER_CODE'],
            'MAKER_DESC'    => isset($data['MAKER_DESC']) ? $data['MAKER_DESC'] : $current['MAKER_DESC'],
            'MAKER_ADDR' => isset($data['MAKER_ADDR']) ? $data['MAKER_ADDR'] : $current['MAKER_ADDR'],
            'MAKER_CITY'    => isset($data['MAKER_CITY']) ? $data['MAKER_CITY'] : $current['MAKER_CITY'],
            'MAKER_COUNTRY' => isset($data['MAKER_COUNTRY']) ? $data['MAKER_COUNTRY'] : $current['MAKER_COUNTRY'],
            'MAKER_STATE'   => isset($data['MAKER_STATE']) ? $data['MAKER_STATE'] : $current['MAKER_STATE'],
            'MAKER_ZIPCODE' => isset($data['MAKER_ZIPCODE']) ? $data['MAKER_ZIPCODE'] : $current['MAKER_ZIPCODE'],
        );

        $ok = $this->tms_db->where('MAKER_ID', $id)->update($this->table, $update);
        if ($ok) {
            $this->messages = 'Maker berhasil diubah.';
            return true;
        }
        $err = $this->tms_db->error();
        $this->messages = 'Gagal mengubah maker. ' . (isset($err['message']) ? $err['message'] : '');
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
        if (!empty($row['IS_DELETED'])) {
            $this->messages = 'Maker sudah dihapus.';
            return false;
        }

        $deletedBy = 'SYSTEM';
        if (isset($this->session) && method_exists($this->session, 'userdata')) {
            $u = $this->session->userdata('username');
            if (!empty($u)) $deletedBy = $u;
        }

        $this->tms_db->trans_begin();

        $updateData = array('IS_DELETED' => 1);
        $set_deleted_at_raw = false;
        if ($this->has_column('DELETED_AT')) $set_deleted_at_raw = true;
        if ($this->has_column('DELETED_BY')) $updateData['DELETED_BY'] = $deletedBy;

        if ($set_deleted_at_raw) {
            $this->tms_db->where('MAKER_ID', $id);
            $ok = $this->tms_db->update($this->table, $updateData);
            if ($ok) {
                $ok2 = $this->tms_db->query("UPDATE {$this->table} SET DELETED_AT = GETDATE() WHERE MAKER_ID = ?", array($id));
                if (!$ok2) $ok = false;
            }
        } else {
            $this->tms_db->where('MAKER_ID', $id);
            $ok = $this->tms_db->update($this->table, $updateData);
        }

        if (!$ok || $this->tms_db->trans_status() === FALSE) {
            $err = $this->tms_db->error();
            $this->tms_db->trans_rollback();
            $this->messages = 'Gagal menghapus maker. ' . (isset($err['message']) ? $err['message'] : '');
            return false;
        }
        $this->tms_db->trans_commit();
        $this->messages = 'Maker berhasil dihapus (soft delete).';
        return true;
    }
}
