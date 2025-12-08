<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Listing Tool BOM untuk kebutuhan Tooling (hanya edit & history)
 * Data diambil dari tabel yang sama dengan engineering (M_tool_bom_engin)
 */
class Tool_bom_tooling extends MY_Controller
{
    /** @var M_tool_bom_engin */
    public $tool_bom_engin;
    public $uid = '';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('form_validation', 'session'));

        $username_from_session = $this->session->userdata('username');
        $this->uid = (string)($username_from_session ?: 'SYSTEM');

        $this->load->model('M_tool_bom_engin', 'tool_bom_engin');
        $this->tool_bom_engin->uid = $this->uid;

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $data['list_data'] = $this->tool_bom_engin->get_all();
        $data['products'] = $this->tool_bom_engin->get_products();
        $data['operations'] = $this->tool_bom_engin->get_operations();
        $data['machine_groups'] = $this->tool_bom_engin->get_machine_groups();

        $this->view('index_tool_bom_tooling', $data, FALSE);
    }
}


