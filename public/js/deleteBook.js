/* SUPPRIMER UN LIVRE PAR ADMIN*/
function informDeleteBook() {
  let rep = confirm('Êtes-vous sûr.e de vouloir supprimer ce livre ?');
  if (rep) 
  {
    let id_book = document.getElementById('id');
    if (id_book) 
    {
      id_book = parseInt(document.getElementById('id').value);
    }
    //console.log(id_book);
    if (id_book !== '' && id_book !== undefined && id_book !== null) 
    {
      $.get('index.php?action=deleteBook&idBook='+id_book, messageDeleteBook);
    }
  } 
  else 
  {
    messageDeleteBook(false);
  }
}

//fonction callBack qui reçoit la réponse AJAX
function messageDeleteBook(response) {
  if (response) 
  {
    let message = document.getElementById('messageAdmin');
    //vide les précédents messages
    let messageAdminPhp = document.getElementById('messageAdminPhp');
    if (messageAdminPhp != '') 
    {
      messageAdminPhp.innerHTML = '';
    }
    //vide le formulaire
    let form = document.getElementById('targetEditBook');
    //console.log(form);
    form.innerHTML = '';
    message.innerHTML = response;
  }
}
