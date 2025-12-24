<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_maker $maker
 */
class Maker extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('M_maker', 'maker');
        $this->load->library(array('form_validation', 'session'));
        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $data['list_data'] = $this->maker->get_all();

        $this->view('index_maker', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT Maker (AJAX)
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');
        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('MAKER_ID', TRUE);

        // validation rules
        $this->form_validation->set_rules('MAKER_NAME', 'Maker Name', 'required|trim');
        $this->form_validation->set_rules('MAKER_CODE', 'Maker Code', 'trim');
        $this->form_validation->set_rules('MAKER_DESC', 'Description', 'trim');
        $this->form_validation->set_rules('MAKER_ADDR', 'Address', 'trim');
        $this->form_validation->set_rules('MAKER_CITY', 'City', 'trim');
        $this->form_validation->set_rules('MAKER_COUNTRY', 'Country', 'trim');
        $this->form_validation->set_rules('MAKER_STATE', 'State', 'trim');
        $this->form_validation->set_rules('MAKER_ZIPCODE', 'Zipcode', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $data = array(
            'MAKER_NAME'    => $this->input->post('MAKER_NAME', TRUE),
            'MAKER_CODE'    => $this->input->post('MAKER_CODE', TRUE),
            'MAKER_DESC'    => $this->input->post('MAKER_DESC', TRUE),
            'MAKER_ADDR' => $this->input->post('MAKER_ADDR', TRUE),
            'MAKER_CITY'    => $this->input->post('MAKER_CITY', TRUE),
            'MAKER_COUNTRY' => $this->input->post('MAKER_COUNTRY', TRUE),
            'MAKER_STATE'   => $this->input->post('MAKER_STATE', TRUE),
            'MAKER_ZIPCODE' => $this->input->post('MAKER_ZIPCODE', TRUE),
        );

        if ($action === 'ADD') {
            // duplicate checks: name or code
            if ($this->maker->exists_by_name($data['MAKER_NAME'])) {
                echo json_encode(array('success' => false, 'message' => 'Nama maker sudah digunakan.'));
                return;
            }
            if (!empty($data['MAKER_CODE']) && $this->maker->exists_by_code($data['MAKER_CODE'])) {
                echo json_encode(array('success' => false, 'message' => 'Maker code sudah digunakan.'));
                return;
            }

            $ok = $this->maker->add_data($data);
            if ($ok) {
                $row = $this->maker->get_by_name($data['MAKER_NAME']);
                $new_id = $row ? (int)$row['MAKER_ID'] : null;
                echo json_encode(array('success' => true, 'message' => $this->maker->messages ?: 'Maker berhasil ditambahkan.', 'new_id' => $new_id));
                return;
            } else {
                echo json_encode(array('success' => false, 'message' => $this->maker->messages ?: 'Gagal menambahkan maker.'));
                return;
            }
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->maker->get_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
                return;
            }

            // duplicate name/code excluding current id
            $dup = $this->maker->get_by_name($data['MAKER_NAME']);
            if ($dup && (int)$dup['MAKER_ID'] !== $id) {
                echo json_encode(array('success' => false, 'message' => 'Nama maker sudah digunakan oleh data lain.'));
                return;
            }
            if (!empty($data['MAKER_CODE'])) {
                $dc = $this->maker->get_by_code($data['MAKER_CODE']);
                if ($dc && (int)$dc['MAKER_ID'] !== $id) {
                    echo json_encode(array('success' => false, 'message' => 'Maker code sudah digunakan oleh data lain.'));
                    return;
                }
            }

            $ok = $this->maker->edit_data($id, $data);
            if ($ok) {
                echo json_encode(array('success' => true, 'message' => $this->maker->messages ?: 'Maker berhasil diperbarui.'));
                return;
            } else {
                echo json_encode(array('success' => false, 'message' => $this->maker->messages ?: 'Gagal memperbarui maker.'));
                return;
            }
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    /**
     * delete_data: soft delete
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('MAKER_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'MAKER_ID tidak ditemukan.'));
            return;
        }

        $ok = $this->maker->delete_data($id);
        if ($ok) {
            echo json_encode(array('success' => true, 'message' => $this->maker->messages ?: 'Maker berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->maker->messages ?: 'Gagal menghapus maker.'));
        }
    }

    /**
     * get_maker_detail: ambil data by id (AJAX)
     */
    public function get_maker_detail()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('MAKER_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'MAKER_ID tidak ditemukan.'));
            return;
        }

        $row = $this->maker->get_by_id($id);
        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }

    public function detail($id = null)
    {
        if ($id === null) show_404();

        $id = (int)$id;
        if ($id <= 0) show_404();

        // ambil data maker
        $row = $this->maker->get_by_id($id);
        if (!$row) show_404();

        // siapkan data untuk view — gunakan fallback '' agar aman
        $data = array(
            'maker' => array(
                'MAKER_ID'      => isset($row['MAKER_ID']) ? $row['MAKER_ID'] : '',
                'MAKER_NAME'    => isset($row['MAKER_NAME']) ? $row['MAKER_NAME'] : '',
                'MAKER_CODE'    => isset($row['MAKER_CODE']) ? $row['MAKER_CODE'] : '',
                'MAKER_DESC'    => isset($row['MAKER_DESC']) ? $row['MAKER_DESC'] : '',
                'MAKER_ADDR' => isset($row['MAKER_ADDR']) ? $row['MAKER_ADDR'] : '',
                'MAKER_CITY'    => isset($row['MAKER_CITY']) ? $row['MAKER_CITY'] : '',
                'MAKER_STATE'   => isset($row['MAKER_STATE']) ? $row['MAKER_STATE'] : '',
                'MAKER_COUNTRY' => isset($row['MAKER_COUNTRY']) ? $row['MAKER_COUNTRY'] : '',
                'MAKER_ZIPCODE' => isset($row['MAKER_ZIPCODE']) ? $row['MAKER_ZIPCODE'] : '',
            )
        );

        $this->view('detail_maker', $data, FALSE);
    }
}
