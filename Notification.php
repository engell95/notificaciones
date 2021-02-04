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
        $addAddress = array('elopez@yotateam.com.ni','gflores@yotateam.com.ni');
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
        echo 'da';
        $response = '';
        $URL = Notification::TelegramConnection();
        $query = http_build_query(array(
            'chat_id'=> $chatId,
            'text'=> $message,
            'parse_mode'=> "HTML", // Optional: Markdown | HTML
        ));

        echo $response = file_get_contents("$URL/sendMessage?$query");
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
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" lang="en-GB">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <title>Yota de Nicaragua</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                <style type="text/css">
                a[x-apple-data-detectors] {color: inherit !important;}
                </style>
            </head>
            <body style="margin: 0; padding: 0;">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="padding: 20px 0 30px 0;">
                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; border: 1px solid #cccccc;">
                        <tr>
                        <td bgcolor="#ffffff" style="padding: 40px 30px 40px 30px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                <tr>
                                    <td style="color: #153643; font-family: Arial, sans-serif;">
                                    <h1 style="font-size: 24px; margin: 0;">¡Notificaciones Yota - Tasa de Cambio!</h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color: #153643; font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding: 20px 0 0 0;">
                                        <p style="margin: 0;"> '.$message.'</p></br>
                                    </td>
                                </tr>
                            </table>
                            </td>
                        </tr>
                        <tr>
                            <td bgcolor="#50a1c6" style="padding: 15px 15px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                                <tr>
                                <td style="color: #ffffff; font-family: Arial, sans-serif; font-size: 14px;">
                                    <p style="margin: 0;">
                                    <strong>Centro de contacto 24/7</strong><br/>
                                    Teléfono: 2253-8225 / 8244-8888<br/>
                                    Correo: <strong>miyota@yotateam.com.ni</strong></br></br>
                                    <strong>Oficinas Administrativas</strong><br>
                                Villa Fontana, Edificio Discover II,7mo Piso.</p>
                                </td>
                                <td align="right">
                                <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
                                    <tr>
                                    <td>
                                        <a href="https://twitter.com/yota_nic">
                                        <img src="https://yota.com.ni/assets/img/TWITTER.png" alt="Twitter." width="25" height="25" style="display: block;" border="0" />
                                        </a>
                                    </td>
                                    <td style="font-size: 0; line-height: 0;" width="20">&nbsp;</td>
                                    <td>
                                        <a href="https://www.facebook.com/YotadeNicaragua/">
                                        <img src="https://yota.com.ni/assets/img/_FACEBOOK.png" alt="Facebook." width="25" height="25" style="display: block;" border="0" />
                                        </a>
                                    </td>
                                    <td style="font-size: 0; line-height: 0;" width="20">&nbsp;</td>
                                    <td>
                                        <a href="https://www.instagram.com/yota_ni/">
                                        <img src="https://yota.com.ni/assets/img/_INSTAGRAM.png" alt="Instragram." width="25" height="25" style="display: block;" border="0" />
                                        </a>
                                    </td>
                                    </tr>
                                </table>
                                </td>
                            </tr>
                            </table>
                        </td>
                        </tr>
                    </table>
                    </td>
                </tr>
                </table>
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
                $mail->Subject  = '¡Notificaciones Yota - Tasa de Cambio!';
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