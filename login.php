<?php

require_once("config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $motDePasse = trim($_POST["motDePasse"]);

    $sql = "SELECT * from Utilisateur WHERE email='$email' AND motDePasse='$motDePasse'";
    $result = mysqli_query($conn, $sql);
    $num=mysqli_num_rows($result);

    if($num==1){
        session_start();
        $utilisateur = $result->fetch_assoc();
        $_SESSION["loggedin"]=true;
        $_SESSION['utilisateur']=$utilisateur["nomUtilisateur"];
        $_SESSION['typeUtilisateur']=$utilisateur["typeUtilisateur"];
        $_SESSION['idCompte']=$utilisateur['idCompte'];

        $sqlChangerEtatConnexion = "UPDATE Utilisateur SET etat='connecte' WHERE idCompte='{$_SESSION['idCompte']}'";
        mysqli_query($conn, $sqlChangerEtatConnexion);

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
    <form action="login.php" method="post">
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
