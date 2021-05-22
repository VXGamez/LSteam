

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

var hash = location.hash.replace(/^#/, '');
if (hash) {
    $('.nav-tabs a[href="#' + hash + '"]').tab('show');
}
$('.nav-tabs a').on('shown.bs.tab', function (e) {
    window.location.hash = e.target.hash;
})
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