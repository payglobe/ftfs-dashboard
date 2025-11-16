<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$bu = htmlspecialchars($_SESSION['bu']);

include 'header.php';
include 'config.php';
?>

<link rel="stylesheet" href="style.css">
<style>
/* Stile per il controllo responsive (+) */
table.dataTable tbody td.dt-control {
    text-align: center;
    cursor: pointer;
}

table.dataTable tbody td.dt-control:before {
    display: inline-block;
    color: #28a745;
    font-weight: bold;
    font-size: 18px;
    content: '+';
    border: 2px solid #28a745;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    line-height: 16px;
    text-align: center;
}

table.dataTable tbody tr.parent td.dt-control:before {
    content: '−';
    background-color: #28a745;
    color: white;
}

/* Tooltip per indicare la funzionalità */
table.dataTable tbody td.dt-control {
    position: relative;
}

table.dataTable tbody td.dt-control:hover:after {
    content: 'Clicca per dettagli';
    position: absolute;
    top: -25px;
    left: 50%;
    transform: translateX(-50%);
    background: #333;
    color: white;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    white-space: nowrap;
    z-index: 1000;
}
</style>
<div class="container mt-5">
   
<h2 class="mb-4" style="font-family: 'Roboto', sans-serif; font-weight: 400; color: #202124; text-transform: none; letter-spacing: 0px; text-shadow: none;">
Transazioni
</h2>


    <!-- Filtri -->
    <div class="row mb-5">
        <div class="col-md-2">
            <label for="startDate">Data Inizio:</label>
            <input type="text" id="startDate" autocomplete="off" class="form-control datetimepicker" placeholder="Seleziona data inizio">
        </div>
        <div class="col-md-2">
            <label for="endDate">Data Fine:</label>
            <input type="text" id="endDate" autocomplete="off" class="form-control datetimepicker" placeholder="Seleziona data fine">
        </div>
        <div class="col-md-2">
            <label for="minAmount">Importo Min:</label>
            <input type="number" id="minAmount" autocomplete="off" class="form-control" placeholder="Min">
        </div>
        <div class="col-md-2">
            <label for="maxAmount">Importo Max:</label>
            <input type="number" id="maxAmount" autocomplete="off" class="form-control" placeholder="Max">
        </div>
        <div class="col-md-2">
            <label for="terminalID">TML:</label>
            <input type="text" id="terminalID" autocomplete="off" class="form-control" placeholder="Terminal ID">
        </div>
        <div class="col-md-2">
            <label for="puntoVendita">Punto Vendita:</label>
            <select id="puntoVendita" class="form-control">
                <option value="">Tutti</option>
                <!-- Options will be populated via AJAX -->
            </select>
        </div>
        <div class="row mb-2 d-flex align-items-end">
            <div class="button-group">
                <button type="button" id="filterButton" class="btn btn-primary">Filtra</button>
                <button type="button" id="resetButton" class="btn btn-secondary">Reset</button>
                <button type="button" id="exportButton" class="btn btn-success">Esporta in Excel</button>
                <div class="custom-control custom-switch ml-3" style="padding-top: 8px;">
                    <input type="checkbox" class="custom-control-input" id="autoRefreshSwitch">
                    <label class="custom-control-label" for="autoRefreshSwitch">Auto-refresh (1 min)</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Diagramma a torta -->
    <div class="row mb-3">
        <div class="col-md-6">
            <canvas id="acquirerChart"></canvas>
        </div>
        <div class="col-md-6">
            <div id="summary" class="border p-3">
                <!-- Riepilogo verrà inserito qui -->
            </div>
            <p id="queryDescription"></p>
        </div>
    </div>

    <!-- Avviso scroll -->
    <div class="alert alert-info" style="background-color: #d4edda; border-color: #c3e6cb; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
        <i class="fas fa-arrows-alt-h"></i> <strong>Info:</strong> Scorri orizzontalmente la tabella per vedere tutte le colonne (Insegna, Ragione Sociale, Indirizzo, Località, ecc.)
    </div>

    <!-- Wrapper con scroll orizzontale -->
    <div style="overflow-x: auto; margin-bottom: 20px;">
    <!-- Tabella DataTables -->
    <table id="ftfsTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Operazione</th>
                <th>Stato</th>
                <th>Importo</th>
                <th>Codifica Stab</th>
                <th>Terminal ID</th>
                <th>Terminal AQ</th>
                <th>Data Operazione</th>
                <th>Modello POS</th>

                <th>Tipo Carta</th>
                <th>Autorizzativo</th>
                <th>Acquirer</th>
                <th>Esito</th>
                <th>PAN</th>
                <th>Circuito</th>
                <th>AId</th>
                <th>Insegna</th>
                <th>Ragione Sociale</th>
                <th>Indirizzo</th>
                <th>Località</th>
                <th>Prov</th>
                <th>Cap</th>
                <th>Scontrino</th>
            </tr>
        </thead>
        <tbody>
            <!-- I dati verranno caricati qui tramite AJAX -->
        </tbody>
    </table>
    </div> <!-- Fine wrapper scroll -->

</div>

<!-- Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="receiptModalLabel">Scontrino</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="receiptModalBody">
                <!-- Il contenuto dello scontrino verrà caricato qui -->
            </div>
            <div class="modal-footer">
                <input type="email" id="emailToSend" class="form-control" placeholder="Inserisci email per l'invio">
                <button type="button" class="btn btn-primary" id="sendEmailButton">Invia Email</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>



<script>
   
    // Gestione della visualizzazione dello scontrino nella modal
    $(document).on('click', '.view-receipt', function(e) {
        e.preventDefault();
        const trid = $(this).data('trid');
        const modal = $('#receiptModal');
        const modalBody = $('#receiptModalBody');
        const sendEmailButton = $('#sendEmailButton');

        modalBody.html('<p>Caricamento dello scontrino...</p>'); // Messaggio di caricamento
        modal.modal('show'); // Mostra la modal

        $.ajax({
            url: 'scontrino_nets.php', // URL dello script che genera lo scontrino
            type: 'GET',
            data: {
                trid: trid
            }, // Invia il Trid come parametro
            success: function(response) {
                modalBody.html('<iframe src="scontrino_nets.php?trid=' + trid + '" width="100%" height="500px"></iframe>');
                sendEmailButton.data('trid', trid); // Store the trid in the button's data
            },
            error: function(error) {
                //modalBody.html('<p>Errore nel caricamento dello scontrino.</p>');


                      $.ajax({
                            url: 'scontrino.php', // URL dello script che genera lo scontrino
                            type: 'GET',
                            data: {
                                trid: trid
                            }, // Invia il Trid come parametro
                            success: function(response) {
                                modalBody.html('<iframe src="scontrino.php?trid=' + trid + '" width="100%" height="500px"></iframe>');
                                sendEmailButton.data('trid', trid); // Store the trid in the button's data
                            },
                            error: function(error) {
                                modalBody.html('<p>Errore nel caricamento dello scontrino.</p>');

                            }
                        });





            }
        });
    });

    // Gestione dell'invio dell'email
    $(document).on('click', '#sendEmailButton', function(e) {
        e.preventDefault();
        const trid = $(this).data('trid');
        const email = $('#emailToSend').val();

        if (email === '') {
            alert('Inserisci un indirizzo email.');
            return;
        }

        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Inserisci un indirizzo email valido.');
            return;
        }

        // Send the email
        $.ajax({
            url: 'send_receipt_email.php', // Create this file
            type: 'POST',
            data: {
                trid: trid,
                email: email
            },
            success: function(response) {
                alert(response); // Show the response from the server
                $('#emailToSend').val(''); // Clear the email field
                $('#receiptModal').modal('hide'); // Close the modal
            },
            error: function(error) {
                alert('Errore durante l\'invio dell\'email.');
            }
        });
    });

    $(document).ready(function() {
        $('.datetimepicker').datetimepicker({
            format: 'Y-m-d H:i:s',
            lang: 'it',
            step: 1,
            timepicker: true,
            datepicker: true,
        });

        // Set default values for startDate and endDate
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate());
        yesterday.setHours(0, 0, 0, 0); // Set to midnight

        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1); // Set to tomorrow
        //tomorrow.setHours(0, 0, 0, 0); // Set to midnight (THIS LINE IS REMOVED)

        const yesterdayFormatted = yesterday.toISOString().slice(0, 19).replace('T', ' ');
        const tomorrowFormatted = tomorrow.toISOString().slice(0, 19).replace('T', ' ');

        $('#startDate').val(yesterdayFormatted);
        $('#endDate').val(tomorrowFormatted);

        // Populate Punto Vendita dropdown
        $.ajax({
            url: 'get_punti_vendita.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                const selectBox = $('#puntoVendita');
                data.forEach(function(item) {
                    selectBox.append($('<option></option>').attr('value', item.value).text(item.label));
                });
            },
            error: function(error) {
                console.error('Errore nel caricamento dei punti vendita:', error);
            }
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let ftfsdataTable; // Changed variable name
    let acquirerChart = null; // Variable to store the chart instance
    let isExporting = false;
   function isDateRangeTooWide(startDateStr, endDateStr, maxDays = 5) {
    if (!startDateStr || !endDateStr) return false;
    const start = new Date(startDateStr);
    const end = new Date(endDateStr);
    const diffInDays = (end - start) / (1000 * 60 * 60 * 24);
    return diffInDays > maxDays;
}

    // Funzione per creare il grafico a torta
    function createAcquirerChart(data) {
        console.log("createAcquirerChart called with data:", data);
        const ctx = document.getElementById('acquirerChart').getContext('2d');
        const labels = Object.keys(data);
        const counts = Object.values(data).map(item => item.count);

        // Destroy the existing chart if it exists
        if (acquirerChart !== null) {
            acquirerChart.destroy();
        }

        acquirerChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Numero di Transazioni',
                    data: counts,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Distribuzione Transazioni per Acquirer'
                    }
                }
            }
        });
    }

    // Funzione per aggiornare il riepilogo
    function updateSummary(totalTransactions, totalAmount) {
        console.log("updateSummary called with:", totalTransactions, totalAmount);
        const summaryDiv = document.getElementById('summary');
        summaryDiv.innerHTML = `
            <p>Totale Transazioni: ${totalTransactions}</p>
            <p>Importo Totale: ${totalAmount.toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })}</p>
        `;
    }

    // Funzione per aggiornare la tabella principale
function updateMainTable() {
    if (isExporting) {
        console.log("Aggiornamento bloccato durante l'esportazione");
        return;
    }

    const startDateStr = $('#startDate').val();
    const endDateStr = $('#endDate').val();

    if (isDateRangeTooWide(startDateStr, endDateStr)) {
        $('#ftfsTable').hide();

        $('.alert').alert('close'); // Rimuove eventuali alert precedenti

        const warningDiv = `
            <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                <strong>Attenzione:</strong> Range troppo ampio. Seleziona un intervallo massimo di 5 giorni per visualizzare la tabella o in alternativa scarica le informazioni in Excel
                <button type="button" class="close" data-dismiss="alert" aria-label="Chiudi">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`;
        $('.container').prepend(warningDiv);
        return;
    }

    $('#ftfsTable').show();

    if ($.fn.DataTable.isDataTable('#ftfsTable')) {
        ftfsdataTable.destroy();
    }
    initializeDataTable();
    updateSummaryAndChart();
}
    // Funzione per inizializzare DataTables

    // Funzione per inizializzare DataTables
    function initializeDataTable() {
        console.log("initializeDataTable called");
        ftfsdataTable = $('#ftfsTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "get_table_data.php",
                "type": "GET",
                "data": function(d) {
                    // Add filter parameters to the AJAX request
                    d.startDate = $('#startDate').val();
                    d.endDate = $('#endDate').val();
                    d.minAmount = $('#minAmount').val();
                    d.maxAmount = $('#maxAmount').val();
                    d.terminalID = $('#terminalID').val();
                    d.puntoVendita = $('#puntoVendita').val();
                    d.conf = 'C'; // Add the conf parameter here
                    console.log("DataTables AJAX data:", d);
                    return d;
                },
                "dataSrc": function(json) {
                    console.log("DataTables AJAX dataSrc:", json);
                    // Correctly return the data and update the pagination information
                    json.recordsTotal = json.rowCount;
                    json.recordsFiltered = json.rowCount;
                    return json.result;
                }
            },
            "columns": [{
                "data": "TP",
                "render": function(data, type, row) {
                    if (data === 'A') {
                        return 'Acquisto';
                    } else if (data === 'R') {
                        return 'Storno';
                    } else if (data === 'N') {
                        return 'Notifica';
                    } else if (data === 'X') {
                        return 'Manuale';
                    } else {
                        return data;
                    }
                }
            }, {
              "data": "Conf",
                "render": function(data, type, row) {
                    let displayValue = '';
                    let colorClass = '';

                    if (data === 'I') {
                        displayValue = 'RIFIUTATA';
                        colorClass = 'text-danger';
                    } else if (data === 'C') {
                        colorClass = 'text-success';
                        displayValue = 'CONFERMATA';
                    } else if (data === 'D') {
                        displayValue = 'RIFIUTATA';
                        colorClass = 'text-danger';
                    } else if (data === 'A') {
                        displayValue = 'RIFIUTATA';
                        colorClass = 'text-danger';
                    } else if (data === 'E') {
                        displayValue = 'STORNO.';
                    } else if (data === 'N') {
                        displayValue = 'PraAuth confermata';
                    } else {
                        colorClass = 'text-warning';
                        displayValue = "ANOMALIA" +data;
                    }

                    return `<span class="${colorClass}">${displayValue}</span>`;
                },
                "visible": true
            }, {
                "data": "importo",
                "render": $.fn.dataTable.render.number('.', ',', 2, '€ ')
            }, {
                "data": "codificaStab",
                    "render": function(data, type, row) {
                    if (data.startsWith('0001024') ) { return data.substring(7);}
                    else {
                        return data;
                      }
                  }
            },
            {
                "data": "terminalID"
            },  {
                "data": "terminal"
            },{
                "data": "dataOperazione",
                "render": function(data, type, row) {
                    if (data && new Date(data) != "Invalid Date") {
                        return new Date(data).toLocaleDateString('it-IT', {
                            year: 'numeric',
                            month: '2-digit',
                            day: '2-digit',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit'
                        });
                    } else {
                        return "";
                    }
                }
            }, {
                "data": "Modello_pos"
            },  {
                "data": "TPC",
                "render": function(data, type, row) {
                    if (data === 'C') {
                        return 'Credito';
                    } else if (data === 'B') {
                        return 'Debito';
                    } else {
                        return data;
                    }
                },
                "visible": true
            }, {
                "data": "codiceAutorizzativo",
                "render": function(data, type, row) {
                    if (typeof data != "string" ) {
                         colorClass = 'text-danger'; 
                         displayValue = "RIFIUTATA";
                        return `<span class="${colorClass}">${displayValue}</span>`;
                    } 
                     else {
                         colorClass = 'text-success'; 
                         return    `<span class="${colorClass}">${data}</span>`;
                    }
                }
            }, {
                "data": "acquirer"
            },{
                "data": "GtResp",  
                "render": function(data, type, row) {
                      if (data === '117') {
                        return 'Pin errato';
                    } else if (data === '121') {
                        return 'Limite fido superato';
                    } else if (data === '116') {
                        return 'Fondi insufficienti';
                    } else if (data === '000') {
                        return 'Aprovata';
                     } else if (data === '100') {
                        return 'Negata';
                    }
                     else if (data === '119') {
                        return 'TRX non permessa al titolare';
                    }
                      else if (data === '118') {
                        return 'Carta inesistente';
                    }
                     else if (data === '122') {
                        return 'Violazione di sicurezza';
                    }
                    else if (data === '911') {
                        return 'Emettitore carta Timeout';
                    }
                    else if (data === '912') {
                        return 'Emettitore carta Irraggiungibile';
                    }   
                    else {
                        return data; // Return the original value if it's not ..
                    }
                }
            }, {
                "data": "pan"
            },{
                "data": "PosAcq"
            }, {
                "data": "AId",
                "visible": false
            }, {
                "data": "insegna",
                "visible": true,
                "defaultContent": "-"
            }, {
                "data": "Ragione_Sociale",
                "visible": true,
                "defaultContent": "-"
            }, {
                "data": "indirizzo",
                "visible": true,
                "defaultContent": "-"
            }, {
                "data": "localita",
                "visible": true,
                "defaultContent": "-"
            }, {
                "data": "prov",
                "visible": true,
                "defaultContent": "-"
            }, {
                "data": "cap",
                "visible": false
            }, {
                "data": "Trid",
                "type": "string", // Tell DataTables to treat Trid as a string
                "render": function(data, type, row) {
                    return '<a href="#" class="view-receipt" data-trid="' + data + '">Visualizza</a>';
                }
            }],
            "responsive": false, // Disable responsive
            "scrollX": true, // Enable horizontal scroll
            "order": [
                [6, 'desc']
            ], // Order by date descending
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/it-IT.json"
            },
            dom: 'Blfrtip',
            buttons: [{
                extend: 'colvis',
                columns: ':not(:first-child)'
            }],
            "autoWidth": false,
            "stateSave": true // Save table state
        });
    }


    // Gestione dei filtri
    document.getElementById('filterButton').addEventListener('click', function() {
        console.log("filterButton clicked");
        // Reload DataTables
        updateMainTable();
        updateSummaryAndChart();
    });

    // Gestione del reset dei filtri
    document.getElementById('resetButton').addEventListener('click', function() {
        console.log("resetButton clicked");
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate());
        yesterday.setHours(0, 0, 0, 0); // Set to midnight

        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1); // Set to tomorrow

        const yesterdayFormatted = yesterday.toISOString().slice(0, 19).replace('T', ' ');
        const tomorrowFormatted = tomorrow.toISOString().slice(0, 19).replace('T', ' ');

        $('#startDate').val(yesterdayFormatted);
        $('#endDate').val(tomorrowFormatted);

        $('#minAmount').val('');
        $('#maxAmount').val('');
        $('#terminalID').val('');
        $('#puntoVendita').val('');
        // Reload DataTables
        updateMainTable();
        updateSummaryAndChart();
    });

    // Gestione dell'esportazione in Excel
  document.getElementById('exportButton').addEventListener('click', function(e) {
    e.preventDefault();
    console.log("🔄 Avviata esportazione...");

    const startDate = $('#startDate').val();
    const endDate = $('#endDate').val();
    const minAmount = $('#minAmount').val();
    const maxAmount = $('#maxAmount').val();
    const terminalID = $('#terminalID').val();
    const puntoVendita = $('#puntoVendita').val();
    const bu = "<?php echo $bu; ?>";

    $('#exportButton').prop('disabled', true).text('Elaborazione in corso...');
    isExporting = true;
    // CREA la barra di avanzamento
    const $progressContainer = $(`
        <div class="progress mt-2" id="progressWrapper">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                style="width: 10%;" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">10%</div>
        </div>
    `);
    $('.container').prepend($progressContainer);
    const $progressElem = $progressContainer.find('.progress-bar');

    // Avvia chiamata per iniziare export
    $.get('export_async.php', {
        startDate,
        endDate,
        minAmount,
        maxAmount,
        terminalID,
        puntoVendita,
        bu
    }, function(response) {
        const token = JSON.parse(response).token;
        console.log("Token ricevuto:", token);
        let visualProgress = 10; // iniziamo dal 10%

const interval = setInterval(() => {
    $.get('check_export_status.php', { token, _: Date.now() }, function(status) {
        const res = JSON.parse(status);
        console.log("📊 Stato export:", res);

        const $progressElem = $('#progressWrapper .progress-bar');

        if (res.progress !== undefined) {
            const actualProgress = Math.max(10, res.progress);

            // Se siamo sotto il valore reale, aggiorniamo subito
            if (visualProgress < actualProgress) {
                visualProgress = actualProgress;
            } else if (visualProgress < 99) {
                // Incremento fittizio per simulare il movimento fluido
                visualProgress = Math.min(visualProgress + 0.4, 99);
            }

            const progStr = visualProgress.toFixed(1) + '%';
            $progressElem.css('width', progStr);
            $progressElem.attr('aria-valuenow', visualProgress.toFixed(1));
            $progressElem.text(progStr);
            console.log("➡️ Avanzamento visuale:", progStr);
        }

        if (res.ready) {

            clearInterval(interval);
            $('#exportButton').prop('disabled', false).text('Esporta in Excel');
            console.log("✅ Esportazione completata, file pronto:", res.url);

            $progressElem.removeClass('progress-bar-animated progress-bar-striped').addClass('bg-success');
            $progressElem.css('width', '100%').attr('aria-valuenow', 100).text('100%');

            // Avvia il download automaticamente
            const downloadLink = document.createElement('a');
            downloadLink.href = res.url;
            downloadLink.download = res.url.split('/').pop(); // Estrae il nome del file
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);

            // Mostra messaggio di conferma
            const $alert = $(`
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    Il file Excel è stato scaricato. Se il download non è partito, <a href="${res.url}" class="alert-link" download>clicca qui</a>.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Chiudi">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `);
            $('.container').prepend($alert);

            // Rimuovi la barra di progresso dopo un breve delay
            setTimeout(() => {
                $('#progressWrapper').remove();
                isExporting = false;
            }, 1500);
        }
    });
}, 2000); // intervallo ogni 2 secondi
       
    });
});

    // Funzione per aggiornare il riepilogo e il grafico
    function updateSummaryAndChart() {
        console.log("updateSummaryAndChart called");
    if (isExporting) {
            console.log("Aggiornamento bloccato durante l'esportazione");
            return;
        }



        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        const minAmount = document.getElementById('minAmount').value;
        const maxAmount = document.getElementById('maxAmount').value;
        const terminalID = document.getElementById('terminalID').value;
        const puntoVendita = document.getElementById('puntoVendita').value;
        const searchValue = ftfsdataTable.search(); // Get the current search term
        console.log("updateSummaryAndChart filter values:", startDate, endDate, minAmount, maxAmount, terminalID, puntoVendita, searchValue);

        $.ajax({
            url: 'get_summary.php',
            type: 'GET',
            data: {
                startDate: startDate,
                endDate: endDate,
                minAmount: minAmount,
                maxAmount: maxAmount,
                terminalID: terminalID,
                puntoVendita: puntoVendita,
                searchValue: searchValue, // Pass the search term
                conf: 'E,C' // Add the conf parameter here
            },
            success: function(response) {
                console.log("updateSummaryAndChart success:", response);
                const summaryDiv = document.getElementById('summary');
                summaryDiv.innerHTML = `
                <p>Totale Transazioni: ${response.totalTransactions}</p>
                <p>Importo Totale: ${parseFloat(response.totalAmount).toLocaleString('it-IT', { style: 'currency', currency: 'EUR' })}</p>
            `;
                createAcquirerChart(response.acquirerData);
                updateSummary(response.totalTransactions, response.totalAmount);
                // Update the query description
                document.getElementById('queryDescription').textContent = response.queryDescription;
            },
            error: function(error) {
                console.error('Errore nella comunicazione con get_summary.php:', error);
            }
        });
    }


    $(document).ready(function() {
        // Initialize DataTables
       // initializeDataTable();
        // Update summary and chart on page load
       // updateSummaryAndChart();

            updateMainTable();

        // Auto-refresh management
        let autoRefreshInterval = null;

        function triggerFilter() {
            console.log("triggerFilter called");
            document.getElementById('filterButton').click();
        }

        // Handle auto-refresh switch
        $('#autoRefreshSwitch').on('change', function() {
            if (this.checked) {
                console.log("Auto-refresh attivato");
                // Start auto-refresh every 60 seconds
                autoRefreshInterval = setInterval(triggerFilter, 60000);
            } else {
                console.log("Auto-refresh disattivato");
                // Stop auto-refresh
                if (autoRefreshInterval) {
                    clearInterval(autoRefreshInterval);
                    autoRefreshInterval = null;
                }
            }
        });
    });
</script>

<?php include 'footer.php'; ?>
