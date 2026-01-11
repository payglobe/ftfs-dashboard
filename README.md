# FTFS Dashboard v2.0

**Fault Tolerant Financial System - Dashboard Transazioni**

Dashboard web per la visualizzazione e gestione delle transazioni POS in tempo reale, con integrazione API N&TS (Nets Group) per generazione scontrini PDF.

## Funzionalita Principali

- **Dashboard Interattiva**: Visualizzazione transazioni con card view paginata
- **6 Grafici ECharts**: Acquirer, circuito, orario, giorno settimana, importo, trend
- **Filtri Avanzati**: Date, importi, terminal ID, punto vendita, stato transazione
- **Statistiche Real-time**: Totale TX, confermate, rifiutate, tasso successo
- **Export CSV**: Streaming ottimizzato per grandi dataset
- **Scontrini PDF**: Generazione via API N&TS esterna
- **Multi Business Unit**: Filtro automatico per BU utente

---

## Struttura Progetto

```
ftfs/
├── v2/                          # Dashboard v2.0 (attiva)
│   ├── index.php                # Dashboard principale con grafici e card view
│   ├── config.php               # Configurazione database MySQL
│   ├── bu_config.php            # Configurazione Business Unit
│   ├── login.php                # Pagina login
│   ├── logout.php               # Logout e pulizia sessione
│   ├── forgot_password.php      # Recupero password
│   ├── reset_password.php       # Reset password
│   │
│   ├── get_table_data.php       # API: Lista transazioni (paginata)
│   ├── get_summary.php          # API: Statistiche e dati grafici
│   ├── get_punti_vendita.php    # API: Autocomplete punti vendita
│   │
│   ├── scontrino_nets.php       # API: Scontrino PDF via N&TS
│   ├── scontrino.php            # Scontrino PDF locale (DomPDF) - legacy
│   │
│   ├── export_large.php         # Export CSV streaming
│   ├── export_progress.php      # Conteggio per export
│   ├── export_async_*.php       # Export asincrono (worker)
│   │
│   └── process_json.php         # Importazione transazioni JSON
│
├── schema.sql                   # Schema database completo
├── config.php                   # Config root (redirect)
├── composer.json                # Dipendenze PHP
└── README.md                    # Questo file
```

---

## Database Schema

### Tabella: `ftfs_transactions`
Transazioni POS principale.

```sql
CREATE TABLE `ftfs_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Trid` bigint(20) DEFAULT NULL,           -- Transaction ID univoco
  `TermId` varchar(255) DEFAULT NULL,       -- Terminal ID (8 cifre)
  `Term` varchar(8) DEFAULT NULL,           -- Codice terminale
  `SiaCode` varchar(255) DEFAULT NULL,      -- Codice SIA
  `DtTrans` datetime DEFAULT NULL,          -- Data transazione
  `ApprNum` varchar(255) DEFAULT NULL,      -- Codice autorizzazione (6 cifre)
  `Acid` varchar(255) DEFAULT NULL,         -- Acquirer ID
  `Acquirer` varchar(255) DEFAULT NULL,     -- Nome acquirer (Nexi, Amex, etc.)
  `Pan` varchar(255) DEFAULT NULL,          -- PAN mascherato (****1234)
  `Amount` decimal(10,2) DEFAULT NULL,      -- Importo EUR
  `Currency` varchar(255) DEFAULT NULL,     -- Valuta (EUR)
  `DtIns` varchar(255) DEFAULT NULL,        -- Data inserimento
  `PointOfService` varchar(255) DEFAULT NULL,
  `Cont` varchar(255) DEFAULT NULL,         -- Contabilizzata (Y/N)
  `NumOper` int(11) DEFAULT NULL,           -- Numero operazione
  `DtPos` datetime DEFAULT NULL,            -- Data/ora POS
  `PosReq` varchar(255) DEFAULT NULL,
  `PosStan` int(11) DEFAULT NULL,           -- STAN POS
  `PfCode` varchar(255) DEFAULT NULL,
  `PMrc` varchar(255) DEFAULT NULL,
  `PosAcq` varchar(255) DEFAULT NULL,       -- Circuito (VISA, MC, AMEX, etc.)
  `GtResp` varchar(255) DEFAULT NULL,       -- Codice risposta gateway
  `NumTent` int(11) DEFAULT NULL,           -- Numero tentativi
  `TP` varchar(1) DEFAULT NULL,             -- Tipo (P=Pagamento, R=Reversal)
  `CatMer` varchar(255) DEFAULT NULL,       -- Categoria merchant
  `VndId` varchar(255) DEFAULT NULL,
  `PvdId` int(11) DEFAULT NULL,
  `Bin` varchar(255) DEFAULT NULL,          -- BIN carta
  `Tpc` varchar(255) DEFAULT NULL,          -- Tipo carta (C=Credito, D=Debito)
  `VaFl` varchar(255) DEFAULT NULL,
  `FvFl` varchar(255) DEFAULT NULL,         -- Flag valenza (1=ok, 0=stornata, 9=doppia)
  `TrKey` varchar(255) DEFAULT NULL,
  `CSeq` bigint(20) DEFAULT NULL,
  `Conf` varchar(255) DEFAULT NULL,         -- Stato conferma (vedi sotto)
  `AutTime` int(11) DEFAULT NULL,
  `DBTime` int(11) DEFAULT NULL,
  `TOTTime` int(11) DEFAULT NULL,
  `MeId` varchar(45) DEFAULT NULL,          -- Merchant ID
  `OperExpl` varchar(65) DEFAULT NULL,      -- Descrizione operazione
  -- ... altri campi EMV ...
  PRIMARY KEY (`id`),
  KEY `idx_ftfs_transactions_TermId` (`TermId`),
  KEY `idx_ftfs_transactions_DtPos` (`DtPos`),
  KEY `idx_ftfs_transactions_Trid` (`Trid`),
  KEY `idx_ftfs_transactions_Conf` (`Conf`),
  KEY `idx_ftfs_transactions_GtResp` (`GtResp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabella: `stores`
Anagrafica punti vendita.

```sql
CREATE TABLE `stores` (
  `TerminalID` varchar(45) NOT NULL,        -- Terminal ID (PK)
  `Ragione_Sociale` varchar(45) DEFAULT NULL,
  `Insegna` varchar(55) DEFAULT NULL,       -- Nome punto vendita
  `indirizzo` varchar(95) DEFAULT NULL,
  `citta` varchar(45) DEFAULT NULL,
  `cap` varchar(45) DEFAULT NULL,
  `prov` varchar(45) DEFAULT NULL,          -- Provincia (2 char)
  `sia_pagobancomat` varchar(45) DEFAULT NULL,
  `six` varchar(45) DEFAULT NULL,
  `amex` varchar(45) DEFAULT NULL,
  `Modello_pos` varchar(45) DEFAULT NULL,   -- Modello terminale
  `country` varchar(2) DEFAULT 'IT',
  `bu` varchar(8) DEFAULT NULL,             -- Business Unit primaria
  `bu1` varchar(8) DEFAULT NULL,            -- Business Unit secondaria
  `bu2` varchar(8) DEFAULT NULL,
  PRIMARY KEY (`TerminalID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```

### Tabella: `users`
Utenti dashboard.

```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,         -- Hash bcrypt
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_token_expiry` datetime DEFAULT NULL,
  `password_last_changed` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `bu` varchar(45) DEFAULT NULL,            -- Business Unit utente (REGEXP)
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `force_password_change` tinyint(1) NOT NULL DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  `application` enum('FTFS','MERCHANT') NOT NULL DEFAULT 'FTFS',
  `schema_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```

---

## Stati Transazione (Campo `Conf`)

| Valore | Descrizione | Colore UI |
|--------|-------------|-----------|
| `C` o ` ` o `` | Confermata | Verde |
| `E` | Storno Esplicito | Rosso |
| `I` | Storno Implicito | Arancione |
| `A` | Stornata Implicitamente | Arancione |
| `D` | Storno per stesso NumOper | Rosso |
| `N` | PreAuth confermata | Verde |

### Storno Implicito
Si verifica quando `NumOper` di un `TermId` e' duplicato. Il sistema invalida la prima transazione.

**Logica JavaScript:**
```javascript
function isStornoImplicito(tx) {
    // Solo se GtResp='000' (era approvata) ma poi invalidata
    if (tx.GtResp && tx.GtResp !== '000') {
        return false; // E' un rifiuto reale
    }
    return tx.Conf === 'I' || tx.Conf === 'A' || tx.OperExpl === 'Storno implicito';
}
```

**ATTENZIONE:** `Cont='Y'/'N'` indica solo se contabilizzata, NON se storno implicito!

---

## Codici Risposta Gateway (`GtResp`)

| Codice | Descrizione |
|--------|-------------|
| `000` | Approvata |
| `100` | Rifiutata |
| `101` | Carta scaduta |
| `102` | Sospetta frode |
| `104` | Carta ritirata |
| `106` | Tentativi PIN superati |
| `109` | Merchant non valido |
| `110` | Importo non valido |
| `111` | Carta non valida |
| `116` | Fondi insufficienti |
| `117` | PIN errato |
| `119` | Transazione non permessa |
| `121` | Limite prelievo superato |
| `125` | Carta non attiva |
| `129` | CVV non valido |
| `180` | Transazione duplicata |
| `181` | Timeout |
| `909` | Errore sistema |
| `910` | Errore emittente |

---

## Integrazione API N&TS (Scontrino PDF)

### Endpoint
```
POST https://wsent.netsgroup.com:1040/mondoconvenienza/v1/ftfs/receipt/enquiry
Content-Type: application/json
```

### Request (Input)

```json
{
    "poiId": "10401866",                    // TermId (8 cifre) - OBBLIGATORIO
    "txDt": "2025-11-15T14:30:00.000Z",     // Data ISO 8601 - OBBLIGATORIO
    "authCode": "ABC123",                   // ApprNum (6 cifre) - OBBLIGATORIO
    "isReversal": "N",                      // Y=Storno, N=Normale
    "amount": "5000"                        // Importo centesimi (50.00 EUR = 5000) - OBBLIGATORIO
}
```

**Mapping campi DB -> API:**
| Campo DB | Campo API | Note |
|----------|-----------|------|
| `TermId` | `poiId` | 8 cifre |
| `DtPos` | `txDt` | Convertire in ISO 8601 |
| `ApprNum` | `authCode` | Se vuoto = TX rifiutata, no scontrino |
| `Amount` | `amount` | Rimuovere decimali (50.00 -> 5000) |
| `TP` | `isReversal` | R -> "Y", altrimenti "N" |

### Response (Output)

**Successo (resultCode=0):**
```json
{
    "resultCode": "0",
    "resultMessage": "SUCCESS",
    "resultReceipt": "JVBERi0xLjQKJeLjz9MK..."  // PDF base64 encoded
}
```

**Errore:**
```json
{
    "resultCode": "3",
    "resultMessage": "Transaction not found"
}
```

### Codici Risposta API N&TS

| resultCode | Descrizione |
|------------|-------------|
| `0` | Success - PDF in `resultReceipt` (base64) |
| `1` | Messaggio JSON non valido |
| `2` | Lunghezza JSON non valida |
| `3` | Transazione non trovata |
| `4` | Transazione non valida (storno implicito o non contabilizzata) |
| `5` | Parametro obbligatorio mancante |
| `6` | Formato dati non valido |
| `9` | Errore generico |

### Note Implementative IMPORTANTI

| Problema | Soluzione |
|----------|-----------|
| `tranId` causa errore Java cast | **NON USARE** questo campo |
| `isReversal` formato errato | Usare `"Y"` o `"N"` (NON `"true"`/`"false"`) |
| `authCode` vuoto | Transazione rifiutata = NO scontrino disponibile. Bloccare PRIMA di chiamare API |
| Tipi dati errati | **Tutti i valori devono essere STRINGHE** |

### Flusso PHP (`scontrino_nets.php`)

```
1. GET /scontrino_nets.php?trid=123456
2. Query DB: SELECT * FROM ftfs_transactions WHERE Trid=?
3. Check ApprNum vuoto -> "Transazione rifiutata, no scontrino"
4. Prepara JSON payload (tutti valori stringa!)
5. POST a N&TS API via cURL
6. Check resultCode (0=success)
7. Decodifica base64 resultReceipt
8. Output PDF (Content-Type: application/pdf)
```

---

## Installazione

### Requisiti
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx con mod_rewrite
- Composer

### Setup
```bash
# 1. Clona repository
git clone <repo-url>
cd ftfs

# 2. Installa dipendenze
composer install

# 3. Configura database
cp config.php.example v2/config.php
# Modifica v2/config.php con credenziali

# 4. Importa schema
mysql -u username -p payglobe < schema.sql
```

### Deploy Server Produzione
```bash
# Server: pguser@pgbe2
# Path: /var/www/html/ftfs/v2/

scp v2/*.php pguser@pgbe2:/var/www/html/ftfs/v2/
```

---

## Tecnologie

| Componente | Tecnologia |
|------------|------------|
| Backend | PHP 7.4, MySQL 5.7 |
| Frontend | Bootstrap 4.5, jQuery 3.6, ECharts 5.4 |
| PDF | DomPDF (locale), N&TS API (scontrini) |
| Email | PHPMailer |

## Dipendenze Composer

```json
{
    "require": {
        "dompdf/dompdf": "^3.1",
        "phpmailer/phpmailer": "^6.9"
    }
}
```

---

## Sicurezza

- **config.php**: Mai committare con credenziali reali
- **SQL Injection**: Prepared statements ovunque
- **Session**: Validazione su ogni endpoint API
- **SSL**: Abilitare CURLOPT_SSL_VERIFYPEER in produzione

---

## Licenza

Proprietary - PayGlobe SRL (GUM Group Company)

## Supporto

Per supporto contattare il team sviluppo PayGlobe.

---
*Ultimo aggiornamento: Gennaio 2026*
