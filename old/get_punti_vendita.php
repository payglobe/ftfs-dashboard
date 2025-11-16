<?php
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

// Query raggruppata per Insegna - mostra un'unica opzione per insegna
$sql = "SELECT DISTINCT st.Insegna
        FROM " . $storesTable . " st
        WHERE st.TerminalID REGEXP ?
        AND st.TerminalID IS NOT NULL
        AND st.TerminalID != ''
        AND st.Insegna IS NOT NULL
        AND st.Insegna != ''
        ORDER BY st.Insegna ASC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error']);
    exit;
}

$stmt->bind_param("s", $bu);
$stmt->execute();
$result = $stmt->get_result();

$puntiVendita = [];
while ($row = $result->fetch_assoc()) {
    $insegna = $row['Insegna'];

    $puntiVendita[] = [
        'value' => $insegna, // Il valore è l'insegna
        'label' => $insegna  // Mostra l'insegna
    ];
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($puntiVendita);
?>
