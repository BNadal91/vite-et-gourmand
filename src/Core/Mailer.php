<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Envoi des e-mails transactionnels.
 * - MAIL_DRIVER=log  : les e-mails sont écrits dans storage/logs/mails.log (développement)
 * - MAIL_DRIVER=smtp : envoi réel via un serveur SMTP authentifié en TLS (Brevo, Mailjet, Gmail…)
 * Client SMTP volontairement simple et sans dépendance externe.
 */
final class Mailer
{
    public static function send(string $to, string $subject, string $view, array $data = []): bool
    {
        $html = View::fetch('emails/layout', ['content' => View::fetch('emails/' . $view, $data), 'subject' => $subject]);
        // Protection contre l'injection d'en-têtes : pas de retour chariot dans l'adresse ou le sujet
        $to = str_replace(["\r", "\n"], '', $to);
        $subject = str_replace(["\r", "\n"], '', $subject);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            if (Env::get('MAIL_DRIVER', 'log') === 'smtp') {
                return self::smtp($to, $subject, $html);
            }
            return self::log($to, $subject, $html);
        } catch (\Throwable $e) {
            error_log('[Mailer] ' . $e->getMessage());
            return false;
        }
    }

    private static function log(string $to, string $subject, string $html): bool
    {
        $dir = ROOT_PATH . '/storage/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $entry = sprintf("=== %s ===\nÀ : %s\nSujet : %s\n%s\n\n", date('Y-m-d H:i:s'), $to, $subject,
            trim(html_entity_decode(strip_tags(preg_replace('/<(br|\/p|\/h\d|\/li)>/i', "\n", $html)))));
        return file_put_contents($dir . '/mails.log', $entry, FILE_APPEND | LOCK_EX) !== false;
    }

    private static function smtp(string $to, string $subject, string $html): bool
    {
        $host = (string) Env::get('SMTP_HOST');
        $port = (int) Env::get('SMTP_PORT', '587');
        $user = (string) Env::get('SMTP_USER');
        $pass = (string) Env::get('SMTP_PASSWORD');
        $from = (string) Env::get('MAIL_FROM', 'no-reply@vite-gourmand.fr');

        $socket = stream_socket_client(($port === 465 ? 'ssl://' : 'tcp://') . "$host:$port", $errno, $errstr, 15);
        if (!$socket) {
            throw new \RuntimeException("Connexion SMTP impossible : $errstr");
        }
        $read = static function () use ($socket): string {
            $data = '';
            while (($line = fgets($socket, 515)) !== false) {
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return $data;
        };
        $cmd = static function (string $c, int $expected) use ($socket, $read): void {
            fwrite($socket, $c . "\r\n");
            $r = $read();
            if ((int) substr($r, 0, 3) !== $expected) {
                throw new \RuntimeException('SMTP : réponse inattendue « ' . trim($r) . ' »');
            }
        };

        $read();
        $cmd('EHLO vite-gourmand', 250);
        if ($port !== 465) {
            $cmd('STARTTLS', 220);
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
            $cmd('EHLO vite-gourmand', 250);
        }
        $cmd('AUTH LOGIN', 334);
        $cmd(base64_encode($user), 334);
        $cmd(base64_encode($pass), 235);
        $cmd("MAIL FROM:<$from>", 250);
        $cmd("RCPT TO:<$to>", 250);
        $cmd('DATA', 354);

        $headers = [
            'Date: ' . date('r'),
            'From: Vite & Gourmand <' . $from . '>',
            'To: <' . $to . '>',
            'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];
        $body = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($html)) . "\r\n.";
        $cmd($body, 250);
        $cmd('QUIT', 221);
        fclose($socket);
        return true;
    }
}
