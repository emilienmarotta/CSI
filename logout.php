<?php

require_once("config/database.php"); // Connexion à la BDD

session_start();

// On met à jour l'état de connexion du woofer
$sqlChangerEtatConnexion = "UPDATE Utilisateur SET etat='deconnecte' WHERE idCompte='{$_SESSION['idCompte']}'";
mysqli_query($conn, $sqlChangerEtatConnexion);

session_unset(); // Supprime toutes les variables de session
session_destroy(); // Détruit la session

header("Location: login.php"); 
exit();

?>