<?php

// Invio di una fattura elettronica tramite dati documento

$username = '......'; // Username e password forniti dal servizio
$password = '......';
$isTest = true; // modalità test o produzione
require_once '../FatturaElettronicaApiClient.class.php';
$feac = new FatturaElettronicaApiClient2($username, $password, NULL, $isTest);

$datiDestinatario = [
	'PartitaIVA' => '12345678901',
	'CodiceFiscale' => '12345678901',
	'CodiceSDI' => '0000000',
	'Denominazione' => 'Azienda di test S.r.l.',
	'Indirizzo' => 'Via Col Vento, 1',
	'CAP' => '00100',
	'Comune' => 'Roma',
	'Provincia' => 'RM'
];

$datiDocumento = [
	'Data' => '2024-01-13',
	'Numero' => '123'
];

$righeDocumento = [
	[
		'Descrizione' => 'Installazione avvolgibile, manodopera (ore)',
		'PrezzoUnitario' => 50,
		'Quantita' => 3
	],
	[
		'Descrizione' => 'Avvolgibile in PVC',
		'PrezzoUnitario' => 100
	]
];



$res = $feac->inviaConDati($datiDestinatario, $datiDocumento, $righeDocumento);

if ($res) {
	$idFatturaElettronicaApi = $res['id'];
	$identificativoSDI = $res['sdi_identificativo'];
	$fatturaXml = $res['sdi_fattura'];
} else {
    echo $feac->getLastError();
}

