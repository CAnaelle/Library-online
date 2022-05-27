<?php
//connexion BDD

class DataBase
{
    const SERVEUR =""; 
    const BDD = "";
    const USER = "";
    const MP = "";
    private $_connexion;
 
    public function __construct()
    {
        try{//pour gérer les erreurs et les afficher
            $this->_connexion = new PDO("mysql:host=".self::SERVEUR.";dbname=".self::BDD,self::USER,self::MP);
            $this->_connexion -> exec("SET CHARACTER SET utf8"); //définition encodage
        
        }catch(Exception $message){
            die("erreur de connexion ".$message->getMessage());
        }
      
    }
    
    public function getConnexion()
    {
        return $this->_connexion;
    }
}
//$connexion =new DataBase();
//var_dump($connexion->getConnexion());
?>