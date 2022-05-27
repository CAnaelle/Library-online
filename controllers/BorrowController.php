<?php

require "models/Borrow.php";

class BorrowController
{
    private $_borrow;
    private $_book;
    private $_booking;
    private $_user;
    private $_userController;
    private $_adminController;
    
    public function __construct()
    {
        $this -> _borrow = new Borrow();
        $this -> _book = new Book();
        $this -> _booking = new Booking();
        $this -> _user = new User();
        $this -> _userController = new UserController();
        $this -> _adminController = new AdminController();
    }
    
    /*****************************************************************************
                                METHODES USER
    *******************************************************************************/
    /* Methode d'affichage de la liste des prêts de user */
    public function listBorrowUser()
    {
        if($this -> _userController ->is_connect())
        {
            //appel au template pour afficher liste des prêts
            $titre="Liste de vos prêts";
            $template = "borrow/listBorrowUser";
            
            if(isset($_SESSION['id']))
            {
                $user_id =$_SESSION['id'];
                $borrowByIdUser= $this -> _borrow -> getBorrowByIdUser($user_id);
            }
            else
            {
                $messageUser= "Une erreur est survenue.";
            }
            //appel au layout
            require "views/layout.phtml";
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }



    /*****************************************************************************
                                METHODES ADMIN
    *******************************************************************************/
    //FONCTION BARRE DE RECHERCHE AVEC AJAX-> retourne les titres de livres ayant la valeur entrée dans titre ou mots-clés ou auteur
    public function researchBookAdmin()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            if(array_key_exists('research',$_GET))
            {
                $keywords = htmlspecialchars(trim($_GET['research']));
                //var_dump($keywords);
    
                //appel fonction requete searchBookBdd
                $books_research = $this -> _book -> searchBookBdd($keywords);
    
                require "views/borrow/bookSearchTargetAdmin.phtml";
            }
            else
            {
                $messageInform = 'Une erreur est survenue.';
            }
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }

    //EMPRUNTER: Ajouter un prêt
    /* 1-Récupérer l'id book et statut book du livre correspondant et id user de l'utilisateur correspondant*/
    /* 2-vérifier statut livre */
    /* 3-Insérer dans BDD prêt */
    /* 4-changer statut livre */
    public function borrowBook()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            if(isset($_POST['name']) && isset($_POST['number']) && isset($_POST['titre']) && isset($_POST['tome']))
            {
                
                $errors=[];
                if(!empty($_POST['name']) && !empty($_POST['number']) && !empty($_POST['titre']))
                {
                    $name = htmlspecialchars(trim($_POST['name']));
                    $number = htmlspecialchars(trim($_POST['number']));
                    $title = htmlspecialchars(trim($_POST['titre']),ENT_NOQUOTES);//pour les titres avec apostrophe
                    $vol = htmlspecialchars(trim($_POST['tome']));
                    //var_dump($title,$vol);
                    //var_dump($vol, $number); //->string

                    //appel à la fonction pour récupérer id_book et statut
                    $book= $this -> _book -> getIdBookAndStatut($title, $vol);
                    //var_dump($book);
                    
                    //appel à la fonction pour récupérer id_user
                    if(is_numeric($number))
                    {
                        $number = intval($number);
                        $idUser= $this -> _user -> getIdUser($name, $number);
                            
                        //si les deux ids (et statut book) sont bien récupérés on lance l'insertion du prêt dans la BDD
                        if($book && $idUser)
                        {
                            date_default_timezone_set('Europe/Paris');
                            $user_id= intval($idUser['id']);
                            $book_id= intval($book['id']);
                            $dateDebPret = date('Y-m-d H:i:s');
                            $dateFinPret= date('Y-m-d H:i:s', strtotime($dateDebPret. ' + 30 days'));
                            $dateDebP = date('d-m-Y');
                            $dateFinP = date('d-m-Y', strtotime($dateFinPret));
                            //var_dump($user_id,$book_id,$dateDebPret,$dateFinPret,$dateDebP,$dateFinP);
                            
                            switch($book['statut'])
                            {
                                //si dispo -> prêt possible
                                case 'disponible':
                                    $borrow= $this -> _borrow -> addBorrow($user_id,$book_id,$dateDebPret,$dateFinPret);
                                    //var_dump($borrow);
                                    //CHANGER LE STATUT EN PRET
                                    $statusToUpdate = 'en prêt';
                                    $newStatusBook= $this -> _book -> updateStatusBook($statusToUpdate,$book_id);
                                    //var_dump($newStatusBook);
    
                                    //vérifier $borrow
                                    if($borrow && $newStatusBook)
                                    {
                                        $messageAdmin= 'Le prêt a bien été pris en compte.';
                                    }
                                    else
                                    {
                                        $messageAdmin= 'Une erreur est survenue, veuillez de nouveau essayer le prêt.';
                                    }
                                break;
                                //si en prêt -> pb !
                                case 'en prêt':
                                    $messageAdmin = 'Le livre est déjà sous le statut de prêt, veuillez vérifier ses informations.';
                                break;
                                //si consultation sur place -> pas de prêt possible
                                case 'consultation sur place':
                                    $messageAdmin= 'Le livre est uniquement consultable sur place.';
                                break;
                                //si réservé -> prêt possible mais controle avant (vérifier si la personne présente est celle qui a réservé le prêt)
                                case 'réservé':
                                    $getUserBooking= $this -> _booking -> getUserForOneBooking($book_id);
                                    //var_dump($getUserBooking);
    
                                    if($getUserBooking)
                                    {
                                        //si l'id lecteur entré par admin = id de l'user qui a réservé alors on lui met en prêt sinon il s'agit d'un autre lecteur qui a emprunté
                                        if($user_id == $getUserBooking['id_user'])
                                        {
                                            $borrow= $this -> _borrow -> addBorrow($user_id,$book_id,$dateDebPret,$dateFinPret);
                                            //CHANGER LE STATUT EN PRET
                                            $statusToUpdate = 'en prêt';
                                            $newStatusBook= $this -> _book -> updateStatusBook($statusToUpdate,$book_id);
                                            //var_dump($newStatusBook);
                                            
                                            if($getUserBooking)
                                            {
                                                //récupère l'id de la réservation
                                                $reservation_id= $getUserBooking['id'];
                                                //var_dump($reservation_id);
                                                //supprimer de la table des réservations
                                                $delete= $this -> _booking -> deleteReservation($reservation_id);
                                            }
                                            else
                                            {
                                                $messageAdmin= 'La suppression des réservations n\'a pas eu lieu.';
                                            }
                                            
                                            if($borrow && $newStatusBook && isset($delete))
                                            {
                                                $messageAdmin= 'Le prêt a bien été pris en compte';
                                            }
                                            else
                                            {
                                                $messageAdmin= 'Une erreur est survenue, veuillez de nouveau essayer le prêt';
                                            }
                                        }
                                        else
                                        {
                                            //affichage
                                            $dateFinR = date('d-m-Y', strtotime($getUserBooking['date_fin_reserv']));
                                            $messageAdmin= 'Le livre est actuellement réservé par '.$getUserBooking['Nom'].' '.$getUserBooking['Prenom'].' ayant le numéro étudiant/lecteur: '.$getUserBooking['numero_etu'].', jusqu\'au '.$dateFinR;
                                        }
    
                                    }
                                    else
                                    {
                                        $messageAdmin= 'Une erreur est survenue.';
                                    }
                                break;
                            }
                            
                        }
                        else if ($idUser == false){
                            $errors['user'] = 'Aucun utilisateur ne correspond à la recherche indiquée.';
                        }
                        else if ($book == false){
                            $errors['book'] = 'Aucun livre ne correspond à la recherche indiquée.';
                        }
                    }
                    else
                    {
                        $errors['user'] = 'Le numéro étudiant n\'est pas valide.';
                    }
                }
                else
                {
                     $messageAdmin = "Veuillez remplir les champs.";
                }
            }
        //appel au template et layout
        $titre="Ajouter un prêt";
        $template = "borrow/borrow";
        require "views/layoutAdmin.phtml";
            
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }

    //RETOUR D'UN PRET
    public function returnBook()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            //1-récupérer id book avec entrée de l'admin
            if(isset($_POST['name']) && isset($_POST['number']) && isset($_POST['titre']) && isset($_POST['tome']))
            {
                if(!empty($_POST['name']) && !empty($_POST['number']) && !empty($_POST['titre']))
                {
                    $name = htmlspecialchars(trim($_POST['name']));
                    $number = htmlspecialchars(trim($_POST['number']));
                    $title = htmlspecialchars(trim($_POST['titre']),ENT_NOQUOTES);//pour les titres avec apostrophe
                    $vol = htmlspecialchars(trim($_POST['tome']));
                    //var_dump($vol, $number); //-> string !!

                    //appel à la fonction pour récupérer id_book et statut
                    $book= $this -> _book -> getIdBookAndStatut($title, $vol);
                    //var_dump($book);
                    
                    //appel à la fonction pour récupérer id_user
                    if(is_numeric($number))
                    {
                        $number = intval($number);
                        $idUser= $this -> _user -> getIdUser($name, $number);
                            
                        //si les deux ids (et statut book) sont bien récupérés on lance le retour du prêt
                        if($book && $idUser)
                        {
                            $user_id= intval($idUser['id']);
                            $book_id= intval($book['id']);
                            //var_dump(gettype($book_id));
                            
                            //2-vérifier si le livre est dans la liste des réservations ou pas
                            $booking = $this -> _booking -> getUserForOneBooking($book_id);
                            //var_dump($booking);
                            if($booking)
                            {
                                //2.1 si oui
                                //vérification que le livre soit bien dans la table des prêts dans le cas où il y aurait pas eu l'enregistrement au moment du prêt(sinon gestion erreur)
                                $controlBorrow = $this -> _borrow -> getBorrowReturnDate($book_id);
                                //var_dump($controlBorrow);
                                if($controlBorrow)
                                {
                                    //on vérifie que le prêt est assigné à la bonne personne
                                    if($user_id == $controlBorrow['id_user'])
                                    {
                                        //suppression du livre de la table des prêts
                                        $deleteBorrow = $this -> _borrow -> deleteBorrow($book_id);
                                        
                                        if($deleteBorrow)
                                        {
                                            //changement de statut
                                            $statusToUpdate = 'réservé';
                                            $newStatusBook= $this -> _book -> updateStatusBook($statusToUpdate,$book_id);
    
                                            if($newStatusBook)
                                            {
                                                //modifier les dates de la réservation à la date de rendu (actuelle)
                                                date_default_timezone_set('Europe/Paris');
                                                $book_id = $booking['id_book'];
                                                $user_id = $booking['id_user'];
                                                $dateDebReservation = date('Y-m-d H:i:s');
                                                $dateFinReservation = date('Y-m-d H:i:s', strtotime($dateDebReservation. ' + 15 days'));
                                                $dateAjout = date('Y-m-d H:i:s');
                                                $newDateBooking= $this -> _booking -> updateBooking($dateDebReservation, $dateFinReservation, $dateAjout, $book_id, $user_id);
                                                if($newDateBooking)
                                                {
                                                    $messageAdmin = 'Le livre a bien été enlevé des prêts.';
                                                }
                                                else
                                                {
                                                    $messageAdmin = 'Les dates de la prochaine réservation ne se sont pas modifiées.';
                                                }
                                                
                                            }
                                            else
                                            {
                                                $messageAdmin = 'Une erreur est survenue, le statut n\'a pas été modifié.';
                                            }
                                        }
                                        else
                                        {
                                            $messageAdmin = 'Une erreur est survenue, la suppression n\'a pas eu lieu.';
                                        }
                                    }
                                    else
                                    {
                                        $messageAdmin = 'Le prêt ne correspond pas à l\'utilisateur entré.';
                                    }
                                    
                                }
                                else
                                {
                                    $messageAdmin = 'Le livre n\'est actuellement pas dans la table des prêts.';
                                }
                            }
                            else
                            {
                                //2.2 sinon 
                                    //vérification que le livre soit bien dans la table des prêts dans le cas où il y aurait pas eu l'enregistrement au moment du prêt(sinon gestion erreur)
                                    $controlBorrow = $this -> _borrow -> getBorrowReturnDate($book_id);
                                    //var_dump($controlBorrow);
                                    if($controlBorrow)
                                    {
                                        //on vérifie que le prêt est assigné à la bonne personne
                                        if($user_id == $controlBorrow['id_user'])
                                        {
                                            //suppression du livre de la table des prêts
                                                $deleteBorrow = $this -> _borrow -> deleteBorrow($book_id);
                                            //changement de statut en disponible
                                                $statusToUpdate = 'disponible';
                                                $newStatusBook= $this -> _book -> updateStatusBook($statusToUpdate,$book_id);
    
                                                if($deleteBorrow && $newStatusBook)
                                                {
                                                    $messageAdmin = 'Le livre a été enlevé des prêts.';
                                                }
                                                else
                                                {
                                                    $messageAdmin = 'Une erreur est survenue, soit le livre n\'a pas été retiré de la liste des prêts soit le statut n\'a pas été mis à jour.';
                                                }
                                        }
                                        else
                                        {
                                            $messageAdmin = 'Le prêt ne correspond pas à l\'utilisateur entré.';
                                        }
                                    }
                                    else
                                    {
                                        $messageAdmin = 'Le livre n\'est actuellement pas dans la table des prêts.';
                                    }
                            }
                        
                        }
                        else if ($idUser == false){
                            $errors['user'] = 'Aucun utilisateur ne correspond à la recherche indiquée.';
                        }
                        else if ($book == false){
                            $errors['book'] = 'Aucun livre ne correspond à la recherche indiquée.';
                        }
                    }
                    else
                    {
                        $errors['user'] = 'Le numéro étudiant n\'est pas valide.';
                    }
                }
                else
                {
                    $messageAdmin = "Veuillez remplir les champs.";
                }
            }
        //appel au template et layout
        $titre="Remettre un livre en service";
        $template = "borrow/returnBook";
        require "views/layoutAdmin.phtml";
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }
    

    //AFFICHER LISTE DES PRETS
    public function listBorrowAdmin()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            $titre="Liste des prêts";
            $template = "borrow/listBorrowAdmin";

            $prets= $this -> _borrow -> getListBorrowAdmin();
            
            //si champs remplis on récupère valeur et on affiche dans tableau
            if(isset($_POST['dateFirst']) && !empty($_POST['dateFirst']) && isset($_POST['dateSecond']) && !empty($_POST['dateSecond']))
            {
                $dateFirst=htmlspecialchars($_POST['dateFirst']);
                $dateSecond=htmlspecialchars($_POST['dateSecond']);
                $prets= $this -> _borrow -> getListBorrowAdminWithDate($dateFirst,$dateSecond);
                //var_dump($prets);
            }
            //appel au layout
            require "views/layoutAdmin.phtml";
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }

    //NOMBRE DE PRETS EN RETARD
    public function numberBorrowAdmin()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            date_default_timezone_set('Europe/Paris');
            $dateFirst = date('Y-m-d');
            $datediff= $this -> _borrow -> getlateBorrowAdmin($dateFirst);
            //var_dump($datediff);
            $array=[];
            foreach($datediff as $value)
            {
                if($value[0]>=1)
                {
                    array_push($array,$value[0]);
                }
            }
            $numberLateBorrow = count($array);
            //var_dump($numberLateBorrow);
    
            if($numberLateBorrow > 1)
            {
                return $result = $numberLateBorrow.' retards.';
            }
            else if($numberLateBorrow == 1)
            {
                return $result = $numberLateBorrow.' retard.';
            }
            else
            {
                return $result = 'Aucun retard.';
            }
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }

     //AFFICHER LA LISTE DES RETARDS
     public function listDelayAdmin()
     {
        if($this -> _adminController ->adminIs_connect())
        {
            $titre="Liste des retards";
            $template = "borrow/listDelayAdmin";

            date_default_timezone_set('Europe/Paris');
            $date = date('Y-m-d');
            $delay= $this -> _borrow -> getDelayAdmin($date);
            //var_dump($delay);

            //appel au layout
            require "views/layoutAdmin.phtml";
        }
        else
        {
            header("location:index.php");
            exit();
        }
     }
}

?>