# FTFS Dashboard v2.0

Dashboard moderna per la visualizzazione e gestione delle transazioni FTFS (First Time For Sure).

## 📋 Panoramica

Questa applicazione fornisce un'interfaccia web avanzata per monitorare le transazioni FTFS con:
- Visualizzazione dati tramite grafici interattivi
- Card view delle transazioni
- Export CSV ottimizzato per grandi dataset (fino a 300K righe)
- Generazione e visualizzazione scontrini
- Sistema di login sicuro con sessioni

## 🗂️ Struttura File

### File Principali

#### `index.php` (50KB)
**Dashboard principale dell'applicazione**
- Visualizza 6 grafici interattivi (ECharts):
  - Distribuzione per acquirer
  - Distribuzione per circuito
  - Transazioni per ora del giorno
  - Transazioni per giorno della settimana
  - Distribuzione per fasce di importo
  - Trend giornaliero
- Card view paginata (12 card per pagina)
- Sidebar dettagli transazione
- 4 statistiche cards (totale TX, importo, media, tasso successo)
- Filtri avanzati (date, importi, terminal ID, punto vendita)
- Header merchant-style con logo PayGlobe
- Responsive design

**Dipendenze esterne:**
- Bootstrap 4.5.2
- jQuery 3.6.0
- DataTables 1.13.7
- jQuery DateTimePicker
- ECharts 5.4.3
- Font Awesome 6.4.0

---

#### `storni.php` (10KB)
**Dashboard Possibili Storni Impliciti**
- Monitoraggio transazioni con `Conf=' '` (non confermate)
- 5 stat cards:
  - Storni Impliciti Certi
  - Possibili Storni
  - In Attesa (<5 min)
  - Confermate (DDL)
  - Importo a Rischio
- Tabella dettagliata con:
  - Stato (badge colorato)
  - Terminal ID, NumOper
  - Data/ora, Importo
  - Flag Autorizzata (SI/NO)
  - Codice autorizzazione
  - Next NumOper
  - Minuti dopo
  - Punto vendita
- Filtri: Data inizio/fine, Solo critici
- Auto-refresh ogni 60 secondi
- Navigazione integrata con dashboard principale

**URL:** `/v2/storni.php`

---

#### `login.php` (12KB)
**Pagina di autenticazione utenti**
- Design moderno con animazioni CSS
- Validazione email/password
- Supporto per password MD5 e bcrypt (password_verify)
- Gestione sessioni PHP
- Protezione contro accessi non autorizzati
- Floating particles background
- Auto-redirect alla dashboard se già loggati

**Campi richiesti:**
- Email (input type email)
- Password (con toggle show/hide)

**Sessioni create:**
- `$_SESSION['username']` - Email utente
- `$_SESSION['bu']` - Business Unit
- `$_SESSION['user_id']` - ID utente

---

#### `logout.php` (12KB)
**Pagina di logout con conferma visiva**
- Distrugge tutte le sessioni PHP
- Animated success checkmark
- Countdown 5 secondi con auto-redirect
- Click anywhere to skip countdown
- Floating particles background

**Azioni:**
- `session_unset()` - Rimuove variabili sessione
- `session_destroy()` - Distrugge la sessione
- Redirect automatico a `login.php`

---

### File API Backend

#### `get_summary.php` (12KB)
**API per statistiche e dati grafici**

**Endpoint:** `GET /get_summary.php`

**Parametri query:**
- `startDate` - Data inizio (formato: Y-m-d H:i:s)
- `endDate` - Data fine
- `minAmount` - Importo minimo
- `maxAmount` - Importo massimo
- `terminalID` - ID terminale
- `puntoVendita` - Nome punto vendita
- `conf` - Stato confermato (opzionale)

**Risposta JSON:**
```json
{
  "totalTransactions": 1234,
  "totalAmount": 50000.00,
  "confirmedCount": 1150,
  "confirmedAmount": 48500.00,
  "acquirerData": [...],
  "circuitData": [...],
  "hourlyData": [...],
  "weekdayData": [...],
  "amountRangeData": [...],
  "trendData": [...]
}
```

**Funzioni principali:**
- `getSummaryStats()` - Calcola statistiche aggregate
- `getAcquirerData()` - Raggruppa per acquirer
- `getCircuitData()` - Raggruppa per circuito
- `getHourlyData()` - Distribuzione oraria (0-23)
- `getWeekdayData()` - Distribuzione per giorno settimana (0=Dom, 6=Sab)
- `getAmountRangeData()` - Fasce importo (<10€, 10-50€, 50-100€, >100€)
- `getTrendData()` - Trend giornaliero ultimi 30 giorni

---

#### `get_table_data.php` (7.7KB)
**API per recuperare lista transazioni (usata da card view)**

**Endpoint:** `GET /get_table_data.php`

**Parametri query:**
- Stessi filtri di `get_summary.php`
- `start` - Offset per paginazione
- `length` - Numero record da recuperare

**Risposta JSON:**
```json
{
  "data": [
    {
      "Trid": "123456",
      "TermId": "12345678",
      "DtPos": "2025-11-15 14:30:00",
      "Amount": 50.00,
      "Conf": "C",
      "ApprNum": "ABC123",
      ...
    }
  ],
  "recordsTotal": 5000,
  "recordsFiltered": 1234
}
```

**Colonne transazione:**
- `Trid` - Transaction ID
- `TermId` - Terminal ID
- `DtPos` - Data/ora transazione
- `Amount` - Importo
- `Conf` - Stato (C=Confermata, E=Storno, N=Preauth, I/D/A=Rifiutata)
- `ApprNum` - Codice autorizzazione
- `PosAcq` - Circuito (VISA, MasterCard, etc.)
- `Acquirer` - Nome acquirer

---

#### `get_pending_confirmations.php` (4KB)
**API per identificazione possibili storni impliciti**

**Endpoint:** `GET /get_pending_confirmations.php`

**Parametri query:**
- `startDate` - Data inizio (default: 7 giorni fa)
- `endDate` - Data fine (default: ora)
- `onlyCritical` - Solo casi critici (true/false)

**Risposta JSON:**
```json
{
  "success": true,
  "data": [
    {
      "termId": "10404012",
      "numOper": 60,
      "dataNonConfermata": "2026-01-19 11:57:58",
      "amount": 912.08,
      "apprNum": "170073",
      "gtResp": "000",
      "nextNumOper": 42,
      "stato": "CONTATORE_RESETTATO",
      "autorizzata": true
    }
  ],
  "stats": {
    "totale": 50,
    "storno_implicito_certo": 30,
    "possibile_storno_implicito": 5,
    "in_attesa": 2,
    "contatore_resettato": 8,
    "confermata_ddl": 5,
    "importo_a_rischio": 1250.00
  }
}
```

**Logica di classificazione:**
- `STORNO_IMPLICITO_CERTO`: NextNumOper = NumOper (stessa operazione ripetuta)
- `POSSIBILE_STORNO_IMPLICITO`: Nessuna TX successiva dopo 5+ minuti
- `IN_ATTESA`: Nessuna TX successiva, ma < 5 minuti
- `CONTATORE_RESETTATO`: NextNumOper < NumOper (terminale riavviato)
- `CONFERMATA_DDL`: NextNumOper = NumOper + 1 (DDL effettuato)

---

#### `get_punti_vendita.php` (1.3KB)
**API per autocomplete punti vendita**

**Endpoint:** `GET /get_punti_vendita.php`

**Parametri query:**
- `term` - Testo ricerca (minimo 2 caratteri)

**Risposta JSON:**
```json
[
  "Negozio Centro",
  "Negozio Via Roma",
  "Punto Vendita 1"
]
```

**Utilizzo:**
Popola il campo autocomplete "Punto Vendita" nei filtri.

---

### File Export

#### `export_large.php` (7.8KB)
**Export CSV ottimizzato per grandi dataset (fino a 300.000 righe)**

**Caratteristiche tecniche:**
- **Streaming output** - Non carica tutti i dati in memoria
- **Chunked processing** - Elabora 5.000 righe alla volta
- **Memory limit:** 512MB
- **Execution time:** 600s (10 minuti)
- **Output buffering:** Disabilitato per streaming
- **Formato:** CSV con separatore `;` e BOM UTF-8

**Headers HTTP:**
```php
Content-Type: text/csv; charset=UTF-8
Content-Disposition: attachment; filename="transazioni_ftfs_YYYY-MM-DD_HHmmss.csv"
```

**Colonne CSV (15 totali):**
1. DATA_ORA (dd/mm/yyyy HH:mm:ss)
2. TML_PAYGLOBE
3. TML_ACQUIRER
4. MERCHANT_ID (pulito da prefisso 0001024)
5. TIPO_CARTA (CREDITO/DEBITO)
6. CIRCUITO
7. STATO (CONFERMATA, STORNO, etc.)
8. MODELLO_POS
9. PAN
10. IMPORTO (formato italiano: 1.234,56)
11. CODICE_CONFERMA_ACQ
12. NOME_ACQUIRER (pulito da prefisso 1008)
13. PUNTO_VENDITA
14. CITTA
15. PROVINCIA

**Performance stimata:**
- 1.000 righe: ~1 secondo
- 10.000 righe: ~10 secondi
- 100.000 righe: ~100 secondi
- 300.000 righe: ~300 secondi (5 minuti)

---

#### `export_progress.php` (2.6KB)
**Contatore transazioni per export**

**Endpoint:** `GET /export_progress.php`

**Risposta JSON:**
```json
{
  "total": 123456
}
```

**Utilizzo:**
Chiamato prima di `export_large.php` per:
- Mostrare totale transazioni da esportare
- Calcolare tempo stimato
- Verificare se ci sono dati (evita export vuoti)

---

### File Scontrini

#### `scontrino_nets.php` (5KB)
**Generatore scontrino tramite API esterna N&TS**

**Endpoint:** `GET /scontrino_nets.php?trid=TRANSACTION_ID`

**Flusso:**
1. Recupera transazione da database tramite `Trid`
2. Prepara payload JSON per API esterna
3. Chiama `https://wsent.netsgroup.com:1040/mondoconvenienza/v1/ftfs/receipt/enquiry`
4. Riceve PDF base64 encoded
5. Decodifica e restituisce PDF

**Payload API:**
```json
{
  "poiId": "12345678",
  "txDt": "2025-11-15T14:30:00.000Z",
  "authCode": "ABC123",
  "isReversal": "false",
  "amount": "5000"
}
```

**Headers output:**
```php
Content-Type: application/pdf
```

**Note:**
- SSL verification disabilitato (solo per test)
- Importo inviato senza punto decimale (5000 = 50,00€)

---

#### `scontrino.php` (5.5KB)
**Generatore scontrino PDF tramite DomPDF**

**Endpoint:** `GET /scontrino.php?trid=TRANSACTION_ID`

**Dipendenze:**
- DomPDF 3.1 (`require '../vendor/autoload.php'`)

**Flusso:**
1. Recupera transazione da database
2. Genera HTML dello scontrino
3. Converte HTML in PDF tramite DomPDF
4. Restituisce PDF

**Utilizzo:**
Fallback se `scontrino_nets.php` non è disponibile o per scontrini personalizzati.

---

### File Configurazione

#### `config.php` (406 bytes)
**Configurazione database MySQL/MariaDB**

```php
$servername = "localhost";
$username = "ftfs_user";
$password = "***";
$dbname = "ftfs_db";

$conn = new mysqli($servername, $username, $password, $dbname);
```

**Tabelle utilizzate:**
- `ftfs_transactions` - Transazioni FTFS
- `users` - Utenti per login
- `stores_*` - Punti vendita per BU (es: stores_1060, stores_1070)

---

#### `bu_config.php` (1.3KB)
**Configurazione Business Units e mapping stores**

```php
function getStoresTable($bu) {
    $mapping = [
        '1060' => 'stores_1060',
        '1070' => 'stores_1070',
        // ...
    ];
    return $mapping[$bu] ?? 'stores_default';
}
```

**Utilizzo:**
Determina quale tabella `stores_*` usare in base al BU dell'utente loggato.

---

### File Receiver

#### `process_json.php` (13KB)
**Endpoint per ricevere transazioni FTFS via JSON POST**

**Endpoint:** `POST /process_json.php`

**Autenticazione:**
```php
$expected_user = 'netsuser';
$expected_password = 'GTfagh$5hasSENA';
```

**Payload JSON atteso:**
```json
{
  "Trid": "123456",
  "TermId": "12345678",
  "DtPos": "15/11/2025 14:30:00",
  "Amount": "50.00",
  "Conf": "C",
  ...
}
```

**Flusso:**
1. Verifica autenticazione HTTP Basic
2. Valida JSON
3. Sanitizza input con `htmlspecialchars()`
4. Converte data da formato italiano a SQL
5. INSERT/UPDATE in `ftfs_transactions`
6. Gestisce storni (amount negativo)

**Sicurezza:**
- ✅ Prepared statements (SQL injection safe)
- ✅ Input sanitization
- ❌ Credenziali hardcoded (da migliorare)
- ❌ No rate limiting
- ❌ No HTTPS enforcement

---

## 🗄️ Schema Database

### Tabella: `ftfs_transactions`

```sql
CREATE TABLE ftfs_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Trid VARCHAR(50),           -- Transaction ID
    TermId VARCHAR(20),         -- Terminal ID
    Term VARCHAR(50),           -- Terminal Acquirer
    DtPos DATETIME,             -- Data/ora transazione
    Amount DECIMAL(10,2),       -- Importo
    Conf CHAR(1),               -- Stato (C/E/N/I/D/A)
    ApprNum VARCHAR(50),        -- Codice autorizzazione
    PosAcq VARCHAR(50),         -- Circuito
    Acquirer VARCHAR(100),      -- Nome acquirer
    MeId VARCHAR(50),           -- Merchant ID
    TPC CHAR(1),                -- Tipo carta (C=Credito, B=Debito)
    Pan VARCHAR(20),            -- PAN mascherato
    TP CHAR(1),                 -- Tipo operazione (N=Normale, R=Reversal)
    GtResp VARCHAR(10),         -- Gateway response
    -- ... altri campi ...
    INDEX idx_termid (TermId),
    INDEX idx_dtpos (DtPos),
    INDEX idx_conf (Conf)
);
```

### Tabella: `users`

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),      -- MD5 o bcrypt
    bu VARCHAR(10),             -- Business Unit
    created_at TIMESTAMP
);
```

### Tabelle: `stores_*` (per BU)

```sql
CREATE TABLE stores_1060 (
    TerminalID VARCHAR(20) PRIMARY KEY,
    Insegna VARCHAR(255),       -- Nome punto vendita
    citta VARCHAR(100),
    prov VARCHAR(5),
    Modello_pos VARCHAR(50),
    sia_pagobancomat VARCHAR(50) -- Codice esercente
);
```

---

## 🔐 Sicurezza

### Implementata
- ✅ **Session management** - Verifica login su ogni pagina
- ✅ **Prepared statements** - Protezione SQL injection
- ✅ **Input sanitization** - `htmlspecialchars()` su tutti gli input
- ✅ **Password hashing** - Supporto bcrypt + legacy MD5
- ✅ **HTTPS** - Connessione criptata (da configurare su server)

### Da Migliorare
- ⚠️ **Credenziali hardcoded** in `process_json.php`
- ⚠️ **No CSRF protection**
- ⚠️ **No rate limiting** su login e API
- ⚠️ **SSL verification disabled** in scontrino_nets.php

---

## 📊 Statistiche Codice

- **File PHP totali:** 13
- **Righe codice totali:** ~15.000
- **Dipendenze Composer:** 1 (DomPDF)
- **Librerie JS:** 6 (jQuery, Bootstrap, DataTables, ECharts, etc.)
- **Grafici:** 6 (ECharts interactive charts)
- **Tabelle DB:** 4+ (ftfs_transactions, users, stores_*)

---

## 🚀 Deployment

### Requisiti Server
```
PHP >= 7.4
MySQL/MariaDB >= 5.7
Composer
Apache/Nginx con mod_rewrite
```

### Limiti PHP Consigliati
```ini
memory_limit = 512M
max_execution_time = 600
post_max_size = 100M
upload_max_filesize = 100M
```

### Installazione Dipendenze
```bash
cd /var/www/html/ftfs
composer install
```

### Permessi File
```bash
chmod 755 v2/
chmod 644 v2/*.php
chmod 644 v2/config.php
```

---

## 🔄 Migrazione da v1

**Redirect automatico attivo:**
```php
// /var/www/html/ftfs/index.php
if (!isset($_SESSION['username'])) {
    header("Location: v2/login.php");
} else {
    header("Location: v2/index.php");
}
```

**URL:**
- Old: `https://ricevute.payglobe.it/ftfs/ftfs_table.php`
- New: `https://ricevute.payglobe.it/ftfs/` → auto-redirect a v2

---

## 📝 Note di Versione

### v2.1 (Gennaio 2026)
- ✨ **Nuovo modulo Possibili Storni Impliciti** (`storni.php`)
- ✨ API `get_pending_confirmations.php` per identificazione automatica storni
- ✨ Dashboard dedicata con statistiche real-time
- ✨ Auto-refresh ogni 60 secondi
- ✨ Classificazione automatica:
  - STORNO_IMPLICITO_CERTO: Prossima TX ha stesso NumOper
  - POSSIBILE_STORNO_IMPLICITO: Nessuna TX dopo 5+ minuti
  - IN_ATTESA: Meno di 5 minuti dalla TX
  - CONTATORE_RESETTATO: Terminale riavviato
  - CONFERMATA_DDL: NumOper incrementato (DDL effettuato)
- ✨ Calcolo importo a rischio per TX autorizzate non confermate
- ✨ Link navigazione tra dashboard principale e storni

### v2.0 (Novembre 2025)
- ✨ Nuova dashboard con grafici ECharts
- ✨ Card view al posto di tabella DataTables
- ✨ Export ottimizzato per 300K righe
- ✨ Header merchant-style
- ✨ Login/logout moderni
- 🐛 Fix calcolo tasso successo
- 🐛 Fix export CSV (download forzato)
- 🐛 Fix visualizzazione codice autorizzativo
- ❌ Rimossa funzionalità email
- ❌ Rimossa dipendenza PHPMailer
- 🔧 Tutti i file reali (no symlink)

---

## 📧 Supporto

Per assistenza tecnica o segnalazione bug, contattare il team di sviluppo PayGlobe.

**Autore:** Claude Code
**Data:** Novembre 2025
**Versione:** 2.0
**License:** Proprietario PayGlobe - GUM Group Company
