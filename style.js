/**
* @file style.js
* @brief Fonction Javascript pour une meilleure gestion de la page
*/

$(document).ready(function(){
  
  // Si aucun bouton n'est coché, on n'affiche pas le champ d'entrée de texte
  $(".specificField").hide();

  // On affiche le champ correspondant au bouton radio coché
  $(".radioSelect").each(function(){
    showSpecificFields(this);
  });

  // On affiche le champ correspondant au bouton radio coché
  $(".radioSelect").click(function(){
    showSpecificFields(this);
  });

  /**
  * Affiche un objet avec un id particulier en fonction du bouton radio coché
  * @param obj L'objet dont on veut contrôler s'il est coché ou non
  */
  function showSpecificFields(obj){
    if($(obj).is(":checked")){
      var radioVal = $(obj).val();
      $(".specificField").not('.'+radioVal).each(function(){
        $(this).hide();
      });
      $(".specificField."+radioVal).each(function(){
        $(this).show();
      });
    }
  }

});
