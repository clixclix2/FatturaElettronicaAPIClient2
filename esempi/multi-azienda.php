<?php

// Esempi di gestione multi-azienda

require_once '../FatturaElettronicaApiClient2.class.php';

$username = '........'; //Username e password forniti dal servizio
$password = '........';
$feac = new FatturaElettronicaApiClient2($username, $password);


$tipoTest = 'elenco';


if ($tipoTest == 'elenco') {

    $res = $feac->elencoAziende();

    if (!$res) {
        echo($feac->getLastError());
    } else {
        foreach ($res as $azienda) {
            var_dump($azienda);
        }
    }
}


if ($tipoTest == 'aggiungi') {

    $ragioneSociale = 'Azienda Test Srl';
    $partitaIva = '12345678901';
    $codiceFiscale = '12345678901';

    $res = $feac->aggiungiAzienda(array(
        'ragione_sociale' => $ragioneSociale,
        'piva' => $partitaIva,
        'cfis' => $codiceFiscale
    ));

    if ($res) {
        $idAzienda = $res['id'];
    } else {
        echo($feac->getLastError());
    }

}

if ($tipoTest == 'dati') {

    $idAzienda = 100;

    $res = $feac->datiAzienda($idAzienda);

    if (!$res) {
        echo($feac->getLastError());
    } else {
        var_dump($res);
    }
}


if ($tipoTest == 'rimuovi') {

    $idAzienda = 100;

    $res = $feac->rimuoviAzienda($idAzienda);

    if (!$res) {
        echo($feac->getLastError());
    }
}


