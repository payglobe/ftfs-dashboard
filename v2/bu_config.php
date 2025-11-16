<?php
/**
 * FTFS Dashboard v2.0 - Business Unit Configuration
 *
 * Descrizione:
 * File di configurazione per la mappatura tra Business Unit (BU) e database
 * contenenti le tabelle 'stores' corrispondenti. Ogni BU puo avere un database
 * dedicato con la propria tabella stores contenente i punti vendita.
 *
 * Funzionalita:
 * - Mappatura BU -> Database
 * - Database di default per BU non specificate
 * - Funzione helper per recuperare nome completo tabella stores
 *
 * Mapping Attuale:
 * - ^1060 -> medgroup.stores (BU Medgroup)
 * - Default -> payglobe.stores (tutte le altre BU)
 *
 * Variabili Globali:
 * - $BU_DATABASE_MAP (array) - Mapping regex BU -> database name
 * - $DEFAULT_DATABASE (string) - Database default (payglobe)
 *
 * Funzioni Esportate:
 * - getDatabaseForBU($bu) - Restituisce database per una BU specifica
 * - getStoresTable($bu) - Restituisce nome completo tabella (database.stores)
 *
 * Utilizzo Tipico:
 * ```php
 * include 'bu_config.php';
 * $bu = $_SESSION['bu']; // es: '^1060'
 * $storesTable = getStoresTable($bu); // 'medgroup.stores'
 * $sql = "SELECT * FROM " . $storesTable . " WHERE TerminalID = ?";
 * ```
 *
 * Estensibilita:
 * Per aggiungere nuove BU, modificare l'array $BU_DATABASE_MAP:
 * '^1070' => 'altrodatabase',
 * '^1080' => 'thirdparty',
 *
 * Note Implementative:
 * - La BU viene ripulita da eventuali apici (trim)
 * - Il matching usa il pattern esatto (es: ^1060, non 1060)
 * - Se BU non trovata in mapping, usa database default
 *
 * @author Claude Code
 * @version 2.0
 * @date Novembre 2025
 * @license Proprietario PayGlobe - GUM Group Company
 */

// Mapping BU -> Database name
$BU_DATABASE_MAP = [
    '^1060' => 'medgroup',  // BU 1060 usa il database medgroup
    // Aggiungi altre BU qui se necessario
    // '^1070' => 'altrodatabase',
    // '^1080' => 'payglobe',
];

// Database di default per BU non specificate
$DEFAULT_DATABASE = 'payglobe';

/**
 * Funzione per ottenere il nome del database in base alla BU
 *
 * @param string $bu Business Unit (es. '^1060')
 * @return string Nome del database da utilizzare
 */
function getDatabaseForBU($bu) {
    global $BU_DATABASE_MAP, $DEFAULT_DATABASE;

    // Rimuovi eventuali apici dalla BU
    $bu = trim($bu, "'");

    // Cerca il database nella mappatura
    if (isset($BU_DATABASE_MAP[$bu])) {
        return $BU_DATABASE_MAP[$bu];
    }

    // Se non trovato, usa il database di default
    return $DEFAULT_DATABASE;
}

/**
 * Funzione per ottenere il nome completo della tabella stores
 *
 * @param string $bu Business Unit
 * @return string Nome completo della tabella (database.stores)
 */
function getStoresTable($bu) {
    $database = getDatabaseForBU($bu);
    return $database . '.stores';
}

?>
