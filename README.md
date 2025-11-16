# FTFS Dashboard

Dashboard web per la gestione del sistema FTFS, sviluppato in PHP con interfaccia di autenticazione e funzionalità di reporting.

## Caratteristiche

- Sistema di autenticazione utente con sessioni
- Dashboard v2 con interfaccia migliorata
- Generazione di report PDF (tramite DomPDF)
- Invio email (tramite PHPMailer)
- Processamento dati JSON
- Esportazione dati

## Requisiti

- PHP 7.4 o superiore
- MySQL/MariaDB
- Composer
- Server web (Apache/Nginx)

## Installazione

1. Clona il repository:
```bash
git clone https://github.com/payglobe/ftfs-dashboard.git
cd ftfs-dashboard
```

2. Installa le dipendenze tramite Composer:
```bash
composer install
```

3. Crea il database e importa lo schema:
```bash
mysql -u your_username -p -e "CREATE DATABASE payglobe;"
mysql -u your_username -p payglobe < schema.sql
```

4. Configura il database:
```bash
cp config.php.example config.php
```

5. Modifica `config.php` con le tue credenziali del database:
```php
$servername = "your_host";
$username = "your_username";
$password = "your_password";
$dbname = "payglobe";
```

6. Configura il tuo server web per puntare alla directory del progetto

## Struttura del Progetto

```
├── index.php              # Entry point - gestisce redirect al login o dashboard
├── config.php             # Configurazione database (non versionato)
├── config.php.example     # Template configurazione
├── schema.sql             # Schema database (senza dati)
├── composer.json          # Dipendenze PHP
├── process_json.php       # Processamento dati JSON
├── v2/                    # Dashboard versione 2
├── exports/               # Directory per file esportati
├── old/                   # Versioni precedenti
└── vendor/                # Dipendenze Composer (non versionato)
```

## Dipendenze

- **dompdf/dompdf** (^3.1) - Generazione PDF
- **phpmailer/phpmailer** (^6.9) - Invio email

## Sicurezza

⚠️ **IMPORTANTE**: Non committare mai il file `config.php` con credenziali reali. Usa sempre `config.php.example` come template.

## Licenza

Proprietario - PayGlobe

## Supporto

Per supporto o domande, contattare il team di sviluppo PayGlobe.
