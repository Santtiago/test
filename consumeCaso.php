 <?php

require_once "/var/www/rest-excel/consultasBD.php";
require_once "/var/www/rest-excel/upload_file.php";



/*
* The routeCaseSGN class save the data on PMT_Tables,
* find appuid of processmaker cases
* and upload files to processmaker through Web services
*/

class routeCaseSGN{


    function buscaNomina($rfc_Cliente, $rfc_Pagadora, $path, $params){
        $caso = new DBSearch(); 
        $app_uid  = $caso->buscarCaso($rfc_Cliente, $rfc_Pagadora);


        if($app_uid){
            
            /*
            *The subeArchivo method upload the file to processmaker
            */
            /********SUBE ARCHIVO SGN******/
            $subido     = $this->subeArchivo($app_uid, $path);
            

            
            /*
            *The login method is a processmaker web service 
            */
            /********LOGIN WS PROCESSMAKER******/
            $sessionId  = $this->login();


             /*
            *The insertar_SGN method save the info inside data tables
            */
            /********ALMACENA INFO DE ARCHIVO SGN******/
            $insertado  = $caso->insertar_SGN($app_uid, $params);

            
            
            
            $continuado = $this->continuarCaso($app_uid, $sessionId);
            if(!$continuado)
               return "Error al continuar caso";
            
            $caso->actualizarCaso($app_uid);
            
            return $app_uid." ".$sessionId.$insertado.$subido;


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


        
                $caseId = $app_uid; 
    
                $Endpoint = "http://staging.portal-excel.com:80/sysworkflow/en/classic/services/wsdl2";
                $client   = new SoapClient($Endpoint);
                
                $params   = array(array('sessionId'=>$sessionId,'caseId'=>$caseId, 'delIndex'=>'1'));


                 $result = $client->__SoapCall('routeCase', $params);

                if ($result->status_code == 0)
                    return true;
                   
                else
                    return false;

            }
            catch ( Exception $e ){
                return false;
            }
     }
     
     function subeArchivo($idCaso,$pathFile)
     {       

            $docUid   = '213801913562e773bbeb813098295973';         
            $idUsuario= '9848340814df501102959b6010421935';

            
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

?>
