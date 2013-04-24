<?php

class Servipag
{

	private $rutaLlavePrivada; 
	private $rutaLlavePublica; 
	private $arrayOrdenamiento;
	private $rutalog;
	
	public function __construct() {
	}
	
	public function setArrayOrdenamiento($ord = null){
		if($ord){
			asort($ord);
			$this->arrayOrdenamiento = $ord;
			$this->Logs('0', 'Realiza ordenamiento:'.$ord);
		}
	}
	
	public function setRutaLlaves($rutaLlavePri = null, $rutaLlavePub = null){
		if($rutaLlavePri){
			$this->rutaLlavePrivada = $rutaLlavePri;
			$this->Logs('1', 'Setea Ruta Llave Privada :'.$rutaLlavePri);
		}
		if($rutaLlavePub){
			$this->rutaLlavePublica = $rutaLlavePub;
			$this->Logs('1', 'Setea Ruta Llave Publica :'.$rutaLlavePub);
		}
	}
	
	public function setRutaLog($rutaL=null){
		if($rutaL){
			$this->rutalog = $rutaL;
			$this->Logs('1', 'Setea Ruta Log:'.$rutaL);
		}
	}
	
	function readFile( $file = null , $type = null ){
		if(!$file)	return false;
		$fp = fopen($file , "r");
		$txt = fread($fp, 8192);
		fclose($fp);
		$this->Logs('1', 'Obtiene '.$type.' :');
		return $txt;
	}

	function getPublica( $file = null ){
		return $this->readFile( $file , "Llave Publica" );
	}

	function getPrivada($file = null){
		return $this->readFile( $file , "Llave Privada" );
	}

	function getFirma($file = null){
		return $this->readFile( $file , "Firma" );
	}

	function encripta($datos){
		$result = "";
		$this->Logs('1', 'Función Encripta :');
		$llavePrivada = $this->getPrivada($this->rutaLlavePrivada);
		$this->Logs('1', '-----------------------------------');
		$this->Logs('1', 'Realiza Firmado de los Datos :');
		$this->Logs('1', '------- variable datos: '.$datos );
		$this->Logs('1', '------- variable result: '.$result );
		$this->Logs('1', '------- variable llavePrivada: '.$llavePrivada );
		$this->Logs('1', '-----------------------------------');
		try {
			$this->Logs('1', '------------------Entra dentro del try---------------------');
    			openssl_sign($datos, $result, $llavePrivada, OPENSSL_ALGO_MD5);
    			$this->Logs('1', 'Realizado Firmado de los Datos :'.$result);
		     } 
		catch( Exception $e ) {
			$this->Logs('1', '------- Error en generación de firma ----: ');
			$this->Logs('1', '------- Mensaje Error: '.$e->getMessage());
		    	$this->Logs('1', '------- Fin Error ----------------: ');  
		}  
		$this->Logs('1', '-----------------------------------');
		$result = base64_encode($result);
		$this->Logs('1', 'Realiza Encriptación de los Datos :'.$result);
		return $result;
	}
	
	public function desencripta($datos, $firma){
		$this->Logs('1', 'Función Desencripta :');
		$llave = $this->getPublica($this->rutaLlavePublica);
		$base64 = base64_decode($firma);
		$this->Logs('1', 'Desencripta en Base64 :'.$base64);
		$this->Logs('1','Verificación de Firma Datos:'.$datos.'--b64:'.$base64.'--Llave:'.$llave);
		if(openssl_verify($datos,$base64,$llave,OPENSSL_ALGO_MD5)){
			$this->Logs('1', 'Verificación de Firma Positiva');
			return true;
			}
		else{
			$this->Logs('1', 'Verificación de Firma Negativa');
			return false;
			}
	}

	public function GeneraXML($CodigoCanalPago = null, $IdTxCliente = null, $FechaPago = null, $MontoTotalDeuda = null, $NumeroBoletas = null, $IdSubTrx = null, $CodigoIdentificador = null, $Boleta = null, $Monto = null, 	$FechaVencimiento = null){
		$datos ="";
		$this->Logs('1', 'Funcion Generación XML1');
		$this->Logs('1', 'IdSubTrx '.$IdSubTrx);
		//$datos = $IdTxCliente."".$FechaPago."".$MontoTotalDeuda."".$NumeroBoletas."".$FechaVencimiento."".$IdSubTrx."".$CodigoIdentificador."".$Boleta."".$Monto;  
		$keyArrays = array('CodigoCanalPago','IdTxCliente','FechaPago','MontoTotalDeuda','NumeroBoletas','IdSubTrx','CodigoIdentificador','Boleta','Monto','FechaVencimiento');
		$datos = '';
		foreach ($this->arrayOrdenamiento as $key => $val) {
			if (in_array($key, $keyArrays)){
				$datos.=${$key};
			}
		}
		$this->Logs('1', 'Datos Concatenado :'.$datos);
		$firma = $this->encripta($datos);
  		$this->Logs('1', 'Firma para XML1:'.$firma);
		$xml = "<?xml version='1.0' encoding='ISO-8859-1'?><Servipag><Header><FirmaEPS>$firma</FirmaEPS><CodigoCanalPago>$CodigoCanalPago</CodigoCanalPago><IdTxCliente>$IdTxCliente</IdTxCliente><FechaPago>$FechaPago</FechaPago><MontoTotalDeuda>$MontoTotalDeuda</MontoTotalDeuda><NumeroBoletas>$NumeroBoletas</NumeroBoletas></Header><Documentos><IdSubTrx>$IdSubTrx</IdSubTrx><CodigoIdentificador>$CodigoIdentificador</CodigoIdentificador><Boleta>$Boleta</Boleta><Monto>$Monto</Monto><FechaVencimiento>$FechaVencimiento</FechaVencimiento></Documentos></Servipag>";
		$this->Logs('1', 'XML1 completo:'.$xml);
    	return $xml;
	}

 	public function Logs($numero, $texto){
		$realtime = $_SERVER['DOCUMENT_ROOT']."logs/servipag_".date("Ymd").".log";
		$ddf = fopen($realtime,'a');
 		fwrite($ddf,"[".date("r")."]     $numero: $texto \r\n");
 		fclose($ddf);
 	}
 
 	function replaceData($xml, $key){
 		$data = substr($xml,strrpos($xml,"<".$key.">"),strrpos($xml,"</".$key.">") - strrpos($xml,"<".$key.">"));
 		$data = str_replace("<".$key.">", '',$data);
 		return $data;
 	}

	public function CompruebaXML2($xml,$nodo){
		$this->Logs('1', 'Función Comprueba Xml2:');
		$this->Logs('1', 'xml:'.$xml);
		$this->Logs('1', 'nodo:'.$nodo);
		$firma = $this->replaceData($xml,"FirmaServipag");
		$this->Logs('1', 'Obtención Firma dentro XML2 :'.$firma);
		asort($nodo);
		$keyArrays = array('IdTrxServipag','IdTxCliente','FechaPago','CodMedioPago','FechaContable','CodigoIdentificador','Boleta','Monto');
		$datos = '';
		foreach ($nodo as $key => $val) {
			if (in_array($key, $keyArrays)){
				$datos.= $this->replaceData($xml,$key);
			}		
		}
		$this->Logs('5', 'Datos concatenacion para verificación de Firma:'.$datos);
		$datos = str_replace(' ', '',$datos);	
		$this->Logs('5', 'Desencriptación Datos:'.$datos.'--Firma:'.$firma);
		$result = $this->desencripta($datos,$firma);
		$this->Logs(($result)?'1':'2','Firma '.($result)?'':'No '.'Valida : ');
		return ($result) ? true : false;
	}
		
	public function GeneraXML3($Codigo, $Mensaje){
		$this->Logs('5', 'Función Genera Xml3 Código:'.$Codigo.'--Mensaje:'.$Mensaje);
		$xml = "<?xml version='1.0' encoding='ISO-8859-1'?><Servipag><CodigoRetorno>$Codigo</CodigoRetorno><MensajeRetorno>$Mensaje</MensajeRetorno></Servipag>";
		$this->Logs('5', 'Xml3 Generado:'.$xml);
		return $xml;
	}

	function replaceGT($data){
		if(strpos($data, '&lt;')!== false) $data = str_replace('&lt;', '<',$data);
		if(strpos($data, '&gt;')!== false) $data = str_replace('&gt;', '>',$data);
		return $data;
	}

	public function ValidaXml4($Xml4,$nodo){
		$this->Logs('4', '***********************************************************************************');
		$this->Logs('4', 'Función Valida XML4 xml:'.$Xml4.'--Nodos:'.$nodo);
		$Xml4 = $this->replaceGT($Xml4);
		$firma = $this->replaceData($Xml4,"FirmaServipag");
		$this->Logs('4', 'Firma que contiene XML4 :'.$firma);
		asort($nodo);
		$keyArrays = array('IdTrxServipag','IdTxCliente','EstadoPago','Mensaje');
		$datos = '';
		foreach ($nodo as $key => $val) {
			if (in_array($key, $keyArrays)){
				$datos.= $this->replaceData($xml,$key);
			}		
		}
		$this->Logs('4', 'valor de concatenacion de Nodos XML4:'.$datos);
		$result = $this->desencripta($datos,$firma);
		$this->Logs('4','Firma '.($result) ? "" : "No ".'Valida XML4 :');
		$this->Logs('4', '*******************************************************************************************');
		return ($result) ? true : false;	
	}
}