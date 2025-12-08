<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends MY_Controller
{

	// protected $pageActive = ['quality_assurance', 'fex_qms_data'];
	protected $viewPermission = '';

	public function __construct()
	{
		parent::__construct();
		$this->session->keep_flashdata('csrfkey');
		$this->session->keep_flashdata('csrfvalue');
		$this->load->library('form_validation');
		$this->load->model(['M_welcome','M_auth']);
		$this->config->set_item('Blade_enable', FALSE);
		if (!$this->M_auth->logged_in()) {
			redirect('auth/login');
		}
		check_permission($this->viewPermission);
	}

	public function index()
	{
		$data = $this->M_welcome->get_message();
		$data ['suppliers']= $this->M_welcome->get_all_supplier();
		$this->view('index', $data);
	}
}
