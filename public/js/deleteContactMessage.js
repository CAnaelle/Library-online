/*SUPPRIMER UN MESSAGE PAR L'ADMIN*/
let id_message;
//fonction ajax de la recherche
function informDeleteMessage() {
  let rep = confirm('Êtes-vous sûr.e de vouloir supprimer ce message ?');
  if (rep) 
  {
    id_message = parseInt(this.dataset.idmess);
    //console.log(id_message);
    if (id_message !== '' && id_message !== undefined) 
    {
      $.get('index.php?action=deleteMessage&idMessage='+id_message, messageDeleteMessage);
    }
  } 
  else 
  {
    messageDeleteMessage(false);
  }
}

//fonction callBack qui reçoit la réponse AJAX
function messageDeleteMessage(response) {
  if (response) 
  {
    let message = document.getElementById('messageContact');
    //console.log(message);
    //VIDE LA LIGNE DE TABLEAU
    let row = document.getElementsByClassName('row' + id_message);
    //console.log(row);
    for (let i = 0; i < row.length; i++) 
    {
      row[i].remove();
    }
    message.innerHTML = response;
  }
}
