<?php

class Book
{
    private $_database;
    private $_connexion;
    
    public function __construct()
    {
        $this -> _database = new DataBase();
        $this -> _connexion = $this -> _database ->getConnexion();
    }

    //METHODE REQUETE RECUPERER NOUVELLES ACQUISITIONS LIMIT 6 livres
    public function getBooksCurrent(): array
    {
        //préparer la requete
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        isbn,
                                                        CONCAT(UPPER(LEFT(auteur,1)),SUBSTRING(auteur,2)) AS auteur,
                                                        CONCAT(UPPER(LEFT(titre,1)),SUBSTRING(titre,2)) AS titre,
                                                        CONCAT(UPPER(LEFT(editeur,1)),LOWER(SUBSTRING(editeur,2))) AS editeur,
                                                        date_parution,
                                                        CONCAT(UPPER(LEFT(collection,1)),LOWER(SUBSTRING(collection,2))) AS collection,
                                                        CONCAT(UPPER(LEFT(genre,1)),LOWER(SUBSTRING(genre,2))) AS genre,
                                                        tome,
                                                        nbre_pages,
                                                        format,
                                                        resume,
                                                        mot_cles,
                                                        UPPER(cote) as cote,
                                                        localisation_interne,
                                                        categorie,
                                                        statut,
                                                        image
                                                    FROM
                                                        book
                                                    ORDER BY id DESC
                                                    LIMIT 6");
        //exécuter la requete
        $query->execute();
        //récupérer les infos de la requete
        $books = $query ->fetchAll();
        //var_dump($books);
        return $books;
    }

    //SELECTION DES DIFFERENTES CATEGORIES EXISTANTES
    public function categorieBook(): array
    {
        $query = $this -> _connexion -> prepare("SELECT DISTINCT
                                                                categorie
                                                            FROM
                                                                book
                                                            ORDER BY
                                                                categorie");
        $query->execute();
        $categories = $query ->fetchAll();
        //var_dump($categories);
        return $categories;
    }

    //SUGGESTIONS DE 10 LIVRES MAX ALEATOIRES PAR CATEGORIE
    public function getBooksRandByCategory(string $categorie): array
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        isbn,
                                                        CONCAT(UPPER(LEFT(auteur,1)),SUBSTRING(auteur,2)) AS auteur,
                                                        CONCAT(UPPER(LEFT(titre,1)),SUBSTRING(titre,2)) AS titre,
                                                        CONCAT(UPPER(LEFT(editeur,1)),LOWER(SUBSTRING(editeur,2))) AS editeur,
                                                        date_parution,
                                                        CONCAT(UPPER(LEFT(collection,1)),LOWER(SUBSTRING(collection,2))) AS collection,
                                                        CONCAT(UPPER(LEFT(genre,1)),LOWER(SUBSTRING(genre,2))) AS genre,
                                                        tome,
                                                        nbre_pages,
                                                        format,
                                                        SUBSTRING(resume,1,400) AS resume,
                                                        mot_cles,
                                                        UPPER(cote) as cote,
                                                        localisation_interne,
                                                        categorie,
                                                        statut,
                                                        image
                                            FROM
                                                book
                                            WHERE
                                                categorie = ?
                                            ORDER BY RAND() LIMIT 10 ");
        $query->execute([$categorie]);
        $books = $query ->fetchAll();
        //var_dump($books);
        return $books;
    }

    //METHODE REQUETE BARRE DE RECHERCHE SIMPLE: proposer 5 livres correspondant à la recherche sous barre de recherche
    public function searchBookBdd(string $keywords): ?array
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        CONCAT(UPPER(LEFT(titre,1)),SUBSTRING(titre,2)) AS titre
                                                    FROM
                                                        book
                                                    WHERE LOWER(titre) LIKE ? OR LOWER(mot_cles) LIKE ? OR LOWER(auteur) LIKE ? LIMIT 5");
        $query -> execute(['%'.$keywords.'%','%'.$keywords.'%','%'.$keywords.'%']);
        $books_research = $query -> fetchAll();
        //var_dump($books_research);
        return $books_research;
    }

    //METHODE REQUETE RETOURNE UN LIVRE AVEC L'ID
    public function getBookById(int $book_id)
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        isbn,
                                                        CONCAT(UPPER(LEFT(auteur,1)),SUBSTRING(auteur,2)) AS auteur,
                                                        CONCAT(UPPER(LEFT(titre,1)),SUBSTRING(titre,2)) AS titre,
                                                        CONCAT(UPPER(LEFT(editeur,1)),LOWER(SUBSTRING(editeur,2))) AS editeur,
                                                        date_parution AS date_par,
                                                        DATE_FORMAT(date_parution, '%d-%m-%Y') AS date_parution,
                                                        CONCAT(UPPER(LEFT(collection,1)),LOWER(SUBSTRING(collection,2))) AS collection,
                                                        CONCAT(UPPER(LEFT(genre,1)),LOWER(SUBSTRING(genre,2))) AS genre,
                                                        tome,
                                                        nbre_pages,
                                                        format,
                                                        resume,
                                                        mot_cles,
                                                        UPPER(cote) as cote,
                                                        localisation_interne,
                                                        categorie,
                                                        statut,
                                                        image
                                                    FROM
                                                        book
                                                    WHERE
                                                        id = ? ");
        $query -> execute([$book_id]);
        $books_result = $query -> fetch();
        //var_dump($books_result);
        return $books_result;
    }

    //METHODE REQUETE BARRE DE RECHERCHE AVANCEE-> limite de 20 résultats, liste des résultats
    public function getBooksList(string $one_keyword): ?array
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        CONCAT(UPPER(LEFT(auteur,1)),SUBSTRING(auteur,2)) AS auteur,
                                                        CONCAT(UPPER(LEFT(titre,1)),SUBSTRING(titre,2)) AS titre,
                                                        CONCAT(UPPER(LEFT(genre,1)),LOWER(SUBSTRING(genre,2))) AS genre,
                                                        SUBSTRING(resume,1,400) AS resume,
                                                        tome,
                                                        image
                                                    FROM
                                                        book
                                                    WHERE CONCAT(LOWER(titre),' ', LOWER(mot_cles),' ', LOWER(auteur)) LIKE ? 
                                                    ORDER BY titre, tome DESC LIMIT 20");
        $query -> execute(['%'.$one_keyword.'%']);
        $research = $query -> fetchAll();
        //var_dump($research);
        return $research;
    }

    /*****************************************************************************
                                METHODE USER 
    *****************************************************************************/
    //RECUPERE LE STATUT DU LIVRE (EX: POUR VERIFIER STATUT LIVRE AVANT AJOUT AUX RESERVATIONS OU PRETS)
    public function getStatusBook(int $book_id)
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        statut
                                                    FROM
                                                        book
                                                    WHERE
                                                        id = ?");
        $query->execute([$book_id]);
        $statusBook = $query ->fetch();
        //var_dump($statusBook);
        return $statusBook;
    }

    //MODIFIER STATUT DU LIVRE (EX:LORS DE LA RESERVATION OU DU PRET)
    public function updateStatusBook(string $statusToUpdate,int $book_id)
    {
        $query = $this -> _connexion -> prepare("UPDATE
                                                        book
                                                    SET
                                                        statut = ?
                                                    WHERE
                                                        id = ?");
        $newStatusBook = $query->execute([$statusToUpdate,$book_id]);
        //var_dump($newStatusBook);
        return $newStatusBook;
    }

     /*****************************************************************************
                                METHODE ADMIN 
    *****************************************************************************/
    //RECUPERE ID ET STATUT BOOK
    public function getIdBookAndStatut(string $title, ?string $vol)
    {
        try
        {
            $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        statut
                                                    FROM
                                                        book
                                                    WHERE
                                                        LOWER(titre) = ?  AND tome = ?");
            $query->execute([$title, $vol]);
            $id_book = $query ->fetch();
            //var_dump($id_book);
            return $id_book;
        }
        catch(Exception $e){
            return $e->getMessage();
        }
    }

    //AJOUTER UN LIVRE DANS BDD
    public function addBookInBdd(string $isbn, string $auteur, string $titre, string $editeur, string $date, ?string $collection, string $genre, ?string $tome, ?int $nombre, string $format, string $resume, string $cle, string $cote, string $localisation, ?string $categorie, ?string $statut, ?string $imageName, string $ajout)
    {
         $query = $this -> _connexion -> prepare("INSERT INTO book(
                                                                    isbn,
                                                                    auteur,
                                                                    titre,
                                                                    editeur,
                                                                    date_parution,
                                                                    collection,
                                                                    genre,
                                                                    tome,
                                                                    nbre_pages,
                                                                    format,
                                                                    resume,
                                                                    mot_cles,
                                                                    cote,
                                                                    localisation_interne,
                                                                    categorie,
                                                                    statut,
                                                                    image,
                                                                    date_ajout
                                                                    )
                                                            VALUES(
                                                                    ?,
                                                                    ?,
                                                                    ?,
                                                                    LOWER(?),
                                                                    ?,
                                                                    ?,
                                                                    LOWER(?),
                                                                    ?,
                                                                    ?,
                                                                    LOWER(?),
                                                                    ?,
                                                                    LOWER(?),
                                                                    UPPER(?),
                                                                    LOWER(?),
                                                                    LOWER(?),
                                                                    LOWER(?),
                                                                    ?,
                                                                    ?
                                                            )");
        $test = $query->execute([$isbn, $auteur, $titre, $editeur, $date, $collection, $genre, $tome, $nombre, $format, $resume, $cle, $cote, $localisation, $categorie, $statut, $imageName, $ajout]);
        //var_dump($test);
        return $test;
    }

    //MODIFIER LA NOTICE D'UN LIVRE SANS IMAGE
    public function updateBookWithoutImg(string $isbn, string $auteur, string $titre, string $editeur, string $date, ?string $collection, string $genre, ?string $tome, int $nombre, string $format, string $resume, string $cle, string $cote, string $localisation, string $categorie, int $book_id)
    {
        try
        {
            $query= $this -> _connexion -> prepare("UPDATE book 
                                                    SET 
                                                        isbn = ?,
                                                        auteur = ?,
                                                        titre = ?,
                                                        editeur = ?,
                                                        date_parution = ?,
                                                        collection = ?,
                                                        genre = ?,
                                                        tome = ?,
                                                        nbre_pages = ?,
                                                        format = ?,
                                                        resume = ?,
                                                        mot_cles = ?,
                                                        cote = ?,
                                                        localisation_interne = ?,
                                                        categorie = ?
                                                    WHERE id= ?");
            $test = $query -> execute([$isbn, $auteur, $titre,  $editeur, $date, $collection, $genre, $tome, $nombre, $format, $resume, $cle, $cote, $localisation, $categorie, $book_id]);
            //var_dump($test);
            return $test;
         }
        catch(Exception $e){
            return $e->getMessage();
        }
    }

    //MODIFIER LA NOTICE D'UN LIVRE AVEC IMAGE
    public function updateBookWithImg(string $isbn, string $auteur, string $titre, string $editeur, string $date, ?string $collection, string $genre, ?string $tome, int $nombre, string $format, string $resume, string $cle, string $cote, string $localisation, string $categorie, string $imageName, int $book_id)
    {
        $query= $this -> _connexion -> prepare("UPDATE book 
                                                SET 
                                                    isbn = ?,
                                                    auteur = ?,
                                                    titre = ?,
                                                    editeur = ?,
                                                    date_parution = ?,
                                                    collection = ?,
                                                    genre = ?,
                                                    tome = ?,
                                                    nbre_pages = ?,
                                                    format = ?,
                                                    resume = ?,
                                                    mot_cles = ?,
                                                    cote = ?,
                                                    localisation_interne = ?,
                                                    categorie = ?,
                                                    image = ?
                                                WHERE id= ?");
                
        $test = $query -> execute([$isbn, $auteur, $titre,  $editeur, $date, $collection, $genre, $tome, $nombre, $format, $resume, $cle, $cote, $localisation, $categorie, $imageName, $book_id]);
        //var_dump($test);
        return $test;

    }

    //RECUPERE LE NOM DE L'IMAGE ACTUELLE AVEC L'ID
    public function getCurrentImg(int $book_id)
    {
        $query= $this -> _connexion -> prepare("SELECT image 
                                                FROM book
                                                WHERE id= ?");
        $query -> execute([$book_id]);
        $imageNow = $query -> fetch();
        //var_dump($imageNow);
        return $imageNow;
    }

    //SUPPRIME UN LIVRE DE LA BDD
    public function deleteBookBdd(int $book_id)
    {
        $query= $this -> _connexion -> prepare("DELETE 
                                                FROM book 
                                                WHERE id= ?");
        $delete = $query -> execute([$book_id]);
        //var_dump($delete);
        return $delete;
    }
}

?>