//Déclarations variables
let id_book;
let globalArray;


//Vérifie dès le chargement de la page les éléments présents dans le localStorage et affecte l'attribut "checked" aux checkbox correspondantes aux livres présents dans le localStorage
function verifyLocaleStorage() {
  //on récupère le localStorage dans globaArray s'il existe déjà, sinon on l'initialise vide
  globalArray = JSON.parse(localStorage.getItem('wishlistArray'));
  //console.log(globalArray);
  //si vide alors -> null :on initialise globArray vide
  if (globalArray == null) 
  {
    globalArray = [];
  }
  //ajoute attribut checked pour les objets book présents dans le globalArray, pour colorer l'icône coeur à chaque rafraîchissement car sinon perte de l'attribut checked (et donc de la couleur rouge)
  //console.log(checkbox);
  for (let i = 0; i < checkbox.length; i++) 
  {
    for (let j = 0; j < globalArray.length; j++) 
    {
      let indice = globalArray[j].id;
      if (indice == checkbox[i].value) 
      {
        checkbox[i].setAttribute('checked', '');
      }
    }
  }
}

//fonction ajoute ou enlève livre au localStorage: si checkbox cliquée -> ajout au localStorage sinon suppression
function getFavorite() {
  //si checkbox cliquée alors récupère les données de l'id book avec ajax
  if (this.checked) 
  {
    //on récupère id book
    id_book = this.value;
    //requête ajax BDD pour récupérer les infos du livre
    if (id_book != '' && id_book != undefined) 
    {
      $.get('index.php?action=getFavorite', 'id='+id_book, addToWishlist);
    } 
    else 
    {
      addToWishlist(false);
    }
  } //sinon si décliquée -> suppression de l'objet book du tableau global
  else 
  {
    id_book = this.value;
    //console.log(typeof id_book)//->string;
    removeBook(id_book);
  }
}

//ajout des infos du-des livres dans un tableau global  puis localStorage
function addToWishlist(response) {
  let book = JSON.parse(response);
  //console.log(book);
  globalArray.push(book);
  //console.log(globalArray);
  saveBook();
  loadStorage();
}

//enregistre tableau global dans localStorage
function saveBook() {
  //transformer une variable en json
  globalArray = JSON.stringify(globalArray); //tableau -> json
  //console.log(globalArray);
  window.localStorage.setItem('wishlistArray', globalArray);
}

//récupère localStorage
function loadStorage() {
  //récupère les données et les retransforme au format tableau
  globalArray = window.localStorage.getItem('wishlistArray');

  if (globalArray != null) 
  {
    globalArray = JSON.parse(globalArray); // json -> tableau
    //console.log(globalArray);
  } 
  else 
  {
    //on gère le cas où le tableau est null, on vide le tableau
    globalArray = [];
  }
}

//fonction pour retirer un livre du tableau
function removeBook(id_book) {
  let indice;
  for (let i = 0; i < globalArray.length; i++) 
  {
    indice = globalArray[i].id;
    //console.log(typeof indice)//->number;
    if (id_book == indice) 
    {
      globalArray.splice(i, 1);
      saveBook();
      loadStorage();
    }
  }
  displayWishList();
}

//fonction pour afficher la liste des favoris
function displayWishList() {
  let urlcourante = document.location.href;
  let queue_url = urlcourante.substring(urlcourante.lastIndexOf('/') + 1);

  if (queue_url == 'index.php?action=wishlist') 
  {
    if (globalArray.length == 0) 
    {
      targetDisplayWishList.innerHTML = '<p class="messageUser infoBooking">Votre liste de favoris est vide.</p>';
    } 
    else 
    {
      let wishlist = [];
      for (let k = 0; k < globalArray.length; k++) 
      {
        wishlist +=
          '<div class="headBook clearfix"><input type="checkbox" name="wishlistArray" id="' +
          globalArray[k].id +
          '" value="' +
          globalArray[k].id +
          '" class="wishlist wishlist--floating"/><label for="' +
          globalArray[k].id +
          '" class="wishlist--floating"></label><h2>' +
          globalArray[k].titre +
          ' ' +
          globalArray[k].tome +
          '</h2><strong>' +
          globalArray[k].auteur +
          '</strong><div class="clearfix"><div class="imgBook"><img src="public/assets/images/' +
          globalArray[k].image +
          '" alt="' +
          globalArray[k].titre +
          '" class="imgBook__img"/></div><p><span>Résumé: </span>' +
          globalArray[k].resume +
          '</p><p><span>Genre: </span>' +
          globalArray[k].genre +
          '</p><p><span>Statut: </span>' +
          globalArray[k].statut +
          '</p></div></div>';
      }
      targetDisplayWishList.innerHTML = wishlist;
      checkbox = document.querySelectorAll('input[type=checkbox].wishlist');
      for (let i = 0; i < checkbox.length; i++) 
      {
        checkbox[i].addEventListener('change', getFavorite);
      }
      verifyLocaleStorage();
    }
  }
}

//fonction pour vider la liste de favoris
function eraseWishList() {
  let rep = confirm('Êtes-vous sûr.e de vouloir vider votre liste de favoris ?');
  if (rep) 
  {
    targetDisplayWishList.innerHTML = '<p class="messageUser infoBooking">Votre liste de favoris est vide.</p>';
    this.arrayGlob = [];
    window.localStorage.clear();
  } 
  else 
  {
    return false;
  }
}
