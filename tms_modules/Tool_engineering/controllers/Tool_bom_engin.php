<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Tool BOM Engineering Controller
 * @property M_tool_bom_engin $tool_bom_engin
 */
class Tool_bom_engin extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('form_validation', 'session'));
        
        $username = $this->session->userdata('username');
        $this->uid = (string)($username ?: 'SYSTEM');

        $this->load->model('M_tool_bom_engin', 'tool_bom_engin');
        $this->tool_bom_engin->uid = $this->uid;

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        $this->view('index_tool_bom_engin', $data, FALSE);
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
            $order_column = isset($order[0]['column']) ? (int)$order[0]['column'] : 0;
            $order_dir = isset($order[0]['dir']) ? $order[0]['dir'] : 'desc';

            $columns = $this->input->post('columns');
            $column_search = array();
            if (is_array($columns)) {
                foreach ($columns as $idx => $col) {
                    if (isset($col['search']['value']) && $col['search']['value'] !== '') {
                        $column_search[$idx] = $col['search']['value'];
                    }
                }
            }

            $result = $this->tool_bom_engin->get_data_serverside($start, $length, $search_value, $order_column, $order_dir, $column_search);

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

                $id = (int)$row['TD_ID'];
                $edit_url = base_url('Tool_engineering/tool_bom_engin/edit_page/' . $id);
                $history_url = base_url('Tool_engineering/tool_bom_engin/history_page/' . $id);
                $detail_url = base_url('Tool_engineering/tool_bom_engin/detail_page/' . $id);
                $tool_bom_escaped = htmlspecialchars(isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '', ENT_QUOTES, 'UTF-8');
                $tool_bom_link = '<a href="' . $detail_url . '" class="text-primary" style="text-decoration: underline;">' . $tool_bom_escaped . '</a>';
                
                $action_html = '<div class="action-buttons">' .
                    '<a href="' . $edit_url . '" class="btn btn-secondary btn-sm" title="Edit">Edit</a> ' .
                    '<a href="' . $history_url . '" class="btn btn-warning btn-sm" title="History">Hist</a> ' .
                    '<button class="btn btn-danger btn-sm btn-delete" data-id="' . $id . '" data-name="' . $tool_bom_escaped . '">Del</button>' .
                    '</div>';

                $formatted_data[] = array(
                    $id,
                    $tool_bom_link,
                    htmlspecialchars(isset($row['TD_DESCRIPTION']) ? $row['TD_DESCRIPTION'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_MACHINE_GROUP']) ? $row['TD_MACHINE_GROUP'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_REVISION']) ? (string)$row['TD_REVISION'] : '0', ENT_QUOTES, 'UTF-8'),
                    $status_badge,
                    htmlspecialchars(isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : '', ENT_QUOTES, 'UTF-8'),
                    $action_html
                );
            }

            $this->output->set_output(json_encode(array(
                'draw' => $draw,
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $formatted_data
            ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        } catch (Exception $e) {
            log_message('error', '[Tool_bom_engin::get_data] Exception: ' . $e->getMessage());
            $this->output->set_output(json_encode(array(
                'draw' => isset($draw) ? $draw : 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'Error loading data.'
            )));
        }
    }

    /**
     * Delete Tool BOM
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TD_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }

        $ok = $this->tool_bom_engin->delete_data($id);
        echo json_encode(array(
            'success' => $ok,
            'message' => $this->tool_bom_engin->messages
        ));
    }

    /**
     * Add page
     */
    public function add_page()
    {
        $data = array();
        $data['products'] = $this->tool_bom_engin->get_products();
        $data['operations'] = $this->tool_bom_engin->get_operations();
        $data['machine_groups'] = $this->tool_bom_engin->get_machine_groups();
        
        $this->view('add_tool_bom_engin', $data, FALSE);
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

        $row = $this->tool_bom_engin->get_by_id($id);
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
            'IS_TRIAL_BOM' => isset($row['ML_IS_TRIAL_BOM']) ? (int)$row['ML_IS_TRIAL_BOM'] : (isset($row['ML_TRIAL']) ? (int)$row['ML_TRIAL'] : 0)
        );

        $data = array();
        $data['bom'] = $bom;
        $data['products'] = $this->tool_bom_engin->get_products();
        $data['operations'] = $this->tool_bom_engin->get_operations();
        $data['machine_groups'] = $this->tool_bom_engin->get_machine_groups();
        $data['tools'] = $this->tool_bom_engin->get_tools();
        $data['materials'] = $this->tool_bom_engin->get_materials();
        $data['makers'] = $this->tool_bom_engin->get_makers();
        $data['additional_info'] = $this->tool_bom_engin->get_additional_info($id);
        
        // Load Tool Drawing Engineering model for dropdown
        $this->load->model('M_tool_draw_engin', 'tool_draw_engin');
        $data['tool_drawings'] = $this->tool_draw_engin->get_all();

        $this->view('edit_tool_bom_engin', $data, FALSE);
    }
    
    /**
     * Get Tool Drawing detail by ID (for auto-fill)
     */
    public function get_drawing_detail()
    {
        $this->output->set_content_type('application/json');
        
        $mlr_id = (int)$this->input->post('MLR_ID', TRUE);
        if ($mlr_id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'ID tidak valid.'));
            return;
        }
        
        $this->load->model('M_tool_draw_engin', 'tool_draw_engin');
        $drawing = $this->tool_draw_engin->get_by_id($mlr_id);
        
        if (!$drawing) {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
            return;
        }
        
        echo json_encode(array(
            'success' => true,
            'data' => array(
                'TD_DRAWING_NO' => isset($drawing['TD_DRAWING_NO']) ? $drawing['TD_DRAWING_NO'] : '',
                'TD_REVISION' => isset($drawing['TD_REVISION']) ? (int)$drawing['TD_REVISION'] : 0,
                'TD_TOOL_ID' => isset($drawing['TD_TOOL_ID']) ? (int)$drawing['TD_TOOL_ID'] : 0,
                'TD_TOOL_NAME' => isset($drawing['TD_TOOL_NAME']) ? $drawing['TD_TOOL_NAME'] : '',
                'TD_DRAWING_FILE' => isset($drawing['TD_DRAWING_FILE']) ? $drawing['TD_DRAWING_FILE'] : '',
                'TD_PRODUCT_ID' => isset($drawing['TD_PRODUCT_ID']) ? (int)$drawing['TD_PRODUCT_ID'] : 0,
                'TD_PROCESS_ID' => isset($drawing['TD_PROCESS_ID']) ? (int)$drawing['TD_PROCESS_ID'] : 0,
                'TD_MATERIAL_ID' => isset($drawing['TD_MATERIAL_ID']) ? (int)$drawing['TD_MATERIAL_ID'] : 0,
                'TD_MAKER_ID' => isset($drawing['TD_MAKER_ID']) ? (int)$drawing['TD_MAKER_ID'] : 0,
                'TD_MACHINE_GROUP_ID' => isset($drawing['TD_MACHINE_GROUP_ID']) ? (int)$drawing['TD_MACHINE_GROUP_ID'] : 0,
                'TD_STATUS' => isset($drawing['TD_STATUS']) ? (int)$drawing['TD_STATUS'] : 1
            )
        ));
    }

    /**
     * History page
     */
    public function history_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_bom_engin->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        // Resolve names for info section
        $product_name = isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '';
        $tool_bom = isset($row['TD_TOOL_BOM']) ? $row['TD_TOOL_BOM'] : '';
        
        // Get process name
        $process_name = '';
        if (isset($row['MLR_OP_ID']) && (int)$row['MLR_OP_ID'] > 0) {
            $operations = $this->tool_bom_engin->get_operations();
            foreach ($operations as $op) {
                if ((int)$op['OPERATION_ID'] === (int)$row['MLR_OP_ID']) {
                    $process_name = $op['OPERATION_NAME'];
                    break;
                }
            }
        }
        
        // Get machine group name
        $machine_group_name = isset($row['TD_MACHINE_GROUP']) ? $row['TD_MACHINE_GROUP'] : '';
        if (empty($machine_group_name) && isset($row['MLR_MACG_ID']) && (int)$row['MLR_MACG_ID'] > 0) {
            $machines = $this->tool_bom_engin->get_machine_groups();
            foreach ($machines as $m) {
                if ((int)$m['MACHINE_ID'] === (int)$row['MLR_MACG_ID']) {
                    $machine_group_name = $m['MACHINE_NAME'];
                    break;
                }
            }
        }

        $data = array();
        $data['drawing'] = $row;
        $data['history'] = $this->tool_bom_engin->get_history($id);
        $data['product_name'] = $product_name;
        $data['tool_bom'] = $tool_bom;
        $data['process_name'] = $process_name;
        $data['machine_group_name'] = $machine_group_name;

        $this->view('history_tool_bom_engin', $data, FALSE);
    }

    /**
     * Submit data: ADD / EDIT Tool BOM (AJAX)
     */
    public function submit_data()
    {
        if (ob_get_level()) ob_clean();
        
        $this->output->set_content_type('application/json');
        $result = array('success' => false, 'message' => '');
        
        try {
            $action = strtoupper($this->input->post('action', TRUE));
            $id = (int)$this->input->post('ID', TRUE);

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
            $drawing_file_temp = null;
            $sketch_file_temp = null;
            $drawing_file = null;
            $sketch_file = null;
            
            if (!empty($_FILES) && isset($_FILES['DRAWING_FILE']) && !empty($_FILES['DRAWING_FILE']['name'])) {
                // Temporary upload directory
                $uploadDir = sys_get_temp_dir() . '/tms_upload_' . time() . '/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $origName = $_FILES['DRAWING_FILE']['name'];
                $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($origName));
                $fileExt = pathinfo($origName, PATHINFO_EXTENSION);
                $fileName = $tool_bom . '_drawing_' . time() . ($fileExt ? '.' . $fileExt : '');
                $target = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['DRAWING_FILE']['tmp_name'], $target)) {
                    $drawing_file_temp = $target; // Store temp path for later move
                    $drawing_file = $fileName; // Store filename for database
                }
            }
            
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

            if ($action === 'ADD') {
                $ok = $this->tool_bom_engin->add_data(
                    $tool_bom, $product_id, $process_id, $machine_group_id, $revision, $status,
                    $description, $effective_date, $change_summary, $is_trial_bom, $drawing_file, $sketch_file
                );
                if ($ok) {
                    // Get ML_ID (MLR_ML_ID) from the newly created record
                    $ml_id = $this->_get_ml_id_by_tool_bom($tool_bom);
                    
                    // Move files to Attachment_TMS folder after successful insert
                    if ($ml_id > 0) {
                        if ($drawing_file_temp && file_exists($drawing_file_temp)) {
                            $this->_move_file_to_attachment_folder($drawing_file_temp, $ml_id, $revision, $drawing_file, 'BOM');
                        }
                        if ($sketch_file_temp && file_exists($sketch_file_temp)) {
                            $this->_move_file_to_attachment_folder($sketch_file_temp, $ml_id, $revision, $sketch_file, 'BOM_Sketch');
                        }
                    }
                    
                    $result['success'] = true;
                    $result['message'] = $this->tool_bom_engin->messages ?: 'Tool BOM berhasil ditambahkan.';
                } else {
                    // Clean up uploaded files if database insert failed
                    if ($drawing_file_temp && file_exists($drawing_file_temp)) {
                        @unlink($drawing_file_temp);
                    }
                    if ($sketch_file_temp && file_exists($sketch_file_temp)) {
                        @unlink($sketch_file_temp);
                    }
                    $result['success'] = false;
                    $result['message'] = $this->tool_bom_engin->messages ?: 'Gagal menambahkan Tool BOM.';
                }
            } elseif ($action === 'EDIT' && $id > 0) {
                // Get current MLR_ML_ID before update
                $current = $this->tool_bom_engin->get_by_id($id);
                $ml_id = isset($current['MLR_ML_ID']) ? (int)$current['MLR_ML_ID'] : 0;
                
                $ok = $this->tool_bom_engin->update_data(
                    $id, $tool_bom, $product_id, $process_id, $machine_group_id, $revision, $status,
                    $description, $effective_date, $change_summary, $is_trial_bom, $drawing_file, $sketch_file
                );
                if ($ok) {
                    // Move files to Attachment_TMS folder after successful update
                    if ($ml_id > 0) {
                        if ($drawing_file_temp && file_exists($drawing_file_temp)) {
                            $this->_move_file_to_attachment_folder($drawing_file_temp, $ml_id, $revision, $drawing_file, 'BOM');
                        }
                        if ($sketch_file_temp && file_exists($sketch_file_temp)) {
                            $this->_move_file_to_attachment_folder($sketch_file_temp, $ml_id, $revision, $sketch_file, 'BOM_Sketch');
                        }
                    }
                    
                    $result['success'] = true;
                    $result['message'] = $this->tool_bom_engin->messages ?: 'Tool BOM berhasil diubah.';
                } else {
                    // Clean up uploaded files if database update failed
                    if ($drawing_file_temp && file_exists($drawing_file_temp)) {
                        @unlink($drawing_file_temp);
                    }
                    if ($sketch_file_temp && file_exists($sketch_file_temp)) {
                        @unlink($sketch_file_temp);
                    }
                    $result['success'] = false;
                    $result['message'] = $this->tool_bom_engin->messages ?: 'Gagal mengubah Tool BOM.';
                }
            } else {
                $result['message'] = 'Action tidak valid.';
            }
            
            echo json_encode($result);
            
        } catch (Exception $e) {
            log_message('error', '[Tool_bom_engin::submit_data] Exception: ' . $e->getMessage());
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

        $row = $this->tool_bom_engin->get_by_id($id);
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
            'PRODUCT' => isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '',
            'PROCESS_ID' => isset($row['MLR_OP_ID']) ? (int)$row['MLR_OP_ID'] : 0,
            'MACHINE_GROUP_ID' => isset($row['MLR_MACG_ID']) ? (int)$row['MLR_MACG_ID'] : 0,
            'MACHINE_GROUP' => isset($row['TD_MACHINE_GROUP']) ? $row['TD_MACHINE_GROUP'] : '',
            'REVISION' => isset($row['TD_REVISION']) ? (int)$row['TD_REVISION'] : 0,
            'STATUS' => isset($row['TD_STATUS']) ? (int)$row['TD_STATUS'] : 1,
            'EFFECTIVE_DATE' => isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : '',
            'CHANGE_SUMMARY' => isset($row['TD_CHANGE_SUMMARY']) ? $row['TD_CHANGE_SUMMARY'] : '',
            'DRAWING' => isset($row['MLR_DRAWING']) ? $row['MLR_DRAWING'] : '',
            'MODIFIED_DATE' => isset($row['TD_MODIFIED_DATE']) ? $row['TD_MODIFIED_DATE'] : '',
            'MODIFIED_BY' => isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : '',
            'IS_TRIAL_BOM' => isset($row['ML_IS_TRIAL_BOM']) ? (int)$row['ML_IS_TRIAL_BOM'] : 0
        );

        $data = array();
        $data['bom'] = $bom;
        $data['products'] = $this->tool_bom_engin->get_products();
        $data['operations'] = $this->tool_bom_engin->get_operations();
        $data['machine_groups'] = $this->tool_bom_engin->get_machine_groups();
        $data['tools'] = $this->tool_bom_engin->get_tools();
        $data['additional_info'] = $this->tool_bom_engin->get_additional_info($id);

        $this->view('detail_tool_bom_engin', $data, FALSE);
    }

    /**
     * Get ML_ID by Tool BOM number
     * @param string $tool_bom Tool BOM number
     * @return int ML_ID or 0 if not found
     */
    private function _get_ml_id_by_tool_bom($tool_bom)
    {
        if (empty($tool_bom)) {
            return 0;
        }

        $db_tms = $this->load->database('tms_NEW', TRUE);
        if (!$db_tms) {
            return 0;
        }

        // Try up to 3 times with small delay (in case of transaction delay)
        $max_retries = 3;
        $retry_delay = 100000; // 100ms in microseconds

        for ($i = 0; $i < $max_retries; $i++) {
            $sql = "SELECT ML_ID FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST WHERE ML_TOOL_DRAW_NO = ? AND ML_TYPE = 2";
            $query = $db_tms->query($sql, array($tool_bom));

            if ($query && $query->num_rows() > 0) {
                $ml_id = (int)$query->row()->ML_ID;
                log_message('debug', '[Tool_bom_engin::_get_ml_id_by_tool_bom] Found ML_ID: ' . $ml_id . ' for Tool BOM: ' . $tool_bom . ' (attempt ' . ($i + 1) . ')');
                return $ml_id;
            }

            // Wait before retry (except on last attempt)
            if ($i < $max_retries - 1) {
                usleep($retry_delay);
            }
        }

        log_message('error', '[Tool_bom_engin::_get_ml_id_by_tool_bom] ML_ID not found for Tool BOM: ' . $tool_bom . ' after ' . $max_retries . ' attempts');
        return 0;
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
    private function _move_file_to_attachment_folder($source_file_path, $ml_id, $revision, $filename, $folder_name = 'BOM')
    {
        if (!file_exists($source_file_path) || !is_file($source_file_path)) {
            log_message('error', '[Tool_bom_engin::_move_file_to_attachment_folder] Source file does not exist: ' . $source_file_path);
            return false;
        }

        // Sanitize filename
        $safe_filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($filename));
        if (empty($safe_filename)) {
            $safe_filename = 'file_' . time();
        }

        // Use application folder: application/tms_modules/Attachment_TMS/{folder}/{ML_ID}/{REVISION}/
        $target_dir = APPPATH . 'tms_modules/Attachment_TMS/' . $folder_name . '/' . (int)$ml_id . '/' . (int)$revision . '/';

        log_message('debug', '[Tool_bom_engin::_move_file_to_attachment_folder] Attempting to create/move to: ' . $target_dir);

        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            $created = @mkdir($target_dir, 0755, true);
            if (!$created) {
                $error = error_get_last();
                log_message('error', '[Tool_bom_engin::_move_file_to_attachment_folder] Cannot create directory: ' . $target_dir . '. Error: ' . ($error ? $error['message'] : 'Unknown'));
                return false;
            }
        }

        $target_file = $target_dir . $safe_filename;

        // Move file
        if (@rename($source_file_path, $target_file)) {
            log_message('info', '[Tool_bom_engin::_move_file_to_attachment_folder] File successfully moved to: ' . $target_file);
            return true;
        } else {
            // Try copy if rename fails
            if (@copy($source_file_path, $target_file)) {
                @unlink($source_file_path);
                log_message('info', '[Tool_bom_engin::_move_file_to_attachment_folder] File successfully copied to: ' . $target_file);
                return true;
            } else {
                $error = error_get_last();
                log_message('error', '[Tool_bom_engin::_move_file_to_attachment_folder] Cannot move file to: ' . $target_file . '. Error: ' . ($error ? $error['message'] : 'Unknown'));
                return false;
            }
        }
    }
}