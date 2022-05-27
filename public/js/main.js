//Déclaration variables
let inputSearch;
let iconXMark;
let container;
let checkbox;
let targetDisplayWishList;
let deleteWishList;
let titleBook;
let editUser;
let deleteUser;
let deleteMessage;
let deleteEvent;
let optionsBook;
let buttonDeleteBook;
let imageSlider;
let containerSlider;
let itemsSlider;
let nextBtnSlider;
let previousBtnSlider;
let navDotsSlider;
let current;
let numberImageSlider;
let imgWidth;

//Déclaration des fonctions
//Code principal
document.addEventListener('DOMContentLoaded', function () {
  //1-sélection élément HTML
  inputSearch = document.getElementById('researchWords');
  iconXMark = document.getElementsByClassName('deleteBooking');
  container = document.querySelectorAll('.container');
  checkbox = document.querySelectorAll('input[type=checkbox].wishlist');
  targetDisplayWishList = document.getElementById('targetWishList');
  deleteWishList = document.querySelector('.deleteWishList');
  titleBook = document.getElementById('titreBook');
  editUser = document.getElementsByClassName('editUser');
  deleteUser = document.getElementsByClassName('deleteUser');
  deleteMessage = document.getElementsByClassName('deleteMessage');
  deleteEvent = document.getElementsByClassName('deleteEvent');
  optionsBook = document.querySelectorAll('#targetResearchAdmin');
  buttonDeleteBook = document.getElementById('deleteBookButton');
  imageSlider = document.querySelectorAll('.item');
  containerSlider = document.querySelector('.items-container');
  nextBtnSlider = document.querySelector('.next-btn');
  previousBtnSlider = document.querySelector('.previous-btn');
  navDotsSlider = document.querySelector('.nav-dots');

  //2- installer un event sur un element HTML

  //Recherche livres/Barre de reherche
  if (inputSearch) {
    inputSearch.addEventListener('input', ajaxResearchBook);
    //enlever les résultats de recherche lorsque l'on clique sur le container
    for (let i = 0; i < container.length; i++) {
      container[i].addEventListener('click', hide);
    }
    $('.form').on('click', stopPropagation); //arrêter la propagation de l'event: évite ajout de la classe hide à la div de resultats de recherche lors du clic sur l'input recherche
  }

  //suppression réservation
  if (iconXMark) {
    for (let i = 0; i < iconXMark.length; i++) {
      iconXMark[i].addEventListener('click', informDelete);
    }
  }

  //ajout aux favoris
  for (let i = 0; i < checkbox.length; i++) {
    checkbox[i].addEventListener('change', getFavorite);
  }

  //vider la wishlist
  if (deleteWishList) {
    deleteWishList.addEventListener('click', eraseWishList);
  }

  //Rechercher avec titre livre (ajouter prêt ou modifier livre côté admin)
  if (titleBook) {
    titleBook.addEventListener('input', ajaxResearchBookAdmin);
    for (let i = 0; i < container.length; i++) {
      container[i].addEventListener('click', hide);
    }
    titleBook.addEventListener('click', stopPropagation);
  }

  //Charger la fiche livre pour la modification
  if (optionsBook) {
    //console.log(optionsBook);
    for (let i = 0; i < optionsBook.length; i++) {
      optionsBook[i].addEventListener('click', loadBook);
    }
  }

  //Modifier infos utilisateur par admin
  if (editUser) {
    for (let i = 0; i < editUser.length; i++) {
      editUser[i].addEventListener('click', displayFormEditUser);
    }
  }

  //Supprimer un utilisateur par admin
  if (deleteUser) {
    for (let i = 0; i < deleteUser.length; i++) {
      deleteUser[i].addEventListener('click', informDeleteUser);
    }
  }

  //Supprimer un message de contact par admin
  if (deleteMessage) {
    for (let i = 0; i < deleteMessage.length; i++) {
      deleteMessage[i].addEventListener('click', informDeleteMessage);
    }
  }
  
  //Supprimer un évènement par admin
  if (deleteEvent) {
    for (let i = 0; i < deleteEvent.length; i++) {
      deleteEvent[i].addEventListener('click', informDeleteEvent);
    }
  }
  
  //Supprimer un livre par admin
  if (buttonDeleteBook) {
    buttonDeleteBook.addEventListener('click', informDeleteBook);
  }

  //3-appel fonctions
  //Carrousel
  if (imageSlider && imageSlider.length !== 0) {
    current = 0;
    imgWidth = imageSlider[current].clientWidth;
    numberImageSlider = imageSlider.length;
    nextBtnSlider.addEventListener('click', nextBtn);
    previousBtnSlider.addEventListener('click', previousBtn);
    initialisation();
    document.addEventListener('keydown', clickClavier);
    setInterval(nextBtn, 6000);
  }

  //Wishlist
  verifyLocaleStorage();
  if (targetDisplayWishList) {
    displayWishList();
  }
});
