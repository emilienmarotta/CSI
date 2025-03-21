<?php
    require_once("config/database.php"); // On importe la BDD

    session_start(); // Permet d'utiliser les variables de session
    
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { // On vérifie si l'utilisateur est connecté
        header("Location: login.php");
        exit();
    }    
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <?php include_once("menu.php") ?>
    <h2>Tableau de bord</h2>
    <p>Accès <?php echo $_SESSION["typeUtilisateur"]?></p>
    <div>
        <h3>Aperçu des stocks</h3>
        <ul>
        <?php
            $sql = "SELECT * from Produit";
            $result = mysqli_query($conn, $sql);
            $num=mysqli_num_rows($result);
            
            while ($produit = $result->fetch_assoc()) {
                echo "<li>{$produit['nom']} : {$produit['quantite']}</li>";
            }
        ?>
        </ul>
    </div>
    <div>
        <h3>Woofers présents</h3>
        <ul>
        <?php
            $sql = "SELECT u.nomUtilisateur AS nomUtilisateur from Woofer w JOIN Utilisateur u ON w.idCompte = u.idCompte";
            $result = mysqli_query($conn, $sql);
            $num=mysqli_num_rows($result);
            
            while ($woofer = $result->fetch_assoc()) {
                echo "<li>{$woofer['nomUtilisateur']}</li>";
            }
        ?>
        </ul>
    </div>
    <div>
        <h3>Statistiques, graphiques, chiffres</h3>
        
    </div>
    <div>
        <h3>Ventes récentes</h3>
        
    </div>
    <div>
        <h3>Ateliers à venir</h3>
        
    </div>
</body>
</html>

