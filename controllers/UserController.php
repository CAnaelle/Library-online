<?php
//avant d'appeler le controlleur il faut récupérer la classe du model
require "models/User.php";

class UserController
{
    private $_user;
    private $_booking;
    private $_book;
    private $_borrow;
    private $_adminController;
    
    public function __construct()
    {
        $this -> _user = new User();
        $this -> _booking = new Booking();
        $this -> _book = new Book();
        $this -> _borrow = new Borrow();
        $this -> _adminController = new AdminController();
    }
    
    public function createAccount()
    {
        //appel au template pour afficher le formulaire
        $titre="Formulaire d'inscription";
        $template = "user/create_account";
        
        
        //tester si le form a bien été envoyé
        if(isset($_POST) && !empty($_POST))
        {

             //On vérifie la validité des données en terme de nomenclature
            $errors=[];//tableau qui récupère message erreurs

            if(isset($_POST['civilite']) && !empty($_POST['civilite']))
            {
                $civilite = htmlspecialchars(trim($_POST['civilite']));
            }
            else
            {
                $errors['civilite'] = "Veuillez renseigner votre civilité.";
            }

            if(isset($_POST['nom']) && !empty($_POST['nom']) && ctype_alpha($_POST['nom']))
            {
                $nom = htmlspecialchars(trim($_POST['nom']));
            }
            else
            {
                $errors['nom'] = "Veuillez renseigner votre nom.";
            }

            if(isset($_POST['prenom']) && !empty($_POST['prenom']) && ctype_alpha($_POST['prenom']))
            {
                $prenom = htmlspecialchars(trim($_POST['prenom']));
            }
            else
            {
                $errors['prenom'] = "Veuillez renseigner votre prénom.";
            }


            if(isset($_POST['date_naissance']) && !empty($_POST['date_naissance']))
            {
                $date_naissance=htmlspecialchars(trim($_POST['date_naissance']));
            }
            else
            {
                $errors['date_naissance'] = "Veuillez renseigner votre date de naissance.";
            }

            if(isset($_POST['adresse']) && !empty($_POST['adresse']))
            {
                $adresse=htmlspecialchars(trim($_POST['adresse']));
            }
            else
            {
                $errors['adresse'] = "Veuillez renseigner votre adresse.";
            }


            if(isset($_POST['ville']) && !empty($_POST['ville']))
            {
                $ville=htmlspecialchars(trim($_POST['ville']));
            }
            else
            {
                $errors['ville'] = "Veuillez renseigner votre ville.";
            }

            if(isset($_POST['cp']) && !empty($_POST['cp']) && is_numeric($_POST['cp']))
            {
                 $cp=intval(htmlspecialchars(trim($_POST['cp'])));
            }
            else
            {
                $errors['cp'] = "Veuillez renseigner votre code postal.";
            }

            if(isset($_POST['tel']) && !empty($_POST['tel']))
            {
                 $tel=htmlspecialchars(trim($_POST['tel']));
            }
            else
            {
                $errors['tel'] = "Veuillez renseigner votre téléphone.";
            }


            if(isset($_POST['number']) && !empty($_POST['number']) && is_numeric($_POST['number']))
            {
                $number = intval(htmlspecialchars(trim($_POST['number'])));
            }
            else
            {
                $errors['number'] = "Veuillez renseigner votre numéro d'étudiant ou de lecteur, il s'agit d'un numéro (ex:202201).";
            }
            
            if(isset($_POST['email']) && !empty($_POST['email']))
            {
               $email=htmlspecialchars(trim($_POST['email']));
               // teste si l'email est correct
                if (filter_var($email, FILTER_VALIDATE_EMAIL)==false) 
                {
                    $errors['email']= $email." n'est pas une adresse email valide."; 
                } 
            }
            else
            {
                $errors['email'] = "Veuillez renseigner votre email.";
            }
             
            if(isset($_POST['password']) && !empty($_POST['password']))
            {
                // teste si le mp est correct
                $pw =  htmlspecialchars(trim($_POST['password']));
                $pattern="/^(?=.*[a-z])(?=.*\d).{8,}$/i"; 
                    
                if(!preg_match($pattern,$pw)) 
                {
                    $errors['password']="Le mot de passe doit contenir au moins 8 caractères dont au moins 1 lettre et 1 chiffre.";
                }
                else
                {
                    $password=password_hash($pw,PASSWORD_DEFAULT);
                }
            }
            else
            {
                $errors['password'] = "Veuillez renseigner un mot de passe.";
            }
                
            //si le tableau erreur est vide on crée le compte: appel à la classe addUser
            if(empty($errors))
            {
                //vérifier si le mail existe déjà (à l'aide de la méthode getUserByEmail() du modèle).
                $reader= $this -> _user -> getUserByEmail($email);
                //var_dump($reader);
                if($reader !== false)
                {
                    //récuperer les infos du model
                    $messageUser="Vous êtes déjà enregistré!";
                    $titre="Formulaire d'authentification";
                    $template = "user/connection";
                }
                else    //on insère user dans BDD 
                {
                    $photo = 'avatar_neutral.svg';
                    $user= $this -> _user -> addUser($nom,$prenom,$civilite,$number,$photo,$date_naissance,$email,$adresse,$cp,$ville,$tel,$password);
                    //var_dump($user);
                    //vérifier que le test est true (insertion success) et redirection sinon message erreur
                    if($user)
                    {
                        $messageUser = "Le compte a bien été crée, vous pouvez dès à présent vous connecter.";

                        //template
                        $titre="Formulaire d'authentification";
                        $template = "user/connection";
                    }
                    else
                    {
                         $messageUser = "Une erreur est survenue, veuillez ré-effectuer la création de compte.";
                    }
                }
            }
        }
    //appel au layout
    require "views/layout.phtml";
    }
    
    
    public function connexion()
    {
        $titre="Formulaire d'authentification";
        $template = "user/connection";
        
        if(isset($_POST['email']) && isset($_POST['password']) && isset($_POST['number']))
        {
            if(!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['number']))
            {
                $email = htmlspecialchars(trim($_POST["email"]));
                $password = htmlspecialchars(trim($_POST["password"]));
                $number = htmlspecialchars(trim($_POST["number"]));
                
                $reader = $this -> _user -> getUserByEmail($email);
               
                if($reader)
                {
                    if($number == $reader["numero_etu"])
                    {
                        if(password_verify($password, $reader["password"]))
                        {
                            // ouverture d'une sesssion 
                            $_SESSION['reader']= $reader["Prenom"];
                            $_SESSION['id']= $reader["id"];
                            $_SESSION['photo']= $reader["photo"];
            
                            // redirection
                            $titre="Accueil";
                            header("location:index.php");
                            exit();
                        } 
                        else 
                        {
                             $messageUser= "Le mot de passe est invalide.";
                        }
                    }
                    else
                    {
                         $messageUser = "Votre numéro de lecteur ou étudiant est invalide.";
                    }
                    
                }
                else
                {
                     $messageUser= "L'email est invalide.";
                }
            }
            else
            {
                 $messageUser= "Veuillez remplir les champs.";
            }   
        }
    //appel au layout
    require "views/layout.phtml";
    }
    
    public function is_connect()
    {
        if(isset($_SESSION['reader']))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    public function deconnexion()
    {
       if(isset($_SESSION['reader']))
       {
            $_SESSION['reader']=[];
            session_unset();
            session_destroy();
            header("location:index.php");
            exit();
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
    //Obtenir tous les utilisateurs ou certains si nom indiqué
    public function getUsers()
    {
         if($this -> _adminController ->adminIs_connect())
        {
            $titre="Liste des lecteurs";
            $template = "admin/listReaderAdmin";

            $users= $this -> _user -> getAllUser();
            //var_dump($users);
            
            //si champs remplis on récupère valeur et on affiche dans tableau
            if(isset($_POST['name']) && !empty($_POST['name']))
            {
                $nom=htmlspecialchars(trim($_POST['name']));
                
                $users= $this -> _user -> getSomeUser($nom);
                //var_dump($users);
            }
            //appel au layout
            require "views/layoutAdmin.phtml";
        }
        else
        {
            header("location:index.php");
        }
    }

    //Obtenir 1 utilisateur avec son id
    public function getOneUser()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            if(array_key_exists("idUser",$_GET))
            {
                $user_id = intval($_GET['idUser']);
                //var_dump($user_id);
                $user = $this -> _user -> getOneUserById($user_id);
                //var_dump($user);  
                
                require "views/admin/targetEditUser.phtml";
                    
            }
            else
            {
                header("location:index.php?action=admin");
            }
        }
        else
        {
            header("location:index.php");
        }
    }
    
    //Obtenir tout le détail d'un utilisateur avec son id
    public function getDetailsUser()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            if(array_key_exists("idUser",$_GET))
            {
                if(is_numeric($_GET['idUser']))
                {
                    $titre="Détails d'un lecteur";
                    $template = "admin/detailsUser";
                
                    $user_id = intval($_GET['idUser']);
                    $user = $this -> _user -> getOneUserById($user_id);
                    $bookingUser = $this -> _booking -> getBookingByIdUser($user_id);
                    $borrowUser = $this -> _borrow -> getBorrowByIdUser($user_id);
                    //var_dump($user, $bookingUser, $borrowUser);  
                    
                    //appel au layout
                    require "views/layoutAdmin.phtml";
                }
                else
                {
                    header("location:index.php");
                }
            }
            else
            {
                header("location:index.php?action=admin");
            }
        }
        else
        {
            header("location:index.php");
        }
    }
    

    //Enregistrer les modifications de coordonnées de l'utilisateur
    public function saveUser()
    {
        if($this -> _adminController ->adminIs_connect())
        {
           if(isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['tel']) && !empty($_POST['tel']) && isset($_POST['adresse']) && !empty($_POST['adresse']) && isset($_POST['cp']) && !empty($_POST['cp']) && isset($_POST['ville']) && !empty($_POST['ville']) && isset($_POST['email']) && !empty($_POST['email']))
            {
                $user_id=intval(htmlspecialchars($_POST['id']));
                $tel=htmlspecialchars(trim($_POST['tel']));
                $adresse=htmlspecialchars(trim($_POST['adresse']));
                $code_postal=htmlspecialchars(trim($_POST['cp']));
                $ville=htmlspecialchars(trim($_POST['ville']));
                $email=htmlspecialchars(trim($_POST['email']));
                //var_dump($user_id);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) 
                {
                    if(is_numeric($code_postal))
                    {
                        $code_postal = intval($code_postal);
                        $saveUser = $this -> _user -> saveChangesUser($user_id, $email, $adresse, $code_postal, $ville, $tel);
                        if($saveUser)
                        {
                             $messageAdmin="Les informations du lecteur ont bien été modifiées.";
                        }
                        else
                        {
                             $messageAdmin="Une erreur est survenue";
                        }
                    }
                    else
                    {
                        $messageAdmin = "Le code postal ne correspond pas.";
                    }
                }
                else
                {
                    $messageAdmin = $email." n'est pas une adresse email valide"; 
                }
            }
            $titre="Liste des lecteurs";
            $template = "admin/listReaderAdmin";

            //Remettre affichage à jour
            $users= $this -> _user -> getAllUser();
            //appel au layout
            require "views/layoutAdmin.phtml";
        }
        else
        {
            header("location:index.php");
        }
    }

    //SUPPRIMER USER ET RESERVATIONS LIEES
     public function deleteUser()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            if(array_key_exists("idUser",$_GET))
            {
                $user_id = intval($_GET['idUser']);
                //var_dump($user_id);
            
                //vérifier que user n'a aucun prêts avant de supprimer
                $controlBorrowByUser = $this -> _borrow -> getBorrowByIdUser($user_id);

                if($controlBorrowByUser)
                {
                     echo $messageAdmin = 'L\'utilisateur ne peut être enlevé de la BDD car il a toujours des prêts en cours.';
                }
                else
                {
                    //récupérer toutes les réservations de l'user
                    $bookingUser = $this -> _booking -> getBookingByIdUser($user_id);
                    //var_dump($bookingUser);
                    if($bookingUser)
                    {
                        //récupérer id_book des reservations pour remettre à jour statut
                        foreach($bookingUser as $booking)
                        {
                            $book_id = $booking['id_book'];
                            //var_dump($book_id);

                            //vérifier si le livre est actuellement en pret ou pas 
                            $controlBorrow = $this -> _borrow -> getBorrowReturnDate($book_id);

                            //s'il n'est pas dans la liste des prêts alors on CHANGE LE STATUT EN DISPONIBLE (sinon on ne touche pas au statut)
                            if($controlBorrow == false)
                            {
                                $statusToUpdate = 'disponible';
                                $newStatusBook= $this -> _book -> updateStatusBook($statusToUpdate,$book_id);

                            }

                            $reservation_id = $booking['id'];
                            //var_dump($reservation_id);
                            
                            //supprimer ces réservations
                            $deleteBooking = $this -> _booking -> deleteReservation($reservation_id);

                        } 
                        //suppression de user
                        $deleteUser = $this -> _user -> deleteUserById($user_id);
                        echo $messageAdmin = 'L\'utilisateur et toutes ses réservations ont bien été supprimés de la BDD.';
                    }
                    else
                    {
                        $deleteUser = $this -> _user -> deleteUserById($user_id);
                        echo $messageAdmin = 'L\'utilisateur a bien été supprimé de la BDD.';
                    } 
                }
            }
            else
            {
                header("location:index.php?action=admin");
            }
        }
        else
        {
            header("location:index.php");
        }
    }
}
?>