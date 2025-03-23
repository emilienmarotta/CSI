<?php

require_once("config/database.php"); // Connexion à la BDD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // On utilise trim() pour enlever les éventuels avant et après les saisies de l'utilisateur
    $email = trim($_POST["email"]); 
    $motDePasse = trim($_POST["motDePasse"]);

    $sql = "SELECT * from Utilisateur WHERE email='$email' AND motDePasse='$motDePasse'";
    $result = mysqli_query($conn, $sql);
    $num=mysqli_num_rows($result);

    // Si on a 1 et 1 seul résultat, alors on peut connecter l'utilisateur
    if($num==1){
        session_start(); // On démarre la session de l'utilisateur
        $utilisateur = $result->fetch_assoc(); // On récupère la réponse à la requête SQL précédente
        $_SESSION["loggedin"]=true; // L'utilisateur est connecté
        // On enregistre les informations de l'utilisateur dans des variables de session pour pouvoir l'utiliser sur toute l'appli durant la durée de la session
        $_SESSION['utilisateur']=$utilisateur["nomUtilisateur"]; 
        $_SESSION['typeUtilisateur']=$utilisateur["typeUtilisateur"];
        $_SESSION['idCompte']=$utilisateur['idCompte'];

        // L'état de l'utilisateur passe à 'connecté'
        $sqlChangerEtatConnexion = "UPDATE Utilisateur SET etat='connecte' WHERE idCompte='{$_SESSION['idCompte']}'";
        mysqli_query($conn, $sqlChangerEtatConnexion);

        // On redirige l'utilisateur vers le tableau de bord
        header("Location: tdb.php");
    } else {
        $erreur = "Identifiants incorrects.";
    }
    
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <h2>Connexion</h2>
    <form action="login.php" method="post"> <!-- Formulaire de connexion -->
        <label>Email :</label>
        <input type="email" name="email" required>
        <br>
        <label>Mot de passe :</label>
        <input type="password" name="motDePasse" required>
        <br>
        <?php if (!empty($erreur)) echo "<p style='color: red;'>$erreur</p>"; ?>
        <button type="submit">Se connecter</button>
    </form>
</body>
</html>
