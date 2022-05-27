<?php
//avant d'appeler le controlleur il faut récupérer la classe du model
require "models/Book.php";

class BookController
{
    private $_book;
    private $_borrow;
    private $_userController;
    private $_adminController;
    
    public function __construct()
    {
        $this -> _book = new Book();
        $this -> _borrow = new Borrow();
        $this -> _userController = new UserController();
        $this -> _adminController = new AdminController();
    }
    
    //AFFICHE LA PAGE HOME (NOUVELLES ACQUISITIONS ET SUGGESTIONS PAR CATEGORIE)
    public function home()
    {
        //récuperer les infos du model
        $books= $this -> _book -> getBooksCurrent();
        $categories= $this -> _book -> categorieBook();
        
        //transmettre les infos vers le template
        $titre='Accueil';
        $template = 'home';
        //appel au layout
        require "views/layout.phtml";
    }

    //AFFICHER DES SUGGESTIONS DE LIVRES SELON LA CATEGORIE CLIQUE
    public function getRandBooksByCategory()
    {
        if(array_key_exists('categorie',$_GET))
        {
            $categorie = htmlspecialchars($_GET['categorie']);

            $books= $this -> _book -> getBooksRandByCategory($categorie);
            //var_dump($books);
            
            if($books)
            {
                $titre='Suggestions par catégorie';
                $template = "views/book/bookByCategorie";
            }
            else
            {
                header("location:index.php");
                exit();
            }
            
        }
        else
        {
            header("location:index.php");
            exit();
        }
        //appel au layout
        require "views/layout.phtml";
    }

    //METHODE BARRE DE RECHERCHE AVEC AJAX-> retourne les titres de livres (ayant la valeur entrée dans titre ou mots-clés ou auteur) dans la div sous la barre de recherche
    public function researchBook()
    {
        if(array_key_exists('research',$_GET))
        {
            $keywords = htmlspecialchars(trim($_GET['research']));
            //var_dump($keywords);

            $books_research = $this -> _book -> searchBookBdd($keywords);
            //si $books_research (array) est vide alors on retourne null pour pouvoir afficher -> aucun résultat
            if(count($books_research) == 0)
            {
                $books_research = 'null';
            }

            require "views/book/bookSearchTarget.phtml";
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }

    //METHODE RETOURNE UN LIVRE AVEC ID
    public function displayBook()
    {
        if(array_key_exists('id',$_GET))
        {
            $book_id = htmlspecialchars($_GET['id']);
            
            //évite erreur au niveau requête car attend un nombre donc si une string est entrée au niveau url -> erreur
            if(is_numeric($book_id))
            {
                $book_id = intval($book_id);
                $book_result = $this -> _book -> getBookById($book_id);
                //var_dump($book_result);
                
                if($book_result)
                {
                    switch($book_result['statut'])
                    {
                        case 'disponible':
                            $return_date['return_date'] ='';
                            break;
                        case 'en prêt':
                            $return_date = $this -> _borrow -> getBorrowReturnDate($book_id);
                            break;
                        case 'consultation sur place':
                            $return_date['return_date'] ='';
                            break;
                        case 'réservé':
                            $return_date['return_date'] = '';
                            break;
                    }
                    //template
                    $titre='Description livre';
                    $template = "views/book/bookSearchById";
                }
                else
                {
                    header("location:index.php");
                    exit();
                } 
            }
            else
            {
                header("location:index.php");
                exit();
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

    //METHODE BARRE DE RECHERCHE AVEC SUBMIT FORM-> retourne listes de livres ayant la valeur entrée dans titre ou mots-clés ou auteur
    public function displayListBook()
    {
        if(isset($_GET['bookList']))
        {
            if(!empty($_GET['bookList']))
            {
                //BARRE RECHERCHE, recherche avec tout les mots
                //récupère les valeurs de l'input de recherche
                $keywords_input = htmlspecialchars($_GET['bookList']);
                //divise la string avec comme séparateur un espace, les différents mots sont mis dans un tableau / trim enlève les espaces
                $array_result=explode(' ', trim($keywords_input));
                //var_dump($array_result);
                $result_books=[];

                //pour chaque mot du tableau array_result on fait une recherche dans la BDD
                for($i=0, $length=count($array_result); $i<$length; $i++){
                    $one_keyword = $array_result[$i];
                    //var_dump($one_keyword);
                    //on insère le résultat de chaque requête dans le tableau research
                    $research[] = $this -> _book -> getBooksList($one_keyword);
                    //var_dump($research);
                    
                }
            
                // On doit remettre tout le tableau sur un seul niveau pour ensuite pouvoir supprimer les doublons
                for($i=0; $i<count($research); $i++) {
                    for($j=0; $j<count($research[$i]); $j++) {
                        $result_books[] = $research[$i][$j];
                    }
                }
                
                if(!empty($result_books))
                {
                    //on supprime les doublons du tableau
                    $result_books = array_unique($result_books,SORT_REGULAR);
                    $numberResearch = count($result_books);

                    if($numberResearch==1){
                        $messageResearch = '<span>'.$numberResearch.'</span> résultat trouvé correspondant à la recherche  '.'<span>'.$keywords_input.'</span>';
                    }else
                    {
                        $messageResearch = '<span>'.$numberResearch.'</span> résultats trouvés correspondant à la recherche  '.'<span>'.$keywords_input.'</span>';
                    }
                }
                else
                {
                    $numberResearch=0;
                    $messageResearch = '<span>'.$numberResearch.'</span> résultat trouvé correspondant à la recherche  '.'<span>'.$keywords_input.'</span>';
                }

                //appel au template
                $titre='Résultats de recherches';
                $template = "views/book/researchBooks";

                //appel au layout
                require "views/layout.phtml";
            }
            else
            {
                header("location:index.php");
                exit();
            }
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }

    /*****************************************************************************
                                METHODES USER
    *******************************************************************************/
    //METHODE RECUPERE UN LIVRE AVEC ID POUR L AJOUTER A LA WISHLIST
    public function getFavoriteBook()
    {
        if($this -> _userController ->is_connect())
        {
            if(array_key_exists('id',$_GET))
            {
                $id_book = intval(htmlspecialchars($_GET['id']));
                $book_result = $this -> _book -> getBookById($id_book);
                //var_dump($book_result);
                    if($book_result)
                    {
                       echo json_encode($book_result);
                    }
                    else
                    {
                       $messageUser='Une erreur est survenue.';
                    }
            }
            else
            {
                header("location:index.php");
                exit();
            }
        }
        else
        {
            header("location:index.php");
            exit();
        }
    } 

    //PAGE DES FAVORIS
    public function getWishList()
    {
        if($this -> _userController ->is_connect())
        {
            //appel au template
            $titre='Mes favoris';
            $template = "views/book/wishListUser";
            //appel au layout
            require "views/layout.phtml";
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
    //AJOUTER UN LIVRE DANS LA BDD
    public function addBook()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            if(isset($_POST) && !empty($_POST))
            {
                $errors=[];

                if(isset($_POST['isbn']) && !empty($_POST['isbn']))
                {
                    $isbn = htmlspecialchars(trim($_POST['isbn']));
                }
                else
                {
                    $errors['isbn']= 'Veuillez remplir l\'ISBN';
                }

                if(isset($_POST['auteur']) && !empty($_POST['auteur']))
                {
                    $auteur = htmlspecialchars(trim($_POST['auteur']));
                }
                else
                {
                    $errors['auteur']= 'Veuillez remplir l\'auteur';
                }

                if(isset($_POST['titre']) && !empty($_POST['titre']))
                {
                    $titre = htmlspecialchars(trim($_POST['titre']),ENT_NOQUOTES);//Pour titre avec apostrophe
                }
                else
                {
                    $errors['titre']= 'Veuillez remplir le titre du livre';
                }

                if(isset($_POST['editeur']) && !empty($_POST['editeur']))
                {
                    $editeur = htmlspecialchars(trim($_POST['editeur']));
                }
                else
                {
                    $errors['editeur']= 'Veuillez remplir l\'éditeur';
                }

                if(isset($_POST['date']) && !empty($_POST['date']))
                {
                    $date = htmlspecialchars(trim($_POST['date']));
                }
                else
                {
                    $errors['date']= 'Veuillez remplir la date de parution du livre';
                }

                if(isset($_POST['collection']) && !empty($_POST['collection']))
                {
                    $collection = htmlspecialchars(trim($_POST['collection']));
                }
                else
                {
                    $collection = '';
                }

                if(isset($_POST['genre']) && !empty($_POST['genre']))
                {
                    $genre = htmlspecialchars(trim($_POST['genre']));
                }
                else
                {
                    $errors['genre']= 'Veuillez remplir le genre';
                }

                if(isset($_POST['tome']) && !empty($_POST['tome']))
                {
                    if(is_numeric($_POST['tome']))
                    {
                        $tome = htmlspecialchars(trim($_POST['tome']));
                    }
                    else
                    {
                        $errors['tome']= 'Le tome doit être un nombre.';
                    }
                    
                }
                else
                {
                    $tome = '';
                }

                if(isset($_POST['nombre']) && !empty($_POST['nombre']) && is_numeric($_POST['nombre']))
                {
                    $nombre = htmlspecialchars(trim($_POST['nombre']));
                    $nombre = intval($nombre);
                }
                else
                {
                    $errors['nombre']= 'Veuillez remplir le nombre de page du livre.';
                }

                if(isset($_POST['format']) && !empty($_POST['format']))
                {
                    $format = htmlspecialchars(trim($_POST['format']));
                }
                else
                {
                    $errors['format']= 'Veuillez remplir le format du livre (grand format, poche, broché, ebook, etc.).';
                }

                if(isset($_POST['resume']) && !empty($_POST['resume']))
                {
                   $resume = htmlspecialchars(trim($_POST['resume']));
                }
                else
                {
                    $errors['resume']= 'Veuillez remplir un résumé du livre.';
                }

                if(isset($_POST['cle']) && !empty($_POST['cle']))
                {
                   $cle = htmlspecialchars(trim($_POST['cle']));
                }
                else
                {
                    $errors['cle']= 'Veuillez indiquer plusieurs mots-clés (séparés par un espace).';
                }
                
                if(isset($_POST['cote']) && !empty($_POST['cote']))
                {
                   $cote = htmlspecialchars(trim($_POST['cote']));
                }
                else
                {
                    $errors['cote']= 'Veuillez indiquer la cote du livre.';
                }

                if(isset($_POST['localisation']) && !empty($_POST['localisation']))
                {
                   $localisation = htmlspecialchars(trim($_POST['localisation']));
                }
                else
                {
                    $errors['localisation']= 'Veuillez indiquer la localisation interne.';
                }

                if(isset($_POST['categorie']) && !empty($_POST['categorie']))
                {
                    //var_dump($_POST['categorie']);
                    $categorie = htmlspecialchars($_POST['categorie']);
                    
                }
                else
                {
                    $errors['categorie']= 'Veuillez sélectionner une catégorie.';
                }
                
 
                if(isset($_POST['statut']) && !empty($_POST['statut']))
                {
                    //var_dump('statut: '.$_POST['statut']); 
                    $statut = htmlspecialchars($_POST['statut']);       
                }
                else
                {
                    $errors['statut']= 'Veuillez sélectionner un statut.';
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

                $ajout = date('Y-m-d');

                 
                //Si aucune erreur
                if(empty($errors))
                { 
                    $addBook = $this -> _book -> addBookInBdd($isbn, $auteur, $titre, $editeur, $date, $collection, $genre, $tome, $nombre, $format, $resume, $cle, $cote, $localisation, $categorie, $statut, $imageName, $ajout);
                    //var_dump($addBook);
                    if($addBook)//si le livre a bien été ajouté dans la BDD
                    {
                        if(!empty($_FILES['image']))
                        {
                            $uploads_dir = 'public/assets/images';
                            $tmp_name = $_FILES['image']['tmp_name'];
                            //var_dump($tmp_name);
                            move_uploaded_file($tmp_name, "$uploads_dir/$imageName");
                        }

                        $messageAdmin= 'Le livre a bien été ajouté au catalogue.';
                    }
                    else
                    {
                        $messageAdmin= 'Une erreur est survenue, le livre n\'a pas été ajouté au catalogue.';
                    }
                            
                }
                else
                {
                    $messageAdmin= 'Veuillez remplir tous les champs.';
                }
            }
           
            $titre="Ajouter un livre au catalogue";
            $template = "admin/addBook";
            //appel au layout
            require "views/layoutAdmin.phtml";
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }


    //AJAX RECUPERE LIVRE ET AFFICHE DONNEES
    public function loadBookAdmin()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            if(array_key_exists('idBook',$_GET))
            {
                $book_id = $_GET['idBook'];
                $book = $this -> _book -> getBookById($book_id);

                require 'views/admin/targetEditBook.phtml';
            }
            else
            {
                echo $messageAdmin = 'Une erreur est survenue.';
            }
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }

    //MODIFIER DONNEES D'UN LIVRE
    public function editBookAdmin()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            if(isset($_POST) && !empty($_POST))
            {
                $errors=[];

                if(isset($_POST['id']) && !empty($_POST['id']))
                {
                    $book_id = htmlspecialchars($_POST['id']);
                    $book_id = intval($book_id);
                    //var_dump($book_id);
                }
                else
                {
                    $errors['id']= 'Une erreur est survenue';
                }

                if(isset($_POST['isbn']) && !empty($_POST['isbn']))
                {
                    $isbn = htmlspecialchars(trim($_POST['isbn']));
                }
                else
                {
                    $errors['isbn'] = 'Veuillez remplir l\'ISBN';
                }

                if(isset($_POST['auteur']) && !empty($_POST['auteur']))
                {
                    $auteur = htmlspecialchars(trim($_POST['auteur']));
                }
                else
                {
                    $errors['auteur']= 'Veuillez remplir l\'auteur';
                }

                if(isset($_POST['titre']) && !empty($_POST['titre']))
                {
                    $titre = htmlspecialchars(trim($_POST['titre']),ENT_NOQUOTES);//Pour titre avec apostrophe
                }
                else
                {
                    $errors['titre']= 'Veuillez remplir le titre du livre';
                }

                if(isset($_POST['editeur']) && !empty($_POST['editeur']))
                {
                    $editeur = mb_strtolower(htmlspecialchars(trim($_POST['editeur'])), 'UTF-8');
                }
                else
                {
                    $errors['editeur']= 'Veuillez remplir l\'éditeur';
                }

                if(isset($_POST['date']) && !empty($_POST['date']))
                {
                    $date = htmlspecialchars(trim($_POST['date']));
                }
                else
                {
                    $errors['date']= 'Veuillez remplir la date de parution du livre';
                }

                if(isset($_POST['collection']) && !empty($_POST['collection']))
                {
                    $collection = htmlspecialchars(trim($_POST['collection']));
                }
                else
                {
                    $collection = '';
                }

                if(isset($_POST['genre']) && !empty($_POST['genre']))
                {
                    $genre = mb_strtolower(htmlspecialchars(trim($_POST['genre'])), 'UTF-8');
                }
                else
                {
                    $errors['genre']= 'Veuillez remplir le genre';
                }

                if(isset($_POST['tome']) && !empty($_POST['tome']))
                {
                    $tome = htmlspecialchars(trim($_POST['tome']));
                }
                else
                {
                    $tome = '';
                }

                if(isset($_POST['nombre']) && !empty($_POST['nombre']) && is_numeric($_POST['nombre']))
                {
                    $nombre = htmlspecialchars(trim($_POST['nombre']));
                    $nombre = intval($nombre);
                }
                else
                {
                    $errors['nombre']= 'Veuillez remplir le nombre de page du livre.';
                }

                if(isset($_POST['format']) && !empty($_POST['format']))
                {
                    $format = mb_strtolower(htmlspecialchars(trim($_POST['format'])), 'UTF-8');
                }
                else
                {
                    $errors['format']= 'Veuillez remplir le format du livre (grand format, poche, broché, ebook, etc.).';
                }

                if(isset($_POST['resume']) && !empty($_POST['resume']))
                {
                   $resume = htmlspecialchars(trim($_POST['resume']));
                }
                else
                {
                    $errors['resume']= 'Veuillez remplir un résumé du livre.';
                }

                if(isset($_POST['cle']) && !empty($_POST['cle']))
                {
                   $cle = mb_strtolower(htmlspecialchars(trim($_POST['cle'])), 'UTF-8');
                }
                else
                {
                    $errors['cle']= 'Veuillez indiquer plusieurs mots-clés (séparés par un espace).';
                }
                
                if(isset($_POST['cote']) && !empty($_POST['cote']))
                {
                   $cote = htmlspecialchars(trim($_POST['cote']));
                }
                else
                {
                    $errors['cote']= 'Veuillez indiquer la cote du livre.';
                }

                if(isset($_POST['localisation']) && !empty($_POST['localisation']))
                {
                   $localisation = mb_strtolower(htmlspecialchars(trim($_POST['localisation'])), 'UTF-8');
                }
                else
                {
                    $errors['localisation']= 'Veuillez indiquer la localisation interne.';
                }

                if(isset($_POST['categorie']) && !empty($_POST['categorie']))
                {
                    //var_dump($_POST['categorie']);
                    $categorie = htmlspecialchars($_POST['categorie']);
                    
                }
                else
                {
                    $errors['categorie']= 'Veuillez sélectionner une catégorie.';
                }
                

                if(isset($_FILES['image']) && !empty($_FILES['image']))
                {
                    $imageName = mb_strtolower($_FILES['image']['name'], 'UTF-8');
                    $image=$_FILES['image'];
                }
                else
                {
                    $imageName = '';
                }
            
                if(empty($errors))
                {
                    //si on ajoute pas d'image dans l'input image on modifie la BDD sans image
                    if(empty($imageName))
                    {
                        $edit = $this -> _book -> updateBookWithoutImg($isbn, $auteur, $titre, $editeur, $date, $collection, $genre, $tome, $nombre, $format, $resume, $cle, $cote, $localisation, $categorie, $book_id);
                        //var_dump($edit);
                        if($edit)
                        {
                            $messageAdmin="La notice du livre a bien été modifiée excepté l'image.";
                        }
                        else
                        {
                            $messageAdmin="Une erreur est survenue.";
                        }
                        
                        
                    }
                    //sinon si on insère une image dans le champs, on récupère le nom de l'image actuelle de la BDD pour la supprimer
                    else if(!empty($imageName) && $image['error'] == 0)
                    {
                        $max_size = 900000;
                        if($image['size']>$max_size)
                        {
                            $messageAdmin= "La taille de l'image est trop grande.";
                        }
                        else
                        {
                            $extensions = ['jpg','jpeg','png'];
                            $extFile = explode('.',$imageName);
                            $extFile = end($extFile);
                            //var_dump($extFile);

                            if(in_array($extFile,$extensions))
                            {
                                require "lib/mime.php";
                                if (is_uploaded_file($_FILES['image']['tmp_name'])) 
                                {
                                    $mime_type = mime_content_type($_FILES['image']['tmp_name']);
                                
                                    if (!in_array($mime_type, MIME_TYPES)) 
                                    {
                                        $messageAdmin = 'Le fichier n\'a pas été enregistré correctement car il ne correspond pas au MIME indiqué';
                                    }
                                    else
                                    {

                                        //appel fonction recupère nom image actuelle pour la supprimer du dossier avant de la remplacer
                                        $imageNow= $this -> _book ->  getCurrentImg($book_id);   
                                        //var_dump($imageNow['image']);
                                        $uploads_dir = 'public/assets/images';
                                        
                                        
                                        //suppression de l'image' du dossier images si le nom de l'image n'est pas vide et si le fichier existe
                                        if(!empty($imageNow['image']) && file_exists($uploads_dir."/".$imageNow['image']))
                                        { 
                                            unlink($uploads_dir."/".$imageNow['image']);
                                            $messageAdmin= "L'image ".$imageNow['image']." a bien été supprimée";
                                        }
                                        else
                                        {
                                            $messageAdmin= "Le fichier n'existe pas";
                                        }
                                    

                                        $edit = $this -> _book -> updateBookWithImg($isbn, $auteur, $titre, $editeur, $date, $collection, $genre, $tome, $nombre, $format, $resume, $cle, $cote, $localisation, $categorie, $imageName, $book_id);
                                    
                                        if($edit)
                                        { //si $test true donc infos recupérées
                                            if(!empty($_FILES['image']))
                                            {
                                                $uploads_dir = 'public/assets/images';
                                                $tmp_name = $_FILES["image"]["tmp_name"];
                                                //var_dump($tmp_name);
                                                move_uploaded_file($tmp_name, "$uploads_dir/$imageName");
                                                
                                                $messageAdmin="La notice de livre a bien été modifiée avec la photo.";
                                            }
                                            else
                                            {
                                                $messageAdmin= "Le champs photo n'est pas correctement rempli.";
                                            }
                                        }
                                        else
                                        {
                                            $messageAdmin= "Une erreur est survenue, veuillez recommencer la démarche.";
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $messageAdmin = 'L\'extension du fichier n\'est pas valide.';
                            }
                        }
                    }
                    else
                    {
                        $messageAdmin= "Votre image n'est pas adaptée.";
                    }
                }
            }

            $titre="Modifier un livre";
            $template = "admin/editBook";
            //appel au layout
            require "views/layoutAdmin.phtml";
        }
        else
        {
            header("location:index.php");
            exit();
        }
    }

    //SUPPRIMER UN LIVRE
    public function deleteBook()
    {
        if($this -> _adminController ->adminIs_connect())
        {
            //on récupère l'id à travers url de requete ajax
            if(array_key_exists("idBook",$_GET))
            {
                $book_id = $_GET['idBook'];
                //var_dump($book_id);
                
                //supprimer la photo du dossier
                $imageNow= $this -> _book ->  getCurrentImg($book_id);
                //var_dump($imageNow);
                $uploads_dir = 'public/assets/images';
            
                //suppression de l'image du dossier images si le nom de l'image n'est pas vide et si le fichier existe
                if(!empty($imageNow['image']) && file_exists($uploads_dir."/".$imageNow['image']))
                { 
                    unlink($uploads_dir."/".$imageNow['image']);
                    echo $messageAdmin= "L'image ".$imageNow['image'].", associée au livre retiré, a bien été supprimée. ";
                }
                else
                {
                    echo $messageAdmin= "Il n'y a pas de fichier image associé au livre retiré. ";
                }
                
                //supprimer les infos de BDD 
                $deleteBook = $this -> _book ->deleteBookBdd($book_id);
                //var_dump($deleteBook);
                if($deleteBook)
                {
                    echo $messageAdmin= "Le livre a été supprimé du catalogue.";
                }
                else
                {
                    echo $messageAdmin= "La suppression a échouée.";
                }
            }
            else
            {
                echo $messageAdmin= "Une erreur est survenue";
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