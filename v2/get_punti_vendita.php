<?php
/**
 * FTFS Dashboard v2.0 - Punti Vendita Autocomplete API
 *
 * Formato: Insegna - Citta - Provincia
 *
 * @author Claude Code
 * @version 2.0
 */

session_start();
if (!isset($_SESSION['username'])) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'Unauthorized';
    exit;
}

include 'config.php';
include 'bu_config.php';

$bu = trim($_SESSION['bu'], "'");

// Get the correct stores table based on BU
$storesTable = getStoresTable($bu);

// Query con Insegna, citta e prov - mostra combinazioni uniche
$sql = "SELECT DISTINCT st.Insegna, st.citta, st.prov
        FROM " . $storesTable . " st
        WHERE st.TerminalID REGEXP ?
        AND st.TerminalID IS NOT NULL
        AND st.TerminalID != ''
        AND st.Insegna IS NOT NULL
        AND st.Insegna != ''
        ORDER BY st.Insegna ASC, st.citta ASC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error']);
    exit;
}

$stmt->bind_param("s", $bu);
$stmt->execute();
$result = $stmt->get_result();

// Normalizza insegne per evitare duplicati
$insegneUniche = [];
while ($row = $result->fetch_assoc()) {
    $insegna = $row['Insegna'];
    $citta = $row['citta'] ?? '';
    $prov = $row['prov'] ?? '';

    // Normalizza: trim + rimuovi punti finali
    $insegnaNormalized = trim($insegna);
    $insegnaNormalized = rtrim($insegnaNormalized, '.');
    
    $cittaNormalized = trim($citta);
    $provNormalized = trim($prov);

    // Crea chiave unica per Insegna
    if (!isset($insegneUniche[$insegnaNormalized])) {
        $insegneUniche[$insegnaNormalized] = [
            'insegna' => $insegnaNormalized,
            'locations' => []
        ];
    }
    
    // Aggiungi combinazione città-provincia se non vuota
    if ($cittaNormalized !== '' || $provNormalized !== '') {
        $location = '';
        if ($cittaNormalized !== '') {
            $location = $cittaNormalized;
        }
        if ($provNormalized !== '') {
            if ($location !== '') {
                $location .= ' - ' . $provNormalized;
            } else {
                $location = $provNormalized;
            }
        }
        
        // Aggiungi solo se non già presente
        if ($location && !in_array($location, $insegneUniche[$insegnaNormalized]['locations'])) {
            $insegneUniche[$insegnaNormalized]['locations'][] = $location;
        }
    }
}

// Ordina alfabeticamente
ksort($insegneUniche);

// Converti in formato autocomplete
$puntiVendita = [];
foreach ($insegneUniche as $data) {
    $insegna = $data['insegna'];
    $locations = $data['locations'];
    
    // Se ci sono location, mostra la prima o le prime 2
    if (count($locations) > 0) {
        if (count($locations) == 1) {
            $label = $insegna . ' - ' . $locations[0];
        } else {
            // Mostra prime 2 location + "..." se ce ne sono più di 2
            $locStr = implode(' | ', array_slice($locations, 0, 2));
            if (count($locations) > 2) {
                $locStr .= '...';
            }
            $label = $insegna . ' - ' . $locStr;
        }
    } else {
        $label = $insegna;
    }
    
    $puntiVendita[] = [
        'value' => $insegna,
        'label' => $label
    ];
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($puntiVendita);
?>
