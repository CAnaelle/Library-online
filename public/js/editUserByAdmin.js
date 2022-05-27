//2-requete AJAX
function displayFormEditUser() {
  let idUser = this.dataset.iduser;
  //console.log(idUser);
  if (idUser == '' || idUser == undefined) 
  {
    displayForm(false);
  } 
  else 
  {
    $.get('index.php?action=getOneUser&idUser='+idUser, displayForm);
  }
}

// 3-fonction callBack qui reçoit la réponse AJAX
function displayForm(response) {
  if (response === false) 
  {
    document.getElementById('targetEditUser').value = '';
    //$('#targetEditUser').empty();
  } 
  else 
  {
    document.getElementById('targetEditUser').value = '';
    //$('#targetEditUser').empty();
    document.getElementById('targetEditUser').innerHTML = response;
    //$('#targetEditUser').html(idUser);
  }
}
