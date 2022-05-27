<?php

class Admin
{    
    //pptés de connexion
    private $_database;
    private $_connexion;
    
    
    public function __construct()
    {
        $this -> _database = new DataBase();
        $this -> _connexion = $this -> _database ->getConnexion();
    }
    
    public function getAdminByEmail($email)
    {
         //préparer la requete
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        pseudo,
                                                        CONCAT(UPPER(LEFT(pseudo,1)),LOWER(SUBSTRING(pseudo,2))) AS Pseudo,
                                                        email,
                                                        password
                                                    FROM
                                                        admin
                                                    WHERE
                                                        email = ?");
        //exécuter la requete
        $query->execute([$email]);
        //récupérer les infos de la requete
        $admin = $query ->fetch();
        //var_dump($admin);
        return $admin;
    }
}
?>