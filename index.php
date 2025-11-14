<?php

	//Documentation php pour sqlite : https://www.php.net/manual/en/book.sqlite3.php
	

	/* Paramètres */
	$bdd_fichier = 'labyrinthe.db';	//Fichier de la base de données
	$type = 'vide';			//Type de couloir à lister
	$depart = 'depart';

	$sqlite = new SQLite3($bdd_fichier);		//On ouvre le fichier de la base de données
	
	/* Instruction SQL pour récupérer la liste des pieces adjacentes à la pièce paramétrée */
	$sql = 'SELECT couloir.id, couloir.type FROM couloir ORDER BY `type` desc';
	
	
	/* Préparation de la requete et de ses paramètres */
	$requete = $sqlite -> prepare($sql);	
	$requete -> bindValue(':type', $type, SQLITE3_TEXT);
	
	$result = $requete -> execute();	//Execution de la requête et récupération du résultat

	/* Requête pour récupérer le couloir de départ */
	$sql_depart = 'SELECT id, type FROM couloir WHERE type=:depart';

	$requete_depart = $sqlite -> prepare($sql_depart);
	$requete_depart -> bindValue(':depart', $depart, SQLITE3_TEXT);
	
	$result_depart = $requete_depart->execute();	//Execution de la requete et recuperation du resultat de depart


	/* Requete pour recuperer les passages des differents couloirs */
	$sql_passage = "SELECT couloir.id, couloir.type, passage.position1, passage.position2, passage.couloir1, passage.couloir2 FROM passage INNER JOIN couloir ON ((passage.couloir1 = :id_courant AND couloir.id = passage.couloir2) OR (passage.couloir2 = :id_courant AND couloir.id = passage.couloir1))";

	$result_passage = null;
	if ($couloir_depart = $result_depart->fetchArray(SQLITE3_ASSOC)) {
    $requete_passage = $sqlite->prepare($sql_passage);
    $requete_passage->bindValue(':id_courant', $couloir_depart['id'], SQLITE3_INTEGER);
    $result_passage = $requete_passage->execute();
	}
	



	/* On génère et on affiche notre page HTML avec la liste de nos films */
	echo "<!DOCTYPE html>\n";		//On demande un saut de ligne avec \n, seulement avec " et pas '
	echo "<html lang=\"fr\"><head><meta charset=\"UTF-8\">\n";	//Avec " on est obligé d'échapper les " a afficher avec \
	echo "<title>Labyrinthe Bilal</title>\n";
	echo "</head>\n";
	
	echo "<body>\n";
	echo "<h1>Liste des couloirs</h1>\n";
	echo "<ul>";
	while($couloir = $result -> fetchArray(SQLITE3_ASSOC)) {
		echo '<li>'.$couloir['id']." (type : {$couloir['type']})</li>";
	}
	echo "</ul>";
	if($couloir_depart) {
		echo "<h1>Vous êtes actuellement dans le couloir {$couloir_depart['id']}</h1>";
	}
	
	echo "<h3>Vous pouvez aller dans :</h3>";
	echo "<ul>";
	if ($result_passage) {
    	while ($passage = $result_passage->fetchArray(SQLITE3_ASSOC)) {
        	if ($passage['couloir1'] == $couloir_depart['id']) {
            	$position = $passage['position2'];
        	} else {
            	$position = $passage['position1'];
        	}
			echo "<li><a href='?id[Couloir {$passage['id']} (position : {$position})</a></li>";   	
		}
	}

	echo "</ul>";

	
	echo "</body>\n";
	echo "</html>\n";

	$sqlite -> close();			//On ferme bien le fichier de la base de données avant de terminer!
	
?>
