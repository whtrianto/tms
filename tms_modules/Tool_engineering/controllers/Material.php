<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Material extends MY_Controller
{
    private $uid;

    public function __construct()
    {
        parent::__construct();
        $this->config->set_item('Blade_enable', FALSE);
        $this->load->library(array('form_validation', 'session'));
        $this->load->helper(array('url', 'form'));

        $this->load->model('M_material', 'material');
        $this->uid = $this->session->userdata('username') ?: 'SYSTEM';
    }

    public function index()
    {
        $data = array();
        $data['list_data'] = $this->material->get_data_master_materials();

        $data['dropdown_uoms'] = $this->material->get_all_uoms();

        $this->view('index_material', $data, FALSE);
    }

    public function submit_data()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $id     = (int)$this->input->post('material_id', TRUE);
        $uom_id = $this->input->post('uom_id', TRUE);
        $uom_value = (!empty($uom_id) && $uom_id != 0) ? (int)$uom_id : NULL;

        // rules
        $this->form_validation->set_rules('material_name', 'Material Name', 'required|trim');
        $this->form_validation->set_rules('uom_id', 'UoM', 'trim|numeric');

        if ($this->form_validation->run() == FALSE) {
            $this->form_validation->set_error_delimiters('', '');
            echo json_encode(array('success' => false, 'message' => validation_errors() ?: 'Data tidak valid.'));
            return;
        }

        $material_name = $this->input->post('material_name', TRUE);

        if ($action === 'ADD') {
            if ($this->material->is_duplicate($material_name)) {
                echo json_encode(array('success' => false, 'message' => 'Material dengan nama tersebut sudah ada.'));
                return;
            }

            $ok = $this->material->add_data($this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->material->messages ?: 'Gagal menambahkan Material.'));
                return;
            }
            echo json_encode(array('success' => true, 'message' => $this->material->messages ?: 'Material berhasil ditambahkan.'));
            return;
        }

        if ($action === 'EDIT' && $id > 0) {
            $current = $this->material->get_data_master_material_by_id($id);
            if (!$current) {
                echo json_encode(array('success' => false, 'message' => 'Data Material tidak ditemukan.'));
                return;
            }

            if ($this->material->is_duplicate($material_name, $id)) {
                echo json_encode(array('success' => false, 'message' => 'Nama Material sudah digunakan oleh data lain.'));
                return;
            }

            // data yang akan di-update
            $dataUpdate = [
                'MAT_NAME' => $material_name,
                'MAT_UNIT'        => $uom_value
            ];

            $ok = $this->material->update_by_id($id, $dataUpdate, $this->uid);
            if (!$ok) {
                echo json_encode(array('success' => false, 'message' => $this->material->messages ?: 'Gagal memperbarui Material.'));
                return;
            }
            echo json_encode(array('success' => true, 'message' => $this->material->messages ?: 'Material berhasil diperbarui.'));
            return;
        }

        echo json_encode(array('success' => false, 'message' => 'Parameter action/ID tidak valid.'));
    }

    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('material_id', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Material ID tidak ditemukan.'));
            return;
        }

        $ok = $this->material->delete_data($id, $this->uid);
        echo json_encode(array('success' => (bool)$ok, 'message' => $this->material->messages ?: ($ok ? 'Material berhasil dihapus.' : 'Gagal menghapus Material.')));
    }
}
