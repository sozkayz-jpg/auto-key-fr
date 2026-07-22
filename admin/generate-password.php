<?php
// Script pour générer le hash de mot de passe admin.
// Usage : php generate-password.php "votre_mot_de_passe"
// Copiez ensuite le hash obtenu dans admin.php à la ligne ADMIN_PASS_HASH

if (!isset($argv[1])) {
  echo "Usage: php generate-password.php \"votre_mot_de_passe\"\n";
  exit(1);
}
$hash = password_hash($argv[1], PASSWORD_BCRYPT);
echo "Hash généré :\n$hash\n\n";
echo "Copiez ce hash dans admin.php à la ligne :\n";
echo "define('ADMIN_PASS_HASH', '" . $hash . "');\n";