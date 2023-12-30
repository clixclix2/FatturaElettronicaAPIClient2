<?php
// esempio di gestione della paginazione (per volumi alti, sopra ai 1000 risultati attesi per chiamata)
require_once '../FatturaElettronicaApiClient2.class.php';
$feac = new FatturaElettronicaApiClient2($username, $password);
$res = $feac->ricevi();
if ($res) {
    $hasData = true;
    while ($hasData) {
        foreach ($res as $arrDati) {
            if ($arrDati['ricezione']) {
                // ricezione di un documento
            } else {
                // ricezione di un aggiornamento di trasmissione
            }
        }
        if ($feac->hasMoreResults()) {
            $res = $feac->getNextResults();
            $hasData = true;
        }
    }
}
