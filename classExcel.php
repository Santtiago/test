<?php

error_reporting(0);
ini_set('display_errors', FALSE);
ini_set('display_startup_errors', FALSE);
date_default_timezone_set("America/Mexico_City");
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');


require_once '/opt/processmaker/gulliver/thirdparty/bpmtech/libs/Excel/Classes/PHPExcel/IOFactory.php';
require_once '/opt/processmaker/gulliver/thirdparty/bpmtech/facturas/scripts/funciones.php';
require_once '/opt/processmaker/gulliver/thirdparty/bpmtech/facturas/scripts/conexion.php';

/*$class    = new grandCreater();
$nomina    = new routeCaseSGN();

$rfc_Cliente = 'ORO110519CW0';
$rfc_Pagadora = 'AES111125T39';
$location = '/var/www/rest-excel/archivos/12062/12062.xlsx';
$location = 'SINDICATO.xls';

$params   = $class->grandReaderLayout($location);

$rfc_Cliente =  $params->RFC_CLIENTE;
$rfc_Pagadora = $params->RFC_PAGADORA;
$execution = $nomina->buscaNomina($rfc_Cliente, $rfc_Pagadora, $location, $params);*/



class grandCreater
{
	function __Construct()
	{
		$this->idCaso = date('ymdh'); 
		$this->funciones = new Funciones($this->idCaso);	
		$this->conexion  = new Conexion($this->idCaso);				
	}		
	
	
    function grandReaderLayout($location)
	{		
			if (!file_exists($location)){	
					//echo $this->idCaso;
					$this->funciones->writeError("Archivo $location no existe");}
	
			$inputFileType = 'EXCEL5';
			//$inputFileType = 'Excel2007';
			$inputFileName = $location;
			
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$sheetnames = $objReader->listWorksheetNames($inputFileName); //SHEETS IN XLS
			
			$sheet = $this->getDualSheet($sheetnames); 			
			//print_r($sheetnames);
			
			$objReader->setLoadSheetsOnly($sheetnames[$sheet]);
			$objPHPExcel = $objReader->load($inputFileName);
			$sheetData = $objPHPExcel->getActiveSheet();			
	
			$Object = array();
			$i=0;
			foreach ($sheetData->getRowIterator() as $row) 
			{ 
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false);
				$ii=0;
					foreach ($cellIterator as $cell)
					{				
					$Object[$i][$ii] = $cell->getCalculatedValue();
					$ii++;
					}
				$i++;
		    }

		    $cliente 	   = '';
			$pagadora 	   = '';
			$pagadora_rfc  = '';
			$periodo 	   = '';
			
			$cliente		= $sheetData->getCell('B1')->getValue();
			$pagadora		= $sheetData->getCell('B2')->getValue();
			$pagadora_rfc 	= $sheetData->getCell('B3')->getValue();
			$periodo 		= $sheetData->getCell('B4')->getValue(); 
		     
		     
		    $key_totales=5;		     
		    foreach($Object as $value)
		    {
			    if(is_numeric($value[0]))
				{	
					$key_totales++;
				}
		    } 		     
		    //echo $key_totales; 		    	    
		    
		    
		    $key_sueldo	   = 0;
			$key_subsidio  = 0;
			$key_retencion = 0;
			$key_infonavit = 0;
			$key_total 	   = 0;
			$increment     = 0; 
						
		    foreach($Object[4] as $header)
		    {
			    if(!empty($header))
			    {
			    
			    	if($header == 'SUELDO')
			    	{			    	
				    	$key_sueldo = $increment;
			    	}
			    	if($header == 'SUBSIDIO AL EMPLEO')
			    	{			    	
				    	$key_subsidio = $increment;
			    	}
			    	if($header == 'RETENCION IMSS')
			    	{			    	
				    	$key_retencion = $increment;
			    	}
			    	if($header == 'INFONAVIT')
			    	{			    	
				    	$key_infonavit = $increment;
			    	}
			    	if($header == 'TOTAL A PAGAR')
			    	{			    	
				    	$key_total = $increment;
			    	}	    			    
				}
				$increment++;
		    }
		    
		    
		    $key_nomina			= $key_totales + 4;
		    $key_comision		= $key_totales + 5;
		    $key_costo_social	= $key_totales + 6;
		    $key_subtotal		= $key_totales + 7;
		    $key_iva			= $key_totales + 8;
		    $key_total_deposito = $key_totales + 9;
		    $key_fija			= 3;
		    
		    
		    $key_sindicato = $key_total-1;
		    
		    $NOMINA 		= $Object[$key_nomina][$key_fija]; 
		    $COMISION		= $Object[$key_comision][$key_fija]; 
		    $COSTO_SOCIAL	= $Object[$key_costo_social][$key_fija]; 
		    $SUBTOTAL		= $Object[$key_subtotal][$key_fija]; 
		    $IVA			= $Object[$key_iva][$key_fija]; 
		    $TOTAL_DEPOSITO	= $Object[$key_total_deposito][$key_fija]; 

		    
		    $TOTAL_SUELDO 	= $Object[$key_totales][$key_sueldo]; 
		    $TOTAL_SUBSIDIO	= $Object[$key_totales][$key_subsidio]; 
		    $TOTAL_RETENCION= $Object[$key_totales][$key_retencion]; 
		    $TOTAL_INFONAVIT= $Object[$key_totales][$key_infonavit]; 
		    $TOTAL_TOTAL	= $Object[$key_totales][$key_total]; 
		    $SINDICATO 		= $Object[4][$key_sindicato];	
		    $TOTAL_SINDICATO= $Object[$key_totales][$key_sindicato];	    
		    $dates = $this->disperseString($periodo);
		    
		    $RFC_Cliente = $this->searchClient($cliente);
		    
		    $group = new stdClass;
		     $group->RFC_CLIENTE    = $RFC_Cliente;
		      $group-> CLIENTE		 = $cliente;
	           $group-> PAGADORA	  = $pagadora;
	            $group-> RFC_PAGADORA  = $pagadora_rfc;			    
		 	     $group-> SUELDO		= $TOTAL_SUELDO;
			      $group-> SUBSISDIO	 = $TOTAL_SUBSIDIO;
			       $group-> RETENCION	  = $TOTAL_RETENCION;
			        $group-> INFONAVIT	   = $TOTAL_INFONAVIT;
			         $group-> TOTAL			= $TOTAL_TOTAL;			         
					  $group-> NOMINA	   	 = $NOMINA;
				 	   $group-> COMISION	  = $COMISION;
					    $group-> COSTO_SOCIAL  = $COSTO_SOCIAL;
					     $group-> SUBTOTAL	    = $SUBTOTAL;
					      $group-> IVA	   		 = $IVA;
					       $group-> TOTAL_DEPOSITO= $TOTAL_DEPOSITO;	
					        $group-> FECHA_INI	   = $dates['INICIO'];
							 $group-> FECHA_END	    = $dates['FIN'];
							  $group-> ID	   		 = $dates['ID'];
							   $group->SINDICATO	  = $SINDICATO;
							    $group->TOTAL_SINDICATO= $TOTAL_SINDICATO;
					    //  $this->insert_SGNInfo($group);		         
		// print_r($group);
		 return $group;	         
		        
	}
	
	function disperseString($string)
	{
		preg_match_all("/[0-9]{4}+[-]+[0-9]{2}+[-]+[0-9]{2}/", $string, $match);
		
		$init = $match[0][0];
		$end  = $match[0][1];
		
		preg_match_all("/[ID]+[:]+[0-9]{5}/", $string, $match);
		
		$id  = $match[0][0];
		$id  = str_replace('ID:', '', $id);
		
		$array['INICIO'] = $init;
		$array['FIN']    = $end;
		$array['ID'] 	 = $id;
		
		return $array;
	}	
	
	function searchClient($client)
	{
        $query = "SELECT RFC FROM PMT_EMPRESA2 WHERE NOMBRE= '$client'";

		$result = $this->conexion->get($query);
		
		if (!$row = mysql_fetch_assoc($result))
		{	//echo "\n".$this->idCaso;		
			$this->funciones->writeError("Error: no existe registro en PMT_EMPRESA para $client -> $query");
		}	

		$resultado = $row['RFC'];
		                
        return $resultado;
	}
	
	function getDualSheet($sheets)
	{
		$sheet = 0;
		$count = count($sheets);
		for($i=0; $i< $count; $i++)
		{
			if(preg_match('/Dual/i', $sheets[$i]))
			{
				$sheet = $i;
			}
		}
		return $sheet;
		
	}
	
	

}



