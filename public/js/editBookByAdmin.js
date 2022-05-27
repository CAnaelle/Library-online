/*AFFICHER FICHE LIVRE*/
//2-requete AJAX
function loadBook(event) {
  let book = event.target;
  //console.log(book);
  let idBook = book.dataset.val;
  //console.log(idBook);
  let message = (document.getElementsByClassName('message').value = '');

  if (idBook == '' || idBook == undefined) 
  {
    showBook(false);
  } 
  else 
  {
    $.get('index.php?action=ajaxEditBook&idBook='+idBook, showBook);
  }
}

// 3-fonction callBack qui reçoit la réponse AJAX
function showBook(response) {
  let editTarget = document.getElementById('targetEditBook');
  if (response === false) 
  {
    if (editTarget) 
    {
      document.getElementById('targetEditBook').value = '';
      //$('#targetEditBook').empty();
    }
  } 
  else 
  {
    //pour éviter erreur sur console avec les autres pages (ajouter un prêt et remettre un livre en service) où cette action est utilisée uniquement pour avoir le titre du livre, on vérifie si la div targetEditbook(présente que dans la page modification d'une notice d'un livre) est présente
    if (editTarget) 
    {
      editTarget.value = '';
      //$('#targetEditBook').empty();
      editTarget.innerHTML = response;
      //$('#targetEditBook').html(response);
    }
  }
}
