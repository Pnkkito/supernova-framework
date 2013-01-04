<?php

class Transbank{

	private $tbkPost;

	function transaction($tbkpost,$ordenCompra){

		$this->tbkPost = $tbkpost;

		$logPath = $_SERVER['DOCUMENT_ROOT']."cgi-bin/log/";
		$compraValida = false;
		$error = '';
		// file_put_contents($logPath.'tbkpost_'.time().'.txt',print_r($this->tbkPost,true));
		
		$this->Pago = new Pago;
		$this->Wepbay = new Webpay;

		if(!empty($this->tbkPost)){
			if($this->tbkPost['TBK_RESPUESTA']==0){
				// Validacion de MAC
				// 1.- Abrir archivo y guardar variables POST recibidas
				$filename = $logPath."log_".$this->tbkPost['TBK_ID_TRANSACCION'].".txt";
				$fp=fopen($filename,"w");
				while (list($key,$val) = each($this->tbkPost)){ fwrite($fp,"$key=$val&"); }
				fclose($fp);
				
				// 2.- Invocar a tbk_check_mac (Que en realidad no es una cgi) usando como parámetro el archivo generado
				$cmdline = $_SERVER['DOCUMENT_ROOT']."cgi-bin/tbk_check_mac.cgi $filename";
				exec($cmdline,$result,$retint);
				
				// Comprobacion el resultado del cgi de MAC
				if($result[0]=="CORRECTO"){
					// Comprobacion de Orden de Compra
					// $ordenCompra = $this->Pago->find('first', array('conditions' => array('TBK_ORDEN_COMPRA' => $this->tbkPost['TBK_ORDEN_COMPRA']),'order' => 'TBK_ORDEN_COMPRA DESC','limit' => '1'));
					if (!empty($ordenCompra)){
						// Comprobacion de Monto
						$trs_monto = substr($this->tbkPost['TBK_MONTO'],0,-2).".00";
						if ($ordenCompra['Pago']['TBK_MONTO'] == $trs_monto){
							$compraValida = true;
						}else{
							$error = "Comprobacion de Montos RECHAZADA => ".$ordenCompra['Pago']['TBK_MONTO']." == ".$trs_monto;
						}
					}else{
						$error = "Comprobacion Orden de Compra RECHAZADA";
					}
				}else{
					$error = "Validacion MAC rechazada";
				}
			}else{
				$compraValida = true;
			}
		}
		
		if ($compraValida == true && empty($error)){
			echo "ACEPTADO";
			return true;
		}else{
			file_put_contents($logPath."error_".$this->tbkPost['TBK_ID_TRANSACCION'].".txt", $error);
			echo "RECHAZADO";
			return false;
		}
	}
}