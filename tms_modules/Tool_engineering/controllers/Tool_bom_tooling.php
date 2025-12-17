<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Listing Tool BOM untuk kebutuhan Tooling (hanya edit & history)
 * Data diambil dari tabel TMS_TACI_SITE (TMS_TOOL_MASTER_LIST_MEMBERS, etc.)
 * 
 * @property M_tool_bom_engin $tool_bom_engin
 * @property M_tool_draw_tooling $tool_draw_tooling
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
        // Try standard load first (same as Tool_bom_engin)
        $this->load->model('M_tool_bom_engin', 'tool_bom_engin');
        
        // Verify model loaded correctly
        if (!isset($this->tool_bom_engin) || !is_object($this->tool_bom_engin) || !method_exists($this->tool_bom_engin, 'get_all')) {
            log_message('debug', '[Tool_bom_tooling::__construct] Standard load failed, trying direct require');
            
            // Fallback: try direct require like Tool.php does
            $possibleFiles = [
                APPPATH . 'tms_modules/Tool_engineering/models/M_tool_bom_engin.php',
                APPPATH . 'tms_modules/tool_engineering/models/M_tool_bom_engin.php'
            ];
            
            $loaded = false;
            foreach ($possibleFiles as $f) {
                if (is_file($f) && is_readable($f)) {
                    try {
                        require_once($f);
                        if (class_exists('M_tool_bom_engin')) {
                            $this->tool_bom_engin = new M_tool_bom_engin();
                            if (method_exists($this->tool_bom_engin, 'get_all')) {
                                $loaded = true;
                                log_message('debug', '[Tool_bom_tooling::__construct] Model loaded via direct require from: ' . $f);
                                break;
                            } else {
                                unset($this->tool_bom_engin);
                            }
                        }
                    } catch (Exception $e) {
                        log_message('error', '[Tool_bom_tooling::__construct] Exception loading model from ' . $f . ': ' . $e->getMessage());
                        if (isset($this->tool_bom_engin)) unset($this->tool_bom_engin);
                    }
                }
            }
            
            if (!$loaded) {
                log_message('error', '[Tool_bom_tooling::__construct] Failed to load M_tool_bom_engin model with all methods');
                show_error('Failed to load required model M_tool_bom_engin. Please check your configuration.');
            }
        }
        
        $this->tool_bom_engin->uid = $this->uid;
        log_message('debug', '[Tool_bom_tooling::__construct] model uid set to "' . $this->tool_bom_engin->uid . '"');

        // drawing model used for additional information section
        $this->load->model('M_tool_draw_tooling', 'tool_draw_tooling');
        $this->tool_draw_tooling->uid = $this->uid;

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

    /**
     * Halaman detail Tool BOM Tooling (read-only)
     * @param int $id
     */
    public function detail_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $bom = $this->tool_bom_engin->get_by_id($id);
        if (!$bom) {
            show_404();
            return;
        }

        $productId = isset($bom['PRODUCT_ID']) ? (int)$bom['PRODUCT_ID'] : 0;
        $processId = isset($bom['PROCESS_ID']) ? (int)$bom['PROCESS_ID'] : 0;

        $data = array();
        $data['bom'] = $bom;
        $data['products'] = $this->tool_bom_engin->get_products();
        $data['operations'] = $this->tool_bom_engin->get_operations();
        $data['machine_groups'] = $this->tool_bom_engin->get_machine_groups();

        // Additional Information: Tool Drawing Tooling data
        // Use tooling model which reads from TMS_TACI_SITE tables
        $data['additional_info'] = $this->tool_draw_tooling->get_all();
        $data['materials'] = $this->tool_draw_tooling->get_materials();
        $data['makers'] = $this->tool_draw_tooling->get_makers();
        $data['tools'] = $this->tool_draw_tooling->get_tools();

        $this->view('detail_tool_bom_tooling', $data, FALSE);
    }

    /**
     * Halaman history Tool BOM Tooling
     * @param int $id
     */
    public function history_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $bom = $this->tool_bom_engin->get_by_id($id);
        if (!$bom) {
            show_404();
            return;
        }

        $history = $this->tool_bom_engin->get_history($id);
        
        // Enrich history with resolved names
        $products = $this->tool_bom_engin->get_products();
        $operations = $this->tool_bom_engin->get_operations();
        $machine_groups = $this->tool_bom_engin->get_machine_groups();

        // Resolve product name for current record
        $product_name = '';
        if (isset($bom['PRODUCT_ID']) && (int)$bom['PRODUCT_ID'] > 0) {
            foreach ($products as $p) {
                if ((int)$p['PRODUCT_ID'] === (int)$bom['PRODUCT_ID']) {
                    $product_name = $p['PRODUCT_NAME'];
                    break;
                }
            }
        }
        if ($product_name === '' && isset($bom['PRODUCT'])) {
            $product_name = $bom['PRODUCT'];
        }

        // Resolve process name for current record
        $process_name = '';
        if (isset($bom['PROCESS_ID']) && (int)$bom['PROCESS_ID'] > 0) {
            foreach ($operations as $o) {
                if ((int)$o['OPERATION_ID'] === (int)$bom['PROCESS_ID']) {
                    $process_name = $o['OPERATION_NAME'];
                    break;
                }
            }
        }

        // Resolve machine group name for current record
        $machine_group_name = '';
        if (isset($bom['MACHINE_GROUP_ID']) && (int)$bom['MACHINE_GROUP_ID'] > 0) {
            foreach ($machine_groups as $mg) {
                if ((int)$mg['MACHINE_ID'] === (int)$bom['MACHINE_GROUP_ID']) {
                    $machine_group_name = $mg['MACHINE_NAME'];
                    break;
                }
            }
        }
        if ($machine_group_name === '' && isset($bom['MACHINE_GROUP'])) {
            $machine_group_name = $bom['MACHINE_GROUP'];
        }

        // Enrich history records with resolved names
        foreach ($history as &$h) {
            // Resolve product name
            $h['PRODUCT_NAME'] = '';
            $product_id_to_resolve = isset($h['PRODUCT_ID']) ? (int)$h['PRODUCT_ID'] : 0;
            if ($product_id_to_resolve <= 0 && isset($bom['PRODUCT_ID']) && (int)$bom['PRODUCT_ID'] > 0) {
                $product_id_to_resolve = (int)$bom['PRODUCT_ID'];
            }
            if ($product_id_to_resolve > 0) {
                foreach ($products as $p) {
                    if ((int)$p['PRODUCT_ID'] === $product_id_to_resolve) {
                        $h['PRODUCT_NAME'] = $p['PRODUCT_NAME'];
                        break;
                    }
                }
            }
            if ($h['PRODUCT_NAME'] === '' && isset($h['PRODUCT'])) {
                $h['PRODUCT_NAME'] = $h['PRODUCT'];
            }

            // Resolve process/operation name
            $h['OPERATION_NAME'] = '';
            $process_id_to_resolve = isset($h['PROCESS_ID']) ? (int)$h['PROCESS_ID'] : 0;
            if ($process_id_to_resolve <= 0 && isset($bom['PROCESS_ID']) && (int)$bom['PROCESS_ID'] > 0) {
                $process_id_to_resolve = (int)$bom['PROCESS_ID'];
            }
            if ($process_id_to_resolve > 0) {
                foreach ($operations as $o) {
                    if ((int)$o['OPERATION_ID'] === $process_id_to_resolve) {
                        $h['OPERATION_NAME'] = $o['OPERATION_NAME'];
                        break;
                    }
                }
            }

            // Resolve machine group name
            $h['MACHINE_NAME'] = '';
            $machine_group_id_to_resolve = isset($h['MACHINE_GROUP_ID']) ? (int)$h['MACHINE_GROUP_ID'] : 0;
            if ($machine_group_id_to_resolve <= 0 && isset($bom['MACHINE_GROUP_ID']) && (int)$bom['MACHINE_GROUP_ID'] > 0) {
                $machine_group_id_to_resolve = (int)$bom['MACHINE_GROUP_ID'];
            }
            if ($machine_group_id_to_resolve > 0) {
                foreach ($machine_groups as $mg) {
                    if ((int)$mg['MACHINE_ID'] === $machine_group_id_to_resolve) {
                        $h['MACHINE_NAME'] = $mg['MACHINE_NAME'];
                        break;
                    }
                }
            }
            if ($h['MACHINE_NAME'] === '' && isset($h['MACHINE_GROUP'])) {
                $h['MACHINE_NAME'] = $h['MACHINE_GROUP'];
            }

            // Ensure TOOL_BOM is set
            if (!isset($h['TOOL_BOM']) || $h['TOOL_BOM'] === '') {
                $h['TOOL_BOM'] = isset($bom['TOOL_BOM']) ? $bom['TOOL_BOM'] : '';
            }
        }
        unset($h);

        $data = array();
        $data['bom'] = $bom;
        $data['history'] = $history;
        $data['product_name'] = $product_name;
        $data['process_name'] = $process_name;
        $data['machine_group_name'] = $machine_group_name;
        $data['tool_bom'] = isset($bom['TOOL_BOM']) ? $bom['TOOL_BOM'] : '';

        $this->view('history_tool_bom_tooling', $data, FALSE);
    }
}
