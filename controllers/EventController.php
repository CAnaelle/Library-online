<?php
require "models/Event.php";

class EventController
{
    private $_event;
    private $_adminController;
    
    public function __construct()
    {
        $this -> _event = new Event();
        $this -> _adminController = new AdminController();
    }


    public function displayEvents()
    {
       $events= $this -> _event -> getEvents();
        
        $titre="Évènements à venir";
        $template = "others/events";
        require "views/layout.phtml";
    }
    
    /*****************************************************************************
                                METHODES ADMIN
    *******************************************************************************/
    //AJOUTER UN EVENEMENT DANS LA BDD
    public function addEvent()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            if(isset($_POST) && !empty($_POST))
            {
                $errors=[];

                if(isset($_POST['titre']) && !empty($_POST['titre']))
                {
                    $titre = htmlspecialchars(trim($_POST['titre']),ENT_NOQUOTES);//Pour titre avec apostrophe
                }
                else
                {
                    $errors['titre']= 'Veuillez remplir le titre de l\'évènement.';
                }

                if(isset($_POST['jour']) && !empty($_POST['jour']))
                {
                    $jour = htmlspecialchars(trim($_POST['jour']));
                }
                else
                {
                    $errors['jour']= 'Veuillez remplir le(s) jour(s) de l\'évènement.';
                }

                if(isset($_POST['date_deb']) && !empty($_POST['date_deb']))
                {
                    $date_deb = htmlspecialchars(trim($_POST['date_deb']));
                }
                else
                {
                    $errors['date_deb']= 'Veuillez remplir la date de début de l\'évènement.';
                }

                if(isset($_POST['date_fin']) && !empty($_POST['date_fin']))
                {
                    $date_fin = htmlspecialchars(trim($_POST['date_fin']));
                }
                else
                {
                    $errors['date_fin']= 'Veuillez remplir la date de fin de l\'évènement.';
                }

                if(isset($_POST['resume']) && !empty($_POST['resume']))
                {
                   $resume = htmlspecialchars(trim($_POST['resume']));
                }
                else
                {
                    $errors['resume']= 'Veuillez remplir le résumé de l\'évènement.';
                }
                
                if(isset($_POST['nombre']) && !empty($_POST['nombre']) && is_numeric($_POST['nombre']))
                {
                    $nombre = htmlspecialchars(trim($_POST['nombre']));
                    $nombre = intval($nombre);
                }
                else
                {
                    $errors['nombre']= 'Veuillez remplir le nombre de places disponible pour l\'évènement.';
                }
                

                //Vérification IMAGE
                if(isset($_FILES['image']) && !empty($_FILES['image']))
                {
                    //var_dump($_FILES['image']);
                    if(!empty($_FILES['image']['name']) && $_FILES['image']['size'] != 0)
                    {
                        $max_size = 900000;
                        //var_dump($_FILES['image']);
                        if($_FILES['image']['size']>$max_size)
                        {
                            $errors['image']= "La taille de l'image est trop grande.";
                        }
                        else
                        {
                            $extensions = ['jpg','jpeg','png'];
                            $extFile = explode('.',$_FILES['image']['name']);
                            $extFile = end($extFile);
                            //var_dump($extFile);

                            if(in_array($extFile,$extensions))
                            {
                                require "lib/mime.php";
                                if (is_uploaded_file($_FILES['image']['tmp_name'])) 
                                {
                                    $mime_type = mime_content_type($_FILES['image']['tmp_name']);
                                
                                    if (in_array($mime_type, MIME_TYPES)) 
                                    {
                                        $imageName = $_FILES['image']['name'];
                                        $image=$_FILES['image']; 
                                        //var_dump($image);  
                                    }
                                    else
                                    {  
                                        $errors['image'] = 'Le fichier n\'a pas été enregistré correctement car il ne correspond pas au MIME indiqué';
                                        
                                    }
                                }
                            }
                            else
                            {
                                $errors['image'] = 'L\'extension du fichier n\'est pas valide.';
                            }  
                        } 
                    }
                    else
                    {
                        //var_dump($_FILES['image']);
                        $imageName = '';
                    }
                }
                else
                {
                    $imageName = '';
                }

                 
                //Si aucune erreur
                if(empty($errors))
                { 
                    $addEvent = $this -> _event -> addEventInBdd($titre, $jour, $date_deb, $date_fin, $resume, $imageName, $nombre);
                    //var_dump($addEvent);
                    if($addEvent)//si l'évènement a bien été ajouté dans la BDD
                    {
                        if(!empty($_FILES['image']))
                        {
                            $uploads_dir = 'public/assets/images/evenements';
                            $tmp_name = $_FILES['image']['tmp_name'];
                            //var_dump($tmp_name);
                            move_uploaded_file($tmp_name, "$uploads_dir/$imageName");
                        }

                        $messageAdmin= 'L\'évènement a bien été ajouté.';
                    }
                    else
                    {
                        $messageAdmin= 'Une erreur est survenue, l\'évènement n\'a pas été ajouté.';
                    }
                            
                }
                else
                {
                    $messageAdmin= 'Veuillez remplir tous les champs.';
                }
            }
           
            $titre="Ajouter un évènement";
            $template = "admin/addEvent";
            //appel au layout
            require "views/layoutAdmin.phtml";
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }
    
    /*AFFICHER LES EVENEMENTS ADMIN*/
    public function displayEventsAdmin()
    {
        if($this -> _adminController -> adminIs_connect())
        {
            $titre="Évènements";
            $template = "admin/listEventsAdmin";
            
            $evenements= $this -> _event -> getAllEventsAdmin();
            //var_dump($evenements);
            
            //si champs remplis on récupère valeur et on affiche dans tableau
            if(isset($_POST['title']) && !empty($_POST['title']))
            {
                $titre=htmlspecialchars(trim($_POST['title']));
                $evenements= $this -> _event -> getEventsWithTitle($titre);
                //var_dump($evenements);
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
    
    /* SUPPRESSION D'UN EVENEMENT PAR L'ADMIN */
    public function deleteEvent()
    {
        if($this -> _adminController -> adminIs_connect())
        {
            if(array_key_exists('idEvent',$_GET))
            {
                $event_id = intval(htmlspecialchars($_GET["idEvent"]));
                //var_dump($event_id);
                
                //supprimer la photo du dossier
                $imageNow= $this -> _event ->  getCurrentEventImg($event_id);
                //var_dump($imageNow);
                $uploads_dir = 'public/assets/images/evenements';
            
                //suppression de l'image du dossier images si le nom de l'image n'est pas vide et si le fichier existe
                if(!empty($imageNow['image']) && file_exists($uploads_dir."/".$imageNow['image']))
                { 
                    unlink($uploads_dir."/".$imageNow['image']);
                    echo $messageAdmin= "L'image ".$imageNow['image'].", associé à l'évènement retiré, a bien été supprimée. ";
                }
                else
                {
                    echo $messageAdmin= "Il n'y a pas de fichier image associé à l'évènement retiré. ";
                }
                
                
                $test= $this -> _event -> deleteEventById($event_id);
                //var_dump($test);

                //si la suppression a bien été faite
                if($test)
                {
                    echo $message = "L'évènement a été supprimé.";
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