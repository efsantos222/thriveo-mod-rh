<?php
namespace Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Configuração SMTP do Hostgator
        $this->mailer->isSMTP();
        $this->mailer->Host = getenv('SMTP_HOST') ?: 'mail.seudominio.com.br';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = getenv('SMTP_USER') ?: 'seu_email@seudominio.com.br';
        $this->mailer->Password = getenv('SMTP_PASSWORD') ?: 'sua_senha';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        
        $this->mailer->setFrom('noreply@seudominio.com.br', 'Portal de Desenvolvimento');
        $this->mailer->CharSet = 'UTF-8';
    }

    public function sendEmail($to, $subject, $body) {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
            return false;
        }
    }

    public function sendPasswordReset($to, $resetToken) {
        $resetLink = "https://seudominio.com.br/reset-password?token=" . $resetToken;
        $subject = "Recuperação de Senha - Portal de Desenvolvimento";
        
        $body = "
        <h2>Recuperação de Senha</h2>
        <p>Foi solicitada a recuperação de senha para sua conta.</p>
        <p>Clique no link abaixo para criar uma nova senha:</p>
        <p><a href='{$resetLink}'>{$resetLink}</a></p>
        <p>Se você não solicitou esta recuperação, ignore este email.</p>
        <p>O link expira em 1 hora.</p>
        ";

        return $this->sendEmail($to, $subject, $body);
    }

    public function sendCourseCompletion($to, $courseName) {
        $subject = "Parabéns! Você completou o curso: " . $courseName;
        
        $body = "
        <h2>Parabéns pela conclusão do curso!</h2>
        <p>Você completou com sucesso o curso: <strong>{$courseName}</strong></p>
        <p>Seu certificado já está disponível na plataforma.</p>
        <p>Acesse sua área de cursos para fazer o download.</p>
        ";

        return $this->sendEmail($to, $subject, $body);
    }

    public function sendCoachingReminder($to, $sessionDate, $coachName) {
        $subject = "Lembrete: Sessão de Coaching";
        
        $body = "
        <h2>Lembrete de Sessão de Coaching</h2>
        <p>Sua sessão de coaching está agendada para:</p>
        <p><strong>" . date('d/m/Y H:i', strtotime($sessionDate)) . "</strong></p>
        <p>Coach: <strong>{$coachName}</strong></p>
        <p>Prepare-se para a sessão revisando seus objetivos e pontos que deseja discutir.</p>
        ";

        return $this->sendEmail($to, $subject, $body);
    }
}
