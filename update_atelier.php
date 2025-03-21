<?php
session_start();

require_once("config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = mysqli_real_escape_string($conn, $_POST["id"]);
    $column = mysqli_real_escape_string($conn, $_POST["column"]);
    $value = mysqli_real_escape_string($conn, $_POST["value"]);

    $sql = "UPDATE Atelier SET $column = '$value' WHERE noAtelier = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>