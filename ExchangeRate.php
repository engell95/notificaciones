<?php

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
    * CONEXION DB MYSQL
    *
    * @return array
    * @version 1.0
    **/
    function SecurityConnection()
    {
        try
        {
            $mysqli = new mysqli('','usr_app','','') or die(mysqli_error());
            if (!mysqli_connect_error()) {
                $mysqli->set_charset("utf8");
            }
            return $mysqli;
        }
        catch (exception $e)
        {
            return mysqli_errno($mysqli) . ": " . mysqli_error($mysqli) . "\n";
        }
    }

    /**
    * CONEXION DB SQL
    *
    * @return array
    * @version 1.0
    **/
    function CySConnection()
    {
        try
        {
            $connectionInfo = array("UID" => 'cnx_budgeting', "PWD" => '', "Database" => '','CharacterSet' => 'UTF-8','ConnectionPooling' => true,'MultipleActiveResultSets' => true);
            $connectionhost = '';
            sqlsrv_configure('WarningsReturnAsErrors',0);
            $connection = sqlsrv_connect($connectionhost, $connectionInfo);
            if (!$connection) {
                die( print_r( sqlsrv_errors(), true));
            }
            return $connection;
        }
        catch (exception $e)
        {
            echo $e->getMessage() . "\n";
        }
    }

    /**
    * CONFIGURACION PARA CONEXION DE TELEGRAM
    *
    * @return string
    * @version 1.0
    **/
    function TelegramConnection()
    {
        $TOKEN    = "1427879019:AAEFBQyRGszax7z9lKwKMlv9UUlpzx-OW-w";
        $TELEGRAM = "https://api.telegram.org:443/bot$TOKEN";
        return $TELEGRAM;
    }

     /**
    * ENVIO DE MENSAJES POR TELEGRAM
    *
    * @param  string   $chatId  [Id de la conversación de destino]
    * @param  string   $message [Mensaje en formato html]
    * @version 1.0
    * @return array
    **/
    function sendMessage($chatId, $message)
    {
        $response = '';
        $URL      = TelegramConnection();
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
    * @param  string   $message [Cuerpo del correo]
    * @version 1.0
    * @return string
    **/
    function SendEmail($message)
    {
        $response = '';
        //CONFIGURACION PARA CONEXION DE ENVIO DE EMAIL
        $SERVER = ''; 
        $PORT = 587;
        $SENDER = '';                  
        $USER   = '';        
        $PASSWORD   = "";
        //DIRECCIONES PARA NOTIFICACIONES
        $addAddress = array('','','','','','');

        foreach ($addAddress as $key => $val) {
            $RECEIVER   = $val;
            //EJECUTANDO COMANDOS
            $response .= exec('swaks --to ' . $RECEIVER . ' --from "' . $SENDER . '" --header "Subject: ¡Notificaciones Yota - Tasa de Cambio!" --body "' . $message . '" --server ' . $SERVER . ' --port ' . $PORT . ' --timeout 10s --auth LOGIN --auth-user "' . $USER . '" --auth-password "' . $PASSWORD . '" -tls');
        }

        return $response;
    }

    /**
    * ALMACENADO DE TASA DE CAMBIO
    *
    * @return string
    * @param  string   $Curreny  [Moneda para cys]
    * @param  string   $Details  [fechas y tasas de cambio]
    * @param  integer  $CounDetails [cantidad de items]
    * @version 1.0
    **/
    function get_Rate($Curreny,$Details,$CounDetails)
    {
        $v_return = '';
        $v_return .= Mysql_Store($Details,$CounDetails);
        $v_return .= Sql_Store($Curreny,$Details,$CounDetails);
        return $v_return;
    }

    /**
    * ALMACENADO DE TASA DE CAMBIO EN MYSQL
    *
    * @return string
    * @param  string   $Details  [fechas y tasas de cambio]
    * @param  integer  $CounDetails [cantidad de items]
    * @version 1.0
    **/
    function Mysql_Store($Details,$CounDetails){
        $return = '';
        $query  = "CALL Pa_ExchangeRate_Insert('$Details',$CounDetails,1);";
        $mysqli = SecurityConnection();

        if($mysqli->connect_error){
           $return .= $mysqli->connect_error;
           return $return;
        }
        else{
            $result  = $mysqli->query($query);
            if(!$result){
                $return .= $result;
            }
            $mysqli->close();
        }

        return $return;
    }

    /**
    * ALMACENADO DE TASA DE CAMBIO EN SQL
    *
    * @return string
    * @param  string   $Curreny  [Moneda para cys]
    * @param  string   $Details  [fechas y tasas de cambio]
    * @param  integer  $CounDetails [cantidad de items]
    * @version 1.0
    **/
    function Sql_Store($Curreny,$Details,$CounDetails){

        $return = '';
        $execute  = "{call Pa_TipoCambio_Insert(?,?,?)}";
        $params   = array($Curreny,$Details,$CounDetails); 
        $procedure_params = array(
            array($Curreny),
            array($Details),
            array($CounDetails)
        );
        $conn = CySConnection();
        if ( ($stmt = sqlsrv_query($conn, $execute, $procedure_params)))
        {
            do
            {
                while( ($row=sqlsrv_fetch_array($stmt)) )
                {
                    $return .=  utf8_encode($row[0]);
                }
            } while ( ($resultsql = sqlsrv_next_result($stmt)) );
        }
        else{
            $return .= DisplayErrors();
            return $return;
        }

        if ( $resultsql === false )
        {
            $return .= DisplayErrors();  
            return $return;
        }

        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        return $return;
    }

    /* ------------- Error Handling Functions --------------*/  
    function DisplayErrors()  
    {   
         $message = '';
         $errors  = sqlsrv_errors(SQLSRV_ERR_ERRORS);  
         foreach( $errors as $error )  
         {  
            $message .= "<br />SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            $message .=  "code: ".$error[ 'code']."<br />";
            $message .=  "message: ".$error[ 'message']."<br />"; 
         }  
         return $message;
    }  
      
    function DisplayWarnings()  
    {    
         $message = '';
         $warnings = sqlsrv_errors(SQLSRV_ERR_WARNINGS);  
         if(!is_null($warnings))  
         {  
              foreach( $warnings as $warning )  
              {  
                   $warnings .= "Warning: ".$warning['message']."\n";  
              }  
         }  
         return $message;
    } 

    /**
    * DIRECCIONES PARA NOTIFICACIONES
    *
    * @return string
    * @version 1.0
    **/
    function get_exchangerate_Month()
    {
        $return   = "";
        $response = "";
        $message  = "";
        $query    = "";
        $store    = "";
        $Parameters = array();
        $datenow    = date("d-m-Y");
        $datenow    = date("d-m-Y",strtotime($datenow."+ 1 month"));
        $datedata   = explode('-',$datenow);
        $day    = (int)$datedata[0];
        $month  = (int)$datedata[1];
        $year   = (int)$datedata[2];

        if ($day == 1) {
            $month = ($month - 1);

            if ($month == 0) {
                $month = 12;    
                $year  = ($year - 1);
            }

            $Parameters['Mes'] = $month;
        }
        else{
            $Parameters['Mes'] = 4;//$datedata[1];
        }
        $Parameters['Ano'] = $year;
        $Connection = ConnectionBCN();
        if (isset($Connection->sdl)) {
            $query  = $Connection->__soapCall("RecuperaTC_Mes", array($Parameters));
            $Class = (array) $query->RecuperaTC_MesResult;
            $ValorDelXML = $Class['any'];
            $xml = simplexml_load_string($ValorDelXML);
            $array = (array) $xml;
            $items = ''; 
            $count = 0; 
            if(empty($array)){
                $message =  "<code>Tasa de cambio no disponible para el mes ".$Parameters['Mes']." del año ".$Parameters['Ano']."</code>\n";
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
                        } 
                        $items .= $date.'|'.$rate.'||';
                        $count = $count + 1;
                    }
                }
                // process insert
                $store = get_Rate('DOL',$items,$count);
                if(!empty($store)){
                    $message .=  $store;
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
            $response = "<strong>El proceso de almacenado para tasa de cambio del mes ".$Parameters['Mes']." del año ".$Parameters['Ano']." finalizo Satisfactoriamente!!</strong>";
        }
        $return .= '-------------------------';
        $return .= $response;
        $return .= '-------------------------';
        //Telegram
        $chatId = '-237503884';
        $return .= sendMessage($chatId, $response);
        $return .= '-------------------------<br>';
        //Email
        $return .= SendEmail($response);
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
