<?php

namespace Core\Mail;

use stdClass;
use Core\Message\Message;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class Mail
{

    /**
     * @var PHPMailer
     */
    private $mail;

    /**
     * @var Message
     */
    private $message;

    /**
     * @var array
     */
    private $data;

    public function __construct()
    {
        $this->message = new Message;

        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP();
        $this->mail->setLanguage(CONFIG_EMAIL_OPTION_LANG);
        $this->mail->isHTML(CONFIG_EMAIL_OPTION_HTML);
        $this->mail->SMTPAuth   = CONFIG_EMAIL_OPTION_AUTH;
        $this->mail->SMTPSecure = CONFIG_EMAIL_OPTION_SECURE;
        $this->mail->CharSet = CONFIG_EMAIL_OPTION_CHARSET;

        $this->mail->Host       = CONFIG_EMAIL_HOST;
        $this->mail->Username   = CONFIG_EMAIL_USERNAME;
        $this->mail->Password   = CONFIG_EMAIL_PASSWORD;
        $this->mail->Port       = CONFIG_EMAIL_PORT;
    }


    public function attach(string $filePath, string $fileName): Mail
    {
        if (empty($this->data)) {
            die($this->message->error("Inicialize o Email"));
        }
        $this->data->attach[]= [
            "name"=>$fileName,
            "path"=>$filePath,
            "image"=>false,
        ];
        return $this;
    }

    public function attachImage(string $filePath, string $fileName): Mail
    {
        if (empty($this->data)) {
            die($this->message->error("Inicialize o Email"));
        }
        $this->data->attach[]= [
            "name"=>$fileName,
            "path"=>$filePath,
            "image"=>true,
        ];
        return $this;
    }

    public function mail(): PHPMailer
    {
        return $this->mail;
    }

    public function message(): Message
    {
        return $this->message;
    }

    public function bootstrap(
        string $subject,
        string $message,
        string $toEmail,
        string $toName
    ): Mail {
        $this->data = new stdClass;
        $this->data->subject = $subject;
        $this->data->message = $message;
        $this->data->toEmail = $toEmail;
        $this->data->toName = $toName;
        return $this;
    }

    public function send(string $fromAddress = CONFIG_EMAIL_SENDER["address"], string $fromName = CONFIG_EMAIL_SENDER["name"]): bool
    {
        if (empty($this->data)) {
            $this->message->error("Favor preencher os dados antes de enviar");
            return false;
        }

        if (!is_email($this->data->toEmail)) {
            $this->message->error("Email de destinatário incorreto.");
            return false;
        }

        if (!is_email($fromAddress)) {
            $this->message->error("Email de remetente incorreto.");
            return false;
        }

        try {
            $this->mail->setFrom($fromAddress, $fromName);
            
            $this->mail->addAddress($this->data->toEmail, $this->data->toName);

            $this->mail->Subject = $this->data->subject;
            $this->mail->msgHtml($this->data->message);

            $this->resolveAttach();
  
            $this->mail->send();
        } catch (PHPMailerException $e) {
            $this->message->error($e->getMessage());
            return false;
        }

        return true;
    }

    private function resolveAttach(): Mail
    {
        if (!empty($this->data->attach)) {
            foreach ($this->data->attach as $attach) {
                if ($attach["image"]) {
                    $this->mail->addEmbeddedImage($attach["path"], $attach["name"]);
                } else {
                    $this->mail->addAttachment($attach["path"], $attach["name"]);
                }
            }
        }
        return $this;
    }
}
