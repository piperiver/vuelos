<?php
require_once "hogar.php";
/*$cartagenaCali = file_get_contents("https://bff.latam.com/ws/proxy/booking-webapp-bff/v1/public/revenue/recommendations/oneway?country=CO&language=ES&home=es_co&origin=CTG&destination=CLO&departure=2018-07-21&adult=1&cabin=Y");
*/




class Vuelos
{

	var $tope = 180000;

	public function controller($htmlHogar)
	{
		//set_time_limit(0);
		// date_default_timezone_set('America/Bogota');



		$fechaInicio = strtotime("now");
		$fechaFin = strtotime("31-01-2020");

		$tbodyCaliCartagena = "";
		$tbodyCargatenaCali = "";
		for ($i = $fechaInicio; $i <= $fechaFin; $i += 86400) {
			//Cali a cartagena
			$infoApi = $this->getTarifas(date("Y", $i), date("m", $i), date("d", $i), "CLO", "CTG");
			if ($infoApi !== false && !empty($infoApi)) {
				$arrayInformacion = $this->procesarInformacion(json_decode($infoApi));
				if (count($arrayInformacion) > 0) {
					$tbodyCaliCartagena .= $this->construirBody($arrayInformacion);
				}
			}


			//Cartagena a cali
			$infoApi = $this->getTarifas(date("Y", $i), date("m", $i), date("d", $i), "CTG", "CLO");
			if ($infoApi !== false && !empty($infoApi)) {
				$arrayInformacion = $this->procesarInformacion(json_decode($infoApi));
				if (count($arrayInformacion) > 0) {
					$tbodyCargatenaCali .= $this->construirBody($arrayInformacion);
				}
			}
		}

		$htmlCaliCartagena = $this->armarTabla($tbodyCaliCartagena);
		$htmlCartagenaCali = $this->armarTabla($tbodyCargatenaCali);

		$html = "
		<div>
			<h1>Vuelos de Cali a Cartagena</h1>
			" . $htmlCaliCartagena . "
		</div>

		<div>
			<h1>Vuelos de Cartagena a Cali</h1>
			" . $htmlCartagenaCali . "
		</div>";

		if (isset($_GET["sendMail"])) {
			$this->enviarEmail($html);
			if(!empty($htmlHogar)){
				$this->enviarEmail($htmlHogar);
			}
		} else {
			echo $html;
		}
	}

	public function enviarEmail($message)
	{
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

		$correos = "piperiver7@gmail.com";
		$subject = "Listado de precios del año Latam";
		$result = mail($correos, $subject, $message, $headers);
		die();
	}

	public function getTarifas($annio, $mes, $dia, $origen, $destino)
	{
		try {
			$infoApi = file_get_contents("https://bff.latam.com/ws/proxy/booking-webapp-bff/v1/public/revenue/recommendations/oneway?country=CO&language=ES&home=es_co&origin=" . $origen . "&destination=" . $destino . "&departure=" . $annio . "-" . $mes . "-" . $dia . "&adult=1&cabin=Y");
			return $infoApi;
		} catch (Exception $e) {
			return false;
		}
	}

	public function procesarInformacion($data)
	{

		if (isset($data->data->flights) && count($data->data->flights) > 0) {

			$arrayVuelos = [];
			foreach ($data->data->flights as $vuelo) {

				if ($vuelo->cabins[0]->displayPrice <= $this->tope) {
					setlocale(LC_TIME, "es_CO");
					$arrayVuelos[] = [
						"paradas" => (isset($vuelo->stops)) ? $vuelo->stops : "",
						"duracion" => (isset($vuelo->flightDuration)) ? str_replace("PT", "", $vuelo->flightDuration) : "",
						"horaSalida" => (isset($vuelo->departure->dateTime)) ? date("d-m-y H:i", strtotime($vuelo->departure->dateTime)) : "",
						"horaLlegada" => (isset($vuelo->arrival->dateTime)) ? date("d-m-y H:i", strtotime($vuelo->arrival->dateTime)) : "",
						"precio" => (isset($vuelo->cabins[0]->displayPrice)) ? number_format($vuelo->cabins[0]->displayPrice, 0, ",", ".") : "",
						"dia" => (isset($vuelo->departure->dateTime)) ? strftime("%A", strtotime($vuelo->departure->dateTime)) : ""
					];
				}
			}

			return $arrayVuelos;
		}
		return [];
	}

	public function construirBody($data)
	{
		$html = "";

		foreach ($data as $tarifa) {
			$html .= "	 	
			<tr>
				<td style='border: 1px solid #ddd;'>" . $tarifa["dia"] . "</td>
				<td style='border: 1px solid #ddd;'>" . $tarifa["horaSalida"] . "</td>
				<td style='border: 1px solid #ddd;'>" . $tarifa["horaLlegada"] . "</td>
				<td style='border: 1px solid #ddd;'>" . $tarifa["duracion"] . "</td>
				<td style='border: 1px solid #ddd;'>" . $tarifa["paradas"] . "</td>
				<td style='border: 1px solid #ddd;'>" . $tarifa["precio"] . "</td>
			</tr>";
		}
		return $html;
	}

	public function armarTabla($tbody)
	{

		return "
        <style>
            #customers {
                font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;
                border-collapse: collapse;
                width: 100%;
            }

            #customers td, #customers th {
                border: 1px solid #ddd;
                padding: 8px;
            }

            #customers tr:nth-child(even){background-color: #f2f2f2;}

            #customers tr:hover {background-color: #ddd;}

            #customers th {
                padding-top: 12px;
                padding-bottom: 12px;
                text-align: left;
                background-color: #4CAF50;
                color: white;
            }
        </style>
	<div>
			<table id='customers' style='font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;border-collapse: collapse;width: 100%;'>
				<thead>
					<tr>
						<th style='border: 1px solid #ddd;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: #4CAF50;color: white;'>Dia</th>
						<th style='border: 1px solid #ddd;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: #4CAF50;color: white;'>Fecha salida</th>
						<th style='border: 1px solid #ddd;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: #4CAF50;color: white;'>Fecha llegada</th>
						<th style='border: 1px solid #ddd;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: #4CAF50;color: white;'>Duraci&oacute;n</th>
						<th style='border: 1px solid #ddd;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: #4CAF50;color: white;'>Paradas</th>
						<th style='border: 1px solid #ddd;padding-top: 12px;padding-bottom: 12px;text-align: left;background-color: #4CAF50;color: white;'>Precio</th>
					</tr>
				</thead>
				<tbody>
					" . $tbody . "
				</tbody>
			</table>
		</div>";
	}
}

$objConsultas = new consultas;
$htmlHogar = $objConsultas->index();

$objVuelos = new Vuelos();
$objVuelos->controller($htmlHogar);
