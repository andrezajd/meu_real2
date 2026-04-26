<?php
session_start();
session_destroy(); // Mata todas as sessões
header("Location: login.php"); // Manda pro login
exit();
?>