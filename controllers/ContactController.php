<?php
//avant d'appeler le controlleur il faut récupérer la classe du model
require "models/Contact.php";


class ContactController
{
    private $_contact;
    private $_adminController;
    
    public function __construct()
    {
        $this -> _contact = new Contact();
        $this -> _adminController = new AdminController();
    }
    
    public function formContact()
    {
        $titre="Formulaire de contact";
        $template = "others/contact";
        
        //récupére valeurs des champs en les protégeant et définit $date
        if(isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['tel']) && isset($_POST['email']) && isset($_POST['sujet']) && isset($_POST['message']))
        {
            if(!empty($_POST['nom']) && !empty($_POST['prenom']) && !empty($_POST['tel']) && !empty($_POST['email']) && !empty($_POST['sujet']) && !empty($_POST['message']))
            {
                //1-on récupère la saisie de l'utilisateur en protégeant des caractères spéciaux   
                $nom = htmlspecialchars(trim($_POST['nom']));
                $prenom = htmlspecialchars(trim($_POST['prenom']));
                $telephone = htmlspecialchars(trim($_POST['tel']));
                $email = htmlspecialchars(trim($_POST['email']));
                $sujet = htmlspecialchars(trim($_POST['sujet']));
                $message = htmlspecialchars(trim($_POST['message']));
                $date = date("Y-m-d H:i:s");
                
                $errors=[];//tableau qui récupère messages erreurs
                
                // teste si l'email est correct
                if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)==false)
                {
                  $errors['email']= $email." n'est pas une adresse email valide."; 
                } 
                if(!ctype_alpha($nom))
                {
                    $errors['nom']= $nom." n'est pas valide."; 
                }
                if(!ctype_alpha($prenom))
                {
                    $errors['prenom']= $prenom." n'est pas valide."; 
                }
                
                if(empty($errors))
                {
                    $form = $this -> _contact -> formContactInsert($nom,$prenom,$telephone,$email,$date,$sujet,$message);
                    
                    if($form)
                    {
                        $messageErrorForm = "Votre message a bien été enregistré.";
                    }
                    else
                    {
                        $messageErrorForm = "Une erreur est survenue, veuillez renvoyer votre demande.";
                    }
                }
            }
            else
            {
                $messageErrorForm = "Veuillez remplir tous les champs.";
            }
        }
    //appel au layout
    require "views/layout.phtml";
    }
    
    
    /*****************************************************************************
                                METHODES ADMIN
    *******************************************************************************/
    
    /*AFFICHER LES MESSAGES DE CONTACT*/
    public function displayMessages()
    {
        if($this -> _adminController -> adminIs_connect())
        {
             $titre="Messages de contact";
             $template = "admin/listContactMessages";
            
            $messages= $this -> _contact -> getMessages();
            //var_dump($messages);
            
            //si champs remplis on récupère valeur et on affiche dans tableau
            if(isset($_POST['dateFirst']) && !empty($_POST['dateFirst']) && isset($_POST['dateSecond']) && !empty($_POST['dateSecond']))
            {
                $dateFirst=htmlspecialchars(trim($_POST['dateFirst']));
                $dateSecond=htmlspecialchars(trim($_POST['dateSecond']));
                $messages= $this -> _contact -> getMessagesWithDate($dateFirst,$dateSecond);
                //var_dump($messages);
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
    
    /* SUPPRESSION D'UN MESSAGE PAR L'ADMIN */
    public function deleteMessage()
    {
        if($this -> _adminController -> adminIs_connect())
        {

            if(array_key_exists('idMessage',$_GET))
            {
                $message_id = intval(htmlspecialchars($_GET["idMessage"]));
                //var_dump($message_id);
                $test= $this -> _contact -> deleteMessageById($message_id);
                //var_dump($test);

                //si la suppression a bien été faite
                if($test)
                {
                    echo $message = "Le message a été supprimé.";
                }
                else
                {
                    echo $message= 'Une erreur est survenue.';
                }
                
            }
            else
            {
                echo $message= "Une erreur est survenue, veuillez de nouveau essayer.";
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