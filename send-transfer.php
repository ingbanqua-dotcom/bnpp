<?php
/**
 * Traitement des virements - send-transfer.php
 * 
 * ⚠️ IMPORTANT - NETLIFY:
 * Netlify n'héberge que du contenu statique et ne supporte pas PHP nativement.
 * Pour utiliser ce script PHP, vous avez 2 options:
 * 
 * 1. Utiliser un serveur PHP séparé (Heroku, Railway, Digital Ocean, etc.)
 * 2. Convertir en Netlify Functions (JavaScript/Node.js)
 * 3. Utiliser un service email externe (Mailgun, SendGrid, Brevo)
 * 
 * Consultez la documentation d'intégration dans le dossier README.md
 */

// Inclure la configuration et la classe SMTP
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/SMTPMailer.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Vérifier que la requête est POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendJsonResponse(false, 'Méthode non autorisée');
}

try {
    // Récupérer et valider les données
    $senderEmail = filter_var($_POST['senderEmail'] ?? '', FILTER_SANITIZE_EMAIL);
    $beneficiaryFirstName = htmlspecialchars($_POST['beneficiaryFirstName'] ?? '', ENT_QUOTES, 'UTF-8');
    $beneficiary = htmlspecialchars($_POST['beneficiary'] ?? '', ENT_QUOTES, 'UTF-8');
    $beneficiaryEmail = filter_var($_POST['beneficiaryEmail'] ?? '', FILTER_SANITIZE_EMAIL);
    $iban = htmlspecialchars($_POST['iban'] ?? '', ENT_QUOTES, 'UTF-8');
    $amount = htmlspecialchars($_POST['amount'] ?? '', ENT_QUOTES, 'UTF-8');
    $motive = htmlspecialchars($_POST['motive'] ?? '', ENT_QUOTES, 'UTF-8');

    // Valider les emails
    if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email du donneur d\'ordre invalide');
    }
    if (!filter_var($beneficiaryEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email du bénéficiaire invalide');
    }

    // Valider les champs requis
    if (empty($beneficiary) || empty($iban) || empty($amount) || empty($motive) || empty($beneficiaryFirstName)) {
        throw new Exception('Tous les champs sont requis');
    }

    // Générer les références
    $operationRef = generateReference('TRF');
    $beneficiaryRef = generateReference('BEN');
    $senderRef = generateReference('SENDER');

    // Formater la date
    $today = new DateTime();
    $formattedDate = $today->format('d/m/Y');

    // Construire le message HTML
    $messageBody = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #008f45; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { background: #f5f5f5; padding: 20px; }
        .section { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #008f45; }
        .section h3 { margin-top: 0; color: #008f45; border-bottom: 2px solid #008f45; padding-bottom: 10px; }
        .row { padding: 8px 0; }
        .row b { display: inline-block; width: 150px; }
        .warning { background: #fff8e1; border-left: 4px solid #e4aa00; padding: 15px; margin: 15px 0; border-radius: 4px; font-size: 12px; }
        .footer { text-align: center; color: #666; font-size: 12px; padding-top: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>✓ Virement Initié</h2>
        </div>
        
        <div class='content'>
            <div class='section'>
                <strong style='color: #008f45; font-size: 16px;'>Statut : Virement initié</strong>
                <p style='color: #666; margin: 10px 0 0 0;'>Votre virement a été enregistré avec succès</p>
            </div>
            
            <div class='section'>
                <h3>DONNEUR D'ORDRE</h3>
                <div class='row'><b>Nom :</b> " . USER_FULLNAME . "</div>
                <div class='row'><b>Email :</b> $senderEmail</div>
                <div class='row'><b>Identifiant :</b> $senderRef</div>
                <div class='row'><b>Photo :</b> <span style='color: #999;'>[Emplacement réservé à la photo]</span></div>
            </div>
            
            <div class='section'>
                <h3>BÉNÉFICIAIRE</h3>
                <div class='row'><b>Nom :</b> $beneficiary</div>
                <div class='row'><b>Prénom :</b> $beneficiaryFirstName</div>
                <div class='row'><b>Email :</b> $beneficiaryEmail</div>
                <div class='row'><b>IBAN :</b> $iban</div>
                <div class='row'><b>Référence bénéficiaire :</b> $beneficiaryRef</div>
            </div>
            
            <div class='section'>
                <h3>DÉTAILS DE L'OPÉRATION</h3>
                <div class='row'><b>Établissement émetteur :</b> " . BANK_NAME . "</div>
                <div class='row'><b>Montant :</b> $amount EUR</div>
                <div class='row'><b>Date d'exécution :</b> $formattedDate</div>
                <div class='row'><b>Référence de l'opération :</b> $operationRef</div>
                <div class='row'><b>Motif :</b> $motive</div>
            </div>
            
            <div class='warning'>
                <strong>Remarque institutionnelle :</strong><br><br>
                Selon les règles interbancaires en vigueur, un délai standard de traitement de 24 à 48 heures ouvrées peut être nécessaire pour le positionnement et le crédit définitif des fonds sur votre compte bancaire.
            </div>
            
            <div class='footer'>
                <p>Cordialement,<br><strong>L'équipe BNP Paribas Fortis</strong></p>
            </div>
        </div>
    </div>
</body>
</html>
";

    // Envoyer l'email via SMTP
    $to = $senderEmail;
    $subject = 'Confirmation de virement - ' . BANK_NAME . ' - Ref: ' . $operationRef;

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">\r\n";
    $headers .= "Reply-To: " . EMAIL_REPLY_TO . "\r\n";

    // Utiliser SMTPMailer pour envoyer l'email
    try {
        $mailer = new SMTPMailer(SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASSWORD, SMTP_SECURE);
        $mailer->send($to, $subject, $messageBody, EMAIL_FROM, EMAIL_FROM_NAME, EMAIL_REPLY_TO);
        
        logMessage("Email envoyé à: $to avec référence: $operationRef", 'SUCCESS');
        echo json_encode([
            'success' => true,
            'message' => 'Email de confirmation envoyé avec succès à ' . $senderEmail,
            'operationRef' => $operationRef
        ]);
    } catch (Exception $e) {
        throw new Exception('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
    }

} catch (Exception $e) {
    http_response_code(500);
    sendJsonResponse(false, 'Erreur serveur: ' . $e->getMessage());
    logMessage('Erreur: ' . $e->getMessage(), 'ERROR');
}
?>

