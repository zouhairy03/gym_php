<?php
session_start();

if (isset($_POST['gender'])) {
    if ($_POST['gender'] === 'women') {
        $_SESSION['viewed_women_notifications'] = $_SESSION['viewed_women_notifications'] ?? 0;
    } elseif ($_POST['gender'] === 'men') {
        $_SESSION['viewed_men_notifications'] = $_SESSION['viewed_men_notifications'] ?? 0;
    }
}
?>
