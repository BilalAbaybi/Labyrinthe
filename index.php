<?php

// --- Initialisation des paramètres et de la session ---
$bdd_fichier = 'labyrinthe.db'; // Nom du fichier de la base SQLite
$depart = 'depart'; // Type du couloir de départ
session_start(); // Démarre la session PHP

// --- Réinitialisation de la partie si demandé ---
if (isset($_GET['new'])) {
    $_SESSION['deplacements'] = 0; // Remet le compteur à zéro
    $_SESSION['cles'] = []; // Vide l'inventaire de clés
    $_SESSION['cles_ramassees'] = []; // Vide la liste des clés ramassées
    $_SESSION['grilles_ouvertes'] = []; // Vide la liste des grilles ouvertes
    header('Location: ?'); // Recharge la page sans paramètre
    exit;
}

// --- Connexion à la base de données ---
$sqlite = new SQLite3($bdd_fichier);

// --- Initialisation des variables de session si besoin ---
if (!isset($_SESSION['deplacements'])) $_SESSION['deplacements'] = 0;
if (!isset($_SESSION['cles'])) $_SESSION['cles'] = [];
if (!isset($_SESSION['cles_ramassees'])) $_SESSION['cles_ramassees'] = [];
if (!isset($_SESSION['grilles_ouvertes'])) $_SESSION['grilles_ouvertes'] = [];

$message = '';

// --- Détermination du couloir courant ---
$couloir_courant = null;
$id_courant = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_courant = intval($_GET['id']);
}
if ($id_courant !== null) {
    // Récupère les infos du couloir courant
    $sql_courant = 'SELECT id, type FROM couloir WHERE id = :id';
    $req_courant = $sqlite->prepare($sql_courant);
    $req_courant->bindValue(':id', $id_courant, SQLITE3_INTEGER);
    $res_courant = $req_courant->execute();
    $couloir_courant = $res_courant->fetchArray(SQLITE3_ASSOC);

    // Ramasse une clé si le couloir est de type "cle" et qu'on ne l'a pas déjà prise
    if ($couloir_courant && $couloir_courant['type'] === 'cle' && !in_array($couloir_courant['id'], $_SESSION['cles_ramassees'])) {
        $_SESSION['cles'][] = 'clé';
        $_SESSION['cles_ramassees'][] = $couloir_courant['id'];
        $message = 'Vous avez ramassé une clé !';
    }

    // --- Contrôle du déplacement (adjacence + gestion grille) ---
    // On doit récupérer le passage exact entre l'ancien et le nouveau couloir
    $id_cible = $id_courant;
    $id_depart = isset($_SESSION['last_couloir']) ? $_SESSION['last_couloir'] : $couloir_courant['id'];
    // On récupère le passage entre last_couloir et id_courant
    $sql_verif = "SELECT rowid, type FROM passage WHERE ((couloir1 = :id_depart AND couloir2 = :id_cible) OR (couloir2 = :id_depart AND couloir1 = :id_cible))";
    $requete_verif = $sqlite->prepare($sql_verif);
    $requete_verif->bindValue(':id_depart', $id_depart, SQLITE3_INTEGER);
    $requete_verif->bindValue(':id_cible', $id_cible, SQLITE3_INTEGER);
    $result_verif = $requete_verif->execute();
    $passage_info = $result_verif->fetchArray(SQLITE3_ASSOC);
    if ($passage_info !== false) {
        $passage_id = $passage_info['rowid'];
        if ($passage_info['type'] === 'grille') {
            // Si la grille est déjà ouverte, passage libre
            if (in_array($passage_id, $_SESSION['grilles_ouvertes'])) {
                $_SESSION['deplacements'] += 1;
            }
            // Sinon, il faut une clé pour ouvrir
            else if (count($_SESSION['cles']) > 0) {
                array_pop($_SESSION['cles']);
                $_SESSION['deplacements'] += 1;
                $_SESSION['grilles_ouvertes'][] = $passage_id;
                $message = 'Vous avez utilisé une clé pour passer la grille.';
            }
            // Sinon, déplacement bloqué
            else {
                $id_courant = $id_depart;
                $message = 'Grille bloquée : vous n\'avez pas de clé.';
            }
        } else {
            // Passage libre
            $_SESSION['deplacements'] += 1;
        }
    } else {
        $message = 'Déplacement non autorisé.';
    }
    // On mémorise le dernier couloir visité pour le prochain déplacement
    $_SESSION['last_couloir'] = $id_courant;
}

// Si aucun couloir courant, on prend le départ
if (!$couloir_courant) {
    $sql_depart = 'SELECT id, type FROM couloir WHERE type = :depart';
    $requete_depart = $sqlite->prepare($sql_depart);
    $requete_depart->bindValue(':depart', $depart, SQLITE3_TEXT);
    $result_depart = $requete_depart->execute();
    $couloir_courant = $result_depart->fetchArray(SQLITE3_ASSOC);
}

// --- Récupère les passages accessibles depuis le couloir courant ---
$sql_passage = "SELECT couloir.id AS couloir_cible_id, passage.position1, passage.position2, passage.couloir1, passage.couloir2
    FROM passage
    INNER JOIN couloir
    ON (couloir.id = passage.couloir1 OR couloir.id = passage.couloir2)
    WHERE :id_courant IN (passage.couloir1, passage.couloir2)
    AND couloir.id != :id_courant
";
$result_passage = null;
if ($couloir_courant && isset($couloir_courant['id'])) {
    $requete_passage = $sqlite->prepare($sql_passage);
    $requete_passage->bindValue(':id_courant', $couloir_courant['id'], SQLITE3_INTEGER);
    $result_passage = $requete_passage->execute();
}

// --- Génération de la page HTML ---
echo "<!DOCTYPE html>\n";
echo "<html lang=\"fr\"><head><meta charset=\"UTF-8\">\n";
echo "<title>Labyrinthe Bilal</title>\n";
echo "</head>\n";
echo "<body>\n";

// Affiche le nombre de déplacements et l'inventaire de clés
echo "<p>Nombre de déplacements : ".$_SESSION['deplacements']."</p>";
echo "<p>Clés dans l'inventaire : ".count($_SESSION['cles'])."</p>";
if ($message) {
    echo '<p style="color:red;">'.htmlspecialchars($message).'</p>';
}

// Affiche le couloir courant et la victoire si sortie
if ($couloir_courant) {
    echo "<h1>Vous êtes actuellement dans le couloir ".$couloir_courant['id']."</h1>";
    if ($couloir_courant['type'] === 'sortie') {
        echo '<h2 style="color:green;">Bravo, vous avez gagné la partie !</h2>';
        echo '<p>Vous avez réussi en <strong>' . $_SESSION['deplacements'] . '</strong> déplacements.</p>';
    } else {
        echo "<h3>Vous pouvez aller dans :</h3>";
        echo "<ul>";

        if ($result_passage) {
            $aAuMoinsUn = false;
            while ($passage = $result_passage->fetchArray(SQLITE3_ASSOC)) {
                $aAuMoinsUn = true;
                $position = ($passage['couloir1'] == $couloir_courant['id']) ? $passage['position2'] : $passage['position1'];
                $cibleId = $passage['couloir_cible_id'];
                // Vérifie le type du passage pour afficher grille ou libre
                $sql_type = "SELECT rowid, type FROM passage WHERE (:id_courant IN (couloir1, couloir2)) AND (:id_cible IN (couloir1, couloir2)) AND couloir1 != couloir2";
                $req_type = $sqlite->prepare($sql_type);
                $req_type->bindValue(':id_courant', $couloir_courant['id'], SQLITE3_INTEGER);
                $req_type->bindValue(':id_cible', $cibleId, SQLITE3_INTEGER);
                $res_type = $req_type->execute();
                $info_type = $res_type->fetchArray(SQLITE3_ASSOC);
                $passage_id = $info_type ? $info_type['rowid'] : null;
                if ($info_type && $info_type['type'] === 'grille' && !in_array($passage_id, $_SESSION['grilles_ouvertes'])) {
                    if (count($_SESSION['cles']) == 0) {
                        // Passage grille non accessible sans clé et non déjà ouvert
                        echo "<li>Couloir $cibleId (position : $position) <span style='color:red'>(Grille, clé requise)</span></li>";
                    } else {
                        // Passage grille accessible avec clé, lien explicite
                        echo "<li><a href=\"?id=$cibleId\">Couloir $cibleId (position : $position) <span style='color:orange'>(Grille, va utiliser une clé)</span></a></li>";
                    }
                } else {
                    echo "<li><a href=\"?id=$cibleId\">Couloir $cibleId (position : $position)</a></li>";
                }
            }
            if (!$aAuMoinsUn) {
                echo "<li>Aucun passage depuis ce couloir.</li>";
            }
        }
    }
}


// Ajoute le bouton pour recommencer une partie
echo '<p><a href="?new=partie">Nouvelle partie</a></p>';

$sqlite->close();    //On ferme bien le fichier de la base de données avant de terminer!
?>