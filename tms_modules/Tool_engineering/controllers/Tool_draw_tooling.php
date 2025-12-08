<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property M_tool_draw_tooling $tool_draw_tooling
 */
class Tool_draw_tooling extends MY_Controller
{
    public $uid = '';

    public function __construct()
    {
        parent::__construct();

        $this->load->library(array('form_validation', 'session'));

        // capture current user id/username for later use (fallback to SYSTEM)
        $username_from_session = $this->session->userdata('username');
        $this->uid = (string) ($username_from_session ?: 'SYSTEM');
        log_message('debug', '[Tool_draw_tooling::__construct] username_from_session=' . var_export($username_from_session, true) . ', uid="' . $this->uid . '"');

        // load models AFTER setting uid
        $this->load->model('M_tool_draw_tooling', 'tool_draw_tooling');
        $this->tool_draw_tooling->uid = $this->uid;
        log_message('debug', '[Tool_draw_tooling::__construct] tooling model uid set to "' . $this->tool_draw_tooling->uid . '"');

        // Also load the engineering model — we will use it as the data source for the tooling UI
        $this->load->model('M_tool_draw_engin', 'tool_draw_engin');
        $this->tool_draw_engin->uid = $this->uid;
        log_message('debug', '[Tool_draw_tooling::__construct] engin model uid set to "' . $this->tool_draw_engin->uid . '"');

        $this->config->set_item('Blade_enable', FALSE);
    }

    public function index()
    {
        $data = array();
        // Use engineering table as data source for the tooling listing
        $data['list_data'] = $this->tool_draw_engin->get_all();

        // Provide master lookups from engineering model
        $data['products'] = $this->tool_draw_engin->get_products();
        $data['operations'] = $this->tool_draw_engin->get_operations();
        $data['tools'] = $this->tool_draw_engin->get_tools();
        $data['materials'] = $this->tool_draw_engin->get_materials();
        // Maker list comes from TMS_M_MAKER
        $data['makers'] = $this->tool_draw_engin->get_makers();

        $this->view('index_tool_draw_tooling', $data, FALSE);
    }

    /**
     * submit_data: ADD / EDIT Tool Drawing Tooling (AJAX)
     */
    public function submit_data()
    {
        // Clear output buffers to ensure clean JSON response
        if (ob_get_level()) ob_clean();

        $this->output->set_content_type('application/json');
        $result = array('success' => false, 'message' => '');

        try {
            $action = strtoupper($this->input->post('action', TRUE));
            $id     = (int)$this->input->post('TT_ID', TRUE);

            // validation rules
            $this->form_validation->set_rules('TT_TOOL_ID', 'Tool ID', 'required|integer');
            $this->form_validation->set_rules('TT_MIN_QTY', 'Min Quantity', 'integer');
            $this->form_validation->set_rules('TT_REPLENISH_QTY', 'Replenish Quantity', 'integer');
            $this->form_validation->set_rules('TT_MAKER_ID', 'Maker ID', 'integer');
            $this->form_validation->set_rules('TT_PRICE', 'Price', 'numeric');
            $this->form_validation->set_rules('TT_TOOL_LIFE', 'Tool Life', 'integer');

            if ($this->form_validation->run() == FALSE) {
                $this->form_validation->set_error_delimiters('', '');
                $result['message'] = validation_errors() ?: 'Data tidak valid.';
                echo json_encode($result);
                return;
            }

            $tool_id = (int)$this->input->post('TT_TOOL_ID', TRUE);
            $min_qty = (int)$this->input->post('TT_MIN_QTY', TRUE);
            $replenish_qty = (int)$this->input->post('TT_REPLENISH_QTY', TRUE);
            $maker_id = (int)$this->input->post('TT_MAKER_ID', TRUE);
            $price = (float)$this->input->post('TT_PRICE', TRUE);
            $description = trim($this->input->post('TT_DESCRIPTION', TRUE));
            $material_id = (int)$this->input->post('TT_MATERIAL_ID', TRUE);
            $tool_life = (int)$this->input->post('TT_TOOL_LIFE', TRUE);

            if ($action === 'ADD') {
                $ok = $this->tool_draw_tooling->add_data($tool_id, $min_qty, $replenish_qty, $maker_id, $price, $description, $material_id, $tool_life);
                if ($ok === true) {
                    $result['success'] = true;
                    $result['message'] = $this->tool_draw_tooling->messages ?: 'Tool Drawing Tooling berhasil ditambahkan.';
                } else {
                    $result['success'] = false;
                    $result['message'] = $this->tool_draw_tooling->messages ?: 'Gagal menambahkan tool drawing tooling.';
                }
                $json = json_encode($result);
                log_message('debug', '[submit_data ADD] response: ' . $json);
                echo $json;
                return;
            }

            if ($action === 'EDIT' && $id > 0) {
                // Try to update in tooling table first (TT)
                log_message('debug', '[submit_data EDIT] trying tooling edit for id=' . $id);
                $current_tt = $this->tool_draw_tooling->get_by_id($id);
                if ($current_tt) {
                    $ok = $this->tool_draw_tooling->edit_data($id, $tool_id, $min_qty, $replenish_qty, $maker_id, $price, $description, $material_id, $tool_life);
                    if ($ok === true) {
                        $result['success'] = true;
                        $result['message'] = $this->tool_draw_tooling->messages ?: 'Tool Drawing Tooling berhasil diperbarui.';
                    } else {
                        $result['success'] = false;
                        $result['message'] = $this->tool_draw_tooling->messages ?: 'Gagal memperbarui tool drawing tooling.';
                    }
                    $json = json_encode($result);
                    log_message('debug', '[submit_data EDIT] tooling response: ' . $json);
                    echo $json;
                    return;
                }

                // If not found in TT, attempt to find in engineering table (TD) and update with full tooling specs
                log_message('debug', '[submit_data EDIT] tooling row not found, trying engineering id=' . $id);
                $current_td = $this->tool_draw_engin->get_by_id($id);
                if ($current_td) {
                    // Map fields: keep product/process/drawing/revision/status from existing record
                    $product_id = isset($current_td['TD_PRODUCT_ID']) ? (int)$current_td['TD_PRODUCT_ID'] : 0;
                    $process_id = isset($current_td['TD_PROCESS_ID']) ? (int)$current_td['TD_PROCESS_ID'] : 0;
                    $drawing_no = isset($current_td['TD_DRAWING_NO']) ? $current_td['TD_DRAWING_NO'] : '';
                    // Map tool id -> tool name (engineering uses TD_TOOL_NAME)
                    $tool_name = '';
                    if ($tool_id > 0) {
                        $tool_row = $this->tool_draw_engin->get_tool_by_id($tool_id);
                        if ($tool_row && isset($tool_row['TOOL_NAME'])) {
                            $tool_name = $tool_row['TOOL_NAME'];
                        }
                    }
                    if ($tool_name === '') {
                        $tool_name = isset($current_td['TD_TOOL_NAME']) ? $current_td['TD_TOOL_NAME'] : '';
                    }
                    $revision = isset($current_td['TD_REVISION']) ? (int)$current_td['TD_REVISION'] : 0;
                    $status = isset($current_td['TD_STATUS']) ? (int)$current_td['TD_STATUS'] : 0;
                    $material_to_set = $material_id > 0 ? $material_id : (isset($current_td['TD_MATERIAL_ID']) ? (int)$current_td['TD_MATERIAL_ID'] : 0);

                    // Use new method that handles tooling-specific columns
                    $ok = $this->tool_draw_engin->edit_data_with_tooling($id, $product_id, $process_id, $drawing_no, $tool_name, $revision, $status, $material_to_set, $maker_id, $min_qty, $replenish_qty, $price, $tool_life, $description);
                    if ($ok === true) {
                        $result['success'] = true;
                        $result['message'] = $this->tool_draw_engin->messages ?: 'Tool Drawing Engineering berhasil diperbarui.';
                    } else {
                        $result['success'] = false;
                        $result['message'] = $this->tool_draw_engin->messages ?: 'Gagal memperbarui tool drawing engineering.';
                    }
                    $json = json_encode($result);
                    log_message('debug', '[submit_data EDIT] engin response: ' . $json);
                    echo $json;
                    return;
                }

                // Neither TT nor TD found
                $result['message'] = 'Data tidak ditemukan.';
                $json = json_encode($result);
                log_message('debug', '[submit_data EDIT] not found in TT or TD, response: ' . $json);
                echo $json;
                return;
            }

            $result['message'] = 'Parameter action/ID tidak valid.';
            $json = json_encode($result);
            log_message('debug', '[submit_data] invalid action/id response: ' . $json);
            echo $json;
            return;
        } catch (Exception $e) {
            // log full context for debugging
            $ctx = array(
                'msg' => $e->getMessage(),
                'post' => $_POST
            );
            log_message('error', '[Tool_draw_tooling::submit_data] Exception: ' . $e->getMessage() . ' | Context: ' . json_encode($ctx));
            $result['success'] = false;
            $result['message'] = 'Server error. Cek log untuk detail.';
            echo json_encode($result);
            return;
        }
    }

    /**
     * delete_data: delete tool drawing tooling
     */
    public function delete_data()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TT_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'TT_ID tidak ditemukan.'));
            return;
        }

        $ok = $this->tool_draw_tooling->delete_data($id);
        if ($ok) {
            echo json_encode(array('success' => true, 'message' => $this->tool_draw_tooling->messages ?: 'Tool Drawing Tooling berhasil dihapus.'));
        } else {
            echo json_encode(array('success' => false, 'message' => $this->tool_draw_tooling->messages ?: 'Gagal menghapus tool drawing tooling.'));
        }
    }

    /**
     * get_tool_draw_tooling_detail: ambil data by id (AJAX)
     */
    public function get_tool_draw_tooling_detail()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TT_ID', TRUE);
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'TT_ID tidak ditemukan.'));
            return;
        }
        // Read from engineering table by TD_ID (we accept TT_ID as the incoming identifier)
        $row = $this->tool_draw_engin->get_by_id($id);
        if ($row) {
            // Resolve product/process/tool/material names using engineering model lookups
            $row['PRODUCT_NAME'] = '';
            foreach ($this->tool_draw_engin->get_products() as $p) {
                if ((int)$p['PRODUCT_ID'] === (int)$row['TD_PRODUCT_ID']) {
                    $row['PRODUCT_NAME'] = $p['PRODUCT_NAME'];
                    break;
                }
            }

            $row['OPERATION_NAME'] = '';
            foreach ($this->tool_draw_engin->get_operations() as $op) {
                if ((int)$op['OPERATION_ID'] === (int)$row['TD_PROCESS_ID']) {
                    $row['OPERATION_NAME'] = $op['OPERATION_NAME'];
                    break;
                }
            }

            $row['TOOL_NAME'] = '';
            foreach ($this->tool_draw_engin->get_tools() as $t) {
                if ((int)$t['TOOL_ID'] === (int)$row['TD_TOOL_ID']) {
                    $row['TOOL_NAME'] = $t['TOOL_NAME'];
                    break;
                }
            }

            $row['MATERIAL_NAME'] = '';
            foreach ($this->tool_draw_engin->get_materials() as $mat) {
                if ((int)$mat['MATERIAL_ID'] === (int)$row['TD_MATERIAL_ID']) {
                    $row['MATERIAL_NAME'] = $mat['MATERIAL_NAME'];
                    break;
                }
            }

            echo json_encode(array('success' => true, 'data' => $row));
        } else {
            echo json_encode(array('success' => false, 'message' => 'Data tidak ditemukan.'));
        }
    }

    /**
     * get_history_by_id: Get revision history for a specific tool drawing tooling
     */
    public function get_history_by_id()
    {
        $this->output->set_content_type('application/json');

        $id = (int)$this->input->post('TT_ID', TRUE);
        log_message('debug', '[get_history_by_id] received TT_ID=' . var_export($id, true));
        if ($id <= 0) {
            echo json_encode(array('success' => false, 'message' => 'TT_ID tidak ditemukan.'));
            return;
        }

        // First, get current tooling record to extract tooling-specific data
        $current_tooling = $this->tool_draw_tooling->get_by_id($id);
        log_message('debug', '[get_history_by_id] current_tooling data: ' . ($current_tooling ? json_encode($current_tooling) : 'null'));

        // Fetch history from engineering model (history stored against TD_ID)
        $history = $this->tool_draw_engin->get_history($id);
        log_message('debug', '[get_history_by_id] engin model returned ' . var_export(is_array($history) ? count($history) : $history, true) . ' history rows');
        if ($history && count($history) > 0) {
            // Enrich history with resolved names using engin model lookups
            $products = $this->tool_draw_engin->get_products();
            $operations = $this->tool_draw_engin->get_operations();
            $tools = $this->tool_draw_engin->get_tools();
            $materials = $this->tool_draw_engin->get_materials();
            $makers = $this->tool_draw_engin->get_makers();

            foreach ($history as &$h) {
                // Merge tooling data from current tooling record if available
                // This ensures tooling fields are populated even if not in history
                // Priority: use history data if exists, otherwise use current tooling data
                if ($current_tooling) {
                    // Only set if not already set in history (preserve history values, including 0)
                    // Check if field is truly missing (not set or null), not if it's 0
                    if ((!isset($h['TD_MIN_QTY']) || $h['TD_MIN_QTY'] === null) && isset($current_tooling['TT_MIN_QTY']) && $current_tooling['TT_MIN_QTY'] !== null) {
                        $h['TD_MIN_QTY'] = (int)$current_tooling['TT_MIN_QTY'];
                    }
                    if ((!isset($h['TD_REPLENISH_QTY']) || $h['TD_REPLENISH_QTY'] === null) && isset($current_tooling['TT_REPLENISH_QTY']) && $current_tooling['TT_REPLENISH_QTY'] !== null) {
                        $h['TD_REPLENISH_QTY'] = (int)$current_tooling['TT_REPLENISH_QTY'];
                    }
                    if ((!isset($h['TD_PRICE']) || $h['TD_PRICE'] === null) && isset($current_tooling['TT_PRICE']) && $current_tooling['TT_PRICE'] !== null) {
                        $h['TD_PRICE'] = (float)$current_tooling['TT_PRICE'];
                    }
                    if ((!isset($h['TD_TOOL_LIFE']) || $h['TD_TOOL_LIFE'] === null) && isset($current_tooling['TT_TOOL_LIFE']) && $current_tooling['TT_TOOL_LIFE'] !== null) {
                        $h['TD_TOOL_LIFE'] = (int)$current_tooling['TT_TOOL_LIFE'];
                    }
                    if ((!isset($h['TD_DESCRIPTION']) || $h['TD_DESCRIPTION'] === null || $h['TD_DESCRIPTION'] === '') && isset($current_tooling['TT_DESCRIPTION']) && $current_tooling['TT_DESCRIPTION'] !== null && $current_tooling['TT_DESCRIPTION'] !== '') {
                        $h['TD_DESCRIPTION'] = $current_tooling['TT_DESCRIPTION'];
                    }
                    // Also add TT_* versions for compatibility (always set from current if not in history)
                    if (!isset($h['TT_MIN_QTY']) && isset($current_tooling['TT_MIN_QTY'])) {
                        $h['TT_MIN_QTY'] = (int)$current_tooling['TT_MIN_QTY'];
                    }
                    if (!isset($h['TT_REPLENISH_QTY']) && isset($current_tooling['TT_REPLENISH_QTY'])) {
                        $h['TT_REPLENISH_QTY'] = (int)$current_tooling['TT_REPLENISH_QTY'];
                    }
                    if (!isset($h['TT_PRICE']) && isset($current_tooling['TT_PRICE'])) {
                        $h['TT_PRICE'] = (float)$current_tooling['TT_PRICE'];
                    }
                    if (!isset($h['TT_TOOL_LIFE']) && isset($current_tooling['TT_TOOL_LIFE'])) {
                        $h['TT_TOOL_LIFE'] = (int)$current_tooling['TT_TOOL_LIFE'];
                    }
                    if (!isset($h['TT_DESCRIPTION']) && isset($current_tooling['TT_DESCRIPTION'])) {
                        $h['TT_DESCRIPTION'] = $current_tooling['TT_DESCRIPTION'];
                    }
                }
                // Normalize common alternative column names into canonical TD_* keys so UI can rely on them
                // Min quantity
                if (!isset($h['TD_MIN_QTY']) || $h['TD_MIN_QTY'] === null) {
                    if (isset($h['MIN_QTY'])) $h['TD_MIN_QTY'] = (int)$h['MIN_QTY'];
                    elseif (isset($h['TT_MIN_QTY'])) $h['TD_MIN_QTY'] = (int)$h['TT_MIN_QTY'];
                    elseif (isset($h['MINQTY'])) $h['TD_MIN_QTY'] = (int)$h['MINQTY'];
                    else $h['TD_MIN_QTY'] = null;
                }
                // Replenish quantity
                if (!isset($h['TD_REPLENISH_QTY']) || $h['TD_REPLENISH_QTY'] === null) {
                    if (isset($h['REPLENISH_QTY'])) $h['TD_REPLENISH_QTY'] = (int)$h['REPLENISH_QTY'];
                    elseif (isset($h['TT_REPLENISH_QTY'])) $h['TD_REPLENISH_QTY'] = (int)$h['TT_REPLENISH_QTY'];
                    elseif (isset($h['REPLENISHQTY'])) $h['TD_REPLENISH_QTY'] = (int)$h['REPLENISHQTY'];
                    else $h['TD_REPLENISH_QTY'] = null;
                }
                // Price
                if (!isset($h['TD_PRICE']) || $h['TD_PRICE'] === null) {
                    if (isset($h['PRICE'])) $h['TD_PRICE'] = (float)$h['PRICE'];
                    elseif (isset($h['TT_PRICE'])) $h['TD_PRICE'] = (float)$h['TT_PRICE'];
                    else $h['TD_PRICE'] = null;
                }
                // Tool life
                if (!isset($h['TD_TOOL_LIFE']) || $h['TD_TOOL_LIFE'] === null) {
                    if (isset($h['TOOL_LIFE'])) $h['TD_TOOL_LIFE'] = (int)$h['TOOL_LIFE'];
                    elseif (isset($h['TT_TOOL_LIFE'])) $h['TD_TOOL_LIFE'] = (int)$h['TT_TOOL_LIFE'];
                    else $h['TD_TOOL_LIFE'] = null;
                }
                // Description
                if (!isset($h['TD_DESCRIPTION']) || $h['TD_DESCRIPTION'] === null) {
                    if (isset($h['DESCRIPTION'])) $h['TD_DESCRIPTION'] = $h['DESCRIPTION'];
                    elseif (isset($h['TT_DESCRIPTION'])) $h['TD_DESCRIPTION'] = $h['TT_DESCRIPTION'];
                    else $h['TD_DESCRIPTION'] = null;
                }
                // Maker name / id aliases
                if (!isset($h['TD_MAKER_ID']) && isset($h['MAKER_ID'])) $h['TD_MAKER_ID'] = (int)$h['MAKER_ID'];
                if (!isset($h['MAKER_NAME']) || $h['MAKER_NAME'] === null) {
                    if (isset($h['MAKER'])) $h['MAKER_NAME'] = $h['MAKER'];
                    elseif (isset($h['MAKER_NAME'])) $h['MAKER_NAME'] = $h['MAKER_NAME'];
                    elseif (isset($h['TD_MAKER_NAME'])) $h['MAKER_NAME'] = $h['TD_MAKER_NAME'];
                }

                $h['PRODUCT_NAME'] = '';
                foreach ($products as $p) {
                    if ((int)$p['PRODUCT_ID'] === (int)$h['TD_PRODUCT_ID']) {
                        $h['PRODUCT_NAME'] = $p['PRODUCT_NAME'];
                        break;
                    }
                }

                $h['OPERATION_NAME'] = '';
                foreach ($operations as $op) {
                    if ((int)$op['OPERATION_ID'] === (int)$h['TD_PROCESS_ID']) {
                        $h['OPERATION_NAME'] = $op['OPERATION_NAME'];
                        break;
                    }
                }

                // Prefer TD_TOOL_NAME (string) if present; otherwise try lookup by TD_TOOL_ID
                if (isset($h['TD_TOOL_NAME']) && $h['TD_TOOL_NAME'] !== '') {
                    $h['TOOL_NAME'] = $h['TD_TOOL_NAME'];
                } else {
                    $h['TOOL_NAME'] = '';
                    foreach ($tools as $t) {
                        if ((int)$t['TOOL_ID'] === (int)$h['TD_TOOL_ID']) {
                            $h['TOOL_NAME'] = $t['TOOL_NAME'];
                            break;
                        }
                    }
                }

                $h['MATERIAL_NAME'] = '';
                foreach ($materials as $mat) {
                    if ((int)$mat['MATERIAL_ID'] === (int)$h['TD_MATERIAL_ID']) {
                        $h['MATERIAL_NAME'] = $mat['MATERIAL_NAME'];
                        break;
                    }
                }

                $h['MAKER_NAME'] = '';
                foreach ($makers as $m) {
                    $makerIdCandidate = (int)(isset($h['TD_MAKER_ID']) ? $h['TD_MAKER_ID'] : (isset($h['MAKER_ID']) ? $h['MAKER_ID'] : 0));
                    if ((int)$m['MAKER_ID'] === $makerIdCandidate) {
                        $h['MAKER_NAME'] = $m['MAKER_NAME'];
                        break;
                    }
                }

                // Final fallback: if any snapshot name is still empty, try to fetch current TD record
                // Also check if tooling fields are missing and try to get from current record
                $needFallback = (empty($h['PRODUCT_NAME']) || empty($h['OPERATION_NAME']) || empty($h['MATERIAL_NAME']) || empty($h['MAKER_NAME']) ||
                    (!isset($h['TD_MIN_QTY']) || $h['TD_MIN_QTY'] === null) ||
                    (!isset($h['TD_REPLENISH_QTY']) || $h['TD_REPLENISH_QTY'] === null) ||
                    (!isset($h['TD_PRICE']) || $h['TD_PRICE'] === null) ||
                    (!isset($h['TD_TOOL_LIFE']) || $h['TD_TOOL_LIFE'] === null) ||
                    (!isset($h['TD_DESCRIPTION']) || $h['TD_DESCRIPTION'] === null));

                if ($needFallback && isset($h['TD_ID']) && (int)$h['TD_ID'] > 0) {
                    $current = $this->tool_draw_engin->get_by_id((int)$h['TD_ID']);
                    if ($current) {
                        if (empty($h['PRODUCT_NAME']) && isset($current['TD_PRODUCT_ID'])) {
                            foreach ($products as $p) {
                                if ((int)$p['PRODUCT_ID'] === (int)$current['TD_PRODUCT_ID']) {
                                    $h['PRODUCT_NAME'] = $p['PRODUCT_NAME'];
                                    break;
                                }
                            }
                        }
                        if (empty($h['OPERATION_NAME']) && isset($current['TD_PROCESS_ID'])) {
                            foreach ($operations as $op) {
                                if ((int)$op['OPERATION_ID'] === (int)$current['TD_PROCESS_ID']) {
                                    $h['OPERATION_NAME'] = $op['OPERATION_NAME'];
                                    break;
                                }
                            }
                        }
                        if (empty($h['MATERIAL_NAME']) && isset($current['TD_MATERIAL_ID'])) {
                            foreach ($materials as $mat) {
                                if ((int)$mat['MATERIAL_ID'] === (int)$current['TD_MATERIAL_ID']) {
                                    $h['MATERIAL_NAME'] = $mat['MATERIAL_NAME'];
                                    break;
                                }
                            }
                        }
                        if (empty($h['MAKER_NAME']) && isset($current['TD_MAKER_ID'])) {
                            foreach ($makers as $m2) {
                                if ((int)$m2['MAKER_ID'] === (int)$current['TD_MAKER_ID']) {
                                    $h['MAKER_NAME'] = $m2['MAKER_NAME'];
                                    break;
                                }
                            }
                        }
                        // Fallback for tooling fields from current record
                        if ((!isset($h['TD_MIN_QTY']) || $h['TD_MIN_QTY'] === null) && isset($current['TD_MIN_QTY'])) {
                            $h['TD_MIN_QTY'] = (int)$current['TD_MIN_QTY'];
                        }
                        if ((!isset($h['TD_REPLENISH_QTY']) || $h['TD_REPLENISH_QTY'] === null) && isset($current['TD_REPLENISH_QTY'])) {
                            $h['TD_REPLENISH_QTY'] = (int)$current['TD_REPLENISH_QTY'];
                        }
                        if ((!isset($h['TD_PRICE']) || $h['TD_PRICE'] === null) && isset($current['TD_PRICE'])) {
                            $h['TD_PRICE'] = (float)$current['TD_PRICE'];
                        }
                        if ((!isset($h['TD_TOOL_LIFE']) || $h['TD_TOOL_LIFE'] === null) && isset($current['TD_TOOL_LIFE'])) {
                            $h['TD_TOOL_LIFE'] = (int)$current['TD_TOOL_LIFE'];
                        }
                        if ((!isset($h['TD_DESCRIPTION']) || $h['TD_DESCRIPTION'] === null) && isset($current['TD_DESCRIPTION'])) {
                            $h['TD_DESCRIPTION'] = $current['TD_DESCRIPTION'];
                        }
                    }
                }
                // Ensure tooling numeric fields are present and normalized (avoid empty/null in JSON)
                // Only set to 0 if truly null/empty, preserve existing values (including 0)
                if (!isset($h['TD_MIN_QTY']) || $h['TD_MIN_QTY'] === null || $h['TD_MIN_QTY'] === '') {
                    $h['TD_MIN_QTY'] = 0;
                } else {
                    $h['TD_MIN_QTY'] = (int)$h['TD_MIN_QTY'];
                }
                if (!isset($h['TD_REPLENISH_QTY']) || $h['TD_REPLENISH_QTY'] === null || $h['TD_REPLENISH_QTY'] === '') {
                    $h['TD_REPLENISH_QTY'] = 0;
                } else {
                    $h['TD_REPLENISH_QTY'] = (int)$h['TD_REPLENISH_QTY'];
                }
                if (!isset($h['TD_PRICE']) || $h['TD_PRICE'] === null || $h['TD_PRICE'] === '') {
                    $h['TD_PRICE'] = 0.0;
                } else {
                    $h['TD_PRICE'] = (float)$h['TD_PRICE'];
                }
                if (!isset($h['TD_TOOL_LIFE']) || $h['TD_TOOL_LIFE'] === null || $h['TD_TOOL_LIFE'] === '') {
                    $h['TD_TOOL_LIFE'] = 0;
                } else {
                    $h['TD_TOOL_LIFE'] = (int)$h['TD_TOOL_LIFE'];
                }
                if (!isset($h['TD_DESCRIPTION']) || $h['TD_DESCRIPTION'] === null) {
                    $h['TD_DESCRIPTION'] = '';
                }

                // If MAKER_NAME still missing but we have TD_MAKER_ID, resolve from masters
                if ((empty($h['MAKER_NAME']) || $h['MAKER_NAME'] === null) && isset($h['TD_MAKER_ID']) && (int)$h['TD_MAKER_ID'] > 0) {
                    foreach ($makers as $m3) {
                        if ((int)$m3['MAKER_ID'] === (int)$h['TD_MAKER_ID']) {
                            $h['MAKER_NAME'] = $m3['MAKER_NAME'];
                            break;
                        }
                    }
                }
            }
            log_message('debug', '[get_history_by_id] returning engin history payload for TD_ID=' . $id);
            // Debug: log the first history row to help troubleshoot
            if (isset($history[0])) {
                log_message('debug', '[get_history_by_id] sample history row (first 500 chars): ' . substr(json_encode($history[0]), 0, 500));
            }
            echo json_encode(array('success' => true, 'data' => $history));
        } else {
            log_message('debug', '[get_history_by_id] no engin history for TD_ID=' . $id);
            echo json_encode(array('success' => false, 'message' => 'Tidak ada history untuk record ini.'));
        }
    }
}
