<?php

$conn = mysqli_connect("localhost", "root", "root", "farm_db");

if (!$conn){
    die("Echec de la connexion : ". mysqli_connect_error());
}

?>