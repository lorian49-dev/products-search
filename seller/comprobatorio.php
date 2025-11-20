<?php
if (!isset($_SESSION['id_vendedor'])) {
    header("Location: ../seller/gestionar-negocio.php");
} else {
    header("Location: ../seller/dashboardSeller.php");
}
?>