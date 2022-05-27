# PROJET BIBLIOTHEQUE EN LIGNE FAIT PAR ANAELLE CUSSAC-04-05/2022

---

# DESCRIPTION PROJET:

Mon projet est un site vitrine d'une bibliothèque en ligne. Il s'agit de la version finale pour le rendu. Par la suite, d'autres fonctionnalités seront ajoutées (citées à la fin du document).

# En tant que SIMPLE UTILISATEUR (SANS COMPTE):

On peut chercher un livre qui se trouve dans la base de données (BDD) avec la barre de recherche, voir les dernières acquisitions de la bibliothèque et avoir des suggestions de livres en fonction de la catégorie. Une page contact est également disponible et contient des informations sur l'accessibilité et les horaires de la structure. Elle permet aussi l'envoi de messages/demandes. Deux autres pages simples sont accessibles : une page évènements où l'on peut visualiser les évènements à venir et une page infos pratiques. Deux pages mentions légales et politiques de confidentialité sont accessibles en bas du site.

# BARRE DE RECHERCHE

La barre de recherche permet 2 types de recherches : une RECHERCHE SIMPLE qui va proposer 5 résultats correspondant le plus à ce que l'utilisateur à taper (lorsque l'on clique sur un des résultats affichés sous la barre de recherche on accède directement à la fiche descriptive du livre), et une RECHERCHE AVANCÉE qui va proposer une liste de livres (20) en fonction de l'entrée de l'utilisateur (en cliquant sur entrée ou le bouton recherche avancée). Cette recherche prend en compte chaque mot entré dans le champs de recherche (une requête est faite dans la BDD pour chaque mot). La barre de recherche fait le lien entre la ou les valeurs entrées dans le champs de recherche et le titre, les mots-clés ou l'auteur.

Pour créer un compte -> en haut à droite le bouton connexion : celui-ci amène à une page pour se connecter. On peut un compte en cliquant sur 'Créer un compte' en bas du formulaire de connexion.

Après avoir créé un compte, on s'identifie.

# En tant qu'UTILISATEUR AVEC UN COMPTE:

Des fonctionnalités apparraissent (onglets dans la nav et boutons tels que 'réserver' et checkbox en coeur pour ajouter aux favoris).

    1- L'utilisateur avec compte a donc la possibilité de RÉSERVER un livre. Différents messages seront affichés en fonction du statut actuel du livre (une vérification du statut est donc faite avec la BDD avant la réservation). Après avoir réservé un livre (celui-ci sera mis de côté par la structure), l'utisateur aura 15 jours pour venir le récupérer (fonctionnalité à venir qui remettra automatiquement le livre en statut disponible au bout de 15 jours). L'utilisateur avec compte possède donc un onglet 'Mes réservations' où il peut retrouver toutes ces réservations. Il peut également supprimer ses réservations.

    2-L'utilisateur avec compte possède aussi un onglet 'Mes prêts' où il peut retrouver tous les prêts en cours et les informations avec la date de fin du prêt. Le prêt est une action qui doit être faite sur place, et donc seul l'admin a la possibilité d'ajouter et de supprimer un prêt (autrement dit de remettre en circulation le livre après qu'il ait été rendu).

    3-L'utilisateur avec compte possède aussi un onglet 'Mes coups de coeur' où il peut retrouver tous ces livres favoris. Pour ajouter un livre dans ces favoris il suffit qu'il recherche le livre en question et en haut à droite se trouve un coeur noir sur lequel il peut cliquer. Une fois cliqué, le coeur sera rouge et donc accessible dans la liste des favoris. Cette fonctionnalité est basée sur le localStorage.

# En tant qu'ADMIN:

Il n'y a pas de bouton pour se connecter. L'arrivée sur la page de connexion de l'admin se fait via une url (action=admin).

    1-On arrive sur la page accueil admin où l'on dispose de plusieurs boutons pour réaliser différentes actions :
        -ajouter un prêt,
        -supprimer un prêt ou autrement dit remettre un livre en circulation,
        -ajouter un livre dans la BDD,
        -modifier une notice de livre : avec recherche du livre pour afficher le formulaire de modification, et supprimer un livre de la BDD,
        -afficher toutes les réservations et avec un tri par date (recherche sur la date d'ajout de la réservation) + possibilité de supprimer les réservations,
        -afficher tous les prêts avec un tri par date (recherche sur la date de début du prêt),
        -afficher les retards,
        -afficher la liste des utilisateurs avec possibilité de modifier les coordonnées de l'utilisateur et possibilité de supprimer un utilisateur en vérifiant en amont qu'il n'ait plus de prêts. En cliquant sur le nom/prénom du lecteur, l'admin accède aux détails de l'utilisateur avec ses réservations et ses prêts.
        -ajouter un évènement,
        -afficher la liste des évènements et possibilité de les supprimer,
        -afficher la liste des messages de contact et les supprimer.

    2-Sur la page d'accueil admin, s'affiche aussi le nombre de réservations qui a été faite la veille (afin que l'admin puisse mettre de côté les livres réservés)

# FUTURES FONCTIONNALITÉS

-modification de son profil côté utilisateur
-option renouvellement de prêts max 1 fois
-système de signalement de retard sur le compte utilisateur ou par envoie de mail automatique
-possibilité de laisser des commentaires sur le livre
-suggestions selon historique de recherches
