<?php
session_start();
header("X-Robots-Tag: noindex, nofollow");
require_once('vendor/autoload.php');

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

$pdo = require_once './database/db.php';
// Récupérer les paramètres de l'URL
$oid = $_GET['oid'] ?? "";
$rid = $_GET['rid'] ?? "";
$lp = $_GET['lp'] ?? "";
// Générer l'ID encrypté (kid)
$key = 'H@kkufr@mwork12*';
$idToEncrypt = $rid . '-' . $oid;
$encryptedId = openssl_encrypt($idToEncrypt, 'AES-256-CBC', $key, 0, '1234567890123456');
// Créer l'URL de la landing page
$landingPageUrl = urlencode($lp);
//utilisation de la variable super global de session pour pouvoir set la variable landing page dans le page tracking
$_SESSION['lp'] = $landingPageUrl;
// Créer l'URL complète du QR code
$qrCodeUrl = "MSB/QRcode.php?oid=$oid&rid=$rid&kid=$encryptedId&lp=$landingPageUrl";
// Générer le QR code
$writer = new PngWriter();
// Create QR code
$qrCode = QrCode::create($qrCodeUrl)
    ->setEncoding(new Encoding('UTF-8'))
    ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
    ->setSize(300)
    ->setMargin(10)
    ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
    ->setForegroundColor(new Color(0, 0, 0))
    ->setBackgroundColor(new Color(255, 255, 255));
if ($qrCode) {
    try {
        $statement = $pdo->prepare("INSERT INTO 
            MSB_tracking(OrgID, RecordId, LandingPage, CreationDateTime, EncryptedId, Trusted, IsVisited, VisitedDateTime, IsSync, SyncDateTime)
            VALUES ('$oid', '$rid', '$lp', NOW(), '$encryptedId', 1, 0, NULL, 0, NULL)");
        $statement->execute();
    } catch (PDOException $e) {
        echo "error" . $e->getMessage();
    }
    // Afficher le QR code
    $result = $writer->write($qrCode);
    header('Content-Type: ' . $result->getMimeType());
    echo $result->getString();
}
