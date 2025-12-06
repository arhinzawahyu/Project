<?php
session_start();

function redirect($url) {
    header("Location: $url");
    exit;
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}
?>