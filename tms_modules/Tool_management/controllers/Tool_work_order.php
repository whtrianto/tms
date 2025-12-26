<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tool Work Order Controller
 * @property M_tool_work_order $tool_work_order
 */
class Tool_work_order extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('form_validation', 'session'));
        
        // Get user ID from session (prefer user_id, fallback to username lookup)
        $user_id = $this->session->userdata('user_id');
        if (!$user_id || !is_numeric($user_id)) {
            // Try to get user ID from username
            $username = $this->session->userdata('username');
            if ($username) {
                $db_tms = $this->load->database('tms_NEW', TRUE);
                $q = $db_tms->query("SELECT TOP 1 USR_ID FROM TMS_NEW.dbo.MS_USERS WHERE USR_NAME = ?", array($username));
                if ($q && $q->num_rows() > 0) {
                    $user_id = (int)$q->row()->USR_ID;
                }
            }
        }
        $this->uid = ($user_id && is_numeric($user_id)) ? (int)$user_id : 1; // Default to user ID 1 if not found

        $this->load->model('M_tool_work_order', 'tool_work_order');
        $this->tool_work_order->uid = $this->uid;

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $this->view('index_tool_work_order', $data, FALSE);
    }

    /**
     * Server-side DataTable AJAX handler
     */
    public function get_data()
    {
        if (ob_get_level()) ob_clean();
        $this->output->set_content_type('application/json', 'UTF-8');

        try {
            $draw = (int)$this->input->post('draw');
            $start = (int)$this->input->post('start');
            $length = (int)$this->input->post('length');
            $search = $this->input->post('search');
            $search_value = isset($search['value']) ? trim($search['value']) : '';
            $order = $this->input->post('order');
            $order_column_raw = isset($order[0]['column']) ? (int)$order[0]['column'] : 1;
            $order_dir = isset($order[0]['dir']) ? $order[0]['dir'] : 'asc';
            
            // Adjust order_column: Action is now at index 0 (non-sortable), so subtract 1 for other columns
            // If order_column is 0 (Action), use default (0 = ID)
            $order_column = ($order_column_raw == 0) ? 0 : ($order_column_raw - 1);

            $columns = $this->input->post('columns');
            $column_search = array();
            if (is_array($columns)) {
                foreach ($columns as $idx => $col) {
                    if ($idx == 0) continue; // Skip Action column (index 0)
                    if (isset($col['search']['value']) && $col['search']['value'] !== '') {
                        $column_search[$idx - 1] = $col['search']['value']; // Adjust index
                    }
                }
            }

            $result = $this->tool_work_order->get_data_serverside($start, $length, $search_value, $order_column, $order_dir, $column_search);

            $formatted_data = array();
            foreach ($result['data'] as $row) {
                $wo_type_name = $this->tool_work_order->get_wo_type_name(isset($row['WO_TYPE']) ? $row['WO_TYPE'] : 0);
                $wo_reason_name = $this->tool_work_order->get_wo_reason_name(isset($row['WO_REASON']) ? $row['WO_REASON'] : 0);
                $status_badge = $this->tool_work_order->get_wo_status_badge(isset($row['WO_STATUS']) ? $row['WO_STATUS'] : 0);
                
                $id = (int)$row['WO_ID'];
                $wo_no = htmlspecialchars(isset($row['WO_NO']) ? $row['WO_NO'] : '', ENT_QUOTES, 'UTF-8');
                $edit_url = base_url('Tool_management/tool_work_order/edit_page/' . $id);
                
                $action_html = '<div class="action-buttons">' .
                    '<a href="' . $edit_url . '" class="btn btn-secondary btn-sm" title="Edit">Edit</a> ' .
                    '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $id . '" data-wo-no="' . $wo_no . '">Del</button>' .
                    '</div>';

                // Format dates
                $date = isset($row['DATE']) ? $row['DATE'] : '';
                if (!empty($date) && strlen($date) >= 10) {
                    $date = substr($date, 0, 10);
                }
                $target_date = isset($row['TARGET_COMPLETION_DATE']) ? $row['TARGET_COMPLETION_DATE'] : '';
                if (!empty($target_date) && strlen($target_date) >= 10) {
                    $target_date = substr($target_date, 0, 10);
                }
                $actual_date = isset($row['ACTUAL_COMPLETION_DATE']) ? $row['ACTUAL_COMPLETION_DATE'] : '';
                if (!empty($actual_date) && strlen($actual_date) >= 10) {
                    $actual_date = substr($actual_date, 0, 10);
                }

                $formatted_data[] = array(
                    $action_html,
                    $id,
                    htmlspecialchars($date, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['WO_NO']) ? $row['WO_NO'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($wo_type_name, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['REQUESTED_BY']) ? $row['REQUESTED_BY'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TOOL_ID']) ? $row['TOOL_ID'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TOOL_DRAWING_NO']) ? $row['TOOL_DRAWING_NO'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($target_date, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($actual_date, ENT_QUOTES, 'UTF-8'),
                    $status_badge,
                    htmlspecialchars($wo_reason_name, ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TOOL_MAKING_DRAW_NO']) ? $row['TOOL_MAKING_DRAW_NO'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['RQ_NO']) ? $row['RQ_NO'] : '', ENT_QUOTES, 'UTF-8')
                );
            }

            $this->output->set_output(json_encode(array(
                'draw' => $draw,
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $formatted_data
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        } catch (Exception $e) {
            log_message('error', '[Tool_work_order::get_data] Exception: ' . $e->getMessage());
            log_message('error', '[Tool_work_order::get_data] Trace: ' . $e->getTraceAsString());
            $this->output->set_output(json_encode(array(
                'draw' => isset($draw) ? $draw : 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'Error loading data: ' . $e->getMessage()
            )));
        }
    }

    /**
     * Delete Work Order
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('WO_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }

        $ok = $this->tool_work_order->delete_data($id);
        echo json_encode(array(
            'success' => $ok,
            'message' => $this->tool_work_order->messages
        ));
    }

    /**
     * Add page
     */
    public function add_page()
    {
        $data = array();
        $data['work_order'] = array(); // Empty array for new work order
        $data['external_costs'] = array(); // Empty for new work order
        $data['work_activities'] = $this->tool_work_order->get_work_activities();
        $data['suppliers'] = $this->tool_work_order->get_suppliers();
        $data['users'] = $this->tool_work_order->get_users();
        $data['departments'] = $this->tool_work_order->get_departments();
        $data['tool_inventory_modal'] = $this->tool_work_order->get_tool_inventory_for_modal();
        $this->view('add_tool_work_order', $data, FALSE);
    }

    /**
     * Edit page
     */
    public function edit_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $wo = $this->tool_work_order->get_by_id($id);
        if (!$wo) {
            show_404();
            return;
        }

        $data = array();
        $data['work_order'] = $wo;
        $data['external_costs'] = $this->tool_work_order->get_external_costs($id);
        $data['work_activities'] = $this->tool_work_order->get_work_activities();
        $data['suppliers'] = $this->tool_work_order->get_suppliers();
        $data['users'] = $this->tool_work_order->get_users();
        $data['departments'] = $this->tool_work_order->get_departments();
        $this->view('edit_tool_work_order', $data, FALSE);
    }

    /**
     * Get Users (AJAX)
     */
    public function get_users()
    {
        $this->output->set_content_type('application/json');
        $users = $this->tool_work_order->get_users();
        echo json_encode(array('success' => true, 'data' => $users));
    }

    /**
     * Get Departments (AJAX)
     */
    public function get_departments()
    {
        $this->output->set_content_type('application/json');
        $departments = $this->tool_work_order->get_departments();
        echo json_encode(array('success' => true, 'data' => $departments));
    }

    /**
     * Get Tool Inventory details by Tool ID (AJAX)
     * PHP 5.6.36 & CI3 compatible
     */
    public function get_tool_inventory_details()
    {
        if (ob_get_level()) ob_clean();
        $this->output->set_content_type('application/json', 'UTF-8');

        try {
            $tool_id = trim($this->input->post('tool_id', TRUE));
            log_message('debug', '[Tool_work_order::get_tool_inventory_details] Received Tool ID: [' . $tool_id . ']');
            
            if (empty($tool_id)) {
                echo json_encode(array('success' => false, 'message' => 'Tool ID tidak valid.'));
                return;
            }

            $details = $this->tool_work_order->get_tool_inventory_details_by_tool_id($tool_id);
            
            if ($details && is_array($details) && count($details) > 0) {
                log_message('debug', '[Tool_work_order::get_tool_inventory_details] Found details for Tool ID: [' . $tool_id . ']');
                
                // PHP 5.6.36 compatible - use json_encode without flags if not available
                if (defined('JSON_UNESCAPED_SLASHES') && defined('JSON_UNESCAPED_UNICODE')) {
                    echo json_encode(array('success' => true, 'data' => $details), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                } else {
                    // Fallback for older PHP versions
                    $json = json_encode(array('success' => true, 'data' => $details));
                    $json = str_replace('\\/', '/', $json);
                    echo $json;
                }
            } else {
                log_message('debug', '[Tool_work_order::get_tool_inventory_details] Tool ID not found: [' . $tool_id . ']');
                echo json_encode(array('success' => false, 'message' => 'Tool ID tidak ditemukan.'));
            }
        } catch (Exception $e) {
            log_message('error', '[Tool_work_order::get_tool_inventory_details] Exception: ' . $e->getMessage());
            echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
        }
    }

    /**
     * Get External Cost by ID (AJAX)
     */
    public function get_external_cost()
    {
        $this->output->set_content_type('application/json');

        $extcost_id = (int)$this->input->post('EXTCOST_ID', TRUE);
        if ($extcost_id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }

        $extcost = $this->tool_work_order->get_external_cost_by_id($extcost_id);
        if (!$extcost) {
            echo json_encode(array('success' => false, 'message' => 'External Cost tidak ditemukan.'));
            return;
        }

        echo json_encode(array(
            'success' => true,
            'data' => $extcost
        ));
    }

    /**
     * Submit External Cost (Add/Edit)
     */
    public function submit_external_cost()
    {
        $this->output->set_content_type('application/json');

        $action = strtoupper($this->input->post('action', TRUE));
        $wo_id = (int)$this->input->post('EXTCOST_WO_ID', TRUE);
        $extcost_id = (int)$this->input->post('EXTCOST_ID', TRUE);

        if ($wo_id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'WO_ID tidak valid.'));
            return;
        }

        $wa_id = (int)$this->input->post('EXTCOST_WA_ID', TRUE);
        $sup_id = (int)$this->input->post('EXTCOST_SUP_ID', TRUE);
        $unit_price = (float)$this->input->post('EXTCOST_SUP_UNIT_PRICE', TRUE);
        $qty = (float)$this->input->post('EXTCOST_SUP_QTY', TRUE);
        $date = $this->input->post('EXTCOST_DATE', TRUE);
        $po_no = $this->input->post('EXTCOST_PO_NO', TRUE);
        $invoice_no = $this->input->post('EXTCOST_INVOICE_NO', TRUE);
        $rf_no = $this->input->post('EXTCOST_RF_NO', TRUE);
        $grn_date = $this->input->post('EXTCOST_GRN_DATE', TRUE);
        $grn_no = $this->input->post('EXTCOST_GRN_NO', TRUE);

        if ($wa_id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'Activity harus dipilih.'));
            return;
        }

        $ok = $this->tool_work_order->save_external_cost($action, $extcost_id, $wo_id, $wa_id, $sup_id, $unit_price, $qty, $date, $po_no, $invoice_no, $rf_no, $grn_date, $grn_no);
        echo json_encode(array(
            'success' => $ok,
            'message' => $this->tool_work_order->messages
        ));
    }

    /**
     * Submit Work Order data (Add/Edit)
     */
    public function submit_data()
    {
        if (ob_get_level()) ob_clean();
        $this->output->set_content_type('application/json', 'UTF-8');

        try {
            $action = $this->input->post('action', TRUE);
            
            if ($action === 'ADD') {
                $created_date = $this->input->post('WO_CREATED_DATE', TRUE);
                $wo_type = (int)$this->input->post('WO_TYPE', TRUE);
                $requested_by = (int)$this->input->post('WO_REQUESTED_BY', TRUE);
                $department = $this->input->post('WO_DEPARTMENT', TRUE);
                $reason = (int)$this->input->post('WO_REASON', TRUE);
                $remarks = $this->input->post('WO_REMARKS', TRUE);
                $qty = (int)$this->input->post('WO_QTY', TRUE);
                $target_com_date = $this->input->post('WO_TARGET_COM_DATE', TRUE);
                $actual_com_date = $this->input->post('WO_ACTUAL_COM_DATE', TRUE);
                $status = (int)$this->input->post('WO_STATUS', TRUE);
                $condition = $this->input->post('WO_CONDITION', TRUE);
                $urgency = $this->input->post('WO_URGENCY', TRUE);
                $inv_id = (int)$this->input->post('WO_INV_ID', TRUE);
                
                // Validate required fields
                if ($requested_by <= 0) {
                    echo json_encode(array('success' => false, 'message' => 'Requested By harus dipilih.'));
                    return;
                }
                
                $data = array(
                    'WO_CREATED_DATE' => !empty($created_date) ? $created_date : date('Y-m-d'),
                    'WO_TYPE' => $wo_type > 0 ? $wo_type : 1, // Default: 1 (Repair)
                    'WO_REQUESTED_BY' => $requested_by, // Required, already validated above
                    'WO_DEPARTMENT' => !empty($department) ? $department : null,
                    'WO_REASON' => $reason > 0 ? $reason : null,
                    'WO_REMARKS' => !empty($remarks) ? $remarks : null,
                    'WO_QTY' => $qty > 0 ? $qty : 1,
                    'WO_TARGET_COM_DATE' => !empty($target_com_date) ? $target_com_date : null,
                    'WO_ACTUAL_COM_DATE' => !empty($actual_com_date) ? $actual_com_date : null,
                    'WO_STATUS' => $status > 0 ? $status : 1, // Default: 1 (Open)
                    'WO_CONDITION' => !empty($condition) ? $condition : null,
                    'WO_URGENCY' => !empty($urgency) ? $urgency : null,
                    'WO_INV_ID' => $inv_id > 0 ? $inv_id : null
                );
                
                $ok = $this->tool_work_order->add_data($data);
                echo json_encode(array(
                    'success' => $ok,
                    'message' => $this->tool_work_order->messages
                ));
            } elseif ($action === 'EDIT') {
                $wo_id = (int)$this->input->post('WO_ID', TRUE);
                if ($wo_id <= 0) {
                    echo json_encode(array('success' => false, 'message' => 'Work Order ID tidak valid.'));
                    return;
                }
                
                $created_date = $this->input->post('WO_CREATED_DATE', TRUE);
                $requested_by = (int)$this->input->post('WO_REQUESTED_BY', TRUE);
                $department = $this->input->post('WO_DEPARTMENT', TRUE);
                $remarks = $this->input->post('WO_REMARKS', TRUE);
                $qty = (int)$this->input->post('WO_QTY', TRUE);
                $target_com_date = $this->input->post('WO_TARGET_COM_DATE', TRUE);
                $actual_com_date = $this->input->post('WO_ACTUAL_COM_DATE', TRUE);
                $condition = $this->input->post('WO_CONDITION', TRUE);
                $urgency = $this->input->post('WO_URGENCY', TRUE);
                
                $data = array(
                    'WO_CREATED_DATE' => !empty($created_date) ? $created_date : null,
                    'WO_REQUESTED_BY' => $requested_by > 0 ? $requested_by : null,
                    'WO_DEPARTMENT' => !empty($department) ? $department : null,
                    'WO_REMARKS' => !empty($remarks) ? $remarks : null,
                    'WO_QTY' => $qty > 0 ? $qty : 1,
                    'WO_TARGET_COM_DATE' => !empty($target_com_date) ? $target_com_date : null,
                    'WO_ACTUAL_COM_DATE' => !empty($actual_com_date) ? $actual_com_date : null,
                    'WO_CONDITION' => !empty($condition) ? $condition : null,
                    'WO_URGENCY' => !empty($urgency) ? $urgency : null
                );
                
                $ok = $this->tool_work_order->update_data($wo_id, $data);
                echo json_encode(array(
                    'success' => $ok,
                    'message' => $this->tool_work_order->messages
                ));
            } else {
                echo json_encode(array('success' => false, 'message' => 'Action tidak valid.'));
            }
        } catch (Exception $e) {
            log_message('error', '[Tool_work_order::submit_data] Exception: ' . $e->getMessage());
            echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
        }
    }

    /**
     * Delete External Cost
     */
    public function delete_external_cost()
    {
        $this->output->set_content_type('application/json');

        $extcost_id = (int)$this->input->post('EXTCOST_ID', TRUE);
        if ($extcost_id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }

        $ok = $this->tool_work_order->delete_external_cost($extcost_id);
        echo json_encode(array(
            'success' => $ok,
            'message' => $this->tool_work_order->messages
        ));
    }
}