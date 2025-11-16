<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: v2/login.php");
    exit();
} else {
    // Redirect to v2 dashboard if logged in
    header("Location: v2/index.php");
    exit();
}

// OLD VERSION (commented out - uncomment to revert)
/*
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
} else {
    header("Location: ftfs_table.php");
    exit();
}
*/
?>
