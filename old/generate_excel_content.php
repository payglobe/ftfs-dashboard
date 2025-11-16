<?php
function generateExcelContent($conn, $bu, $startDate, $endDate, $minAmount, $maxAmount, $terminalID, $siaCode) {
    ob_start();

    // Set headers solo se NON siamo in CLI (cioè output diretto al browser)
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=export_transazioni.xls');
    }

    // Includi la logica di export_excel.php, ma senza leggere $_SESSION o $_GET
    include 'export_excel_logic.php';

    return ob_get_clean();
}
