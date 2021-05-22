
/************************************************
* Donat que tenim una marquesina al lateral de la profile per mostrar els elements comprats, volem iniciar el moviment de totes elles en carregar la pàgina
************************************************/
var needsAlert = false;
window.onload = function() {
    var elements = document.getElementsByClassName("elementComprat");
    for (var i = 0, len = elements.length; i < len; i++) {
        elements[i].start();
    }
};


/************************************************
* A més, de la mateixa manera que a friends.js, voldrem canviar la pestanya d'acord amb el link al que s'accedeixi
************************************************/
jQuery(document).ready(function($){

    var hash = window.location.hash;
    hash && $('ul.nav a[href="' + hash + '"]').tab('show');
    $('.nav-tabs a').click(function (e) {
        $(this).tab('show');
        var scrollmem = $('body').scrollTop();
        window.location.hash = this.hash;
        $('html,body').scrollTop(scrollmem);
    });

});


/************************************************
* Aquesta funció previsualitza la imatge que esta a punt de carregar el usuari abans que aquest realitzi el post en es faran totes les comprovacions
************************************************/
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#profilePicture')
                .attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
}

/************************************************
* Al perfil, a la part de canviar contrasenya, trobem la possibilitat de alternar la visibilitat d'aquesta. Per fer-ho emprem uns petits ulls al input i aquesta funció se'n encarrega de canviar el format del input
************************************************/
$(function() {
    $('.password-group').find('.password-box').each(function(index, input) {
        var $input = $(input);
        $input.parent().find('.password-visibility').click(function() {
            var change = "";
            if ($(this).find('i').hasClass('fa-eye')) {
                $(this).find('i').removeClass('fa-eye')
                $(this).find('i').addClass('fa-eye-slash')
                change = "text";
            } else {
                $(this).find('i').removeClass('fa-eye-slash')
                $(this).find('i').addClass('fa-eye')
                change = "password";
            }
            var rep = $("<input type='" + change + "' />")
                .attr('id', $input.attr('id'))
                .attr('name', $input.attr('name'))
                .attr('class', $input.attr('class'))
                .val($input.val())
                .insertBefore($input);
            $input.remove();
            $input = rep;
        }).insertAfter($input);
    });
});