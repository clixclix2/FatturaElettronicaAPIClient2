<?php

// Esempio di codice per gestire le chiamate webhook dal server FatturaElettronicaAPI

// https://fattura-elettronica-api.it/guida2.0/

// verifica autorizazione Bearer
$preSharedBearerToken = '.................................'; // il token segreto, da noi generato, che abbiamo inserito nel pannello fattura-elettronica-api.it

$headers = getallheaders();
$authorization = $headers['Authorization'] ?? NULL;
if (!preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
    http_response_code(401);
    die('Auth error');
}
$token = $matches[1];
if ($token != $preSharedBearerToken) {
    http_response_code(401);
    die('Auth error 2');
}

/** @var mysqli $database */

$strInput = file_get_contents('php://input');
$arrInput = json_decode($strInput, true);

foreach ($arrInput as $aggiornamento) {

    if ($aggiornamento['ricezione']) {

        // nuovo documento ricevuto
        $database->query("
            INSERT INTO fatture_elettroniche
            SET id_fattura_elettronica_api = {$aggiornamento['id']},
                sdi_identificativo = '" . $database->escape_string($aggiornamento['sdi_identificativo']) . "',
                sdi_stato = 'RICE', -- ricezione
                sdi_fattura = '" . $database->escape_string($aggiornamento['sdi_fattura']) . "',
				sdi_fattura_xml = '" . $database->escape_string($aggiornamento['sdi_fattura_xml']) . "',
				sdi_data_aggiornamento = '" . $database->escape_string($aggiornamento['sdi_data_aggiornamento']) . "',
				sdi_messaggio = '" . $database->escape_string($aggiornamento['sdi_messaggio']) . "',
				sdi_nome_file = '" . $database->escape_string($aggiornamento['sdi_nome_file']) . "'
        ");

    } else {
        
        // aggiornamento invio
        $idFea = $aggiornamento['id'];
        $stato = $aggiornamento['sdi_stato']; // ERRO, CONS, NONC, ACCE, RIFI, DECO
        $messaggio = $aggiornamento['sdi_messaggio'];

        $database->query("
            UPDATE fatture_elettroniche
            SET sdi_stato = '{$stato}', 
                sdi_messaggio = '" . $database->escape_string($messaggio) . "', 
                sdi_identificativo = '" . $database->escape_string($aggiornamento['sdi_identificativo']) . "',
                sdi_data_aggiornamento = NOW()
            WHERE id_fattura_elettronica_api = {$idFea}
        ");

    }

}

