<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_line $line
 * @property CI_Session $session
 * @property CI_Form_validation $form_validation
 * @property CI_Output $output
 * @property CI_Input $input
 */
class Line extends MY_Controller
{
    private $uid;

    public function __construct()
    {
        parent::__construct();
        $this->config->set_item('Blade_enable', FALSE);
        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));

        $this->load->model('M_line', 'line');
        $this->uid = $this->session->userdata('username') ?: 'SYSTEM';
    }

    /**
     * index: tampilkan list
     */
    public function index()
    {
        $data = array();
        $data['list_data'] = $this->line->get_data_master_lines();

        $this->view('index_line', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT (AJAX)
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('line_id', TRUE);

        // rules
        $this->form_validation->set_rules('line_name', 'Line Name', 'required|trim');
        $this->form_validation->set_rules('line_desc', 'Description', 'trim');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $name = $this->input->post('line_name', TRUE);

        // ADD
        if ($action === 'ADD') {

            if ($this->line->is_duplicate($name)) {
                echo json_encode(array('success' => false, 'message' => 'Nama line tersebut sudah ada.'));
                return;
            }

            $ok = $this->line->add_data($this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->line->messages ?: 'Gagal menambahkan data.'));
                return;
            }

            echo json_encode(array('success' => true, 'message' => $this->line->messages ?: 'Data berhasil ditambahkan.'));
            return;
        }

        // EDIT
        if ($action === 'EDIT' && $id > 0) {

            $current = $this->line->get_data_master_line_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
                return;
            }

            if ($this->line->is_duplicate($name, $id)) {
                echo json_encode(array('success' => false, 'message' => 'Nama line sudah digunakan oleh data lain.'));
                return;
            }

            $dataUpdate = [
                'LINE_NAME' => $name,
                'LINE_DESC' => trim((string)$this->input->post('line_desc')) ?: NULL
            ];

            $ok = $this->line->update_by_id($id, $dataUpdate, $this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->line->messages ?: 'Gagal memperbarui data.'));
                return;
            }

            echo json_encode(array('success' => true, 'message' => $this->line->messages ?: 'Data berhasil diperbarui.'));
            return;
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    /**
     * delete_data: soft delete (AJAX)
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('line_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Line ID tidak ditemukan.'));
            return;
        }

        $ok = $this->line->delete_data($id, $this->uid);
        echo json_encode(array(
            'success' => (bool)$ok,
            'message' => $this->line->messages ?: ($ok ? 'Data berhasil dihapus.' : 'Gagal menghapus data.')
        ));
    }
}
