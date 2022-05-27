/*SUPPRIMER UN EVENEMENT PAR L'ADMIN*/
let id_event;
//fonction ajax de la recherche
function informDeleteEvent() {
  let rep = confirm('Êtes-vous sûr.e de vouloir supprimer cet évènement ?');
  if (rep) 
  {
    id_event = parseInt(this.dataset.idevent);
    //console.log(id_message);
    if (id_event !== '' && id_event !== undefined) 
    {
      $.get('index.php?action=deleteEvent&idEvent='+id_event, messageDeleteEvent);
    }
  } 
  else 
  {
    messageDeleteEvent(false);
  }
}

//fonction callBack qui reçoit la réponse AJAX
function messageDeleteEvent(response) {
  if (response) 
  {
    let message = document.getElementById('messageAdmin');
    //VIDE LA LIGNE DE TABLEAU
    let row = document.getElementsByClassName('row' + id_event);
    //console.log(row);
    for (let i = 0; i < row.length; i++) 
    {
      row[i].remove();
    }
    message.innerHTML = response;
  }
}
