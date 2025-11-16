<?php
session_start(); // Assicurati che la sessione sia avviata
?>
<!DOCTYPE html>
<html lang="it">
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- Aggiunto Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
   
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.it.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>


</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="#">
            <div style="width: 182px; height: 42px;">
                <svg preserveAspectRatio="xMidYMid meet" data-bbox="-0.017 -0.004 761.147 177.121" viewBox="0 0 761.13 177.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg" data-type="ugc" role="presentation" aria-hidden="true" aria-label="">
                    <g>
                        <defs>
                            <linearGradient gradientUnits="userSpaceOnUse" y2="17.34" x2="109.39" y1="17.34" x1="74.71" id="c098f476-78fe-4e15-a134-aee2def839e6_comp-m0nn9rq4">
                                <stop stop-color="#488dec" offset="0"></stop>
                                <stop stop-color="#9a1bf1" offset="1"></stop>
                            </linearGradient>
                            <linearGradient xlink:href="#c098f476-78fe-4e15-a134-aee2def839e6_comp-m0nn9rq4" y2="106.75" x2="154.73" y1="106.75" x1="0" id="8ec02c78-373e-41e0-a110-f2333f0c117f_comp-m0nn9rq4"></linearGradient>
                        </defs>
                        <g>
                            <path d="M431.25 46.38c20.22 0 30.1 10 30.1 10l-8.73 10.34s-7.7-6.78-21.03-6.78c-20.68 0-34.01 17.12-34.01 34.7 0 14.36 9.65 22.52 22.4 22.52s22.29-8.85 22.29-8.85l1.84-9.19h-12.64L434 86.37h25.85l-8.39 42.97h-12.64l.8-4.02c.34-1.72.81-3.45.81-3.45h-.23s-8.5 8.85-23.32 8.85c-18.73 0-34.58-13.44-34.58-35.16 0-26.66 21.83-49.18 48.94-49.18" fill="#ffffff"></path>
                            <path fill="#ffffff" d="M484.11 47.76h14.82l-13.44 68.83h35.27l-2.65 12.75h-49.86z"></path>
                            <path d="M577.86 46.39c22.4 0 37.23 14.71 37.23 35.04 0 26.65-23.78 49.29-48.37 49.29-22.52 0-37.11-15.05-37.11-35.85 0-26.2 23.44-48.49 48.26-48.49m-11.03 70.77c16.31 0 32.97-15.51 32.97-34.81 0-13.33-9.08-22.4-22.06-22.4-16.66 0-32.97 15.28-32.97 34.12 0 13.67 9.08 23.09 22.06 23.09" fill="#ffffff"></path>
                            <path d="M637.85 47.76h26.2c4.71 0 8.85.46 12.29 1.72 7.47 2.64 11.83 8.73 11.83 16.77 0 8.62-5.28 16.43-13.33 20.11v.23c6.55 2.41 9.88 8.62 9.88 15.85 0 12.52-7.81 21.26-18.27 24.93-3.91 1.38-8.16 1.95-12.41 1.95h-32.05l15.85-81.57Zm17.23 68.82c2.53 0 4.83-.46 6.78-1.5 4.59-2.41 7.7-7.35 7.7-12.98s-3.56-9.08-9.88-9.08h-15.85l-4.6 23.56zm5.4-35.5c7.24 0 12.41-5.86 12.41-12.87 0-4.48-2.64-7.7-8.62-7.7h-14.13l-4.02 20.57z" fill="#ffffff"></path>
                            <path fill="#ffffff" d="M712.42 47.76h48.71l-2.52 12.76H724.6l-4.14 21.37h27.46l-2.53 12.75h-27.46l-4.25 21.95h35.85l-2.41 12.75h-50.67z"></path>
                            <path d="M189.47 48.68h29.64c14.82 0 25.51 10 25.51 25.39S233.94 99.8 219.11 99.8h-18.27v29.99h-11.37zm27.8 41.25c9.77 0 15.74-6.09 15.74-15.85s-5.97-15.51-15.63-15.51h-16.54v31.37h16.43Z" fill="#ffffff"></path>
                            <path d="M297.02 106.47h-30.56l-8.04 23.32H246.7l29.18-81.12h11.95l29.18 81.12h-11.83zm-15.28-46.65s-1.84 7.35-3.22 11.49l-9.08 25.73h24.59l-8.96-25.73c-1.38-4.14-3.1-11.49-3.1-11.49z" fill="#ffffff"></path>
                            <path d="m341.14 95.44-27.23-46.76h12.87l15.05 26.66c2.53 4.48 4.94 10.23 4.94 10.23h.23s2.41-5.63 4.94-10.23l14.82-26.66h12.87l-27.11 46.76v34.35h-11.38z" fill="#ffffff"></path>
                            <path d="M103.24 30.59c7.31-6.18 8.23-17.12 2.06-24.44-6.19-7.32-17.12-8.24-24.44-2.06-7.31 6.18-8.24 17.12-2.06 24.44 6.18 7.31 17.12 8.24 24.44 2.06" fill="url(#c098f476-78fe-4e15-a134-aee2def839e6_comp-m0nn9rq4)"></path>
                            <path d="M139.67 142.35c28.74-41.92 13.35-99.92-20.92-105.49-41.38-6.95-66.36 65.65-58.42 65.09 4.6-.32 16.89-20 31.61-32.54 5.54-4.7 15.12-11.34 21.88-8.72 6.4 2.54 9.62 12.45 10.5 19.65 2.1 16.81-4.75 32.71-9.57 38.9-13.07 16.81-31.6 26.31-46.84 27.13-24.12 1.29-45.56-14.91-52.06-37.52-6.55-22.71 2.28-50.29 26.54-68.58 3.45-2.59 5.56-3.81 5.55-3.83 0 0-33.33 11.61-44.62 47.17-18.78 58.97 45.13 114.34 106.28 85.75 5.61-2.72 15.19-9.32 23.24-18.33-.03.04-.07.07-.1.11.15-.16.31-.33.45-.49 2.36-2.67 4.51-5.44 6.45-8.28" fill="url(#8ec02c78-373e-41e0-a110-f2333f0c117f_comp-m0nn9rq4)"></path>
                        </g>
                    </g>
                </svg>
                 <span style="font-size: 0.4em;font-weight:lighter:white;">a GUM Group Company</span> 
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item <?php if (basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home"></i> <!-- Icona Home -->
                        <span class="link-text">Home</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> <!-- Icona Logout -->
                        <span class="link-text">Logout</span>
                    </a>
                </li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user"></i> <!-- Icona User -->
                            <span class="link-text">Benvenuto, <?php echo $_SESSION['username']; ?></span>
                        </span>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
