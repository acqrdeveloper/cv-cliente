<?php
namespace CVClient\Common\Repos;
use CVClient\CV\Models\Notification;
use PHPMailerAutoload; 
use PHPMailer;
use ElephantIO\Client,
    ElephantIO\Engine\SocketIO\Version1X;

class SessionRepo{
	public function __construct()
    {
		// Production
	    $this->PUSHER_APP_ID 		= '123983';
		$this->PUSHER_KEY 			= '2116ac4618cc50e5b9da';
	    $this->PUSHER_SECRET 		= '0f761ebb16ed2a1c8823';
	    $this->PUSHER_DEFAULT_EVENT = 'notify';
		// PARSE PUSH NOTIFICATION
		$this->PARSE_APP_ID 		= 'H72HjXfGLnorqiPPI0lwgI499BGb3Qh5zG3CrWlT';
		$this->PARSE_REST_KEY 		= 'LIEBdUi39z1dhBhUQChOTB7J3sIgwurG1I7MJxTS';
		$this->PARSE_MASTER_KEY 	= 'sTqMiMWBjfhi6pc9ix1PnUtbeARQ7lpH8ejnA3tr';
		// IONIC
		$this->IONIC_APP_ID			= 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJqdGkiOiI1ZTZkZWQxNi0zYzI1LTRlZmMtODM0ZC1jYzkwYWQzZGFkYmMifQ.ARKr8Kx7hwxm7Ghbx68AQZXiXXuMSw2x3Srn5YwwKxg';
	}

	public function CallRaw( $connection, $procName, $parameters = null, $isExecute = false)
    {
	    $syntax = '';
	    for ($i = 0; $i < count($parameters); $i++) {
	        $syntax .= (!empty($syntax) ? ',' : '') . '?';
	    }
	    $syntax = 'CALL ' . $procName . '(' . $syntax . ');';
	    $pdo  	= \DB::connection($connection)->getPdo();
	    $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
	    $stmt 	= $pdo->prepare($syntax,[\PDO::ATTR_CURSOR=>\PDO::CURSOR_SCROLL]);
	    for ($i = 0; $i < count($parameters); $i++) {
	        $stmt->bindValue((1 + $i), $parameters[$i]);
	    }
	    $exec 	= $stmt->execute();
	    if (!$exec) return $pdo->errorInfo();
	    if ($isExecute) return $exec;
	    $results = [];
	    do {
	        try {
	            $results[] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
	        } catch (\Exception $ex) {

	        }
	    } while ($stmt->nextRowset());
	    if (1 === count($results)) return $results[0];
	    return $results;
	}
    //sendMessage($para_email, $para_nombre, $asunto, $mensaje, $de, $acc = '', $bcc = '', $attachment = '', $emails = [])
	public function send( $remitente, $destinatario, $newmensaje, $extras, $pie = true, $attachment = '', $attachname = '' ){
		try {
			$mail = new PHPMailer(true);
			$mail->IsSMTP();
			$mail->SMTPAuth   = true;
			$mail->CharSet    = "UTF-8";
			$mail->Host       = 'dinnovas.com';
			$mail->Port       = '2525';
			$mail->Username   = 'p0w3rmta';
			$mail->Password   = 'p0w3rmta2015$';

			$mail->AddReplyTo( $remitente["de"], 'Centros Virtuales');
			$mail->SetFrom(    $remitente["de"], 'Centros Virtuales');//COMENTAR PARA TEST

			//$mail->AddAddress( trim("gdelportal@sitatel.com"), "Gonzalo A. Del Portal Ch.");//TEST
			$mail->AddAddress( trim($destinatario["correo"]), $destinatario["nombre"]);



			$mail->Subject = $remitente["asunto"];
			$mail->AltBody = 'Para visualizar el mensaje correctamente, por favor habilite la vista HTML';
			// Envia una copia del correo  
            if (isset($extras['addcc'])) {
                if( !empty($extras['addcc']) ){
                    $mail->AddCC( $extras['addcc'] );
                }
            }

			if (isset($extras['addbcc'])) {
                if( is_array($extras['addbcc']) ){
                    foreach( $extras['addbcc'] as $bcc ){
                        $mail->AddBCC( $bcc );    
                    }
    			}
            }

            $mensaje_completo = $newmensaje["mensaje"];
			if ($pie) {
				$mensaje_completo .= $remitente["pie"];
			}
			$mail->MsgHTML( $mensaje_completo );


            if ($attachment != '') {
                $mail->AddAttachment($attachment, ( $attachname == '' ? 'comporbate.pdf' : $attachname) );
            }


			if (false) {
				$envio = true;
				//errorLog('Se intentó envíar un mensaje para "' . $destinatario["correo"] . '"');
			} else {
				$envio = $mail->Send();
				//if (!$envio) {
				//    throw new \Exception($mail->ErrorInfo);
				//}
			}
			$mail->ClearAddresses();
			return $envio;
		} catch (\phpmailerException $epx) {
			throw $epx;
		}
	}

	public function Notification( $values, $message, $usuario, $modulo, $url = null, $noNotify = false, $flag = true )
    {
		if ($flag) {
			$paramsNotify = [
				'creado_por' => $usuario,
				'content' => '',
				'modulo' => $modulo,
				'descripcion' => $message,
				'empresa_nombre' => isset($values['empresa_nombre']) ? $values['empresa_nombre'] : '',
				'url' => $url ? $url : strtolower($modulo),
				'created_at' => date("Y-m-d H:i:s"),
				'tipo' => 1//,
				//'noNotify' => $noNotify
			];
			$notificated = Notification::create($paramsNotify);
			$paramsNotify['noNotify'] = $noNotify;
			$paramsNotify['id'] = (int) $notificated->id;
		} else {
			$paramsNotify = ['noNotify' => true];
		}
		$pushvalues = [
			'model' => strtolower($modulo),
			'notify' => $paramsNotify,
			'data' => json_encode($values, JSON_NUMERIC_CHECK),
			'usuario_id' => (int) \Auth::user()->id
		];
		$this->push($pushvalues);
	}

	public function push( $values )
    {
        /*
		$pusher = new \Pusher( $this->PUSHER_KEY, $this->PUSHER_SECRET, $this->PUSHER_APP_ID );
		$pusher->trigger('centros', $this->PUSHER_DEFAULT_EVENT, $values);
        */
        try {
            $client = new Client(new Version1X( env('NOTIFICATION_SERVER') ));
            $client->initialize();
            $client->emit('emitNotification', ['foo' => 'bar']);
            $client->close();   
        } catch (\Exception $e) {        
        }
	}

    public function numeroLetras($xcifra)
    {
        $xarray = array(0 => "Cero",
            1 => "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE",
            "DIEZ", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE",
            "VEINTI", 30 => "TREINTA", 40 => "CUARENTA", 50 => "CINCUENTA", 60 => "SESENTA", 70 => "SETENTA", 80 => "OCHENTA", 90 => "NOVENTA",
            100 => "CIENTO", 200 => "DOSCIENTOS", 300 => "TRESCIENTOS", 400 => "CUATROCIENTOS", 500 => "QUINIENTOS", 600 => "SEISCIENTOS", 700 => "SETECIENTOS", 800 => "OCHOCIENTOS", 900 => "NOVECIENTOS"
        );
        //
        $xcifra = trim($xcifra);
        //$xlength = strlen($xcifra);
        $xpos_punto = strpos($xcifra, '.');
        $xaux_int = $xcifra;
        $xdecimales = '00';
        if (!($xpos_punto === false)) {
            if ($xpos_punto == 0) {
                $xcifra = '0' . $xcifra;
                $xpos_punto = strpos($xcifra, '.');
            }
            $xaux_int = substr($xcifra, 0, $xpos_punto); // obtengo el entero de la cifra a covertir
            $xdecimales = substr($xcifra . '00', $xpos_punto + 1, 2); // obtengo los valores decimales
        }

        $XAUX = str_pad($xaux_int, 18, " ", STR_PAD_LEFT); // ajusto la longitud de la cifra, para que sea divisible por centenas de miles (grupos de 6)
        $xcadena = "";
        for ($xz = 0; $xz < 3; $xz++) {
            $xaux = substr($XAUX, $xz * 6, 6);
            $xi = 0;
            $xlimite = 6; // inicializo el contador de centenas xi y establezco el límite a 6 dígitos en la parte entera
            $xexit = true; // bandera para controlar el ciclo del While
            while ($xexit) {
                if ($xi == $xlimite) { // si ya llegó al límite máximo de enteros
                    break; // termina el ciclo
                }

                $x3digitos = ($xlimite - $xi) * -1; // comienzo con los tres primeros digitos de la cifra, comenzando por la izquierda
                $xaux = substr($xaux, $x3digitos, abs($x3digitos)); // obtengo la centena (los tres dígitos)
                for ($xy = 1; $xy < 4; $xy++) { // ciclo para revisar centenas, decenas y unidades, en ese orden
                    switch ($xy) {
                        case 1: // checa las centenas
                            if (substr($xaux, 0, 3) < 100) { // si el grupo de tres dígitos es menor a una centena ( < 99) no hace nada y pasa a revisar las decenas

                            } else {
                                $key = (int)substr($xaux, 0, 3);
                                if (true === array_key_exists($key, $xarray)) {  // busco si la centena es número redondo (100, 200, 300, 400, etc..)
                                    $xseek = $xarray[$key];
                                    $xsub = $this->subfijo($xaux); // devuelve el $this->subfijo correspondiente (Millón, Millones, Mil o nada)
                                    if (substr($xaux, 0, 3) == 100)
                                        $xcadena = ' ' . $xcadena . ' CIEN ' . $xsub;
                                    else
                                        $xcadena = ' ' . $xcadena . ' ' . $xseek . ' ' . $xsub;
                                    $xy = 3; // la centena fue redonda, entonces termino el ciclo del for y ya no reviso decenas ni unidades
                                } else { // entra aquí si la centena no fue numero redondo (101, 253, 120, 980, etc.)
                                    $key = (int)substr($xaux, 0, 1) * 100;
                                    $xseek = $xarray[$key]; // toma el primer caracter de la centena y lo multiplica por cien y lo busca en el arreglo (para que busque 100,200,300, etc)
                                    $xcadena = ' ' . $xcadena . ' ' . $xseek;
                                } // ENDIF ($xseek)
                            } // ENDIF (substr($xaux, 0, 3) < 100)
                            break;
                        case 2: // checa las decenas (con la misma lógica que las centenas)
                            if (substr($xaux, 1, 2) < 10) {

                            } else {
                                $key = (int)substr($xaux, 1, 2);
                                if (true === array_key_exists($key, $xarray)) {
                                    $xseek = $xarray[$key];
                                    $xsub = $this->subfijo($xaux);
                                    if (substr($xaux, 1, 2) == 20)
                                        $xcadena = ' ' . $xcadena . ' VEINTE ' . $xsub;
                                    else
                                        $xcadena = ' ' . $xcadena . ' ' . $xseek . ' ' . $xsub;
                                    $xy = 3;
                                } else {
                                    $key = (int)substr($xaux, 1, 1) * 10;
                                    $xseek = $xarray[$key];
                                    if (20 == substr($xaux, 1, 1) * 10)
                                        $xcadena = ' ' . $xcadena . ' ' . $xseek;
                                    else
                                        $xcadena = ' ' . $xcadena . ' ' . $xseek . ' Y ';
                                } // ENDIF ($xseek)
                            } // ENDIF (substr($xaux, 1, 2) < 10)
                            break;
                        case 3: // checa las unidades
                            if (substr($xaux, 2, 1) < 1) { // si la unidad es cero, ya no hace nada

                            } else {
                                $key = (int)substr($xaux, 2, 1);
                                $xseek = $xarray[$key]; // obtengo directamente el valor de la unidad (del uno al nueve)
                                $xsub = $this->subfijo($xaux);
                                $xcadena = ' ' . $xcadena . ' ' . $xseek . ' ' . $xsub;
                            } // ENDIF (substr($xaux, 2, 1) < 1)
                            break;
                    } // END SWITCH
                } // END FOR
                $xi = $xi + 3;
            } // ENDDO

            if (substr(trim($xcadena), -5, 5) == 'ILLON') // si la cadena obtenida termina en MILLON o BILLON, entonces le agrega al final la conjuncion DE
                $xcadena .= ' DE';

            if (substr(trim($xcadena), -7, 7) == 'ILLONES') // si la cadena obtenida en MILLONES o BILLONES, entoncea le agrega al final la conjuncion DE
                $xcadena .= ' DE';

            // ----------- esta línea la puedes cambiar de acuerdo a tus necesidades o a tu país -------
            if (trim($xaux) != '') {
                switch ($xz) {
                    case 0:
                        if (trim(substr($XAUX, $xz * 6, 6)) == '1')
                            $xcadena .= 'UN BILLON ';
                        else
                            $xcadena .= ' BILLONES ';
                        break;
                    case 1:
                        if (trim(substr($XAUX, $xz * 6, 6)) == '1')
                            $xcadena .= 'UN MILLON ';
                        else
                            $xcadena .= ' MILLONES ';
                        break;
                    case 2:
                        if ($xcifra < 1) {
                            $xcadena = "CERO CON $xdecimales/100 SOLES";// M.N.
                        }
                        if ($xcifra >= 1 && $xcifra < 2) {
                            $xcadena = "UN SOL CON $xdecimales/100 SOL";// M.N.
                        }
                        if ($xcifra >= 2) {
                            $xcadena .= " CON $xdecimales/100 SOLES"; //// M.N.
                        }
                        break;
                } // endswitch ($xz)
            } // ENDIF (trim($xaux) != "")
            // ------------------      en este caso, para México se usa esta leyenda     ----------------
            $xcadena = str_replace('VEINTI ', 'VEINTI', $xcadena); // quito el espacio para el VEINTI, para que quede: VEINTICUATRO, VEINTIUN, VEINTIDOS, etc
            $xcadena = str_replace('  ', ' ', $xcadena); // quito espacios dobles
            $xcadena = str_replace('UN UN', 'UN', $xcadena); // quito la duplicidad
            $xcadena = str_replace('  ', ' ', $xcadena); // quito espacios dobles
            $xcadena = str_replace('BILLON DE MILLONES', 'BILLON DE', $xcadena); // corrigo la leyenda
            $xcadena = str_replace('BILLONES DE MILLONES', 'BILLONES DE', $xcadena); // corrigo la leyenda
            $xcadena = str_replace('DE UN', 'UN', $xcadena); // corrigo la leyenda
        } // ENDFOR ($xz)
        return trim($xcadena);
    }

    private function subfijo($xx)
    { // esta función regresa un subfijo para la cifra
        $xx = trim($xx);
        $xstrlen = strlen($xx);
        if ($xstrlen == 1 || $xstrlen == 2 || $xstrlen == 3)
            $xsub = '';
        //
        if ($xstrlen == 4 || $xstrlen == 5 || $xstrlen == 6)
            $xsub = 'MIL';
        //
        return $xsub;
    }

}
?>