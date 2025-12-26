<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tool BOM Tooling Controller
 * @property M_tool_bom_tooling $tool_bom_tooling
 */
class Tool_bom_tooling extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('form_validation', 'session'));
        
        $username = $this->session->userdata('username');
        $this->uid = (string)($username ?: 'SYSTEM');

        $this->load->model('M_tool_bom_tooling', 'tool_bom_tooling');
        $this->tool_bom_tooling->uid = $this->uid;

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $this->view('index_tool_bom_tooling', $data, FALSE);
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
            $order_dir = isset($order[0]['dir']) ? $order[0]['dir'] : 'desc';
            
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

            $result = $this->tool_bom_tooling->get_data_serverside($start, $length, $search_value, $order_column, $order_dir, $column_search);

            $formatted_data = array();
            foreach ($result['data'] as $row) {
                $st = isset($row['TD_STATUS']) ? (int)$row['TD_STATUS'] : 0;
                // Status: 2=Active, 3=Pending, 5/lainnya=Inactive
                if ($st === 2) {
                    $status_badge = '<span class="badge badge-success">Active</span>';
                } elseif ($st === 3) {
                    $status_badge = '<span class="badge badge-warning">Pending</span>';
                } else {
                    $status_badge = '<span class="badge badge-secondary">Inactive</span>';
                }

                // Type: Selalu menampilkan "ToolBOM"
                $type_text = 'ToolBOM';

                $id = (int)$row['TD_ID'];
                $edit_url = base_url('Tool_engineering/tool_bom_tooling/edit_page/' . $id);
                $history_url = base_url('Tool_engineering/tool_bom_tooling/history_page/' . $id);
                $detail_url = base_url('Tool_engineering/tool_bom_tooling/detail_page/' . $id);
                $tool_bom_escaped = htmlspecialchars(isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '', ENT_QUOTES, 'UTF-8');
                $tool_bom_link = '<a href="' . $detail_url . '" class="text-primary" style="text-decoration: underline;">' . $tool_bom_escaped . '</a>';
                
                $action_html = '<div class="action-buttons">' .
                    '<a href="' . $edit_url . '" class="btn btn-secondary btn-sm" title="Edit">Edit</a> ' .
                    '<a href="' . $history_url . '" class="btn btn-warning btn-sm" title="History">History</a>' .
                    '</div>';

                $formatted_data[] = array(
                    $action_html,
                    $id,
                    htmlspecialchars(isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    $tool_bom_link,
                    htmlspecialchars(isset($row['TD_PROCESS_NAME']) ? $row['TD_PROCESS_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_MACHINE_GROUP']) ? $row['TD_MACHINE_GROUP'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_REVISION']) ? (string)$row['TD_REVISION'] : '0', ENT_QUOTES, 'UTF-8'),
                    $status_badge,
                    htmlspecialchars(isset($row['TD_EFFECTIVE_DATE']) && $row['TD_EFFECTIVE_DATE'] !== '' ? substr($row['TD_EFFECTIVE_DATE'], 0, 10) : '-', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_MODIFIED_DATE']) && $row['TD_MODIFIED_DATE'] !== '' ? $row['TD_MODIFIED_DATE'] : '-', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : '', ENT_QUOTES, 'UTF-8'),
                    $type_text
                );
            }

            $this->output->set_output(json_encode(array(
                'draw' => $draw,
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $formatted_data
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } catch (Exception $e) {
            log_message('error', '[Tool_bom_tooling::get_data] Exception: ' . $e->getMessage());
            $this->output->set_output(json_encode(array(
                'draw' => isset($draw) ? $draw : 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'An error occurred while fetching data.'
            )));
        }
    }

    public function edit_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_bom_tooling->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        // Map data to view format
        $bom = array(
            'ID' => isset($row['TD_ID']) ? (int)$row['TD_ID'] : 0,
            'TOOL_BOM' => isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '',
            'DESCRIPTION' => isset($row['TD_DESCRIPTION']) ? $row['TD_DESCRIPTION'] : '',
            'PRODUCT_ID' => isset($row['PRODUCT_ID']) ? (int)$row['PRODUCT_ID'] : 0,
            'PROCESS_ID' => isset($row['MLR_OP_ID']) ? (int)$row['MLR_OP_ID'] : 0,
            'MACHINE_GROUP_ID' => isset($row['MLR_MACG_ID']) ? (int)$row['MLR_MACG_ID'] : 0,
            'REVISION' => isset($row['TD_REVISION']) ? (int)$row['TD_REVISION'] : 0,
            'STATUS' => isset($row['TD_STATUS']) ? (int)$row['TD_STATUS'] : 1,
            'EFFECTIVE_DATE' => isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : '',
            'CHANGE_SUMMARY' => isset($row['TD_CHANGE_SUMMARY']) ? $row['TD_CHANGE_SUMMARY'] : '',
            'DRAWING' => isset($row['MLR_DRAWING']) ? $row['MLR_DRAWING'] : '',
            'SKETCH' => isset($row['MLR_SKETCH']) ? $row['MLR_SKETCH'] : '',
            'IS_TRIAL_BOM' => isset($row['ML_TRIAL']) ? (int)$row['ML_TRIAL'] : 0
        );

        $data = array();
        $data['bom'] = $bom;
        $data['products'] = $this->tool_bom_tooling->get_products();
        $data['operations'] = $this->tool_bom_tooling->get_operations();
        $data['machine_groups'] = $this->tool_bom_tooling->get_machine_groups();
        $data['tools'] = $this->tool_bom_tooling->get_tools();
        $data['materials'] = $this->tool_bom_tooling->get_materials();
        $data['makers'] = $this->tool_bom_tooling->get_makers();
        $data['additional_info'] = $this->tool_bom_tooling->get_additional_info($id);

        $this->view('edit_tool_bom_tooling', $data, FALSE);
    }

    public function history_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_bom_tooling->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        $history = $this->tool_bom_tooling->get_history($id);
        
        // Resolve names for info section
        $product_name = isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '';
        $tool_bom = isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '';
        $process_name = isset($row['TD_PROCESS_NAME']) ? $row['TD_PROCESS_NAME'] : '';
        $machine_group_name = isset($row['TD_MACHINE_GROUP']) ? $row['TD_MACHINE_GROUP'] : '';

        $data = array();
        $data['bom'] = $row; // Renamed from 'drawing' to 'bom' for consistency
        $data['history'] = $history;
        $data['product_name'] = $product_name;
        $data['tool_bom'] = $tool_bom;
        $data['process_name'] = $process_name;
        $data['machine_group_name'] = $machine_group_name;

        $this->view('history_tool_bom_tooling', $data, FALSE);
    }

    /**
     * Submit data: EDIT Tool BOM (AJAX)
     */
    public function submit_data()
    {
        if (ob_get_level()) ob_clean();
        
        $this->output->set_content_type('application/json');
        $result = array('success' => false, 'message' => '');
        
        try {
            $action = strtoupper($this->input->post('action', TRUE));
            $id = (int)$this->input->post('ID', TRUE);

            if ($action !== 'EDIT' || $id <= 0) {
                $result['message'] = 'Action atau ID tidak valid.';
                echo json_encode($result);
                return;
            }

            // Validation rules
            $this->form_validation->set_rules('TOOL_BOM', 'Tool BOM', 'required|trim');
            $this->form_validation->set_rules('PRODUCT_ID', 'Product ID', 'required|integer');
            $this->form_validation->set_rules('PROCESS_ID', 'Process ID', 'integer');
            $this->form_validation->set_rules('REVISION', 'Revision', 'integer');
            $this->form_validation->set_rules('STATUS', 'Status', 'integer');

            if ($this->form_validation->run() == FALSE) {
                $this->form_validation->set_error_delimiters('', '');
                $result['message'] = validation_errors() ?: 'Data tidak valid.';
                echo json_encode($result);
                return;
            }

            $tool_bom = trim($this->input->post('TOOL_BOM', TRUE));
            $product_id = (int)$this->input->post('PRODUCT_ID', TRUE);
            $process_id = (int)$this->input->post('PROCESS_ID', TRUE);
            $machine_group_id = (int)$this->input->post('MACHINE_GROUP_ID', TRUE);
            $revision = (int)$this->input->post('REVISION', TRUE);
            $status = (int)$this->input->post('STATUS', TRUE);
            $description = trim($this->input->post('DESCRIPTION', TRUE));
            $effective_date = trim($this->input->post('EFFECTIVE_DATE', TRUE));
            $change_summary = trim($this->input->post('CHANGE_SUMMARY', TRUE));
            $is_trial_bom = $this->input->post('IS_TRIAL_BOM', TRUE) == '1' ? 1 : 0;

            // Handle file uploads - store temporarily, will be moved to Attachment_TMS after getting ML_ID
            $sketch_file_temp = null;
            $sketch_file = null;
            
            if (!empty($_FILES) && isset($_FILES['SKETCH_FILE']) && !empty($_FILES['SKETCH_FILE']['name'])) {
                // Temporary upload directory
                $uploadDir = sys_get_temp_dir() . '/tms_upload_' . time() . '/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $origName = $_FILES['SKETCH_FILE']['name'];
                $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($origName));
                $fileExt = pathinfo($origName, PATHINFO_EXTENSION);
                $fileName = $tool_bom . '_sketch_' . time() . ($fileExt ? '.' . $fileExt : '');
                $target = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['SKETCH_FILE']['tmp_name'], $target)) {
                    $sketch_file_temp = $target; // Store temp path for later move
                    $sketch_file = $fileName; // Store filename for database
                }
            }

            // Get current MLR_ML_ID before update
            $current = $this->tool_bom_tooling->get_by_id($id);
            $ml_id = isset($current['MLR_ML_ID']) ? (int)$current['MLR_ML_ID'] : 0;

            $ok = $this->tool_bom_tooling->update_data(
                $id, $tool_bom, $product_id, $process_id, $machine_group_id, $revision, $status,
                $description, $effective_date, $change_summary, $is_trial_bom, null, $sketch_file
            );
            if ($ok) {
                // Move file to Attachment_TMS folder after successful update
                if ($ml_id > 0 && $sketch_file_temp && file_exists($sketch_file_temp)) {
                    $this->_move_file_to_attachment_folder($sketch_file_temp, $ml_id, $revision, $sketch_file, 'BOM_Sketch');
                }
                
                $result['success'] = true;
                $result['message'] = $this->tool_bom_tooling->messages ?: 'Tool BOM berhasil diubah.';
            } else {
                // Clean up uploaded file if database update failed
                if ($sketch_file_temp && file_exists($sketch_file_temp)) {
                    @unlink($sketch_file_temp);
                }
                $result['success'] = false;
                $result['message'] = $this->tool_bom_tooling->messages ?: 'Gagal mengubah Tool BOM.';
            }
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            log_message('error', '[Tool_bom_tooling::submit_data] Exception: ' . $e->getMessage());
            $result['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
            echo json_encode($result);
        }
    }

    /**
     * Detail page
     */
    public function detail_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_bom_tooling->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        // Map data to view format
        $bom = array(
            'ID' => isset($row['TD_ID']) ? (int)$row['TD_ID'] : 0,
            'MLR_ML_ID' => isset($row['MLR_ML_ID']) ? (int)$row['MLR_ML_ID'] : 0,
            'TOOL_BOM' => isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '',
            'DESCRIPTION' => isset($row['TD_DESCRIPTION']) ? $row['TD_DESCRIPTION'] : '',
            'PRODUCT_ID' => isset($row['PRODUCT_ID']) ? (int)$row['PRODUCT_ID'] : 0,
            'PRODUCT' => isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '',
            'PROCESS_ID' => isset($row['MLR_OP_ID']) ? (int)$row['MLR_OP_ID'] : 0,
            'MACHINE_GROUP_ID' => isset($row['MLR_MACG_ID']) ? (int)$row['MLR_MACG_ID'] : 0,
            'MACHINE_GROUP' => isset($row['TD_MACHINE_GROUP']) ? $row['TD_MACHINE_GROUP'] : '',
            'REVISION' => isset($row['TD_REVISION']) ? (int)$row['TD_REVISION'] : 0,
            'STATUS' => isset($row['TD_STATUS']) ? (int)$row['TD_STATUS'] : 1,
            'EFFECTIVE_DATE' => isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : '',
            'CHANGE_SUMMARY' => isset($row['TD_CHANGE_SUMMARY']) ? $row['TD_CHANGE_SUMMARY'] : '',
            'DRAWING' => isset($row['MLR_DRAWING']) ? $row['MLR_DRAWING'] : '',
            'SKETCH' => isset($row['MLR_SKETCH']) ? $row['MLR_SKETCH'] : '',
            'MODIFIED_DATE' => isset($row['TD_MODIFIED_DATE']) ? $row['TD_MODIFIED_DATE'] : '',
            'MODIFIED_BY' => isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : '',
            'IS_TRIAL_BOM' => isset($row['ML_IS_TRIAL_BOM']) ? (int)$row['ML_IS_TRIAL_BOM'] : (isset($row['ML_TRIAL']) ? (int)$row['ML_TRIAL'] : 0)
        );

        $data = array();
        $data['bom'] = $bom;
        $data['products'] = $this->tool_bom_tooling->get_products();
        $data['operations'] = $this->tool_bom_tooling->get_operations();
        $data['machine_groups'] = $this->tool_bom_tooling->get_machine_groups();
        $data['tools'] = $this->tool_bom_tooling->get_tools();
        $data['additional_info'] = $this->tool_bom_tooling->get_additional_info($id);

        $this->view('detail_tool_bom_tooling', $data, FALSE);
    }

    /**
     * Move uploaded file to Attachment_TMS folder structure
     * Folder structure: Attachment_TMS/{folder}/{ML_ID}/{REVISION}/{filename}
     * @param string $source_file_path Full path to source file
     * @param int $ml_id ML_ID (MLR_ML_ID)
     * @param int $revision MLR_REV
     * @param string $filename Original filename
     * @param string $folder_name Folder name (BOM, BOM_Sketch, Drawing, Drawing_Sketch)
     * @return bool True if successful, false otherwise
     */
    private function _move_file_to_attachment_folder($source_file_path, $ml_id, $revision, $filename, $folder_name = 'BOM_Sketch')
    {
        if (!file_exists($source_file_path) || !is_file($source_file_path)) {
            log_message('error', '[Tool_bom_tooling::_move_file_to_attachment_folder] Source file does not exist: ' . $source_file_path);
            return false;
        }

        // Sanitize filename
        $safe_filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($filename));
        if (empty($safe_filename)) {
            $safe_filename = 'file_' . time();
        }

        // Use application folder: application/tms_modules/Attachment_TMS/{folder}/{ML_ID}/{REVISION}/
        $target_dir = APPPATH . 'tms_modules/Attachment_TMS/' . $folder_name . '/' . (int)$ml_id . '/' . (int)$revision . '/';

        log_message('debug', '[Tool_bom_tooling::_move_file_to_attachment_folder] Attempting to create/move to: ' . $target_dir);

        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            $created = @mkdir($target_dir, 0755, true);
            if (!$created) {
                $error = error_get_last();
                log_message('error', '[Tool_bom_tooling::_move_file_to_attachment_folder] Cannot create directory: ' . $target_dir . '. Error: ' . ($error ? $error['message'] : 'Unknown'));
                return false;
            }
        }

        $target_file = $target_dir . $safe_filename;

        // Move file
        if (@rename($source_file_path, $target_file)) {
            log_message('info', '[Tool_bom_tooling::_move_file_to_attachment_folder] File successfully moved to: ' . $target_file);
            return true;
        } else {
            // Try copy if rename fails
            if (@copy($source_file_path, $target_file)) {
                @unlink($source_file_path);
                log_message('info', '[Tool_bom_tooling::_move_file_to_attachment_folder] File successfully copied to: ' . $target_file);
                return true;
            } else {
                $error = error_get_last();
                log_message('error', '[Tool_bom_tooling::_move_file_to_attachment_folder] Cannot move file to: ' . $target_file . '. Error: ' . ($error ? $error['message'] : 'Unknown'));
                return false;
            }
        }
    }
}