<?php
require_once '/opt/processmaker/gulliver/thirdparty/bpmtech/facturas/scripts/funciones.php';
require_once "/var/www/rest-excel/consultasBD.php";
require_once "/var/www/rest-excel/upload_file.php";
require_once "/var/www/rest-excel/IOS.php";

//$subido   	= $this->subeArchivo($app_uid, $path);
/*
$idCaso='965564549564badf9a92186072323503';
$path = "/var/www/rest-excel/facturas/49783";
$SGN  = "0026";
$tipo = "1";

$fact = new Facturas($idCaso);
$res = $fact->mainMethod($idCaso, $path, $SGN, $tipo);
*/
//$fact->cargarFacturas('/opt/processmaker/gulliver/thirdparty/bpmtech/ws/facturas_0026/', $idCaso);

class Facturas
{
	function __Construct($idCaso)
	{
		$this->funciones = new Funciones($idCaso);		
		$this->DB 		 = new DBSearch(); 
		$this->xmlRead   = new grandCreaterxml();	
		$this->uploader  = new uploaderFile(); 		
		$this->user      = '9848340814df501102959b6010421935';	

	}	
	
	function mainMethod($idCaso, $path, $id_sgn, $tipo)
	{
		$files  = $this->cargarFacturas($path);
		
		$upload = $this->salvarFacturas($files, $idCaso, $id_sgn, $tipo);

		return $files;
	}
	
	function cargarFacturas($path)
	{
		$ficheros  = scandir($path);		
		$drc='';
		foreach($ficheros as $fi)
		{ 
			if(is_dir($path.'/'.$fi))
			{		
				$drc = '/'.$fi.'/';
				//error_log($drc."\n");
			}
		}
		
	    $path = $path.$drc;
	    //error_log($path);
		$ficheros = scandir($path);
		
		$files = array(); $i=0;
		foreach($ficheros as $f)
		{
			if(is_file($path.$f))
			{
				$files[$i]['pdf']='';
				$files[$i]['xml']='';
				
				if(preg_match("/.xml/i", $f))
				{
					$files[$i]['xml'] = $path.$f; 
					$f_pdf = str_replace('.xml', '.pdf', $f);
					$files[$i]['pdf'] = $path.$f_pdf; 
					$files[$i]['namexml'] = $f; 
					$files[$i]['namepdf'] = $f_pdf; 
					// error_log($f_pdf."\n");
					$i++;
				}
			}			
		}
				
		return $files;
	}
	
	
	function salvarFacturas($files, $idCaso, $id_sgn, $tipo)
	{
		foreach($files as $x)
		{
			$date         = date("Y-m-d H:i:s");
			$xml          = $x['xml'];
			
			$pdf          = $x['pdf'];
			$namexml      = $x['namexml'];
			$namepdf      = $x['namepdf'];
			$this->subeArchivo($idCaso, $xml, 'xml');
			$this->subeArchivo($idCaso, $pdf, 'pdf');	
			$doc_uid_xml = $this->funciones->getDocumentUID($idCaso, 'facturaxml_sgn', $namexml); 
			$doc_uid_pdf = $this->funciones->getDocumentUID($idCaso, 'facturapdf_sgn', $namepdf);

	
			$res2 = $this->xmlRead->ReaderLayout($xml);
			//print_r($res2);

			$emisor 	  = $res2[0];
			$rfc_emisor   = $res2[1];
			$receptor 	  = $res2[2];
			$rfc_receptor = $res2[3];
			$folio 		  = $res2[4];
			$fecha  	  = $res2[5];
			$hora 		  = $res2[6];

			//$this->DB->insertarFactura($id_sgn, $xml, $pdf, $idCaso, $tipo, $doc_uid_xml, $doc_uid_pdf, $date);
			$this->DB->insertarFactura($id_sgn, $xml, $pdf, $tipo, $doc_uid_xml, $doc_uid_pdf, $hora, $emisor, $rfc_emisor, $receptor, $rfc_receptor, $folio, $fecha);							
		}
		
	}
	
	function subeArchivo($idCaso,$pathFile, $type)
    {	     
    		if($type=='xml') $docUid   = '475980286564e182327b110014655153';		
    		if($type=='pdf') $docUid   = '736608930564e1836ad82c3005925669';	
		
			$idUsuario= $this->user;
			//$pathFile = '/var/www/rest-excel/archivos/a_003/a_003.xls';
			//echo "\n save $pathFile";
			
			$this->uploader->upload_file($idCaso, $idUsuario, $pathFile, $docUid);	     
    }
}


//factura xml 475980286564e182327b110014655153
//factura pdf 736608930564e1836ad82c3005925669

?>