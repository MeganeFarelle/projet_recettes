<?php

// Indiquer à PHP où trouver openssl.cnf
putenv("OPENSSL_CONF=" . __DIR__ . "/openssl.cnf");

// Dossier où mettre les clés
$dir = __DIR__ . '/config/jwt';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$passphrase = "jwtpass";

// Paramètres RSA
$config = [
    "private_key_bits" => 4096,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
];

// Générer la clé privée
$privateKey = openssl_pkey_new($config);

if ($privateKey === false) {
    die("❌ Erreur : Impossible de générer la clé RSA\n");
}

// Exporter la clé privée
openssl_pkey_export($privateKey, $privateKeyOutput, $passphrase);

// Extraire la clé publique
$keyDetails = openssl_pkey_get_details($privateKey);
$publicKeyOutput = $keyDetails["key"];

// Sauvegarder les fichiers
file_put_contents($dir . '/private.pem', $privateKeyOutput);
file_put_contents($dir . '/public.pem', $publicKeyOutput);

echo "✔ Clés générées dans config/jwt/\n";
