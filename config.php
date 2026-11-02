<?php
// =========================================================================
// SaNDS Lab & Al Rabeeh Dental Center EMRS Document Portal - Configuration
// Environment-Aware Configuration: Auto-switches between Local & Production
// =========================================================================

// Detect Environment: Local vs Production Server
$http_host   = isset($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : '';
$server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
$is_local    = (
    strpos($http_host, 'localhost') !== false ||
    strpos($http_host, '127.0.0.1') !== false ||
    $server_addr === '127.0.0.1' ||
    $server_addr === '::1' ||
    (php_sapi_name() === 'cli' && strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
);

// Database Driver ('auto' prefers MySQL, falls back to SQLite)
if (!defined('DB_DRIVER')) define('DB_DRIVER', 'auto');

if ($is_local) {
    // ---------------------------------------------------------------------
    // LOCAL DEVELOPMENT CONFIGURATION
    // ---------------------------------------------------------------------
    if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
    if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
    if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'alrabeeh_track_db');
    if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
    if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'S@nds1@b');
} else {
    // ---------------------------------------------------------------------
    // PRODUCTION SERVER CONFIGURATION
    // User: sandsl23_alrabeeh_docs_user
    // Database: sandsl23_alrabeeh_docs_db
    // Password: Same as local (S@nds1@b)
    // ---------------------------------------------------------------------
    if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
    if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'sandsl23_alrabeeh_docs_db');
    if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'sandsl23_alrabeeh_docs_user');
    if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'S@nds1@b');
}

// Security Salt & Portal Branding
if (!defined('PORTAL_SECRET_SALT')) define('PORTAL_SECRET_SALT', 'SaNDS_Lab_Secured_Token_Key_2026_EMRS_AlRabeeh');
if (!defined('PORTAL_NAME')) define('PORTAL_NAME', 'Al Rabeeh Dental Center &bull; EMRS Proposal & Architecture Portal');
?>
