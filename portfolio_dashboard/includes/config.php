<?php
// includes/config.php - Environment-based configuration
// On production hosts (InfinityFree, 000webhost, etc.) set these as real environment
// variables in your hosting control panel. Locally on XAMPP, none of these env vars
// exist, so the fallback values on the right of ?: are used automatically.

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'portfolio_dashboard');

// App-level settings
define('APP_NAME', 'Portfolio Admin');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/portfolio_dashboard');
?>
