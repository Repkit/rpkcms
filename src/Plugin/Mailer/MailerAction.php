<?php

namespace Plugin\Mailer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class MailerAction
{
    private $mailer;

    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }
    
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        try{
            // $plugin = \RpkPluginManager\PluginChain::getInstance();
            
            if ('POST' === $request->getMethod()) {
                $post = $request->getParsedBody();
                // var_dump($post);exit(__FILE__.'::'.__LINE__);
                
                $this->mailer->Subject = 'Message from website';
                $body = json_encode($post);
                $this->mailer->Body    = $body; //'This is the HTML message body <b>in bold!</b>'
                $this->mailer->AltBody = $body; //'This is the body in plain text for non-HTML mail clients'
                
                if(!$this->mailer->send()) {
                    $msg = 'Message could not be sent.';
                    // echo 'Mailer Error: ' . $mail->ErrorInfo;
                } else {
                    $msg =  'Message has been sent';
                }
            }
        }catch (\Exception $e){
            $msg = 'Message could not be sent. Some error occured!';
        }
        
        return new JsonResponse(['message' => $msg]);
    }
}
