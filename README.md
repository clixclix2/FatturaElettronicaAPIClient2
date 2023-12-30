# FatturaElettronicaAPIClient2
Client PHP per utilizzare il servizio fattura-elettronica-api.it (v.2.0)

Questa libreria PHP consente di inviare e ricevere le fatture elettroniche dal tuo gestionale al Sistema di Interscambio (SDI) dell'Agenzia delle Entrate, tramite il servizio https://fattura-elettronica-api.it

Il servizio consente la creazione e la ricezione delle fatture sia in formato XML, sia in forma semplificata, senza necessità di creare o leggere XML.

## Utilizzo
La libreria è composta da un'unica classe: *FatturaElettronicaApiClient2*

I metodi principali sono:
* ***invia()*** - Invia una fattura XML al SDI
* ***inviaConDati()*** - Invia una fattura al SDI, specificando i dati della fattura (destinatario, data, numero, righe del documento)
* ***ricevi()*** - Ritorna gli eventuali nuovi documenti ricevuti o aggiornamenti sui documenti inviati
* ***riceviDocumenti()*** - Ritorna gli eventuali nuovi documenti ricevuti
* ***riceviAggiornamenti()*** - Ritorna gli eventuali nuovi aggiornamenti sui documenti inviati
* ***ottieniPDF()*** - Ritorna una versione PDF leggibile di una fattura elettronica
* ***ottieniAllegati()*** - Ritorna gli eventuali file allegati

### Inizializzazone
```php
$username = '......'; // Username e password forniti dal servizio
$password = '......';
$isTest = true; // per lavorare con l'endpoint di test
$feac = new FatturaElettronicaApiClient2($username, $password, NULL, $isTest);
```
### Trasmissione di una fattura XML
```php
/**
 * Invia un documento (fattura, nota di credito, nota di debito, etc.) al SdI, trmamite Fattura Elettronica API
 * Il sistema (FatturaElettronicaAPI) aggiungerà o modificherà la sezione relativa ai dati di trasmissione (sezione FatturaElettronicaHeader/DatiTrasmissione dell'XML)
 * @param string $xml Documento XML, charset UTF-8
 * @return null|array (id, sdi_identificativo, sdi_nome_file, sdi_fattura, sdi_stato, sdi_messaggio)
 */
function invia($xml) {}
```
### Trasmissione di una fattura tramite i dati del documento
```php
/**
 * Invia un documento al SdI tramite Fattura Elettronica API, indicando i dati del documento
 * Lista completa dei dati che è possibile specificare: https://fattura-elettronica-api.it/guida2.0/#approfondimenti_datifattura
 * Questo metodo può gestire le casistiche di fatturazine più comuni. Per casistiche più complesse, è necessario generare l'XML completo ed utilizzare il metodo invia()
 * Per utilizzare questo metodo, è necessario aver inserito i propri dati aziendali completi nel pannello di controllo fattura-elettronica-api.it, nella sezione "Dati per generazione automatica fatture", oppure tramite API /aziende
 * In caso di esito positivo, la fattura elettronica finale (quella effettivamente trasmessa al SDI) viene ritornata nel campo 'sdi_fattura'
 * @param array $datiDestinatario PartitaIVA (opz.), CodiceFiscale (opz.), PEC (opz.), CodiceSDI (opz.), Denominazione, Indirizzo, CAP, Comune, Provincia (opz.), Nazione (opz., codice a 2 lettere)
 * @param array $datiDocumento tipo=FATT,NDC,NDD (opz. - default 'FATT'), Data, Numero, Causale (opz.)
 * @param array $righeDocumento Ogni riga è un array coi campi: Descrizione, PrezzoUnitario, Quantita (opz.), AliquotaIVA (opz. - default 22)
 * @param string $partitaIvaMittente In caso di scenario multi-azienda, specificare la partita iva del Cedente
 * @return null|array
 */
function inviaConDati($datiDestinatario, $datiDocumento, $righeDocumento, $partitaIvaMittente = null) {}
```
Per una guida completa ai dati che è possibile inserire in fattura, vedere la guida online: https://fattura-elettronica-api.it/guida2.0/#approfondimenti_datifattura
### Ricezione nuove fatture e aggiornamenti sui documenti inviati
```php
/**
 * Ritorna gli eventuali nuovi documenti ricevuti o aggiornamenti sui documenti inviati
 * Vedere guida online per i campi ritornati (https://fattura-elettronica-api.it/guida2.0/#ricezione)
 * Nota: in alternativa alla ricezione dei documenti e degli aggiornamenti di invio con ricevi(), è possibile configurare la ricezione automatica tramite webhook (vedere guida online)
 * Una volta ricevuto un documento o un aggiornamento, questo non viene più trasmesso alle successive invocazioni del metodo ricevi(), salvo andando sul pannello di controllo e reimpostando la spunta "Da leggere"
 * Paginazione: verificare hasMoreResults() e getNextResults()
 * @param array $opzioni array associativo - vedere guida
 * @return null|array array di array coi campi: partita_iva, ricezione, sdi_identificativo, sdi_messaggio, sdi_nome_file, sdi_fattura, sdi_fattura_xml, sdi_data_aggiornamento, sdi_stato, dati_mittente, dati_documento, righe_documento
 */
function ricevi($opzioni) {}
```
### Ricezione nuove fatture
```php
/**
 * Ritorna gli eventuali nuovi documenti ricevuti
 * Vedere guida online per i campi ritornati (https://fattura-elettronica-api.it/guida2.0/#ricezione)
 * Nota: in alternativa alla ricezione dei documenti con riceviDocumenti(), è possibile configurare la ricezione automatica tramite webhook (vedere guida online)
 * Una volta ricevuto un documento, questo non viene più trasmesso alle successive invocazioni del metodo riceviDocumenti(), salvo andando sul pannello di controllo e reimpostando la spunta "Da leggere"
 * Paginazione: verificare hasMoreResults() e getNextResults()
 * @param array $opzioni array associativo - vedere guida
 * @return null|array array di array coi campi: partita_iva, ricezione, sdi_identificativo, sdi_messaggio, sdi_nome_file, sdi_fattura, sdi_fattura_xml, sdi_data_aggiornamento, sdi_stato, dati_mittente, dati_documento, righe_documento
 */
function riceviDocumenti($opzioni = []) {}
```
### Ricezione aggiornamenti sui documenti inviati
```php
/**
 * Ritorna gli eventuali nuovi aggiornamenti sui documenti inviati
 * Vedere guida online per i campi ritornati (https://fattura-elettronica-api.it/guida2.0/#ricezione)
 * Nota: in alternativa alla ricezione dei documenti con riceviDocumenti(), è possibile configurare la ricezione automatica tramite webhook (vedere guida online)
 * Una volta ricevuto un aggiornamento, questo non viene più trasmesso alle successive invocazioni del metodo riceviAggiornamenti(), salvo andando sul pannello di controllo e reimpostando la spunta "Da leggere"
 * Paginazione: verificare hasMoreResults() e getNextResults()
 * @param array $opzioni array associativo - vedere guida
 * @return null|array array di array coi campi: partita_iva, ricezione, sdi_identificativo, sdi_messaggio, sdi_nome_file, sdi_fattura, sdi_fattura_xml, sdi_data_aggiornamento, sdi_stato, dati_mittente, dati_documento, righe_documento
 */
function riceviAggiornamenti($opzioni = []) {}
```
### Ottenimento del file PDF che rappresenta una fattura elettronica
```php
/**
 * Ottiene la rappresentazione PDF di un documento ricevuto
 * @param int $idDocumento - ID FatturaElettronicaAPI
 * @return null|string doccumento PDF in formato binario
 */
function ottieniPDF($idDocumento) {}
```
### Ottenimento degli eventuali allegati di una fattura elettronica
```php
/**
 * Ottiene gli eventuali file allegati ad una fattura ricevuta
 * @param int $idDocumento - ID FatturaElettronicaAPI
 * @return null|array array di array(nome_file, descrizione, file_base64)
 */
function ottieniAllegati($idDocumento) {}
```
## Esempio di utilizzo
### Predisposizione della tabella sul database MySQL
```sql
CREATE TABLE `fatture_elettroniche` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_fattura` int(10) unsigned DEFAULT NULL, -- RIFERIMENTO ALLA FATTURA SUL PROPRIO DATABASE
  `id_fattura_elettronica_api` bigint(20) unsigned DEFAULT NULL, -- identificativo "fattura elettronica api"
  `sdi_identificativo` bigint(20) unsigned DEFAULT NULL,
  `sdi_stato` varchar(14) CHARACTER SET utf8 NOT NULL,
  `sdi_fattura` mediumtext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `sdi_fattura_xml` mediumtext CHARACTER SET utf8 NOT NULL,
  `sdi_data_aggiornamento` datetime NOT NULL,
  `sdi_messaggio` text CHARACTER SET utf8 NOT NULL,
  `sdi_nome_file` varchar(50) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_fattura` (`id_fattura`),
  KEY `id_fattura_elettronica_api` (`id_fattura_elettronica_api`)
);
```
### Invio fattura XML
```php
$idFattura = [identificativo della fattura sul proprio database];
$fatturaXml = creaFatturaXml($idFattura); // Funzione - da creare - che estrae i dati della fattura dal proprio database e crea il formato XML nel formato <FatturaElettronica> (vedere esempi)

$res = $feac->invia($fatturaXml);

if ($res) {
	$stato = 'Inviato';
	$messaggio = $res['sdi_messaggio'];
	$sdiIidentificativoDB = $res['sdi_identificativo'] ? intval($res['sdi_identificativo']) : 'NULL';
	$fatturaXml = $res['sdi_fattura']; // La fattura elettronica xml finale
	$nomeFile = $res['sdi_nome_file'];
	$idFeaDB = intval($res['id']);
} else {
	$stato = 'Errore';
	$messaggio = $feac->getLastError();
	$sdiIidentificativoDB = 'NULL';
	// $fatturaXml = $fatturaXml; // salviamo inalterata la fattura provvisoria
	$nomeFile = '';
	$idFeaDB = 'NULL';
}

$sqlInsertUpdate = "
	sdi_fattura = '" . $database->escape_string($fatturaXml) . "',
	sdi_nome_file = '" . $database->escape_string($nomeFile) . "',
	sdi_stato = '" .  $database->escape_string($stato) . "',
	sdi_messaggio = '" .  $database->escape_string($messaggio) . "',
	sdi_identificativo = {sdiIidentificativoDB},
	sdi_data_aggiornamento = now(),
	id_fattura = {$idFattura},
	id_fattura_elettronica_api = {$idFeaDB}
";

/** @var mysqli $database */

$lineFE = $database->query("
	SELECT * FROM fatture_elettroniche WHERE id_fattura = {$idFattura}
")->fetch_assoc();

if ($lineFE) { // aggiorniamo un record esistente
	$database->query("
		UPDATE fatture_elettroniche
		SET {$sqlInsertUpdate}
		WHERE id_fattura = {$idFattura}
	");
} else { // inseriamo un nuovo record
	$database->query("
		INSERT INTO fatture_elettroniche
		SET {$sqlInsertUpdate}
	");
}
```

### Invio fattura tramite dati documento
```php
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


$username = '......'; // Username e password forniti dal servizio
$password = '......';
$isTest = true; // test o produzione
$feac = new FatturaElettronicaApiClient2($username, $password, NULL, $isTest);

$res = $feac->inviaConDati($datiDestinatario, $datiDocumento, $righeDocumento);

if ($res) {
	$idFatturaElettronicaApi = $res['id'];
	$identificativoSDI = $res['sdi_identificativo'];
	$fatturaXml = $res['sdi_fattura'];
}
```

### Ricezione fatture ed aggiornamenti
```php
// Script da invocare periodicamente, per esempio ogni 30 minuti

$result = $feac->ricevi();

if (!$result) {
	echo "Errore: " . $feac->getLastError();
} else {
	echo "Elaborazione iniziata: " . date('Y-m-d H:i:s') . "\n<br>";
	foreach ($result as $arrDati) {
		if (!$arrDati['ricezione']) {
    
			// È un aggiornamento di un invio
			if ($arrDati['sdi_stato'] == 'ERRO') {
				$sdiStato = 'Errore';
			} elseif ($arrDati['sdi_stato'] == 'CONS') {
				$sdiStato = 'Consegnato';
			} elseif ($arrDati['sdi_stato'] == 'NONC') {
				$sdiStato = 'Non Consegnato';
			} elseif ($arrDati['sdi_stato'] == 'ACCE') {
				$sdiStato = 'Accettato'; // solo pubblica amministrazione
			} elseif ($arrDati['sdi_stato'] == 'RIFI') {
				$sdiStato = 'Rifiutato'; // solo pubblica amministrazione
			} elseif ($arrDati['sdi_stato'] == 'DECO') {
				$sdiStato = 'Decorrenza Termini'; // solo pubblica amministrazione
			} else {
				$sdiStato = $arrDati['sdi_stato'];
			}
			$sdiMessaggio = $arrDati['sdi_messaggio'];
			$sdiIdentificativoDB = $arrDati['sdi_identificativo'] ? intval($arrDati['sdi_identificativo']) : 'NULL';
			
			$database->query("
				UPDATE fatture_elettroniche
				SET sdi_stato = '{$sdiStato}',
					sdi_messaggio = '" . $database->escape_string($sdiMessaggio) . "',
					sdi_identificativo = {$sdiIdentificativoDB}
				WHERE id_fattura_elettronica_api = " . intval($arrDati['id']) . "
			");
			echo "Aggiorno Stato SDI {$arrDati['id']}/{$sdiIdentificativoDB} a {$sdiStato}\n<br>";
      
		} else {
    
			// È la ricezione di un documento
			
			$arrDati['sdi_fattura'] = base64_decode($arrDati['sdi_fattura_base64']); // la fattura originale arriva codificata base64
			
			$sqlInsertUpdate = "
				sdi_identificativo = '" . $database->escape_string($arrDati['sdi_identificativo']) . "',
				sdi_stato = 'Ricevuto',
				sdi_fattura = '" . $database->escape_string($arrDati['sdi_fattura']) . "',
				sdi_fattura_xml = '" . $database->escape_string($arrDati['sdi_fattura_xml']) . "',
				sdi_data_aggiornamento = '" . $database->escape_string($arrDati['sdi_data_aggiornamento']) . "',
				sdi_messaggio = '" . $database->escape_string($arrDati['sdi_messaggio']) . "',
				sdi_nome_file = '" . $database->escape_string($arrDati['sdi_nome_file']) . "',
				id_fattura_elettronica_api = " . intval($arrDati['id']) . "
			";
			
			// verifichiamo se ce l'abbiamo già
			$res = $database->query("
				SELECT id
				FROM fatture_elettroniche
				WHERE id_fattura_elettronica_api = " . intval($arrDati['id']) . "
			");
			if ($res->num_rows == 0) {
				$database->query("
					INSERT INTO fatture_elettroniche
					SET {$strInsertUpdate}
				");
			} else {
				// aggiornamento
				$database->query("
					UPDATE fatture_elettroniche
					SET {$strInsertUpdate}
					WHERE id_fattura_elettronica_api = " . intval($arrDati['id']) . "
				");
			}
			echo "Inserisco fattura SDI {$arrDati['sdi_identificativo']}\n<br>";
			
		}
		
	}

	echo "Elaborazione termin.: " . date('Y-m-d H:i:s') . "\n<br>";
}

```

### Ricezione semplificata delle fatture
```php
$result = $feac->riceviDocumenti();
foreach ($result as $arrDati) {
	
	$datiMitente = $arrDati['dati_documento']['mittente'];
	/*
	$datiMittente è un array che contiene i campi:
	- PartitaIVA
	- CodiceFiscale
	- Denominazione
	- Indirizzo
	- CAP
	- Comune
	- Provincia
	- Nazione
	*/
	
	$datiDocumento = $arrDati['dati_documento']['documento'];
	/*
	$datiDocumento è un array che contiene i campi:
	- Tipo (FATT|NDC|NDD)
	- Data (formato yyyy-mm-dd)
	- Numero
	- Causale
	- Totale
	*/
	
	$righeDocumento = $arrDati['dati_documento']['righe'];
	/*
	$righeDocumento è un array che contiene più array, ciascuno coi seguenti campi:
	- Descrizione
	- PrezzoUnitario
	- Quantita
	- AliquotaIVA
	*/
}
```
## Guida ai Dati per creare una Fattura
Per un elenco completo dei dati utilizzabili per creare una fattura dai dati ( metodo inviaConDati() ) vedere la guida: https://fattura-elettronica-api.it/guida2.0/#approfondimenti_datifattura
