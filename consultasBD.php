<?php

//include '/opt/processmaker/shared/sites/workflow/db.php';
require_once '/opt/processmaker/gulliver/thirdparty/bpmtech/facturas/scripts/funciones.php';
//include '/opt/processmaker/gulliver/thirdparty/bpmtech/config.inc';

define ('DEBUG',1);

class Bd{
    
   private $link;
    
    function __construct(){
        $this->getMySqlLink();
        //echo "estoy en consultas bd";
        //$html="<table>";
    }
    
    function __destruct() {
        mysql_close($this->link);

    }
    
    function getMySqlLink(){
    
            $this->link = mysql_pconnect(DB_HOST, DB_USER, DB_PASS);
            if (!$this->link) {
                die('Could not connect: ' . mysql_error());
            }
            // make foo the current db
            $db_selected = mysql_select_db(DB_NAME, $this->link);
            if (!$db_selected) {
                die ('Can\'t use foo : ' . mysql_error());
            }
    }
   
    function insertNomina($app_uid, $id_sgn)
    {

        $sqlquery = "INSERT INTO PAYROLL_RECEIVED VALUES (0, '$app_uid', '$id_sgn')";

                if($query = mysql_query($sqlquery,$this->link))
                {       
                //echo 'Success  \n';           
                }
                else
                {
                //echo 'error';
                }    
    }   
    
}




class DBSearch{
    
   private $link;
    
    function __construct(){
        $this->getMySqlLink();
        //echo "estoy en consultas bd";
        //$html="<table>";
    }
    
    function __destruct() {
        mysql_close($this->link);

    }
    
    function getMySqlLink(){
    
            $this->link = mysql_pconnect(DB_HOST, DB_USER, DB_PASS);
            if (!$this->link) {
                die('Could not connect: ' . mysql_error());
            }
            // make foo the current db
            $db_selected = mysql_select_db(DB_NAME, $this->link);
            if (!$db_selected) {
                die ('Can\'t use foo : ' . mysql_error());
            }
    }
   
    function buscarCaso($rfc_Cliente, $rfc_Pagadora)
    {
        
        //$rfc_Cliente  = 'MAD810401JD1';
        //$rfc_Pagadora = 'ABC12345678911';

        $sqlquery = "SELECT APP_UID FROM PMT_CASO_NOMINA AS CN
                    INNER JOIN EMPRESAS AS E
                    ON CN.ID_EMPRESA = E.ID
                    AND E.RFC = '$rfc_Cliente'
                    AND E.pagadora = '$rfc_Pagadora'
                    AND CN.CASO_INICIADO = 0";

                if($query = mysql_query($sqlquery,$this->link))
                {       
                    $row    = mysql_fetch_array($query);
                    //$result = $row['APP_UID'];
                    $result = $row['0'];           
                }
                else
                {
                    $result = 0;
                }

        return $result;   
    }
    

    function actualizarCaso($app_uid){
        
        $sqlquery = "UPDATE PMT_CASO_NOMINA
                      SET CASO_INICIADO=1
                      WHERE  APP_UID = '$app_uid'";

        if($query = mysql_query($sqlquery,$this->link))
        {       
           return "actualizado";           
        }else{
           return "no actualizado";
        }        
    }   
    
    function insertar_SGN($app_uid, $array)
    {   

        $app_uid = $app_uid; 
        $rfcCliente     = $array[0]['RFC_CLIENTE'];
        $cliente        = $array[0]['CLIENTE'];
        $rfcPagadora    = $array[0]['RFC_PAGADORA'];
        $pagadora       = $array[0]['PAGADORA'];
        $sueldo         = $array[0]['SUELDO'];
        $subsidio       = $array[0]['SUBSISDIO'];
        $retencion      = $array[0]['RETENCION'];
        $infinvit       = $array[0]['INFONAVIT'];
        $total          = $array[0]['TOTAL'];
        $nomina         = $array[0]['NOMINA'];
        $comision       = $array[0]['COMISION'];
        $costoSocial    = $array[0]['COSTO_SOCIAL'];
        $subtotal       = $array[0]['SUBTOTAL'];
        $iva            = $array[0]['IVA'];
        $totalDeposito  = $array[0]['TOTAL_DEPOSITO'];
        $fechaIni       = $array[0]['FECHA_INI'];
        $fechaEnd       = $array[0]['FECHA_END'];
        $id             = $array[0]['ID'];
        $sindicato      = $array[0]['SINDICATO'];
        $totalSindicato = $array[0]['TOTAL_SINDICATO'];

       
        //$sqlquery = "INSERT INTO PMT_INFO_SGN  VALUES (0, '$app_uid', '$Object->RFC_CLIENTE', '$Object->CLIENTE', '$Object->RFC_PAGADORA', '$Object->PAGADORA', '$Object->SUELDO', '$Object->SUBSISDIO', '$Object->RETENCION', '$Object->INFONAVIT', '$Object->TOTAL', '$Object->NOMINA', '$Object->COMISION', '$Object->COSTO_SOCIAL', '$Object->SUBTOTAL', '$Object->IVA', '$Object->TOTAL_DEPOSITO', '$Object->FECHA_INI', '$Object->FECHA_END', '$Object->ID', '$Object->SINDICATO', '$Object->TOTAL_SINDICATO')";
        
        $sqlquery = "INSERT INTO PMT_INFO_SGN VALUES (0, 
                                                    '$app_uid',
                                                    '$rfcCliente',
                                                    '$cliente',
                                                    '$rfcPagadora',
                                                    '$pagadora',
                                                    '$sueldo',
                                                    '$subsidio',
                                                    '$retencion',
                                                    '$infinvit',
                                                    '$total',
                                                    '$nomina',
                                                    '$comision',
                                                    '$costoSocial',
                                                    '$subtotal',
                                                    '$iva',
                                                    '$totalDeposito',
                                                    '$fechaIni',
                                                    '$fechaEnd',
                                                    '$id',
                                                    '$sindicato',
                                                    '$totalSindicato')";

        if($query = mysql_query($sqlquery,$this->link))
        {       
            //return "success";
            $respuesta = "ok inserto en  PMT_INFO_SGN";        
        }else{
            $respuesta = "NO inserto en  PMT_INFO_SGN";
        }

        $niveles = count($array);

        $subnivel = array();

        $sqlquery2 = array();

        $sqlquery3 = array();


        for($i=1; $i<=$niveles-1; $i++){

            $pagadoraCheck     = $array[$i][0]['PAGADORA'];
            $rfcPagadoraCheck  = $array[$i][0]['RFC_PAGADORA'];
            $importeTotalCheck = $array[$i][0]['IMPORTE_TOTAL'];
            $fechaIniCheck     = $array[$i][0]['FECHA_INI'];
            $fechaFinCheck     = $array[$i][0]['FECHA_END'];

            $subnivel = count($array[$i][0]['CHEQUESINFO']); 
            
            
            for($j=1; $j<=$subnivel; $j++){

                $beneficiarioCheck = $array[$i][0]['CHEQUESINFO'][$j]['Beneficiario'];
                $importeCheck      = $array[$i][0]['CHEQUESINFO'][$j]['Importe'];

                $sqlquery2[$i][$j] = "INSERT INTO PMT_CHEQUES_SGN VALUES (0, '$app_uid', '$beneficiarioCheck', '$importeCheck', '$id', '$pagadoraCheck')";

                 $sqlquery3[$i][$j] = $sqlquery2[$i][$j];
           
                if($query = mysql_query($sqlquery2[$i][$j],$this->link))
                {       
                    //return "success" ;

                }else{
                    //return "no se inserto";
                }
                

            }
            

            
        }

        return $respuesta;




        
    }


    function insertaCheques($app_uid, $array){

        $niveles = count($array);

        for($i=1; $i<=$niveles-1; $i++){

            $pagadoraCheck     = $array[$i][0]['PAGADORA'];
            $rfcPagadoraCheck  = $array[$i][0]['RFC_PAGADORA'];
            $importeTotalCheck = $array[$i][0]['IMPORTE_TOTAL'];
            $fechaIniCheck     = $array[$i][0]['FECHA_INI'];
            $fechaFinCheck     = $array[$i][0]['FECHA_END'];

            $subnivel = count($array[$i][0]['CHEQUESINFO']); 
            
            
            for($j=1; $j<=$subnivel; $j++){

                $beneficiarioCheck = $array[$i][0]['CHEQUESINFO'][$j]['Beneficiario'];
                $importeCheck      = $array[$i][0]['CHEQUESINFO'][$j]['Importe'];

                $sqlquery2 = "INSERT INTO PMT_CHEQUES_SGN VALUES (0, '$app_uid', '$beneficiarioCheck', '$importeCheck', '$id', '$pagadoraCheck')";

           
                
                if($query = mysql_query($sqlquery2,$this->link))
                {       
                    return "success" ;          
                }else{
                    return "no se inserto";
                }
                

            }
            

            
        }
        

    }


    function buscaCasoFac($id_sgn)
    {


        $sqlquery = "SELECT APP_UID FROM PMT_INFO_SGN WHERE ID_NOMINA = '$id_sgn'";

                if($query = mysql_query($sqlquery,$this->link))
                {       
                    $row    = mysql_fetch_array($query);
                    $result = $row['0'];           
                }
                else
                {
                    $result = 0;
                }

        return $result;   
    }
    
    /*Factura normal*/
    function insertarFactura($id_sgn, $xml, $pdf, $tipo, $doc_uid_xml, $doc_uid_pdf, $hora, $emisor, $rfc_emisor, $receptor, $rfc_receptor, $folio, $fecha)                         
    {
    $sqlquery = "INSERT INTO PMT_FACTURA_SGN2 VALUES (0, '$folio','$xml','$pdf','$doc_uid_xml','$doc_uid_pdf', '$receptor', '$rfc_receptor', '$emisor', '$rfc_emisor', '$fecha', '$id_sgn', '$tipo')";
    if($query = mysql_query($sqlquery,$this->link))
            {       
                return "success" ;          
            }else{
                return "no se inserto";
            }  
    }

    /*Factura cancelada*/
    function insertarFacturaC($id_sgn, $xml, $pdf, $tipo, $doc_uid_xml, $doc_uid_pdf, $hora, $emisor, $rfc_emisor, $receptor, $rfc_receptor, $folio, $fecha)                         
    {
    $sqlquery = "INSERT INTO PMT_FACTURA_CAN_SGN2 VALUES (0, '$folio','$xml','$pdf','$doc_uid_xml','$doc_uid_pdf', '$receptor', '$rfc_receptor', '$emisor', '$rfc_emisor', '$fecha', '$id_sgn', '$tipo')";
   
    if($query = mysql_query($sqlquery,$this->link))
            {       
                return "success" ;          
            }else{
                return "no se inserto";
            }  
    }
    
}


?>





