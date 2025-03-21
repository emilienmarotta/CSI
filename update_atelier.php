<?php
session_start(); // Permet d'utiliser les variables de session

require_once("config/database.php"); // Connexion à la BDD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = mysqli_real_escape_string($conn, $_POST["id"]);
    $col = mysqli_real_escape_string($conn, $_POST["column"]);
    $value = mysqli_real_escape_string($conn, $_POST["value"]);

    // On met à jour l'atelier
    $sql = "UPDATE Atelier SET $col = '$value' WHERE noAtelier = '$id'";

    if (mysqli_query($conn, $sql)) {
        echo "L'exécution a réussi";
    } else {
        echo "L'exécution a échoué";
    }
}
?>