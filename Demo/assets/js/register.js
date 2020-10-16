$(document).ready(function (){

    // On clicking signup, show signup form and hide signin form
    $("#signup").click(function (){
       $("#first").slideUp("slow", function (){
          $("#second").slideDown("slow");
       });
    });

    // On clicking signin, show signup form and hide signup form
    $("#signin").click(function (){
       $("#second").slideUp("slow", function (){
          $("#first").slideDown("slow");
       });
    });

})