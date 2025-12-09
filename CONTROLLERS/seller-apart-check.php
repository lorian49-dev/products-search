<?php
if (!isset($_SESSION['id_vendedor'])) {
    header("Location: seller-apart-manage-bussiness.php");
} else {
    header("Location: seller-apart-main-view.php");
}
?>
