<?php
require_once("config/database.php"); // Connexion à la base de données
session_start(); // Permet d'utiliser les variables de session

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { // On vérifie si l'utilisateur est authentifié
    header("Location: login.php"); // Si l'utilisateur n'est pas connecté, on le renvoit vers la page de connexion
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
    <?php include_once("menu.php"); ?> <!-- On importe le menu -->
    <h2>Tableau de bord</h2>
    <div id="welcome">
        <p>Bonjour <strong><?php echo $_SESSION["utilisateur"]; ?></strong> !<br>Bienvenue sur votre tableau de bord.</p>
    </div>

    <section>
        <h3>Statistiques globales</h3>
        <ul>
            <?php
            // On affiche le nombre de produits, ventes et woofers
            $totalProduits = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Produit"))['total'];
            $totalVentes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Vente"))['total'];
            $totalWoofers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM Woofer"))['total'];

            echo "<li>Total de produits : <strong>{$totalProduits}</strong></li>";
            echo "<li>Total de ventes : <strong>{$totalVentes}</strong></li>";
            echo "<li>Total de woofers : <strong>{$totalWoofers}</strong></li>";
            ?>
        </ul>
    </section>

    <div id="big-box-tdb">
        <section>
            <h3>Aperçu des stocks</h3>
            <ul>
                <?php
                // On affiche les 5 produits dont la quantité est la plus élevée 
                $sqlStocks = "SELECT nom, quantite FROM Produit ORDER BY quantite DESC LIMIT 5"; 
                $resultStocks = mysqli_query($conn, $sqlStocks);
                while ($row = mysqli_fetch_assoc($resultStocks)) { // Pour chaque ligne de la réponse à notre requête SQL
                    echo "<li><strong>{$row['nom']}</strong> : {$row['quantite']} en stock</li>";
                }
                ?>
            </ul>
            <a href="stocks.php">Gérer les stocks</a>
        </section>

        <section>
            <h3>Woofers connectés</h3>
            <ul>
                <?php
                // On affiche les woofers connectés
                $sqlWoofersConnectes = "SELECT u.nomUtilisateur FROM Utilisateur u WHERE u.etat = 'connecte' AND u.typeUtilisateur = 'Woofer'";
                $resultWoofersConnectes = mysqli_query($conn, $sqlWoofersConnectes);
                while ($row = mysqli_fetch_assoc($resultWoofersConnectes)) { // Pour chaque ligne de la réponse à notre requête SQL
                    echo "<li>{$row['nomUtilisateur']}</li>";
                }
                ?>
            </ul>
            <a href="woofers.php">Gérer les woofers</a>
        </section>

        <section>
            <h3>Ventes récentes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Numéro</th>
                        <th>Woofer</th>
                        <th>Prix TTC</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // On affiche les 5 ventes les plus récentes
                    $sqlVentesRecentes = "
                        SELECT v.date, v.idnoVente, u.nomUtilisateur, v.prixTTC
                        FROM Vente v
                        JOIN Woofer w ON v.woofer = w.idCompte
                        JOIN Utilisateur u ON w.idCompte = u.idCompte
                        ORDER BY v.date DESC LIMIT 5
                    ";
                    $resultVentes = mysqli_query($conn, $sqlVentesRecentes);
                    while ($vente = mysqli_fetch_assoc($resultVentes)) { // Pour chaque ligne de la réponse à notre requête SQL
                        echo "<tr>
                                <td>{$vente['date']}</td>
                                <td>{$vente['idnoVente']}</td>
                                <td>{$vente['nomUtilisateur']}</td>
                                <td>{$vente['prixTTC']}€</td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
            <a href="ventes.php">Gérer les ventes</a>
        </section>

        <section>
            <h3>Ateliers à venir</h3>
            <table>
                <thead>
                    <tr>
                        <th>Thème</th>
                        <th>Date</th>
                        <th>Woofer</th>
                        <th>État</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // On affiche les 5 prochains ateliers
                    $sqlAteliers = "
                        SELECT a.theme, a.date, u.nomUtilisateur, a.etat 
                        FROM Atelier a
                        JOIN Woofer w ON a.woofer = w.idCompte
                        JOIN Utilisateur u ON w.idCompte = u.idCompte
                        WHERE a.date >= CURDATE()
                        ORDER BY a.date ASC
                        LIMIT 5
                    ";
                    $resultAteliers = mysqli_query($conn, $sqlAteliers);
                    while ($atelier = mysqli_fetch_assoc($resultAteliers)) { // Pour chaque ligne de la réponse à notre requête SQL
                        echo "<tr>
                                <td>{$atelier['theme']}</td>
                                <td>{$atelier['date']}</td>
                                <td>{$atelier['nomUtilisateur']}</td>
                                <td>{$atelier['etat']}</td>
                            </tr>";
                    }
                    ?>
                </tbody>
            </table>
            <a href="ateliers.php">Gérer les ateliers</a>
        </section>
    </div>    
    </body>
</html>
