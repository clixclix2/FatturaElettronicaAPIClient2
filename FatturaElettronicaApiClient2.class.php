<?php
/**
 * Libreria Client PHP per utilizzare il servizio Fattura Elettronica API v.2.0 - https://fattura-elettronica-api.it
 * Guida: https://fattura-elettronica-api.it/guida2.0/
 * @author Itala Tecnologia Informatica S.r.l. - www.itala.it
 * @version 2.0
 * @since 2023-12-30
 */

class FatturaElettronicaApiClient2
{
    /**
     * Indicare credenziali utente fornite da Fattura-Elettronica API, oppure il Token di qutenticazione se già disponibile
     * @param string $username
     * @param string $password
     * @param string $authToken
     * @param bool $testMode
     * @throws Exception
     */
    function __construct($username = NULL, $password = NULL, $authToken = NULL, $testMode = false)
    {
        if ($username === NULL && $password === NULL && $authToken === NULL) {
            throw new Exception('Either username and password, of authToken must be provided.');
        }

        $this->username = $username;
        $this->password = $password;

        if ($authToken) {
            $this->authToken = $authToken;
        }

        $this->testMode = $testMode;
        $this->endpoint = $this->endpoints[$testMode ? 'test' : 'prod'];
    }


    /**
     * Invia un documento (fattura, nota di credito, nota di debito, etc.) al SdI, trmamite Fattura Elettronica API
     * Il sistema (FatturaElettronicaAPI) aggiungerà o modificherà la sezione relativa ai dati di trasmissione (sezione FatturaElettronicaHeader/DatiTrasmissione dell'XML)
     * @param string $xml Documento XML, charset UTF-8
     * @return null|array (id, sdi_identificativo, sdi_nome_file, sdi_fattura, sdi_stato, sdi_messaggio)
     */
    function invia($xml)
    {
        $ret = $this->call('post', '/fatture', $xml);
        if ($ret) {
            return json_decode($ret, true);
        }
        return NULL;
    }

    /**
     * Invia un documento al SdI tramite Fattura Elettronica API, indicando i dati del documento
     * Questo metodo può gestire le casistiche di fatturazine più comuni. Per casistiche più complesse, è necessario generare l'XML completo ed utilizzare il metodo invia()
     * Per utilizzare questo metodo, è necessario aver inserito i propri dati aziendali completi nel pannello di controllo fattura-elettronica-api.it, nella sezione "Dati per generazione automatica fatture", oppure tramite API /aziende
     * In caso di esito positivo, la fattura elettronica finale (quella effettivamente trasmessa al SDI) viene ritornata nel campo 'sdi_fattura'
     * @param array $datiDestinatario PartitaIVA (opz.), CodiceFiscale (opz.), PEC (opz.), CodiceSDI (opz.), Denominazione, Indirizzo, CAP, Comune, Provincia (opz.), Nazione (opz., codice a 2 lettere)
     * @param array $datiDocumento tipo=FATT,NDC,NDD (opz. - default 'FATT'), Data, Numero, Causale (opz.)
     * @param array $righeDocumento Ogni riga è un array coi campi: Descrizione, PrezzoUnitario, Quantita (opz.), AliquotaIVA (opz. - default 22)
     * @param string $partitaIvaMittente In caso di account multi-azienda, specificare la partita iva del Cedente
     * @return null|array
     */
    function inviaConDati($datiDestinatario, $datiDocumento, $righeDocumento, $partitaIvaMittente = null)
    {
        $data = array(
            'destinatario' => $datiDestinatario,
            'documento' => $datiDocumento,
            'righe' => $righeDocumento,
            'piva_mittente' => $partitaIvaMittente
        );
        $ret = $this->call('post', '/fatture', $data);
        if ($ret) {
            return json_decode($ret, true);
        }
        return NULL;
    }


    /**
     * Ritorna gli eventuali nuovi documenti ricevuti
     * Vedere guida online per i campi ritornati (https://fattura-elettronica-api.it/guida2.0/#ricezione)
     * Una volta ricevuto un documento, questo non viene più trasmesso alle successive invocazioni del metodo riceviDocumenti(), salvo andando sul pannello di controllo e reimpostando la spunta "Da leggere"
     * Paginazione: verificare hasMoreResults() e getNextResults()
     * @param array $opzioni array associativo - vedere guida
     * @return null|array array di array coi campi: partita_iva, ricezione, sdi_identificativo, sdi_messaggio, sdi_nome_file, sdi_fattura, sdi_fattura_xml, sdi_data_aggiornamento, sdi_stato, dati_mittente, dati_documento, righe_documento
     */
    function riceviDocumenti($opzioni = [])
    {
        $callOptions = [
            'unread' => true,
            'solo_ricezioni' => true
        ];

        $callOptions = array_merge($callOptions, $opzioni);

        $ret = $this->call('get', '/fatture', $callOptions);
        if ($ret) {
            return json_decode($ret, true);
        }
        return NULL;
    }

    /**
     * Ritorna gli eventuali nuovi aggiornamenti sui documenti inviati
     * Vedere guida online per i campi ritornati (https://fattura-elettronica-api.it/guida2.0/#ricezione)
     * Una volta ricevuto un aggiornamento, questo non viene più trasmesso alle successive invocazioni del metodo riceviAggiornamenti(), salvo andando sul pannello di controllo e reimpostando la spunta "Da leggere"
     * Paginazione: verificare hasMoreResults() e getNextResults()
     * @param array $opzioni array associativo - vedere guida
     * @return null|array array di array coi campi: partita_iva, ricezione, sdi_identificativo, sdi_messaggio, sdi_nome_file, sdi_fattura, sdi_fattura_xml, sdi_data_aggiornamento, sdi_stato, dati_mittente, dati_documento, righe_documento
     */
    function riceviAggiornamenti($opzioni = [])
    {
        $callOptions = [
            'unread' => true,
            'solo_trasmissioni' => true
        ];

        $callOptions = array_merge($callOptions, $opzioni);

        $ret = $this->call('get', '/fatture', $callOptions);
        if ($ret) {
            return json_decode($ret, true);
        }
        return NULL;
    }


    /**
     * Ottiene la rappresentazione PDF di un documento ricevuto
     * @param int $idDocumento - ID FatturaElettronicaAPI
     * @return null|string doccumento PDF in formato binario
     */
    function ottieniPDF($idDocumento)
    {
        return $this->call('get', '/fatture/' . $idDocumento . '/pdf');
    }


    /**
     * Ottiene gli eventuali file allegati ad una fattura ricevuta
     * @param int $idDocumento - ID FatturaElettronicaAPI
     * @return null|array array di array(nome_file, descrizione, file_base64)
     */
    function ottieniAllegati($idDocumento)
    {
        $ret = $this->call('get', '/fatture/' . $idDocumento . '/allegati');
        if ($ret) {
            return json_decode($ret, true);
        }
        return NULL;
    }

    /**
     * Estrae l'elenco delle aziende abilitate all'invio/ricezione
     * Paginazione: verificare hasMoreResults() e getNextResults()
     * @return null|array array di array(id, nome, ragione_sociale, piva, cfis, ...) - vedere documentazione
     */
    function elencoAziende()
    {
        $ret = $this->call('get', '/aziende');
        if ($ret) {
            return json_decode($ret, true);
        }
        return NULL;
    }

    /**
     * @param int $idAzienda
     * @return null|array dati azienda
     */
    function datiAzienda($idAzienda)
    {
        $ret = $this->call('get', '/aziende/' . $idAzienda);
        if ($ret) {
            return json_decode($ret, true);
        }
        return NULL;
    }

    /**
     * Aggiunge un'azienda alla lista delle proprie aziende abilitate (se si dispone dei permessi)
     * @param array $arrCampi array('ragione_sociale' => 'ragione sociale', 'piva' => 'partita iva', 'cfis' => 'codice fiscale') - charset utf8
     * @return null|array dati azienda
     */
    function aggiungiAzienda($arrCampi)
    {
        $ret = $this->call('post', '/aziende', $arrCampi);
        if ($ret) {
            return json_decode($ret, true);
        }
        return NULL;
    }


    /**
     * Aggiorna un'azienda
     * @param int $idAzienda
     * @param array $arrCampi array('ragione_sociale' => 'ragione sociale', 'piva' => 'partita iva', 'cfis' => 'codice fiscale') - charset utf8
     * @return null|array dati azienda
     */
    function aggiornaAzienda($idAzienda, $arrCampi)
    {
        $ret = $this->call('put', '/aziende/' . $idAzienda, $arrCampi);
        if ($ret) {
            return json_decode($ret, true);
        }
        return NULL;
    }


    /**
     * Elimina un'azienda dalla lista delle proprie aziende abilitate
     * @param int $idAzienda
     * @return null|array dati azienda cancellata
     */
    function rimuoviAzienda($idAzienda)
    {
        $ret = $this->call('delete', '/aziende/' . $idAzienda);
        if ($ret) {
            return json_decode($ret, true);
        }
        return NULL;
    }



    /**
     * In caso di errore della chiamata (risposta NULL) qui abbiamo l'eventuale messaggio di errore
     * @return string
     */
    function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Ritorna l'ultimo codice HTTP ricevuto dal server
     * @return string
     */
    function getLastCode()
    {
        return $this->lastCode;
    }

    /**
     * @param string $method get|post|put|delete|patch
     * @param string $path (se inizia con 'http' richiama in get la url così com'è)
     * @param array|string|null $data
     * @return string|null
     */
    protected function call($method, $path, $data = NULL)
    {
        $methodUp = strtoupper($method);

        $httpHeaders = [];

        if ($this->authToken && (!$this->authExpires || $this->authExpires > (new DateTime())->add(new DateInterval('PT5M'))->format('Y-m-d H:i:s'))) {
            // token valido
            $httpHeaders[] = 'Authorization: Bearer ' . $this->authToken;
        } else {
            $httpHeaders[] = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
        }

        $curlOpts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $methodUp
        ];


        if ($methodUp === 'GET' && substr($path, 0, 4) === 'http') { // risultati successivi

            $callUrl = $path;

        } else { // caso normale

            if ($methodUp === 'GET') {
                if ($data === NULL) {
                    $data = [];
                }
                if (!isset($data['per_page'])) {
                    $data['per_page'] = 1000; // massimo consentito
                }
            }

            $callUrl = $this->endpoint . $path;

            if ($data !== NULL) {
                if (in_array($methodUp, ['POST', 'PUT', 'PATCH'])) {
                    if (is_string($data)) {
                        $curlOpts[CURLOPT_POSTFIELDS] = $data;
                        $httpHeaders[] = 'content-type: application/xml';
                    } else {
                        $curlOpts[CURLOPT_POSTFIELDS] = json_encode($data);
                        $httpHeaders[] = 'content-type: application/json';
                    }
                } else { // 'GET', 'DELETE'
                    $joinChar = '?';
                    foreach ($data as $key => $val) {
                        $callUrl .= $joinChar . $key . '=' . urlencode($val);
                        $joinChar = '&';
                    }
                }
            }

        }


        $curlOpts[CURLOPT_URL] = $callUrl;
        $curlOpts[CURLOPT_HTTPHEADER] = $httpHeaders;

        $responseHeaders = [];

        $curlOpts[CURLOPT_HEADERFUNCTION] = function ($curl, $header) use (&$responseHeaders) {
            $len = strlen($header);
            $arrHeader = explode(':', $header, 2);
            if (count($arrHeader) >= 2) { // ignore invalid headers
                $responseHeaders[trim($arrHeader[0])] = trim($arrHeader[1]);
            }
            return $len;
        };

        $curl = curl_init();

        curl_setopt_array($curl, $curlOpts);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        $this->lastCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if (isset($responseHeaders['X-auth-token'])) {
            $this->authToken = $responseHeaders['X-auth-token'];
            $this->authExpires = $responseHeaders['X-auth-expires'];
        }

        $this->lastGetHasNextUrl = NULL;
        if ($methodUp === 'GET' && isset($responseHeaders['Link'])) {
            if (preg_match('#^<([^>]+)>; rel="next"#', $responseHeaders['Link'], $matches)) {
                $this->lastGetHasNextUrl = $matches[1];
            }
        }

        if ($err) {
            $this->lastError = $err;
            return NULL;
        } else {
            $this->lastError = '';
            return $response;
        }
    }

    public function hasMoreResults()
    {
        return $this->lastGetHasNextUrl !== NULL;
    }
    public function getNextResults()
    {
        return $this->call('get', $this->lastGetHasNextUrl);
    }

    private $lastGetHasNextUrl = NULL;


    private $endpoints = [
        'test' => 'https://fattura-elettronica-api.it/ws2.0/test',
        'prod' => 'https://fattura-elettronica-api.it/ws2.0/prod'
    ];

    private $lastCode = '';
    private $lastError = '';

    private $testMode = false;
    private $endpoint = '';
    private $username = NULL;
    private $password = NULL;

    private $authToken = NULL;
    private $authExpires = NULL;
    private $authError = '';
}
