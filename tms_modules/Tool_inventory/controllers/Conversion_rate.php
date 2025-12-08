<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_conversion_rate $conversion_rate
 */
class Conversion_rate extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_conversion_rate', 'conversion_rate');
        $this->load->library(array('form_validation', 'session'));
        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $search = $this->input->get('search', true);
        $data = array();
        $data['list_data'] = $this->conversion_rate->get_all($search);
        $this->view('index_conversion_rate', $data, FALSE);
    }

    public function submit_data()
    {
        $this->output->set_content_type('application/json');
        $action = strtoupper($this->input->post('action', TRUE));
        $id = (int)$this->input->post('CON_ID', TRUE);

        // validation
        $this->form_validation->set_rules('CON_CURRENCY', 'Currency', 'required|trim');
        $this->form_validation->set_rules('CON_RATE', 'Rate', 'required|trim|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $currency = $this->input->post('CON_CURRENCY', TRUE);
        $rate = $this->input->post('CON_RATE', TRUE);

        $data = array('CON_CURRENCY' => $currency, 'CON_RATE' => $rate);

        if ($action === 'ADD') {
            // cek duplikat pada baris aktif
            if ($this->conversion_rate->exists_by_currency($currency, true)) {
                echo json_encode(['success' => false, 'message' => 'Currency sudah digunakan pada data aktif.']);
                return;
            }

            $ok = $this->conversion_rate->add_data($data);
            if ($ok) {
                $row = $this->conversion_rate->get_by_currency($currency);
                $new_id = $row ? (int)$row['CON_ID'] : null;
                echo json_encode(array('success' => true, 'message' => $this->conversion_rate->messages ?: 'Conversion rate berhasil ditambahkan.', 'new_id' => $new_id));
                return;
            } else {
                echo json_encode(array('success' => false, 'message' => $this->conversion_rate->messages ?: 'Gagal menambahkan conversion rate.'));
                return;
            }
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->conversion_rate->get_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
                return;
            }

            // cek duplicate excluding current id (pada baris aktif)
            if ($this->conversion_rate->is_duplicate_currency($currency, $id)) {
                echo json_encode(array('success' => false, 'message' => 'Currency sudah digunakan oleh data lain.'));
                return;
            }

            $ok = $this->conversion_rate->edit_data($id, $data);
            if ($ok) {
                echo json_encode(array('success' => true, 'message' => $this->conversion_rate->messages ?: 'Conversion rate berhasil diperbarui.'));
                return;
            } else {
                echo json_encode(array('success' => false, 'message' => $this->conversion_rate->messages ?: 'Gagal memperbarui conversion rate.'));
                return;
            }
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    public function delete_data()
    {
        $this->output->set_content_type('application/json');
        $id = (int)$this->input->post('CON_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'CON_ID tidak ditemukan.'));
            return;
        }

        $ok = $this->conversion_rate->delete_data($id);
        if ($ok) {
            echo json_encode(array('success' => true, 'message' => $this->conversion_rate->messages ?: 'Conversion rate berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->conversion_rate->messages ?: 'Gagal menghapus conversion rate.'));
        }
    }

    public function get_detail()
    {
        $this->output->set_content_type('application/json');
        $id = (int)$this->input->post('CON_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'CON_ID tidak ditemukan.'));
            return;
        }
        $row = $this->conversion_rate->get_by_id($id);
        if ($row) {
            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }
}
