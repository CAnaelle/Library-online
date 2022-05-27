<?php
declare(strict_types=1); 
session_start();

require "config/DataBase.php";

require "controllers/BookController.php";
require "controllers/ContactController.php";
require "controllers/InfosController.php";
require "controllers/EventController.php";
require "controllers/LegalController.php";
require "controllers/UserController.php";
require "controllers/BookingController.php";
require "controllers/BorrowController.php";
/* require "controllers/CreateAdminController.php"; */
require "controllers/AdminController.php";


$bookController = new BookController();
$contactController = new ContactController();
$infosController = new InfosController();
$eventController = new EventController();
$legalController = new LegalController();
$userController = new UserController();
$bookingController = new BookingController();
$borrowController = new BorrowController();
/* $createAdminController = new CreateAdminController(); */
$adminController = new AdminController();


if(array_key_exists('action',$_GET)) //si on a une url avec mot-clé action
{
    switch($_GET["action"])
    {
        /* Barre recherche*/
        case "ajaxResearchBook":
            $bookController -> researchBook();
            break;
        /*Retourne la fiche d'un livre à l'aide de son id*/
        case "searchBook":
            $bookController -> displayBook();
            break;
        /*Retourne des livres selon la catégorie*/
        case "suggestions":
            $bookController -> getRandBooksByCategory();
            break;

        /*OTHERS pages contact/infos pratiques/mentions légales/politiques de confidentialité*/
        case "message":
            $contactController -> formContact();
            break;
        case "infos":
            $infosController -> displayInfos();
            break;
        case "events":
            $eventController -> displayEvents();
            break;
        case "mentions":
            $legalController -> displayMentions();
            break;
        case "privacy":
            $legalController -> displayPrivacyPolicies();
            break;
        
         /*ACTION USER*/
        case "create_account":
            $userController -> createAccount();   
            break;
        case "connect":
            $userController -> connexion();
            break;
        case "deconnect":
            $userController -> deconnexion();
            break;
        /*Réserver un livre*/
        case "reserve":
            $bookingController -> booking();
            break;
        /* Afficher le liste des réservations */
        case "listBookingUser":
            $bookingController -> listBookingUser();
            break;
        /* Afficher la liste des prêts */
         case "listBorrowUser":
            $borrowController -> listBorrowUser();
            break;
        /* Supprimer une réservation */
        case "deleteBooking":
            $bookingController -> deleteBooking();
            break;
        /* Retourner les données d'un livre pour ajouter aux favoris */
        case "getFavorite":
            $bookController -> getFavoriteBook();
            break;
        /* Afficher la liste des favoris */
        case "wishlist":
            $bookController -> getWishList();
            break;

        /*ACTION ADMIN*/
        /* case "createAdmin":
            $createAdminController -> admin();
            break; */
        case "admin":
            $adminController -> admin();
            break;
        case "deconnectAdmin":
            $adminController -> deconnexion();
            break;
        /* Rechercher un livre lors de ajout/retour prêt*/
        case "ajaxResearchBookAdmin":
            $borrowController -> researchBookAdmin();
            break;
        /* Ajouter un prêt */
        case "borrow":
            $borrowController -> borrowBook();
            break;
        /* Retour prêt */
        case "returnBook":
            $borrowController -> returnBook();
            break;
        /* Ajouter un livre bdd */
        case "addBook":
            $bookController -> addBook();
            break;
        /* Afficher la liste des réservations */
        case "listBookingAdmin":
            $bookingController -> listBookingAdmin();
            break;
        /* Afficher la liste des prêts */
        case "listBorrowAdmin":
            $borrowController -> listBorrowAdmin();
            break;
        /* Afficher la liste des retards */
        case "listDelayAdmin":
            $borrowController -> listDelayAdmin();
            break;
         /* Afficher la liste des lecteurs */
        case "listReaderAdmin":
            $userController -> getUsers();
            break;
        /* Afficher le détail d'un lecteur */
        case "userDetails":
            $userController -> getDetailsUser();
            break;
        /* Affichage pour modifier infos lecteur */
         case "getOneUser":
            $userController -> getOneUser();
            break;
        /* Enregistrer modification infos lecteur */
         case "editUser":
            $userController -> saveUser();
            break;
        /* Supprimer lecteur */
         case "deleteUser":
            $userController -> deleteUser();
            break;
        /* Ajax afficher la fiche livre pour modifier le livre */
         case "ajaxEditBook":
            $bookController -> loadBookAdmin();
            break;
        /* Modifier un livre */
         case "editBook":
            $bookController -> editBookAdmin();
            break;
        /* Supprimer un livre */
        case "deleteBook":
            $bookController -> deleteBook();
            break;
        /*Afficher la liste des messages de contact*/
        case "contactMessagesAdmin":
            $contactController -> displayMessages();
            break;
        /* Supprimer un message */
        case "deleteMessage":
            $contactController -> deleteMessage();
            break;
        /*Afficher les évènements*/
        case "eventsAdmin":
            $eventController -> displayEventsAdmin();
            break;
        /*Ajouter un évènement*/
        case "addEventAdmin":
            $eventController -> addEvent();
            break;
        /* Supprimer un évènement */
        case "deleteEvent":
            $eventController -> deleteEvent();
            break;
        default:
            header('location:index.php');
            exit();
    }
}
else if (array_key_exists('bookList',$_GET)) //si on a une url avec mot-clé bookList (barre de recherche)
{
    $bookController -> displayListBook();
}
else //sinon sans url: affichage de la page home
{
    $bookController -> home();
    
}
?>