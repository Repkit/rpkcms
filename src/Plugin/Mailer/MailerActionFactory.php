<?php

namespace Plugin\Mailer;

use Interop\Container\ContainerInterface;

class MailerActionFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $mail = new \PHPMailer();
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'smtpdev@dcsplus.net';                 // SMTP username
        $mail->Password = '=qP8PBCQ';                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to
        
        $mail->setFrom('noreply@bymistake.com', 'by-mistake.com');
        $mail->addAddress('repkit@gmail.com', 'office bymistake');     // Add a recipient
        $mail->isHTML(true);     // Set email format to HTML
        $mail->CharSet = "UTF-8";
        
        return new MailerAction($mail);
    }
}
