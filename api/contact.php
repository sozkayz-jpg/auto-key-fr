<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$nom = htmlspecialchars(trim($_POST['nom'] ?? ''), ENT_QUOTES, 'UTF-8');
$tel = htmlspecialchars(trim($_POST['tel'] ?? ''), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$ville = htmlspecialchars(trim($_POST['ville'] ?? ''), ENT_QUOTES, 'UTF-8');
$marque = htmlspecialchars(trim($_POST['marque'] ?? ''), ENT_QUOTES, 'UTF-8');
$annee = htmlspecialchars(trim($_POST['annee'] ?? ''), ENT_QUOTES, 'UTF-8');
$urgence = htmlspecialchars(trim($_POST['urgence'] ?? ''), ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');

if (!$nom || !$tel || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Nom, téléphone et message sont obligatoires.']);
    exit;
}

$to = 'contact@allo-cle-auto.fr';
$sujet = 'Demande de devis - ' . $nom;
$corps = "Nom : $nom
Téléphone : $tel
";
if ($email) $corps .= "Email : $email
";
if ($ville) $corps .= "Ville : $ville
";
if ($marque) $corps .= "Véhicule : $marque" . ($annee ? " ($annee)" : "") . "
";
if ($urgence) $corps .= "Demande : $urgence
";
$corps .= "
Message :
$message
";

$headers = "From: $to

Reply-To: " . ($email ?: $to) . "

Content-Type: text/plain; charset=UTF-8";

if (mail($to, $sujet, $corps, $headers)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => "L'envoi a échoué. Veuillez appeler le 07 46 57 17 03."]);
}
