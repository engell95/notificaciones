<?php
    require_once 'Notification.php';

    /**
    * CONEXION AL SERVICIO DEL BCN
    *
    * @return array
    * @version 1.0
    **/
    function ConnectionBCN()
    {
        try {
            $url    = "https://servicios.bcn.gob.ni/Tc_Servicio/ServicioTC.asmx?WSDL";
            $context = stream_context_create(array(
                'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
                )
            ));
            $client = new SoapClient($url, array('stream_context' => $context,"trace" => 1, "exception" => 0, 'encoding'=>'UTF-8'));
            return $client;
        }
        catch (SoapFault $e ) {
            return "<span class='text-danger'> El servicio web del BCN esta fuera de servicio! </span>";
        }
    }

    /**
    * CONEXION DB
    *
    * @return array
    * @version 1.0
    **/
    function SecurityConnection()
    {
        try
        {
            $mysqli = new mysqli('localhost','portalyota','8306&$=pFjmEA','SystemSecurity') or die();
            $mysqli->set_charset("utf8");
            return $mysqli;
        }
        catch (exception $e)
        {
            echo mysqli_errno($mysqli) . ": " . mysqli_error($mysqli) . "\n";
        }
    }

    /**
    * ALMACENADO DE TASA DE CAMBIO
    *
    * @return string
    * @param  string   $Date  [Fecha de la tasa]
    * @param  string   $Rate  [Tasa de cambio en cordobas]
    * @version 1.0
    **/
    function get_Rate($Date,$Rate)
    {
        $return = '';
        $query = "CALL Pa_ExchangeRate_Insert('$Date',$Rate,1);";
        $mysqli = SecurityConnection();
        $result = $mysqli->query($query);
        if(!$result)
        $return = $mysqli->error;
        $mysqli->close();
        return $return;
    }

    /**
    * DIRECCIONES PARA NOTIFICACIONES
    *
    * @return string
    * @version 1.0
    **/
    function get_exchangerate_Month()
    {
        $Notification = new Notification();
        $return   = "";
        $response = "";
        $message  = "";
        $query    = "";
        $store    = "";
        $Parameters = array();
        $datenow    = date("d-m-Y");
        $datenow    = date("d-m-Y",strtotime($datenow."+ 1 month"));
        $datedata   = explode('-',$datenow);
        $Parameters['Mes'] = $datedata[1];
        $Parameters['Ano'] = $datedata[2];
        $Connection = ConnectionBCN();
        if (isset($Connection->sdl)) {
            $query  = $Connection->__soapCall("RecuperaTC_Mes", array($Parameters));
            $Class = (array) $query->RecuperaTC_MesResult;
            $ValorDelXML = $Class['any'];
            $xml = simplexml_load_string($ValorDelXML);
            $array = (array) $xml;
            if(empty($array)){
                $message =  "<code>Tasa de cambio no disponible para el mes ".$datedata[1]." del año ".$datedata[2]."</code>\n";
            }
            else{
                foreach ($array as $key => $a) {                    //Recorremos el arreglo con todos los Datos
                    foreach ($a as $key2 => $aa) {                  //Con este For, recorremos Los Dias del Mes
                        foreach ($aa as $key3 => $a3) {             //Con este for, recorremos las Fechas y Sus valores
                            if ($key3 == "Fecha"){
                                $date  = $a3;
                            }
                            else if($key3 == "Valor"){
                                $rate = $a3;
                            }
                        }           //Terminado este For, pasa a la Siguiente Fecha
                        $store = get_Rate($date,$rate);  //Almacenamos tasa de cambio
                        if(!empty($store)){
                            $message .=  "<code>Fecha ".$date." Tasa de cambio ".$rate ."No almacenada!\n".$store."</code>\n";
                        }
                    }
                }
            }
        }else{
            $message = $Connection;
        }

        //Resultado del proceso
        if(!empty($message)){
            $response = "<strong>El proceso de almacenado para tasa de cambio finalizo con errores </strong>\n<strong>Detalles:</strong>\n".$message;
        }
        else{
            $response = "<strong>El proceso de almacenado para tasa de cambio del mes ".$datedata[1]." del año ".$datedata[2]." finalizo Satisfactoriamente!!</strong>";
        }
        $return .= '-------------------------';
        $return .= $response;
        $return .= '-------------------------';
        //Telegram
        $chatId = '-237503884';
        $return .= $Notification->sendMessage($chatId, $response);
        $return .= '-------------------------<br>';
        //Email
        $return .= $Notification->SendEmail($response);
        $return .= '-------------------------<br>';
        //Log txt
        $date = date('Y-m-d H:i:s');
        $content = "$date\n$return\n\n";
        file_put_contents("error.log", $content, FILE_APPEND);
        echo $return;
        return;
    }

    //PROCESO DE ALMACENADO DE TASAS DE CAMBIO
    get_exchangerate_Month();