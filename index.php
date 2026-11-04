<?php
/**
 * SaNDS Lab & Al Rabeeh Dental Center Enterprise Document Portal & Security Engine
 * Project: Al Rabeeh Dental Center EMRS Proposal & Technical Architecture
 * Database: alrabeeh_track_db (MySQL 8.0+ / SQLite Fallback)
 * 
 * Features:
 *   - Mandatory Email Verification & 6-Digit OTP Delivery before any Document Access
 *   - Granular Access Tracking: Separate counters for HTML Reads and PDF Downloads
 *   - Comprehensive Audit Trail: User Email, Doc ID, Action (VIEW_HTML / DOWNLOAD_PDF), IP, Device, Location, Timestamp
 *   - 5-Year Persistent Device Fingerprint Cookie (Zero-friction subsequent visits)
 *   - Super Admin Analytics Dashboard for ajit@sandslab.com & projects@sandslab.com
 *   - Real-Time User Matrix & Stakeholder Management (Add, Edit, Deactivate, Revoke Devices)
 *   - Automated Route Interception for all HTML & PDF requests
 */

// Safe Session Save Path Setup
if (session_status() === PHP_SESSION_NONE) {
    $session_save_dir = __DIR__ . '/.sessions';
    if (!is_dir($session_save_dir)) {
        @mkdir($session_save_dir, 0700, true);
    }
    if (is_dir($session_save_dir) && is_writable($session_save_dir)) {
        @session_save_path($session_save_dir);
    } elseif (is_dir(sys_get_temp_dir()) && is_writable(sys_get_temp_dir())) {
        @session_save_path(sys_get_temp_dir());
    }
    @session_start();
}

// =========================================================================
// 1. UNIFIED DATABASE LAYER (MySQL InnoDB with SQLite Fallback)
// =========================================================================
require_once __DIR__ . '/db.php';

// =========================================================================
// 2. DOCUMENT ROUTING & METADATA REGISTRY
// =========================================================================
$routes = array(
    'SL-ARDC-EMRS-2026-V1' => array(
        'title'    => '★ MASTER PROPOSAL: Al Rabeeh Dental Center EMRS Custom Ecosystem Development',
        'html'     => 'Al_Rabeeh_EMRS_Detailed_Proposal.html',
        'pdf'      => 'Al_Rabeeh_EMRS_Detailed_Proposal.pdf',
        'status'   => 'Submitted & Ready for Sign-off',
        'timeline' => '6-Month Delivery (+ 1-Mo Deployment Buffer)',
        'scope'    => '11 Clinical Reception Modules, Custom .NET Smart CPR Driver & Offline Auto-Sync',
        'ba_ref'   => 'SL-ARDC-EMRS-2026-V1',
        'date'     => '31/10/2026',
        'desc'     => 'Comprehensive 11-module clinical reception ecosystem proposal for Al Rabeeh Dental Center (6 Bahrain Clinics). Features ReactJS + PHP MVC Microservices, RabbitMQ message queuing, .NET Smart CPR card reader driver, offline SQLite/IndexedDB auto-sync, 6-month accelerated sprint with 1-month pilot buffer, commercial terms (BD 8,400.000 total offer), and 100% client source code ownership.'
    ),
    'SL-ARDC-EMRS-INFRA-2026-V1' => array(
        'title'    => '★ TECHNICAL ADDENDUM: Cloud Hosting, In-House Infrastructure & Scope Demarcation',
        'html'     => 'Al_Rabeeh_EMRS_Cloud_Infrastructure_and_Scope_Addendum.html',
        'pdf'      => 'Al_Rabeeh_EMRS_Cloud_Infrastructure_and_Scope_Addendum.pdf',
        'status'   => 'Active Addendum & Clarification',
        'timeline' => 'Hosting Options & Technical Specifications',
        'scope'    => 'Vendor Cloud (BD 630/Yr) vs. In-House Rack Server & 6-Branch Access + Scope Boundaries',
        'ba_ref'   => 'SL-ARDC-EMRS-INFRA-2026-V1',
        'date'     => '04/11/2026',
        'desc'     => 'Formal technical addendum responding to Al Rabeeh Dental Center inquiry. Details Option 1: Vendor-Managed Cloud Hosting (3 vCPU / 12GB RAM @ BD 630/Yr), Option 2: Client In-House Physical Server (Dell PowerEdge/HPE ProLiant @ ~BD 3,700-4,300), 6-branch networking prerequisites (HQ Fiber, Static IP, FortiGate NGFW, IPsec VPN), strategic comparison matrix, and definitive scope boundary confirmation (100% of standard masters included in BD 8,400 proposal).'
    ),
    'ARDC-RESOURCE-PRICING-2026' => array(
        'title'    => 'EMRS Resource Cost & 12-Milestone Pricing Matrix',
        'html'     => 'Al_Rabeeh_Internal_Resource_Pricing.html',
        'pdf'      => 'Al_Rabeeh_Internal_Resource_Pricing.pdf',
        'status'   => 'Active Pricing Benchmark',
        'timeline' => '28 Dedicated Engineering Weeks (7.0 Mo)',
        'scope'    => 'Engineering Resource Rate Cards & Milestone Payment Gates',
        'ba_ref'   => 'ARDC-PRICING-001',
        'date'     => '31/10/2026',
        'desc'     => 'Detailed internal rate card breakdowns across 6 senior engineering designations (Project Lead, Senior Full-Stack React/.NET, PHP Microservices Engineer, QA Lead, UI/UX Designer, Executive Steering), 12-milestone payment schedule, and commercial margin governance.'
    ),
    'ARDC-EMRS-FLOWCHART-2026' => array(
        'title'    => 'Al Rabeeh EMRS Architectural Flow Chart (11 Modules & 12 Milestones)',
        'html'     => 'emrs_flow_chart.html',
        'pdf'      => 'Al_Rabeeh_EMRS_Architectural_Flowchart.pdf',
        'status'   => 'Architectural Specification',
        'timeline' => 'Full-Lifecycle Workflow',
        'scope'    => 'Visual Process Logic & Multi-Branch Dental Operations',
        'ba_ref'   => 'FLOW-CHART-001',
        'date'     => '31/10/2026',
        'desc'     => 'Interactive visual architecture flowchart showing front-office reception check-in, patient recall automation, dental charting, Smart CPR card reader integration, and offline-to-cloud data sync pipeline across 6 clinics.'
    ),
    'ARDC-NAV-SEQUENCE-2026' => array(
        'title'    => '11-Module Unified Navigation Sequence Flow Chart',
        'html'     => 'navigation_sequence_flow_chart.html',
        'pdf'      => 'Al_Rabeeh_Navigation_Sequence_Flowchart.pdf',
        'status'   => 'UI/UX Blueprint',
        'timeline' => 'Reception Workflow Sequence',
        'scope'    => 'Front-Office Screen Progression & Doctor Trigger Flow',
        'ba_ref'   => 'NAV-SEQ-001',
        'date'     => '31/10/2026',
        'desc'     => 'Step-by-step user journey and screen hierarchy mapping patient registration, appointment scheduling, treatment plans, lab coordination, insurance pre-authorization, and cash counter closure.'
    ),
    'ARDC-INTERNAL-REF-2026' => array(
        'title'         => 'Resource Cost, Executive Advisory & Complete Pricing Reference',
        'html'          => 'Internal_Resource_Pricing_Reference.html',
        'pdf'           => 'Internal_Resource_Pricing_Reference.pdf',
        'status'        => 'Executive Benchmark',
        'timeline'      => 'Master Reference',
        'scope'         => 'Consolidated Commercial Benchmark & Advisory Rules',
        'ba_ref'        => 'REF-PRICING-001',
        'date'          => '31/10/2026',
        'desc'          => 'Executive pricing reference manual and governance rules covering commercial milestone gates, risk buffers, third-party consultant exclusions, and technical compliance.',
        'restricted_to' => 'ajit@sandslab.com' // STRICTLY RESTRICTED TO ajit@sandslab.com ONLY
    ),
    'ARDC-SOFTWARE-BLUEPRINT' => array(
        'title'    => 'Reception Login Software Development Blueprint (35-Page Specification)',
        'html'     => 'Al_Rabeeh_EMRS_Detailed_Proposal.html',
        'pdf'      => 'EMRS-Software_Blueprint (1).pdf',
        'status'   => 'Client Specification',
        'timeline' => 'Original Blueprint',
        'scope'    => 'Client EMRS Reception Functional Requirement Document',
        'ba_ref'   => 'BLUEPRINT-REV-OCT26',
        'date'     => '19/10/2026',
        'desc'     => 'Original 35-page client-provided blueprint for Al Rabeeh Dental Center Reception Login Software Development (Part 1, Revised 19 October 2026).'
    )
);

// Secondary Aliases for direct file access matching
$aliases = array(
    'al_rabeeh_emrs_detailed_proposal' => 'SL-ARDC-EMRS-2026-V1',
    'al_rabeeh_emrs_cloud_infrastructure_and_scope_addendum' => 'SL-ARDC-EMRS-INFRA-2026-V1',
    'ardc-infra-addendum-2026' => 'SL-ARDC-EMRS-INFRA-2026-V1',
    'sl-ardc-emrs-infra-2026-v1' => 'SL-ARDC-EMRS-INFRA-2026-V1',
    'al_rabeeh_internal_resource_pricing' => 'ARDC-RESOURCE-PRICING-2026',
    'emrs_flow_chart' => 'ARDC-EMRS-FLOWCHART-2026',
    'navigation_sequence_flow_chart' => 'ARDC-NAV-SEQUENCE-2026',
    'internal_resource_pricing_reference' => 'ARDC-INTERNAL-REF-2026',
    'emrs-software_blueprint' => 'ARDC-SOFTWARE-BLUEPRINT',
    'emrs-software_blueprint (1)' => 'ARDC-SOFTWARE-BLUEPRINT',
    'al_rabeeh_emrs_architectural_flowchart' => 'ARDC-EMRS-FLOWCHART-2026',
    'al_rabeeh_navigation_sequence_flowchart' => 'ARDC-NAV-SEQUENCE-2026'
);

// Helper to resolve canonical document ID
function resolve_doc_id($raw_id) {
    global $routes, $aliases;
    if (empty($raw_id)) return '';
    $clean = trim($raw_id);
    $clean_no_ext = preg_replace('/\.(html|pdf)$/i', '', $clean);
    
    if (isset($routes[$clean])) return $clean;
    if (isset($routes[$clean_no_ext])) return $clean_no_ext;
    
    $lower = strtolower($clean_no_ext);
    if (isset($aliases[$lower])) return $aliases[$lower];
    
    // Fuzzy matching against routes keys
    foreach ($routes as $k => $info) {
        if (strcasecmp($k, $clean_no_ext) === 0 || strcasecmp($info['html'], $clean) === 0 || strcasecmp($info['pdf'], $clean) === 0) {
            return $k;
        }
    }
    return '';
}

// =========================================================================
// 3. HELPER FUNCTIONS: DEVICE DETECTION, IP GEOLOCATION & AUDIT LOGGING
// =========================================================================
$secret_salt = defined('PORTAL_SECRET_SALT') ? PORTAL_SECRET_SALT : 'SaNDS_Lab_Secured_Token_Key_2026_EMRS_AlRabeeh';

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
}

function parse_device_info($ua) {
    if (empty($ua)) return 'Desktop Browser';
    $os = 'Unknown OS';
    $browser = 'Unknown Browser';
    
    if (preg_match('/windows nt 10/i', $ua)) $os = 'Windows 10/11';
    elseif (preg_match('/windows nt/i', $ua)) $os = 'Windows';
    elseif (preg_match('/iphone/i', $ua)) $os = 'iPhone (iOS)';
    elseif (preg_match('/ipad/i', $ua)) $os = 'iPad (iPadOS)';
    elseif (preg_match('/android/i', $ua)) $os = 'Android Device';
    elseif (preg_match('/macintosh|mac os x/i', $ua)) $os = 'macOS (Apple)';
    elseif (preg_match('/linux/i', $ua)) $os = 'Linux';
    
    if (preg_match('/edg/i', $ua)) $browser = 'Edge';
    elseif (preg_match('/chrome|crios/i', $ua)) $browser = 'Chrome';
    elseif (preg_match('/firefox|fxios/i', $ua)) $browser = 'Firefox';
    elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome|crios/i', $ua)) $browser = 'Safari';
    elseif (preg_match('/opera|opr/i', $ua)) $browser = 'Opera';
    
    if (preg_match('/mobile|iphone|android/i', $ua)) {
        return '📱 Mobile (' . $os . ' • ' . $browser . ')';
    } elseif (preg_match('/tablet|ipad/i', $ua)) {
        return '📱 Tablet (' . $os . ' • ' . $browser . ')';
    } else {
        return '💻 Desktop (' . $os . ' • ' . $browser . ')';
    }
}

function get_ip_location($ip) {
    global $pdo;
    if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0 || strpos($ip, '172.16.') === 0) {
        return 'Local Network (Kingdom of Bahrain)';
    }
    
    if (isset($_SESSION['ip_loc_' . md5($ip)])) {
        return $_SESSION['ip_loc_' . md5($ip)];
    }
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT location FROM ip_cache WHERE ip = ?");
            $stmt->execute(array($ip));
            $cached = $stmt->fetchColumn();
            if ($cached) {
                $_SESSION['ip_loc_' . md5($ip)] = $cached;
                return $cached;
            }
        } catch (Exception $e) {}
    }
    
    $loc = 'Kingdom of Bahrain';
    $ctx = stream_context_create(array('http' => array('timeout' => 0.5)));
    $res = @file_get_contents('http://ip-api.com/json/' . urlencode($ip) . '?fields=status,country,city,countryCode', false, $ctx);
    if ($res) {
        $data = @json_decode($res, true);
        if ($data && isset($data['status']) && $data['status'] === 'success') {
            $city = !empty($data['city']) ? $data['city'] : '';
            $country = !empty($data['country']) ? $data['country'] : '';
            $code = !empty($data['countryCode']) ? $data['countryCode'] : '';
            if ($city && $country) {
                $loc = $city . ', ' . $country . ' (' . $code . ')';
            } elseif ($country) {
                $loc = $country . ' (' . $code . ')';
            }
        }
    }
    
    if ($pdo) {
        try {
            $now_str = date('Y-m-d H:i:s');
            $check = $pdo->prepare("SELECT ip FROM ip_cache WHERE ip = ?");
            $check->execute(array($ip));
            if ($check->fetchColumn()) {
                $ins = $pdo->prepare("UPDATE ip_cache SET location = ?, cached_at = ? WHERE ip = ?");
                $ins->execute(array($loc, $now_str, $ip));
            } else {
                $ins = $pdo->prepare("INSERT INTO ip_cache (ip, location, cached_at) VALUES (?, ?, ?)");
                $ins->execute(array($ip, $loc, $now_str));
            }
        } catch (Exception $e) {}
    }
    
    $_SESSION['ip_loc_' . md5($ip)] = $loc;
    return $loc;
}

function send_enterprise_email($to, $user_name, $otp) {
    $ref_id = substr(md5($otp . time()), 0, 6);
    $subject = "Your Verification Code: $otp [Ref #$ref_id] - SaNDS Lab & Al Rabeeh Dental Center";
    $from_email = 'no-reply@sandslab.com';
    $msg_id = sprintf("<%s.%s@sandslab.com>", time(), mt_rand(10000, 99999));

    $headers_arr = array(
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: SaNDS Lab Security <' . $from_email . '>',
        'Reply-To: support@sandslab.com',
        'Return-Path: <' . $from_email . '>',
        'Date: ' . date(DATE_RFC2822),
        'Message-ID: ' . $msg_id,
        'X-Priority: 1 (Highest)',
        'Importance: High',
        'Auto-Submitted: auto-generated',
        'X-Mailer: PHP/' . phpversion()
    );
    $headers = implode("\r\n", $headers_arr);

    $email_body = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verification Code</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background-color: #f4f7fa; margin: 0; padding: 30px;">
  <table align="center" border="0" cellpadding="0" cellspacing="0" width="560" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border-top: 5px solid #ea580c; border-collapse: separate;">
    <tr>
      <td style="background-color: #07192c; padding: 25px 30px; text-align: center;">
        <h2 style="color: #ffffff; margin: 0; font-size: 20px; letter-spacing: 0.5px;">SaNDS Lab &bull; Enterprise Document Portal</h2>
        <div style="color: #ea580c; font-size: 11.5px; font-weight: bold; text-transform: uppercase; margin-top: 5px; letter-spacing: 1px;">Al Rabeeh Dental Center EMRS Proposal &amp; Technical Architecture</div>
      </td>
    </tr>
    <tr>
      <td style="padding: 35px 35px 25px;">
        <p style="font-size: 15px; color: #334155; margin-top: 0;">Hello <strong>' . htmlspecialchars($user_name) . '</strong>,</p>
        <p style="font-size: 14px; color: #475569; line-height: 1.6;">You requested secure access to the <strong>Al Rabeeh Dental Center</strong> EMRS Proposal &amp; Technical Blueprint Document Repository. Use the 6-digit verification code below to authorize this device:</p>
        
        <div style="background-color: #fff7ed; border: 2px dashed #ea580c; border-radius: 8px; padding: 18px; text-align: center; margin: 25px 0;">
          <span style="font-size: 34px; font-weight: bold; letter-spacing: 8px; color: #0a2540; font-family: monospace;">' . $otp . '</span>
        </div>
        
        <p style="font-size: 12.5px; color: #64748b; line-height: 1.5;">This verification code is valid for <strong>15 minutes</strong>. Once verified, this device will remain permanently authenticated for seamless access.</p>
        <p style="font-size: 12.5px; color: #e11d48; margin-bottom: 0;">If you did not request this verification code, please ignore this email or notify security at support@sandslab.com.</p>
      </td>
    </tr>
    <tr>
      <td style="background-color: #f8fafc; padding: 18px 35px; border-top: 1px solid #e2e8f0; text-align: center; font-size: 11px; color: #94a3b8;">
        SaNDS Lab Middle East W.L.L &bull; Salmabad, Kingdom of Bahrain &bull; Hotline: +973 35 078 079 &bull; ISO 9001:2015 Certified
      </td>
    </tr>
  </table>
</body>
</html>';

    $additional_params = "-f " . escapeshellarg($from_email);
    $sent = @mail($to, $subject, $email_body, $headers, $additional_params);
    if ($sent) return true;
    return @mail($to, $subject, $email_body, $headers);
}

// LOGGING ENGINE: TRACKS BOTH HTML VIEWS AND PDF DOWNLOADS
function log_document_access($email, $doc_id, $doc_title, $action = 'VIEW_HTML') {
    global $pdo;
    if (!$pdo || empty($email)) return;
    try {
        $ip  = get_client_ip();
        $ua  = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $dev = parse_device_info($ua);
        $loc = get_ip_location($ip);
        
        $stmt = $pdo->prepare("INSERT INTO document_access_logs (email, doc_id, doc_title, action_type, ip_address, device_name, user_agent, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute(array($email, $doc_id, $doc_title, $action, $ip, $dev, $ua, $loc));
    } catch (Exception $e) {
        error_log("Audit Log Error: " . $e->getMessage());
    }
}

function get_device_fingerprint($email) {
    global $secret_salt;
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'generic_browser';
    return hash('sha256', strtolower($email) . '|' . $secret_salt . '|' . $ua);
}

function is_device_authenticated() {
    global $pdo;
    if (isset($_SESSION['authenticated_user']) && !empty($_SESSION['authenticated_user'])) {
        return $_SESSION['authenticated_user'];
    }
    
    if (isset($_COOKIE['sands_auth_device']) && !empty($_COOKIE['sands_auth_device'])) {
        $token = $_COOKIE['sands_auth_device'];
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT u.email, u.is_active FROM authenticated_devices d JOIN authorized_users u ON LOWER(d.email) = LOWER(u.email) WHERE d.device_token = ?");
            $stmt->execute(array($token));
            $row = $stmt->fetch();
            if ($row && $row['is_active'] == 1) {
                $_SESSION['authenticated_user'] = $row['email'];
                return $row['email'];
            }
        }
    }
    return false;
}

function register_authenticated_device($email) {
    global $pdo;
    $email = strtolower(trim($email));
    $token = get_device_fingerprint($email);
    $ua    = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $ip    = get_client_ip();
    $dev   = parse_device_info($ua);
    $loc   = get_ip_location($ip);
    
    // Set 5-year persistent cookie
    setcookie('sands_auth_device', $token, time() + (86400 * 365 * 5), '/', '', false, true);
    
    if ($pdo) {
        try {
            $now_str = date('Y-m-d H:i:s');
            $check = $pdo->prepare("SELECT id FROM authenticated_devices WHERE device_token = ?");
            $check->execute(array($token));
            if ($check->fetchColumn()) {
                $stmt = $pdo->prepare("UPDATE authenticated_devices SET email = ?, device_name = ?, user_agent = ?, ip_address = ?, location = ?, verified_at = ? WHERE device_token = ?");
                $stmt->execute(array($email, $dev, $ua, $ip, $loc, $now_str, $token));
            } else {
                $stmt = $pdo->prepare("INSERT INTO authenticated_devices (email, device_token, device_name, user_agent, ip_address, location, verified_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute(array($email, $token, $dev, $ua, $ip, $loc, $now_str));
            }
        } catch (Exception $e) {
            error_log("Device Registration Error: " . $e->getMessage());
        }
    }
    
    $_SESSION['authenticated_user'] = $email;
    unset($_SESSION['otp_code']);
    unset($_SESSION['otp_email']);
    unset($_SESSION['otp_time']);
    
    // Log Login Event
    log_document_access($email, 'LOGIN', 'Successful OTP Verification & Device Registered', 'LOGIN_VERIFIED');
}

// Handle Logout / User Switching
if (isset($_GET['logout']) || isset($_GET['switch_user']) || isset($_GET['reset'])) {
    if ($pdo && isset($_COOKIE['sands_auth_device'])) {
        $token = $_COOKIE['sands_auth_device'];
        $stmt = $pdo->prepare("DELETE FROM authenticated_devices WHERE device_token = ?");
        $stmt->execute(array($token));
        setcookie('sands_auth_device', '', time() - 3600, '/', '', false, true);
        unset($_COOKIE['sands_auth_device']);
    }
    $_SESSION = array();
    if (session_id()) {
        @session_destroy();
    }
    $target_redirect = strtok($_SERVER["REQUEST_URI"], '?');
    header('Location: ' . $target_redirect);
    exit;
}

// =========================================================================
// 4. SUPER ADMIN USER MANAGEMENT HANDLERS (ajit@sandslab.com)
// =========================================================================
$authenticated_user = is_device_authenticated();
$is_super_admin = ($authenticated_user && (
    strtolower($authenticated_user) === 'ajit@sandslab.com' || 
    strtolower($authenticated_user) === 'projects@sandslab.com'
));

$admin_msg = '';
$admin_error = '';

if ($is_super_admin && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action'])) {
    $action = $_POST['admin_action'];
    
    // 1. ADD NEW USER
    if ($action === 'add_user') {
        $new_email = strtolower(trim($_POST['new_email']));
        $new_name  = trim($_POST['new_name']);
        $new_org   = trim($_POST['new_org']);
        $new_role  = trim($_POST['new_role']);
        $is_act    = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $admin_error = 'Please enter a valid email address.';
        } elseif (empty($new_name) || empty($new_org)) {
            $admin_error = 'Please enter both Full Name and Organization.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO authorized_users (email, full_name, organization, role, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute(array($new_email, $new_name, $new_org, $new_role, $is_act));
                $admin_msg = 'Authorized Stakeholder <strong>' . htmlspecialchars($new_email) . '</strong> added successfully!';
            } catch (Exception $e) {
                $admin_error = 'Error adding user: Email may already exist in registry.';
            }
        }
    }
    
    // 2. UPDATE USER
    if ($action === 'edit_user') {
        $user_id   = intval($_POST['user_id']);
        $up_name   = trim($_POST['edit_name']);
        $up_org    = trim($_POST['edit_org']);
        $up_role   = trim($_POST['edit_role']);
        $up_email  = strtolower(trim($_POST['edit_email']));
        
        try {
            $stmt = $pdo->prepare("UPDATE authorized_users SET full_name = ?, organization = ?, role = ?, email = ? WHERE id = ?");
            $stmt->execute(array($up_name, $up_org, $up_role, $up_email, $user_id));
            $admin_msg = 'Stakeholder details updated successfully!';
        } catch (Exception $e) {
            $admin_error = 'Error updating stakeholder details.';
        }
    }
    
    // 3. TOGGLE ACTIVE / DEACTIVATE STATUS
    if ($action === 'toggle_status') {
        $user_id   = intval($_POST['user_id']);
        $curr_stat = intval($_POST['current_status']);
        $new_stat  = ($curr_stat == 1) ? 0 : 1;
        
        try {
            $stmt = $pdo->prepare("UPDATE authorized_users SET is_active = ? WHERE id = ?");
            $stmt->execute(array($new_stat, $user_id));
            
            if ($new_stat == 0) {
                $get_mail = $pdo->prepare("SELECT email FROM authorized_users WHERE id = ?");
                $get_mail->execute(array($user_id));
                $target_mail = $get_mail->fetchColumn();
                if ($target_mail) {
                    $del_dev = $pdo->prepare("DELETE FROM authenticated_devices WHERE LOWER(email) = LOWER(?)");
                    $del_dev->execute(array($target_mail));
                }
            }
            $admin_msg = 'Stakeholder access status updated successfully!';
        } catch (Exception $e) {
            $admin_error = 'Error changing status.';
        }
    }
    
    // 4. REVOKE DEVICE TOKENS (FORCE RE-AUTHENTICATION)
    if ($action === 'revoke_devices') {
        $target_email = strtolower(trim($_POST['user_email']));
        try {
            $stmt = $pdo->prepare("DELETE FROM authenticated_devices WHERE LOWER(email) = LOWER(?)");
            $stmt->execute(array($target_email));
            $admin_msg = 'All remembered devices for <strong>' . htmlspecialchars($target_email) . '</strong> have been revoked.';
        } catch (Exception $e) {
            $admin_error = 'Error revoking devices.';
        }
    }
    
    // 5. DELETE USER
    if ($action === 'delete_user') {
        $user_id = intval($_POST['user_id']);
        try {
            $get_mail = $pdo->prepare("SELECT email FROM authorized_users WHERE id = ?");
            $get_mail->execute(array($user_id));
            $target_mail = $get_mail->fetchColumn();
            
            if ($target_mail && (strtolower($target_mail) === 'ajit@sandslab.com' || strtolower($target_mail) === 'projects@sandslab.com')) {
                $admin_error = 'Action Prohibited: Core Super Admin accounts cannot be deleted.';
            } else {
                $stmt = $pdo->prepare("DELETE FROM authorized_users WHERE id = ?");
                $stmt->execute(array($user_id));
                if ($target_mail) {
                    $del_dev = $pdo->prepare("DELETE FROM authenticated_devices WHERE LOWER(email) = LOWER(?)");
                    $del_dev->execute(array($target_mail));
                }
                $admin_msg = 'Stakeholder deleted from authorized directory.';
            }
        } catch (Exception $e) {
            $admin_error = 'Error deleting stakeholder.';
        }
    }
}

// =========================================================================
// 5. AUTHENTICATION CONTROLLER & OTP DISPATCH / VERIFICATION
// =========================================================================
$auth_error = '';
$auth_success = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auth_action'])) {
    $auth_action = $_POST['auth_action'];
    
    // STEP 1: SEND OTP
    if ($auth_action === 'send_otp') {
        $input_email = strtolower(trim($_POST['email']));
        if (empty($input_email) || !filter_var($input_email, FILTER_VALIDATE_EMAIL)) {
            $auth_error = 'Please enter a valid corporate email address.';
        } else {
            $user_data = null;
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT * FROM authorized_users WHERE LOWER(email) = LOWER(?)");
                $stmt->execute(array($input_email));
                $user_data = $stmt->fetch();
            }
            
            if (!$user_data) {
                $auth_error = 'Access Restricted: <strong>' . htmlspecialchars($input_email) . '</strong> is not registered in the Al Rabeeh Dental Center Authorized Stakeholder Directory. Please contact SaNDS Lab Super Admin at <a href="mailto:ajit@sandslab.com" style="color:#ea580c; font-weight:bold;">ajit@sandslab.com</a>.';
            } elseif ($user_data['is_active'] != 1) {
                $auth_error = 'Account Suspended: Your stakeholder access has been temporarily deactivated. Please contact SaNDS Lab administration.';
            } else {
                // Generate 6-digit OTP code
                $otp = sprintf("%06d", mt_rand(100000, 999999));
                $_SESSION['otp_code']  = $otp;
                $_SESSION['otp_email'] = $input_email;
                $_SESSION['otp_time']  = time();
                $_SESSION['otp_name']  = $user_data['full_name'];
                
                // Log OTP dispatch
                if ($pdo) {
                    try {
                        $ip  = get_client_ip();
                        $loc = get_ip_location($ip);
                        $stmt = $pdo->prepare("INSERT INTO otp_logs (email, otp_code, ip_address, location) VALUES (?, ?, ?, ?)");
                        $stmt->execute(array($input_email, $otp, $ip, $loc));
                    } catch (Exception $e) {}
                }
                
                // Dispatch corporate email
                $mail_sent = send_enterprise_email($input_email, $user_data['full_name'], $otp);
                
                $auth_success = 'A 6-digit verification code has been dispatched to <strong>' . htmlspecialchars($input_email) . '</strong>. Please check your inbox (and spam folder).';
                
                // Developer / Local Environment Fallback Notice
                if (get_client_ip() === '127.0.0.1' || get_client_ip() === '::1' || strpos(get_client_ip(), '192.168.') === 0) {
                    $_SESSION['dev_otp_hint'] = $otp;
                }
            }
        }
    }
    
    // STEP 2: VERIFY OTP
    if ($auth_action === 'verify_otp') {
        $entered_otp = trim($_POST['otp_code']);
        $otp_email   = isset($_SESSION['otp_email']) ? $_SESSION['otp_email'] : '';
        $cached_otp  = isset($_SESSION['otp_code']) ? $_SESSION['otp_code'] : '';
        $otp_time    = isset($_SESSION['otp_time']) ? $_SESSION['otp_time'] : 0;
        
        if (empty($entered_otp) || strlen($entered_otp) !== 6) {
            $auth_error = 'Please enter the complete 6-digit verification code.';
        } elseif (empty($otp_email) || empty($cached_otp)) {
            $auth_error = 'Verification session expired. Please enter your email address again.';
            unset($_SESSION['otp_code']);
            unset($_SESSION['otp_email']);
        } elseif ((time() - $otp_time) > 900) { // 15 Minutes
            $auth_error = 'Verification code has expired (valid for 15 minutes). Please request a new code.';
            unset($_SESSION['otp_code']);
            unset($_SESSION['otp_email']);
        } elseif ($entered_otp !== $cached_otp) {
            $auth_error = 'Invalid verification code. Please check your email and try again.';
        } else {
            // SUCCESSFUL AUTHENTICATION
            register_authenticated_device($otp_email);
            unset($_SESSION['dev_otp_hint']);
            
            // Redirect back to intended document or current clean URL
            if (isset($_SESSION['intended_url']) && !empty($_SESSION['intended_url'])) {
                $dest = $_SESSION['intended_url'];
                unset($_SESSION['intended_url']);
                header('Location: ' . $dest);
                exit;
            }
            
            $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
            if (isset($_GET['doc']) && !empty($_GET['doc'])) {
                $redirect_url .= '?doc=' . urlencode($_GET['doc']);
            }
            header('Location: ' . $redirect_url);
            exit;
        }
    }
}

// Resend OTP trigger
if (isset($_GET['resend_otp']) && isset($_SESSION['otp_email'])) {
    $resend_email = $_SESSION['otp_email'];
    $user_data = null;
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM authorized_users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute(array($resend_email));
        $user_data = $stmt->fetch();
    }
    if ($user_data && $user_data['is_active'] == 1) {
        $otp = sprintf("%06d", mt_rand(100000, 999999));
        $_SESSION['otp_code'] = $otp;
        $_SESSION['otp_time'] = time();
        send_enterprise_email($resend_email, $user_data['full_name'], $otp);
        if (get_client_ip() === '127.0.0.1' || get_client_ip() === '::1') {
            $_SESSION['dev_otp_hint'] = $otp;
        }
        $auth_success = 'A fresh 6-digit verification code has been re-dispatched to <strong>' . htmlspecialchars($resend_email) . '</strong>.';
    }
}

// =========================================================================
// 6. ENFORCE AUTHENTICATION GATE (RENDER AUTH SCREEN IF NOT LOGGED IN)
// =========================================================================
$authenticated_user = is_device_authenticated();

if (!$authenticated_user) {
    // Save intended destination for post-auth redirect
    if (!isset($_SESSION['intended_url']) && !empty($_SERVER['REQUEST_URI'])) {
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
    }
    
    // Render Corporate Authentication Screen
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stakeholder Authentication &bull; Al Rabeeh Dental Center EMRS Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #0a2540;
      --primary-dark: #07192c;
      --accent: #ea580c;
      --accent-hover: #c2410c;
      --gray-900: #0f172a;
      --gray-700: #334155;
      --gray-500: #64748b;
      --gray-200: #e2e8f0;
      --gray-100: #f1f5f9;
      --white: #ffffff;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background: radial-gradient(circle at 10% 20%, #07192c 0%, #0a2540 90%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      color: var(--gray-700);
    }
    .auth-card {
      background: var(--white);
      border-radius: 16px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
      width: 100%;
      max-width: 520px;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    .auth-header {
      background: linear-gradient(135deg, #07192c 0%, #0f2742 100%);
      padding: 30px 35px 25px;
      text-align: center;
      border-bottom: 4px solid var(--accent);
      position: relative;
    }
    .auth-logos {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 16px;
      margin-bottom: 18px;
    }
    .auth-logo-img {
      height: 48px;
      width: auto;
      background: #ffffff;
      padding: 6px 12px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .auth-header h1 {
      font-family: 'Outfit', sans-serif;
      font-size: 20px;
      color: #ffffff;
      font-weight: 700;
      letter-spacing: 0.3px;
      margin-bottom: 4px;
    }
    .auth-header p {
      font-size: 12px;
      color: #94a3b8;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      font-weight: 600;
    }
    .auth-body {
      padding: 35px;
    }
    .alert {
      padding: 14px 16px;
      border-radius: 8px;
      font-size: 13px;
      line-height: 1.5;
      margin-bottom: 22px;
    }
    .alert-error {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #991b1b;
    }
    .alert-success {
      background: #f0fdf4;
      border: 1px solid #bbf7d0;
      color: #166534;
    }
    .alert-hint {
      background: #fffbeb;
      border: 1px solid #fde68a;
      color: #92400e;
      font-size: 12px;
      margin-top: 15px;
      border-radius: 6px;
      padding: 10px 14px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-label {
      display: block;
      font-size: 12.5px;
      font-weight: 600;
      color: var(--gray-700);
      margin-bottom: 8px;
    }
    .form-input {
      width: 100%;
      padding: 13px 16px;
      border: 1.5px solid var(--gray-200);
      border-radius: 8px;
      font-size: 15px;
      color: var(--gray-900);
      transition: all 0.2s;
    }
    .form-input:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.15);
    }
    .otp-input {
      font-family: 'JetBrains Mono', monospace;
      font-size: 26px;
      letter-spacing: 10px;
      text-align: center;
      font-weight: 700;
      color: var(--primary);
    }
    .btn-submit {
      width: 100%;
      padding: 14px;
      background: var(--accent);
      color: #ffffff;
      border: none;
      border-radius: 8px;
      font-size: 14.5px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: background 0.2s, transform 0.1s;
    }
    .btn-submit:hover {
      background: var(--accent-hover);
    }
    .btn-submit:active {
      transform: scale(0.99);
    }
    .auth-footer-nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 18px;
      font-size: 12.5px;
    }
    .auth-footer-nav a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 600;
    }
    .auth-footer-nav a:hover {
      text-decoration: underline;
    }
    .security-badge {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 14px;
      background: #f8fafc;
      border-top: 1px solid var(--gray-200);
      font-size: 11.5px;
      color: var(--gray-500);
      font-weight: 500;
    }
  </style>
</head>
<body>

  <div class="auth-card">
    <div class="auth-header">
      <div class="auth-logos">
        <img src="Logo/AlRabeeh-Logo.png" alt="Al Rabeeh Dental Center" class="auth-logo-img">
        <img src="Logo/SaNDSLab-LogoNewUpdated copy.png" alt="SaNDS Lab" class="auth-logo-img">
      </div>
      <h1>Al Rabeeh Dental Center</h1>
      <p>EMRS Proposal &bull; 256-Bit Authenticated Access</p>
    </div>

    <div class="auth-body">
      <?php if (!empty($auth_error)): ?>
        <div class="alert alert-error">
          <i class="fa-solid fa-circle-exclamation"></i> <?php echo $auth_error; ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($auth_success)): ?>
        <div class="alert alert-success">
          <i class="fa-solid fa-circle-check"></i> <?php echo $auth_success; ?>
        </div>
      <?php endif; ?>

      <?php if (!isset($_SESSION['otp_code'])): ?>
        <!-- STEP 1: ENTER EMAIL -->
        <p style="font-size: 13.5px; color: var(--gray-600); margin-bottom: 22px; line-height: 1.5;">
          This repository contains confidential technical specifications, milestone pricing, and architectural blueprints. Enter your registered corporate email address to receive a 6-digit access code:
        </p>
        <form method="POST">
          <input type="hidden" name="auth_action" value="send_otp">
          <div class="form-group">
            <label class="form-label">Stakeholder Corporate Email</label>
            <input type="email" name="email" class="form-input" placeholder="e.g. director@alrabeeh.bh" required autofocus>
          </div>
          <button type="submit" class="btn-submit">
            <span>Send 6-Digit Verification Code</span>
            <i class="fa-solid fa-paper-plane"></i>
          </button>
        </form>
      <?php else: ?>
        <!-- STEP 2: ENTER OTP -->
        <p style="font-size: 13.5px; color: var(--gray-600); margin-bottom: 20px; line-height: 1.5;">
          Enter the 6-digit code dispatched to <strong><?php echo htmlspecialchars($_SESSION['otp_email']); ?></strong>:
        </p>
        <form method="POST">
          <input type="hidden" name="auth_action" value="verify_otp">
          <div class="form-group">
            <label class="form-label">6-Digit Verification Code</label>
            <input type="text" name="otp_code" class="form-input otp-input" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="------" required autofocus>
          </div>
          <button type="submit" class="btn-submit">
            <span>Authorize &amp; Remember Device</span>
            <i class="fa-solid fa-shield-halved"></i>
          </button>
        </form>

        <div class="auth-footer-nav">
          <a href="?resend_otp=1"><i class="fa-solid fa-rotate-right"></i> Resend Code</a>
          <a href="?reset=1"><i class="fa-solid fa-arrow-left"></i> Change Email</a>
        </div>

        <?php if (isset($_SESSION['dev_otp_hint'])): ?>
          <div class="alert-hint">
            <strong><i class="fa-solid fa-laptop-code"></i> Local Dev Mode:</strong> Verification code is <code><?php echo htmlspecialchars($_SESSION['dev_otp_hint']); ?></code>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <div class="security-badge">
      <i class="fa-solid fa-lock" style="color: var(--accent);"></i>
      Enterprise AES-256 Encrypted Multi-Device Authentication Engine
    </div>
  </div>

</body>
</html>
    <?php
    exit;
}

// =========================================================================
// 7. SUPER ADMIN USER MANAGEMENT & ANALYTICS DASHBOARD (ajit@sandslab.com)
// =========================================================================
if ($is_super_admin && isset($_GET['view']) && $_GET['view'] === 'admin') {
    $all_users = array();
    $all_devices = array();
    $all_logs = array();
    $doc_stats = array();
    $user_matrix = array();
    $total_doc_views = 0;
    $total_doc_downloads = 0;
    
    if ($pdo) {
        $users_stmt = $pdo->query("SELECT * FROM authorized_users ORDER BY id ASC");
        $all_users = $users_stmt ? $users_stmt->fetchAll() : array();
        
        $dev_stmt = $pdo->query("SELECT * FROM authenticated_devices ORDER BY last_active DESC");
        $all_devices = $dev_stmt ? $dev_stmt->fetchAll() : array();
        
        $logs_stmt = $pdo->query("SELECT * FROM document_access_logs ORDER BY accessed_at DESC LIMIT 500");
        $all_logs = $logs_stmt ? $logs_stmt->fetchAll() : array();

        // Document Access Aggregation (HTML Reads vs PDF Downloads)
        $doc_stats = $pdo->query("SELECT doc_id, doc_title, 
                                        COUNT(*) as total_actions,
                                        SUM(CASE WHEN action_type = 'VIEW_HTML' THEN 1 ELSE 0 END) as total_reads,
                                        SUM(CASE WHEN action_type = 'DOWNLOAD_PDF' THEN 1 ELSE 0 END) as total_downloads,
                                        COUNT(DISTINCT email) as unique_users,
                                        MAX(accessed_at) as last_accessed
                                 FROM document_access_logs 
                                 WHERE action_type IN ('VIEW_HTML', 'DOWNLOAD_PDF')
                                 GROUP BY doc_id
                                 ORDER BY total_actions DESC")->fetchAll();
        
        // Stakeholder Engagement Matrix
        $user_matrix = $pdo->query("SELECT u.id, u.email, u.full_name, u.organization, u.role, u.is_active,
                                           COUNT(d.id) as total_events,
                                           SUM(CASE WHEN d.action_type = 'VIEW_HTML' THEN 1 ELSE 0 END) as total_reads,
                                           SUM(CASE WHEN d.action_type = 'DOWNLOAD_PDF' THEN 1 ELSE 0 END) as total_downloads,
                                           MAX(d.accessed_at) as last_activity,
                                           (SELECT device_name FROM document_access_logs WHERE LOWER(email) = LOWER(u.email) ORDER BY accessed_at DESC LIMIT 1) as last_device,
                                           (SELECT location FROM document_access_logs WHERE LOWER(email) = LOWER(u.email) ORDER BY accessed_at DESC LIMIT 1) as last_location
                                    FROM authorized_users u
                                    LEFT JOIN document_access_logs d ON LOWER(u.email) = LOWER(d.email) AND d.action_type IN ('VIEW_HTML', 'DOWNLOAD_PDF')
                                    GROUP BY u.id
                                    ORDER BY total_events DESC, u.id ASC")->fetchAll();
                                    
        $tot_reads_stmt = $pdo->query("SELECT COUNT(*) FROM document_access_logs WHERE action_type = 'VIEW_HTML'");
        $total_doc_views = $tot_reads_stmt ? $tot_reads_stmt->fetchColumn() : 0;

        $tot_dl_stmt = $pdo->query("SELECT COUNT(*) FROM document_access_logs WHERE action_type = 'DOWNLOAD_PDF'");
        $total_doc_downloads = $tot_dl_stmt ? $tot_dl_stmt->fetchColumn() : 0;
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Super Admin &bull; Document Tracking &amp; Analytics &bull; Al Rabeeh EMRS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <style>
    :root {
      --primary: #0a2540;
      --primary-dark: #07192c;
      --accent: #ea580c;
      --emerald: #059669;
      --blue: #0284c7;
      --purple: #7c3aed;
      --gray-900: #0f172a;
      --gray-800: #1e293b;
      --gray-700: #334155;
      --gray-600: #475569;
      --gray-500: #64748b;
      --gray-200: #e2e8f0;
      --gray-100: #f1f5f9;
      --white: #ffffff;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background: #f8fafc;
      color: var(--gray-700);
      font-size: 13.5px;
      line-height: 1.5;
    }
    .admin-navbar {
      background: #07192c;
      border-bottom: 3px solid var(--accent);
      padding: 14px 30px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      color: #fff;
    }
    .admin-nav-left {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .admin-nav-title {
      font-family: 'Outfit', sans-serif;
      font-size: 17px;
      font-weight: 700;
      color: #ffffff;
    }
    .admin-nav-sub {
      font-size: 11.5px;
      color: #94a3b8;
    }
    .admin-nav-right {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .btn-nav {
      padding: 7px 14px;
      border-radius: 6px;
      font-size: 12.5px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-portal {
      background: rgba(255, 255, 255, 0.12);
      color: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .btn-portal:hover { background: rgba(255, 255, 255, 0.25); }
    .btn-logout {
      background: #dc2626;
      color: #ffffff;
    }
    .btn-logout:hover { background: #b91c1c; }
    .admin-container {
      max-width: 1440px;
      margin: 25px auto;
      padding: 0 25px;
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
      gap: 16px;
      margin-bottom: 25px;
    }
    .stat-card {
      background: #ffffff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.06);
      border: 1px solid var(--gray-200);
      position: relative;
      overflow: hidden;
    }
    .stat-card::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 4px;
    }
    .stat-card.reads::before { background: var(--emerald); }
    .stat-card.downloads::before { background: var(--accent); }
    .stat-card.users::before { background: var(--blue); }
    .stat-card.devices::before { background: var(--purple); }
    .stat-card.db::before { background: #0a2540; }
    .stat-label {
      font-size: 12px;
      font-weight: 600;
      color: var(--gray-500);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }
    .stat-value {
      font-family: 'Outfit', sans-serif;
      font-size: 28px;
      font-weight: 800;
      color: var(--gray-900);
      line-height: 1.1;
    }
    .stat-sub {
      font-size: 11.5px;
      color: var(--gray-500);
      margin-top: 6px;
    }
    .card-box {
      background: #ffffff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.06);
      border: 1px solid var(--gray-200);
      margin-bottom: 25px;
    }
    .card-title {
      font-family: 'Outfit', sans-serif;
      font-size: 17px;
      font-weight: 700;
      color: var(--gray-900);
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
    }
    .badge-reads { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    .badge-downloads { background: #ffedd5; color: #c2410c; border: 1px solid #fdba74; }
    .badge-active { background: #dcfce7; color: #15803d; }
    .badge-inactive { background: #fee2e2; color: #991b1b; }
    .badge-login { background: #f3e8ff; color: #6b21a8; }
    .badge-portal { background: #e0f2fe; color: #0369a1; }
    .table-responsive {
      width: 100%;
      overflow-x: auto;
    }
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    table.data-table th {
      background: #f1f5f9;
      color: var(--gray-700);
      font-weight: 700;
      text-align: left;
      padding: 11px 14px;
      border-bottom: 2px solid var(--gray-200);
      white-space: nowrap;
    }
    table.data-table td {
      padding: 12px 14px;
      border-bottom: 1px solid var(--gray-200);
      vertical-align: middle;
    }
    table.data-table tr:hover td {
      background: #f8fafc;
    }
    .btn-action {
      padding: 5px 9px;
      border-radius: 5px;
      font-size: 11.5px;
      font-weight: 600;
      border: 1px solid var(--gray-200);
      background: #ffffff;
      color: var(--gray-700);
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      transition: all 0.15s;
    }
    .btn-action:hover {
      background: var(--gray-100);
    }
    .btn-action.btn-danger {
      color: #dc2626;
      border-color: #fca5a5;
    }
    .btn-action.btn-danger:hover {
      background: #fef2f2;
    }
    .form-inline-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) 110px;
      gap: 12px;
      align-items: end;
      background: #f8fafc;
      padding: 16px;
      border-radius: 8px;
      border: 1px solid var(--gray-200);
      margin-top: 15px;
    }
    .form-input-sm {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid var(--gray-200);
      border-radius: 6px;
      font-size: 13px;
    }
    .form-input-sm:focus {
      outline: none;
      border-color: var(--accent);
    }
  </style>
</head>
<body>

  <header class="admin-navbar">
    <div class="admin-nav-left">
      <div>
        <div class="admin-nav-title">SaNDS Lab &bull; Document Tracking &amp; Security Console</div>
        <div class="admin-nav-sub">Al Rabeeh Dental Center EMRS Proposal &bull; Database: <strong><?php echo htmlspecialchars(DB_NAME); ?></strong> (<?php echo strtoupper($db_driver_active); ?>)</div>
      </div>
    </div>
    <div class="admin-nav-right">
      <span style="font-size:12px; color:#cbd5e1;"><i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($authenticated_user); ?></span>
      <a href="index.php" class="btn-nav btn-portal"><i class="fa-solid fa-folder-open"></i> Client Document Hub</a>
      <a href="?logout=1" class="btn-nav btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
  </header>

  <div class="admin-container">
    <?php if (!empty($admin_msg)): ?>
      <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; padding:12px 18px; border-radius:8px; margin-bottom:20px;">
        <i class="fa-solid fa-circle-check"></i> <?php echo $admin_msg; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($admin_error)): ?>
      <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:12px 18px; border-radius:8px; margin-bottom:20px;">
        <i class="fa-solid fa-circle-exclamation"></i> <?php echo $admin_error; ?>
      </div>
    <?php endif; ?>

    <!-- STATS CARDS -->
    <div class="stats-grid">
      <div class="stat-card reads">
        <div class="stat-label">Total HTML Document Reads</div>
        <div class="stat-value" style="color: var(--emerald);"><?php echo number_format($total_doc_views); ?></div>
        <div class="stat-sub">Online Interactive Views</div>
      </div>
      <div class="stat-card downloads">
        <div class="stat-label">Total PDF Downloads</div>
        <div class="stat-value" style="color: var(--accent);"><?php echo number_format($total_doc_downloads); ?></div>
        <div class="stat-sub">Official PDF Copies Exported</div>
      </div>
      <div class="stat-card users">
        <div class="stat-label">Authorized Stakeholders</div>
        <div class="stat-value" style="color: var(--blue);"><?php echo count($all_users); ?></div>
        <div class="stat-sub">In Active Stakeholder Directory</div>
      </div>
      <div class="stat-card devices">
        <div class="stat-label">Authenticated Devices</div>
        <div class="stat-value" style="color: var(--purple);"><?php echo count($all_devices); ?></div>
        <div class="stat-sub">Persistent 5-Year Tokens Active</div>
      </div>
      <div class="stat-card db">
        <div class="stat-label">Database Connection</div>
        <div class="stat-value" style="font-size:20px; margin-top:5px; color:#0a2540;">
          <i class="fa-solid fa-database" style="color:var(--emerald);"></i> MySQL 8.0+
        </div>
        <div class="stat-sub"><?php echo htmlspecialchars(DB_NAME); ?> &bull; 127.0.0.1:3306</div>
      </div>
    </div>

    <!-- DOCUMENT PERFORMANCE BREAKDOWN -->
    <div class="card-box">
      <div class="card-title">
        <span><i class="fa-solid fa-chart-line" style="color:var(--accent);"></i> Document Access &amp; Download Tracking Breakdown</span>
        <span style="font-size:12px; font-weight:normal; color:var(--gray-500);">Real-Time Metrics from <code>document_access_logs</code></span>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Document Code</th>
              <th>Document Title</th>
              <th style="text-align:center;">HTML Reads</th>
              <th style="text-align:center;">PDF Downloads</th>
              <th style="text-align:center;">Total Interactions</th>
              <th style="text-align:center;">Unique Stakeholders</th>
              <th>Last Accessed</th>
              <th>Direct Action</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $stats_lookup = array();
            foreach ($doc_stats as $ds) {
                $stats_lookup[$ds['doc_id']] = $ds;
            }
            foreach ($routes as $code => $r): 
                $st = isset($stats_lookup[$code]) ? $stats_lookup[$code] : null;
                $reads = $st ? intval($st['total_reads']) : 0;
                $downloads = $st ? intval($st['total_downloads']) : 0;
                $total_interact = $st ? intval($st['total_actions']) : 0;
                $unq = $st ? intval($st['unique_users']) : 0;
                $last_t = $st ? date('d M Y, h:i A', strtotime($st['last_accessed'])) : 'Not accessed yet';
            ?>
              <tr>
                <td><strong class="mono" style="color:var(--primary); font-size:12px;"><?php echo htmlspecialchars($code); ?></strong></td>
                <td><strong><?php echo htmlspecialchars($r['title']); ?></strong></td>
                <td style="text-align:center;">
                  <span class="badge badge-reads" style="font-size:12px; padding:4px 10px;">
                    <i class="fa-solid fa-eye"></i> <?php echo $reads; ?> Reads
                  </span>
                </td>
                <td style="text-align:center;">
                  <span class="badge badge-downloads" style="font-size:12px; padding:4px 10px;">
                    <i class="fa-solid fa-file-arrow-down"></i> <?php echo $downloads; ?> Downloads
                  </span>
                </td>
                <td style="text-align:center;">
                  <strong style="font-size:14px; color:var(--gray-900);"><?php echo $total_interact; ?></strong>
                </td>
                <td style="text-align:center;">
                  <span style="font-weight:600; color:var(--blue);"><i class="fa-solid fa-users"></i> <?php echo $unq; ?></span>
                </td>
                <td style="font-size:12px; color:var(--gray-500);"><?php echo $last_t; ?></td>
                <td>
                  <a href="index.php?doc=<?php echo urlencode($code); ?>" target="_blank" class="btn-action">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View HTML
                  </a>
                  <a href="index.php?download=<?php echo urlencode($code); ?>" class="btn-action">
                    <i class="fa-solid fa-download"></i> PDF
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- STAKEHOLDER ENGAGEMENT MATRIX -->
    <div class="card-box">
      <div class="card-title">
        <span><i class="fa-solid fa-users-gear" style="color:var(--blue);"></i> Stakeholder Engagement &amp; Device Registry</span>
        <span style="font-size:12px; font-weight:normal; color:var(--gray-500);"><?php echo count($all_users); ?> Stakeholders Registered</span>
      </div>
      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th>Stakeholder Name &amp; Email</th>
              <th>Organization &amp; Role</th>
              <th style="text-align:center;">HTML Reads</th>
              <th style="text-align:center;">PDF Downloads</th>
              <th>Last Activity</th>
              <th>Last Detected Device</th>
              <th>Last Location</th>
              <th>Status</th>
              <th>Manage</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($user_matrix as $u): ?>
              <tr>
                <td>
                  <strong><?php echo htmlspecialchars($u['full_name']); ?></strong><br>
                  <span style="font-size:11.5px; color:var(--gray-500);"><?php echo htmlspecialchars($u['email']); ?></span>
                </td>
                <td>
                  <strong><?php echo htmlspecialchars($u['organization']); ?></strong><br>
                  <span style="font-size:11.5px; color:var(--gray-500);"><?php echo htmlspecialchars($u['role']); ?></span>
                </td>
                <td style="text-align:center;">
                  <span class="badge badge-reads"><?php echo intval($u['total_reads']); ?></span>
                </td>
                <td style="text-align:center;">
                  <span class="badge badge-downloads"><?php echo intval($u['total_downloads']); ?></span>
                </td>
                <td style="font-size:12px; color:var(--gray-500);">
                  <?php echo $u['last_activity'] ? date('d M Y, h:i A', strtotime($u['last_activity'])) : 'Never'; ?>
                </td>
                <td style="font-size:12px;">
                  <?php echo htmlspecialchars($u['last_device'] ? $u['last_device'] : 'No device logged'); ?>
                </td>
                <td style="font-size:12px; color:var(--gray-600);">
                  <?php echo htmlspecialchars($u['last_location'] ? $u['last_location'] : 'N/A'); ?>
                </td>
                <td>
                  <?php if ($u['is_active'] == 1): ?>
                    <span class="badge badge-active">Active</span>
                  <?php else: ?>
                    <span class="badge badge-inactive">Deactivated</span>
                  <?php endif; ?>
                </td>
                <td>
                  <form method="POST" style="display:inline-block;" onsubmit="return confirm('Revoke all remembered devices for this stakeholder?');">
                    <input type="hidden" name="admin_action" value="revoke_devices">
                    <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($u['email']); ?>">
                    <button type="submit" class="btn-action" title="Force OTP verification next visit">
                      <i class="fa-solid fa-key"></i> Revoke
                    </button>
                  </form>
                  <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="admin_action" value="toggle_status">
                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                    <input type="hidden" name="current_status" value="<?php echo $u['is_active']; ?>">
                    <button type="submit" class="btn-action">
                      <?php echo ($u['is_active'] == 1) ? '<i class="fa-solid fa-ban" style="color:#dc2626;"></i> Pause' : '<i class="fa-solid fa-check" style="color:#059669;"></i> Activate'; ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- ADD NEW USER FORM -->
      <form method="POST" class="form-inline-grid" style="margin-top:20px;">
        <input type="hidden" name="admin_action" value="add_user">
        <div>
          <label style="font-size:11px; font-weight:700; color:var(--gray-600); display:block; margin-bottom:4px;">EMAIL ADDRESS</label>
          <input type="email" name="new_email" class="form-input-sm" placeholder="e.g. cto@alrabeeh.bh" required>
        </div>
        <div>
          <label style="font-size:11px; font-weight:700; color:var(--gray-600); display:block; margin-bottom:4px;">FULL NAME</label>
          <input type="text" name="new_name" class="form-input-sm" placeholder="e.g. Dr. Faisal" required>
        </div>
        <div>
          <label style="font-size:11px; font-weight:700; color:var(--gray-600); display:block; margin-bottom:4px;">ORGANIZATION</label>
          <input type="text" name="new_org" class="form-input-sm" value="Al Rabeeh Dental Center" required>
        </div>
        <div>
          <label style="font-size:11px; font-weight:700; color:var(--gray-600); display:block; margin-bottom:4px;">ROLE / TITLE</label>
          <input type="text" name="new_role" class="form-input-sm" placeholder="e.g. Clinical Director" required>
        </div>
        <button type="submit" class="btn-submit" style="padding:8px 14px; font-size:13px; height:36px;">
          <i class="fa-solid fa-user-plus"></i> Add User
        </button>
      </form>
    </div>

    <!-- REAL-TIME ACCESS AUDIT LOGS -->
    <div class="card-box">
      <div class="card-title">
        <span><i class="fa-solid fa-shield-halved" style="color:var(--emerald);"></i> Real-Time Access &amp; Download Audit Logs</span>
        <span style="font-size:12px; font-weight:normal; color:var(--gray-500);">Last 500 Recorded Events</span>
      </div>
      <div class="table-responsive">
        <table id="auditTable" class="data-table">
          <thead>
            <tr>
              <th>Timestamp</th>
              <th>Stakeholder</th>
              <th>Document / Event</th>
              <th>Action Type</th>
              <th>IP Address</th>
              <th>Device / Browser</th>
              <th>Location</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_logs as $l): ?>
              <tr>
                <td style="white-space:nowrap; font-size:12px; color:var(--gray-500);">
                  <?php echo date('d M Y, h:i:s A', strtotime($l['accessed_at'])); ?>
                </td>
                <td>
                  <strong><?php echo htmlspecialchars($l['email']); ?></strong>
                </td>
                <td>
                  <span class="mono" style="font-weight:700; color:var(--primary);"><?php echo htmlspecialchars($l['doc_id']); ?></span><br>
                  <span style="font-size:11.5px; color:var(--gray-500);"><?php echo htmlspecialchars($l['doc_title']); ?></span>
                </td>
                <td>
                  <?php if ($l['action_type'] === 'VIEW_HTML'): ?>
                    <span class="badge badge-reads"><i class="fa-solid fa-eye"></i> HTML View</span>
                  <?php elseif ($l['action_type'] === 'DOWNLOAD_PDF'): ?>
                    <span class="badge badge-downloads"><i class="fa-solid fa-file-arrow-down"></i> PDF Download</span>
                  <?php elseif ($l['action_type'] === 'LOGIN_VERIFIED'): ?>
                    <span class="badge badge-login"><i class="fa-solid fa-shield-check"></i> OTP Verified</span>
                  <?php else: ?>
                    <span class="badge badge-portal"><?php echo htmlspecialchars($l['action_type']); ?></span>
                  <?php endif; ?>
                </td>
                <td class="mono" style="font-size:12px;"><?php echo htmlspecialchars($l['ip_address']); ?></td>
                <td style="font-size:12px;"><?php echo htmlspecialchars($l['device_name']); ?></td>
                <td style="font-size:12px; color:var(--gray-600);"><?php echo htmlspecialchars($l['location']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      $('#auditTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        language: {
          search: "Filter Audit Logs:",
          lengthMenu: "Show _MENU_ logs"
        }
      });
    });
  </script>
</body>
</html>
    <?php
    exit;
}

// =========================================================================
// 8. DOCUMENT CONTROLLER (VIEW HTML / DOWNLOAD PDF)
// =========================================================================
$requested_doc = '';
if (isset($_GET['doc']) && !empty($_GET['doc'])) {
    $requested_doc = trim($_GET['doc']);
} elseif (isset($_GET['download']) && !empty($_GET['download'])) {
    $requested_doc = trim($_GET['download']);
}

// Check if clean URL path was rewritten via Apache
if (empty($requested_doc) && !empty($_SERVER['REQUEST_URI'])) {
    $clean_uri = strtok($_SERVER['REQUEST_URI'], '?');
    $path_parts = array_values(array_filter(explode('/', trim($clean_uri, '/'))));
    if (!empty($path_parts)) {
        $last_part = end($path_parts);
        if (strcasecmp($last_part, 'index.php') !== 0) {
            $requested_doc = $last_part;
        }
    }
}

$canonical_doc_id = resolve_doc_id($requested_doc);

// --- STRICT STAKEHOLDER RESTRICTION GATE ---
if (!empty($canonical_doc_id) && isset($routes[$canonical_doc_id]['restricted_to'])) {
    $allowed_user = strtolower(trim($routes[$canonical_doc_id]['restricted_to']));
    if (strtolower(trim($authenticated_user)) !== $allowed_user) {
        log_document_access($authenticated_user, $canonical_doc_id, $routes[$canonical_doc_id]['title'], 'ACCESS_DENIED');
        http_response_code(403);
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <title>Access Restricted &bull; Confidential</title>
          <link rel="preconnect" href="https://fonts.googleapis.com">
          <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
          <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
          <style>
            body { font-family: 'Inter', sans-serif; background: #07192c; color: #334155; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; margin: 0; }
            .denied-card { background: #ffffff; border-radius: 14px; max-width: 520px; width: 100%; padding: 40px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.4); border-top: 5px solid #dc2626; }
            .denied-icon { font-size: 48px; color: #dc2626; margin-bottom: 16px; }
            h2 { font-family: 'Outfit', sans-serif; color: #0f172a; margin-bottom: 10px; font-size: 22px; }
            p { font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 24px; }
            .btn-back { display: inline-flex; align-items: center; gap: 8px; background: #0a2540; color: #fff; padding: 12px 24px; border-radius: 8px; font-weight: 600; text-decoration: none; }
            .btn-back:hover { background: #163e67; }
          </style>
        </head>
        <body>
          <div class="denied-card">
            <div class="denied-icon"><i class="fa-solid fa-lock"></i></div>
            <h2>Access Restricted</h2>
            <p>The document <strong><?php echo htmlspecialchars($routes[$canonical_doc_id]['title']); ?></strong> is classified and accessible solely by <strong><?php echo htmlspecialchars($routes[$canonical_doc_id]['restricted_to']); ?></strong>.</p>
            <a href="index.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Return to Document Repository</a>
          </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// --- HANDLER A: DIRECT PDF DOWNLOAD / STREAM ---
$is_pdf_request = (
    isset($_GET['download']) || 
    (isset($_GET['format']) && strtolower($_GET['format']) === 'pdf') ||
    (!empty($requested_doc) && substr(strtolower($requested_doc), -4) === '.pdf')
);

if ($is_pdf_request && !empty($canonical_doc_id) && isset($routes[$canonical_doc_id])) {
    $pdf_filename = $routes[$canonical_doc_id]['pdf'];
    $pdf_filepath = __DIR__ . '/' . $pdf_filename;
    
    // Log Download Audit Event
    log_document_access($authenticated_user, $canonical_doc_id, $routes[$canonical_doc_id]['title'], 'DOWNLOAD_PDF');
    
    if (file_exists($pdf_filepath)) {
        // Clear output buffer
        if (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($pdf_filepath) . '"');
        header('Content-Length: ' . filesize($pdf_filepath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        readfile($pdf_filepath);
        exit;
    } else {
        die("PDF file not found on server: " . htmlspecialchars($pdf_filename));
    }
}

// --- HANDLER B: VIEW HTML DOCUMENT ---
if (!empty($canonical_doc_id) && isset($routes[$canonical_doc_id])) {
    $html_filename = $routes[$canonical_doc_id]['html'];
    $html_filepath = __DIR__ . '/' . $html_filename;
    
    // Log View HTML Audit Event
    log_document_access($authenticated_user, $canonical_doc_id, $routes[$canonical_doc_id]['title'], 'VIEW_HTML');
    
    // Constant guard for inner document protection
    define('AUTH_PORTAL_ACTIVE', true);
    
    if (file_exists($html_filepath)) {
        ob_start();
        include $html_filepath;
        $html_content = ob_get_clean();
        
        $top_bar = '<!-- SaNDS Lab Authenticated Top Control Bar -->
        <style>
          .sands-portal-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 44px;
            background: #07192c;
            border-bottom: 2px solid #ea580c;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 999999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            font-family: \'Inter\', sans-serif;
            font-size: 12px;
            color: #ffffff;
          }
          .sands-portal-bar a {
            color: #ffffff;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 4px;
            background: rgba(255,255,255,0.1);
            transition: background 0.15s;
          }
          .sands-portal-bar a:hover {
            background: rgba(255,255,255,0.22);
          }
          .sands-portal-bar .btn-pdf-dl {
            background: #ea580c;
            color: #ffffff;
          }
          .sands-portal-bar .btn-pdf-dl:hover {
            background: #c2410c;
          }
          body {
            padding-top: 44px !important;
          }
          @media print {
            .sands-portal-bar { display: none !important; }
            body { padding-top: 0 !important; }
          }
        </style>
        <div class="sands-portal-bar">
          <div style="display:flex; align-items:center; gap:12px;">
            <a href="index.php"><i class="fa-solid fa-arrow-left"></i> Document Repository</a>
            <span style="color:#94a3b8;">|</span>
            <span style="font-weight:700; color:#ea580c;">' . htmlspecialchars($canonical_doc_id) . '</span>
          </div>
          <div style="display:flex; align-items:center; gap:12px;">
            <span style="color:#94a3b8;"><i class="fa-solid fa-user-check" style="color:#22c55e;"></i> ' . htmlspecialchars($authenticated_user) . '</span>' .
            ($canonical_doc_id === 'ARDC-EMRS-FLOWCHART-2026' ? ' <button type="button" onclick="if(typeof openDiagramModal===\'function\'){openDiagramModal(\'mermaid-tech\');}" style="background:#ea580c; border:none; color:#ffffff; cursor:pointer; font-weight:700; padding:5px 12px; border-radius:4px; font-size:12px; display:inline-flex; align-items:center; gap:6px; font-family:\'Inter\',sans-serif;"><i class="fa-solid fa-diagram-project"></i> Graphs Popup</button>' : '') . '
            <a href="index.php?download=' . urlencode($canonical_doc_id) . '" class="btn-pdf-dl">
              <i class="fa-solid fa-file-pdf"></i> Download PDF
            </a>' .
            ($is_super_admin ? ' <a href="index.php?view=admin" style="background:#0284c7;"><i class="fa-solid fa-chart-line"></i> Analytics</a>' : '') . '
            <a href="index.php?logout=1" style="background:#dc2626;"><i class="fa-solid fa-right-from-bracket"></i> Exit</a>
          </div>
        </div>';

        if (preg_match('/<body[^>]*>/i', $html_content, $m)) {
            $html_content = preg_replace('/(<body[^>]*>)/i', '$1' . $top_bar, $html_content, 1);
        } else {
            $html_content = $top_bar . $html_content;
        }

        echo $html_content;
        exit;
    } else {
        die("HTML file not found on server: " . htmlspecialchars($html_filename));
    }
}

// =========================================================================
// 9. CLIENT DOCUMENT REPOSITORY HUB (PORTAL DASHBOARD)
// =========================================================================
log_document_access($authenticated_user, 'PORTAL_HUB', 'Al Rabeeh EMRS Proposal & Document Repository Hub', 'PORTAL_ACCESS');

// Pre-fetch document access metrics for counter badges
$doc_counts = array();
if ($pdo) {
    try {
        $c_stmt = $pdo->query("SELECT doc_id,
                                      SUM(CASE WHEN action_type = 'VIEW_HTML' THEN 1 ELSE 0 END) as total_reads,
                                      SUM(CASE WHEN action_type = 'DOWNLOAD_PDF' THEN 1 ELSE 0 END) as total_downloads
                               FROM document_access_logs
                               GROUP BY doc_id");
        if ($c_stmt) {
            while ($r = $c_stmt->fetch()) {
                $doc_counts[$r['doc_id']] = $r;
            }
        }
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Al Rabeeh Dental Center &bull; EMRS Proposal &amp; Technical Architecture Hub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #0a2540;
      --primary-dark: #07192c;
      --accent: #ea580c;
      --accent-hover: #c2410c;
      --emerald: #059669;
      --blue: #0284c7;
      --purple: #7c3aed;
      --gray-900: #0f172a;
      --gray-800: #1e293b;
      --gray-700: #334155;
      --gray-600: #475569;
      --gray-500: #64748b;
      --gray-200: #e2e8f0;
      --gray-100: #f1f5f9;
      --white: #ffffff;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background: #f8fafc;
      color: var(--gray-700);
      line-height: 1.5;
    }
    .portal-nav {
      background: #07192c;
      border-bottom: 3px solid var(--accent);
      padding: 16px 35px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .portal-brand {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .portal-logo-img {
      height: 44px;
      width: auto;
      background: #ffffff;
      padding: 5px 10px;
      border-radius: 6px;
    }
    .portal-titles h1 {
      font-family: 'Outfit', sans-serif;
      font-size: 18px;
      color: #ffffff;
      font-weight: 700;
    }
    .portal-titles p {
      font-size: 11.5px;
      color: #94a3b8;
    }
    .portal-user-badge {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .user-info {
      text-align: right;
    }
    .user-email {
      font-size: 13px;
      font-weight: 700;
      color: #ffffff;
    }
    .user-tag {
      font-size: 11px;
      color: #22c55e;
      display: flex;
      align-items: center;
      gap: 4px;
      justify-content: flex-end;
    }
    .btn-header {
      padding: 7px 14px;
      border-radius: 6px;
      font-size: 12.5px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s;
    }
    .btn-admin { background: #0284c7; color: #ffffff; }
    .btn-admin:hover { background: #0369a1; }
    .btn-logout { background: rgba(255,255,255,0.12); color: #ffffff; border: 1px solid rgba(255,255,255,0.2); }
    .btn-logout:hover { background: #dc2626; border-color: #dc2626; }
    
    .portal-hero {
      background: linear-gradient(135deg, #07192c 0%, #0a2540 100%);
      color: #ffffff;
      padding: 45px 35px;
      text-align: center;
      border-bottom: 1px solid var(--gray-200);
    }
    .portal-hero h2 {
      font-family: 'Outfit', sans-serif;
      font-size: 28px;
      font-weight: 800;
      margin-bottom: 8px;
      letter-spacing: -0.5px;
    }
    .portal-hero p {
      font-size: 14.5px;
      color: #94a3b8;
      max-width: 780px;
      margin: 0 auto 18px;
    }
    .hero-meta-badges {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      flex-wrap: wrap;
    }
    .hero-pill {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 12px;
      color: #e2e8f0;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    
    .portal-container {
      max-width: 1280px;
      margin: 35px auto;
      padding: 0 25px;
    }
    .doc-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
      gap: 24px;
    }
    .doc-card {
      background: #ffffff;
      border-radius: 12px;
      border: 1px solid var(--gray-200);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .doc-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
    }
    .doc-card-header {
      padding: 20px 22px 16px;
      border-bottom: 1px solid var(--gray-100);
    }
    .doc-code-badge {
      font-family: 'JetBrains Mono', monospace;
      font-size: 11px;
      font-weight: 700;
      color: var(--accent);
      background: #fff7ed;
      padding: 3px 8px;
      border-radius: 4px;
      display: inline-block;
      margin-bottom: 8px;
      border: 1px solid #ffedd5;
    }
    .doc-title {
      font-family: 'Outfit', sans-serif;
      font-size: 16px;
      font-weight: 700;
      color: var(--gray-900);
      line-height: 1.35;
      margin-bottom: 6px;
    }
    .doc-card-body {
      padding: 18px 22px;
      flex-grow: 1;
      font-size: 13px;
      color: var(--gray-600);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .doc-desc {
      line-height: 1.5;
      margin-bottom: 16px;
    }
    .doc-meta-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 11.5px;
      color: var(--gray-500);
      padding: 10px 0;
      border-top: 1px dashed var(--gray-200);
    }
    .doc-counters {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 14px;
    }
    .counter-pill {
      font-size: 11.5px;
      font-weight: 700;
      padding: 4px 9px;
      border-radius: 6px;
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }
    .counter-reads { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    .counter-downloads { background: #ffedd5; color: #c2410c; border: 1px solid #fdba74; }
    
    .doc-card-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      padding: 16px 22px;
      background: #f8fafc;
      border-top: 1px solid var(--gray-200);
    }
    .btn-doc {
      padding: 10px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 700;
      text-align: center;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.15s;
    }
    .btn-read {
      background: var(--primary);
      color: #ffffff;
    }
    .btn-read:hover {
      background: #0f3459;
    }
    .btn-download {
      background: #ffffff;
      color: var(--accent);
      border: 1.5px solid var(--accent);
    }
    .btn-download:hover {
      background: var(--accent);
      color: #ffffff;
    }
  </style>
</head>
<body>

  <header class="portal-nav">
    <div class="portal-brand">
      <img src="Logo/AlRabeeh-Logo.png" alt="Al Rabeeh Dental Center" class="portal-logo-img">
      <img src="Logo/SaNDSLab-LogoNewUpdated copy.png" alt="SaNDS Lab" class="portal-logo-img">
      <div class="portal-titles">
        <h1>Al Rabeeh Dental Center &bull; Document Portal</h1>
        <p>Enterprise Medical Records System (EMRS) &bull; Kingdom of Bahrain</p>
      </div>
    </div>
    <div class="portal-user-badge">
      <div class="user-info">
        <div class="user-email"><?php echo htmlspecialchars($authenticated_user); ?></div>
        <div class="user-tag"><i class="fa-solid fa-circle-check"></i> Device Authenticated</div>
      </div>
      <?php if ($is_super_admin): ?>
        <a href="?view=admin" class="btn-header btn-admin"><i class="fa-solid fa-chart-line"></i> Admin Console</a>
      <?php endif; ?>
      <a href="?logout=1" class="btn-header btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
  </header>

  <div class="portal-hero">
    <h2>Client Proposal &amp; Technical Architecture Documentation</h2>
    <p>
      Welcome to the authenticated document portal for <strong>Al Rabeeh Dental Center</strong>. You may read the interactive HTML specifications online or download the official PDF documents for offline review and sign-off.
    </p>
    <div class="hero-meta-badges">
      <div class="hero-pill"><i class="fa-solid fa-hospital-user"></i> 6 Bahrain Clinics</div>
      <div class="hero-pill"><i class="fa-solid fa-layer-group"></i> 11 Reception Modules</div>
      <div class="hero-pill"><i class="fa-solid fa-id-card"></i> Smart CPR Integration</div>
      <div class="hero-pill"><i class="fa-solid fa-bolt"></i> Offline Auto-Sync</div>
      <div class="hero-pill"><i class="fa-solid fa-code"></i> 100% Client Source Code Ownership</div>
    </div>
  </div>

  <div class="portal-container">
    <div class="doc-grid">
      <?php foreach ($routes as $id => $doc): 
          // Strictly hide restricted documents from non-authorized users
          if (isset($doc['restricted_to']) && !empty($doc['restricted_to'])) {
              if (strtolower(trim($authenticated_user)) !== strtolower(trim($doc['restricted_to']))) {
                  continue;
              }
          }
          $cnt = isset($doc_counts[$id]) ? $doc_counts[$id] : array('total_reads' => 0, 'total_downloads' => 0);
          $reads_val = intval(isset($cnt['total_reads']) ? $cnt['total_reads'] : 0);
          $dl_val = intval(isset($cnt['total_downloads']) ? $cnt['total_downloads'] : 0);
      ?>
        <div class="doc-card">
          <div class="doc-card-header">
            <span class="doc-code-badge"><?php echo htmlspecialchars($id); ?></span>
            <div class="doc-title"><?php echo htmlspecialchars($doc['title']); ?></div>
          </div>
          <div class="doc-card-body">
            <div class="doc-desc"><?php echo htmlspecialchars($doc['desc']); ?></div>
            
            <div class="doc-counters">
              <span class="counter-pill counter-reads" title="How many times this HTML specification was read online">
                <i class="fa-solid fa-eye"></i> <?php echo $reads_val; ?> Reads
              </span>
              <span class="counter-pill counter-downloads" title="How many times the PDF was downloaded">
                <i class="fa-solid fa-file-arrow-down"></i> <?php echo $dl_val; ?> Downloads
              </span>
            </div>

            <div class="doc-meta-row">
              <span><i class="fa-solid fa-clock"></i> <?php echo htmlspecialchars($doc['timeline']); ?></span>
              <span><i class="fa-solid fa-shield-halved"></i> <?php echo htmlspecialchars($doc['status']); ?></span>
            </div>
          </div>
          <div class="doc-card-actions">
            <a href="index.php?doc=<?php echo urlencode($id); ?>" class="btn-doc btn-read">
              <i class="fa-solid fa-book-open"></i> Read HTML
            </a>
            <a href="index.php?download=<?php echo urlencode($id); ?>" class="btn-doc btn-download">
              <i class="fa-solid fa-file-pdf"></i> Download PDF
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</body>
</html>
