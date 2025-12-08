<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_machine_group $machine_group
 */
class Machine_group extends MY_Controller
{
    private $uid;

    public function __construct()
    {
        parent::__construct();
        $this->config->set_item('Blade_enable', FALSE);
        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));
        
        $this->load->model('M_machine_group', 'machine_group');
        
        $this->uid = $this->session->userdata('username') ?: 'SYSTEM';
    }

    /**
     * index: tampilkan list mapping
     */
    public function index()
    {
        $data = array();
        $data['list_data'] = $this->machine_group->get_data_master_groups();
        $data['dropdown_operations'] = $this->machine_group->get_all_operations();
        
        $this->view('index_machine_group', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT mapping (AJAX)
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('machine_id', TRUE);

        // rules
        $this->form_validation->set_rules('machine_name', 'Group Name', 'required|trim');
        $this->form_validation->set_rules('operation_id', 'Operation', 'trim|numeric'); // Opsional

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        if ($action === 'ADD') {
            $ok = $this->machine_group->add_data($this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->machine_group->messages ?: 'Gagal menambahkan group.'));
                return;
            }
            echo json_encode(array('success' => true, 'message' => $this->machine_group->messages ?: 'Group berhasil ditambahkan.'));
            return;
        }
        
        if ($action === 'EDIT' && $id > 0) {
            $ok = $this->machine_group->update_data($id, $this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->machine_group->messages ?: 'Gagal memperbarui group.'));
                return;
            }
            echo json_encode(array('success' => true, 'message' => $this->machine_group->messages ?: 'Group berhasil diperbarui.'));
            return;
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    /**
     * delete_data: soft delete mapping (AJAX)
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('machine_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Machine Group ID tidak ditemukan.'));
            return;
        }
        
        // Cek dulu apakah grup ini punya anak
        if ($this->machine_group->is_parent($id)) {
             echo json_encode(array('success' => false, 'message' => 'Gagal hapus: Grup ini masih memiliki member (mesin).'));
            return;
        }

        $ok = $this->machine_group->delete_data($id, $this->uid);
        echo json_encode(array('success' => (bool)$ok, 'message' => $this->machine_group->messages ?: ($ok ? 'Grup berhasil dihapus.' : 'Gagal menghapus grup.')));
    }
    
    /**
     * [AJAX POST] Mengambil data detail satu grup
     */
    public function get_group_detail()
    {
        $this->output->set_content_type('application/json');
        $id = (int)$this->input->post('machine_id');
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }
        
        $data = $this->machine_group->get_data_master_group_by_id($id);
        if ($data) {
            echo json_encode(array('success' => true, 'data' => $data));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }
}