/*SUPPRIMER UN UTILISATEUR PAR ADMIN*/
let user_id;
//fonction ajax de la recherche
function informDeleteUser() {
  let rep = confirm('Êtes-vous sûr.e de vouloir supprimer cet utilisateur ?');
  if (rep) 
  {
    user_id = parseInt(this.dataset.iduser);
    //console.log(user_id);
    if (user_id !== '' && user_id !== undefined) 
    {
      $.get('index.php?action=deleteUser&idUser='+user_id, messageDeleteUser);
    }
  } 
  else 
  {
    messageDeleteUser(false);
  }
}

//fonction callBack qui reçoit la réponse AJAX
function messageDeleteUser(response) {
  if (response) 
  {
    let mess = document.getElementById('test');
    let message = document.getElementById('messageAdmin');
    //console.log(message);
    //enlève le message précédent si modification avant
    if (message != null && mess!='') 
    {
      mess.innerHTML ='';
    }
    message.innerHTML = response;
  //PRECISION de la suppression de la ligne uniquement dans le cas de ces 2 messages, (pas de suppression si utilisateur a toujours des prêts)
   if (response == "L'utilisateur a bien été supprimé de la BDD." || response == "L'utilisateur et toutes ses réservations ont bien été supprimés de la BDD.") 
   {
      //VIDE LA LIGNE DE TABLEAU
      let row = document.getElementsByClassName('row' + user_id);
      //console.log(row);
      for (let i = 0; i < row.length; i++) 
      {
        row[i].remove();
      }
    }
  }
}
