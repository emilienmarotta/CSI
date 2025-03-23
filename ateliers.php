<?php
   require_once("config/database.php"); // Connexion à la BDD

   session_start(); // Permet d'utiliser les variables de session
   
   if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { // On vérifie si l'utilisateur est connecté
       header("Location: login.php"); // Si non, on le redirige vers le Login
       exit();
   }    
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouterAtelier'])) {
    // On récupère les données du formulaire
    $theme = mysqli_real_escape_string($conn, $_POST['theme']);
    $nbMaxParticipants = mysqli_real_escape_string($conn, $_POST['nbMaxParticipants']);
    $prix = mysqli_real_escape_string($conn, $_POST['prix']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $woofer = mysqli_real_escape_string($conn, $_POST['woofer']);

    // Requête d'insertion des données 
    $sqlAtelier = "INSERT INTO Atelier (theme, nbMaxParticipants, etat, prix, date, woofer)  
                    VALUES ('$theme', '$nbMaxParticipants', 'cree', '$prix', '$date', '$woofer')";

    mysqli_query($conn, $sqlAtelier);
        
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des ateliers</title>
    <link rel="stylesheet" href="css/menu.css">
    <link rel="stylesheet" href="css/index.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script> // On veut pouvoir modifier les valeurs en double-cliquant sur un élément puis en cliquant à côté pour pouvoir enregistrer la modification dans la BDD
    $(document).ready(function() { // On attend que la page soit chargée
        $(".editable").dblclick(function() { // On détecte ici le double-clic
            let elementActuel = $(this); // this = élément double-cliqué
            let valeurActuelle = elementActuel.text();
            let column = elementActuel.data("column");
            let id = elementActuel.data("id");

            let input = $("<input>", { // On transforme la cellule en input pour pouvoir la modifier
                type: "text",
                value: valeurActuelle,
                class: "edit-input"
            });

            elementActuel.html(input);
            input.focus();

            input.blur(function() { // On détecte ici lorsque l'utilisateur clique en dehors de l'élément modifié
                let nouvelleValeur = $(this).val(); // On récupère la nouvelle valeur 

                if (nouvelleValeur !== valeurActuelle) { // On envoie une requête uniquement si la valeur a changé
                    $.ajax({
                        url: "update_atelier.php",
                        type: "POST",
                        data: {
                            id: id,
                            column: column,
                            value: nouvelleValeur
                        },
                        success: function(response) {
                            if (response == "success") {
                                elementActuel.text(nouvelleValeur);
                            } else {
                                alert("Erreur lors de la mise à jour");
                                elementActuel.text(valeurActuelle);
                            }
                        }
                    });
                } else {
                    elementActuel.text(valeurActuelle);
                }
            });
        });
    });
    </script>
</head>
<body>
    <?php include_once("menu.php") ?>
    <h2>Gestion des ateliers</h2>
    <p>Accès <?php echo $_SESSION["typeUtilisateur"]?></p>
    <h3>Statistiques des Ateliers</h3>
    <table>
        <thead>
            <tr>
                <th>Total Ateliers</th>
                <th>Nombre d'ateliers par état</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // On récupère le nombre total d'ateliers
            $sqlTotalAteliers = "SELECT COUNT(*) as total FROM Atelier";
            $resultTotal = mysqli_query($conn, $sqlTotalAteliers);
            $totalAteliers = mysqli_fetch_assoc($resultTotal)['total'];

            // On récupère le nombre d'ateliers de chaque état pour chaque type d'atelier
            $sqlAteliersParEtat = "SELECT etat, COUNT(*) as total FROM Atelier GROUP BY etat";
            $resultEtat = mysqli_query($conn, $sqlAteliersParEtat);
            $etatStats = [];
            while ($row = mysqli_fetch_assoc($resultEtat)) {
                $etatStats[] = "{$row['etat']}: {$row['total']}";
            }
            $etatStatsStr = implode('<br>', $etatStats);
            
            echo "<tr>
                    <td>{$totalAteliers}</td>
                    <td>{$etatStatsStr}</td>
                  </tr>";
            ?>
        </tbody>
    </table>
    <h3>Gestion des Ateliers</h3>
    <!-- Formulaire de recherche et tri -->
    <form method="GET" action="">
        <label for="search">Rechercher un atelier : </label>
        <input type="text" name="search" id="search" placeholder="Thème, état, woofer..." value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
        <br>

        <label for="sort">Trier par : </label>
        <select name="sort" id="sort">
            <option value="theme" <?= (isset($_GET['sort']) && $_GET['sort'] == 'theme') ? 'selected' : '' ?>>Thème</option>
            <option value="nbMaxParticipants" <?= (isset($_GET['sort']) && $_GET['sort'] == 'nbMaxParticipants') ? 'selected' : '' ?>>Nombre max de participants</option>
            <option value="etat" <?= (isset($_GET['sort']) && $_GET['sort'] == 'etat') ? 'selected' : '' ?>>État</option>
            <option value="prix" <?= (isset($_GET['sort']) && $_GET['sort'] == 'prix') ? 'selected' : '' ?>>Prix</option>
            <option value="date" <?= (isset($_GET['sort']) && $_GET['sort'] == 'date') ? 'selected' : '' ?>>Date</option>
        </select>
        
        <button type="submit">Appliquer les filtres</button>
    </form>

    <!-- Tableau des ateliers -->
    <p class='instruction'>Double-cliquez sur les éléments en <span style='color:#535fc9'>bleu</span> pour modifier leurs valeurs.</p>
    <table>
        <thead>
            <tr>
                <th scope="col">Numéro Atelier</th>
                <th scope="col">Thème</th>
                <th scope="col">Max Participants</th>
                <th scope="col">État</th>
                <th scope="col">Prix</th>
                <th scope="col">Date</th>
                <th scope="col">Woofer Responsable</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // On récupère les paramètres de recherche et tri
            $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'theme'; // Tri par défaut

            // On récupère les ateliers avec les paramètres de recherche et de tri
            $sql = "SELECT a.noAtelier, a.theme, a.nbMaxParticipants, a.etat, a.prix, a.date, u.nomUtilisateur AS wooferResponsable
                    FROM Atelier a
                    LEFT JOIN Woofer w ON a.woofer = w.idCompte
                    LEFT JOIN Utilisateur u ON w.idCompte = u.idCompte
                    WHERE a.theme LIKE '%$search%'
                    OR a.etat LIKE '%$search%'
                    OR u.nomUtilisateur LIKE '%$search%'
                    ORDER BY $sort DESC";

            $result = mysqli_query($conn, $sql);

            // Affichage 
            while ($atelier = $result->fetch_assoc()) {
                echo "<tr>
                        <th scope='row'>{$atelier['noAtelier']}</th>
                        <td class='editable' data-column='theme' data-id='{$atelier['noAtelier']}'>{$atelier['theme']}</td>
                        <td class='editable' data-column='nbMaxParticipants' data-id='{$atelier['noAtelier']}'>{$atelier['nbMaxParticipants']}</td>
                        <td class='editable' data-column='etat' data-id='{$atelier['noAtelier']}'>{$atelier['etat']}</td>
                        <td class='editable' data-column='prix' data-id='{$atelier['noAtelier']}'>{$atelier['prix']}</td>
                        <td class='editable' data-column='date' data-id='{$atelier['noAtelier']}'>{$atelier['date']}</td>
                        <td>{$atelier['wooferResponsable']}</td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
    </div>
    <div>
    <h3>Ajouter un atelier</h3> <!-- Seuls les responsables peuvent ajouter un atelier -->
    <?php
        if ($_SESSION["typeUtilisateur"] !== "Responsable") {
            echo "<p>Fonctionnalité réservée aux responsables.</p>";
        }
        else {
    ?>
    <form method="POST" action="">
        <label for="theme">Thème :</label>
        <select name="theme" id="theme" required>
            <option value="">-- Sélectionner un thème --</option>
            <?php
            // On récupère la liste des thèmes des ateliers
            $sqlTheme = "SELECT noAtelier, theme FROM Atelier";
            $resultTheme = mysqli_query($conn, $sqlTheme);
            while ($atelier = mysqli_fetch_assoc($resultTheme)) {
                echo "<option value='{$atelier['noAtelier']}'>{$atelier['theme']}</option>";
            }
            ?>
        </select>
        <br>

        <label for="nbMaxParticipants">Nombre max de participants :</label>
        <input type="number" name="nbMaxParticipants" id="nbMaxParticipants" required value=0 min=0>
        <br>

        <label for="prix">Prix :</label>
        <input type="number" name="prix" id="prix" required value=0 min=0>
        <br>

        <label for="date">Date :</label>
        <input type="date" name="date" id="date" required>
        <br>

        <label for="woofer">Woofer responsable :</label>
        <select name="woofer" id="woofer" required>
            <option value="">-- Sélectionner un woofer --</option>
            <?php
            // On récupère les woofers
            $sqlWoofer = "SELECT w.idCompte, u.nomUtilisateur FROM Woofer w JOIN Utilisateur u ON u.idCompte = w.idCompte";
            $resultWoofer = mysqli_query($conn, $sqlWoofer);
            while ($woofer = mysqli_fetch_assoc($resultWoofer)) {
                echo "<option value='{$woofer['idCompte']}'>{$woofer['nomUtilisateur']}</option>";
            }
            ?>
        </select>
        <br>

        <br>
        <button type="submit" name="ajouterAtelier">Ajouter Atelier</button>
    </form>
    <?php
        } 
    ?>
    </div>
</body>
</html>