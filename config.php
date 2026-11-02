<?php
// =========================================================================
// SaNDS Lab & Al Rabeeh Dental Center EMRS Document Portal - Configuration
// Database: alrabeeh_track_db
// =========================================================================

if (!defined('DB_DRIVER')) define('DB_DRIVER', 'auto'); // Auto prefers MySQL, falls back to SQLite
if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_NAME')) define('DB_NAME', 'alrabeeh_track_db');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'S@nds1@b');
if (!defined('PORTAL_SECRET_SALT')) define('PORTAL_SECRET_SALT', 'SaNDS_Lab_Secured_Token_Key_2026_EMRS_AlRabeeh');
if (!defined('PORTAL_NAME')) define('PORTAL_NAME', 'Al Rabeeh Dental Center &bull; EMRS Proposal & Architecture Portal');
?>

