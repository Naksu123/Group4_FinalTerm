<?php
// ============================================================
//  logout.php  –  Clears the session and sends back to login
// ============================================================
session_start();

// Remove all session data
session_destroy();

// Send back to the login page
header('Location: ../index.php');
exit();
