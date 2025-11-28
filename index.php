<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labyrinthe Bilal ABAYBI</title>
</head>
<body>
    <?php include 'header.php'; ?>

    <?php
    // =============================================
    // 1. Initialisation de la session et des variables
    // =============================================
    session_start(); // Démarre la session pour stocker les données du joueur
    $bdd_fichier = 'labyrinthe.db'; // Fichier de la base de données SQLite
    $depart = 'depart'; // Type du couloir de départ

    // --- Réinitialisation de la partie si demandé ---
    if (isset($_GET['new'])) {
        $_SESSION['deplacements'] = 0; // Le score est le nombre de déplacements
        $_SESSION['cles'] = [];
        $_SESSION['cles_ramassees'] = [];
        $_SESSION['grilles_ouvertes'] = [];
        header('Location: ?'); // Recharge la page sans paramètre
        exit;
    }

    // --- Réinitialisation si retour depuis les règles ---
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'regles.php') !== false) {
        $_SESSION['deplacements'] = 0; // Réinitialise le score
        $_SESSION['cles'] = [];
        $_SESSION['cles_ramassees'] = [];
        $_SESSION['grilles_ouvertes'] = [];
    }

    // --- Initialisation des variables de session si besoin ---
    if (!isset($_SESSION['deplacements'])) $_SESSION['deplacements'] = 0; // Initialise le score
    if (!isset($_SESSION['cles'])) $_SESSION['cles'] = [];
    if (!isset($_SESSION['cles_ramassees'])) $_SESSION['cles_ouvertes'] = [];
    if (!isset($_SESSION['grilles_ouvertes'])) $_SESSION['grilles_ouvertes'] = [];
    $message = ''; // Variable pour afficher les messages au joueur

    // =============================================
    // 2. Connexion à la base de données
    // =============================================
    $sqlite = new SQLite3($bdd_fichier); // Connexion à la base de données SQLite

    // =============================================
    // 3. Détermination du couloir courant
    // =============================================
    $couloir_courant = null;
    $id_courant = null;

    // Si un ID de couloir est passé dans l'URL, on le récupère
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id_courant = intval($_GET['id']);
    }

    // Si un ID de couloir est défini, on récupère ses informations
    if ($id_courant !== null) {
        $sql_courant = 'SELECT id, type FROM couloir WHERE id = :id';
        $req_courant = $sqlite->prepare($sql_courant);
        $req_courant->bindValue(':id', $id_courant, SQLITE3_INTEGER);
        $res_courant = $req_courant->execute();
        $couloir_courant = $res_courant->fetchArray(SQLITE3_ASSOC);

        // Si le couloir courant est de type "clé" et qu'on ne l'a pas déjà prise, on la ramasse
        if ($couloir_courant && $couloir_courant['type'] === 'cle' && !in_array($couloir_courant['id'], $_SESSION['cles_ramassees'])) {
            $_SESSION['cles'][] = 'clé';
            $_SESSION['cles_ramassees'][] = $couloir_courant['id'];
            $message = 'Vous avez ramassé une clé !';
        }

        // --- Contrôle du déplacement ---
        $id_depart = isset($_SESSION['last_couloir']) ? $_SESSION['last_couloir'] : $couloir_courant['id'];

        // On vérifie si le passage entre le couloir de départ et le couloir courant est valide
        $sql_verif = "SELECT rowid, type FROM passage WHERE ((couloir1 = :id_depart AND couloir2 = :id_cible) OR (couloir2 = :id_depart AND couloir1 = :id_cible))";
        $requete_verif = $sqlite->prepare($sql_verif);
        $requete_verif->bindValue(':id_depart', $id_depart, SQLITE3_INTEGER);
        $requete_verif->bindValue(':id_cible', $id_courant, SQLITE3_INTEGER);
        $result_verif = $requete_verif->execute();
        $passage_info = $result_verif->fetchArray(SQLITE3_ASSOC);

        if ($passage_info !== false) {
            $passage_id = $passage_info['rowid'];
            if ($passage_info['type'] === 'grille') {
                // Si la grille est déjà ouverte, on peut passer
                if (in_array($passage_id, $_SESSION['grilles_ouvertes'])) {
                    $_SESSION['deplacements'] += 1; // Incrémente le score
                }
                // Sinon, il faut une clé pour ouvrir
                else if (count($_SESSION['cles']) > 0) {
                    array_pop($_SESSION['cles']); // On utilise une clé
                    $_SESSION['deplacements'] += 1; // Incrémente le score
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
                $_SESSION['deplacements'] += 1; // Incrémente le score
            }
        }
        $_SESSION['last_couloir'] = $id_courant; // On mémorise le dernier couloir visité
    }

    // --- Si aucun couloir courant, on prend le départ ---
    if (!$couloir_courant) {
        $sql_depart = 'SELECT id, type FROM couloir WHERE type = :depart';
        $requete_depart = $sqlite->prepare($sql_depart);
        $requete_depart->bindValue(':depart', $depart, SQLITE3_TEXT);
        $result_depart = $requete_depart->execute();
        $couloir_courant = $result_depart->fetchArray(SQLITE3_ASSOC);
        if (!isset($_SESSION['last_couloir'])) {
            $_SESSION['last_couloir'] = $couloir_courant['id'];
        }
    }

    // =============================================
    // 4. Récupère les passages accessibles depuis le couloir courant
    // =============================================
    $sql_passage = "SELECT couloir.id AS couloir_cible_id, passage.position1, passage.position2, passage.couloir1, passage.couloir2
                    FROM passage
                    INNER JOIN couloir ON (couloir.id = passage.couloir1 OR couloir.id = passage.couloir2)
                    WHERE :id_courant IN (passage.couloir1, passage.couloir2) AND couloir.id != :id_courant";
    $result_passage = null;
    if ($couloir_courant) {
        $requete_passage = $sqlite->prepare($sql_passage);
        $requete_passage->bindValue(':id_courant', $couloir_courant['id'], SQLITE3_INTEGER);
        $result_passage = $requete_passage->execute();
    }
    ?>

    <!-- =============================================
         5. Affichage des infos de jeu
         ============================================= -->
    <p>👣 Score (déplacements) : <?php echo $_SESSION['deplacements']; ?></p>
    <p>🔑 Clés dans l’inventaire : <?php echo count($_SESSION['cles']); ?></p>

    <?php
    // --- Affiche les messages ---
    if ($message) {
        if (strpos($message, 'ramassé une clé') !== false) {
            echo '<p style="color:green; font-weight:bold;">🔑 ' . htmlspecialchars($message) . '</p>';
        } else if (strpos($message, 'Grille bloquée') !== false) {
            echo '<p style="color:red; font-weight:bold;">🚫 ' . htmlspecialchars($message) . '</p>';
        } else if (strpos($message, 'utilisé une clé') !== false) {
            echo '<p style="color:orange; font-weight:bold;">🔓 ' . htmlspecialchars($message) . '</p>';
        } else {
            echo '<p style="color:blue; font-weight:bold;">' . htmlspecialchars($message) . '</p>';
        }
    }
    ?>

    <hr>

    <?php
    // =============================================
    // 6. Affichage du couloir courant et des passages
    // =============================================
    if ($couloir_courant) {
        echo "<h1>Vous êtes actuellement dans le couloir " . htmlspecialchars($couloir_courant['id']) . "</h1>";
        if ($couloir_courant['type'] === 'sortie') {
            echo '<h2 style="color:green;">Bravo, vous avez gagné la partie !</h2>';
            echo '<p>Votre score final : <strong>' . $_SESSION['deplacements'] . '</strong> déplacements.</p>';
        } else {
            echo "<h3>Vous pouvez aller dans :</h3>";
            echo "<ul>";
            if ($result_passage) {
                $aAuMoinsUn = false;
                while ($passage = $result_passage->fetchArray(SQLITE3_ASSOC)) {
                    $aAuMoinsUn = true;
                    $position = ($passage['couloir1'] == $couloir_courant['id']) ? $passage['position2'] : $passage['position1'];
                    $cibleId = $passage['couloir_cible_id'];
                    // On vérifie le type du passage pour afficher grille ou libre
                    $sql_type = "SELECT rowid, type FROM passage WHERE (:id_courant IN (couloir1, couloir2)) AND (:id_cible IN (couloir1, couloir2)) AND couloir1 != couloir2";
                    $req_type = $sqlite->prepare($sql_type);
                    $req_type->bindValue(':id_courant', $couloir_courant['id'], SQLITE3_INTEGER);
                    $req_type->bindValue(':id_cible', $cibleId, SQLITE3_INTEGER);
                    $res_type = $req_type->execute();
                    $info_type = $res_type->fetchArray(SQLITE3_ASSOC);
                    $passage_id = $info_type ? $info_type['rowid'] : null;
                    if ($info_type && $info_type['type'] === 'grille' && !in_array($passage_id, $_SESSION['grilles_ouvertes'])) {
                        if (count($_SESSION['cles']) == 0) {
                            echo "<li>🔒 Couloir $cibleId (position : $position) <span style='color:red'>(Grille, clé requise)</span></li>";
                        } else {
                            echo "<li>🔓 <a href=\"?id=$cibleId\">Couloir $cibleId (position : $position) (Grille, va utiliser une clé)</a></li>";
                        }
                    } else {
                        echo "<li>🔗 <a href=\"?id=$cibleId\">Couloir $cibleId (position : $position)</a></li>";
                    }
                }
                if (!$aAuMoinsUn) {
                    echo "<li>Aucun passage depuis ce couloir.</li>";
                }
            }
            echo "</ul>";
        }
    }
    $sqlite->close(); // On ferme la connexion à la base de données
    ?>

    <?php include 'footer.php'; ?>
</body>
</html>
