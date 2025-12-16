<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller alternatif untuk list Tool Drawing menggunakan struktur DB lama (struktur-tms.sql)
 * View: index_tool_draw_engin.php
 */
class Tool_draw_engin extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('session', 'form_validation'));
        
        // Capture current user id/username for later use (fallback to SYSTEM)
        $username_from_session = $this->session->userdata('username');
        $this->uid = (string) ($username_from_session ?: 'SYSTEM');
        
        $this->load->model('M_tool_draw_engin', 'tool_draw_engin');
        $this->tool_draw_engin->uid = $this->uid;
        
        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        // Don't load all data here - will be loaded via AJAX
        $this->view('index_tool_draw_engin', $data, FALSE);
    }

    /**
     * Server-side processing for DataTables
     */
    public function get_data()
    {
        // Clear any output buffers to ensure clean JSON response
        if (ob_get_level()) {
            ob_clean();
        }
        
        $this->output->set_content_type('application/json', 'UTF-8');

        try {
            // Get DataTables parameters
            $draw = (int)$this->input->post('draw');
            $start = (int)$this->input->post('start');
            $length = (int)$this->input->post('length');
            $search = $this->input->post('search');
            $search_value = isset($search['value']) ? trim($search['value']) : '';
            $columns = $this->input->post('columns');
            $order = $this->input->post('order');
            $order_column = isset($order[0]['column']) ? (int)$order[0]['column'] : 0;
            $order_dir = isset($order[0]['dir']) ? strtoupper($order[0]['dir']) : 'DESC';

            // Get all data (with search if needed)
            $all_data = $this->tool_draw_engin->get_all();
            
            // Ensure all_data is an array
            if (!is_array($all_data)) {
                $all_data = array();
            }

            // Column mapping for search
            $column_map = array(
                0 => 'TD_ID',
                1 => 'TD_PRODUCT_NAME',
                2 => 'TD_OPERATION_NAME',
                3 => 'TD_DRAWING_NO',
                4 => 'TD_TOOL_NAME',
                5 => 'TD_REVISION',
                6 => 'TD_STATUS',
                7 => 'TD_EFFECTIVE_DATE',
                8 => 'TD_MODIFIED_DATE',
                9 => 'TD_MODIFIED_BY'
            );

            // Apply per-column search filters
            $has_column_search = false;
            $column_searches = array();
            if (is_array($columns)) {
                foreach ($columns as $idx => $col) {
                    $col_idx = (int)$idx;
                    if (isset($column_map[$col_idx])) {
                        $col_search = isset($col['search']['value']) ? trim($col['search']['value']) : '';
                        if ($col_search !== '') {
                            $has_column_search = true;
                            $column_searches[$column_map[$col_idx]] = strtolower($col_search);
                        }
                    }
                }
            }

            // Apply search filter (global search or per-column search)
            if ($search_value !== '' || $has_column_search) {
                $filtered_data = array();
                foreach ($all_data as $row) {
                    $match = true;
                    
                    // Global search
                    if ($search_value !== '') {
                        $match_global = false;
                        $search_lower = strtolower($search_value);
                        
                        // Search in all visible columns
                        if (stripos(strtolower(isset($row['TD_ID']) ? (string)$row['TD_ID'] : ''), $search_lower) !== false) $match_global = true;
                        if (stripos(strtolower(isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : ''), $search_lower) !== false) $match_global = true;
                        if (stripos(strtolower(isset($row['TD_OPERATION_NAME']) ? $row['TD_OPERATION_NAME'] : ''), $search_lower) !== false) $match_global = true;
                        if (stripos(strtolower(isset($row['TD_DRAWING_NO']) ? $row['TD_DRAWING_NO'] : ''), $search_lower) !== false) $match_global = true;
                        if (stripos(strtolower(isset($row['TD_TOOL_NAME']) ? $row['TD_TOOL_NAME'] : ''), $search_lower) !== false) $match_global = true;
                        if (stripos(strtolower(isset($row['TD_REVISION']) ? (string)$row['TD_REVISION'] : ''), $search_lower) !== false) $match_global = true;
                        if (stripos(strtolower(isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : ''), $search_lower) !== false) $match_global = true;
                        if (stripos(strtolower(isset($row['TD_MODIFIED_DATE']) ? $row['TD_MODIFIED_DATE'] : ''), $search_lower) !== false) $match_global = true;
                        if (stripos(strtolower(isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : ''), $search_lower) !== false) $match_global = true;
                        
                        $match = $match && $match_global;
                    }
                    
                    // Per-column search
                    if ($has_column_search && $match) {
                        foreach ($column_searches as $col_name => $col_search_value) {
                            $row_value = '';
                            if (isset($row[$col_name])) {
                                if ($col_name === 'TD_STATUS') {
                                    // Special handling for status
                                    $st = (int)$row[$col_name];
                                    if ($st === 2) $row_value = 'active';
                                    elseif ($st === 1) $row_value = 'pending';
                                    else $row_value = 'inactive';
                                } else {
                                    $row_value = strtolower((string)$row[$col_name]);
                                }
                            }
                            if (stripos($row_value, $col_search_value) === false) {
                                $match = false;
                                break;
                            }
                        }
                    }
                    
                    if ($match) {
                        $filtered_data[] = $row;
                    }
                }
                $all_data = $filtered_data;
            }

            $total_records = count($all_data);

            // Apply sorting
            $sort_column_map = array(
                0 => 'TD_ID',
                1 => 'TD_PRODUCT_NAME',
                2 => 'TD_OPERATION_NAME',
                3 => 'TD_DRAWING_NO',
                4 => 'TD_TOOL_NAME',
                5 => 'TD_REVISION',
                6 => 'TD_STATUS',
                7 => 'TD_EFFECTIVE_DATE',
                8 => 'TD_MODIFIED_DATE',
                9 => 'TD_MODIFIED_BY'
            );

            $sort_column = isset($sort_column_map[$order_column]) ? $sort_column_map[$order_column] : 'TD_ID';
            
            usort($all_data, function($a, $b) use ($sort_column, $order_dir) {
                $val_a = isset($a[$sort_column]) ? $a[$sort_column] : '';
                $val_b = isset($b[$sort_column]) ? $b[$sort_column] : '';
                
                // Special handling for date columns
                if (in_array($sort_column, array('TD_EFFECTIVE_DATE', 'TD_MODIFIED_DATE'))) {
                    // Convert date strings to timestamps for proper comparison
                    $time_a = !empty($val_a) ? strtotime($val_a) : 0;
                    $time_b = !empty($val_b) ? strtotime($val_b) : 0;
                    $result = $time_a - $time_b;
                } elseif (is_numeric($val_a) && is_numeric($val_b)) {
                    $result = (int)$val_a - (int)$val_b;
                } else {
                    $result = strcmp((string)$val_a, (string)$val_b);
                }
                
                return $order_dir === 'ASC' ? $result : -$result;
            });

            // Apply pagination
            $paginated_data = array_slice($all_data, $start, $length);

            // Format data for DataTables
            $formatted_data = array();
            foreach ($paginated_data as $row) {
                $status_badge = '<span class="badge badge-secondary">Inactive</span>';
                if (isset($row['TD_STATUS'])) {
                    $st = (int)$row['TD_STATUS'];
                    if ($st === 2 || strtoupper((string)$row['TD_STATUS']) === 'ACTIVE') {
                        $status_badge = '<span class="badge badge-success">Active</span>';
                    } elseif ($st === 1) {
                        $status_badge = '<span class="badge badge-warning">Pending</span>';
                    }
                }

                // Build action buttons HTML
                $edit_url = base_url('Tool_drawing/tool_draw_engin/edit_page/' . (int)$row['TD_ID']);
                $history_url = base_url('Tool_drawing/tool_draw_engin/history_page/' . (int)$row['TD_ID']);
                $drawing_no_escaped = htmlspecialchars(isset($row['TD_DRAWING_NO']) ? $row['TD_DRAWING_NO'] : '', ENT_QUOTES, 'UTF-8');
                
                $action_html = '<div class="action-buttons">' .
                    '<a href="' . $edit_url . '" class="btn btn-secondary btn-sm" title="Edit">Edit</a> ' .
                    '<a href="' . $history_url . '" class="btn btn-warning btn-sm" title="History">Hist</a> ' .
                    '<button class="btn btn-danger btn-sm btn-delete" data-id="' . (int)$row['TD_ID'] . '" data-name="' . $drawing_no_escaped . '">Del</button>' .
                    '</div>';

                // Make Drawing No clickable
                $drawing_no = isset($row['TD_DRAWING_NO']) ? $row['TD_DRAWING_NO'] : '';
                $drawing_no_escaped = htmlspecialchars($drawing_no, ENT_QUOTES, 'UTF-8');
                $drawing_no_html = '';
                if ($drawing_no !== '') {
                    $drawing_no_html = '<a href="javascript:void(0);" class="drawing-no-link" data-id="' . (int)$row['TD_ID'] . '" style="color: #007bff; text-decoration: underline; cursor: pointer;" title="Click to view details">' . $drawing_no_escaped . '</a>';
                } else {
                    $drawing_no_html = $drawing_no_escaped;
                }

                $formatted_data[] = array(
                    (int)$row['TD_ID'],
                    htmlspecialchars(isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_OPERATION_NAME']) ? $row['TD_OPERATION_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    $drawing_no_html,
                    htmlspecialchars(isset($row['TD_TOOL_NAME']) ? $row['TD_TOOL_NAME'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_REVISION']) ? (string)$row['TD_REVISION'] : '0', ENT_QUOTES, 'UTF-8'),
                    $status_badge,
                    htmlspecialchars(isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_MODIFIED_DATE']) ? $row['TD_MODIFIED_DATE'] : '', ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars(isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : '', ENT_QUOTES, 'UTF-8'),
                    $action_html
                );
            }

            $response = array(
                'draw' => $draw,
                'recordsTotal' => $total_records,
                'recordsFiltered' => $total_records,
                'data' => $formatted_data
            );

            $json_response = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            
            if ($json_response === false) {
                log_message('error', '[Tool_draw_engin::get_data] JSON encode error: ' . json_last_error_msg());
                $error_response = array(
                    'draw' => $draw,
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => array(),
                    'error' => 'Error encoding response'
                );
                $this->output->set_output(json_encode($error_response));
            } else {
                $this->output->set_output($json_response);
            }
            
        } catch (Exception $e) {
            log_message('error', '[Tool_draw_engin::get_data] Exception: ' . $e->getMessage());
            $error_response = array(
                'draw' => isset($draw) ? $draw : 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => array(),
                'error' => 'Error loading data. Please check logs.'
            );
            $this->output->set_output(json_encode($error_response));
        }
    }

    /**
     * Halaman add Tool Drawing Engineering
     */
    public function add_page()
    {
        $data = array();
        $data['products'] = $this->tool_draw_engin->get_products();
        $data['operations'] = $this->tool_draw_engin->get_operations();
        $data['tools'] = $this->tool_draw_engin->get_tools();
        $data['materials'] = $this->tool_draw_engin->get_materials();
        $data['makers'] = $this->tool_draw_engin->get_makers();
        $data['machine_groups'] = $this->tool_draw_engin->get_machine_groups();

        $this->view('add_tool_draw_engin', $data, FALSE);
    }

    /**
     * Halaman edit Tool Drawing Engineering
     * @param int $id
     */
    public function edit_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_draw_engin->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        // Resolve tool ID
        $row['TD_TOOL_ID'] = isset($row['TD_TOOL_ID']) ? (int)$row['TD_TOOL_ID'] : null;

        // Get Tool BOM by Product ID
        $tool_bom_list = array();
        if (isset($row['TD_PRODUCT_ID']) && (int)$row['TD_PRODUCT_ID'] > 0) {
            $tool_bom_list = $this->tool_draw_engin->get_tool_bom_by_product_id((int)$row['TD_PRODUCT_ID']);
        }

        $data = array();
        $data['drawing'] = $row;
        $data['products'] = $this->tool_draw_engin->get_products();
        $data['operations'] = $this->tool_draw_engin->get_operations();
        $data['tools'] = $this->tool_draw_engin->get_tools();
        $data['materials'] = $this->tool_draw_engin->get_materials();
        $data['makers'] = $this->tool_draw_engin->get_makers();
        $data['machine_groups'] = $this->tool_draw_engin->get_machine_groups();
        $data['tool_bom_list'] = $tool_bom_list;

        $this->view('edit_tool_draw_engin', $data, FALSE);
    }

    /**
     * Halaman history Tool Drawing Engineering
     * @param int $id
     */
    public function history_page($id = 0)
    {
        $id = (int)$id;
        if ($id <= 0) {
            show_404();
            return;
        }

        $row = $this->tool_draw_engin->get_by_id($id);
        if (!$row) {
            show_404();
            return;
        }

        $history = $this->tool_draw_engin->get_history($id);

        $data = array();
        $data['drawing'] = $row;
        $data['history'] = $history;

        $this->view('history_tool_draw_engin', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT Tool Drawing Engineering (AJAX)
     */
    public function submit_data()
    {
        if (ob_get_level()) ob_clean();
        
        $this->output->set_content_type('application/json');
        $result = array('success' => false, 'message' => '');
        
        try {
            $action = strtoupper($this->input->post('action', TRUE));
            $id     = (int)$this->input->post('TD_ID', TRUE);

            // Validation rules
            $this->form_validation->set_rules('TD_PRODUCT_ID', 'Product ID', 'required|integer');
            $this->form_validation->set_rules('TD_PROCESS_ID', 'Process ID', 'required|integer');
            $this->form_validation->set_rules('TD_DRAWING_NO', 'Tool Draw No', 'required|trim');
            $this->form_validation->set_rules('TD_TOOL_NAME', 'Tool Name', 'trim');
            $this->form_validation->set_rules('TD_REVISION', 'Revision', 'integer');
            $this->form_validation->set_rules('TD_STATUS', 'Status', 'integer');
            $this->form_validation->set_rules('TD_MATERIAL_ID', 'Material ID', 'integer');

            if ($this->form_validation->run() == FALSE) {
                $this->form_validation->set_error_delimiters('', '');
                $result['message'] = validation_errors() ?: 'Data tidak valid.';
                echo json_encode($result);
                return;
            }

            $product_id = (int)$this->input->post('TD_PRODUCT_ID', TRUE);
            $process_id = (int)$this->input->post('TD_PROCESS_ID', TRUE);
            
            // Get Drawing No from form input (user input, not from filename)
            $drawing_no = trim($this->input->post('TD_DRAWING_NO', TRUE));
            if (empty($drawing_no)) {
                $result['message'] = 'Tool Draw No wajib diisi.';
                echo json_encode($result);
                return;
            }
            
            // Handle uploaded file (TD_DRAWING_FILE)
            $uploaded_file_path = null; // Store temporary upload path for later move
            $file_name_for_storage = ''; // Store original filename for file storage
            if (!empty($_FILES) && isset($_FILES['TD_DRAWING_FILE']) && !empty($_FILES['TD_DRAWING_FILE']['name'])) {
                // Temporary upload directory
                $uploadDir = FCPATH . 'tool_drawing/img/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $origName = $_FILES['TD_DRAWING_FILE']['name'];
                $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($origName));
                // Use Drawing No + timestamp + extension for filename
                $fileExt = pathinfo($origName, PATHINFO_EXTENSION);
                $fileName = $drawing_no . '_' . time() . ($fileExt ? '.' . $fileExt : '');
                $target = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['TD_DRAWING_FILE']['tmp_name'], $target)) {
                    $file_name_for_storage = $fileName;
                    $uploaded_file_path = $target; // Store path for later move to proper folder
                } else {
                    $result['message'] = 'Gagal mengunggah file drawing.';
                    echo json_encode($result);
                    return;
                }
            } else {
                // For EDIT action, check if old file exists
                $drawing_no_old = $this->input->post('TD_DRAWING_NO_OLD', TRUE);
                if (!empty($drawing_no_old)) {
                    // Keep using old filename if no new file uploaded
                    $file_name_for_storage = $drawing_no_old;
                }
            }

            // TD_TOOL_NAME from form contains TOOL_ID (select)
            $tool_id = (int)$this->input->post('TD_TOOL_NAME', TRUE);
            $revision = (int)$this->input->post('TD_REVISION', TRUE);
            $status = (int)$this->input->post('TD_STATUS', TRUE);
            $material_id = (int)$this->input->post('TD_MATERIAL_ID', TRUE);
            $maker_id = (int)$this->input->post('TD_MAKER_ID', TRUE);
            $machine_group_id = (int)$this->input->post('TD_MACG_ID', TRUE);
            $effective_date = trim($this->input->post('TD_EFFECTIVE_DATE', TRUE));
            if (empty($effective_date)) {
                $effective_date = null;
            }

            if ($action === 'ADD') {
                if (empty($drawing_no)) {
                    $result['message'] = 'Drawing wajib diunggah.';
                    echo json_encode($result);
                    return;
                }
                $ok = $this->tool_draw_engin->add_data($product_id, $process_id, $drawing_no, $tool_id, $revision, $status, $material_id, $maker_id);
                if ($ok === true) {
                    // Get ML_ID (MLR_ML_ID) from the newly created record
                    $ml_id = $this->_get_ml_id_by_drawing_no($drawing_no);
                    
                    // Force write log immediately
                    error_log('[Tool_draw_engin::submit_data] After add_data - ML_ID: ' . $ml_id . ', Drawing No: ' . $drawing_no . ', Uploaded file path: ' . ($uploaded_file_path ? $uploaded_file_path : 'NULL') . ', File name for storage: ' . ($file_name_for_storage ? $file_name_for_storage : 'NULL'));
                    log_message('debug', '[Tool_draw_engin::submit_data] After add_data - ML_ID: ' . $ml_id . ', Drawing No: ' . $drawing_no . ', Uploaded file path: ' . ($uploaded_file_path ? $uploaded_file_path : 'NULL') . ', File name for storage: ' . ($file_name_for_storage ? $file_name_for_storage : 'NULL'));
                    
                    if ($ml_id > 0 && $uploaded_file_path && file_exists($uploaded_file_path)) {
                        // Use file_name_for_storage if available, otherwise use original filename
                        $target_filename = !empty($file_name_for_storage) ? $file_name_for_storage : basename($uploaded_file_path);
                        
                        error_log('[Tool_draw_engin::submit_data] Attempting to move file. ML_ID: ' . $ml_id . ', Revision: ' . $revision . ', File: ' . $target_filename . ', Source: ' . $uploaded_file_path);
                        
                        // Move file to proper folder: Attachment_TMS/Drawing/{ML_ID}/{REVISION}/
                        $moved = $this->_move_file_to_attachment_folder($uploaded_file_path, $ml_id, $revision, $target_filename);
                        if ($moved) {
                            // Verify file was moved (check application folder only)
                            $expected_path = APPPATH . 'tms_modules/Attachment_TMS/Drawing/' . (int)$ml_id . '/' . (int)$revision . '/' . $target_filename;
                            $file_exists = file_exists($expected_path);
                            
                            error_log('[Tool_draw_engin::submit_data] File move result: ' . ($moved ? 'SUCCESS' : 'FAILED') . ', File exists at target: ' . ($file_exists ? 'YES' : 'NO'));
                            log_message('info', '[Tool_draw_engin::submit_data] File successfully moved to Attachment_TMS folder. ML_ID: ' . $ml_id . ', Revision: ' . $revision . ', File: ' . $target_filename);
                            
                            if (!$file_exists) {
                                error_log('[Tool_draw_engin::submit_data] WARNING: File move reported success but file not found at expected location!');
                            }
                        } else {
                            // Log error but don't fail the transaction
                            error_log('[Tool_draw_engin::submit_data] ERROR: Failed to move file to Attachment_TMS folder. ML_ID: ' . $ml_id . ', Revision: ' . $revision . ', File: ' . $target_filename . ', Source: ' . $uploaded_file_path);
                            log_message('error', '[Tool_draw_engin::submit_data] Failed to move file to Attachment_TMS folder. ML_ID: ' . $ml_id . ', Revision: ' . $revision . ', File: ' . $target_filename . ', Source: ' . $uploaded_file_path);
                        }
                    } else {
                        error_log('[Tool_draw_engin::submit_data] WARNING: Cannot move file - ML_ID: ' . $ml_id . ', Uploaded file path exists: ' . ($uploaded_file_path && file_exists($uploaded_file_path) ? 'YES' : 'NO'));
                        log_message('warning', '[Tool_draw_engin::submit_data] Cannot move file - ML_ID: ' . $ml_id . ', Uploaded file path exists: ' . ($uploaded_file_path && file_exists($uploaded_file_path) ? 'YES' : 'NO'));
                    }
                    
                    $result['success'] = true;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Tool Drawing Engineering berhasil ditambahkan.';
                } else {
                    // Clean up uploaded file if database insert failed
                    if ($uploaded_file_path && file_exists($uploaded_file_path)) {
                        @unlink($uploaded_file_path);
                    }
                    $result['success'] = false;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Gagal menambahkan tool drawing.';
                }
                echo json_encode($result);
                return;
            }

            if ($action === 'EDIT' && $id > 0) {
                $current = $this->tool_draw_engin->get_by_id($id);
                if (!$current) {
                    $result['message'] = 'Data tidak ditemukan.';
                    echo json_encode($result);
                    return;
                }

                if (empty($drawing_no)) {
                    $result['message'] = 'Drawing wajib ada.';
                    echo json_encode($result);
                    return;
                }

                $ok = $this->tool_draw_engin->edit_data_engineering(
                    $id,
                    $product_id,
                    $process_id,
                    $drawing_no,
                    $tool_id,
                    $status,
                    $material_id,
                    $maker_id,
                    $machine_group_id,
                    $effective_date
                );
                if ($ok === true) {
                    $result['success'] = true;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Tool Drawing Engineering berhasil diperbarui.';
                } else {
                    $result['success'] = false;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Gagal memperbarui tool drawing.';
                }
                echo json_encode($result);
                return;
            }

            $result['message'] = 'Parameter action/ID tidak valid.';
            echo json_encode($result);
            return;
            
        } catch (Exception $e) {
            log_message('error', '[Tool_draw_engin::submit_data] Exception: ' . $e->getMessage());
            $result['success'] = false;
            $result['message'] = 'Server error. Cek log untuk detail.';
            echo json_encode($result);
            return;
        }
    }

    /**
     * delete_data: delete tool drawing
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TD_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'TD_ID tidak ditemukan.'));
            return;
        }

        $ok = $this->tool_draw_engin->delete_data($id);
        if ($ok) {
            echo json_encode(array('success' => true, 'message' => $this->tool_draw_engin->messages ?: 'Tool Drawing Engineering berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->tool_draw_engin->messages ?: 'Gagal menghapus tool drawing.'));
        }
    }

    /**
     * get_tool_bom_by_product: Get Tool BOM by Product ID (AJAX)
     */
    public function get_tool_bom_by_product()
    {
        if (ob_get_level()) ob_clean();
        
        $this->output->set_content_type('application/json');

        $product_id = (int)$this->input->post('PRODUCT_ID', TRUE);
        if ($product_id <= 0) {
            $result = array('success' => false, 'message' => 'PRODUCT_ID tidak ditemukan.');
            $this->output->set_output(json_encode($result));
            return;
        }

        $tool_bom_list = $this->tool_draw_engin->get_tool_bom_by_product_id($product_id);
        
        // Get product name for display
        $product_name = '';
        $products = $this->tool_draw_engin->get_products();
        foreach ($products as $p) {
            if ((int)$p['PRODUCT_ID'] === $product_id) {
                $product_name = $p['PRODUCT_NAME'];
                break;
            }
        }

        $result = array(
            'success' => true,
            'data' => $tool_bom_list,
            'product_name' => $product_name
        );
        $this->output->set_output(json_encode($result));
    }

    /**
     * Helper function to build file URL using MLR_ML_ID and MLR_REV (preferred method)
     * @param int $mlr_ml_id MLR_ML_ID from database (used for folder location)
     * @param int $mlr_rev MLR_REV from database
     * @param string $fileIdentifier File identifier from database (MLR_DRAWING or MLR_SKETCH)
     * @param string $type File type ('drawing' or 'sketch')
     * @return string Full URL to access the file
     */
    private function build_file_url_by_mlr($mlr_ml_id, $mlr_rev, $fileIdentifier, $type = 'drawing')
    {
        if ($mlr_ml_id <= 0) {
            return '';
        }
        
        // Determine folder name
        $folder_name = 'Drawing';
        if ($type === 'sketch' || strpos(strtolower($type), 'sketch') !== false) {
            $folder_name = 'Drawing_Sketch';
        }
        
        // Try to get actual folder path to determine correct URL
        // Use mlr_ml_id for folder location
        $path_info = $this->_get_folder_path($mlr_ml_id, $mlr_rev, $folder_name);
        
        if ($path_info) {
            // If filename is provided, use direct URL to file
            if (!empty($fileIdentifier) && trim($fileIdentifier) !== '') {
                $filename = basename(trim($fileIdentifier));
                
                // Always use direct URL if folder is in web root (bypass PHP controller completely)
                if (strpos($path_info['dir'], FCPATH) === 0) {
                    // Direct URL to file in web root - bypasses PHP, no corruption possible
                    return $path_info['url'] . rawurlencode($filename);
                } else {
                    // Folder is in application folder, must use Attachment_TMS controller
                    // Format: Attachment_TMS/{folder}/{MLR_ML_ID}/{MLR_REV}/{filename}
                    return $path_info['url'] . rawurlencode($filename);
                }
            }
            
            // Return the base URL for the folder
            return $path_info['url'];
        }
        
        // Fallback: use Attachment_TMS URL format
        // Format: Attachment_TMS/Drawing/{MLR_ML_ID}/{MLR_REV}/
        $folder_name = 'Drawing';
        if ($type === 'sketch' || strpos(strtolower($type), 'sketch') !== false) {
            $folder_name = 'Drawing_Sketch';
        }
        
        $fileUrl = base_url('Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/' . $mlr_rev . '/');
        
        if (!empty($fileIdentifier) && trim($fileIdentifier) !== '') {
            $filename = basename(trim($fileIdentifier));
            // Use rawurlencode for path (produces %20 instead of + for spaces)
            $fileUrl .= rawurlencode($filename);
        }
        
        return $fileUrl;
    }
    
    /**
     * Helper function to build file URL from database/local (legacy method, uses file identifier)
     * @param string $fileIdentifier File identifier from database (MLR_DRAWING or MLR_SKETCH)
     * @param string $module Module name (default: 'ToolDrawing')
     * @return string Full URL to access the file
     */
    private function build_file_url($fileIdentifier, $module = 'ToolDrawing')
    {
        if (empty($fileIdentifier) || trim($fileIdentifier) === '') {
            return '';
        }
        
        // If fileIdentifier already contains full URL, return as is
        if (strpos($fileIdentifier, 'http://') === 0 || strpos($fileIdentifier, 'https://') === 0) {
            return $fileIdentifier;
        }
        
        // Use Attachment_TMS endpoint to serve file from filesystem
        // Format: base_url('Attachment_TMS/serve_file')?id={identifier}&type={drawing|sketch}
        $fileUrl = base_url('Attachment_TMS/serve_file') . '?id=' . urlencode($fileIdentifier);
        
        // Add type parameter based on module
        // 'sketch' or contains 'sketch' -> type=sketch, otherwise type=drawing
        if (strpos(strtolower($module), 'sketch') !== false) {
            $fileUrl .= '&type=sketch';
        } else {
            $fileUrl .= '&type=drawing';
        }
        
        return $fileUrl;
    }

    /**
     * serve_file: Serve file from filesystem based on filename stored in database
     * This method retrieves filename from database and serves file from filesystem
     * @param string $id File identifier/name from MLR_DRAWING or MLR_SKETCH
     * @param string $type File type (drawing or sketch)
     */
    /**
     * Get folder path for MLR_ML_ID and MLR_REV
     * Tries multiple possible locations
     * @param int $mlr_ml_id MLR_ML_ID (used for folder location)
     * @param int $mlr_rev MLR_REV
     * @param string $folder_name
     * @return array Array with 'dir_path' and 'base_url' if found, null otherwise
     */
    private function _get_folder_path($mlr_ml_id, $mlr_rev, $folder_name)
    {
        // Get base URL without trailing slash for direct file access
        $base_url = rtrim(base_url(), '/');
        
        // Try multiple possible paths
        $possible_paths = array(
            // Path 1: Web root Attachment_TMS (most common for direct access)
            // Use direct URL that bypasses CodeIgniter routing completely
            array(
                'dir' => FCPATH . 'Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/' . $mlr_rev . '/',
                'url' => $base_url . '/Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/' . $mlr_rev . '/'
            ),
            // Path 2: Application folder tms_modules (must use controller)
            array(
                'dir' => APPPATH . 'tms_modules/Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/' . $mlr_rev . '/',
                'url' => base_url('Attachment_TMS/index') . '?folder=' . rawurlencode($folder_name) . '&mlr_ml_id=' . (int)$mlr_ml_id . '&mlr_rev=' . (int)$mlr_rev . '&filename='
            ),
            // Path 3: Try without revision subfolder (some files might be directly in MLR_ML_ID folder)
            array(
                'dir' => FCPATH . 'Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/',
                'url' => $base_url . '/Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/'
            ),
            array(
                'dir' => APPPATH . 'tms_modules/Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/',
                'url' => base_url('Attachment_TMS/index') . '?folder=' . rawurlencode($folder_name) . '&mlr_ml_id=' . (int)$mlr_ml_id . '&mlr_rev=0&filename='
            )
        );
        
        foreach ($possible_paths as $path_info) {
            if (is_dir($path_info['dir'])) {
                log_message('debug', '[serve_file_by_mlr] Found folder at: ' . $path_info['dir']);
                return $path_info;
            }
        }
        
        return null;
    }
    
    /**
     * List all files in folder by MLR_ID and MLR_REV
     * Returns JSON list of files
     */
    public function list_files_by_mlr()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        $this->output->set_content_type('application/json', 'UTF-8');
        
        $mlr_id = (int)$this->input->get('mlr_id', TRUE);
        $mlr_rev = (int)$this->input->get('mlr_rev', TRUE);
        $file_type = $this->input->get('type', TRUE);
        
        if ($mlr_id <= 0) {
            $result = array('success' => false, 'message' => 'Invalid MLR_ID', 'files' => array());
            $this->output->set_output(json_encode($result));
            return;
        }
        
        // Get MLR_ML_ID from database using MLR_ID
        $db_tms = $this->load->database('tms_NEW', true);
        if (!$db_tms) {
            $result = array('success' => false, 'message' => 'Database connection failed', 'files' => array());
            $this->output->set_output(json_encode($result));
            return;
        }
        
        $sql = "SELECT MLR_ML_ID FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV WHERE MLR_ID = ?";
        $q = $db_tms->query($sql, array($mlr_id));
        $mlr_ml_id = 0;
        if ($q && $q->num_rows() > 0) {
            $mlr_ml_id = (int)$q->row()->MLR_ML_ID;
        }
        
        if ($mlr_ml_id <= 0) {
            $result = array('success' => false, 'message' => 'MLR_ML_ID not found for MLR_ID=' . $mlr_id, 'files' => array());
            $this->output->set_output(json_encode($result));
            return;
        }
        
        // Determine folder name based on file type
        $folder_name = 'Drawing';
        if ($file_type === 'sketch' || strpos(strtolower($file_type), 'sketch') !== false) {
            $folder_name = 'Drawing_Sketch';
        }
        
        // Get folder path using MLR_ML_ID
        $path_info = $this->_get_folder_path($mlr_ml_id, $mlr_rev, $folder_name);
        
        if (!$path_info) {
            $result = array(
                'success' => false, 
                'message' => 'Folder not found for MLR_ID=' . $mlr_id . ', MLR_REV=' . $mlr_rev,
                'files' => array()
            );
            $this->output->set_output(json_encode($result));
            return;
        }
        
        // Read files from directory
        $files = @scandir($path_info['dir']);
        $file_list = array();
        
        if ($files) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === 'cb.txt') {
                    continue;
                }
                
                $file_path = $path_info['dir'] . $file;
                if (is_file($file_path)) {
                    // Use rawurlencode for path (produces %20 instead of + for spaces)
                    $file_url = $path_info['url'] . rawurlencode($file);
                    $file_size = filesize($file_path);
                    $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    
                    $file_list[] = array(
                        'name' => $file,
                        'url' => $file_url,
                        'size' => $file_size,
                        'size_formatted' => $this->_format_file_size($file_size),
                        'extension' => $file_ext,
                        'is_image' => in_array($file_ext, array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp')),
                        'is_pdf' => ($file_ext === 'pdf')
                    );
                }
            }
        }
        
        $result = array(
            'success' => true,
            'message' => 'Found ' . count($file_list) . ' file(s)',
            'mlr_id' => $mlr_id,
            'mlr_rev' => $mlr_rev,
            'folder_path' => $path_info['dir'],
            'folder_url' => $path_info['url'],
            'files' => $file_list
        );
        
        $this->output->set_output(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * Format file size to human readable format
     */
    private function _format_file_size($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
    
    /**
     * Serve file using MLR_ID and MLR_REV directly (preferred method)
     * This is more reliable than searching by file identifier
     * Now supports reading folder contents and serving files
     */
    public function serve_file_by_mlr()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        $mlr_id = (int)$this->input->get('mlr_id', TRUE);
        $mlr_rev = (int)$this->input->get('mlr_rev', TRUE);
        $file_type = $this->input->get('type', TRUE);
        $filename = $this->input->get('filename', TRUE); // Optional: specific filename to serve
        
        if ($mlr_id <= 0) {
            log_message('error', '[serve_file_by_mlr] Invalid MLR_ID: ' . $mlr_id);
            show_404();
            return;
        }
        
        // Get MLR_ML_ID from database using MLR_ID
        $db_tms = $this->load->database('tms_NEW', true);
        if (!$db_tms) {
            log_message('error', '[serve_file_by_mlr] Failed to load database tms_NEW');
            show_404();
            return;
        }
        
        $sql = "SELECT MLR_ML_ID FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV WHERE MLR_ID = ?";
        $q = $db_tms->query($sql, array($mlr_id));
        $mlr_ml_id = 0;
        if ($q && $q->num_rows() > 0) {
            $mlr_ml_id = (int)$q->row()->MLR_ML_ID;
        }
        
        if ($mlr_ml_id <= 0) {
            log_message('error', '[serve_file_by_mlr] MLR_ML_ID not found for MLR_ID=' . $mlr_id);
            show_404();
            return;
        }
        
        // Determine folder name based on file type
        $folder_name = 'Drawing';
        if ($file_type === 'sketch' || strpos(strtolower($file_type), 'sketch') !== false) {
            $folder_name = 'Drawing_Sketch';
        }
        
        // Get folder path using MLR_ML_ID
        $path_info = $this->_get_folder_path($mlr_ml_id, $mlr_rev, $folder_name);
        
        if (!$path_info) {
            log_message('error', '[serve_file_by_mlr] Folder not found for MLR_ML_ID=' . $mlr_ml_id . ', MLR_REV=' . $mlr_rev);
            show_404();
            return;
        }
        
        // If filename is provided, serve that specific file
        if (!empty($filename)) {
            $filename = basename($filename); // Prevent directory traversal
            $file_path = $path_info['dir'] . $filename;
            
            if (file_exists($file_path) && is_file($file_path)) {
                log_message('debug', '[serve_file_by_mlr] Serving file: ' . $file_path);
                $this->_output_file($file_path);
                return;
            } else {
                log_message('error', '[serve_file_by_mlr] File not found: ' . $file_path);
                show_404();
                return;
            }
        }
        
        // If no filename provided, try to get filename from database
        $db_tms = $this->load->database('tms_NEW', true);
        if (!$db_tms) {
            log_message('error', '[serve_file_by_mlr] Failed to load database tms_NEW');
            show_404();
            return;
        }
        
        // Determine which column to query based on file type
        $column_name = 'MLR_DRAWING';
        if ($file_type === 'sketch' || strpos(strtolower($file_type), 'sketch') !== false) {
            $column_name = 'MLR_SKETCH';
        }
        
        // Get filename from database using MLR_ID and MLR_REV
        $sql = "
            SELECT TOP 1
                rev." . $column_name . " AS FILE_NAME
            FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
            WHERE rev.MLR_ID = ? AND rev.MLR_REV = ?
        ";
        
        $q = $db_tms->query($sql, array($mlr_id, $mlr_rev));
        
        if (!$q || $q->num_rows() == 0) {
            log_message('error', '[serve_file_by_mlr] Record not found: MLR_ID=' . $mlr_id . ', MLR_REV=' . $mlr_rev);
            show_404();
            return;
        }
        
        $row = $q->row_array();
        $file_name = isset($row['FILE_NAME']) ? trim($row['FILE_NAME']) : '';
        
        // If database has filename, try to serve it
        if (!empty($file_name)) {
            $file_name = basename($file_name);
            $file_path = $path_info['dir'] . $file_name;
            
            if (file_exists($file_path) && is_file($file_path)) {
                log_message('debug', '[serve_file_by_mlr] File found from database, serving: ' . $file_path);
                $this->_output_file($file_path);
                return;
            }
        }
        
        // If database filename doesn't exist, try to find first file in folder
        $files = @scandir($path_info['dir']);
        if ($files) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === 'cb.txt') {
                    continue;
                }
                
                $file_path = $path_info['dir'] . $file;
                if (is_file($file_path)) {
                    log_message('debug', '[serve_file_by_mlr] Serving first file found in folder: ' . $file_path);
                    $this->_output_file($file_path);
                    return;
                }
            }
        }
        
        // No file found
        log_message('error', '[serve_file_by_mlr] No file found in folder: ' . $path_info['dir']);
        show_404();
    }
    
    /**
     * Serve file using file identifier (legacy method)
     * This method searches database by filename, which can be unreliable
     */
    public function serve_file()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        $this->output->set_content_type('application/octet-stream'); // Default MIME type
        
        $file_id = $this->input->get('id', TRUE);
        $file_type = $this->input->get('type', TRUE);
        
        if (empty($file_id)) {
            show_404();
            return;
        }
        
        // URL decode the file identifier (handle + as space and % encoding)
        // Note: CodeIgniter's input->get() already does urldecode, but we do it again to be sure
        $file_id_original = $file_id;
        $file_id = urldecode($file_id);
        $file_id = str_replace('+', ' ', $file_id); // Handle + as space
        $file_id = trim($file_id);
        
        // Log for debugging
        log_message('debug', '[serve_file] Original file_id: ' . $file_id_original);
        log_message('debug', '[serve_file] Decoded file_id: ' . $file_id);
        log_message('debug', '[serve_file] File type: ' . $file_type);
        
        // Load database connection
        $db_tms = $this->load->database('tms_NEW', true);
        if (!$db_tms) {
            show_404();
            return;
        }
        
        // Determine which column to query based on file type
        // Note: All files are stored in 'Drawing' folder, not 'Drawing_Sketch'
        $column_name = 'MLR_DRAWING';
        $folder_name = 'Drawing';
        if ($file_type === 'sketch' || strpos(strtolower($file_type), 'sketch') !== false) {
            $column_name = 'MLR_SKETCH';  // Column name for database query
            // folder_name remains 'Drawing' - all files go to Drawing folder
        }
        
        // Get MLR_ID, MLR_REV, and filename from database
        // MLR_DRAWING and MLR_SKETCH store filename (varchar(50)), not BLOB
        // File structure: Attachment_TMS/Drawing/{MLR_ML_ID}/{MLR_REV}/{filename}
        // Note: All files (both drawing and sketch) are stored in 'Drawing' folder
        
        // Get basename for partial matching
        $file_basename = basename($file_id);
        
        // Try multiple search strategies
        $found = false;
        $row = null;
        
        // Strategy 1: Exact match (trimmed, case sensitive)
        $sql = "
            SELECT TOP 1
                rev.MLR_ID,
                rev.MLR_REV,
                rev." . $column_name . " AS FILE_NAME
            FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
            WHERE LTRIM(RTRIM(rev." . $column_name . ")) = ?
        ";
        $q = $db_tms->query($sql, array($file_id));
        if ($q && $q->num_rows() > 0) {
            $found = true;
            $row = $q->row_array();
        }
        
        // Strategy 2: Case-insensitive match
        if (!$found) {
            $sql = "
                SELECT TOP 1
                    rev.MLR_ID,
                    rev.MLR_REV,
                    rev." . $column_name . " AS FILE_NAME
                FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
                WHERE LOWER(LTRIM(RTRIM(rev." . $column_name . "))) = LOWER(?)
            ";
            $q = $db_tms->query($sql, array($file_id));
            if ($q && $q->num_rows() > 0) {
                $found = true;
                $row = $q->row_array();
            }
        }
        
        // Strategy 3: Partial match (filename only, ignore path if any)
        if (!$found) {
            $sql = "
                SELECT TOP 1
                    rev.MLR_ID,
                    rev.MLR_REV,
                    rev." . $column_name . " AS FILE_NAME
                FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
                WHERE LOWER(LTRIM(RTRIM(rev." . $column_name . "))) = LOWER(?)
            ";
            $q = $db_tms->query($sql, array($file_basename));
            if ($q && $q->num_rows() > 0) {
                $found = true;
                $row = $q->row_array();
            }
        }
        
        if (!$found || !$row) {
            // Log for debugging - also try to find similar filenames
            log_message('error', '[serve_file] File identifier not found in database: ' . $file_id . ' (type: ' . $file_type . ')');
            
            // Try to find similar filenames for debugging
            $debug_sql = "
                SELECT TOP 5
                    rev.MLR_ID,
                    rev.MLR_REV,
                    rev." . $column_name . " AS FILE_NAME,
                    LEN(rev." . $column_name . ") AS FILE_NAME_LENGTH
                FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
                WHERE rev." . $column_name . " IS NOT NULL 
                    AND rev." . $column_name . " <> ''
                    AND rev." . $column_name . " LIKE ?
                ORDER BY rev.MLR_ID DESC
            ";
            $debug_q = $db_tms->query($debug_sql, array('%' . $file_basename . '%'));
            if ($debug_q && $debug_q->num_rows() > 0) {
                $similar = $debug_q->result_array();
                log_message('error', '[serve_file] Similar filenames found: ' . json_encode($similar));
            }
            
            show_404();
            return;
        }
        
        // $row already set from successful query above
        $mlr_id = isset($row['MLR_ID']) ? (int)$row['MLR_ID'] : 0;
        $mlr_rev = isset($row['MLR_REV']) ? (int)$row['MLR_REV'] : 0;
        $file_name = isset($row['FILE_NAME']) ? trim($row['FILE_NAME']) : '';
        
        if (empty($file_name) || $mlr_id <= 0) {
            log_message('error', '[serve_file] Empty file_name or invalid MLR_ID. MLR_ID: ' . $mlr_id . ', FILE_NAME: ' . $file_name);
            show_404();
            return;
        }
        
        // Get MLR_ML_ID from database using MLR_ID
        $mlr_ml_id = 0;
        $ml_sql = "SELECT MLR_ML_ID FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV WHERE MLR_ID = ?";
        $ml_q = $db_tms->query($ml_sql, array($mlr_id));
        if ($ml_q && $ml_q->num_rows() > 0) {
            $mlr_ml_id = (int)$ml_q->row()->MLR_ML_ID;
        }
        
        if ($mlr_ml_id <= 0) {
            log_message('error', '[serve_file] MLR_ML_ID not found for MLR_ID: ' . $mlr_id);
            show_404();
            return;
        }
        
        // Clean filename to prevent directory traversal
        $file_name = basename($file_name);
        
        // Log successful database lookup
        log_message('debug', '[serve_file] Found in database - MLR_ID: ' . $mlr_id . ', MLR_ML_ID: ' . $mlr_ml_id . ', MLR_REV: ' . $mlr_rev . ', FILE_NAME: ' . $file_name);
        
        // File location: Attachment_TMS/{Drawing|Drawing_Sketch}/{MLR_ML_ID}/{MLR_REV}/{filename}
        $file_path = APPPATH . 'tms_modules/Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/' . $mlr_rev . '/' . $file_name;
        
        // Log for debugging (can be removed in production)
        log_message('debug', '[serve_file] Looking for file: ' . $file_path);
        log_message('debug', '[serve_file] MLR_ID: ' . $mlr_id . ', MLR_REV: ' . $mlr_rev . ', File: ' . $file_name);
        
        if (file_exists($file_path) && is_file($file_path)) {
            // Serve file from Attachment_TMS folder
            log_message('debug', '[serve_file] File found, serving: ' . $file_path);
            $this->_output_file($file_path);
            return;
        }
        
        // If file not found, log and return 404
        log_message('error', '[serve_file] File NOT found at path: ' . $file_path);
        log_message('error', '[serve_file] Searched for: MLR_ID=' . $mlr_id . ', MLR_ML_ID=' . $mlr_ml_id . ', MLR_REV=' . $mlr_rev . ', File=' . $file_name);
        log_message('error', '[serve_file] Folder structure: Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/' . $mlr_rev . '/');
        
        // Check if directory exists
        $dir_path = APPPATH . 'tms_modules/Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/' . $mlr_rev . '/';
        if (!is_dir($dir_path)) {
            log_message('error', '[serve_file] Directory does not exist: ' . $dir_path);
        } else {
            log_message('error', '[serve_file] Directory exists but file not found. Files in directory: ' . implode(', ', scandir($dir_path)));
        }
        
        show_404();
    }
    
    /**
     * Output file from filesystem with proper headers
     * @param string $file_path Full path to file
     */
    private function _output_file($file_path)
    {
        if (!file_exists($file_path) || !is_file($file_path)) {
            show_404();
            return;
        }
        
        // Get MIME type
        $mime_type = $this->_get_mime_type_from_filename($file_path);
        
        // Try to detect MIME type from file content if available
        if (function_exists('mime_content_type')) {
            $detected_mime = @mime_content_type($file_path);
            if ($detected_mime) {
                $mime_type = $detected_mime;
            }
        }
        
        $file_size = filesize($file_path);
        $file_name = basename($file_path);
        
        // Clean output buffer
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set headers
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . $file_size);
        header('Content-Disposition: inline; filename="' . $file_name . '"');
        header('Cache-Control: private, max-age=3600');
        header('Pragma: cache');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
        
        // Output file
        readfile($file_path);
        exit;
    }
    
    /**
     * Get MIME type from filename
     * @param string $filename
     * @return string MIME type
     */
    private function _get_mime_type_from_filename($filename)
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime_types = array(
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        return isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';
    }

    /**
     * get_detail: Get drawing detail by ID (AJAX) for modal popup
     */
    public function get_detail()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        $this->output->set_content_type('application/json', 'UTF-8');

        $id = (int)$this->input->post('TD_ID', TRUE);
        if ($id <= 0) {
            $result = array('success' => false, 'message' => 'TD_ID tidak ditemukan.');
            $this->output->set_output(json_encode($result));
            return;
        }

        $row = $this->tool_draw_engin->get_by_id($id);
        if (!$row) {
            $result = array('success' => false, 'message' => 'Data tidak ditemukan.');
            $this->output->set_output(json_encode($result));
            return;
        }

        // Format status
        $status_text = 'Inactive';
        if (isset($row['TD_STATUS'])) {
            $st = (int)$row['TD_STATUS'];
            if ($st === 2 || strtoupper((string)$row['TD_STATUS']) === 'ACTIVE') {
                $status_text = 'Active';
            } elseif ($st === 1) {
                $status_text = 'Pending';
            }
        }

        // Build file URLs using MLR_ML_ID and MLR_REV directly (more reliable)
        // Use MLR_ML_ID (TD_ML_ID) for folder location, not MLR_ID (TD_ID)
        $mlr_ml_id = isset($row['TD_ML_ID']) ? (int)$row['TD_ML_ID'] : 0;
        $mlr_rev = isset($row['TD_REVISION']) ? (int)$row['TD_REVISION'] : 0;
        $drawing_file_id = isset($row['TD_DRAWING_FILE']) ? $row['TD_DRAWING_FILE'] : '';
        $sketch_file_id = isset($row['TD_SKETCH_FILE']) ? $row['TD_SKETCH_FILE'] : '';
        
        // Use MLR_ML_ID and MLR_REV to build URL directly (more reliable than file identifier)
        $drawing_file_url = $this->build_file_url_by_mlr($mlr_ml_id, $mlr_rev, $drawing_file_id, 'drawing');
        $sketch_file_url = $this->build_file_url_by_mlr($mlr_ml_id, $mlr_rev, $sketch_file_id, 'sketch');
        
        // Get list of all files in drawing folder
        $drawing_files = array();
        $drawing_path_info = $this->_get_folder_path($mlr_ml_id, $mlr_rev, 'Drawing');
        if ($drawing_path_info) {
            $files = @scandir($drawing_path_info['dir']);
            if ($files) {
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..' || $file === 'cb.txt') {
                        continue;
                    }
                    $file_path = $drawing_path_info['dir'] . $file;
                    if (is_file($file_path)) {
                        // Always use direct URL if folder is in web root (bypasses PHP/CodeIgniter completely)
                        if (strpos($drawing_path_info['dir'], FCPATH) === 0) {
                            // File is in web root, use DIRECT URL - bypasses all PHP/CodeIgniter routing
                            // This URL goes directly to the file without any PHP processing
                            $file_url = $drawing_path_info['url'] . rawurlencode($file);
                        } else {
                            // File is in application folder, must use Attachment_TMS controller
                            // Append filename to the URL that already has query parameters
                            if (strpos($drawing_path_info['url'], '?') !== false) {
                                $file_url = $drawing_path_info['url'] . rawurlencode($file);
                            } else {
                                $file_url = $this->build_file_url_by_mlr($mlr_ml_id, $mlr_rev, $file, 'drawing');
                            }
                        }
                        $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $drawing_files[] = array(
                            'name' => $file,
                            'url' => $file_url,
                            'size' => filesize($file_path),
                            'extension' => $file_ext,
                            'is_image' => in_array($file_ext, array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp')),
                            'is_pdf' => ($file_ext === 'pdf')
                        );
                    }
                }
            }
        }
        
        // Get list of all files in sketch folder
        $sketch_files = array();
        $sketch_path_info = $this->_get_folder_path($mlr_ml_id, $mlr_rev, 'Drawing');
        if ($sketch_path_info) {
            $files = @scandir($sketch_path_info['dir']);
            if ($files) {
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..' || $file === 'cb.txt') {
                        continue;
                    }
                    $file_path = $sketch_path_info['dir'] . $file;
                    if (is_file($file_path)) {
                        $file_url = $this->build_file_url_by_mlr($mlr_ml_id, $mlr_rev, $file, 'sketch');
                        $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $sketch_files[] = array(
                            'name' => $file,
                            'url' => $file_url,
                            'size' => filesize($file_path),
                            'extension' => $file_ext,
                            'is_image' => in_array($file_ext, array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp')),
                            'is_pdf' => ($file_ext === 'pdf')
                        );
                    }
                }
            }
        }

        // Prepare response data
        $result = array(
            'success' => true,
            'data' => array(
                'TD_ID' => isset($row['TD_ID']) ? (int)$row['TD_ID'] : 0,
                'TD_DRAWING_NO' => isset($row['TD_DRAWING_NO']) ? $row['TD_DRAWING_NO'] : '',
                'TD_PRODUCT_NAME' => isset($row['TD_PRODUCT_NAME']) ? $row['TD_PRODUCT_NAME'] : '',
                'TD_OPERATION_NAME' => isset($row['TD_OPERATION_NAME']) ? $row['TD_OPERATION_NAME'] : '',
                'TD_TOOL_NAME' => isset($row['TD_TOOL_NAME']) ? $row['TD_TOOL_NAME'] : '',
                'TD_REVISION' => isset($row['TD_REVISION']) ? (string)$row['TD_REVISION'] : '0',
                'TD_STATUS' => $status_text,
                'TD_EFFECTIVE_DATE' => isset($row['TD_EFFECTIVE_DATE']) ? $row['TD_EFFECTIVE_DATE'] : '',
                'TD_MODIFIED_DATE' => isset($row['TD_MODIFIED_DATE']) ? $row['TD_MODIFIED_DATE'] : '',
                'TD_MODIFIED_BY' => isset($row['TD_MODIFIED_BY']) ? $row['TD_MODIFIED_BY'] : '',
                'TD_MATERIAL_NAME' => isset($row['TD_MATERIAL_NAME']) ? $row['TD_MATERIAL_NAME'] : '',
                'TD_MAKER_NAME' => isset($row['TD_MAKER_NAME']) ? $row['TD_MAKER_NAME'] : '',
                'TD_MAC_NAME' => isset($row['TD_MAC_NAME']) ? $row['TD_MAC_NAME'] : '',
                'TD_DRAWING_FILE' => $drawing_file_id,
                'TD_DRAWING_FILE_URL' => $drawing_file_url,
                'TD_DRAWING_FILES' => $drawing_files,
                'TD_SKETCH_FILE' => $sketch_file_id,
                'TD_SKETCH_FILE_URL' => $sketch_file_url,
                'TD_SKETCH_FILES' => $sketch_files
            )
        );

        $this->output->set_output(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * test_serve_file: Test endpoint to check if serve_file can find a specific file
     * Usage: Tool_engineering/tool_draw_engin/test_serve_file?id=Screenshot (2).png&type=drawing
     */
    public function test_serve_file()
    {
        $file_id = $this->input->get('id', TRUE);
        $file_type = $this->input->get('type', TRUE);
        
        if (empty($file_id)) {
            echo "Error: No file ID provided. Usage: ?id=filename&type=drawing<br>";
            return;
        }
        
        echo "<h3>Testing serve_file for: " . htmlspecialchars($file_id) . " (type: " . htmlspecialchars($file_type) . ")</h3>";
        
        // Load database connection
        $db_tms = $this->load->database('tms_NEW', true);
        if (!$db_tms) {
            echo "Error: Database connection failed<br>";
            return;
        }
        
        // Determine column
        $column_name = 'MLR_DRAWING';
        $folder_name = 'Drawing';
        if ($file_type === 'sketch' || strpos(strtolower($file_type), 'sketch') !== false) {
            $column_name = 'MLR_SKETCH';
            $folder_name = 'Drawing_Sketch';
        }
        
        // URL decode
        $file_id_decoded = urldecode($file_id);
        $file_id_decoded = str_replace('+', ' ', $file_id_decoded);
        $file_id_decoded = trim($file_id_decoded);
        
        echo "<strong>Original:</strong> " . htmlspecialchars($file_id) . "<br>";
        echo "<strong>Decoded:</strong> " . htmlspecialchars($file_id_decoded) . "<br><br>";
        
        // Query database
        $sql = "
            SELECT TOP 5
                rev.MLR_ID,
                rev.MLR_REV,
                rev." . $column_name . " AS FILE_NAME,
                LEN(rev." . $column_name . ") AS FILE_NAME_LENGTH
            FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
            WHERE LTRIM(RTRIM(rev." . $column_name . ")) = ?
        ";
        
        $q = $db_tms->query($sql, array($file_id_decoded));
        
        echo "<h4>Query Result (Exact Match):</h4>";
        if ($q && $q->num_rows() > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>MLR_ID</th><th>MLR_REV</th><th>FILE_NAME</th><th>Length</th><th>Expected Path</th></tr>";
            foreach ($q->result_array() as $r) {
                $mlr_id = (int)$r['MLR_ID'];
                $mlr_rev = (int)$r['MLR_REV'];
                $fname = $r['FILE_NAME'];
                $expected_path = APPPATH . 'tms_modules/Attachment_TMS/' . $folder_name . '/' . $mlr_id . '/' . $mlr_rev . '/' . basename($fname);
                $exists = file_exists($expected_path) ? 'YES' : 'NO';
                echo "<tr>";
                echo "<td>" . $mlr_id . "</td>";
                echo "<td>" . $mlr_rev . "</td>";
                echo "<td>" . htmlspecialchars($fname) . "</td>";
                echo "<td>" . $r['FILE_NAME_LENGTH'] . "</td>";
                echo "<td>" . htmlspecialchars($expected_path) . " <strong>(" . $exists . ")</strong></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No exact match found.<br>";
            
            // Try case-insensitive
            $sql2 = "
                SELECT TOP 5
                    rev.MLR_ID,
                    rev.MLR_REV,
                    rev." . $column_name . " AS FILE_NAME
                FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
                WHERE LOWER(LTRIM(RTRIM(rev." . $column_name . "))) = LOWER(?)
            ";
            $q2 = $db_tms->query($sql2, array($file_id_decoded));
            if ($q2 && $q2->num_rows() > 0) {
                echo "<h4>Query Result (Case-Insensitive):</h4>";
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>MLR_ID</th><th>MLR_REV</th><th>FILE_NAME</th></tr>";
                foreach ($q2->result_array() as $r) {
                    echo "<tr><td>" . $r['MLR_ID'] . "</td><td>" . $r['MLR_REV'] . "</td><td>" . htmlspecialchars($r['FILE_NAME']) . "</td></tr>";
                }
                echo "</table>";
            } else {
                echo "No case-insensitive match found either.<br>";
            }
        }
        
        // Show similar filenames
        $basename = basename($file_id_decoded);
        $sql3 = "
            SELECT TOP 10
                rev.MLR_ID,
                rev.MLR_REV,
                rev." . $column_name . " AS FILE_NAME
            FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
            WHERE rev." . $column_name . " IS NOT NULL 
                AND rev." . $column_name . " <> ''
                AND rev." . $column_name . " LIKE ?
            ORDER BY rev.MLR_ID DESC
        ";
        $q3 = $db_tms->query($sql3, array('%' . $basename . '%'));
        if ($q3 && $q3->num_rows() > 0) {
            echo "<h4>Similar Filenames (contains '" . htmlspecialchars($basename) . "'):</h4>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>MLR_ID</th><th>MLR_REV</th><th>FILE_NAME</th></tr>";
            foreach ($q3->result_array() as $r) {
                echo "<tr><td>" . $r['MLR_ID'] . "</td><td>" . $r['MLR_REV'] . "</td><td>" . htmlspecialchars($r['FILE_NAME']) . "</td></tr>";
            }
            echo "</table>";
        }
    }

    /**
     * debug_file_data: Debug endpoint to see file identifiers from database
     * This helps understand the format of MLR_DRAWING and MLR_SKETCH data
     */
    public function debug_file_data()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        
        $this->output->set_content_type('application/json', 'UTF-8');

        // Load database connection
        $db_tms = $this->load->database('tms_NEW', true);
        if (!$db_tms) {
            $result = array(
                'success' => false,
                'message' => 'Failed to connect to database',
                'data' => array()
            );
            $this->output->set_output(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            return;
        }

        // Get sample data with file identifiers
        $sql = "
            SELECT TOP 20
                rev.MLR_ID,
                ml.ML_TOOL_DRAW_NO AS Drawing_No,
                rev.MLR_REV AS Revision,
                rev.MLR_DRAWING AS Drawing_File_Identifier,
                rev.MLR_SKETCH AS Sketch_File_Identifier,
                LEN(rev.MLR_DRAWING) AS Drawing_Length,
                LEN(rev.MLR_SKETCH) AS Sketch_Length
            FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
            INNER JOIN TMS_NEW.dbo.TMS_TOOL_MASTER_LIST ml
                ON ml.ML_ID = rev.MLR_ML_ID
            WHERE ml.ML_TYPE = 1
                AND (
                    (rev.MLR_DRAWING IS NOT NULL AND rev.MLR_DRAWING <> '')
                    OR (rev.MLR_SKETCH IS NOT NULL AND rev.MLR_SKETCH <> '')
                )
            ORDER BY rev.MLR_ID DESC
        ";

        $q = $db_tms->query($sql);
        $data = array();
        
        if ($q) {
            $rows = $q->result_array();
            foreach ($rows as $row) {
                $drawing_id = isset($row['Drawing_File_Identifier']) ? $row['Drawing_File_Identifier'] : '';
                $sketch_id = isset($row['Sketch_File_Identifier']) ? $row['Sketch_File_Identifier'] : '';
                
                // Build URLs for testing
                $drawing_url = $this->build_file_url($drawing_id, 'drawing');
                $sketch_url = $this->build_file_url($sketch_id, 'sketch');
                
                $data[] = array(
                    'MLR_ID' => (int)$row['MLR_ID'],
                    'Drawing_No' => isset($row['Drawing_No']) ? $row['Drawing_No'] : '',
                    'Revision' => isset($row['Revision']) ? (int)$row['Revision'] : 0,
                    'Drawing_Identifier' => $drawing_id,
                    'Drawing_Length' => isset($row['Drawing_Length']) ? (int)$row['Drawing_Length'] : 0,
                    'Drawing_URL' => $drawing_url,
                    'Sketch_Identifier' => $sketch_id,
                    'Sketch_Length' => isset($row['Sketch_Length']) ? (int)$row['Sketch_Length'] : 0,
                    'Sketch_URL' => $sketch_url,
                    'Drawing_Type' => $this->detect_file_identifier_type($drawing_id),
                    'Sketch_Type' => $this->detect_file_identifier_type($sketch_id)
                );
            }
        }

        $result = array(
            'success' => true,
            'count' => count($data),
            'data' => $data,
            'note' => 'Ini adalah sample data file identifier dari database. Gunakan untuk memahami format data yang tersimpan.'
        );

        $this->output->set_output(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        return;
    }

    /**
     * Helper to detect file identifier type
     */
    private function detect_file_identifier_type($identifier)
    {
        if (empty($identifier)) {
            return 'Empty';
        }
        
        if (strpos($identifier, 'http://') === 0 || strpos($identifier, 'https://') === 0) {
            return 'Full URL';
        }
        
        if (preg_match('/\.(jpg|jpeg|png|gif|bmp|pdf|doc|docx)$/i', $identifier)) {
            return 'Filename with Extension';
        }
        
        if (strpos($identifier, '/') !== false || strpos($identifier, '\\') !== false) {
            return 'Path';
        }
        
        if (preg_match('/[+\/=]/', $identifier)) {
            return 'Encoded/Base64-like';
        }
        
        return 'Plain Identifier';
    }
    
    /**
     * Get ML_ID by drawing_no (ML_TOOL_DRAW_NO)
     * @param string $drawing_no
     * @return int ML_ID or 0 if not found
     */
    private function _get_ml_id_by_drawing_no($drawing_no)
    {
        if (empty($drawing_no)) {
            log_message('error', '[Tool_draw_engin::_get_ml_id_by_drawing_no] Drawing No is empty');
            return 0;
        }
        
        // Load database connection
        $db_tms = $this->load->database('tms_NEW', TRUE);
        
        // Try up to 3 times with small delay (in case of transaction delay)
        $max_retries = 3;
        $retry_delay = 100000; // 100ms in microseconds
        
        for ($i = 0; $i < $max_retries; $i++) {
            $sql = "SELECT ML_ID FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST WHERE ML_TOOL_DRAW_NO = ?";
            $query = $db_tms->query($sql, array($drawing_no));
            
            if ($query && $query->num_rows() > 0) {
                $ml_id = (int)$query->row()->ML_ID;
                log_message('debug', '[Tool_draw_engin::_get_ml_id_by_drawing_no] Found ML_ID: ' . $ml_id . ' for Drawing No: ' . $drawing_no . ' (attempt ' . ($i + 1) . ')');
                return $ml_id;
            }
            
            // Wait before retry (except on last attempt)
            if ($i < $max_retries - 1) {
                usleep($retry_delay);
            }
        }
        
        log_message('error', '[Tool_draw_engin::_get_ml_id_by_drawing_no] ML_ID not found for Drawing No: ' . $drawing_no . ' after ' . $max_retries . ' attempts');
        return 0;
    }
    
    /**
     * Move uploaded file to Attachment_TMS folder structure
     * Folder structure: Attachment_TMS/Drawing/{ML_ID}/{REVISION}/{filename}
     * @param string $source_file_path Full path to source file
     * @param int $ml_id ML_ID (MLR_ML_ID)
     * @param int $revision MLR_REV
     * @param string $filename Original filename
     * @return bool True if successful, false otherwise
     */
    private function _move_file_to_attachment_folder($source_file_path, $ml_id, $revision, $filename)
    {
        if (!file_exists($source_file_path) || !is_file($source_file_path)) {
            log_message('error', '[Tool_draw_engin::_move_file_to_attachment_folder] Source file does not exist: ' . $source_file_path);
            return false;
        }
        
        // Sanitize filename
        $safe_filename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($filename));
        if (empty($safe_filename)) {
            $safe_filename = 'file_' . time();
        }
        
        // Use application folder: application/tms_modules/Attachment_TMS/Drawing/{ML_ID}/{REVISION}/
        $target_dir = APPPATH . 'tms_modules/Attachment_TMS/Drawing/' . (int)$ml_id . '/' . (int)$revision . '/';
        
        error_log('[Tool_draw_engin::_move_file_to_attachment_folder] Attempting to create/move to: ' . $target_dir);
        log_message('debug', '[Tool_draw_engin::_move_file_to_attachment_folder] Attempting to create/move to: ' . $target_dir);
        
        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            $created = @mkdir($target_dir, 0755, true);
            if (!$created) {
                $error = error_get_last();
                error_log('[Tool_draw_engin::_move_file_to_attachment_folder] ERROR: Cannot create directory: ' . $target_dir . '. Error: ' . ($error ? $error['message'] : 'Unknown'));
                log_message('error', '[Tool_draw_engin::_move_file_to_attachment_folder] Cannot create directory: ' . $target_dir . '. Error: ' . ($error ? $error['message'] : 'Unknown'));
                return false;
            } else {
                error_log('[Tool_draw_engin::_move_file_to_attachment_folder] Directory created successfully: ' . $target_dir);
                log_message('info', '[Tool_draw_engin::_move_file_to_attachment_folder] Directory created successfully: ' . $target_dir);
            }
        } else {
            error_log('[Tool_draw_engin::_move_file_to_attachment_folder] Directory already exists: ' . $target_dir);
        }
        
        $target_file = $target_dir . $safe_filename;
        
        error_log('[Tool_draw_engin::_move_file_to_attachment_folder] Moving file from: ' . $source_file_path . ' to: ' . $target_file);
        log_message('debug', '[Tool_draw_engin::_move_file_to_attachment_folder] Moving file from: ' . $source_file_path . ' to: ' . $target_file);
        
        // Check if target file already exists
        if (file_exists($target_file)) {
            error_log('[Tool_draw_engin::_move_file_to_attachment_folder] WARNING: Target file already exists, will be overwritten: ' . $target_file);
            @unlink($target_file);
        }
        
        // Move file to target location
        if (@rename($source_file_path, $target_file)) {
            // Verify file was moved
            if (file_exists($target_file) && !file_exists($source_file_path)) {
                error_log('[Tool_draw_engin::_move_file_to_attachment_folder] SUCCESS: File moved and verified. From: ' . $source_file_path . ' To: ' . $target_file);
                log_message('info', '[Tool_draw_engin::_move_file_to_attachment_folder] File moved successfully. From: ' . $source_file_path . ' To: ' . $target_file);
                return true;
            } else {
                error_log('[Tool_draw_engin::_move_file_to_attachment_folder] WARNING: rename() succeeded but file verification failed. Target exists: ' . (file_exists($target_file) ? 'YES' : 'NO') . ', Source exists: ' . (file_exists($source_file_path) ? 'YES' : 'NO'));
            }
        }
        
        // Try copy if rename fails (different filesystem)
        error_log('[Tool_draw_engin::_move_file_to_attachment_folder] rename() failed, trying copy()...');
        if (@copy($source_file_path, $target_file)) {
            // Verify copy was successful
            if (file_exists($target_file) && filesize($target_file) === filesize($source_file_path)) {
                // Delete source after successful copy
                if (@unlink($source_file_path)) {
                    error_log('[Tool_draw_engin::_move_file_to_attachment_folder] SUCCESS: File copied and source deleted. From: ' . $source_file_path . ' To: ' . $target_file);
                    log_message('info', '[Tool_draw_engin::_move_file_to_attachment_folder] File copied successfully. From: ' . $source_file_path . ' To: ' . $target_file);
                    return true;
                } else {
                    error_log('[Tool_draw_engin::_move_file_to_attachment_folder] WARNING: File copied but source deletion failed: ' . $source_file_path);
                    // Still return true as file is in correct location
                    return true;
                }
            } else {
                error_log('[Tool_draw_engin::_move_file_to_attachment_folder] ERROR: copy() succeeded but file size mismatch or target missing');
            }
        }
        
        // All methods failed
        $error = error_get_last();
        error_log('[Tool_draw_engin::_move_file_to_attachment_folder] ERROR: Failed to move/copy file. From: ' . $source_file_path . ' To: ' . $target_file . '. Error: ' . ($error ? $error['message'] : 'Unknown'));
        log_message('error', '[Tool_draw_engin::_move_file_to_attachment_folder] Failed to move/copy file. From: ' . $source_file_path . ' To: ' . $target_file . '. Error: ' . ($error ? $error['message'] : 'Unknown'));
        return false;
    }
}

