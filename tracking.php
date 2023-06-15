<?php
session_start();
header("X-Robots-Tag: noindex, nofollow");
$pdo = require_once './database/db.php';
// Récupérer les paramètres de l'URL
$oid = $_GET['oid'] ?? "";
$rid = $_GET['rid'] ?? "";
$encryptedId = $_GET['kid'] ?? "";
$landingPageUrl = $_SESSION['lp'];
// Vérifier si l'ID encrypté correspond à l'ID attendu (oid-rid)
$key = 'H@kkufr@mwork12*';
$idToDecrypt = openssl_decrypt($encryptedId,'AES-256-CBC', $key, 0, '1234567890123456');
if ($idToDecrypt != null) {
    list($decryptedRid, $decryptedOid) = explode('-', $idToDecrypt);
    if ($decryptedRid === $rid && $decryptedOid === $oid) {
        // L'URL est vérifiée donc tu peux effectuer les mises à jour dans la base  données ici
        try {
            $statement = $pdo->prepare("UPDATE MSB_tracking
                SET IsVisited = 1, VisitedDateTime = NOW()
                WHERE OrgID = '$oid' AND RecordId = '$rid'");
            $statement->execute();
        } catch (PDOException $e) {
            echo "error" . $e->getMessage();
        }
        // Rediriger le visiteur vers la landing page
        header("Location: $landingPageUrl");
    } else {
        // L'URL n'est pas vérifiée donc faire ce que tu souhaites dans ce cas ici
        try {
            $statement = $pdo->prepare("INSERT INTO 
               MSB_tracking(OrgID, RecordId, LandingPage, CreationDateTime, EncryptedId, Trusted, IsVisited, VisitedDateTime, IsSync, SyncDateTime)
                VALUES ('$oid', '$rid', '$lp', NOW(), '$encryptedId', 1, 0, NULL, 0, NULL)");
            $statement->execute();
        } catch (PDOException $e) {
            echo "error" . $e->getMessage();
        }
    }
} else {
    echo "kao";
}
