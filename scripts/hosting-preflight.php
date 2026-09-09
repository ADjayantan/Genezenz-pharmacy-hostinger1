<?php
declare(strict_types=1);

// Read-only launch checks. Run from SSH/terminal with the host's PHP executable:
// php scripts/hosting-preflight.php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$failures = 0;
function hostingCheck(bool $passed, string $label, string $fix = ''): void
{
    global $failures;
    echo ($passed ? 'PASS ' : 'FAIL ').$label;
    if (!$passed) {
        $failures++;
        if ($fix !== '') echo ' — '.$fix;
    }
    echo PHP_EOL;
}

function hostingBytes(string $value): ?float
{
    $value = trim($value);
    if ($value === '-1') return INF;
    if (!preg_match('/^(\d+(?:\.\d+)?)\s*([KMG]?)$/iD', $value, $match)) return null;
    $power = ['' => 0, 'K' => 1, 'M' => 2, 'G' => 3][strtoupper($match[2])];
    return (float)$match[1] * (1024 ** $power);
}

function hostingHasPlaceholder(string $value): bool
{
    return preg_match('/replace|change[-_ ]?me|placeholder|example|your[-_ ]|test[-_ ]|demo[-_ ]/i', $value) === 1
        || count(array_unique(str_split($value))) < 8;
}

function hostingInside(string $path, string $directory): bool
{
    $path = str_replace('\\', '/', $path);
    $directory = rtrim(str_replace('\\', '/', $directory), '/');
    if (PHP_OS_FAMILY === 'Windows') {
        $path = strtolower($path);
        $directory = strtolower($directory);
    }
    return $path === $directory || str_starts_with($path, $directory.'/');
}

echo "Genezenz hosting preflight (read-only)".PHP_EOL;
hostingCheck(PHP_VERSION_ID >= 80200, 'PHP 8.2 or newer', 'Select PHP 8.2+ in the hosting panel.');
if (PHP_VERSION_ID < 80200) exit(1);

$basePath = dirname(__DIR__);
require $basePath.'/app/Core/Env.php';
\App\Core\Env::load($basePath.'/.env');
$env = static fn(string $key, string $default = ''): string => \App\Core\Env::get($key, $default);

foreach (['pdo_mysql', 'openssl', 'fileinfo', 'curl', 'gd'] as $extension) {
    hostingCheck(extension_loaded($extension), 'PHP extension '.$extension, 'Enable it for the website and CLI PHP.');
}
$gd = function_exists('gd_info') ? gd_info() : [];
hostingCheck(function_exists('imagewebp') && !empty($gd['WebP Support']), 'GD WebP support', 'Enable GD with WebP encoding for product uploads.');
hostingCheck(function_exists('openssl_get_cipher_methods') && in_array('aes-256-gcm', openssl_get_cipher_methods(), true), 'AES-256-GCM encryption support');

$uploadCap = hostingBytes((string)ini_get('upload_max_filesize'));
$postCap = hostingBytes((string)ini_get('post_max_size'));
$memoryCap = hostingBytes((string)ini_get('memory_limit'));
hostingCheck(filter_var(ini_get('file_uploads'), FILTER_VALIDATE_BOOL), 'PHP file uploads enabled');
hostingCheck($uploadCap !== null && $uploadCap >= 8 * 1024 * 1024, 'Upload limit supports 8 MB prescriptions', 'Set upload_max_filesize to at least 8M.');
// PHP treats post_max_size=0 as unlimited; multipart overhead needs headroom.
hostingCheck($postCap !== null && $uploadCap !== null && ($postCap === 0.0 || $postCap > $uploadCap), 'POST limit exceeds the upload limit', 'For an 8M upload limit, use post_max_size=12M or higher.');
hostingCheck($memoryCap !== null && $memoryCap >= 128 * 1024 * 1024, 'Memory limit supports image processing', 'Use memory_limit=128M or higher.');

$url = $env('APP_URL');
$urlParts = parse_url($url);
$host = strtolower(rtrim((string)($urlParts['host'] ?? ''), '.'));
$publicHost = $host !== '' && str_contains($host, '.')
    && !preg_match('/(?:^|\.)(?:localhost|local|test|invalid|example)$/iD', $host)
    && !in_array($host, ['example.com', 'example.net', 'example.org'], true)
    && !filter_var($host, FILTER_VALIDATE_IP);
hostingCheck(filter_var($url, FILTER_VALIDATE_URL) !== false && ($urlParts['scheme'] ?? '') === 'https'
    && $publicHost && !isset($urlParts['user']) && !isset($urlParts['pass']), 'APP_URL uses a nonlocal HTTPS domain', 'Set the confirmed production HTTPS origin.');
hostingCheck($env('APP_ENV') === 'production', 'Production environment configured', 'Set APP_ENV=production.');
hostingCheck($env('APP_DEBUG') !== '' && filter_var($env('APP_DEBUG'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) === false,
    'Debug output disabled', 'Set APP_DEBUG=false.');

$appKey = $env('APP_KEY');
hostingCheck(strlen($appKey) >= 32 && !hostingHasPlaceholder($appKey), 'Application key configured', 'Generate a random key of at least 32 characters.');
$fileKey = $env('FILE_ENCRYPTION_KEY');
$decodedKey = base64_decode($fileKey, true);
$binaryKey = is_string($decodedKey) && strlen($decodedKey) === 32 ? $decodedKey : $fileKey;
hostingCheck(strlen($binaryKey) === 32 && !hostingHasPlaceholder($fileKey) && count(array_unique(str_split($binaryKey))) >= 8,
    'Prescription encryption key configured', 'Use a random 32-byte key, optionally base64 encoded; retain the existing key for existing uploads.');
unset($appKey, $fileKey, $decodedKey, $binaryKey);

$publicPath = realpath($basePath.'/public_html');
$privatePath = realpath($basePath.'/private_uploads');
$productPath = realpath($basePath.'/public_html/uploads/products');
hostingCheck(is_string($publicPath) && is_readable($publicPath), 'Public document-root directory readable');
hostingCheck(is_string($privatePath) && is_dir($privatePath) && is_readable($privatePath) && is_writable($privatePath),
    'Private prescription directory readable and writable', 'Grant the PHP user access to private_uploads.');
hostingCheck(is_string($publicPath) && is_string($privatePath) && !hostingInside($privatePath, $publicPath),
    'Prescription storage stays outside public_html', 'Keep private_uploads outside the real document root, including symlink targets.');
hostingCheck(is_string($productPath) && is_dir($productPath) && is_readable($productPath) && is_writable($productPath),
    'Public product-image directory readable and writable', 'Grant the PHP user access to public_html/uploads/products.');
hostingCheck(is_string($publicPath) && is_string($productPath) && hostingInside($productPath, $publicPath), 'Product images resolve inside public_html');
hostingCheck(is_readable($basePath.'/public_html/uploads/products/.htaccess'), 'Product-image protection file present', 'Deploy the uploads/products/.htaccess file.');
$publicEnvFiles = glob($basePath.'/public_html/.env*');
$envPath = realpath($basePath.'/.env');
hostingCheck($publicEnvFiles !== false && $publicEnvFiles === [] && is_string($publicPath)
    && ($envPath === false || !hostingInside($envPath, $publicPath)), 'Environment files stay outside public_html', 'Move .env files outside the document root.');

$database = null;
if (extension_loaded('pdo_mysql')) {
    try {
        $dsn = 'mysql:host='.$env('DB_HOST', '127.0.0.1').';port='.$env('DB_PORT', '3306').';dbname='.$env('DB_NAME', 'genezenz').';charset=utf8mb4';
        $database = new PDO($dsn, $env('DB_USER'), $env('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $database->query('SELECT 1')->fetchColumn();
    } catch (Throwable $error) {
        // Exception messages can contain database credentials or host details.
        $database = null;
    }
}
hostingCheck($database instanceof PDO, 'MySQL connection available', 'Check database configuration and PHP pdo_mysql; credentials are never printed.');
$rxColumn = false;
if ($database instanceof PDO) {
    try {
        $rxColumn = $database->query("SHOW COLUMNS FROM orders LIKE 'requires_prescription'")->fetch(PDO::FETCH_ASSOC) !== false;
    } catch (Throwable $error) {
        $rxColumn = false;
    }
}
hostingCheck($rxColumn, 'Order prescription migration present', 'Import the fresh schema or apply database/migrations/001-order-prescription.sql to an existing installation.');
$database = null;

echo 'WARN CLI PHP settings and permissions can differ from web PHP; verify the same settings and real upload/checkout flows over HTTPS.'.PHP_EOL;
echo 'WARN Confirm the real domain, SSL, pharmacy licence, pharmacist details, business contact information, prices and policies with the owner.'.PHP_EOL;
echo 'WARN This check does not verify DNS, Apache rewrites, backups, cron jobs or Google indexing.'.PHP_EOL;
echo ($failures === 0 ? 'PASS No automated launch blockers detected.' : 'FAIL '.$failures.' automated launch blocker(s) detected.').PHP_EOL;
exit($failures === 0 ? 0 : 1);
