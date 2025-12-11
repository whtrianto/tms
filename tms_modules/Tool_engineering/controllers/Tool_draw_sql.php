<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller alternatif untuk list Tool Drawing menggunakan struktur DB lama (struktur-tms.sql)
 * View: index_tool_draw_sql.php
 */
class Tool_draw_sql extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('session'));
        $this->load->model('M_tool_draw_sql', 'tool_draw_sql');
        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $data['list_data'] = $this->tool_draw_sql->get_all();

        $this->view('index_tool_draw_sql', $data, FALSE);
    }
}

