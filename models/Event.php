<?php

class Event
{
    private $_database;
    private $_connexion;
    
    public function __construct()
    {
        $this -> _database = new DataBase();
        $this -> _connexion = $this -> _database ->getConnexion();
    }

    //METHODE REQUETE RECUPERER EVENEMENTS LIMIT 6 évènements
    public function getEvents(): array
    {
        //préparer la requete
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        CONCAT(UPPER(LEFT(titre,1)),LOWER(SUBSTRING(titre,2))) AS Titre,
                                                        CONCAT(UPPER(LEFT(jour,1)),LOWER(SUBSTRING(jour,2))) AS Jour,
                                                        DATE_FORMAT(date_deb, '%d/%m/%Y') AS date_deb,
                                                        DATE_FORMAT(date_deb, '%H:%i') AS heure_deb,
                                                        DATE_FORMAT(date_fin, '%d/%m/%Y') AS date_fin,
                                                        DATE_FORMAT(date_fin, '%H:%i') AS heure_fin,
                                                        resume,
                                                        image,
                                                        nbre_place
                                                    FROM event
                                                    ORDER BY
                                                        id
                                                    DESC
                                                    LIMIT 6");
        //exécuter la requete
        $query->execute();
        //récupérer les infos de la requete
        $events = $query ->fetchAll();
        //var_dump($events);
        return $events;
    }
    
    /*****************************************************************************
                                METHODES ADMIN
    *******************************************************************************/
    //AJOUTER UN EVENEMENT DANS BDD
    public function addEventInBdd(string $titre, string $jour, string $date_deb, string $date_fin, string $resume, string $imageName,int $nombre)
    {
         $query = $this -> _connexion -> prepare("INSERT INTO event(
                                                                    titre,
                                                                    jour,
                                                                    date_deb,
                                                                    date_fin,
                                                                    resume,
                                                                    image,
                                                                    nbre_place
                                                                    )
                                                            VALUES(
                                                                    LOWER(?),
                                                                    LOWER(?),
                                                                    ?,
                                                                    ?,
                                                                    ?,
                                                                    ?,
                                                                    ?
                                                            )");
        $test = $query->execute([$titre, $jour, $date_deb, $date_fin, $resume, $imageName, $nombre]);
        //var_dump($test);
        return $test;
    }
    
    //RECUPERER TOUS LES EVENEMENTS
    public function getAllEventsAdmin()
    {
         $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        CONCAT(UPPER(LEFT(titre,1)),LOWER(SUBSTRING(titre,2))) AS Titre,
                                                        CONCAT(UPPER(LEFT(jour,1)),LOWER(SUBSTRING(jour,2))) AS Jour,
                                                        DATE_FORMAT(date_deb, '%d/%m/%Y') AS date_deb,
                                                        DATE_FORMAT(date_deb, '%H:%i') AS heure_deb,
                                                        DATE_FORMAT(date_fin, '%d/%m/%Y') AS date_fin,
                                                        DATE_FORMAT(date_fin, '%H:%i') AS heure_fin,
                                                        resume,
                                                        image,
                                                        nbre_place
                                                    FROM event
                                                    ORDER BY
                                                        id
                                                    DESC");
        $query->execute();
        $events = $query ->fetchAll();
        //var_dump($events);
        return $events;
    }
    
    //RECUPERER LES EVENEMENTS AVEC LE TITRE
    public function getEventsWithTitle(string $titre)
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        CONCAT(UPPER(LEFT(titre,1)),LOWER(SUBSTRING(titre,2))) AS Titre,
                                                        CONCAT(UPPER(LEFT(jour,1)),LOWER(SUBSTRING(jour,2))) AS Jour,
                                                        DATE_FORMAT(date_deb, '%d/%m/%Y') AS date_deb,
                                                        DATE_FORMAT(date_deb, '%H:%i') AS heure_deb,
                                                        DATE_FORMAT(date_fin, '%d/%m/%Y') AS date_fin,
                                                        DATE_FORMAT(date_fin, '%H:%i') AS heure_fin,
                                                        resume,
                                                        image,
                                                        nbre_place
                                                    FROM
                                                        event
                                                    WHERE
                                                        titre LIKE ?
                                                    ORDER BY
                                                        id
                                                    DESC");
        $query->execute(['%'.$titre.'%']);
        $events = $query ->fetchAll();
        //var_dump($events);
        return $events;
    }
    
    //RECUPERE LE NOM DE L'IMAGE ACTUELLE AVEC L'ID
    public function getCurrentEventImg(int $event_id)
    {
        $query= $this -> _connexion -> prepare("SELECT image 
                                                FROM event
                                                WHERE id= ?");
        $query -> execute([$event_id]);
        $imageNow = $query -> fetch();
        //var_dump($imageNow);
        return $imageNow;
    }
    
    /* SUPPRIMER EVENEMENT PAR ADMIN */
    public function deleteEventById(int $event_id)
    {
        $query= $this -> _connexion -> prepare("DELETE FROM event WHERE id= ?");
        $test= $query -> execute([$event_id]);
        //var_dump($test);
        return $test;
    }
}

?>