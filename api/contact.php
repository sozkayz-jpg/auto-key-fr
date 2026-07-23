<?php
/**
 * API Contact - Version securisee
 * Auto-Key.fr / allo-cle-auto.fr
 *
 * Correctifs de securite :
 * - Rate limiting par IP (5 requetes / 15 min)
 * - Honeypot anti-spam
 * - Validation stricte telephone FR
 * - Headers mail securises
 * - CORS restrictif
 * - Pas d'email open relay
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// CORS restrictif - seulement notre domaine
$allowed_origins = [
    'https://www.allo-cle-auto.fr',
    'https://allo-cle-auto.fr',
    'http://allo-cle-auto.fr',
    'http://www.allo-cle-auto.fr'
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
}
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Methode uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Methode non autorisee']);
    exit;
}

// Rate limiting par IP via fichier
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_dir = sys_get_temp_dir() . '/allo_cle_ratelimit';
if (!is_dir($rate_dir)) @mkdir($rate_dir, 0700, true);
$rate_file = $rate_dir . '/' . md5($ip) . '.txt';
$now = time();
$limit = 5; // 5 requetes
$window = 900; // 15 minutes

$attempts = [];
if (file_exists($rate_file)) {
    $data = @file_get_contents($rate_file);
    $attempts = $data ? array_filter(explode(',', trim($data)), function($t) use ($now, $window) {
        return is_numeric($t) && ($now - (int)$t) < $window;
    }) : [];
}
if (count($attempts) >= $limit) {
    http_response_code(429);
    echo json_encode(['error' => 'Trop de requetes. Reessayez dans quelques minutes.']);
    exit;
}
$attempts[] = $now;
@file_put_contents($rate_file, implode(',', $attempts), LOCK_EX);

// Validation des inputs
$nom     = trim($_POST['nom']     ?? '');
$tel     = trim($_POST['tel']     ?? '');
$email   = trim($_POST['email']   ?? '');
$ville   = trim($_POST['ville']   ?? '');
$marque  = trim($_POST['marque']  ?? '');
$annee   = trim($_POST['annee']   ?? '');
$urgence = trim($_POST['urgence'] ?? '');
$message = trim($_POST['message'] ?? '');

// Honeypot anti-spam (champ invisible que les robots remplissent)
$honeypot = trim($_POST['website'] ?? '');
if ($honeypot !== '') {
    // Faux succes pour ne pas alerter le bot
    echo json_encode(['success' => true]);
    exit;
}

// Validation stricte
if (empty($nom) || empty($tel) || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nom, telephone et message sont obligatoires.']);
    exit;
}

// Longueurs max pour eviter abus
if (mb_strlen($nom) > 100 || mb_strlen($tel) > 30 || mb_strlen($message) > 2000 ||
    mb_strlen($ville) > 100 || mb_strlen($marque) > 100 || mb_strlen($annee) > 10 ||
    mb_strlen($urgence) > 50) {
    http_response_code(400);
    echo json_encode(['error' => 'Donnees trop longues.']);
    exit;
}

// Validation telephone francais : 10 chiffres, espaces/tirets/points/+ autorises
$tel_clean = preg_replace('/[\s\-\.\(\)]/', '', $tel);
if (!preg_match('/^(\+33|0)[1-9](\d{2}){4}$/', $tel_clean)) {
    http_response_code(400);
    echo json_encode(['error' => 'Numero de telephone invalide.']);
    exit;
}

// Validation email (optionnel)
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email invalide.']);
    exit;
}

// Validation annee si fournie
if (!empty($annee) && !preg_match('/^(19|20)\d{2}$/', $annee)) {
    http_response_code(400);
    echo json_encode(['error' => 'Annee invalide.']);
    exit;
}

// Verification anti-spam basique : pas de liens dans le message
$link_count = preg_match_all('/https?:\/\//i', $message);
if ($link_count > 3) {
    http_response_code(400);
    echo json_encode(['error' => 'Trop de liens dans le message.']);
    exit;
}

// Log pour detecter les abus
$log_dir = __DIR__ . '/../logs';
if (!is_dir($log_dir)) @mkdir($log_dir, 0700, true);
@file_put_contents(
    $log_dir . '/contact.log',
    date('Y-m-d H:i:s') . " | $ip | $nom | $tel | $email | " . mb_strlen($message) . " chars\n",
    FILE_APPEND | LOCK_EX
);

// Envoi de l'email avec headers securises
$to = 'contact@allo-cle-auto.fr';

// Sanitization pour les headers (pas de \r\n)
$nom_safe     = str_replace(["\r", "\n"], ' ', $nom);
$sujet        = 'Demande de devis - ' . $nom_safe;
$sujet_clean  = str_replace(["\r", "\n", "\0"], '', $sujet);

// Validation anti-injection dans le sujet
if (preg_match('/[\r\n\0]/', $sujet)) {
    http_response_code(400);
    echo json_encode(['error' => 'Donnees invalides.']);
    exit;
}

$corps  = "Nom : $nom\n";
$corps .= "Telephone : $tel\n";
if ($email)   $corps .= "Email : $email\n";
if ($ville)   $corps .= "Ville : $ville\n";
if ($marque)  $corps .= "Vehicule : $marque" . ($annee ? " ($annee)" : '') . "\n";
if ($urgence) $corps .= "Demande : $urgence\n";
$corps .= "\nMessage :\n$message\n";

// Headers mail securises (anti-injection \r\n, encodage UTF-8)
$headers = [];
$headers[] = 'From: Auto-Key <noreply@allo-cle-auto.fr>';
$headers[] = 'Reply-To: ' . ($email ?: 'noreply@allo-cle-auto.fr');
$headers[] = 'X-Mailer: PHP/' . phpversion();
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'Content-Transfer-Encoding: 8bit';
$headers_str = implode("\r\n", $headers);

// Envoi
$sent = @mail($to, $sujet_clean, $corps, $headers_str);

if ($sent) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => "L'envoi a echoue. Veuillez appeler le 07 46 57 17 03."]);
}
