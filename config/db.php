<?php
// Database connection using mysqli (procedural)

// Try to load composer's autoloader if present (for phpdotenv)
$projectRoot = dirname(__DIR__);
$autoload = $projectRoot . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Load environment variables from .env (if vlucas/phpdotenv is available)
if (class_exists('Dotenv\Dotenv')) {
    try {
        $dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
        $dotenv->safeLoad();
    } catch (Exception $e) {
        // ignore failures and fall back to getenv/defaults
    }
}

// Read DB credentials from environment variables with sensible defaults
$host = getenv('DB_HOST') ?: (isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost');
$user = getenv('DB_USER') ?: (isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root');
$password = getenv('DB_PASS') ?: (isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : '');
$dbname = getenv('DB_NAME') ?: (isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'tuition360');

$conn = mysqli_connect($host, $user, $password, $dbname);
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
// Set charset
mysqli_set_charset($conn, 'utf8mb4');
?>
