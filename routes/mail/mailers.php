<?php
class Mailers {
    public function __construct() {    
    }
    public function paymentMail($name, $asunto, $cuerpo, $to) {
        $this->destinatario = $name;
        $this->asunto = $asunto;
        $this->cuerpo = $cuerpo;
        $headers = "MIME-Version: 1.0\r\n"; 
        $headers .= "Content-type: text/html; charset=utf-8\r\n"; 
        //dirección del remitente 
        $headers .= "From: RagazzaShop <support@ragazzashop.com>\r\n"; 
        //dirección de respuesta, si queremos que sea distinta que la del remitente 
        $headers .= "Reply-To: support@ragazzashop.com\r\n"; 
        $mailer = mail($to,$asunto,$cuerpo,$headers);
        if ($mailer) {
            return true;
        } else {
            return false;
        }
    }
    protected $destinatario;
    protected $asunto;
    protected $cuerpo;
    
}