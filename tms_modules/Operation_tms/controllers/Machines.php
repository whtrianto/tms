<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_machines $machines
 */
class Machines extends MY_Controller
{
    private $uid;

    public function __construct()
    {
        parent::__construct();
        $this->config->set_item('Blade_enable', FALSE);
        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));
        $this->load->model('M_machines', 'machines');
        
        $this->uid = $this->session->userdata('username') ?: 'SYSTEM';
    }

    /**
     * index: tampilkan list Machines (IS_GROUP = 0)
     */
    public function index()
    {
        $data = array();
        $data['list_data'] = $this->machines->get_data_master_machines();
        $data['dropdown_groups']    = $this->machines->get_all_machine_groups();
        $data['dropdown_operations'] = $this->machines->get_all_operations();
        
        $this->view('index_machines', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT Machine (AJAX)
     */
    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('machine_id', TRUE);
        $is_group = (int)$this->input->post('is_group') === 1;

        // rules
        $this->form_validation->set_rules('machine_name', 'Machine Name', 'required|trim');
        $this->form_validation->set_rules('operation_id', 'Operation', 'required|numeric');
        $this->form_validation->set_rules('is_group', 'Is Group', 'trim');
        
        // PARENT_ID hanya wajib jika IS_GROUP = 0 (ini adalah mesin)
        if (!$is_group) {
            $this->form_validation->set_rules('parent_id', 'Machine Group', 'required|numeric');
        }
        // $this->form_validation->set_rules('charge_rate', 'Charge Rate', 'trim|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimitERS('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $machine_name = $this->input->post('machine_name', TRUE);

        if ($action === 'ADD') {
            if ($this->machines->is_name_duplicate($machine_name)) {
                echo json_encode(array('success' => false, 'message' => 'Machine/Group dengan nama tersebut sudah ada.'));
                return;
            }

            $ok = $this->machines->add_data($this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->machines->messages ?: 'Gagal menambahkan Machine.'));
                return;
            }
            echo json_encode(array('success' => true, 'message' => $this->machines->messages ?: 'Machine berhasil ditambahkan.'));
            return;
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->machines->get_data_master_machine_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data Machine tidak ditemukan.'));
                return;
            }
            if ($this->machines->is_name_duplicate($machine_name, $id)) {
                echo json_encode(array('success' => false, 'message' => 'Nama Machine/Group sudah digunakan oleh data lain.'));
                return;
            }
            
            // $charge_rate = $this->input->post('charge_rate');

            // data yang akan di-update (HANYA untuk tabel TMS_M_MACHINES)
            $dataUpdate = [
                'MACHINE_NAME'      => $machine_name,
                'OPERATION_ID'      => (int)$this->input->post('operation_id'),
                // 'IS_GROUP' akan di-handle oleh model
                // 'CHARGE_RATE'       => $charge_rate ?: NULL,
            ];

            $ok = $this->machines->update_by_id($id, $dataUpdate, $this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->machines->messages ?: 'Gagal memperbarui Machine.'));
                return;
            }
            echo json_encode(array('success' => true, 'message' => $this->machines->messages ?: 'Machine berhasil diperbarui.'));
            return;
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    /**
     * delete_data: soft delete Machine (AJAX)
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('machine_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Machine ID tidak ditemukan.'));
            return;
        }

        if ($this->machines->is_parent($id)) {
             echo json_encode(array('success' => false, 'message' => 'Gagal hapus: Machine ini adalah Grup yang masih memiliki member.'));
            return;
        }

        $ok = $this->machines->delete_data($id, $this->uid);
        echo json_encode(array('success' => (bool)$ok, 'message' => $this->machines->messages ?: ($ok ? 'Machine berhasil dihapus.' : 'Gagal menghapus Machine.')));
    }
    
    public function get_machine_detail()
    {
        $this->output->set_content_type('application/json');
        $id = (int)$this->input->post('machine_id');
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }
        
        $data = $this->machines->get_data_master_machine_by_id($id);
        if ($data) {
            // Ambil PARENT_ID dari tabel mapping
            $data['PARENT_ID'] = $this->machines->get_parent_id_for_machine($id);
            echo json_encode(array('success' => true, 'data' => $data));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }
}