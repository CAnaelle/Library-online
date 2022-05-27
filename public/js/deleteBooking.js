/*SUPPRIMER UNE RESERVATION */
let id_reservation;
//fonction ajax de la recherche
function informDelete() {
  let rep = confirm('Êtes-vous sûr.e de vouloir supprimer la réservation de ce livre ?');
  if (rep) 
  {
    id_reservation = parseInt(this.dataset.idreserv);
    let id_book = parseInt(this.dataset.idbook);
    //console.log(id_reservation, id_book);
    if (id_reservation !== '' && id_reservation !== undefined && id_book !== '' && id_book !== undefined) 
    {
      $.get('index.php?action=deleteBooking&idBooking='+id_reservation+'&idBook='+id_book, messageDelete);
    }
  } 
  else 
  {
    messageDelete(false);
  }
}

//fonction callBack qui reçoit la réponse AJAX
function messageDelete(response) 
{
  if (response) 
  {
    let message = document.getElementById('messageUser');
    //console.log(message);
    //VIDE LA LIGNE DE TABLEAU
    let row = document.getElementsByClassName('row' + id_reservation);
    //console.log(row);
    for (let i = 0; i < row.length; i++) 
    {
      row[i].remove();
    }

    //AFFICHE LE MESSAGE DE CONFIRMATION
    //pour éviter conflit si c'est l'admin qui supprime
    if (message != null) 
    {
      message.innerHTML = 'La réservation a bien été supprimée.';
      /* $('#messageUser').text(
      'La réservation a bien été supprimée.'
    ); */
    }
  }
}
