<?php

/**
 * Centralized email/SMTP configuration + delivery helper.
 *
 * Credentials live OUTSIDE source code, read in this priority order:
 *   1. Environment variables (MAIL_*)
 *   2. bookshop/config.ini   (git-ignored)
 *   3. Defaults: local mode (messages are logged instead of sent)
 *
 * MAIL_ENVIRONMENT=local is for development only - verification codes are
 * appended to bookshop/logs/mail_codes.log. Set it to "production" with
 * real SMTP credentials before deploying.
 */

function mail_env(string $key)
{
    $value = getenv($key);
    if ($value !== false && $value !== "") {
        return $value;
    }

    static $ini = null;
    if ($ini === null) {
        $ini = array();
        $iniFile = __DIR__ . DIRECTORY_SEPARATOR . "config.ini";
        if (is_readable($iniFile)) {
            $parsed = parse_ini_file($iniFile, false, INI_SCANNER_RAW);
            if (is_array($parsed)) {
                $ini = $parsed;
            }
        }
    }

    if (isset($ini[$key]) && trim((string)$ini[$key]) !== "") {
        return trim((string)$ini[$key]);
    }

    return null;
}

function app_mail_config(): array
{
    return array(
        "environment" => strtolower(mail_env("MAIL_ENVIRONMENT") ?: "local"),
        "host"        => mail_env("MAIL_SMTP_HOST") ?: "smtp.gmail.com",
        "port"        => (int)(mail_env("MAIL_SMTP_PORT") ?: 465),
        "username"    => mail_env("MAIL_SMTP_USER") ?: "",
        "password"    => mail_env("MAIL_SMTP_PASS") ?: "",
        "from_email"  => mail_env("MAIL_FROM_EMAIL") ?: "no-reply@localhost",
        "from_name"   => mail_env("MAIL_FROM_NAME") ?: "Unicorn Book Shop",
    );
}

/** Apply configured SMTP settings to a PHPMailer instance (secrets stay server-side). */
function app_mail_configure($mail): array
{
    $cfg = app_mail_config();

    $mail->IsSMTP();
    $mail->Host       = $cfg["host"];
    $mail->SMTPAuth   = true;
    $mail->Username   = $cfg["username"];
    $mail->Password   = $cfg["password"];
    $mail->SMTPSecure = "ssl";
    $mail->Port       = $cfg["port"];
    $mail->setFrom($cfg["from_email"], $cfg["from_name"]);
    $mail->addReplyTo($cfg["from_email"], $cfg["from_name"]);

    return $cfg;
}

/**
 * Deliver the prepared PHPMailer message.
 *
 * In local mode nothing leaves the machine: the message (and verification
 * code, when applicable) is appended to logs/mail_codes.log and the call
 * succeeds so development flows keep working.
 *
 * Never log passwords or SMTP credentials here.
 */
function app_mail_deliver($mail, ?string $verificationCode = null): bool
{
    $cfg = app_mail_config();

    if ($cfg["environment"] === "production") {
        return (bool)$mail->send();
    }

    // Local development mode: capture the message to a log file.
    $logDir = __DIR__ . DIRECTORY_SEPARATOR . "logs";
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0775, true);
    }

    $toAddresses = method_exists($mail, "getToAddresses") ? $mail->getToAddresses() : array();
    $to = "";
    if (!empty($toAddresses[0][0])) {
        $to = (string)$toAddresses[0][0];
    }

    $record = array(
        "timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
        "mode"      => "local",
        "to"        => $to,
        "subject"   => isset($mail->Subject) ? (string)$mail->Subject : "",
    );

    if ($verificationCode !== null && $verificationCode !== "") {
        $record["verification_code"] = $verificationCode;
    }

    @file_put_contents(
        $logDir . DIRECTORY_SEPARATOR . "mail_codes.log",
        json_encode($record) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );

    return true;
}
