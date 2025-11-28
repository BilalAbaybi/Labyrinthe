<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Règles du jeu</title>
</head>
<body>
<?php include 'header.php'; ?>
<h1>📜 Règles du jeu : Labyrinthe Web</h1>

<p>
    Bienvenue dans le <strong>Labyrinthe Web</strong> ! 🧩  
    Ton objectif est simple : <strong>trouver la sortie</strong> du labyrinthe tout en gérant 
    tes déplacements et les obstacles sur ton chemin.  
    Voici toutes les règles pour bien comprendre le fonctionnement du jeu.
</p>

<hr>

<h2>🎮 1. Principe général du jeu</h2>
<p>
    Le jeu consiste en un ensemble de <strong>couloirs connectés entre eux</strong>.  
    Chaque couloir est représenté par une <strong>page web différente</strong>.  
    Le joueur commence dans un couloir spécial appelé <strong>Départ</strong> et doit naviguer 
    de page en page pour atteindre la <strong>Sortie</strong> 🏁.
</p>

<hr>

<h2>🧭 2. Déplacements</h2>
<p>
    À chaque étape, le joueur voit les directions où il peut aller :  
    <strong>Nord</strong> ⬆️, <strong>Sud</strong> ⬇️, <strong>Est</strong> ➡️ ou <strong>Ouest</strong> ⬅️.
</p>

<p>
    Le joueur ne peut se déplacer que vers les couloirs :
</p>

<ul>
    <li>directement connectés au couloir actuel 🔗</li>
    <li>et non bloqués par une grille verrouillée 🔒</li>
</ul>

<p>
    Chaque déplacement compte comme un "pas" 👣 utilisé pour calculer le score final.
</p>

<hr>

<h2>🗝️ 3. Clés et grilles</h2>
<p>
    Le labyrinthe contient des <strong>clés</strong> 🗝️ que le joueur peut trouver dans certains couloirs.  
    Ces clés permettent d’ouvrir des <strong>grilles verrouillées</strong> 🔒 placées dans le labyrinthe.
</p>

<p>
    Voici les règles concernant les clés :
</p>

<ul>
    <li>Une clé peut ouvrir exactement <strong>une seule grille</strong> ✔️</li>
    <li>Une fois utilisée, elle est consommée ❌</li>
    <li>Si le joueur n’a pas de clé, il ne peut pas franchir une grille 🔐</li>
</ul>

<p>
    Dans certains cas, le joueur devra explorer plusieurs couloirs pour trouver suffisamment de clés 
    avant de pouvoir continuer.
</p>

<hr>

<h2>🗺️ 4. Structure du labyrinthe</h2>
<p>
    Le labyrinthe n’est pas linéaire : certains couloirs mènent à des impasses, d’autres à des zones plus larges.  
    Le joueur doit donc :
</p>

<ul>
    <li>mémoriser son chemin ou réfléchir à son orientation 🧠</li>
    <li>éviter de tourner en rond 🔄</li>
    <li>collecter toutes les clés nécessaires pour progresser 🔎</li>
</ul>

<p>
    Attention : selon la direction par laquelle tu arrives dans un couloir, l’orientation peut changer !  
    Un passage qui était au Nord peut devenir à l’Est selon d’où tu viens.  
    Reste attentif 🧭.
</p>

<hr>

<h2>🏁 5. Fin de la partie</h2>
<p>
    La partie se termine lorsque tu atteins la <strong>sortie</strong> du labyrinthe 🚪.  
    Une page de fin s’affiche alors, résumant ta performance.
</p>

<h2>📊 6. Calcul du score</h2>
<p>
    Ton score dépend du nombre total de déplacements effectués.  
</p>

<ul>
    <li>Moins tu fais de pas 👉 meilleur est ton score ⭐</li>
    <li>Les déplacements inutiles ou demi-tours pénalisent ton score ❗</li>
    <li>Ouvrir des grilles ne modifie pas le score</li>
</ul>

<hr>

<h2>💡 7. Conseils pour réussir</h2>
<ul>
    <li>Explore méthodiquement, évite les déplacements inutiles 🧩</li>
    <li>Garde en tête d’où tu viens pour mieux comprendre les directions 🧭</li>
    <li>Récupère toutes les clés que tu trouves 🗝️ — elles peuvent sauver la partie</li>
    <li>Ne te précipite pas : réfléchis avant de te déplacer 🤔</li>
</ul>

<hr>

<h2>📥 8. Retour au jeu</h2>
<p>
    Quand tu es prêt, retourne à l’accueil et commence une partie :
</p>
<p>
    <a href="index.php?new=partie">⬅️ Retour à l’accueil</a>
</p>

<button onclick="window.scrollTo(0, 0);" class="btn-top">⬆️ Retour en haut</button>
<?php include 'footer.php'; ?>

</body>
</html>
