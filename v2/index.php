<?php
/**
 * FTFS Dashboard v2.0 - Main Dashboard Page
 *
 * Descrizione:
 * Dashboard principale per la visualizzazione e gestione delle transazioni FTFS.
 * Include visualizzazione dati tramite grafici interattivi ECharts, card view paginata
 * delle transazioni, filtri avanzati, statistiche aggregate e funzionalita di export.
 *
 * Funzionalita Principali:
 * - 6 grafici interattivi (acquirer, circuito, orario, giorno settimana, importo, trend)
 * - Card view paginata (12 card per pagina)
 * - Sidebar con dettagli transazione completi
 * - 4 statistiche cards (totale TX, OK, KO, tasso successo)
 * - Filtri: date, importi, terminal ID, punto vendita
 * - Export CSV ottimizzato per grandi dataset
 * - Visualizzazione scontrini PDF
 *
 * Dipendenze Esterne:
 * - Bootstrap 4.5.2 (UI Framework)
 * - jQuery 3.6.0 (DOM manipulation)
 * - DataTables 1.13.7 (non utilizzato per tabelle ma per utility)
 * - jQuery DateTimePicker (selezione date)
 * - ECharts 5.4.3 (grafici interattivi)
 * - Font Awesome 6.4.0 (icone)
 *
 * API Backend Utilizzate:
 * - get_summary.php - Statistiche e dati grafici
 * - get_table_data.php - Lista transazioni per card view
 * - get_punti_vendita.php - Autocomplete punti vendita
 * - export_large.php - Export CSV streaming
 * - export_progress.php - Conteggio transazioni per export
 * - scontrino_nets.php - Generazione scontrino via API N&TS
 *
 * Sessioni Richieste:
 * - $_SESSION['username'] - Email utente (verifica autenticazione)
 * - $_SESSION['bu'] - Business Unit (filtro dati)
 * - $_SESSION['user_id'] - ID utente
 *
 * File di Configurazione:
 * - config.php - Connessione database MySQL
 *
 * @author Claude Code
 * @version 2.0
 * @date Novembre 2025
 * @license Proprietario PayGlobe - GUM Group Company
 */

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$bu = htmlspecialchars($_SESSION['bu']);

include 'config.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Transazioni - FTFS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
</head>
<body>

<!-- Top Navigation Bar (Merchant Style) -->
<header style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000;">
    <div class="container-fluid" style="max-width: 1600px; padding: 1rem 1.5rem;">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Logo e Titolo -->
            <div class="d-flex align-items-center">
                <div class="d-flex align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 0.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);">
                    <i class="fas fa-chart-line" style="color: white; font-size: 1.5rem;"></i>
                </div>
                <div style="margin-left: 0.75rem;">
                    <h1 class="mb-0 d-flex align-items-center" style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">
                        <svg style="height: 1.5rem; margin-right: 0.5rem;" viewBox="0 0 761.13 177.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <defs>
                                <linearGradient id="grad1-ftfs" x1="74.71" y1="17.34" x2="109.39" y2="17.34" gradientUnits="userSpaceOnUse">
                                    <stop offset="0" stop-color="#488dec"/>
                                    <stop offset="1" stop-color="#9a1bf1"/>
                                </linearGradient>
                                <linearGradient id="grad2-ftfs" x1="0" y1="106.75" x2="154.73" y2="106.75" xlink:href="#grad1-ftfs"/>
                            </defs>
                            <g>
                                <path fill="#06112c" d="m431.25,46.38c20.22,0,30.1,10,30.1,10l-8.73,10.34s-7.7-6.78-21.03-6.78c-20.68,0-34.01,17.12-34.01,34.7,0,14.36,9.65,22.52,22.4,22.52s22.29-8.85,22.29-8.85l1.84-9.19h-12.64l2.53-12.75h25.85l-8.39,42.97h-12.64l.8-4.02c.34-1.72.81-3.45.81-3.45h-.23s-8.5,8.85-23.32,8.85c-18.73,0-34.58-13.44-34.58-35.16,0-26.66,21.83-49.18,48.94-49.18"/>
                                <polygon fill="#06112c" points="484.11 47.76 498.93 47.76 485.49 116.59 520.76 116.59 518.11 129.34 468.25 129.34 484.11 47.76"/>
                                <path fill="#06112c" d="m577.86,46.39c22.4,0,37.23,14.71,37.23,35.04,0,26.65-23.78,49.29-48.37,49.29-22.52,0-37.11-15.05-37.11-35.85,0-26.2,23.44-48.49,48.26-48.49m-11.03,70.77c16.31,0,32.97-15.51,32.97-34.81,0-13.33-9.08-22.4-22.06-22.4-16.66,0-32.97,15.28-32.97,34.12,0,13.67,9.08,23.09,22.06,23.09"/>
                                <path fill="#06112c" d="m637.85,47.76h26.2c4.71,0,8.85.46,12.29,1.72,7.47,2.64,11.83,8.73,11.83,16.77,0,8.62-5.28,16.43-13.33,20.11v.23c6.55,2.41,9.88,8.62,9.88,15.85,0,12.52-7.81,21.26-18.27,24.93-3.91,1.38-8.16,1.95-12.41,1.95h-32.05l15.85-81.57Zm17.23,68.82c2.53,0,4.83-.46,6.78-1.5,4.59-2.41,7.7-7.35,7.7-12.98s-3.56-9.08-9.88-9.08h-15.85l-4.6,23.56h15.85Zm5.4-35.5c7.24,0,12.41-5.86,12.41-12.87,0-4.48-2.64-7.7-8.62-7.7h-14.13l-4.02,20.57h14.36Z"/>
                                <polygon fill="#06112c" points="712.42 47.76 761.13 47.76 758.61 60.52 724.6 60.52 720.46 81.89 747.92 81.89 745.39 94.64 717.93 94.64 713.68 116.59 749.53 116.59 747.12 129.34 696.45 129.34 712.42 47.76"/>
                                <path fill="#06112c" d="m189.47,48.68h29.64c14.82,0,25.51,10,25.51,25.39s-10.68,25.73-25.51,25.73h-18.27v29.99h-11.37V48.68Zm27.8,41.25c9.77,0,15.74-6.09,15.74-15.85s-5.97-15.51-15.63-15.51h-16.54v31.37h16.43Z"/>
                                <path fill="#06112c" d="m297.02,106.47h-30.56l-8.04,23.32h-11.72l29.18-81.12h11.95l29.18,81.12h-11.83l-8.16-23.32Zm-15.28-46.65s-1.84,7.35-3.22,11.49l-9.08,25.73h24.59l-8.96-25.73c-1.38-4.14-3.1-11.49-3.1-11.49h-.23Z"/>
                                <path fill="#06112c" d="m341.14,95.44l-27.23-46.76h12.87l15.05,26.66c2.53,4.48,4.94,10.23,4.94,10.23h.23s2.41-5.63,4.94-10.23l14.82-26.66h12.87l-27.11,46.76v34.35h-11.38v-34.35Z"/>
                                <path fill="url(#grad1-ftfs)" d="m103.24,30.59c7.31-6.18,8.23-17.12,2.06-24.44-6.19-7.32-17.12-8.24-24.44-2.06-7.31,6.18-8.24,17.12-2.06,24.44,6.18,7.31,17.12,8.24,24.44,2.06Z"/>
                                <path fill="url(#grad2-ftfs)" d="m139.67,142.35c28.74-41.92,13.35-99.92-20.92-105.49-41.38-6.95-66.36,65.65-58.42,65.09,4.6-.32,16.89-20,31.61-32.54,5.54-4.7,15.12-11.34,21.88-8.72,6.4,2.54,9.62,12.45,10.5,19.65,2.1,16.81-4.75,32.71-9.57,38.9-13.07,16.81-31.6,26.31-46.84,27.13-24.12,1.29-45.56-14.91-52.06-37.52-6.55-22.71,2.28-50.29,26.54-68.58,3.45-2.59,5.56-3.81,5.55-3.83,0,0-33.33,11.61-44.62,47.17-18.78,58.97,45.13,114.34,106.28,85.75,5.61-2.72,15.19-9.32,23.24-18.33-.03.04-.07.07-.1.11.15-.16.31-.33.45-.49,2.36-2.67,4.51-5.44,6.45-8.28"/>
                            </g>
                        </svg>
                        Dashboard Transazioni
                    </h1>
                    <p class="mb-0" style="font-size: 0.875rem; color: #6b7280;">FTFS v2.0</p>
                </div>
            </div>

            <!-- Logout Button -->
            <a href="logout.php" class="btn d-flex align-items-center" style="background: #fee2e2; color: #dc2626; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'">
                <i class="fas fa-sign-out-alt" style="margin-right: 0.5rem;"></i>
                Esci
            </a>
        </div>

        <!-- Navigation Menu -->
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
            <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                <a href="index.php" class="btn d-flex align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; border: none; transition: all 0.3s; box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(102, 126, 234, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 4px rgba(102, 126, 234, 0.3)'">
                    <i class="fas fa-home" style="margin-right: 0.5rem;"></i>
                    Home
                </a>
            </div>
        </div>
    </div>
</header>

<style>
* {
    box-sizing: border-box;
}

body {
    background: #f5f7fa;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

/* Modern Dashboard Container */
.dashboard-container {
    max-width: 1600px;
    margin: 0 auto;
    padding: 20px;
}

/* Stats Cards */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin-bottom: 15px;
}

.stat-label {
    font-size: 13px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin: 8px 0;
}

.stat-trend {
    font-size: 12px;
    color: #10b981;
    font-weight: 600;
}

/* Filter Panel */
.filter-panel {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.filter-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 10px 15px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.2s;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.btn-modern {
    padding: 12px 24px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #e5e7eb;
    color: #374151;
}

.btn-secondary:hover {
    background: #d1d5db;
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
}

/* Transaction Cards Grid */
.transaction-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.transaction-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.transaction-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #667eea, #764ba2);
    transform: scaleY(0);
    transition: transform 0.3s;
}

.transaction-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.transaction-card:hover::before {
    transform: scaleY(1);
}

.transaction-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.transaction-amount {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
}

.transaction-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-success {
    background: #d1fae5;
    color: #065f46;
}

.status-failed {
    background: #fee2e2;
    color: #991b1b;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}

.status-warning {
    background: #fed7aa;
    color: #c2410c;
}

.transaction-info {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-top: 15px;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-size: 11px;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin-bottom: 4px;
}

.info-value {
    font-size: 14px;
    color: #1f2937;
    font-weight: 500;
}

.transaction-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f3f4f6;
}

.transaction-time {
    font-size: 12px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 5px;
}

.transaction-terminal {
    font-size: 12px;
    background: #f3f4f6;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 600;
    color: #4b5563;
}

/* Detail Sidebar */
.detail-sidebar {
    position: fixed;
    right: -500px;
    top: 0;
    width: 500px;
    height: 100vh;
    background: white;
    box-shadow: -5px 0 30px rgba(0,0,0,0.2);
    transition: right 0.3s ease;
    z-index: 9999;
    overflow-y: auto;
}

.detail-sidebar.active {
    right: 0;
}

.detail-sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
    z-index: 9998;
}

.detail-sidebar-overlay.active {
    opacity: 1;
    visibility: visible;
}

.detail-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    position: relative;
}

.detail-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    transition: all 0.3s;
}

.detail-close:hover {
    background: rgba(255,255,255,0.3);
    transform: rotate(90deg);
}

.detail-amount {
    font-size: 42px;
    font-weight: 700;
    margin: 10px 0;
}

.detail-body {
    padding: 30px;
}

.detail-section {
    margin-bottom: 30px;
}

.detail-section-title {
    font-size: 14px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 15px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.detail-item {
    background: #f9fafb;
    padding: 15px;
    border-radius: 10px;
}

.detail-item-label {
    font-size: 11px;
    color: #9ca3af;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 6px;
}

.detail-item-value {
    font-size: 15px;
    color: #1f2937;
    font-weight: 600;
}

.detail-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
}

.detail-actions button {
    flex: 1;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin: 30px 0;
}

.pagination-btn {
    padding: 10px 20px;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.pagination-btn:hover:not(:disabled) {
    border-color: #667eea;
    color: #667eea;
}

.pagination-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.pagination-info {
    font-size: 14px;
    color: #6b7280;
    font-weight: 600;
}

/* Loading State */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.9);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.loading-overlay.active {
    display: flex;
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #e5e7eb;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 80px;
    color: #d1d5db;
    margin-bottom: 20px;
}

.empty-title {
    font-size: 24px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 10px;
}

.empty-text {
    font-size: 16px;
    color: #6b7280;
}

/* Charts Section */
.charts-section {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

.charts-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}

.charts-title {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
}

.toggle-charts-btn {
    background: none;
    border: none;
    color: #667eea;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s;
}

.toggle-charts-btn:hover {
    color: #764ba2;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-top: 20px;
}

.chart-box {
    height: 350px;
}

.chart-box-title {
    font-size: 15px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 15px;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard-header h1 {
        font-size: 24px;
    }

    .transaction-grid {
        grid-template-columns: 1fr;
    }

    .detail-sidebar {
        width: 100%;
        right: -100%;
    }

    .charts-grid {
        grid-template-columns: 1fr;
    }

    .filter-row {
        grid-template-columns: 1fr;
    }
}

.switch-container {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 26px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

input:checked + .slider:before {
    transform: translateX(24px);
}
</style>

<div class="dashboard-container">

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #dbeafe; color: #1e40af;">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-label">Totale Transazioni</div>
            <div class="stat-value" id="totalTransactions">-</div>
            <div class="stat-trend" id="okKoStats" style="font-size: 13px; color: #6b7280; font-weight: 600;">
                <span style="color: #10b981;"><i class="fas fa-check-circle"></i> OK: <span id="okCount">0</span></span>
                <span style="margin: 0 8px;">|</span>
                <span style="color: #ef4444;"><i class="fas fa-times-circle"></i> KO: <span id="koCount">0</span></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #d1fae5; color: #065f46;">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-label">Importo Totale</div>
            <div class="stat-value" id="totalAmount">Ã¢â€šÂ¬0,00</div>
            <div class="stat-trend"><i class="fas fa-chart-line"></i> Volume</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #e9d5ff; color: #6b21a8;">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="stat-label">Importo Medio</div>
            <div class="stat-value" id="avgAmount">Ã¢â€šÂ¬0,00</div>
            <div class="stat-trend"><i class="fas fa-equals"></i> Media</div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: #fed7aa; color: #92400e;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-label">Tasso Successo</div>
            <div class="stat-value" id="successRate">0%</div>
            <div class="stat-trend"><i class="fas fa-percentage"></i> Confermate</div>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="filter-panel">
        <div class="filter-row">
            <div class="filter-group">
                <label><i class="far fa-calendar-alt"></i> Data Inizio</label>
                <input type="text" id="startDate" class="datetimepicker" placeholder="Seleziona data">
            </div>
            <div class="filter-group">
                <label><i class="far fa-calendar-alt"></i> Data Fine</label>
                <input type="text" id="endDate" class="datetimepicker" placeholder="Seleziona data">
            </div>
            <div class="filter-group">
                <label><i class="fas fa-euro-sign"></i> Importo Min</label>
                <input type="number" id="minAmount" placeholder="0.00">
            </div>
            <div class="filter-group">
                <label><i class="fas fa-euro-sign"></i> Importo Max</label>
                <input type="number" id="maxAmount" placeholder="0.00">
            </div>
            <div class="filter-group">
                <label><i class="fas fa-terminal"></i> Terminal ID</label>
                <input type="text" id="terminalID" placeholder="ID Terminale">
            </div>
            <div class="filter-group">
                <label><i class="fas fa-store"></i> Punto Vendita</label>
                <select id="puntoVendita">
                    <option value="">Tutti</option>
                </select>
            </div>
        </div>

        <div class="filter-actions">
            <button class="btn-modern btn-primary" id="filterButton">
                <i class="fas fa-filter"></i> Applica Filtri
            </button>
            <button class="btn-modern btn-secondary" id="resetButton">
                <i class="fas fa-redo"></i> Reset
            </button>
            <button class="btn-modern btn-success" id="exportButton">
                <i class="fas fa-file-excel"></i> Esporta Excel
            </button>
            <div class="switch-container">
                <label class="switch">
                    <input type="checkbox" id="autoRefreshSwitch">
                    <span class="slider"></span>
                </label>
                <span style="font-size: 14px; color: #6b7280; font-weight: 600;">Auto-refresh</span>
            </div>
        </div>
    </div>

    <!-- Charts Section (Collapsible) -->
    <div class="charts-section">
        <div class="charts-toggle">
            <h3 class="charts-title"><i class="fas fa-chart-pie"></i> Analisi e Statistiche</h3>
            <button class="toggle-charts-btn" id="toggleCharts">
                <span id="chartsToggleText">Nascondi</span>
                <i class="fas fa-chevron-up" id="chartsToggleIcon"></i>
            </button>
        </div>
        <div id="chartsContainer" class="charts-grid">
            <div>
                <div class="chart-box-title">Distribuzione per Acquirer</div>
                <div id="acquirerChart" class="chart-box"></div>
            </div>
            <div>
                <div class="chart-box-title">Distribuzione per Circuito</div>
                <div id="circuitChart" class="chart-box"></div>
            </div>
            <div>
                <div class="chart-box-title">Andamento Orario</div>
                <div id="hourlyChart" class="chart-box"></div>
            </div>
            <div>
                <div class="chart-box-title">Distribuzione Settimanale</div>
                <div id="weekdayChart" class="chart-box"></div>
            </div>
        </div>
    </div>

    <!-- Info Banner -->
    <div id="recordBanner" style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: none; align-items: center; gap: 12px;">
        <i class="fas fa-info-circle" style="color: #f59e0b; font-size: 18px;"></i>
        <div style="flex: 1;">
            <div style="font-weight: 600; color: #92400e; font-size: 14px;">Visualizzazione limitata</div>
            <div id="recordText" style="color: #78350f; font-size: 13px;">Caricamento...</div>
        </div>
    </div>

    <!-- Transactions Grid -->
    <div id="transactionsContainer" class="transaction-grid">
        <!-- Cards will be inserted here via JavaScript -->
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="empty-state" style="display: none;">
        <div class="empty-icon"><i class="fas fa-inbox"></i></div>
        <div class="empty-title">Nessuna transazione trovata</div>
        <div class="empty-text">Prova a modificare i filtri per visualizzare le transazioni</div>
    </div>

    <!-- Pagination -->
    <div class="pagination-container" id="paginationContainer" style="display: none;">
        <button class="pagination-btn" id="prevPage"><i class="fas fa-chevron-left"></i> Precedente</button>
        <div class="pagination-info" id="pageInfo">Pagina 1 di 1</div>
        <button class="pagination-btn" id="nextPage">Successiva <i class="fas fa-chevron-right"></i></button>
    </div>

</div>

<!-- Detail Sidebar -->
<div class="detail-sidebar-overlay" id="detailOverlay"></div>
<div class="detail-sidebar" id="detailSidebar">
    <div class="detail-header">
        <button class="detail-close" id="closeDetail"><i class="fas fa-times"></i></button>
        <div id="detailStatusBadge"></div>
        <div class="detail-amount" id="detailAmount">Ã¢â€šÂ¬0,00</div>
        <div id="detailDate"></div>
    </div>
    <div class="detail-body" id="detailBody">
        <!-- Details will be inserted here -->
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
</div>

<!-- Modal for Receipt -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scontrino</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="receiptModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Timeout Warning -->
<div class="modal fade" id="timeoutModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <div class="modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; border-radius: 12px 12px 0 0; border: none;">
                <h5 class="modal-title" style="font-weight: 700;">
                    <i class="fas fa-exclamation-triangle"></i> Troppi Dati Richiesti
                </h5>
                <button type="button" class="close" data-dismiss="modal" style="color: white; opacity: 1;">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 30px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-database" style="font-size: 48px; color: #f59e0b;"></i>
                </div>
                <p style="font-size: 15px; color: #374151; margin-bottom: 15px;">
                    Il range di date selezionato contiene troppi dati e ha causato un timeout.
                </p>
                <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                    <div style="font-weight: 700; color: #92400e; margin-bottom: 8px;">
                        <i class="fas fa-clock"></i> Ridimensionamento automatico
                    </div>
                    <div style="color: #78350f; font-size: 14px; line-height: 1.6;">
                        Il range di date ÃƒÂ¨ stato ridimensionato alle <strong id="timeoutModalHoursText">ultime 4 ore</strong>.
                    </div>
                </div>
                <div style="background: #f3f4f6; padding: 12px 16px; border-radius: 6px; margin-bottom: 15px;">
                    <div style="font-size: 13px; color: #6b7280; margin-bottom: 6px;">Nuovo range:</div>
                    <div style="font-weight: 700; color: #1f2937; font-size: 15px; font-family: 'Courier New', monospace;">
                        <i class="fas fa-calendar-check" style="color: #667eea;"></i> <span id="newStartDateDisplay">-</span>
                    </div>
                    <div style="font-size: 13px; color: #6b7280; margin-top: 4px;">
                        <i class="fas fa-arrow-down" style="color: #9ca3af;"></i> fino ad adesso
                    </div>
                </div>
                <p style="font-size: 13px; color: #6b7280; margin: 0;">
                    <i class="fas fa-info-circle"></i> I dati verranno ricaricati automaticamente con il nuovo range.
                </p>
            </div>
            <div class="modal-footer" style="border: none; padding: 15px 30px 30px;">
                <button type="button" class="btn btn-primary" data-dismiss="modal" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 10px 24px; font-weight: 600; border-radius: 8px;">
                    <i class="fas fa-check"></i> Ho Capito
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 0;
let totalPages = 0;
let pageSize = 12;
let allTransactions = [];
let totalRecords = 0; // Total records from server
let currentFilters = {};
let timeoutHours = 3; // Start with 3 hours, reduce on timeout

// Charts
let acquirerChart, circuitChart, hourlyChart, weekdayChart;

// Initialize
$(document).ready(function() {
    initializeDatePickers();
    initializeCharts();
    loadPuntiVendita();
    loadAllData();

    // Event listeners
    $('#filterButton').on('click', function() {
        currentPage = 0;
        loadAllData();
    });

    $('#resetButton').on('click', resetFilters);
    $('#exportButton').on('click', exportToExcel);
    $('#toggleCharts').on('click', toggleCharts);
    $('#prevPage').on('click', () => changePage(-1));
    $('#nextPage').on('click', () => changePage(1));
    $('#closeDetail, #detailOverlay').on('click', closeDetailSidebar);

    // Auto-refresh
    $('#autoRefreshSwitch').on('change', function() {
        if (this.checked) {
            setInterval(loadAllData, 60000);
        }
    });
});

function initializeDatePickers() {
    $('.datetimepicker').datetimepicker({
        format: 'Y-m-d H:i:s',
        lang: 'it',
        step: 60
    });

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const todayEnd = new Date();
    todayEnd.setHours(23, 0, 0, 0);

    $("#startDate").val(today.toISOString().slice(0, 19).replace("T", " "));
    $("#endDate").val(todayEnd.toISOString().slice(0, 19).replace("T", " "));
}

function initializeCharts() {
    acquirerChart = echarts.init(document.getElementById('acquirerChart'));
    circuitChart = echarts.init(document.getElementById('circuitChart'));
    hourlyChart = echarts.init(document.getElementById('hourlyChart'));
    weekdayChart = echarts.init(document.getElementById('weekdayChart'));

    window.addEventListener('resize', function() {
        acquirerChart.resize();
        circuitChart.resize();
        hourlyChart.resize();
        weekdayChart.resize();
    });
}

function loadPuntiVendita() {
    $.ajax({
        url: 'get_punti_vendita.php',
        type: 'GET',
        dataType: 'json',
        timeout: 10000, // 10 second timeout
        success: function(data) {
            const select = $('#puntoVendita');
            data.forEach(item => {
                select.append($('<option></option>').attr('value', item.value).text(item.label));
            });
        },
        error: function(xhr, status, error) {
            console.error('Error loading punti vendita:', status, error);
        }
    });
}

function handleTimeout() {
    // Reduce hours progressively: 4 -> 3 -> 2 -> 1
    if (timeoutHours > 1) {
        timeoutHours--;
    } else {
        // Already at 1 hour, cannot reduce further
        alert('Impossibile caricare i dati anche con 1 ora. Prova ad applicare piÃƒÂ¹ filtri.');
        hideLoading();
        return;
    }

    // Set new range: NOW - X hours to NOW
    const now = new Date();
    const hoursAgo = new Date(now.getTime() - (timeoutHours * 60 * 60 * 1000));

    // Format date for datetimepicker (Y-m-d H:i:s format)
    const formatDate = (date) => {
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');
        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    };

    const newStartDateFormatted = formatDate(hoursAgo);
    const newEndDateFormatted = formatDate(now);

    // Update both start and end date fields
    $('#startDate').val(newStartDateFormatted);
    $('#endDate').val(newEndDateFormatted);

    // Update modal text
    const hoursText = timeoutHours === 1 ? "ultima ora" : `ultime ${timeoutHours} ore`;
    $('#timeoutModalHoursText').text(hoursText);
    $('#newStartDateDisplay').text(newStartDateFormatted);

    // Show modal
    $('#timeoutModal').modal('show');

    // Reload data when modal closes
    $('#timeoutModal').on('hidden.bs.modal', function() {
        // Remove event handler to avoid multiple bindings
        $(this).off('hidden.bs.modal');
        // Reload data with new date range
        loadAllData();
    });
}


// Update record count banner
function updateRecordBanner(loaded, total) {
    if (total > loaded) {
        $("#recordText").text(
            "Visualizzate " + loaded.toLocaleString('it-IT') + 
            " su " + total.toLocaleString('it-IT') + 
            " transazioni totali. Usa i filtri per affinare la ricerca."
        );
        $("#recordBanner").show();
    } else if (loaded >= 1000) {
        $("#recordText").text(
            "Visualizzate " + loaded.toLocaleString('it-IT') + 
            " transazioni. Usa i filtri per affinare la ricerca."
        );
        $("#recordBanner").show();
    } else {
        $("#recordBanner").hide();
    }
}

function loadAllData() {
    showLoading();

    currentFilters = {
        startDate: $('#startDate').val(),
        endDate: $('#endDate').val(),
        minAmount: $('#minAmount').val(),
        maxAmount: $('#maxAmount').val(),
        terminalID: $('#terminalID').val(),
        puntoVendita: $('#puntoVendita').val()
    };

    // Load transactions (reduced from 1000 to 120 for better performance)
    $.ajax({
        url: 'get_table_data.php',
        type: 'GET',
        data: Object.assign({}, currentFilters, {
            start: 0,
            length: 12 // Load one page at a time
            // Nessun filtro conf - mostra tutte le transazioni (confermate, storni, rifiutate)
        }),
        timeout: 30000, // 30 second timeout
        success: function(response) {
            allTransactions = response.result || [];
            totalRecords = response.rowCount || 0;
            const totalAvailable = response.rowCount || 0;
            
            // Render immediately for instant user feedback
            totalPages = Math.ceil(totalRecords / pageSize);
            renderTransactions();
            hideLoading();
            
            // Update banner
            
            
            
        },
        error: function(xhr, status, error) {
            hideLoading();
            console.error('Error loading transactions:', status, error, 'HTTP Status:', xhr.status);
            // Handle timeout or 504 Gateway Timeout
            if (status === 'timeout' || xhr.status === 504 || status === 'error') {
                handleTimeout();
            } else {
                alert('Errore caricamento transazioni. Riprova.');
            }
        }
    });

    // Load analytics
    $.ajax({
        url: 'get_summary.php',
        type: 'GET',
        data: currentFilters, // Nessun filtro conf per analytics
        timeout: 45000, // 45 second timeout for analytics
        success: function(data) {
            window.statsLoaded = true;
            updateStats(data);
            updateCharts(data);
        },
        error: function(xhr, status, error) {
            console.error('Error loading analytics:', status, error, 'HTTP Status:', xhr.status);
            // Show user-friendly message when charts fail
            if (status === 'timeout' || xhr.status === 504 || status === 'error') {
                // Show warning banner for charts
                $("#chartsContainer").html(
                    '<div style="background:#fef3c7;border-left:4px solid #f59e0b;padding:20px;border-radius:8px;margin:20px 0;text-align:center">' +
                    '<i class="fas fa-exclamation-triangle" style="font-size:32px;color:#f59e0b;margin-bottom:10px"></i>' +
                    '<h5 style="color:#92400e;font-weight:700;margin-bottom:8px">Grafici Non Disponibili</h5>' +
                    '<p style="color:#78350f;margin:0">Le query per i grafici richiedono troppo tempo con questo range di date.</p>' +
                    '<p style="color:#78350f;margin:5px 0 0 0"><strong>Suggerimento:</strong> Riduci il range di date o applica piÃƒÂ¹ filtri.</p>' +
                    '</div>'
                );
                
                // Calculate stats from loaded transactions
                if (!window.statsLoaded && allTransactions.length > 0) {
                    const stats = calculateStatsFromTransactions();
                    updateStats(stats);
                    // Add note that stats are based on loaded transactions only
                    $("#chartsContainer").prepend(
                        '<div style="background:#e0f2fe;border-left:4px solid #0284c7;padding:12px;border-radius:8px;margin-bottom:15px;font-size:13px">' +
                        '<i class="fas fa-info-circle" style="color:#0284c7;margin-right:8px"></i>' +
                        '<strong>Nota:</strong> Le statistiche sopra sono calcolate sulle ' + allTransactions.length.toLocaleString('it-IT') + ' transazioni caricate, non su tutte le transazioni del periodo.' +
                        '</div>'
                    );
                }
            } else {
                alert('Errore caricamento grafici. Riprova.');
            }
        }
    });
}

function renderTransactions() {
    const container = $('#transactionsContainer');
    const emptyState = $('#emptyState');
    const pagination = $('#paginationContainer');

    container.empty();

    if (allTransactions.length === 0) {
        emptyState.show();
        pagination.hide();
        return;
    }

    emptyState.hide();
    pagination.show();

    // Server-side pagination: allTransactions already contains only current page records
    allTransactions.forEach(tx => {
        const card = createTransactionCard(tx);
        container.append(card);
    });

    updatePagination();
}

function createTransactionCard(tx) {
    // Determina stato e colore basandosi su Conf e codiceAutorizzativo
    let statusClass, statusText;

    // Priorità: Storno implicito > altri stati
    // Conf='I' (storno implicito) o Conf='A' (stornata implicitamente) o OperExpl='Storno implicito'
    if (tx.Conf === 'I' || tx.Conf === 'A' || tx.OperExpl === 'Storno implicito') {
        statusClass = 'status-warning';
        statusText = 'STORNO IMPL.';
    } else if (tx.Conf === 'C' || tx.Conf === ' ' || tx.Conf === '') {
        statusClass = 'status-success';
        statusText = 'CONFERMATA';
    } else if (tx.Conf === 'E') {
        statusClass = 'status-pending';
        statusText = 'STORNO';
    } else if (tx.Conf === 'N') {
        statusClass = 'status-success';
        statusText = 'PREAUTH CONF.';
    } else if (tx.Conf === 'I' || tx.Conf === 'D' || tx.Conf === 'A') {
        statusClass = 'status-failed';
        statusText = 'RIFIUTATA';
    } else {
        statusClass = 'status-pending';
        statusText = 'ANOMALIA ' + tx.Conf;
    }

    // Se non c'ÃƒÂ¨ codice autorizzativo, ÃƒÂ¨ comunque rifiutata
    if (typeof tx.codiceAutorizzativo !== 'string' || !tx.codiceAutorizzativo) {
        statusClass = 'status-failed';
        statusText = 'RIFIUTATA';
    }

    const amount = parseFloat(tx.importo || 0).toLocaleString('it-IT', {style: 'currency', currency: 'EUR'});
    const date = new Date(tx.dataOperazione).toLocaleDateString('it-IT', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    const card = $(`
        <div class="transaction-card" data-tx='${JSON.stringify(tx)}'>
            <div class="transaction-header">
                <div class="transaction-amount">${amount}</div>
                <div class="transaction-status ${statusClass}">${statusText}</div>
            </div>
            <div class="transaction-info">
                <div class="info-item">
                    <div class="info-label">Acquirer</div>
                    <div class="info-value">${tx.acquirer || '-'}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Circuito</div>
                    <div class="info-value">${tx.PosAcq || '-'}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">PAN</div>
                    <div class="info-value">${tx.pan || '-'}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">${isStornoImplicito(tx) ? 'Motivo' : (isRejected(tx) ? 'Motivo Rifiuto' : 'Autorizzativo')}</div>
                    <div class="info-value">${isStornoImplicito(tx) ? '<span style="color: #c2410c; font-weight: 600;">Storno implicito</span>' : (isRejected(tx) ? getGtRespDisplay(tx.GtResp) : getAuthCodeDisplay(tx.codiceAutorizzativo))}</div>
                </div>
            </div>
            <div class="transaction-meta">
                <div class="transaction-time">
                    <i class="far fa-clock"></i> ${date}
                </div>
                <div class="transaction-terminal">
                    <i class="fas fa-terminal"></i> ${tx.terminalID || '-'}
                </div>
            </div>
        </div>
    `);

    card.on('click', function() {
        showDetailSidebar(tx);
    });

    return card;
}

function showDetailSidebar(tx) {
    const sidebar = $('#detailSidebar');
    const overlay = $('#detailOverlay');
    const amount = parseFloat(tx.importo || 0).toLocaleString('it-IT', {style: 'currency', currency: 'EUR'});
    const date = new Date(tx.dataOperazione).toLocaleString('it-IT');

    // Determina stato e colore (stessa logica della card)
    let statusClass, statusText;

    // Priorità: Storno implicito > altri stati
    // Conf='I' (storno implicito) o Conf='A' (stornata implicitamente) o OperExpl='Storno implicito'
    if (tx.Conf === 'I' || tx.Conf === 'A' || tx.OperExpl === 'Storno implicito') {
        statusClass = 'status-warning';
        statusText = 'STORNO IMPL.';
    } else if (tx.Conf === 'C' || tx.Conf === ' ' || tx.Conf === '') {
        statusClass = 'status-success';
        statusText = 'CONFERMATA';
    } else if (tx.Conf === 'E') {
        statusClass = 'status-pending';
        statusText = 'STORNO';
    } else if (tx.Conf === 'N') {
        statusClass = 'status-success';
        statusText = 'PREAUTH CONF.';
    } else if (tx.Conf === 'I' || tx.Conf === 'D' || tx.Conf === 'A') {
        statusClass = 'status-failed';
        statusText = 'RIFIUTATA';
    } else {
        statusClass = 'status-pending';
        statusText = 'ANOMALIA ' + tx.Conf;
    }

    // Se non c'ÃƒÂ¨ codice autorizzativo, ÃƒÂ¨ comunque rifiutata
    if (typeof tx.codiceAutorizzativo !== 'string' || !tx.codiceAutorizzativo) {
        statusClass = 'status-failed';
        statusText = 'RIFIUTATA';
    }

    $('#detailAmount').text(amount);
    $('#detailDate').text(date);
    $('#detailStatusBadge').html(`<div class="transaction-status ${statusClass}">${statusText}</div>`);

    const detailsHTML = `
        <div class="detail-section">
            <div class="detail-section-title">Informazioni Transazione</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-item-label">Tipo Operazione</div>
                    <div class="detail-item-value">${getOperationType(tx.TP)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Tipo Carta</div>
                    <div class="detail-item-value">${getCardType(tx.TPC)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Codice Autorizzativo</div>
                    <div class="detail-item-value">${getAuthCodeDisplay(tx.codiceAutorizzativo)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Esito</div>
                    <div class="detail-item-value">${getGtRespDescription(tx.GtResp)}</div>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Dati Pagamento</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-item-label">PAN</div>
                    <div class="detail-item-value">${tx.pan || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Circuito</div>
                    <div class="detail-item-value">${tx.PosAcq || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Acquirer</div>
                    <div class="detail-item-value">${tx.acquirer || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">AID</div>
                    <div class="detail-item-value">${tx.AId || '-'}</div>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Punto Vendita</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-item-label">Terminal ID</div>
                    <div class="detail-item-value">${tx.terminalID || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Terminal AQ</div>
                    <div class="detail-item-value">${tx.terminal || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Modello POS</div>
                    <div class="detail-item-value">${tx.Modello_pos || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Codifica Stab</div>
                    <div class="detail-item-value">${tx.codificaStab || '-'}</div>
                </div>
            </div>
        </div>

        <div class="detail-section">
            <div class="detail-section-title">Informazioni Commerciante</div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-item-label">Insegna</div>
                    <div class="detail-item-value">${tx.insegna || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Ragione Sociale</div>
                    <div class="detail-item-value">${tx.Ragione_Sociale || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">Indirizzo</div>
                    <div class="detail-item-value">${tx.indirizzo || '-'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-item-label">LocalitÃƒÂ </div>
                    <div class="detail-item-value">${(tx.localita || '-') + ' (' + (tx.prov || '-') + ')'}</div>
                </div>
            </div>
        </div>

        <div class="detail-actions">
            <button class="btn-modern btn-primary" onclick="viewReceipt('${tx.Trid}')">
                <i class="fas fa-receipt"></i> Visualizza Scontrino
            </button>
        </div>
    `;

    $('#detailBody').html(detailsHTML);
    sidebar.addClass('active');
    overlay.addClass('active');
}

function closeDetailSidebar() {
    $('#detailSidebar').removeClass('active');
    $('#detailOverlay').removeClass('active');
}

function calculateStatsFromTransactions() {
    // Calculate basic stats from already loaded transactions
    const totalTx = allTransactions.length;
    const confirmedTx = allTransactions.filter(tx => tx.Conf === 'C' || tx.Conf === ' ' || tx.Conf === '').length;
    const koTx = totalTx - confirmedTx;

    // Calculate total amount (only confirmed)
    const confirmedAmount = allTransactions
        .filter(tx => tx.Conf === 'C' || tx.Conf === ' ' || tx.Conf === '')
        .reduce((sum, tx) => sum + parseFloat(tx.importo || 0), 0);
    
    return {
        totalTransactions: totalTx,
        confirmedCount: confirmedTx,
        confirmedAmount: confirmedAmount,
        successRate: totalTx > 0 ? (confirmedTx / totalTx * 100) : 0
    };
}

function updateStats(data) {
    const totalTx = data.totalTransactions || 0;
    const confirmedTx = data.confirmedCount || 0;
    const koTx = totalTx - confirmedTx;

    // Totale transazioni (include KO)
    $('#totalTransactions').text(totalTx.toLocaleString('it-IT'));

    // OK / KO counts
    $('#okCount').text(confirmedTx.toLocaleString('it-IT'));
    $('#koCount').text(koTx.toLocaleString('it-IT'));

    // Importo totale (solo transazioni confermate)
    const confirmedAmount = parseFloat(data.confirmedAmount || 0);
    $('#totalAmount').text(confirmedAmount.toLocaleString('it-IT', {style: 'currency', currency: 'EUR'}));

    // Importo medio (basato su transazioni confermate)
    const avgAmount = confirmedTx > 0 ? confirmedAmount / confirmedTx : 0;
    $('#avgAmount').text(avgAmount.toLocaleString('it-IT', {style: 'currency', currency: 'EUR'}));

    // Tasso di successo (confermate su totali, incluse KO)
    const successRate = totalTx > 0 ? (confirmedTx / totalTx * 100).toFixed(1) : 0;
    $('#successRate').text(successRate + '%');
}

function updateCharts(data) {
    renderPieChart(acquirerChart, 'Acquirer', data.acquirerData);
    renderPieChart(circuitChart, 'Circuito', data.circuitData);
    renderHourlyChart(data.hourlyData || []);
    renderWeekdayChart(data.weekdayData || []);
}

function renderPieChart(chart, name, data) {
    const chartData = Object.entries(data || {}).map(([key, val]) => ({
        name: key || 'Sconosciuto',
        value: val.count || val
    }));

    chart.setOption({
        tooltip: {trigger: 'item'},
        legend: {orient: 'vertical', left: 'left'},
        series: [{
            name: name,
            type: 'pie',
            radius: ['40%', '70%'],
            itemStyle: {borderRadius: 10, borderColor: '#fff', borderWidth: 2},
            data: chartData,
            color: ['#667eea', '#764ba2', '#10b981', '#f59e0b', '#ef4444']
        }]
    }, true);
}

function renderHourlyChart(data) {
    const hours = Array.from({length: 24}, (_, i) => `${String(i).padStart(2, '0')}:00`);
    const counts = Array(24).fill(0);

    data.forEach(item => {
        counts[item.hour] = item.count || 0;
    });

    hourlyChart.setOption({
        tooltip: {trigger: 'axis'},
        xAxis: {type: 'category', data: hours, axisLabel: {rotate: 45, fontSize: 10}},
        yAxis: {type: 'value'},
        series: [{
            name: 'Transazioni',
            type: 'bar',
            data: counts,
            itemStyle: {color: '#667eea', borderRadius: [8, 8, 0, 0]}
        }]
    }, true);
}

function renderWeekdayChart(data) {
    const weekdays = ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'];
    const counts = Array(7).fill(0);

    data.forEach(item => {
        counts[item.weekday] = item.count || 0;
    });

    weekdayChart.setOption({
        tooltip: {trigger: 'axis'},
        xAxis: {type: 'category', data: weekdays},
        yAxis: {type: 'value'},
        series: [{
            name: 'Transazioni',
            type: 'bar',
            data: counts,
            itemStyle: {color: '#10b981', borderRadius: [8, 8, 0, 0]}
        }]
    }, true);
}

function updatePagination() {
    $('#pageInfo').text(`Pagina ${currentPage + 1} di ${totalPages || 1}`);
    $('#prevPage').prop('disabled', currentPage === 0);
    $('#nextPage').prop('disabled', currentPage >= totalPages - 1);
}

function changePage(delta) {
    currentPage += delta;
    loadTransactionsPage(currentPage);
}

function loadTransactionsPage(page) {
    showLoading();
    
    $.ajax({
        url: "get_table_data.php",
        type: "GET",
        data: Object.assign({}, currentFilters, {
            start: page * pageSize,
            length: pageSize
            // Nessun filtro conf - mostra tutte
        }),
        timeout: 30000,
        success: function(response) {
            allTransactions = response.result || [];
            renderTransactions();
            hideLoading();
        },
        error: function(xhr, status, error) {
            hideLoading();
            console.error("Error loading page:", status, error);
            if (status === "timeout" || xhr.status === 504) {
                alert("Timeout caricamento pagina. Prova con filtri piÃƒÂ¹ restrittivi.");
            } else {
                alert("Errore caricamento pagina. Riprova.");
            }
        }
    });
}

function toggleCharts() {
    const container = $('#chartsContainer');
    const icon = $('#chartsToggleIcon');
    const text = $('#chartsToggleText');

    container.slideToggle(300);

    if (container.is(':visible')) {
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        text.text('Nascondi');
        setTimeout(() => {
            acquirerChart.resize();
            circuitChart.resize();
            hourlyChart.resize();
            weekdayChart.resize();
        }, 350);
    } else {
        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        text.text('Mostra');
    }
}

function resetFilters() {
    initializeDatePickers();
    $('#minAmount, #maxAmount, #terminalID').val('');
    $('#puntoVendita').val('');
    currentPage = 0;
    loadAllData();
}

function exportToExcel() {
    const startDate = $('#startDate').val();
    const endDate = $('#endDate').val();
    const minAmount = $('#minAmount').val();
    const maxAmount = $('#maxAmount').val();
    const terminalID = $('#terminalID').val();
    const puntoVendita = $('#puntoVendita').val();
    const bu = "<?php echo $bu; ?>";

    $('#exportButton').prop('disabled', true).text('Preparazione export...');
    isExporting = true;

    // Get total row count first
    $.get('export_progress.php', {
        startDate, endDate, minAmount, maxAmount, terminalID, puntoVendita, bu
    }, function(response) {
        const totalRows = response.total;

        if (totalRows === 0) {
            alert('Nessuna transazione da esportare');
            $('#exportButton').prop('disabled', false).text('Esporta in Excel');
            isExporting = false;
            return;
        }

        // Show info message
        const $infoContainer = $(`
            <div class="alert alert-info alert-dismissible fade show mt-3" id="exportInfo">
                <strong>Preparazione export...</strong><br>
                Totale transazioni: <strong>${totalRows.toLocaleString('it-IT')}</strong><br>
                Tempo stimato: <strong>${Math.ceil(totalRows / 1000)} secondi</strong><br>
                Il download partira automaticamente.
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        `);
        $('.dashboard-container').prepend($infoContainer);

        // Create progress bar
        const $progressContainer = $(`
            <div class="progress mt-2" id="progressWrapper" style="height: 25px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                     role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    0%
                </div>
            </div>
        `);
        $('.dashboard-container').prepend($progressContainer);
        const $progressElem = $progressContainer.find('.progress-bar');

        // Update button text
        $('#exportButton').text('Download in corso...');

        // Simulate progress based on estimated time (1000 rows/second)
        const estimatedSeconds = Math.max(2, Math.ceil(totalRows / 1000));
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += (100 / (estimatedSeconds * 10)); // Update every 100ms
            if (progress >= 95) {
                progress = 95; // Cap at 95% until complete
            }
            const progStr = Math.floor(progress) + '%';
            $progressElem.css('width', progStr).attr('aria-valuenow', Math.floor(progress)).text(progStr);
        }, 100);

        // Build export URL with parameters
        const exportUrl = 'export_large.php?' + $.param({
            startDate, endDate, minAmount, maxAmount, terminalID, puntoVendita, bu
        });

        // Trigger download using invisible iframe to avoid navigation
        const $iframe = $('<iframe>', {
            src: exportUrl,
            style: 'display: none;'
        }).appendTo('body');

        // Complete progress after estimated time
        setTimeout(() => {
            clearInterval(progressInterval);
            $progressElem.css('width', '100%').attr('aria-valuenow', 100).text('100%')
                .removeClass('progress-bar-animated');

            setTimeout(() => {
                $('#progressWrapper').remove();
                $('#exportInfo').remove();
                $('#exportButton').prop('disabled', false).text('Esporta in Excel');
                isExporting = false;
                $iframe.remove();

                // Success message
                const $success = $(`
                    <div class="alert alert-success alert-dismissible fade show mt-3">
                        <i class="fas fa-check-circle"></i> <strong>Export completato!</strong><br>
                        ${totalRows.toLocaleString('it-IT')} transazioni esportate.<br>
                        Controlla la cartella dei download.
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                `);
                $('.dashboard-container').prepend($success);
                setTimeout(() => $success.fadeOut(), 5000);
            }, 1000);
        }, estimatedSeconds * 1000);

    }).fail(function() {
        alert('Errore durante la preparazione dell\'export');
        $('#exportButton').prop('disabled', false).text('Esporta in Excel');
        isExporting = false;
    });
}

function viewReceipt(trid) {
    $('#receiptModalBody').html('<iframe src="scontrino_nets.php?trid=' + trid + '" width="100%" height="500px"></iframe>');
    $('#receiptModal').modal('show');
}

function showLoading() {
    $('#loadingOverlay').addClass('active');
}

function hideLoading() {
    $('#loadingOverlay').removeClass('active');
}

// Helper functions
function getAuthCodeDisplay(code) {
    if (typeof code !== 'string' || !code || code === '' || code === null || code === undefined) {
        return '<span style="color: #ef4444; font-weight: 600;">RIFIUTATA</span>';
    }
    return '<span style="color: #10b981; font-weight: 600;">' + code + '</span>';
}

function getOperationType(code) {
    const types = {'A': 'Acquisto', 'R': 'Storno', 'N': 'Notifica', 'X': 'Manuale'};
    return types[code] || code;
}

function getCardType(code) {
    return code === 'C' ? 'Credito' : (code === 'B' ? 'Debito' : code);
}

function getGtRespDescription(code) {
    const codes = {
        '000': 'Approvata', '116': 'Fondi insufficienti', '117': 'Pin errato',
        '121': 'Limite fido superato', '100': 'Negata', '119': 'TRX non permessa',
        '118': 'Carta inesistente', '122': 'Violazione sicurezza',
        '911': 'Timeout', '912': 'Irraggiungibile', '114': 'Carta non abilitata',
        '120': 'Transazione non permessa', '200': 'Rifiutata generica',
        '904': 'Errore formato', '907': 'Emittente non disponibile',
        '909': 'Errore sistema', '910': 'Errore emittente'
    };
    return codes[code] || code;
}

function isRejected(tx) {
    // Transazione rifiutata se GtResp != '000' o no codice autorizzativo
    // Ma NON se è uno storno implicito (Conf='I' o Conf='A')
    if (isStornoImplicito(tx)) {
        return false; // Storno implicito non è un rifiuto
    }
    return tx.GtResp !== '000' || !tx.codiceAutorizzativo;
}

function isStornoImplicito(tx) {
    // Storno implicito SOLO se:
    // 1. La transazione era stata inizialmente approvata (GtResp = '000')
    // 2. Ma poi invalidata per storno implicito (Conf='I' o 'A')
    // Se GtResp != '000' è un RIFIUTO reale, non storno implicito!
    if (tx.GtResp && tx.GtResp !== '000') {
        return false; // È un rifiuto reale (PIN errato, fondi insufficienti, ecc.)
    }
    return tx.Conf === 'I' || tx.Conf === 'A' || tx.OperExpl === 'Storno implicito';
}

function getGtRespDisplay(code) {
    const desc = getGtRespDescription(code);
    return '<span style="color: #ef4444; font-weight: 600;">' + desc + '</span>';
}
</script>

</body>
</html>
