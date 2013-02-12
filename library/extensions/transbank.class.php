<?php
class Transbank{

	// Si siempre te tira a pagina de fracaso, recuerda guardar tu tbk_config.dat en formato UTF-8 sin BOM
	// Chequea tu .htaccess para evitar que la redireccion vaya a cgi-bin con la siguiente linea
	// RewriteCond %{REQUEST_URI} !^/cgi-bin/ [NC]

	function transaction($tbkPost = '',$ordenCompra = ''){
		$cgiPath = realpath($_SERVER["DOCUMENT_ROOT"])."/cgi-bin/";
		$logPath = $cgiPath."log/";
		
		if(!empty($tbkPost)){
			if($tbkPost['TBK_RESPUESTA'] == 0){
				
				// 1.- Abrir archivo y guardar variables POST recibidas
				$filename = $logPath."log_".$tbkPost['TBK_ID_TRANSACCION'].".txt";
				$fp=fopen($filename,"w");
				while (list($key,$val) = each($tbkPost)){ fwrite($fp,"$key=$val&"); }
				fclose($fp);
				
				// 2.- Invocar a tbk_check_mac (Que en realidad no es una cgi) usando como parámetro el archivo generado
				$cmdline = $cgiPath."tbk_check_mac.cgi $filename";
				exec($cmdline,$result,$errorCode);

				try{
					// Comprobacion el resultado del cgi de MAC
					if($result[0]=="CORRECTO"){
						// Comprobacion de Orden de Compra
						if (!empty($ordenCompra)){
							// Comprobacion de Monto
							// $trs_monto = substr($this->tbkPost['TBK_MONTO'],0,-2).".00"; // KCC 5.1
							$trs_monto = substr($tbkPost['TBK_MONTO'],0,-2); // KCC 6.0
							if ($ordenCompra['Pago']['TBK_MONTO'] == $trs_monto){
								return true;
							}else{
								throw new Exception("Comprobacion de Montos RECHAZADA => ".$ordenCompra['Pago']['TBK_MONTO']." == ".$trs_monto, 1);
							}
						}else{
							throw new Exception("Comprobacion Orden de Compra RECHAZADA",1);
						}
					}else{
						throw new Exception("Validacion MAC rechazada",1);
					}
				}
				catch(Exception $e){
					file_put_contents($logPath."rechazado_".$tbkPost['TBK_ID_TRANSACCION'].".txt", time().' => '.$e->getMessage(), FILE_APPEND);
					return false;
				}
			}else{
				return true;
			}
		}else{
			return true;
		}	
	}
}