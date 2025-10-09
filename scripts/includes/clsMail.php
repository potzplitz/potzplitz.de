<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mail {
    private $to;
    private $subject;
    private $message;

    public function __construct($to) {
        $this->to = $to;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function send() {
        $mail = new PHPMailer(true);

        try {
            // SMTP Konfiguration
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port       = SMTP_PORT;

            // Absender + Empfänger
            $mail->setFrom(SMTP_USER, 'Potzplitz');
            $mail->addAddress($this->to);

            // Inhalt
            $mail->isHTML(true);
            $mail->Subject = $this->subject;
            $mail->Body    = $this->message;

            return $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
}
