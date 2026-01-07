<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reports extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Reports/M_reports', 'reports');
        $this->load->model('Tool_inventory/M_tool_scrap', 'tool_scrap');
        
        // Fix BladeOne paths for Reports module - MUST be called after parent::__construct()
        // because MY_Controller initializes BladeOne in its constructor
        $this->fix_reports_module_paths();
        
        // ALWAYS force update to correct path for Reports module - regardless of what fix_reports_module_paths() did
        if ($this->config->item('Blade_enable')) {
            // Try multiple path formats to find the correct one
            $possible_views_paths = array(
                realpath(APPPATH . 'tms_modules/Reports/views'),
                realpath(APPPATH . 'tms_modules\Reports\views'),
                APPPATH . 'tms_modules/Reports/views',
                APPPATH . 'tms_modules\Reports\views'
            );
            
            $expected_views = null;
            foreach ($possible_views_paths as $path) {
                if ($path && is_dir($path)) {
                    $expected_views = $path;
                    break;
                }
            }
            
            if ($expected_views) {
                // Force update to correct path
                $this->views = $expected_views;
                
                // Try multiple cache paths
                $possible_cache_paths = array(
                    realpath(APPPATH . 'tms_modules/Reports/cache'),
                    realpath(APPPATH . 'tms_modules\Reports\cache'),
                    APPPATH . 'tms_modules/Reports/cache',
                    APPPATH . 'tms_modules\Reports\cache'
                );
                
                $expected_cache = null;
                foreach ($possible_cache_paths as $path) {
                    if ($path && (is_dir($path) || @mkdir($path, 0755, true))) {
                        $expected_cache = $path;
                        break;
                    }
                }
                
                $this->cache = $expected_cache ?: APPPATH . 'tms_modules/Reports/cache';
                
                // Create cache directory if it doesn't exist
                if (!is_dir($this->cache)) {
                    @mkdir($this->cache, 0755, true);
                }
                
                // Always reinitialize BladeOne with normalized paths
                $views_normalized = str_replace('\\', '/', $this->views);
                $cache_normalized = str_replace('\\', '/', $this->cache);
                
                $this->blade = new \eftec\bladeone\BladeOne($views_normalized, $cache_normalized);
                $this->blade->setBaseUrl(base_url());
                $this->blade->setFileExtension('.php');
                
                log_message('error', 'FORCED BladeOne paths - Views: ' . $views_normalized . ', Cache: ' . $cache_normalized);
                log_message('error', 'Template file should be at: ' . $this->views . DIRECTORY_SEPARATOR . 'index_reports.php');
                log_message('error', 'Template file exists: ' . (file_exists($this->views . DIRECTORY_SEPARATOR . 'index_reports.php') ? 'YES' : 'NO'));
            } else {
                log_message('error', 'Could not find expected views path. Tried: ' . implode(', ', $possible_views_paths));
            }
        }
    }
    
    /**
     * Fix module paths specifically for Reports module
     * This ensures BladeOne looks in tms_modules/Reports instead of modules/Reports
     */
    protected function fix_reports_module_paths()
    {
        // Skip if paths are already correct
        $correct_views = realpath(APPPATH . 'tms_modules/Reports/views');
        if ($correct_views && $this->views === $correct_views) {
            log_message('debug', 'Paths already correct, skipping fix_reports_module_paths()');
            return;
        }
        
        if ($this->config->item('Blade_enable') && $this->config->item('HMVC_enable')) {
            $module_name = 'Reports';
            $module_name_lower = strtolower($module_name);
            
            // Get all module locations
            $module_locations = $this->config->item('modules_locations');
            if (!is_array($module_locations)) {
                return;
            }
            
            // Find the correct location for Reports module
            $found_location = null;
            $found_module_name = $module_name;
            
            foreach ($module_locations as $location => $offset) {
                $location_path = rtrim($location, '/\\');
                
                // Skip if location directory doesn't exist
                if (!is_dir($location_path)) {
                    continue;
                }
                
                // Scan directory for case-insensitive matching
                $dirs = @scandir($location_path);
                if ($dirs === false) {
                    continue;
                }
                
                foreach ($dirs as $dir) {
                    if ($dir === '.' || $dir === '..') {
                        continue;
                    }
                    
                    $dir_path = $location_path . DIRECTORY_SEPARATOR . $dir;
                    if (is_dir($dir_path) && strtolower($dir) === $module_name_lower) {
                        $found_location = $location_path;
                        $found_module_name = $dir; // Use actual directory name with correct case
                        break 2; // Break both loops
                    }
                }
            }
            
            // If found, update paths
            if ($found_location !== null) {
                $real_path = realpath($found_location);
                if ($real_path !== false) {
                    $module_location_dir = rtrim($real_path, '/\\') . DIRECTORY_SEPARATOR;
                    
                    $views_path = $module_location_dir . $found_module_name . DIRECTORY_SEPARATOR . 'views';
                    $cache_path = $module_location_dir . $found_module_name . DIRECTORY_SEPARATOR . 'cache';
                    
                    // Ensure absolute paths using realpath
                    $views_real = realpath($views_path);
                    if ($views_real !== false) {
                        $this->views = $views_real;
                    } else {
                        // If realpath fails, use the constructed path
                        $this->views = $views_path;
                    }
                    
                    $cache_real = realpath($cache_path);
                    if ($cache_real !== false) {
                        $this->cache = $cache_real;
                    } else {
                        // If realpath fails, use the constructed path
                        $this->cache = $cache_path;
                    }
                    
                    // Log the paths for debugging
                    log_message('debug', 'Found module location: ' . $found_location);
                    log_message('debug', 'Module name: ' . $found_module_name);
                    log_message('debug', 'Updated views path: ' . $this->views);
                    log_message('debug', 'Updated cache path: ' . $this->cache);
                    
                    // Reinitialize BladeOne with correct paths
                    try {
                        // Normalize paths to use forward slashes for BladeOne compatibility
                        $views_path_normalized = str_replace('\\', '/', $this->views);
                        $cache_path_normalized = str_replace('\\', '/', $this->cache);
                        
                        // Create cache directory if it doesn't exist
                        if (!is_dir($this->cache)) {
                            @mkdir($this->cache, 0755, true);
                        }
                        
                        // Delete old cache files if they exist in wrong location
                        $wrong_cache_path = APPPATH . 'modules/reports/cache';
                        if (is_dir($wrong_cache_path)) {
                            $files = glob($wrong_cache_path . '/*.bladec');
                            foreach ($files as $file) {
                                @unlink($file);
                            }
                        }
                        
                        // Verify views directory exists
                        if (!is_dir($this->views)) {
                            log_message('error', 'Views directory does not exist: ' . $this->views);
                            // Try to create it
                            @mkdir($this->views, 0755, true);
                        }
                        
                        // Verify template file exists
                        $template_file = $this->views . DIRECTORY_SEPARATOR . 'index_reports.php';
                        if (!file_exists($template_file)) {
                            log_message('error', 'Template file does not exist: ' . $template_file);
                            // Try alternative path
                            $alt_template = str_replace('\\', '/', $this->views) . '/index_reports.php';
                            if (file_exists($alt_template)) {
                                log_message('debug', 'Found template at alternative path: ' . $alt_template);
                            }
                        }
                        
                        // Ensure views path is absolute and exists
                        $final_views_path = realpath($this->views);
                        if ($final_views_path === false) {
                            $final_views_path = $views_path_normalized;
                        } else {
                            $final_views_path = str_replace('\\', '/', $final_views_path);
                        }
                        
                        // Reinitialize BladeOne with normalized paths
                        $this->blade = new \eftec\bladeone\BladeOne($final_views_path, $cache_path_normalized);
                        $this->blade->setBaseUrl(base_url());
                        $this->blade->setFileExtension('.php'); // Set extension to .php instead of .blade.php
                        
                        // Debug: Log paths (can be removed later)
                        log_message('debug', 'BladeOne Views Path: ' . $final_views_path);
                        log_message('debug', 'BladeOne Cache Path: ' . $cache_path_normalized);
                        log_message('debug', 'Template file check: ' . $template_file . ' - Exists: ' . (file_exists($template_file) ? 'Yes' : 'No'));
                    } catch (Exception $ex) {
                        log_message('error', 'BladeOne reinitialization error in Reports controller: ' . $ex->getMessage());
                    }
                } else {
                    log_message('error', 'Could not get realpath for found_location: ' . $found_location);
                }
            } else {
                log_message('error', 'Reports module location not found in modules_locations');
            }
        }
    }

    /**
     * Index page - Reports list
     */
    public function index()
    {
        // Paths are already fixed in constructor, no need to fix again
        
        $data = array();
        $data['reports'] = $this->reports->get_reports_list();
        $this->view('index_reports', $data, FALSE);
    }

    /**
     * All Tool List Report
     */
    public function all_tool_list()
    {
        $data = array();
        $data['report_title'] = 'ALL TOOL LIST (trial)';
        $data['generated_on'] = date('n/j/Y g:i:s A');
        $data['tools'] = $this->reports->get_all_tool_list();
        
        $this->view('all_tool_list', $data, FALSE);
    }

    /**
     * All Tool List Old Report (Legacy version using database functions)
     */
    public function all_tool_list_old()
    {
        $data = array();
        $data['report_title'] = 'ALL TOOL LIST (trial)';
        $data['generated_on'] = date('n/j/Y g:i:s A');
        $data['tools'] = $this->reports->get_all_tool_list_old();
        
        $this->view('all_tool_list_old', $data, FALSE);
    }

    /**
     * ReOrder Point Report
     */
    public function reorder_point()
    {
        $data = array();
        $data['report_title'] = 'REORDER TOOL LIST';
        $data['generated_on'] = date('n/j/Y g:i:s A');
        $data['tools'] = $this->reports->get_reorder_point_data();
        
        $this->view('reorder_point', $data, FALSE);
    }

    /**
     * Tool Scrap Summary Report
     */
    public function tool_scrap_summary()
    {
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        $reason_id = $this->input->get('reason_id');
        $reason_id = $reason_id ? (int)$reason_id : 0;
        
        $data = array();
        $data['date_from'] = $date_from ? $date_from : '';
        $data['date_to'] = $date_to ? $date_to : '';
        $data['reason_id'] = $reason_id;
        $data['reasons'] = $this->reports->get_all_reasons();
        $data['scraps'] = array();
        $data['date_from_formatted'] = '';
        $data['date_to_formatted'] = '';
        
        // If filters are provided, fetch data
        if (!empty($date_from) && !empty($date_to)) {
            $filters = array(
                'date_from' => $date_from,
                'date_to' => $date_to,
                'reason_id' => $reason_id
            );
            
            $data['scraps'] = $this->reports->get_tool_scrap_summary($filters);
            
            // Format dates for display
            try {
                $from_date = new DateTime($date_from);
                $to_date = new DateTime($date_to);
                $data['date_from_formatted'] = $from_date->format('d-m-Y');
                $data['date_to_formatted'] = $to_date->format('d-m-Y');
            } catch (Exception $e) {
                $data['date_from_formatted'] = $date_from;
                $data['date_to_formatted'] = $date_to;
            }
        }
        
        $this->view('tool_scrap_summary', $data, FALSE);
    }

    /**
     * TOOLSET PM BM Report
     */
    public function toolset_pm_bm()
    {
        $data = array();
        $data['toolsets'] = $this->reports->get_toolsets_dropdown();
        $this->view('toolset_pm_bm', $data, FALSE);
    }

    public function get_parts_over_lifetime()
    {
        $tset_id = $this->input->post('tset_id');
        $data = $this->reports->get_toolset_parts($tset_id);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Tool Scrap Report - Search by SCRAP_ID
     */
    public function tool_scrap_report()
    {
        $scrap_id = $this->input->get('scrap_id');
        $scrap_id = $scrap_id ? (int)$scrap_id : 0;
        
        $data = array();
        $data['scrap_id'] = $scrap_id;
        $data['scrap'] = null;
        $data['error'] = null;
        
        if ($scrap_id > 0) {
            $row = $this->tool_scrap->get_by_id($scrap_id);
            if ($row) {
                // Map data to view format (same as report_tool_scrap.php expects)
                $data['scrap'] = $row;
            } else {
                $data['error'] = 'SCRAP_ID tidak ditemukan.';
            }
        }
        
        $this->view('tool_scrap_report', $data, FALSE);
    }
}

