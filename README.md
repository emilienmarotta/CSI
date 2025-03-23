# Comment récupérer l'appli web ?

## Se rendre sur le dépôt Github

- Cliquer sur le bouton vert "Code", télécharger le zip (ou faire un git clone avec l'URL du projet) dans le dossier htdocs de MAMP ou XAMPP ou WAMP

## Si vous utilisez XAMPP (ne pas en tenir compte pour MAMP)

- Ouvrir le fichier config/database.php, et remplacer le deuxième "root" par ""
  (Le mot de passe est "root" par défaut pour MAMP et il est nul pour XAMPP)

## Créer la base de données dans PhpMyAdmin

- Ouvrir le fichier database/database.sql
- Copier-coller le code SQL dans la rubrique SQL de PhpMyAdmin
- Exécuter le tout en faisant Ctrl + Enter

## Ouvrir l'application web (en s'assurant que le localhost est lancé)
