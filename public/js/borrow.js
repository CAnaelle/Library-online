/* RECHERCHER LIVRE AVEC LE TITRE LORS DE PRET PAR ADMIN*/

//fonction ajax de la recherche
function ajaxResearchBookAdmin() {
  let research = this.value;
  research = research.toLowerCase();
  //console.log(research);

  if (research != '') 
  {
    $('#targetResearchAdmin').removeClass('hide');
    $.get('index.php?action=ajaxResearchBookAdmin','research='+research, displayBook);
  } 
  else 
  {
    displayBook(false);
  }
}

function stopPropagation(event) {
  event.stopPropagation();
}

function hide() {
  $('#targetResearchAdmin').addClass('hide');
  //vide la barre de recherche
  document.getElementById('targetResearchAdmin').value = '';
}

//fonction callBack qui reçoit la réponse AJAX
function displayBook(response) {
  $('#targetResearchAdmin').html(response);
  let option = document.querySelectorAll('.optionSearch');
  for (let i = 0; i < option.length; i++) 
  {
    option[i].addEventListener('click', function () {
      titleBook.value = this.innerText;
    });
  }
}
