<?php
/**
 * FTFS Dashboard v2.0 - Possibili Storni Impliciti
 *
 * Pagina per monitorare le transazioni con Conf=' ' che potrebbero
 * diventare storni impliciti se il terminale effettua una nuova operazione
 * con lo stesso NumOper.
 *
 * @author Claude Code
 * @version 2.0
 * @date Gennaio 2026
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
    <title>Possibili Storni - FTFS Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>
</head>
<body>

<!-- Top Navigation Bar -->
<header style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000;">
    <div class="container-fluid" style="max-width: 1600px; padding: 1rem 1.5rem;">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <div class="d-flex align-items-center" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 0.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.3);">
                    <i class="fas fa-exclamation-triangle" style="color: white; font-size: 1.5rem;"></i>
                </div>
                <div style="margin-left: 0.75rem;">
                    <h1 class="mb-0" style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">
                        Possibili Storni Impliciti
                    </h1>
                    <p class="mb-0" style="font-size: 0.875rem; color: #6b7280;">Monitoraggio transazioni non confermate</p>
                </div>
            </div>
            <a href="logout.php" class="btn d-flex align-items-center" style="background: #fee2e2; color: #dc2626; border: none; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600;">
                <i class="fas fa-sign-out-alt" style="margin-right: 0.5rem;"></i> Esci
            </a>
        </div>

        <!-- Navigation Menu -->
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
            <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                <a href="index.php" class="btn d-flex align-items-center" style="background: #f3f4f6; color: #374151; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; border: none;">
                    <i class="fas fa-home" style="margin-right: 0.5rem;"></i> Home
                </a>
                <a href="storni.php" class="btn d-flex align-items-center" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-weight: 600; border: none; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i> Possibili Storni
                </a>
            </div>
        </div>
    </div>
</header>

<style>
* { box-sizing: border-box; }
body { background: #f5f7fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }

.dashboard-container { max-width: 1600px; margin: 0 auto; padding: 20px; }

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }

.stat-card {
    background: white; padding: 20px; border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08); position: relative; overflow: hidden;
}
.stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 4px; }
.stat-card.warning::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
.stat-card.danger::before { background: linear-gradient(90deg, #ef4444, #dc2626); }
.stat-card.success::before { background: linear-gradient(90deg, #10b981, #059669); }
.stat-card.info::before { background: linear-gradient(90deg, #3b82f6, #2563eb); }

.stat-icon { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px; }
.stat-label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600; }
.stat-value { font-size: 28px; font-weight: 700; color: #1f2937; margin: 5px 0; }

.filter-panel { background: white; padding: 20px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px; }
.filter-row { display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; }
.filter-group label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 5px; }
.filter-group input, .filter-group select {
    padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;
}
.filter-group input:focus, .filter-group select:focus { outline: none; border-color: #f59e0b; }

.btn-modern { padding: 10px 20px; border: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; }
.btn-warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; }
.btn-warning:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4); }
.btn-secondary { background: #e5e7eb; color: #374151; }

.table-container { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; }
.table-header { padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
.table-title { font-size: 18px; font-weight: 700; color: #1f2937; }

table { width: 100%; border-collapse: collapse; }
thead { background: #f9fafb; }
th { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }
td { padding: 14px 16px; border-bottom: 1px solid #f3f4f6; font-size: 14px; color: #374151; }
tr:hover { background: #f9fafb; }

.status-badge {
    padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase;
}
.status-storno-certo { background: #fee2e2; color: #dc2626; }
.status-possibile-storno { background: #fef3c7; color: #92400e; }
.status-in-attesa { background: #dbeafe; color: #1d4ed8; }
.status-resettato { background: #e5e7eb; color: #374151; }
.status-confermata { background: #d1fae5; color: #065f46; }

.auth-badge { padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
.auth-yes { background: #d1fae5; color: #065f46; }
.auth-no { background: #f3f4f6; color: #6b7280; }

.empty-state { text-align: center; padding: 60px 20px; }
.empty-icon { font-size: 60px; color: #d1d5db; margin-bottom: 15px; }
.empty-title { font-size: 20px; font-weight: 700; color: #374151; margin-bottom: 8px; }
.empty-text { font-size: 14px; color: #6b7280; }

.loading { text-align: center; padding: 40px; }
.loading i { font-size: 40px; color: #f59e0b; animation: spin 1s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.amount-risk { color: #dc2626; font-weight: 700; }
</style>

<div class="dashboard-container">

    <!-- Stats Cards -->
    <div class="stats-grid" id="statsGrid">
        <div class="stat-card danger">
            <div class="stat-icon" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-times-circle"></i></div>
            <div class="stat-label">Gia Stornate</div>
            <div class="stat-value" id="statStornoCerto">-</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon" style="background: #fef3c7; color: #92400e;"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-label">A Rischio Storno</div>
            <div class="stat-value" id="statPossibileStorno">-</div>
        </div>
        <div class="stat-card info">
            <div class="stat-icon" style="background: #dbeafe; color: #1d4ed8;"><i class="fas fa-clock"></i></div>
            <div class="stat-label">In Attesa (&lt;5 min)</div>
            <div class="stat-value" id="statInAttesa">-</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon" style="background: #fef3c7; color: #92400e;"><i class="fas fa-euro-sign"></i></div>
            <div class="stat-label">Importo a Rischio</div>
            <div class="stat-value" id="statImportoRischio">-</div>
        </div>
    </div>

    <!-- Legenda -->
    <div style="background: white; padding: 15px 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 20px; align-items: center;">
        <span style="font-weight: 600; color: #374151; font-size: 13px;"><i class="fas fa-info-circle"></i> Legenda:</span>
        <span><span class="status-badge status-storno-certo">Gia Stornata</span> <small style="color:#6b7280;font-size:11px;">NextNumOper = NumOper</small></span>
        <span><span class="status-badge status-possibile-storno">A Rischio</span> <small style="color:#6b7280;font-size:11px;">Nessuna TX dopo 5+ min</small></span>
        <span><span class="status-badge status-in-attesa">In Attesa</span> <small style="color:#6b7280;font-size:11px;">&lt; 5 min</small></span>
        <span><span class="status-badge status-resettato">Resettato</span> <small style="color:#6b7280;font-size:11px;">Terminale riavviato</small></span>
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
                <label><i class="fas fa-filter"></i> Solo Critici</label>
                <select id="onlyCritical">
                    <option value="false">Tutti</option>
                    <option value="true">Solo Critici</option>
                </select>
            </div>
            <button class="btn-modern btn-warning" id="filterButton">
                <i class="fas fa-search"></i> Cerca
            </button>
            <button class="btn-modern btn-secondary" id="resetButton">
                <i class="fas fa-redo"></i> Reset
            </button>
            <button class="btn-modern" id="exportButton" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                <i class="fas fa-file-excel"></i> Esporta Excel
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <div class="table-header">
            <div class="table-title"><i class="fas fa-list"></i> Transazioni Non Confermate</div>
            <div id="tableCount" style="font-size: 14px; color: #6b7280;"></div>
        </div>
        <div id="tableBody">
            <div class="loading"><i class="fas fa-spinner"></i><p>Caricamento...</p></div>
        </div>
    </div>

</div>

<script>
$(document).ready(function() {
    // Init datetime pickers
    $('.datetimepicker').datetimepicker({
        format: 'Y-m-d H:i:s',
        step: 15
    });

    // Default dates: last 7 days
    var now = new Date();
    var sevenDaysAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
    $('#startDate').val(formatDate(sevenDaysAgo));
    $('#endDate').val(formatDate(now));

    function formatDate(d) {
        return d.getFullYear() + '-' +
               String(d.getMonth() + 1).padStart(2, '0') + '-' +
               String(d.getDate()).padStart(2, '0') + ' ' +
               String(d.getHours()).padStart(2, '0') + ':' +
               String(d.getMinutes()).padStart(2, '0') + ':' +
               String(d.getSeconds()).padStart(2, '0');
    }

    function loadData() {
        $('#tableBody').html('<div class="loading"><i class="fas fa-spinner"></i><p>Caricamento...</p></div>');

        $.ajax({
            url: 'get_pending_confirmations.php',
            data: {
                startDate: $('#startDate').val(),
                endDate: $('#endDate').val(),
                onlyCritical: $('#onlyCritical').val()
            },
            success: function(response) {
                if (response.success) {
                    updateStats(response.stats);
                    renderTable(response.data);
                } else {
                    $('#tableBody').html('<div class="empty-state"><div class="empty-icon"><i class="fas fa-exclamation-circle"></i></div><div class="empty-title">Errore</div><div class="empty-text">' + (response.error || 'Errore sconosciuto') + '</div></div>');
                }
            },
            error: function() {
                $('#tableBody').html('<div class="empty-state"><div class="empty-icon"><i class="fas fa-wifi"></i></div><div class="empty-title">Errore di connessione</div><div class="empty-text">Impossibile connettersi al server</div></div>');
            }
        });
    }

    function updateStats(stats) {
        $('#statStornoCerto').text(stats.storno_implicito_certo);
        $('#statPossibileStorno').text(stats.possibile_storno_implicito);
        $('#statInAttesa').text(stats.in_attesa);
        $('#statImportoRischio').html('&euro;' + stats.importo_a_rischio.toLocaleString('it-IT', {minimumFractionDigits: 2}));
    }

    function renderTable(data) {
        if (data.length === 0) {
            $('#tableBody').html('<div class="empty-state"><div class="empty-icon"><i class="fas fa-check-circle" style="color: #10b981;"></i></div><div class="empty-title">Nessuna transazione trovata</div><div class="empty-text">Non ci sono transazioni non confermate nel periodo selezionato</div></div>');
            $('#tableCount').text('');
            return;
        }

        $('#tableCount').text(data.length + ' transazioni');

        var html = '<table><thead><tr>' +
            '<th>Stato</th>' +
            '<th>Terminal ID</th>' +
            '<th>NumOper</th>' +
            '<th>Data/Ora</th>' +
            '<th>Importo</th>' +
            '<th>Autorizzata</th>' +
            '<th>Cod. Aut.</th>' +
            '<th>Next NumOper</th>' +
            '<th>Minuti Dopo</th>' +
            '<th>Punto Vendita</th>' +
            '</tr></thead><tbody>';

        data.forEach(function(tx) {
            var statusClass = getStatusClass(tx.stato);
            var statusLabel = getStatusLabel(tx.stato);

            html += '<tr>' +
                '<td><span class="status-badge ' + statusClass + '">' + statusLabel + '</span></td>' +
                '<td style="font-family: monospace; font-weight: 600;">' + tx.termId + '</td>' +
                '<td style="font-family: monospace;">' + tx.numOper + '</td>' +
                '<td>' + tx.dataNonConfermata + '</td>' +
                '<td class="' + (tx.autorizzata ? 'amount-risk' : '') + '">&euro;' + tx.amount.toLocaleString('it-IT', {minimumFractionDigits: 2}) + '</td>' +
                '<td><span class="auth-badge ' + (tx.autorizzata ? 'auth-yes' : 'auth-no') + '">' + (tx.autorizzata ? 'SI' : 'NO') + '</span></td>' +
                '<td style="font-family: monospace;">' + (tx.apprNum || '-') + '</td>' +
                '<td style="font-family: monospace;">' + (tx.nextNumOper || '-') + '</td>' +
                '<td>' + (tx.minutiDopo !== null ? tx.minutiDopo + ' min' : (tx.minutiDaOra + ' min fa')) + '</td>' +
                '<td>' + (tx.insegna || '-') + '</td>' +
                '</tr>';
        });

        html += '</tbody></table>';
        $('#tableBody').html(html);
    }

    function getStatusClass(stato) {
        switch(stato) {
            case 'STORNO_IMPLICITO_CERTO': return 'status-storno-certo';
            case 'POSSIBILE_STORNO_IMPLICITO': return 'status-possibile-storno';
            case 'IN_ATTESA': return 'status-in-attesa';
            case 'CONTATORE_RESETTATO': return 'status-resettato';
            default: return '';
        }
    }

    function getStatusLabel(stato) {
        switch(stato) {
            case 'STORNO_IMPLICITO_CERTO': return 'Gia Stornata';
            case 'POSSIBILE_STORNO_IMPLICITO': return 'A Rischio';
            case 'IN_ATTESA': return 'In Attesa';
            case 'CONTATORE_RESETTATO': return 'Resettato';
            case 'CONTATORE_SALTATO': return 'Saltato';
            default: return stato;
        }
    }

    // Event handlers
    $('#filterButton').click(loadData);
    $('#exportButton').click(function() {
        var params = new URLSearchParams({
            startDate: $('#startDate').val(),
            endDate: $('#endDate').val(),
            onlyCritical: $('#onlyCritical').val()
        });
        window.location.href = 'export_storni.php?' + params.toString();
    });
    $('#resetButton').click(function() {
        var now = new Date();
        var sevenDaysAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
        $('#startDate').val(formatDate(sevenDaysAgo));
        $('#endDate').val(formatDate(now));
        $('#onlyCritical').val('false');
        loadData();
    });

    // Initial load
    loadData();

    // Auto-refresh every 60 seconds
    setInterval(loadData, 60000);
});
</script>

</body>
</html>
