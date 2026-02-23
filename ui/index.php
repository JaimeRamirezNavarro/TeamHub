<?php
/**
 * TeamHub - Main Router
 * Redirects to Dashboard if logged in, otherwise Login.
 */

session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;