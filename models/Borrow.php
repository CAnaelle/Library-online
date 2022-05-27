<?php

class Borrow
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
                                METHODES USER
    *******************************************************************************/
    /* PAGE LISTE DES PRETS */
    public function getBorrowByIdUser(int $user_id): ?array
    {
        //préparer la requete
        $query = $this -> _connexion -> prepare("SELECT
                                                        borrow.id,
                                                        id_user,
                                                        id_book,
                                                        DATE_FORMAT(date_deb_pret, '%d/%m/%Y') AS date_deb_pret,
                                                        DATE_FORMAT(date_fin_pret, '%d/%m/%Y') AS date_fin_pret,
                                                        book.auteur,
                                                        book.titre,
                                                        book.tome,
                                                        book.statut,
                                                        book.image
                                                    FROM
                                                    borrow
                                                    INNER JOIN book ON borrow.id_book = book.id
                                                    WHERE id_user = ?
                                                    ORDER BY YEAR(date_deb_pret), MONTH(date_deb_pret), DAY(date_deb_pret)");
        //exécuter la requete
        $query->execute([$user_id]);
        //récupérer les infos de la requete
        $borrowByIdUser = $query ->fetchAll();
        //var_dump($borrowByIdUser);
        return $borrowByIdUser;
    }

    /*****************************************************************************
                                METHODES ADMIN
    *******************************************************************************/
     //AJOUT PRET
    public function addBorrow(int $user_id,int $book_id,string $dateDebPret,string $dateFinPret): ?bool
    {
        $query = $this -> _connexion -> prepare("INSERT INTO borrow (
                                                                        id_user,
                                                                        id_book,
                                                                        date_deb_pret,
                                                                        date_fin_pret
                                                                    )
                                                                    VALUES(?,?,?,?)");
        $test = $query->execute([$user_id,$book_id,$dateDebPret,$dateFinPret]);
        //var_dump($test);
        return $test;
    }

    //RECUPERER DONNEES (DATE FIN DE PRET) D'UN LIVRE EMPRUNTE POUR RESERVATION OU VERIFIER SI PRESENT DANS LISTE PRET
    public function getBorrowReturnDate(int $book_id)
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        id_user,
                                                        id_book,
                                                        DATE_FORMAT(date_fin_pret, '%d-%m-%Y') AS return_date,
                                                        date_fin_pret
                                                    FROM
                                                        borrow
                                                    WHERE
                                                        id_book = ?");
        $query->execute([$book_id]);
        $return_date = $query ->fetch();
        //var_dump($return_date);
        return $return_date;
    }

    //SUPPRIMER PRET
    public function deleteBorrow(int $book_id): ?bool
    {
        $query= $this -> _connexion -> prepare("DELETE FROM borrow WHERE id_book= ?");
        $test= $query -> execute([$book_id]);
        //var_dump($test);
        return $test;
    }

     /* RECUPERER LA LISTE DE TOUS LES PRETS */
    public function getListBorrowAdmin()
    {
        $query= $this -> _connexion -> prepare("SELECT
                                                        borrow.id,
                                                        id_user,
                                                        id_book,
                                                        DATE_FORMAT(date_deb_pret, '%d/%m/%Y') AS date_deb_pret,
                                                        date_fin_pret AS returnDate,
                                                        DATE_FORMAT(date_fin_pret, '%d/%m/%Y') AS date_fin_pret,
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
                                                        borrow
                                                    INNER JOIN user ON user.id = id_user
                                                    INNER JOIN book ON book.id = id_book
                                                    ORDER BY YEAR(date_deb_pret), MONTH(date_deb_pret), DAY(date_deb_pret)");
        $query->execute();
        $allBorrow = $query ->fetchAll();
        //var_dump($allBorrow);
        return $allBorrow;
    }
    

    /* RECUPERER LES PRETS EN FONCTION DE LA DATE DE DEBUT DE PRET*/
    public function getListBorrowAdminWithDate(string $dateFirst, string $dateSecond)
    {
        $query= $this -> _connexion -> prepare("SELECT
                                                        borrow.id,
                                                        id_user,
                                                        id_book,
                                                        DATE_FORMAT(date_deb_pret, '%d/%m/%Y') AS date_deb_pret,
                                                        DATE_FORMAT(date_fin_pret, '%d/%m/%Y') AS date_fin_pret,
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
                                                        borrow
                                                    INNER JOIN user ON user.id = id_user
                                                    INNER JOIN book ON book.id = id_book
                                                    WHERE date_deb_pret BETWEEN ? AND ? 
                                                    ORDER BY YEAR(date_deb_pret), MONTH(date_deb_pret), DAY(date_deb_pret)");
        $query->execute([$dateFirst,$dateSecond]);
        $borrowWithDate = $query ->fetchAll();
        //var_dump($borrowWithDate);
        return $borrowWithDate;
    }

     /* RECUPERER LE NOMBRE DE RETARDS DE PRETS EN FONCTION DE LA DATE DE FIN DE PRET*/
    public function getlateBorrowAdmin(string $dateFirst)
    {
        $query= $this -> _connexion -> prepare("SELECT DATEDIFF(?,date_fin_pret) FROM borrow");
        $query->execute([$dateFirst]);
        $lateBorrow = $query ->fetchAll();
       //var_dump($lateBorrow);
        return $lateBorrow;
    }

    /* RECUPERER LA LISTE DES RETARDS DE PRETS*/
    public function getDelayAdmin(string $date)
    {
        $query= $this -> _connexion -> prepare("SELECT
                                                        borrow.id,
                                                        id_user,
                                                        id_book,
                                                        DATE_FORMAT(date_deb_pret, '%d/%m/%Y') AS date_deb_pret,
                                                        date_fin_pret AS returnDate,
                                                        DATE_FORMAT(date_fin_pret, '%d/%m/%Y') AS date_fin_pret,
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
                                                        borrow
                                                    INNER JOIN user ON user.id = id_user
                                                    INNER JOIN book ON book.id = id_book
                                                    WHERE date_fin_pret < ?
                                                    ORDER BY date_deb_pret DESC ");
        $query->execute([$date]);
        $allDelay = $query ->fetchAll();
        //var_dump($allDelay);
        return $allDelay;
    }
}

?>