<?php

class M_welcome extends Base_Model
{
    public $messages = '';
    public $user;
    public $table;
    function __construct()
    {
        parent::__construct();
        date_default_timezone_set("Asia/Jakarta");
        $this->sql = $this->load->database('default');
        $this->sql = $this->load->database('taci_mfg',TRUE);
    }

    public function get_message()
    {
        return ["message"=>"Hello, ". $this->session->userdata('fullname')];
    }

    public function get_all_supplier() {
        // Attempt the query and check for errors
        $query = $this->sql->get('db_suppliers');

        if (!$query) {
            log_message('error', 'Database query failed: ' . $this->sql->last_query());
            return [];
        }

        return $query->result();  // Return the result if the query is successful
    }
}
