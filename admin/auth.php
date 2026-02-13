<?php
require_once __DIR__ . '/config.php';
admin_session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: /admin/index.php');
    exit;
}
