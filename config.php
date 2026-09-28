<?php
/**
 * Configuration générale du projet BNP Paribas Fortis
 * À personnaliser selon votre environnement
 */

// ============================================
// 1. ENVIRONNEMENT
// ============================================
define('ENVIRONMENT', 'production'); // 'local' ou 'production'
define('DEBUG', false); // false en production

// ============================================
// 2. CHEMIN RACINE
// ============================================
define('BASE_PATH', __DIR__);
define('LOGS_PATH', BASE_PATH . '/logs');

// ============================================
// 4. INFORMATIONS BANQUE
// ============================================
define('BANK_NAME', 'BNP Paribas Fortis');
define('BANK_COUNTRY', 'Belgium');
define('BIC_CODE', 'GEBABEBB');
define('IBAN_PREFIX', 'BE');

// ============================================
// 5. DONNÉES FICTIVES (Profil utilisateur)
// ============================================
define('USER_FULLNAME', 'Franck Eric');
define('USER_FIRST_NAME', 'FRANÇOIS');
define('USER_LAST_NAME', 'GARCIA');
define('USER_EMAIL_CR', 'francisco.garcia@example.com');
define('USER_ID', 'CL-2026-12345');

// Soldes de compte (fictifs)
define('ACCOUNT_BALANCE', '80000.00');
define('ACCOUNT_NUMBER_MASKED', '**** **** 2020');
define('ACCOUNT_NUMBER_FULL', 'BE40357565216695261520');

// Infos carte (fictives)
define('CARD_NUMBER', '4532123456786738');
define('CARD_NUMBER_MASKED', '4532 •••• •••• 6738');
define('CARD_EXPIRY', '12/27');
define('CARD_CVV', '965');
define('CARD_NAME', 'FRANÇOIS GARCIA');

// ============================================
// 6. PARAMÈTRES DE VIREMENT
// ============================================
define('TRANSFER_MIN_AMOUNT', 0.01);
define('TRANSFER_MAX_AMOUNT', 1000000);
define('TRANSFER_PROCESSING_TIME', '24 à 48 heures ouvrées');

// ============================================
// 7. COULEURS THÈME
// ============================================
define('PRIMARY_COLOR', '#008f45'); // Vert BNP
define('SECONDARY_COLOR', '#006f36');
define('DANGER_COLOR', '#d71920');
define('WARNING_COLOR', '#e4aa00');
define('SUCCESS_COLOR', '#087b39');

// ============================================
// 8. FONCTION UTILITAIRES
// ============================================

/**
 * Crée les dossiers nécessaires s'ils n'existent pas
 */
function createRequiredDirectories() {
    $dirs = [LOGS_PATH];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}

/**
 * Enregistre un message dans les logs
 */
function logMessage($message, $type = 'INFO') {
    if (!DEBUG) return;
    
    createRequiredDirectories();
    $logFile = LOGS_PATH . '/app_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type] $message\n";
    
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * Envoie une réponse JSON
 */
function sendJsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

/**
 * Génère une référence unique
 */
function generateReference($prefix = 'REF', $length = 9) {
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $ref = $prefix . strtoupper(substr(str_shuffle($chars), 0, $length));
    return $ref;
}

/**
 * Formate une date au format français
 */
function formatDateFR($date = null) {
    $date = $date ?: new DateTime();
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    return $date->format('d/m/Y');
}

/**
 * Formate une date et heure au format français
 */
function formatDateTimeFR($date = null) {
    $date = $date ?: new DateTime();
    if (is_string($date)) {
        $date = new DateTime($date);
    }
    return $date->format('d/m/Y H:i:s');
}

/**
 * Formate un montant en EUR
 */
function formatAmount($amount) {
    return number_format((float)$amount, 2, ',', ' ') . ' €';
}

/**
 * Valide un IBAN
 */
function validateIBAN($iban) {
    $iban = strtoupper(str_replace(' ', '', $iban));
    if (!preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{1,30}$/', $iban)) {
        return false;
    }
    return true;
}

/**
 * Valide un email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Nettoie et valide une entrée utilisateur
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Créer les dossiers nécessaires au démarrage
createRequiredDirectories();

?>
