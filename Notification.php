<?php
require_once './vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Notification{

    /**
    * CONFIGURACION PARA CONEXION DE TELEGRAM
    *
    * @access private
    * @return string
    * @version 1.0
    **/
    private static function TelegramConnection()
    {
        $TOKEN    = "1427879019:AAEFBQyRGszax7z9lKwKMlv9UUlpzx-OW-w";
        $TELEGRAM = "https://api.telegram.org:443/bot$TOKEN";
        return $TELEGRAM;
    }

    /**
    * CONFIGURACION PARA CONEXION DE ENVIO DE EMAIL
    *
    * @access private
    * @return array
    * @version 1.0
    **/
    private static function ConnectionEmail(){
        //mode production
        $Host       = '190.181.132.202';
        $Username   = 'notifications@yotateam.com.ni';
        $Password   = 'G7mEZXH5DS';
        $From       = 'notifications@yotateam.com.ni';
        $FromName   = 'Yota de Nicaragua';
        $Authorization = array($Host,$Username,$Password,$From,$FromName);
        return $Authorization;
    }

    /**
    * DIRECCIONES PARA NOTIFICACIONES
    *
    * @access private
    * @return array
    * @version 1.0
    **/
    private static function EmailaddAddress(){
        $addAddress = array('elopez@yotateam.com.ni','apotosme@yotateam.com.ni');
        return $addAddress;
    }

    /**
    * ENVIO DE MENSAJES POR TELEGRAM
    *
    * @access public
    * @param  string   $chatId  [Id de la conversación de destino]
    * @param  string   $message [Mensaje en formato html]
    * @version 1.0
    * @return array
    **/
    public static function sendMessage($chatId, $message)
    {
        $response = '';
        $URL = Notification::TelegramConnection();
        $query = http_build_query(array(
            'chat_id'=> $chatId,
            'text'=> $message,
            'parse_mode'=> "HTML", // Optional: Markdown | HTML
        ));

        $response = file_get_contents("$URL/sendMessage?$query");
        return $response;
    }

    /**
    * ENVIO DE CORREOS POR SMTP
    *
    * @access public
    * @param  string   $message [Cuerpo del correo]
    * @version 1.0
    * @return string
    **/
    public static function SendEmail($message)
    {
        $mail        = new PHPMailer(true);
        $Credential  = Notification::ConnectionEmail();
        $Emails      = Notification::EmailaddAddress();
        $response    = "";

        if(empty($message)){
            $response = 'Error el correo no posee cuerpo';
        }
        else{
            $Emailmessage = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8" />
            </head>
            <body>
                <div style="display: grid;">
                    <label style="font-weight: bold;">Correo informativo.</label><br/><br/>
                    '.$message.'<br/><br/>
                    <label style="font-weight: bold;color: #01A0C6 !important;">Yota de Nicaragua S.A</label>
                </div>
            </body>
            </html>';

            try {
                $mail->isSMTP();                           // Send using SMTP
                $mail->Host         = $Credential[0];      // Set the SMTP server to send through
                $mail->SMTPAuth     = true;                // Enable SMTP authentication
                $mail->Username     = $Credential[1];      // SMTP username
                $mail->Password     = $Credential[2];
                $mail->From         = $Credential[3];
                $mail->FromName     = $Credential[4];
                /** Add a recipient optional for devops **/
                //$mail->Port = 587;
                /** mode production **/
                $mail->SMTPOptions  = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
                $mail->IsHTML(true);
                $mail->CharSet  = 'UTF-8';
                $mail->Encoding = 'base64';
                $mail->Subject  = 'Notificaciones YOTA';
                $mail->MsgHTML($Emailmessage);
                foreach ($Emails as $key => $val) {
                    $mail->addAddress($val);
                }
                if(!$mail->Send()) {
                    $response = 'Mail error: '.$mail->ErrorInfo;
                }
                else{
                    $response = 'Correo Enviado Satisfactoriamente';
                }
            }catch (Exception $e) {
                $response = $mail->ErrorInfo;
            }
        }
        return $response;
    }
}