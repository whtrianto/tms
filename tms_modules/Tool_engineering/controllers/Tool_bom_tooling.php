<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Listing Tool BOM untuk kebutuhan Tooling (hanya edit & history)
 * Data diambil dari tabel yang sama dengan engineering (M_tool_bom_engin)
 * 
 * @property M_tool_bom_engin $tool_bom_engin
 */
class Tool_bom_tooling extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('form_validation', 'session'));

        // capture current user id/username for later use (fallback to SYSTEM)
        $username_from_session = $this->session->userdata('username');
        $this->uid = (string) ($username_from_session ?: 'SYSTEM');
        log_message('debug', '[Tool_bom_tooling::__construct] username_from_session=' . var_export($username_from_session, true) . ', uid="' . $this->uid . '"');

        // load model AFTER setting uid, then assign uid to model
        $this->load->model('M_tool_bom_engin', 'tool_bom_engin');
        log_message('debug', '[Tool_bom_tooling::__construct] Model loaded, checking if valid...');
        
        // Verify model was loaded correctly
        if (!property_exists($this, 'tool_bom_engin') || !is_object($this->tool_bom_engin)) {
            log_message('error', '[Tool_bom_tooling::__construct] Model property check failed. Property exists: ' . (property_exists($this, 'tool_bom_engin') ? 'yes' : 'no'));
            // Try loading again with explicit path
            $this->load->model('Tool_engineering/M_tool_bom_engin', 'tool_bom_engin');
        }
        
        if (!isset($this->tool_bom_engin) || !is_object($this->tool_bom_engin) || !method_exists($this->tool_bom_engin, 'get_all')) {
            log_message('error', '[Tool_bom_tooling::__construct] Failed to load M_tool_bom_engin model or model is invalid');
            show_error('Failed to load required model M_tool_bom_engin. Please check your configuration.');
        }
        
        $this->tool_bom_engin->uid = $this->uid;
        log_message('debug', '[Tool_bom_tooling::__construct] model uid set to "' . $this->tool_bom_engin->uid . '"');

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        // Ensure model is loaded
        if (!isset($this->tool_bom_engin) || !is_object($this->tool_bom_engin)) {
            log_message('error', '[Tool_bom_tooling::index] Model not loaded, attempting to reload');
            $this->load->model('M_tool_bom_engin', 'tool_bom_engin');
            if (!isset($this->tool_bom_engin) || !is_object($this->tool_bom_engin)) {
                show_error('Failed to load M_tool_bom_engin model');
            }
            $this->tool_bom_engin->uid = $this->uid;
        }

        $data = array();
        $data['list_data'] = $this->tool_bom_engin->get_all();
        $data['products'] = $this->tool_bom_engin->get_products();
        $data['operations'] = $this->tool_bom_engin->get_operations();
        $data['machine_groups'] = $this->tool_bom_engin->get_machine_groups();

        $this->view('index_tool_bom_tooling', $data, FALSE);
    }
}


