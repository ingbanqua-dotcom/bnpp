<?php
/**
 * Classe SMTP simple pour envoyer des emails
 * Sans dépendances externes
 */
class SMTPMailer {
    private $host;
    private $port;
    private $user;
    private $password;
    private $secure;
    private $connection;

    public function __construct($host, $port, $user, $password, $secure = 'tls') {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->secure = $secure;
    }

    /**
     * Envoie un email via SMTP
     */
    public function send($to, $subject, $body, $from, $fromName = '', $replyTo = '') {
        try {
            // Créer la connexion socket
            $socket = @fsockopen(
                $this->secure === 'ssl' ? 'ssl://' . $this->host : $this->host,
                $this->port,
                $errno,
                $errstr,
                10
            );

            if (!$socket) {
                throw new Exception("Impossible de se connecter au serveur SMTP: $errstr ($errno)");
            }

            $this->connection = $socket;
            stream_set_timeout($this->connection, 10);

            // Lire la réponse du serveur
            $response = $this->readResponse();
            if (substr($response, 0, 3) !== '220') {
                throw new Exception("Erreur de connexion SMTP: $response");
            }

            // EHLO
            $this->sendCommand("EHLO localhost");
            $this->readResponse();

            // STARTTLS si nécessaire
            if ($this->secure === 'tls') {
                $this->sendCommand("STARTTLS");
                $this->readResponse();
                stream_context_set_option($this->connection, 'ssl', 'allow_self_signed', true);
                stream_context_set_option($this->connection, 'ssl', 'verify_peer', false);
                stream_socket_enable_crypto($this->connection, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                
                $this->sendCommand("EHLO localhost");
                $this->readResponse();
            }

            // Authentification
            $this->sendCommand("AUTH LOGIN");
            $this->readResponse();
            
            $this->sendCommand(base64_encode($this->user));
            $this->readResponse();
            
            $this->sendCommand(base64_encode($this->password));
            $response = $this->readResponse();
            
            if (substr($response, 0, 3) !== '235') {
                throw new Exception("Authentification SMTP échouée: $response");
            }

            // Définir l'expéditeur
            $this->sendCommand("MAIL FROM:<$from>");
            $this->readResponse();

            // Ajouter le destinataire
            $this->sendCommand("RCPT TO:<$to>");
            $this->readResponse();

            // Envoyer le message
            $this->sendCommand("DATA");
            $this->readResponse();

            // Construire les headers
            $headers = "From: " . ($fromName ? "$fromName <$from>" : $from) . "\r\n";
            $headers .= "To: $to\r\n";
            $headers .= "Subject: $subject\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            if ($replyTo) {
                $headers .= "Reply-To: $replyTo\r\n";
            }
            $headers .= "Date: " . date('r') . "\r\n";
            $headers .= "\r\n";

            // Envoyer les données
            $message = $headers . $body;
            $message = str_replace("\r\n.\r\n", "\r\n..\r\n", $message);
            
            fwrite($this->connection, $message . "\r\n.\r\n");
            $response = $this->readResponse();

            if (substr($response, 0, 3) !== '250') {
                throw new Exception("Erreur lors de l'envoi du message: $response");
            }

            // QUIT
            $this->sendCommand("QUIT");
            fclose($this->connection);

            return true;

        } catch (Exception $e) {
            if ($this->connection) {
                @fclose($this->connection);
            }
            throw $e;
        }
    }

    /**
     * Envoie une commande SMTP
     */
    private function sendCommand($command) {
        fwrite($this->connection, $command . "\r\n");
    }

    /**
     * Lit la réponse du serveur SMTP
     */
    private function readResponse() {
        $response = '';
        while (!feof($this->connection)) {
            $line = fgets($this->connection, 512);
            $response .= $line;
            
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }
}
?>
