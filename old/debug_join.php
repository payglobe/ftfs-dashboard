<?php
// Script di debug per verificare il JOIN tra ftfs_transactions e stores
require_once 'config.php';
require_once 'bu_config.php'; // Include BU configuration
session_start();

if (!isset($_SESSION['username'])) {
    die("Non autorizzato");
}

$bu = trim($_SESSION['bu'], "'");

// Get the correct stores table based on BU
$storesTable = getStoresTable($bu);

// Test query per verificare il JOIN
$sql = "SELECT
    ft.TermId,
    ft.Acquirer,
    st.TerminalID,
    st.Insegna,
    st.Ragione_Sociale,
    st.indirizzo,
    st.citta,
    st.prov
FROM ftfs_transactions ft
LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId
WHERE ft.TermId REGEXP ?
LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $bu);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Debug JOIN ftfs_transactions + stores</h2>";
echo "<p>Business Unit: $bu</p>";
echo "<table border='1' cellpadding='5'>";
echo "<tr>
    <th>ft.TermId</th>
    <th>ft.Acquirer</th>
    <th>st.TerminalID</th>
    <th>st.Insegna</th>
    <th>st.Ragione_Sociale</th>
    <th>st.indirizzo</th>
    <th>st.citta</th>
    <th>st.prov</th>
</tr>";

$count = 0;
$withData = 0;
$withoutData = 0;

while ($row = $result->fetch_assoc()) {
    $count++;
    $hasStoreData = !empty($row['Insegna']) || !empty($row['Ragione_Sociale']);
    if ($hasStoreData) {
        $withData++;
    } else {
        $withoutData++;
    }

    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['TermId'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($row['Acquirer'] ?? '') . "</td>";
    echo "<td style='background-color: " . ($row['TerminalID'] ? '#90EE90' : '#FFB6C6') . "'>" . htmlspecialchars($row['TerminalID'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['Insegna'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['Ragione_Sociale'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['indirizzo'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['citta'] ?? 'NULL') . "</td>";
    echo "<td>" . htmlspecialchars($row['prov'] ?? 'NULL') . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<p><strong>Totale righe analizzate:</strong> $count</p>";
echo "<p><strong>Righe con dati stores:</strong> $withData</p>";
echo "<p><strong>Righe senza dati stores:</strong> $withoutData</p>";

if ($withoutData > 0) {
    echo "<h3>Possibili cause dei dati mancanti:</h3>";
    echo "<ul>";
    echo "<li>TerminalID non presente nella tabella stores</li>";
    echo "<li>Differenze nel formato del TerminalID (spazi, maiuscole/minuscole)</li>";
    echo "<li>Dati non ancora inseriti nella tabella stores</li>";
    echo "</ul>";

    // Verifica se ci sono TermId in ftfs_transactions non presenti in stores
    echo "<h3>TerminalID mancanti nella tabella " . htmlspecialchars($storesTable) . ":</h3>";
    $sqlMissing = "SELECT DISTINCT ft.TermId
                   FROM ftfs_transactions ft
                   LEFT JOIN " . $storesTable . " st ON st.TerminalID = ft.TermId
                   WHERE ft.TermId REGEXP ?
                   AND st.TerminalID IS NULL
                   LIMIT 20";
    $stmtMissing = $conn->prepare($sqlMissing);
    $stmtMissing->bind_param("s", $bu);
    $stmtMissing->execute();
    $resultMissing = $stmtMissing->get_result();

    echo "<ul>";
    while ($rowMissing = $resultMissing->fetch_assoc()) {
        echo "<li>" . htmlspecialchars($rowMissing['TermId']) . "</li>";
    }
    echo "</ul>";
}

$stmt->close();
$conn->close();
?>
