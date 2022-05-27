<?php

class User
{    
    //pptés de connexion
    private $_database;
    private $_connexion;
    
    
    public function __construct()
    {
        $this -> _database = new DataBase();
        $this -> _connexion = $this -> _database ->getConnexion();
    }
    
    //AJOUTER UN UTILISATEUR
    public function addUser(string $nom, string $prenom,string $civilite,int $number,string $photo,string $date_naissance,string $email,string $adresse,int $cp,string $ville,string $tel,string $password): ?bool
    {
        //préparer la requête pour insérer les infos du formulaire dans la BDD
        $query = $this -> _connexion -> prepare("INSERT INTO user(
                                                                    nom,
                                                                    prenom,
                                                                    civilite,
                                                                    numero_etu,
                                                                    photo,
                                                                    date_naissance,
                                                                    email,
                                                                    adresse,
                                                                    code_postal,
                                                                    ville,
                                                                    tel,
                                                                    password
                                                                )
                                                                VALUES(
                                                                    LOWER(?),
                                                                    LOWER(?),
                                                                    LOWER(?),
                                                                    ?,
                                                                    LOWER(?),
                                                                    ?,
                                                                    ?,
                                                                    LOWER(?),
                                                                    ?,
                                                                    LOWER(?),
                                                                    ?,
                                                                    ?)");
        //tester la requête
        $test = $query->execute([$nom,$prenom,$civilite,$number,$photo,$date_naissance,$email,$adresse,$cp,$ville,$tel,$password]);
        return $test;
    }
    
    //RECUPERER UN UTILISATEUR AVEC SON EMAIL
    public function getUserByEmail(string $email)
    {
        //préparer la requête
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        UPPER(nom) AS NOM,
                                                        CONCAT(UPPER(LEFT(prenom,1)),LOWER(SUBSTRING(prenom,2))) AS Prenom,
                                                        CONCAT(UPPER(LEFT(civilite,1)),LOWER(SUBSTRING(civilite,2))) AS Civilite,
                                                        numero_etu,
                                                        photo,
                                                        date_naissance,
                                                        email,
                                                        adresse,
                                                        code_postal,
                                                        CONCAT(UPPER(LEFT(ville,1)),LOWER(SUBSTRING(ville,2))) AS Ville,
                                                        tel,
                                                        password
                                                    FROM
                                                        user
                                                    WHERE
                                                        email = ?");
        //exécuter la requête
        $query->execute([$email]);
        //récupérer les infos de la requete
        $reader = $query ->fetch();
        return $reader;
    }

    /*****************************************************************************
                                METHODE ADMIN 
    *****************************************************************************/
    //RECUPERER ID USER POUR PRET
    public function getIdUser(string $name, int $number)
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        id
                                                    FROM
                                                        user
                                                    WHERE
                                                        LOWER(nom) = ?  AND numero_etu = ?");
        $query->execute([$name, $number]);
        $id_user = $query ->fetch();
        //var_dump($id_user);
        return $id_user;
    }

    //RECUPERER TOUS LES UTILISATEURS
    public function getAllUser()
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        CONCAT(UPPER(LEFT(nom,1)),LOWER(SUBSTRING(nom,2))) AS Nom,
                                                        CONCAT(UPPER(LEFT(prenom,1)),LOWER(SUBSTRING(prenom,2))) AS Prenom,
                                                        CONCAT(UPPER(LEFT(civilite,1)),LOWER(SUBSTRING(civilite,2))) AS civilite,
                                                        numero_etu,
                                                        DATE_FORMAT(date_naissance, '%d/%m/%Y') AS date_naissance,
                                                        email,
                                                        adresse,
                                                        code_postal,
                                                        CONCAT(UPPER(LEFT(ville,1)),LOWER(SUBSTRING(ville,2))) AS Ville,
                                                        tel
                                                    FROM
                                                        user");
        $query->execute();
        $users = $query ->fetchAll();
        //var_dump($users);
        return $users;
    }

    //RECUPERER USER avec nom
    public function getSomeUser(string $nom)
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        CONCAT(UPPER(LEFT(nom,1)),LOWER(SUBSTRING(nom,2))) AS Nom,
                                                        CONCAT(UPPER(LEFT(prenom,1)),LOWER(SUBSTRING(prenom,2))) AS Prenom,
                                                        CONCAT(UPPER(LEFT(civilite,1)),LOWER(SUBSTRING(civilite,2))) AS civilite,
                                                        numero_etu,
                                                        DATE_FORMAT(date_naissance, '%d/%m/%Y') AS date_naissance,
                                                        email,
                                                        adresse,
                                                        code_postal,
                                                        CONCAT(UPPER(LEFT(ville,1)),LOWER(SUBSTRING(ville,2))) AS Ville,
                                                        tel
                                                    FROM
                                                        user
                                                    WHERE
                                                        nom LIKE ?");
        $query->execute(['%'.$nom.'%']);
        $users = $query ->fetchAll();
        //var_dump($users);
        return $users;
    }

    //RECUPERER LES DONNEES USER AVEC ID
    public function getOneUserById(int $user_id)
    {
        $query = $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        CONCAT(UPPER(LEFT(nom,1)),LOWER(SUBSTRING(nom,2))) AS Nom,
                                                        CONCAT(UPPER(LEFT(prenom,1)),LOWER(SUBSTRING(prenom,2))) AS Prenom,
                                                        CONCAT(UPPER(LEFT(civilite,1)),LOWER(SUBSTRING(civilite,2))) AS civilite,
                                                        numero_etu,
                                                        DATE_FORMAT(date_naissance, '%d/%m/%Y') AS date_naissance,
                                                        email,
                                                        adresse,
                                                        code_postal,
                                                        CONCAT(UPPER(LEFT(ville,1)),LOWER(SUBSTRING(ville,2))) AS Ville,
                                                        tel
                                                    FROM
                                                        user
                                                    WHERE
                                                        id = ?");
        $query->execute([$user_id]);
        $user = $query ->fetch();
        //var_dump($user);
        return $user;
    }

    //MODIFIER LES INFOS DE CONTACT DE USER
    public function saveChangesUser(int $user_id, string $email, string $adresse, int $code_postal, string $ville, string $tel)
    {
        $query= $this -> _connexion -> prepare("
                                                UPDATE user 
                                                SET email= ?,adresse= ?,code_postal= ?,ville= ?, tel= ?
                                                WHERE id= ?");
                
        $test = $query -> execute([$email, $adresse, $code_postal, $ville, $tel, $user_id]);
        return $test;
    }

    //SUPPRIMER USER
    public function deleteUserById(int $user_id): ?bool
    {
        $query= $this -> _connexion -> prepare("DELETE FROM user WHERE id= ?");
        $test= $query -> execute([$user_id]);
        //var_dump($test);
        return $test;
    }
}

?>