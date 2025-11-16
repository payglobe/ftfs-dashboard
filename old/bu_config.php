<?php
/**
 * Configurazione mapping Business Unit -> Database
 *
 * Questo file contiene la mappatura tra le Business Unit e i database
 * che contengono la tabella 'stores' corrispondente.
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
