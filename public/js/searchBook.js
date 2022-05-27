/*BARRE RECHERCHE LIVRE*/

//fonction ajax de la recherche
function ajaxResearchBook() {
  let research = inputSearch.value;
  research = research.toLowerCase();
  //console.log(research);

  if (research != '') 
  {
    $('#targetResearch').removeClass('hide');

    //AJAX SANS JQUERY
    let request = new Request('index.php?action=ajaxResearchBook&research=' + research);
    fetch(request)
      // Récupère les données
      .then((res) => res.text())
      // Exploite les données
      .then((res) => {
        //console.log(res);
        document.getElementById('targetResearch').innerHTML = res;
      })
      .catch((erreur) => {
        // Gestion des erreurs
        document.getElementById('targetResearch').innerHTML = 'Erreur';
      });
  }
}

function stopPropagation(event) {
  event.stopPropagation();
}

function hide() {
  $('#targetResearch').addClass('hide');
  //vide la barre de recherche
  document.getElementById('researchWords').value = '';
}