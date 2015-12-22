<?php

  
    require_once "/var/www/rest-excel/lib/nusoap.php";

        /*
        * enviaNomina web service method
        * This method recive:
        * $usuario  = user_name
        * $password = pass
        * $app_uid  = processmaker app_uid
        * $nomina   = encode file on  string base64  
        */

        function envioNomina($usuario, $password, $app_uid, $id_sgn, $nomina) {

            $user = $usuario;
            $pass = $password;

            /*
            *
            *
            *
            */

            $Endpoint = "http://staging.portal-excel.com:10080/sysworkflow/en/classic/services/wsdl2";
            $client = new SoapClient($Endpoint);
            $params = array('userid'=>$user, 'password'=>$pass);

            /*
            * On line 36, i call to process maker login web service
            * and if the call result is "ok", the code create a new path
            * and decode de $nomina parameter to get a file.
            */

            $result = $client->__SoapCall('login', array($params));
           
            if ($result->status_code == 0){

                
                /******nomina sgn***************/
                $path  = "/var/www/rest-excel/archivos/".$id_sgn;
                mkdir($path, 770);
                shell_exec("chmod 770 $path");
                $pathFile = $path."/".$id_sgn.".xls";
                $x = base64_decode($nomina);
                file_put_contents($pathFile,base64_decode($nomina));
       

                /*
                * On line 59 i create a new object of grandCreater class,
                * the grandCreater class parse a xml file and return an array from the data of the same file
                *
                *
                *
                *
                */

                require_once "/var/www/rest-excel/classExcel2.php";
                $class = new grandCreater();
                $location  = $pathFile;               
                $params   = $class->grandReaderLayout($location);

                $params = json_decode( json_encode($params), true);
                    


                $rfc_Cliente  = $params[0]['RFC_CLIENTE'];
                $rfc_Pagadora = $params[0]['RFC_PAGADORA'];
                
                /*
                * On line 70 i create a new object of routeCaseSGN class,
                * on line 71 the object call a method of the routeCaseSGN class and send parameters
                *
                *
                */
                require_once "/var/www/rest-excel/consumeCaso.php";
                
                $route     = new routeCaseSGN();     
                $execution = $route->buscaNomina($rfc_Cliente, $rfc_Pagadora, $location, $params);
         
                return "El archivo se recibio correntamente".print_r($execution, true).print_r($result, true);

                
            }else{
                return "Error favor de revisar usuario o password";
            }

        }

        function enviaFactura($usuario, $password, $factura, $tipo){

            $user = $usuario;
            $pass = $password;

            $Endpoint = "http://staging.portal-excel.com:10080/sysworkflow/en/classic/services/wsdl2";
            $client = new SoapClient($Endpoint);
            $params = array('userid'=>$user, 'password'=>$pass);

            $fechaID = date("ymdHis");

            $result = $client->__SoapCall('login', array($params));

            if ($result->status_code == 0){

                if($tipo == '1'){

                    $path = "/var/www/rest-excel/facturas/".$fechaID;
                    mkdir($path, 770);
                    shell_exec("chmod -R 770 $path");

                    $pathFile = $path."/".$fechaID.".zip";

                }
                if($tipo == '2'){

                    $path = "/var/www/rest-excel/facturas_fondeo/".$fechaID;
                    mkdir($path, 770);
                    shell_exec("chmod 770 $path");

                    $pathFile = $path."/".$fechaID.".zip";
                    
                }


                $x = base64_decode($factura);
                file_put_contents($pathFile,base64_decode($factura));

                $zip = new ZipArchive;

                if ($zip->open($pathFile) === TRUE) {
                    $zip->extractTo($path);
                    $zip->close();
                    $zip_r ='zip ok';
                } else {
                    $zip_r ='failed';
                }

                
                
                require_once "/var/www/rest-excel/classFacturas.php";
                $caso='965564549564badf9a92186072323503';              
                $fact = new Facturas($caso);
                $resultado = $fact->mainMethod($caso, $path, $fechaID, $tipo);
                
                return $resultado;

            }else
            {
                return "Error favor de revisar usuario o password";
            }

        }



        function cancelaFactura($usuario, $password, $factura, $tipo){

            $user = $usuario;
            $pass = $password;

            $Endpoint = "http://staging.portal-excel.com:10080/sysworkflow/en/classic/services/wsdl2";
            $client = new SoapClient($Endpoint);
            $params = array('userid'=>$user, 'password'=>$pass);

            $fechaID = date("ymdHis");

            $result = $client->__SoapCall('login', array($params));

            if ($result->status_code == 0){


                if($tipo == '1'){

                    $path = "/var/www/rest-excel/facturas_canceladas/".$fechaID;
                    mkdir($path, 770);
                    shell_exec("chmod -R 770 $path");

                    $pathFile = $path."/".$fechaID.".zip";

                }
                if($tipo == '2'){

                    $path = "/var/www/rest-excel/facturas_canceladas_fondeo/".$fechaID;
                    mkdir($path, 770);
                    shell_exec("chmod 770 $path");

                    $pathFile = $path."/".$fechaID.".zip";
                    
                }

                $x = base64_decode($factura);
                file_put_contents($pathFile,base64_decode($factura));

                $zip = new ZipArchive;

                if ($zip->open($pathFile) === TRUE) {
                    $zip->extractTo($path);
                    $zip->close();
                    $zip_r ='zip ok';
                } else {
                    $zip_r ='failed';
                }

                
                
                require_once "/var/www/rest-excel/classFacturasCancel.php";
                $caso ='965564549564badf9a92186072323503';               
                $fact = new Facturas($caso);
                $fact->mainMethod($caso, $path, $fechaID, $tipo);
                
                return "El archivo factura se recibio correntamente ".$zip_r;
            }else
            {
                return "Error favor de revisar usuario o password";
            }

        }





    $server = new soap_server();
    $server->configureWSDL("bpm2", "urn:bpm2");

    $server->register("envioNomina",
        array("usuario" => "xsd:string", "password"=>"xsd:string", "app_uid"=>"xsd:string", "id_sgn"=>"xsd:string", "nomina"=>"xsd:string", "archivos"=>"xsd:string"),
        array("return" => "xsd:string"),
        "urn:bpm2",
        "urn:bpm2#envioNomina",
        "rpc",
        "encoded",
        "Recibe archivo nomina sgn");

    $server->register("enviaFactura",
        array("usuario" => "xsd:string", "password"=>"xsd:string", "factura"=>"xsd:string", "tipo"=>"xsd:string"),
        array("return" => "xsd:string"),
        "urn:bpm2",
        "urn:bpm2#enviaFactura",
        "rpc",
        "encoded",
        "Recibe factura sgn");


    $server->register("cancelaFactura",
        array("usuario" => "xsd:string", "password"=>"xsd:string",  "factura"=>"xsd:string", "tipo"=>"xsd:string"),
        array("return" => "xsd:string"),
        "urn:bpm2",
        "urn:bpm2#enviaFactura",
        "rpc",
        "encoded",
        "Recibe factura sgn");

    $server->service($HTTP_RAW_POST_DATA);
?>



