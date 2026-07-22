<?php
session_start();

define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', '$2y$10$wH8mV3qKz9xY7nL4pR2aEeJ6sT5uV1wB0cD6fG7hI3jK2lM1nO0pQ');
define('IMAGES_DIR', __DIR__ . '/../assets/img/');
define('CONFIG_FILE', __DIR__ . '/config.json');
define('MAX_SIZE', 5 * 1024 * 1024);
define('ALLOWED_EXT', ['jpg','jpeg','png','webp','gif','svg']);
define('ALLOWED_MIME', ['image/jpeg','image/png','image/webp','image/gif','image/svg+xml','image/svg']);

$config = file_exists(CONFIG_FILE) ? json_decode(file_get_contents(CONFIG_FILE), true) : default_config();

function default_config() {
  return [
    'site_name' => 'Auto-Key',
    'phone' => '07 46 57 17 03',
    'email' => 'contact@auto-key.fr',
    'address' => '10 Rue du Collège, 74950 Scionzier',
    'hours' => 'Ouvert 24h/24 · 7j/7',
    'hero_image' => 'hero-key.jpg',
    'services' => [
      'duplication' => 'service-duplication.jpg',
      'perdue' => 'service-perdue.jpg',
      'reparation' => 'service-reparation.jpg',
      'diagnostic' => 'service-diagnostic.jpg',
      'multimedia' => 'service-multimedia.jpg',
      'urgence' => 'service-urgence.jpg',
    ],
    'og_image' => 'og-image.jpg',
    'logo' => 'logo.svg',
  ];
}

function is_logged_in() {
  return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function send_json($data, $code = 200) {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  exit;
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
  foreach (glob($dir . '*') as $f) {
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
  if ($config['hero_image'] === $name) $uses[] = 'Hero accueil';
  if ($config['og_image'] === $name) $uses[] = 'Open Graph';
  if ($config['logo'] === $name) $uses[] = 'Logo';
  foreach ($config['services'] as $key => $img) {
    if ($img === $name) $uses[] = 'Service ' . $key;
  }
  return $uses;
}

function save_config($c) {
  file_put_contents(CONFIG_FILE, json_encode($c, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function apply_config_to_site($c) {
  $map = [
    'hero-key.jpg' => $c['hero_image'],
    'service-duplication.jpg' => $c['services']['duplication'] ?? 'service-duplication.jpg',
    'service-perdue.jpg' => $c['services']['perdue'] ?? 'service-perdue.jpg',
    'service-reparation.jpg' => $c['services']['reparation'] ?? 'service-reparation.jpg',
    'service-diagnostic.jpg' => $c['services']['diagnostic'] ?? 'service-diagnostic.jpg',
    'service-multimedia.jpg' => $c['services']['multimedia'] ?? 'service-multimedia.jpg',
    'service-urgence.jpg' => $c['services']['urgence'] ?? 'service-urgence.jpg',
    'og-image.jpg' => $c['og_image'],
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

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  $user = $input['user'] ?? '';
  $pass = $input['pass'] ?? '';
  if ($user === ADMIN_USER && password_verify($pass, ADMIN_PASS_HASH)) {
    $_SESSION['admin_logged_in'] = true;
    send_json(['success' => true]);
  }
  send_json(['error' => 'Identifiants incorrects'], 401);
}

if (!is_logged_in()) {
  include __DIR__ . '/login.html';
  exit;
}

if ($action === 'logout') {
  session_destroy();
  header('Location: admin.php');
  exit;
}

if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($_FILES['file'])) send_json(['error' => 'Aucun fichier'], 400);
  $file = $_FILES['file'];
  if ($file['error'] !== UPLOAD_ERR_OK) send_json(['error' => 'Erreur upload: ' . $file['error']], 400);
  if ($file['size'] > MAX_SIZE) send_json(['error' => 'Fichier trop volumineux (max 5 MB)'], 400);
  $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, ALLOWED_EXT)) send_json(['error' => 'Extension non autorisée: ' . $ext], 400);
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);
  $mimeMap = ['image/svg' => 'image/svg+xml'];
  if (isset($mimeMap[$mime])) $mime = $mimeMap[$mime];
  if (!in_array($mime, ALLOWED_MIME)) send_json(['error' => 'Type MIME non autorisé: ' . $mime], 400);
  $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
  $slugName = slugify($baseName) . '.' . $ext;
  $dest = IMAGES_DIR . $slugName;
  if (file_exists($dest)) $slugName = slugify($baseName) . '-' . time() . '.' . $ext;
  $dest = IMAGES_DIR . $slugName;
  if (!move_uploaded_file($file['tmp_name'], $dest)) send_json(['error' => 'Échec déplacement fichier'], 500);
  send_json(['success' => true, 'name' => $slugName, 'url' => '../assets/img/' . $slugName, 'size_kb' => round(filesize($dest)/1024)]);
}

if ($action === 'assign' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  $role = $input['role'] ?? '';
  $image = $input['image'] ?? '';
  if (!$image || !is_file(IMAGES_DIR . $image)) send_json(['error' => 'Image inexistante'], 400);
  $valid = ['hero_image','og_image','logo','service_duplication','service_perdue','service_reparation','service_diagnostic','service_multimedia','service_urgence'];
  if (!in_array($role, $valid)) send_json(['error' => 'Rôle invalide'], 400);
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
  send_json(['success' => true, 'config' => $config, 'applied' => $applied]);
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true);
  $name = $input['name'] ?? '';
  if (!$name || !is_file(IMAGES_DIR . $name)) send_json(['error' => 'Image inexistante'], 400);
  $uses = get_usage($name);
  if (count($uses) > 0) send_json(['error' => 'Image utilisée: ' . implode(', ', $uses) . '. Réassignez d\'abord.'], 400);
  unlink(IMAGES_DIR . $name);
  send_json(['success' => true, 'name' => $name]);
}

if ($action === 'config') {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    foreach (['site_name','phone','email','address','hours'] as $k) {
      if (isset($input[$k])) $config[$k] = $input[$k];
    }
    save_config($config);
    send_json(['success' => true, 'config' => $config]);
  } else {
    send_json(['config' => $config]);
  }
}

if ($action === 'list') {
  $images = list_images(IMAGES_DIR);
  send_json(['images' => $images, 'config' => $config]);
}

if ($action === 'api') {
  header('Content-Type: application/json; charset=utf-8');
  $images = list_images(IMAGES_DIR);
  echo json_encode(['images' => $images, 'count' => count($images)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  exit;
}

include __DIR__ . '/dashboard.html';