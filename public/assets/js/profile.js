

var needsAlert = false;

window.onload = function() {
    var elements = document.getElementsByClassName("elementComprat");
    for (var i = 0, len = elements.length; i < len; i++) {
        elements[i].start();
    }
};

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