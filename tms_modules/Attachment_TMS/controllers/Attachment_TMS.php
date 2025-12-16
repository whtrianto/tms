<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller untuk serve file dari folder Attachment_TMS
 * Handle routing: Attachment_TMS/{folder}/{mlr_id}/{mlr_rev}/{filename}
 */
class Attachment_TMS extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // No authentication required for file serving (or add if needed)
    }

    /**
     * Serve file from Attachment_TMS folder
     * URL format: Attachment_TMS/{folder}/{mlr_id}/{mlr_rev}/{filename}
     * Alternative: Attachment_TMS?folder={folder}&mlr_id={id}&mlr_rev={rev}&filename={name}
     * Example: Attachment_TMS/Drawing/73443/0/Screenshot (2).png
     */
    public function index()
    {
        // Try to get from query parameters first (more reliable for special chars)
        $folder_name = $this->input->get('folder', TRUE);
        $mlr_ml_id = (int)$this->input->get('mlr_ml_id', TRUE);
        $mlr_rev = (int)$this->input->get('mlr_rev', TRUE);
        $filename = $this->input->get('filename', TRUE);
        
        // If not in query params, try URI segments
        // Note: URL format uses MLR_ML_ID for folder location
        if (empty($folder_name) || $mlr_ml_id <= 0 || empty($filename)) {
            $segments = $this->uri->segment_array();
            
            // Expected format: Attachment_TMS/{folder}/{mlr_ml_id}/{mlr_rev}/{filename}
            // segments[0] = 'Attachment_TMS' (controller name)
            // segments[1] = folder name (Drawing, Drawing_Sketch, BOM, etc)
            // segments[2] = mlr_ml_id (MLR_ML_ID used for folder location)
            // segments[3] = mlr_rev
            // segments[4+] = filename (may contain spaces/special chars, so join remaining segments)
            
            if (count($segments) >= 4) {
                $folder_name = $segments[1];
                $mlr_ml_id = (int)$segments[2];
                $mlr_rev = (int)$segments[3];
                
                // Join remaining segments as filename (handle files with spaces/special chars)
                $filename_parts = array_slice($segments, 4);
                $filename = implode('/', $filename_parts);
            }
        }
        
        // URL decode the filename
        if (!empty($filename)) {
            $filename = urldecode($filename);
            $filename = basename($filename); // Prevent directory traversal
        }
        
        if ($mlr_ml_id <= 0 || empty($filename) || empty($folder_name)) {
            show_404();
            return;
        }
        
        // Try multiple possible paths using MLR_ML_ID
        $possible_paths = array(
            // Path 1: Web root Attachment_TMS
            FCPATH . 'Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/' . $mlr_rev . '/' . $filename,
            // Path 2: Application folder tms_modules
            APPPATH . 'tms_modules/Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/' . $mlr_rev . '/' . $filename,
            // Path 3: Try without revision subfolder
            FCPATH . 'Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/' . $filename,
            APPPATH . 'tms_modules/Attachment_TMS/' . $folder_name . '/' . $mlr_ml_id . '/' . $filename,
        );
        
        $file_path = null;
        foreach ($possible_paths as $path) {
            if (file_exists($path) && is_file($path)) {
                $file_path = $path;
                break;
            }
        }
        
        if (!$file_path) {
            log_message('error', '[Attachment_TMS] File not found: ' . $folder_name . '/' . $mlr_ml_id . '/' . $mlr_rev . '/' . $filename);
            show_404();
            return;
        }
        
        // Serve the file
        $this->_output_file($file_path);
    }
    
    /**
     * Output file with proper headers
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
        
        // Set headers for download - always use attachment for proper download
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . $file_size);
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Cache-Control: public, max-age=3600');
        header('Pragma: public');
        
        // Disable output buffering to prevent corruption
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Output file directly
        readfile($file_path);
        exit;
    }
    
    /**
     * Get MIME type from filename extension
     */
    private function _get_mime_type_from_filename($file_path)
    {
        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
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
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip',
            'txt' => 'text/plain',
        );
        
        return isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';
    }
}

