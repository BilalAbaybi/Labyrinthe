# Labyrinthe - Bilal ABAYBI
# Projet BTS SIO 1 - LYCEE FULBERT

**Date de création :** 14/11/2025
**Dernière mise à jour :** 12/12/2025

## 📝 Description
Labyrinthe interactif web développé en PHP/SQLite. Le joueur doit trouver la sortie en se déplaçant dans des couloirs, en ramassant des clés et en ouvrant des grilles.
Le jeu propose désormais une expérience immersive grâce à une ambiance visuelle (Dark Mode), des illustrations dynamiques et des effets sonores.

## 📂 Structure du projet
Assurez-vous d'avoir l'arborescence suivante pour que le jeu fonctionne :
- `index.php` : Moteur principal du jeu.
- `regles.php` : Page explicative des règles.
- `header.php` : En-tête commun (contient le menu et l'inclusion CSS).
- `footer.php` : Pied de page commun (contient les scripts et fermetures).
- `styles.css` : Feuille de style (Design Thème sombre).
- `labyrinthe.db` : Base de données SQLite contenant les couloirs et passages.
- `/img` : Dossier des images (`depart.jpg`, `couloir.jpg`, `cle.jpg`, `sortie.jpg`).
- `/sons` : Dossier des bruitages (`cle.mp3`, `mur.mp3`, `grille.mp3`, `victoire.mp3`).

## 🎮 Comment jouer
- **Déplacez-vous** dans le labyrinthe en cliquant sur les boutons de direction.
- **Ramassez les clés** 🗝️ automatiquement en entrant dans les pièces spéciales.
- **Ouvrez les grilles** 🔓 : Si vous avez une clé, la grille s'ouvrira automatiquement au passage.

## ✨ Fonctionnalités principales
- **Gameplay** : Déplacement libre, gestion de l'inventaire et compteur de pas.
- **Immersion** :
    - Images contextuelles (l'image change si on a ramassé la clé).
    - Bruitages sonores (ouverture de porte, mur bloqué, victoire).
    - Interface "Dark Mode" moderne et responsive.
- **Ergonomie** : Historique du parcours affiché en bas de page.
- **Sécurité** : Système anti-triche empêchant la modification manuelle de l'URL (`$_GET`).
- **Gestion de partie** : Bouton de réinitialisation complète (score, inventaire et position).

## 📅 Historique des modifications
- **14/11/2025** : Création du projet et de la structure de base.
- **21/11/2025** : Implémentation du moteur de déplacement, du compteur de score, de l'inventaire et de la condition de victoire.
- **28/11/2025** : Création de la page `regles.php`, premières améliorations esthétiques et réorganisation du code.
- **12/12/2025** : Mise à jour majeure "Immersion & Sécurité" :
    - **Refonte graphique** : Ajout de `styles.css` (Design sombre type "Donjon", boutons stylisés).
    - **Médias** : Intégration d'images dynamiques selon l'état de la salle et ajout de bruitages.
    - **UX** : Ajout de l'historique de navigation.
    - **Sécurité** : Correction de la faille de téléportation (anti-cheat via URL).
    - **Code** : Nettoyage complet, factorisation et correction de bugs.

## 👤 Auteur
Bilal ABAYBI