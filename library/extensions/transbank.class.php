<?php
class Transbank{

	// Si siempre te tira a pagina de fracaso, recuerda guardar tu tbk_config.dat en formato UTF-8 sin BOM

	function transaction($tbkPost = '',$ordenCompra = ''){
		$cgiPath = $_SERVER['DOCUMENT_ROOT']."/cgi-bin/";
		$logPath = $cgiPath."log/";
		
		try{
			if(!empty($tbkPost)){
				if($tbkPost['TBK_RESPUESTA'] == 0){
					// Validacion de MAC
					// 1.- Abrir archivo y guardar variables POST recibidas
					$filename = $logPath."log_".$tbkPost['TBK_ID_TRANSACCION'].".txt";
					$fp=fopen($filename,"w");
					while (list($key,$val) = each($tbkPost)){ fwrite($fp,"$key=$val&"); }
					fclose($fp);
					
					// 2.- Invocar a tbk_check_mac (Que en realidad no es una cgi) usando como parámetro el archivo generado
					
					// Si tienes permisos de "exec" para php, utiliza la siguiente linea 
					$cmdline = $cgiPath."tbk_check_mac.cgi $filename";
					exec($cmdline,$result,$retint);
					
					// SI NO FUNCIONA, prueba creando el siguiente chkmac.cgi con las siguientes lineas
					/*
						#!/usr/bin/perl
						use CGI qw(:standard);

						my $pass = 'imthewholecornolio';
						my $filename = param('filename');
						my $tbk_pass = param('passwd');

						my $cmd = "./tbk_check_mac.cgi ". $filename;

						print "Content-type: text/html\n\n";
						if ($pass == $tbk_pass) {
							print exec($cmd);
						}
					*/

					// y descomenta las siguientes lineas
					/*
						$cgi_pass = "imthewholecornolio";
						$result[0] = file_get_contents($cgiPath."chkmac.cgi?passwd=".$cgi_pass."&filename=".$filename);
						file_put_contents($logPath."cgilog_".$tbkPost['TBK_ID_TRANSACCION'].".txt", time().' => '.$result[0], FILE_APPEND);	
					*/

					// Si ambos no funcionan, pidele a tu proveedor de hosting que te permita el "exec" del php o cgi

					// Comprobacion el resultado del cgi de MAC
					if($result[0]=="CORRECTO"){
						// Comprobacion de Orden de Compra
						// $ordenCompra = $this->Pago->find('first', array('conditions' => array('TBK_ORDEN_COMPRA' => $this->tbkPost['TBK_ORDEN_COMPRA']),'order' => 'TBK_ORDEN_COMPRA DESC','limit' => '1'));
						if (!empty($ordenCompra)){
							// Comprobacion de Monto
							// $trs_monto = substr($this->tbkPost['TBK_MONTO'],0,-2).".00";
							$trs_monto = substr($tbkPost['TBK_MONTO'],0,-2);
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
				}else{
					return true;
				}
			}else{
				return true;
			}
		}
		catch(Exception $e){
			file_put_contents($logPath."rechazado_".$tbkPost['TBK_ID_TRANSACCION'].".txt", time().' => '.$e->getMessage(), FILE_APPEND);
			return false;
		}
	}
}