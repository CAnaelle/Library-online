<?php

require "models/Admin.php";

class AdminController
{
    private $_admin;
    
    public function __construct()
    {
        $this -> _admin = new Admin();
    }
    
    public function admin()
    {
        $titre="Formulaire d'authentification Admin";
        $template= "views/admin/connectionAdmin";
            
        if(isset($_POST['pseudo']) && isset($_POST['email']) &&  isset($_POST['password']))
        {
            if(!empty($_POST['pseudo']) && !empty($_POST['email']) && !empty($_POST['password']))
            {
                $pseudo = htmlspecialchars(trim($_POST["pseudo"]));
                $email = htmlspecialchars(trim($_POST["email"]));
                $password = htmlspecialchars(trim($_POST["password"]));
                
                $admin= $this -> _admin -> getAdminByEmail($email);
               
                if($admin)
                {
                    if($pseudo === $admin["pseudo"])
                    {
                        if(password_verify($password, $admin["password"]))
                        {
                            // ouverture d'une sesssion 
                            $_SESSION['admin']= $admin["Pseudo"];
                            $_SESSION['id']= $admin["id"];
            
                            // redirection
                            $titre="Accueil Admin";
                            header("location:index.php?action=admin");
                        }
                        else
                        {
                            $messageAdmin= "Le mot de passe est invalide.";
                        }
                    } 
                    else 
                    {
                        $messageAdmin= "Le pseudo est invalide.";
                    }
                }
                else
                {
                    $messageAdmin= "L'email est invalide.";
                }
            }
            else
            {
                $messageAdmin= "Veuillez remplir les champs.";
            }   
        }
        //appel au layout
        require "views/layoutAdmin.phtml";
    }
    
    public function adminIs_connect()
    {
       if(isset($_SESSION['admin']))
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
       if(isset($_SESSION['admin']))
       {
            $_SESSION['admin']=[];
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
}
?>