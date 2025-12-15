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
            
            // Handle uploaded file (TD_DRAWING_FILE) or use old filename (TD_DRAWING_NO_OLD)
            $drawing_no = '';
            if (!empty($_FILES) && isset($_FILES['TD_DRAWING_FILE']) && !empty($_FILES['TD_DRAWING_FILE']['name'])) {
                $uploadDir = FCPATH . 'tool_drawing/img/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $origName = $_FILES['TD_DRAWING_FILE']['name'];
                $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($origName));
                $fileName = 'TD_' . time() . '_' . $safeName;
                $target = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['TD_DRAWING_FILE']['tmp_name'], $target)) {
                    $drawing_no = $fileName;
                } else {
                    $result['message'] = 'Gagal mengunggah file drawing.';
                    echo json_encode($result);
                    return;
                }
            } else {
                $drawing_no = $this->input->post('TD_DRAWING_NO_OLD', TRUE);
                if ($drawing_no === '' || $drawing_no === null) {
                    $drawing_no = $this->input->post('TD_DRAWING_NO', TRUE);
                }
            }

            // TD_TOOL_NAME from form contains TOOL_ID (select)
            $tool_id = (int)$this->input->post('TD_TOOL_NAME', TRUE);
            $revision = (int)$this->input->post('TD_REVISION', TRUE);
            $status = (int)$this->input->post('TD_STATUS', TRUE);
            $material_id = (int)$this->input->post('TD_MATERIAL_ID', TRUE);
            $maker_id = (int)$this->input->post('TD_MAKER_ID', TRUE);

            if ($action === 'ADD') {
                if (empty($drawing_no)) {
                    $result['message'] = 'Drawing wajib diunggah.';
                    echo json_encode($result);
                    return;
                }
                $ok = $this->tool_draw_engin->add_data($product_id, $process_id, $drawing_no, $tool_id, $revision, $status, $material_id, $maker_id);
                if ($ok === true) {
                    $result['success'] = true;
                    $result['message'] = $this->tool_draw_engin->messages ?: 'Tool Drawing Engineering berhasil ditambahkan.';
                } else {
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
                    $material_id
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
     * Helper function to build file URL from database/local
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
        
        // Use local endpoint to serve file from database
        // Format: base_url('Tool_drawing/tool_draw_engin/serve_file')?id={identifier}&type={drawing|sketch}
        $fileUrl = base_url('Tool_drawing/tool_draw_engin/serve_file') . '?id=' . urlencode($fileIdentifier) . '&type=' . urlencode($module);
        
        return $fileUrl;
    }

    /**
     * serve_file: Serve file from filesystem based on filename stored in database
     * This method retrieves filename from database and serves file from filesystem
     * @param string $id File identifier/name from MLR_DRAWING or MLR_SKETCH
     * @param string $type File type (drawing or sketch)
     */
    public function serve_file()
    {
        $file_id = $this->input->get('id', TRUE);
        $file_type = $this->input->get('type', TRUE);
        
        if (empty($file_id)) {
            show_404();
            return;
        }
        
        // Load database connection
        $db_tms = $this->load->database('tms_NEW', true);
        if (!$db_tms) {
            show_404();
            return;
        }
        
        // Determine which column to query based on file type
        $column_name = 'MLR_DRAWING';
        if ($file_type === 'sketch' || strpos(strtolower($file_type), 'sketch') !== false) {
            $column_name = 'MLR_SKETCH';
        }
        
        // Get filename from database
        // MLR_DRAWING and MLR_SKETCH store filename (varchar(50)), not BLOB
        $sql = "
            SELECT TOP 1
                rev." . $column_name . " AS FILE_NAME
            FROM TMS_NEW.dbo.TMS_TOOL_MASTER_LIST_REV rev
            WHERE rev." . $column_name . " = ?
        ";
        
        $q = $db_tms->query($sql, array($file_id));
        if (!$q || $q->num_rows() == 0) {
            show_404();
            return;
        }
        
        $row = $q->row_array();
        $file_name = isset($row['FILE_NAME']) ? trim($row['FILE_NAME']) : '';
        
        if (empty($file_name)) {
            show_404();
            return;
        }
        
        // Clean filename to prevent directory traversal
        $file_name = basename($file_name);
        
        // File location: tool_drawing/img/ (current upload directory)
        $file_path = FCPATH . 'tool_drawing/img/' . $file_name;
        
        if (file_exists($file_path) && is_file($file_path)) {
            // Serve file from filesystem
            $this->_output_file($file_path);
            return;
        }
        
        // If file not found, return 404
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

        // Build file URLs from server
        $drawing_file_id = isset($row['TD_DRAWING_FILE']) ? $row['TD_DRAWING_FILE'] : '';
        $sketch_file_id = isset($row['TD_SKETCH_FILE']) ? $row['TD_SKETCH_FILE'] : '';
        
        $drawing_file_url = $this->build_file_url($drawing_file_id, 'ToolDrawing');
        $sketch_file_url = $this->build_file_url($sketch_file_id, 'ToolDrawing');

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
                'TD_SKETCH_FILE' => $sketch_file_id,
                'TD_SKETCH_FILE_URL' => $sketch_file_url
            )
        );

        $this->output->set_output(json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
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
                $drawing_url = $this->build_file_url($drawing_id, 'ToolDrawing');
                $sketch_url = $this->build_file_url($sketch_id, 'ToolDrawing');
                
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
}

