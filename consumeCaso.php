 <?php

require_once "/var/www/rest-excel/consultasBD.php";
require_once "/var/www/rest-excel/upload_file.php";





class routeCaseSGN{

    //function buscaNomina($rfc_Cliente, $rfc_Pagadora, $path, $params, $path2){
    function buscaNomina($rfc_Cliente, $rfc_Pagadora, $path, $params){
        $caso = new DBSearch(); 
        $app_uid  = $caso->buscarCaso($rfc_Cliente, $rfc_Pagadora);


        if($app_uid){
            
            /********SUBE ARCHIVO SGN******/
            $subido     = $this->subeArchivo($app_uid, $path);

            //if(isset($path2)){
                /********SUBE ARCHIVO SGN ADJUNTO******/
                //$subido = $this->subeArchivo2($app_uid, $path2);
            //}
           
            /********LOGIN WS PROCESSMAKER******/
            $sessionId  = $this->login();
            /********ALMACENA INFO DE ARCHIVO SGN******/
            $insertado  = $caso->insertar_SGN($app_uid, $params);

            
            
            
            $continuado = $this->continuarCaso($app_uid, $sessionId);
            if(!$continuado)
               return "Error al continuar caso";
            
            $caso->actualizarCaso($app_uid);
            
            return $app_uid." ".$sessionId.$insertado.$subido;
            //return $app_uid."\n\n".$insertado."\n\n".print_r($params, true);

        }else{
            return "No se encontro caso";
        }
    }



    function login(){

        ini_set("soap.wsdl_cache_enabled", "1"); // enabling WSDL cache
        try {

            $user = 'autosystem';
            $pass = 'NhiCIfjKFlxVs';

            $Endpoint = "http://staging.portal-excel.com:80/sysworkflow/en/classic/services/wsdl2";
            $client   = new SoapClient($Endpoint);
            $params   = array('userid'=>$user, 'password'=>$pass );


             $result = $client->__SoapCall('login', array($params));
            if ( $result->status_code == 0 ) {

                return $result->message;
           
            }

            
        }
        catch ( Exception $e ){
            return false;
        }
    }

 
    function continuarCaso($app_uid, $sessionId){

            try {

                //$sessionId = '769878572562a887e8df4d9034463287';
                //$caseId    = '410137061562a7ec3ebdd50000925979';
        
                $caseId = $app_uid; 
    
                $Endpoint = "http://staging.portal-excel.com:80/sysworkflow/en/classic/services/wsdl2";
                $client   = new SoapClient($Endpoint);
                
                $params   = array(array('sessionId'=>$sessionId,'caseId'=>$caseId, 'delIndex'=>'1'));


                 $result = $client->__SoapCall('routeCase', $params);

                if ($result->status_code == 0)
                    return true;
                    //return  "Case derived: $result->message \n";
                else
                    return false;
                    //return  "Error deriving case: $result->message \n";
                    //print_r($result->status_code);
                
            }
            catch ( Exception $e ){
                return false;
            }
     }
     
     function subeArchivo($idCaso,$pathFile)
     {       
            //$idCaso   = '903788435561e803025a939083544181';
            $docUid   = '213801913562e773bbeb813098295973';         
            $idUsuario= '9848340814df501102959b6010421935';
            //$pathFile = '/var/www/rest-excel/archivos/a_003/a_003.xls';
            
            $this->uploader = new uploaderFile(); 
            $this->uploader->upload_file($idCaso, $idUsuario, $pathFile, $docUid);       
     }
	 
	function subeArchivo2($app_uid, $location)	
    {       
					
			$directorio = $location;	
			$ficheros   = scandir($directorio);
			
			$objfile    = $this->getValuefile2($ficheros);	
			$numFile    = count($objfile);
			
            for($a=1; $a<=$numFile; $a++){

				$arrayfile = $objfile[$a];

            }
			
            $fich        = $ficheros[$arrayfile];
			
			$directorio2 = $directorio."/".$fich;
			$ficheros1   = scandir($directorio2);
		
			$objfile     = $this->getValuefile($ficheros1);
			$numFile     = count($objfile);
			
            for($a=1; $a<=$numFile; $a++){

				$objfile_1  = $objfile[$a];

            }
			
            $excel      = $ficheros1[$objfile_1];
			
		
			//$idCaso     = '427920657566eee9ae3ca22039688636';
            $idCaso     = $app_uid;   
            $idUsuario  = '9848340814df501102959b6010421935';
			$docUid     = '117600019566f0c7a4ca8c7050645875';
            $pathFile   = $directorio."/".$fich."/".$excel;
			
            $this->uploader = new uploaderFile(); 
            $this->uploader->upload_file($idCaso, $idUsuario, $pathFile, $docUid);
			
		
    }
	
	 
	  function getValuefile2($ficheros)
	{
		$objfile = array();
		$a = 1;
		$c = count($ficheros);    
    
		for($i=2; $i<3; $i++)
		{
			if(preg_match('/[a-z]/i', $ficheros[$i]))
			{
				$objfile[$a] = $i;
				$a++;
			}
			
		}
		return $objfile;

	}
	 
	function getValuefile($ficheros1)
	{
		$objfile = array();
		$a = 1;
		$c = count($ficheros1);    
    
		for($i=0; $i< $c; $i++)
		{
			if(preg_match('/.xls/i', $ficheros1[$i]))
			{
				$objfile[$a] = $i;
				$a++;
			}
			
		}
		
		return $objfile;

	}
	
     
     
}

//$x = new routeCaseSGN();
//$x->subeArchivo2();
?>
