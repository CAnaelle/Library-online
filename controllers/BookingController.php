<?php

require "models/Booking.php";

class BookingController
{
    private $_booking;
    private $_book;
    private $_borrow;
    private $_userController;
    private $_adminController;
    
    public function __construct()
    {
        $this -> _booking = new Booking();
        $this -> _book = new Book();
        $this -> _borrow = new Borrow();
        $this -> _userController = new UserController();
        $this -> _adminController = new AdminController();
    }
    
    /*****************************************************************************
                                METHODES USER
    *******************************************************************************/
    /* Methode de réservation by user */
    public function booking()
    {
        if($this -> _userController ->is_connect())
        {
            if(isset($_SESSION['id']) && array_key_exists('book',$_GET))
            {
                date_default_timezone_set('Europe/Paris');
                $dateDebReservation = date('Y-m-d H:i:s');
                $dateFinReservation = date('Y-m-d H:i:s', strtotime($dateDebReservation. ' + 15 days'));
                $dateAjout = date('Y-m-d H:i:s');
                //var_dump($dateAjout);
                
                //pour affichage sur template
                $dateDebR = date('d-m-Y');
                $dateFinR = date('d-m-Y', strtotime($dateFinReservation));
                $user_id =$_SESSION['id'];
                $book_id = htmlspecialchars(trim($_GET["book"]));
                //var_dump($user_id,$book_id,$dateDebReservation,$dateFinReservation);
                if(is_numeric($book_id))
                {
                    $book_id = intval($book_id);
                    $statusBook = $this -> _book -> getStatusBook($book_id);
                    //var_dump($statusBook);
                }
                else
                {
                    header("location:index.php");
                    exit();
                }
                
                if($statusBook)
                {
                    switch($statusBook['statut'])
                    {
                        //si dispo -> réservation possible
                        case 'disponible':
                            $reservation= $this -> _booking -> addBooking($user_id,$book_id,$dateDebReservation,$dateFinReservation,$dateAjout);
                            
                            if($reservation)
                            {
                                //CHANGER LE STATUT EN RESERVE
                                $statusToUpdate = 'réservé';
                                $newStatusBook= $this -> _book -> updateStatusBook($statusToUpdate,$book_id);
                                //var_dump($newStatusBook);
                                $messageUser= 'Votre réservation a bien été prise en compte';
                            }
                            else
                            {
                                $messageUser= 'Une erreur est survenue, veuillez de nouveau réserver';
                            }
                        break;
                        //si en prêt -> réservation possible à partir de la date de fin de prêt
                        case 'en prêt':
                             //pas de changement de statut -> informer l'utilisateur de la date de fin de prêt et mettre sa réservation à la fin du prêt
                            //1-vérifier que le livre n'est pas dans la table des réservations déjà
                            $bookBooking = $this -> _booking -> getUserForOneBooking($book_id);
    
                            if($bookBooking)
                            {
                                //1.1 si le livre s'y trouve alors on ne peut pas le réserver car déjà réservé après le prêt
                                $messageUser = 'Le livre est actuellement en prêt et est déjà réservé par la suite.';
                            }
                            else
                            {
                                //1.2 sinon on le met en réservation (sans changement de statut) après la date de fin de prêt
                                
                                $date_return_borrow= $this -> _borrow -> getBorrowReturnDate($book_id);
                                $dateDebReservation =date('Y-m-d H:i:s',strtotime($date_return_borrow['date_fin_pret']. ' + 1 days'));
                                $dateFinReservation = date('Y-m-d H:i:s', strtotime($dateDebReservation. ' + 15 days'));
                                //var_dump($dateDebReservation);
    
                                //pour affichage dans template
                                $dateDebR = date('d-m-Y', strtotime($dateDebReservation));
                                $dateFinR = date('d-m-Y', strtotime($dateFinReservation));
                               
                                $reservation= $this -> _booking -> addBooking($user_id,$book_id,$dateDebReservation,$dateFinReservation,$dateAjout);
                                if($reservation)
                                {
                                    $messageUser= 'Le livre est actuellement en prêt mais votre réservation a bien été prise en compte et commence à partir du '. $dateDebR;
                                }
                                else
                                {
                                    $messageUser= 'Une erreur est survenue, veuillez de nouveau réserver';
                                }
                            }
                        break;
                        //si consultation sur place -> pas de réservation possible
                        case 'consultation sur place':
                            $messageUser= 'Le livre est uniquement consultable sur place.';
                        break;
                        //si réservé -> pas de réservation possible actuellement
                        case 'réservé':
                            $messageUser= 'Le livre est actuellement réservé, veuillez réessayer dans les jours à venir.';
                        break;
                    }
    
                    $someBookingByIdUser= $this -> _booking -> getSomeBookingByIdUser($user_id);
                }
            }
            else
            {
                header("location:index.php");
                exit();
            }
           
            //appel au template et layout
            $titre="Information sur votre réservation";
            $template = "booking/booking";
            require "views/layout.phtml";
        }
        else
        {
            header("location:index.php?action=connect");
            exit();
        }
    }

    /* Methode d'affichage de la liste des réservations de user */
    public function listBookingUser()
    {
        if($this -> _userController ->is_connect())
        {
            //appel au template pour afficher liste des réservations
            $titre="Liste de vos réservations";
            $template = "booking/listBookingUser";
            
            if(isset($_SESSION['id']))
            {
                $user_id =$_SESSION['id'];
                $bookingByIdUser= $this -> _booking -> getBookingByIdUser($user_id);
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
    
    /* Methode suppression d'une réservation par user ou admin et changement statut livre ->dispo ou non */
    public function deleteBooking()
    {
        if($this -> _userController ->is_connect() || $this -> _adminController ->adminIs_connect())
        {

            if(array_key_exists('idBooking',$_GET) && array_key_exists('idBook',$_GET))
            {
                $reservation_id = intval(htmlspecialchars($_GET["idBooking"]));
                $book_id = intval(htmlspecialchars($_GET["idBook"]));
                //var_dump($reservation_id,$book_id);
                $test= $this -> _booking -> deleteReservation($reservation_id);
                //var_dump($test);

                //si la suppression a bien été faite
                if($test)
                {
                    //vérifier si le livre est actuellement en pret ou pas 
                     $controlBorrow = $this -> _borrow -> getBorrowReturnDate($book_id);

                     //s'il n'est pas dans la liste des prêts alors on CHANGE LE STATUT EN DISPONIBLE (sinon on ne touche pas au statut)
                     if($controlBorrow === false)
                     {
                        $statusToUpdate = 'disponible';
                        $newStatusBook= $this -> _book -> updateStatusBook($statusToUpdate,$book_id);
                     }
                }
                else
                {
                    $messageUser= 'Une erreur est survenue.';
                }
                
            }
            else
            {
                $messageUser= "Une erreur est survenue, veuillez de nouveau essayer";
            }
            
            if($this -> _userController ->is_connect())
            {
                //appel au template pour afficher liste des réservations
                $titre="Liste de vos réservations";
                $template = "booking/listBookingUser";
                //appel au layout
                require "views/layout.phtml"; 
            }
            else if($this -> _adminController ->adminIs_connect())
                //appel au template pour afficher liste des réservations
                $titre="Liste des réservations";
                $template = "booking/listBookingAdmin";
                //appel au layout
                require "views/layoutAdmin.phtml"; 
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

    //Afficher liste des réservations
    public function listBookingAdmin()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            $titre="Liste des réservations";
            $template = "booking/listBookingAdmin";
            
            $reservations= $this -> _booking -> getListBookingAdmin();
            //var_dump($reservations);
            
            //si champs remplis on récupère valeur et on affiche dans tableau
            if(isset($_POST['dateFirst']) && !empty($_POST['dateFirst']) && isset($_POST['dateSecond']) && !empty($_POST['dateSecond']))
            {
                $dateFirst=htmlspecialchars(trim($_POST['dateFirst']));
                $dateSecond=htmlspecialchars(trim($_POST['dateSecond']));
                $reservations= $this -> _booking -> getListBookingAdminWithDate($dateFirst,$dateSecond);
                //var_dump($reservations);
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

    public function numberBookingAdmin()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            date_default_timezone_set('Europe/Paris');
            $dateSecond = date('Y-m-d H:i:s');
            $dateFirst=date('Y-m-d H:i:s', strtotime($dateSecond. ' - 1 day'));
            //$dateFirst = strftime("%Y-%m-%d %H:%i:%s", mktime(0, 0, 0, date('m'), date('d')-1, date('Y')));
            //var_dump($dateFirst);
            //var_dump($dateSecond);
            
            $reservations= $this -> _booking -> getListBookingAdminWithDate($dateFirst,$dateSecond);
            //var_dump($reservations);
            
            $numberBooking = count($reservations);
            if($numberBooking > 1)
            {
                return $result = $numberBooking.' réservations ont été faite la veille.';
            }
            else if($numberBooking == 1)
            {
                return $result = $numberBooking.' réservation a été faite la veille.';
            }
            else
            {
                return $result = 'Aucune réservation faite.';
            }
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }

}
?>