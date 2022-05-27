<?php

class Contact
{    
    //pptés de connexion
    private $_database;
    private $_connexion;
    
    
    public function __construct()
    {
        $this -> _database = new DataBase();
        $this -> _connexion = $this -> _database ->getConnexion();
    }
    
    //ENVOI MESSAGE CONTACT
    public function formContactInsert(string $nom, string $prenom,string $telephone,string $email,string $date,string $sujet,string $message): ?bool
    {
        //préparer la requete pour inserer les infos du formulaire dans la BDD
        $query = $this -> _connexion -> prepare("INSERT INTO message_contact(
                                                                                nom,
                                                                                prenom,
                                                                                tel,
                                                                                email,
                                                                                date,
                                                                                sujet_message,
                                                                                message
                                                                            )
                                                                            VALUES(
                                                                                LOWER(?),
                                                                                LOWER(?),
                                                                                ?,
                                                                                ?,
                                                                                ?,
                                                                                LOWER(?),
                                                                                LOWER(?)
                                                                            )");
        $test = $query->execute([$nom,$prenom,$telephone,$email,$date,$sujet,$message]);
        return $test;
    }
    
    
    /*****************************************************************************
                                METHODES ADMIN
    *******************************************************************************/
    // RECUPERER TOUS LES MESSAGES
    public function getMessages()
    {
        $query= $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        CONCAT(UPPER(LEFT(nom,1)),LOWER(SUBSTRING(nom,2))) AS Nom,
                                                        CONCAT(UPPER(LEFT(prenom,1)),LOWER(SUBSTRING(prenom,2))) AS Prenom,
                                                        tel,
                                                        email,
                                                        DATE_FORMAT(date, '%d/%m/%Y') AS date,
                                                        sujet_message,
                                                        message
                                                    FROM
                                                        message_contact
                                                    ORDER BY date DESC");
        $query -> execute();
        $messages = $query -> fetchAll();
        //var_dump($messages);
        return $messages;
    }
    
     /* RECUPERER LES MESSAGES DE CONTACT EN FONCTION DE DATE*/
    public function getMessagesWithDate(string $dateFirst,  string $dateSecond)
    {
        $query= $this -> _connexion -> prepare("SELECT
                                                        id,
                                                        CONCAT(UPPER(LEFT(nom,1)),LOWER(SUBSTRING(nom,2))) AS Nom,
                                                        CONCAT(UPPER(LEFT(prenom,1)),LOWER(SUBSTRING(prenom,2))) AS Prenom,
                                                        tel,
                                                        email,
                                                        DATE_FORMAT(date, '%d/%m/%Y') AS date,
                                                        sujet_message,
                                                        message
                                                    FROM
                                                        message_contact
                                                    WHERE date BETWEEN ? AND ? 
                                                    ORDER BY date DESC");
        $query->execute([$dateFirst,$dateSecond]);
        $messagesWithDate = $query ->fetchAll();
        //var_dump($messagesWithDate);
        return $messagesWithDate;
    }
    
    /* SUPPRIMER MESSAGE PAR ADMIN */
    public function deleteMessageById(int $message_id)
    {
        $query= $this -> _connexion -> prepare("DELETE FROM message_contact WHERE id= ?");
        $test= $query -> execute([$message_id]);
        //var_dump($test);
        return $test;
    }
}
?>