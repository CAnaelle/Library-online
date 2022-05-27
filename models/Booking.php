<?php

class Booking
{    
    //pptés de connexion
    private $_database;
    private $_connexion;
    
    
    public function __construct()
    {
        $this -> _database = new DataBase();
        $this -> _connexion = $this -> _database ->getConnexion();
    }
    
     /*****************************************************************************
                                METHODE USER
    *****************************************************************************/
    
     /* AJOUT RESERVATION */
    public function addBooking(int $user_id,int $book_id,string $dateDebReservation,string $dateFinReservation, string $dateAjout): ?bool
    {
        //préparer la requete
        $query = $this -> _connexion -> prepare("INSERT INTO booking (
                                                                        id_user,
                                                                        id_book,
                                                                        date_deb_reserv,
                                                                        date_fin_reserv,
                                                                        date_ajout
                                                                    )
                                                                    VALUES(?,?,?,?,?)");
        //tester la requete
        $test = $query->execute([$user_id,$book_id,$dateDebReservation,$dateFinReservation,$dateAjout]);
        //var_dump($test);
        return $test;
    }

    /* PAGE LISTE DES RESERVATIONS ET DETAILS  USER ADMIN*/
    public function getBookingByIdUser(int $user_id): ?array
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        booking.id,
                                                        id_user,
                                                        id_book,
                                                        DATE_FORMAT(date_deb_reserv, '%d/%m/%Y') AS date_deb_reserv,
                                                        DATE_FORMAT(date_fin_reserv, '%d/%m/%Y') AS date_fin_reserv,
                                                        DATE_FORMAT(booking.date_ajout, '%d/%m/%Y %H:%i:%s') AS date_ajout,
                                                        CONCAT(UPPER(LEFT(book.auteur,1)),SUBSTRING(book.auteur,2)) AS auteur,
                                                        CONCAT(UPPER(LEFT(book.titre,1)),SUBSTRING(book.titre,2)) AS titre,
                                                        book.tome,
                                                        book.statut,
                                                        book.localisation_interne,
                                                        book.image
                                                    FROM
                                                    booking
                                                    INNER JOIN book ON booking.id_book = book.id
                                                    WHERE id_user = ?
                                                    ORDER BY YEAR(date_deb_reserv), MONTH(date_deb_reserv), DAY(date_deb_reserv)");
        $query->execute([$user_id]);
        $bookingByIdUser = $query ->fetchAll();
        //var_dump($bookingByIdUser);
        return $bookingByIdUser;
    }

    /* PAGE CONFIRMATION RESERVATION LIMIT 3 */
    public function getSomeBookingByIdUser(int $user_id): ?array
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        booking.id,
                                                        id_user,
                                                        id_book,
                                                        DATE_FORMAT(date_deb_reserv, '%d/%m/%Y') AS date_deb_reserv,
                                                        DATE_FORMAT(date_fin_reserv, '%d/%m/%Y') AS date_fin_reserv,
                                                        book.titre,
                                                        book.tome
                                                    FROM
                                                    booking
                                                    INNER JOIN book ON booking.id_book = book.id
                                                    WHERE id_user = ?
                                                    ORDER BY YEAR(date_deb_reserv) DESC, MONTH(date_deb_reserv) DESC, DAY(date_deb_reserv) DESC  
                                                    LIMIT 3");
        $query->execute([$user_id]);
        $someBookingByIdUser = $query ->fetchAll();
        //var_dump($someBookingByIdUser);
        return $someBookingByIdUser;
    }

    /* SUPPRIMER RESERVATION BY USER */
    public function deleteReservation(int $reservation_id): ?bool
    {
        $query= $this -> _connexion -> prepare("DELETE FROM booking WHERE id= ?");
        $test= $query -> execute([$reservation_id]);
        //var_dump($test);
        return $test;
    }


    /*****************************************************************************
                                METHODES ADMIN
    *******************************************************************************/
    /* RECUPERER DONNEES D'UN LIVRE PRESENT DANS LA TABLE RESERVATION LORS D'UN PRET OU AUTRE*/
    public function getUserForOneBooking(int $book_id)
    {
        $query= $this -> _connexion -> prepare("SELECT
                                                        booking.id,
                                                        id_user,
                                                        id_book,
                                                        date_deb_reserv,
                                                        date_fin_reserv,
                                                        CONCAT(UPPER(LEFT(user.nom,1)),LOWER(SUBSTRING(user.nom,2))) AS Nom,
                                                        CONCAT(UPPER(LEFT(user.prenom,1)),LOWER(SUBSTRING(user.prenom,2))) AS Prenom,
                                                        user.numero_etu
                                                    FROM
                                                        booking
                                                        INNER JOIN user ON user.id= id_user
                                                    WHERE
                                                        id_book= ? ");
         //exécuter la requete
        $query->execute([$book_id]);
        //récupérer les infos de la requete
        $userBooking = $query ->fetch();
        //var_dump($userBooking);
        return $userBooking;
    }

     /* MODIFICATION DE LA DATE DE RESERVATION LORS D'UN RETOUR DE PRET*/
    public function updateBooking(string $dateDebReservation,string $dateFinReservation, string $dateAjout, int $book_id, int $user_id): ?bool
    {
        $query = $this -> _connexion -> prepare("UPDATE booking SET
                                                                    date_deb_reserv = ?,
                                                                    date_fin_reserv = ?,
                                                                    date_ajout = ?
                                                                WHERE id_book = ? AND id_user = ?");
        $test = $query->execute([$dateDebReservation, $dateFinReservation, $dateAjout, $book_id, $user_id]);
        //var_dump($test);
        return $test;
    }

    /* RECUPERER LA LISTE DE TOUTES LES RESERVATIONS */
    public function getListBookingAdmin()
    {
        $query= $this -> _connexion -> prepare("SELECT
                                                        booking.id,
                                                        id_user,
                                                        id_book,
                                                        DATE_FORMAT(date_deb_reserv, '%d/%m/%Y') AS date_deb_reserv,
                                                        DATE_FORMAT(date_fin_reserv, '%d/%m/%Y') AS date_fin_reserv,
                                                        DATE_FORMAT(booking.date_ajout, '%d/%m/%Y %H:%i:%s') AS date_ajout,
                                                        CONCAT(
                                                            UPPER(LEFT(user.nom, 1)),
                                                            LOWER(SUBSTRING(user.nom, 2))
                                                        ) AS Nom,
                                                        CONCAT(
                                                            UPPER(LEFT(user.prenom, 1)),
                                                            LOWER(SUBSTRING(user.prenom, 2))
                                                        ) AS Prenom,
                                                        user.numero_etu,
                                                        tel,
                                                        email,
                                                        titre,
                                                        auteur,
                                                        tome,
                                                        localisation_interne,
                                                        statut
                                                    FROM
                                                        booking
                                                    INNER JOIN user ON user.id = id_user
                                                    INNER JOIN book ON book.id = id_book
                                                    ORDER BY YEAR(booking.date_ajout) DESC, MONTH(booking.date_ajout) DESC, DAY(booking.date_ajout) DESC");
        $query->execute();
        $allBooking = $query ->fetchAll();
        //var_dump($allBooking);
        return $allBooking;
    }

    /* RECUPERER LES RESERVATIONS EN FONCTION DE LA DATE DE DEB DE RESERV*/
    public function getListBookingAdminWithDate(string $dateFirst,  string $dateSecond)
    {
        $query= $this -> _connexion -> prepare("SELECT
                                                        booking.id,
                                                        id_user,
                                                        id_book,
                                                        DATE_FORMAT(date_deb_reserv, '%d/%m/%Y') AS date_deb_reserv,
                                                        DATE_FORMAT(date_fin_reserv, '%d/%m/%Y') AS date_fin_reserv,
                                                        DATE_FORMAT(booking.date_ajout, '%d/%m/%Y %H:%i:%s') AS date_ajout,
                                                        CONCAT(
                                                            UPPER(LEFT(user.nom, 1)),
                                                            LOWER(SUBSTRING(user.nom, 2))
                                                        ) AS Nom,
                                                        CONCAT(
                                                            UPPER(LEFT(user.prenom, 1)),
                                                            LOWER(SUBSTRING(user.prenom, 2))
                                                        ) AS Prenom,
                                                        user.numero_etu,
                                                        tel,
                                                        email,
                                                        titre,
                                                        auteur,
                                                        tome,
                                                        localisation_interne,
                                                        statut
                                                    FROM
                                                        booking
                                                    INNER JOIN user ON user.id = id_user
                                                    INNER JOIN book ON book.id = id_book
                                                    WHERE booking.date_ajout BETWEEN ? AND ? 
                                                    ORDER BY booking.date_ajout DESC ");
        $query->execute([$dateFirst,$dateSecond]);
        $bookingWithDate = $query ->fetchAll();
        //var_dump($allBooking);
        return $bookingWithDate;
    }
}

?>