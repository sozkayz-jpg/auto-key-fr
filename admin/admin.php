<?php
/**
 * Admin Auto-Key - Version securisee
 *
 * Correctifs :
 * - Sessions securisees (httponly, samesite, regenerate)
 * - Rate limiting login (5 tentatives / 15 min)
 * - CSRF tokens sur toutes les actions
 * - Validation stricte des uploads
 * - Logs des actions admin
 * - Constantes sensibles en dehors du webroot idealement
 */

// Demarrer la session avec options securisees
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Headers de securite
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

define('ADMIN_USER', 'admin');
// IMPORTANT : remplacer ce hash par un nouveau genere via generate-password.php
define('ADMIN_PASS_HASH', '$2y$10$wH8mV3qKz9xY7nL4pR2aEeJ6sT5uV1wB0cD6fG7hI3jK2lM1nO0pQ');
define('IMAGES_DIR', __DIR__ . '/../assets/img/');
define('CONFIG_FILE', __DIR__ . '/config.json');
define('LOG_FILE', __DIR__ . '/admin.log');
define('MAX_SIZE', 5 * 1024 * 1024);
define('ALLOWED_EXT', ['jpg','jpeg','png','webp','gif']);
// SVG retire pour raison de securite (peut contenir du JS)
define('ALLOWED_MIME', ['image/jpeg','image/png','image/webp','image/gif']);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_WINDOW', 900); // 15 min

// Charger la config
$config = file_exists(CONFIG_FILE) ? json_decode(file_get_contents(CONFIG_FILE), true) : default_config();

function default_config() {
    return [
        'site_name' => 'Auto-Key',
        'phone' => '07 46 57 17 03',
        'email' => 'contact@allo-cle-auto.fr',
        'address' => '10 Rue du College, 74950 Scionzier',
        'hours' => 'Ouvert 24h/24 - 7j/7',
        'hero_image' => 'hero-key.webp',
        'services' => [
            'duplication' => 'service-duplication.webp',
            'perdue' => 'service-perdue.webp',
            'reparation' => 'service-reparation.webp',
            'diagnostic' => 'service-diagnostic.webp',
            'multimedia' => 'service-multimedia.webp',
            'urgence' => 'service-urgence.webp',
        ],
        'og_image' => 'og-image.webp',
        'logo' => 'logo-full.png',
    ];
}

function log_action($msg) {
    if (!defined('LOG_FILE')) return;
    @file_put_contents(
        LOG_FILE,
        date('Y-m-d H:i:s') . ' | ' . ($_SERVER['REMOTE_ADDR'] ?? 'cli') . ' | ' . $msg . "\n",
        FILE_APPEND | LOCK_EX
    );
}

function is_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true
        && isset($_SESSION['ip']) && $_SESSION['ip'] === ($_SERVER['REMOTE_ADDR'] ?? '')
        && isset($_SESSION['ua']) && $_SESSION['ua'] === ($_SERVER['HTTP_USER_AGENT'] ?? '');
}

function require_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            send_json(['error' => 'Jeton CSRF invalide. Rechargez la page.'], 403);
        }
    }
}

function send_json($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function get_client_ip() {
    // En production derriere proxy, valider la liste
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function check_rate_limit($ip, $max = LOGIN_MAX_ATTEMPTS, $window = LOGIN_WINDOW) {
    $file = sys_get_temp_dir() . '/allo_admin_rl_' . md5($ip);
    $now = time();
    $attempts = [];
    if (file_exists($file)) {
        $data = @file_get_contents($file);
        $attempts = $data ? array_filter(explode(',', trim($data)), function($t) use ($now, $window) {
            return is_numeric($t) && ($now - (int)$t) < $window;
        }) : [];
    }
    return ['count' => count($attempts), 'file' => $file, 'attempts' => $attempts];
}

function record_attempt($file, $attempts) {
    $attempts[] = time();
    @file_put_contents($file, implode(',', $attempts), LOCK_EX);
}

function clear_attempts($file) {
    @unlink($file);
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    return $text ?: 'file-' . time();
}

function list_images($dir) {
    $out = [];
    if (!is_dir($dir)) return $out;
    $files = @glob($dir . '*');
    if (!$files) return $out;
    foreach ($files as $f) {
        if (is_file($f)) {
            $name = basename($f);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (in_array($ext, ALLOWED_EXT)) {
                $out[] = [
                    'name' => $name,
                    'size' => filesize($f),
                    'size_kb' => round(filesize($f)/1024),
                    'modified' => date('Y-m-d H:i', filemtime($f)),
                    'url' => '../assets/img/' . $name,
                    'used_as' => get_usage($name),
                ];
            }
        }
    }
    usort($out, function($a, $b) { return strcmp($b['modified'], $a['modified']); });
    return $out;
}

function get_usage($name) {
    global $config;
    $uses = [];
    if (($config['hero_image'] ?? '') === $name) $uses[] = 'Hero accueil';
    if (($config['og_image'] ?? '') === $name) $uses[] = 'Open Graph';
    if (($config['logo'] ?? '') === $name) $uses[] = 'Logo';
    foreach (($config['services'] ?? []) as $key => $img) {
        if ($img === $name) $uses[] = 'Service ' . $key;
    }
    return $uses;
}

function save_config($c) {
    file_put_contents(CONFIG_FILE, json_encode($c, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function apply_config_to_site($c) {
    $map = [
        'hero-key.webp' => $c['hero_image'] ?? '',
        'service-duplication.webp' => $c['services']['duplication'] ?? '',
        'service-perdue.webp' => $c['services']['perdue'] ?? '',
        'service-reparation.webp' => $c['services']['reparation'] ?? '',
        'service-diagnostic.webp' => $c['services']['diagnostic'] ?? '',
        'service-multimedia.webp' => $c['services']['multimedia'] ?? '',
        'service-urgence.webp' => $c['services']['urgence'] ?? '',
        'og-image.webp' => $c['og_image'] ?? '',
    ];
    $results = [];
    foreach ($map as $alias => $source) {
        $src = IMAGES_DIR . $source;
        $dst = IMAGES_DIR . $alias;
        if ($source && is_file($src) && $source !== $alias) {
            if (copy($src, $dst)) {
                $results[] = ['alias' => $alias, 'source' => $source, 'status' => 'ok'];
            } else {
                $results[] = ['alias' => $alias, 'source' => $source, 'status' => 'fail'];
            }
        } elseif ($source === $alias) {
            $results[] = ['alias' => $alias, 'source' => $source, 'status' => 'self'];
        }
    }
    return $results;
}

$action = $_GET['action'] ?? '';

// === LOGIN ===
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $user = trim($input['user'] ?? '');
    $pass = $input['pass'] ?? '';

    // Validation longueur
    if (strlen($user) > 100 || strlen($pass) > 200) {
        send_json(['error' => 'Identifiants invalides'], 401);
    }

    $ip = get_client_ip();
    $rl = check_rate_limit($ip);
    if ($rl['count'] >= LOGIN_MAX_ATTEMPTS) {
        log_action("LOGIN_BLOCKED rate_limit user=$user");
        send_json(['error' => 'Trop de tentatives. Reessayez dans 15 minutes.'], 429);
    }

    // Delai artificiel pour eviter timing attack
    $valid_user = hash_equals(ADMIN_USER, $user);
    $valid_pass = $valid_user && password_verify($pass, ADMIN_PASS_HASH);

    if ($valid_pass) {
        // Regenere l'ID de session
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['ip'] = $ip;
        $_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['login_time'] = time();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        clear_attempts($rl['file']);
        log_action("LOGIN_OK user=$user");
        send_json(['success' => true, 'csrf_token' => $_SESSION['csrf_token']]);
    }

    record_attempt($rl['file'], $rl['attempts']);
    log_action("LOGIN_FAIL user=$user");
    // Delai fixe pour eviter enumeration
    usleep(500000);
    send_json(['error' => 'Identifiants incorrects'], 401);
}

if ($action === 'logout') {
    log_action("LOGOUT");
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    header('Location: admin.php');
    exit;
}

if (!is_logged_in()) {
    include __DIR__ . '/login.html';
    exit;
}

// Regenere le token CSRF si pas encore set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// === ACTIONS AUTHENTIFIEES ===
require_csrf();

if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file'])) send_json(['error' => 'Aucun fichier'], 400);
    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) send_json(['error' => 'Erreur upload: ' . $file['error']], 400);
    if ($file['size'] > MAX_SIZE) send_json(['error' => 'Fichier trop volumineux (max 5 MB)'], 400);
    if ($file['size'] === 0) send_json(['error' => 'Fichier vide'], 400);

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT)) send_json(['error' => 'Extension non autorisee: ' . $ext], 400);

    // Double verification : extension + MIME reel
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, ALLOWED_MIME)) send_json(['error' => 'Type MIME non autorise: ' . $mime], 400);

    // Verification supplementaire pour eviter les doubles extensions (ex: image.php.jpg)
    $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
    if (preg_match('/\.(php|phtml|phar|js|html|htm|exe|sh|pl|cgi)$/i', $baseName)) {
        send_json(['error' => 'Nom de fichier invalide'], 400);
    }

    $slugName = slugify($baseName) . '.' . $ext;
    $dest = IMAGES_DIR . $slugName;
    if (file_exists($dest)) $slugName = slugify($baseName) . '-' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $ext;
    $dest = IMAGES_DIR . $slugName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) send_json(['error' => 'Echec deplacement fichier'], 500);
    @chmod($dest, 0644);

    log_action("UPLOAD $slugName");
    send_json(['success' => true, 'name' => $slugName, 'url' => '../assets/img/' . $slugName, 'size_kb' => round(filesize($dest)/1024), 'csrf_token' => $_SESSION['csrf_token']]);
}

if ($action === 'assign' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $role = $input['role'] ?? '';
    $image = basename($input['image'] ?? ''); // basename pour eviter path traversal
    if (!$image || !is_file(IMAGES_DIR . $image)) send_json(['error' => 'Image inexistante'], 400);
    $valid = ['hero_image','og_image','logo','service_duplication','service_perdue','service_reparation','service_diagnostic','service_multimedia','service_urgence'];
    if (!in_array($role, $valid)) send_json(['error' => 'Role invalide'], 400);
    $map = [
        'hero_image' => 'hero_image',
        'og_image' => 'og_image',
        'logo' => 'logo',
        'service_duplication' => 'services.duplication',
        'service_perdue' => 'services.perdue',
        'service_reparation' => 'services.reparation',
        'service_diagnostic' => 'services.diagnostic',
        'service_multimedia' => 'services.multimedia',
        'service_urgence' => 'services.urgence',
    ];
    $path = $map[$role];
    if (strpos($path, '.') !== false) {
        list($k1, $k2) = explode('.', $path);
        $config[$k1][$k2] = $image;
    } else {
        $config[$path] = $image;
    }
    save_config($config);
    $applied = apply_config_to_site($config);
    log_action("ASSIGN $role = $image");
    send_json(['success' => true, 'config' => $config, 'applied' => $applied, 'csrf_token' => $_SESSION['csrf_token']]);
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = basename($input['name'] ?? ''); // anti path traversal
    if (!$name || !is_file(IMAGES_DIR . $name)) send_json(['error' => 'Image inexistante'], 400);
    // Securite supplementaire : verifier que c'est bien dans IMAGES_DIR
    $real = realpath(IMAGES_DIR . $name);
    if ($real === false || dirname($real) !== realpath(IMAGES_DIR)) {
        log_action("DELETE_BLOCKED path_traversal $name");
        send_json(['error' => 'Chemin invalide'], 400);
    }
    $uses = get_usage($name);
    if (count($uses) > 0) send_json(['error' => 'Image utilisee: ' . implode(', ', $uses) . '. Reassignez d\'abord.'], 400);
    if (!unlink($real)) send_json(['error' => 'Echec suppression'], 500);
    log_action("DELETE $name");
    send_json(['success' => true, 'name' => $name, 'csrf_token' => $_SESSION['csrf_token']]);
}

if ($action === 'config') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        foreach (['site_name','phone','email','address','hours'] as $k) {
            if (isset($input[$k])) {
                $val = trim($input[$k]);
                if (mb_strlen($val) > 500) continue;
                $config[$k] = $val;
            }
        }
        save_config($config);
        log_action("CONFIG_UPDATE");
        send_json(['success' => true, 'config' => $config, 'csrf_token' => $_SESSION['csrf_token']]);
    } else {
        send_json(['config' => $config, 'csrf_token' => $_SESSION['csrf_token']]);
    }
}

if ($action === 'list') {
    $images = list_images(IMAGES_DIR);
    send_json(['images' => $images, 'config' => $config, 'csrf_token' => $_SESSION['csrf_token']]);
}

include __DIR__ . '/dashboard.html';
