<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller untuk serve file dari folder Attachment_TMS
 * Handle routing: Attachment_TMS/{folder}/{mlr_id}/{mlr_rev}/{filename}
 * 
 * IMPORTANT: This controller extends CI_Controller directly to avoid MY_Controller output issues
 */
class Attachment_TMS extends CI_Controller
{
    public function __construct()
    {
        // Disable ALL output buffering immediately
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Turn off output buffering completely
        if (ini_get('output_buffering')) {
            ini_set('output_buffering', 'Off');
        }
        
        parent::__construct();
        
        // Disable CodeIgniter output class completely
        $this->output->enable_profiler(FALSE);
        $this->output->_display = FALSE;
        $this->output->set_output(''); // Clear any output
        
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
        // CRITICAL: Disable ALL output buffering FIRST - before anything else
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Turn off output buffering at PHP level
        if (ini_get('output_buffering')) {
            ini_set('output_buffering', 'Off');
        }
        
        // CRITICAL: Check if any output has been sent already
        if (headers_sent($file, $line)) {
            error_log('[Attachment_TMS] ERROR: Headers already sent at ' . $file . ':' . $line . ' - This will cause file corruption!');
            http_response_code(500);
            exit;
        }
        
        // Disable CodeIgniter output class completely
        $this->output->enable_profiler(FALSE);
        $this->output->_display = FALSE;
        $this->output->set_output(''); // Clear any output
        
        // Prevent CodeIgniter from appending anything
        $this->output->_final_output = '';
        
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
            // Send 404 header without using show_404() to avoid output
            http_response_code(404);
            header('Content-Type: text/plain');
            echo 'File not found';
            exit;
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
            // Send 404 header without using show_404() or log_message() to avoid output
            http_response_code(404);
            header('Content-Type: text/plain');
            echo 'File not found';
            exit;
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
            http_response_code(404);
            header('Content-Type: text/plain');
            echo 'File not found';
            exit;
        }
        
        // Disable all output buffering completely - do this FIRST
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Disable CodeIgniter output class completely
        $this->output->_display = FALSE;
        
        // Get file info BEFORE any output
        $file_size = filesize($file_path);
        $file_name = basename($file_path);
        
        // Get MIME type
        $mime_type = $this->_get_mime_type_from_filename($file_path);
        
        // Try to detect MIME type from file content if available
        if (function_exists('mime_content_type')) {
            $detected_mime = @mime_content_type($file_path);
            if ($detected_mime) {
                $mime_type = $detected_mime;
            }
        }
        
        // CRITICAL: Check if headers already sent (would cause corruption)
        if (headers_sent($file, $line)) {
            // Headers already sent - this is the problem!
            // Log error but don't output anything
            error_log('[Attachment_TMS] Headers already sent at ' . $file . ':' . $line);
            http_response_code(500);
            exit;
        }
        
        // Set headers for download - MUST be before any output
        header('Content-Type: ' . $mime_type);
        header('Content-Length: ' . $file_size);
        header('Content-Disposition: attachment; filename="' . addslashes($file_name) . '"');
        header('Cache-Control: public, max-age=3600');
        header('Pragma: public');
        header('Expires: 0');
        
        // CRITICAL: Make sure NO output buffer exists before reading file
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        // Use fopen + fpassthru for better control and error handling
        $handle = @fopen($file_path, 'rb');
        if ($handle === FALSE) {
            error_log('[Attachment_TMS] Cannot open file: ' . $file_path);
            http_response_code(500);
            exit;
        }
        
        // Use fpassthru which is more reliable than readfile for large files
        // fpassthru outputs file directly to output stream
        $bytes_sent = @fpassthru($handle);
        fclose($handle);
        
        if ($bytes_sent === FALSE) {
            error_log('[Attachment_TMS] Error sending file: ' . $file_path);
            http_response_code(500);
            exit;
        }
        
        // Verify bytes sent matches file size
        if ($bytes_sent !== $file_size) {
            error_log('[Attachment_TMS] Size mismatch. Expected: ' . $file_size . ', Sent: ' . $bytes_sent . '. File: ' . $file_path);
        }
        
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
