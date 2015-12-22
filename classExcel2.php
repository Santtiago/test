<?php

//error_reporting(0);
ini_set('display_errors', FALSE);
ini_set('display_startup_errors', FALSE);
date_default_timezone_set("America/Mexico_City");
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');




require_once '/opt/processmaker/gulliver/thirdparty/bpmtech/libs/Excel/Classes/PHPExcel/IOFactory.php';
require_once '/opt/processmaker/gulliver/thirdparty/bpmtech/facturas/scripts/funciones.php';
require_once '/opt/processmaker/gulliver/thirdparty/bpmtech/facturas/scripts/conexion.php';

//$class    = new grandCreater();
//$nomina    = new routeCaseSGN();

//RFC_Cliente = 'ORO110519CW0';
//$rfc_Pagadora = 'AES111125T39';
//$location = '/var/www/rest-excel/archivos/12062/12062.xlsx';
//$location = 'SINDICATO.xls';

//$params   = $class->grandReaderLayout($location);
//$params   = $class->grandReaderLayoutCheques($location);

//$rfc_Cliente =  $params->RFC_Cliente;
//$rfc_Pagadora = $params->RFC_PAGADORA;
//$execution = $nomina->buscaNomina($rfc_Cliente, $rfc_Pagadora, $location, $params);*/



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
			// 'ya se modifico';
			if (!file_exists($location)){	
			//echo $this->idCaso;
			$this->funciones->writeError("Archivo $location no existe");
			}	
			$inputFileType = 'EXCEL5';
			//$inputFileType = 'Excel2007';
			$inputFileName = $location;			
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$sheetnames = $objReader->listWorksheetNames($inputFileName); //SHEETS IN XLS		
			//print_r ($sheetnames);

			$dualON = 0;
			for($i=0; $i<count($sheetnames); $i++){
				if(preg_match('/Dual/i', $sheetnames[$i]))
				{
					$dualON = 1;
				}
				if(preg_match('/Cheques/i', $sheetnames[$i]))
				{
					$chequeON = 1;
					}
				     else{
					$chequeON = 0;
				}
			}
			
		
			
			if ($dualON == 1)
			{
				if($chequeON >= 0)
				{
	
			$sheet = $this->getDualSheets($sheetnames);					
			$objReader->setLoadSheetsOnly($sheetnames[$sheet]);
			$objPHPExcel = $objReader->load($inputFileName);
			$sheetData = $objPHPExcel->getActiveSheet();
			$hoja_datos = $sheetData;
			$objectdatos = $this->showCheques($hoja_datos);	
			$group1 = $this->showDual($hoja_datos);
			//print_r ($group1);
									
			if($chequeON == 1)
			{					
			$objsheet   = $this->getChequesSheet($sheetnames);
			$numCheques = count($objsheet);
			if ($numCheques>0)
				{
					$flagCheques = 1;
			
					}
				
			    for($a=1; $a<=$numCheques; $a++){

				$objsheet_1  = $objsheet[$a];				
				$objReader->setLoadSheetsOnly($sheetnames[$objsheet_1]);
				$objPHPExcel = $objReader->load($inputFileName);
			    $sheetData   = $objPHPExcel->getActiveSheet();
				$hoja_datos = $sheetData;
				$objectdatos = $this->showCheques($hoja_datos);		
  				$group2 = $this->showCheques($hoja_datos);
				$groupfinal[$a] = (array)$group2;
			}
				$resultante = $this->conjoinArray($groupfinal);	
		        $arrayresultante= $this->conjoinArray($groupfinal);
				$union = array_merge($group1, $arrayresultante);
				
			}
		}
					
	}			
			if ($dualON == 0)
			{
				if($chequeON >= 0)
				{
	
			$sheet = $this->getIDAsimSheets	($sheetnames);					
			$objReader->setLoadSheetsOnly($sheetnames[$sheet]);
			$objPHPExcel = $objReader->load($inputFileName);
			$sheetData = $objPHPExcel->getActiveSheet();	
			$hoja_datos = $sheetData;
			$objectdatos = $this->showCheques($hoja_datos);	
			$group1 = $this->showAsim($hoja_datos);
			//print_r ($group1);
			$union = $group1;
						
			if($chequeON == 1)
			{					
			$objsheet   = $this->getChequesSheet($sheetnames);
			$numCheques = count($objsheet);
			if ($numCheques>0)
				{
					$flagCheques = 1;
				   
				}
				
			    for($a=1; $a<=$numCheques; $a++)
				{
					$objsheet_1  = $objsheet[$a];
				$objReader->setLoadSheetsOnly($sheetnames[$objsheet_1]);
				$objPHPExcel = $objReader->load($inputFileName);
			    $sheetData   = $objPHPExcel->getActiveSheet();
				$hoja_datos  = $sheetData;
				$objectdatos = $this->showCheques($hoja_datos);		
  				$group2 = $this->showCheques($hoja_datos);
				$groupfinal[$a] = (array)$group2;
			}
				$resultante = $this->conjoinArray($groupfinal);	
		        $arrayresultante= $this->conjoinArray($groupfinal);
				$union = array_merge($group1, $arrayresultante);
				
				
			  }
	     	}		
	      }
		  //print_r ($union);
		  return $union;
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
	function showDual($hoja_datos)
	{
		$sheetData = $hoja_datos;
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
		    $SINDICATO 		= $Object[4][	$key_sindicato];	
		    $TOTAL_SINDICATO= $Object[$key_totales][$key_sindicato];	    
		    $dates = $this->disperseString($periodo);
		
		    
		    $RFC_Cliente = $this->searchClient($cliente);
		    
		    $group = new stdClass;	
			$group-> RFC_CLIENTE    = $RFC_Cliente;
			$group-> CLIENTE		= $cliente;
			$group-> PAGADORA	    = $pagadora;
			$group-> RFC_PAGADORA   = $pagadora_rfc;			    
			$group-> SUELDO		    = $TOTAL_SUELDO;
			$group-> SUBSISDIO	    = $TOTAL_SUBSIDIO;
			$group-> RETENCION	    = $TOTAL_RETENCION;
			$group-> INFONAVIT	    = $TOTAL_INFONAVIT;
			$group-> TOTAL			= $TOTAL_TOTAL;			         
			$group-> NOMINA	   	    = $NOMINA;
			$group-> COMISION	    = $COMISION;
			$group-> COSTO_SOCIAL   = $COSTO_SOCIAL;
			$group-> SUBTOTAL	    = $SUBTOTAL;
			$group-> IVA	   		= $IVA;
			$group-> TOTAL_DEPOSITO = $TOTAL_DEPOSITO;	
			$group-> FECHA_INI	    = $dates['INICIO'];
			$group-> FECHA_END	    = $dates['FIN'];
			$group-> ID	   		    = $dates['ID'];
			$group-> SINDICATO	    = $SINDICATO;
			$group-> TOTAL_SINDICATO = $TOTAL_SINDICATO;
							   
					    //  $this->insert_SGNInfo($group);		         
			$group1 =array($group);
			return $group1;
						
	}
    function showAsim ($hoja_datos)
	{
			$sheetData = $hoja_datos;
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

		     
		     
		    $plier_totales=5;		     
		    foreach($Object as $value)
		    {
			    if(is_numeric($value[0]))
				{	
					$plier_totales++;
				}
		    } 
          //echo "los plier Totales son: ".$plier_totales;			
		   
		    $plier_salarioAsim	   = 0;
			$plier_ISR_Asim		   = 0;
			$acrecimiento          = 0;
			
						
		    foreach($Object[4] as $header)
		    {
			    if(!empty($header))
			    {
			    
			    	if($header == 'SALARIO ASIMILADO')
			    	{			    	
				    	$plier_salarioAsim = $acrecimiento;
			    	}
			    	if($header == 'ISR ASIMILADOS')
			    	{			    	
				    	$plier_ISR_Asim = $acrecimiento;
			    	}			    	  			    
				}
				$acrecimiento++;
				//print_r ($acrecimiento);
		    }
						    
		    $plier_nomina		= $plier_totales + 2;
		    $plier_impoBruto    = $plier_totales + 3;
		    $plier_comision  	= $plier_totales + 4;
		    $plier_subtotal		= $plier_totales + 5;
		    $plier_iva			= $plier_totales + 6;
			$plier_total_deposito = $plier_totales + 8;
		    $plier_fija			= 2;
		    
		    
		    $NOMINA 		= $Object[$plier_nomina][$plier_fija]; 
		    $COMISION		= $Object[$plier_comision][$plier_fija]; 
		    $IMPORTE_BRUTO	= $Object[$plier_impoBruto][$plier_fija]; 
		    $SUBTOTAL		= $Object[$plier_subtotal][$plier_fija]; 
		    $IVA			= $Object[$plier_iva][$plier_fija]; 
		    $TOTAL_DEPOSITO	= $Object[$plier_total_deposito][$plier_fija]; 

		    
		    $TOTAL_SALARIOASIM 	= $Object[$plier_totales][$plier_salarioAsim]; 
		    $TOTAL_ISRASIM	= $Object[$plier_totales][$plier_ISR_Asim]; 
		        
		    $dates = $this->disperseString($periodo);
		
		    
		    $RFC_Cliente = $this->searchClient($cliente);
		    
		    $group = new stdClass;	
			$group-> RFC_CLIENTE        = $RFC_Cliente;
			$group-> CLIENTE		    = $cliente;
			$group-> PAGADORA	        = $pagadora;
			$group-> RFC_PAGADORA       = $pagadora_rfc;			    
			$group-> SALARIO_ASIMILADO	= $TOTAL_SALARIOASIM;
			$group-> ISR_ASIMILADOS	    = $TOTAL_ISRASIM;       
			$group-> NOMINA	   	        = $NOMINA;
			$group-> COMISION	        = $COMISION;
			$group-> IMPORTE_BRUTO      = $IMPORTE_BRUTO;
			$group-> SUBTOTAL	        = $SUBTOTAL;
			$group-> IVA	   		    = $IVA;
			$group-> TOTAL_DEPOSITO     = $TOTAL_DEPOSITO;	
			$group-> FECHA_INI	        = $dates['INICIO'];
			$group-> FECHA_END	        = $dates['FIN'];
			$group-> ID	   		        = $dates['ID'];
							   							   
					    //  $this->insert_SGNInfo($group);		         
						$group1 =array($group);
						return $group1;
		
	}
	function showCheques($hoja_datos)
	{
				
			$numCheques = 5;
			for($a=1; $a<=$numCheques; $a++){

				
		        $sheetData = $hoja_datos;
				$Object = array();
			    $i= 0;

			    foreach ($sheetData->getRowIterator() as $row){ 
				
					$cellIterator = $row->getCellIterator();
					$cellIterator->setIterateOnlyExistingCells(false);
					$ii = 0;
					
					foreach ($cellIterator as $cell){				
					     
						 
						$Object[$i][$ii] = $cell->getCalculatedValue();
						$ii++;
					}

					$i++;
		    	}

		    	$cliente_c 	   = '';
				$empresa 	   = '';
				$rfc_cliente   = '';
				$periodo_c 	   = '';
				
				
				$cliente_c		= $sheetData->getCell('B1')->getValue();
				$empresa		= $sheetData->getCell('B2')->getValue();
				$rfc_empresa 	= $sheetData->getCell('B3')->getValue();
				$periodo_c 		= $sheetData->getCell('B4')->getValue();
				
							
			    $key_total = 5;		     
			    foreach($Object	 as $value)
			    {
				    if(is_numeric($value[0]))
					{	
						$key_total++;
					}
			    }
				$Keyiteration = $key_total - 5;
				
				/*echo "Los numeros de key son\n\n";
				print_r ($Keyiteration);
				echo "\n\n";*/				
				
			    $key_importe  = 0;
				$incremento   = 0;
							
			    foreach($Object[4] as $header)
			    {
				    if(!empty($header))
				    {
				    
				    	if($header == 'IMPORTE')
				    	{			    	
					    	$key_importe = $incremento;
	
				    	}
								
					}
					$incremento++;
			    }
				
				$key_fij       = 3;	

				$page = array();
				$check  = array();

				for($x=1; $x<=$Keyiteration; $x++){
					
					$x1 = 5;
					$x2 = $x1+$x;
					$b  = 'B'.$x2;
					
					$beneficiario = $sheetData->getCell($b)->getValue();

					$y1 = 5;
					$y2 = $y1+$x;
					$c  = 'C'.$y2;

					$importe = $sheetData->getCell($c)->getValue();

					$check[$x]['Beneficiario'] = $beneficiario;
					$check[$x]['Importe']      = $importe;

					//print_r ($check);
				}
					$hojacheq = ('HOJA DE CHEQUES');
					
			    $TOTAL_IMPORTE = $Object[$key_total][$key_importe]; 
			
			    $dates = $this->disperseString($periodo_c);
			     $group[$a] = new stdClass;
				  $group[$a]-> HOJA_DE_CHEQUES         = $hojacheq;
				    $group[$a]-> CLIENTE		       = $cliente_c;
				     $group[$a]-> PAGADORA	           = $empresa;
				      $group[$a]-> RFC_PAGADORA        = $rfc_empresa;			    
				       $group[$a]-> IMPORTE_TOTAL	   = $TOTAL_IMPORTE;
				        $group[$a]-> FECHA_INI	       = $dates['INICIO'];
				         $group[$a]-> FECHA_END	       = $dates['FIN'];
				          $group[$a]-> CHEQUESINFO	   = $check;
							// $this->insert_SGNInfo($group);		
							
							$group2 = $group[$a];
							$group2 = array($group2);
							return $group2;
			}				
	}
	function getChequesSheet($sheets)
	{
		$objsheet = array();
		$a = 1;
		$c = count($sheets);    
    
		for($i=0; $i< $c; $i++)
		{
			if(preg_match('/Cheques/i', $sheets[$i]))
			{
				$objsheet[$a] = $i;
				$a++;
			}
		}	
		//echo "\n";
		//print_r ($objsheet);
		return $objsheet;
	}
    function getDualSheets($sheets)
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
	function getIDAsimSheets($sheets)
	{
		$sheetAsim = 0;
		$Asim = count($sheets);
		for($i=0; $i< $Asim; $i++)
		{
			if(preg_match('/ID/i', $sheets[$i]))
			{
				$sheetAsim = $i;
			}
		}

		return $sheetAsim;
		
	}
	function conjoinArray($groupfinal)
	{
	$arrayresultante = array_merge($groupfinal);
		
		return $arrayresultante;
	}				
}
		
	
		