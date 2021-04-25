

var needsAlert = false;

window.onload = function() {
    var elements = document.getElementsByClassName("elementComprat");
    for (var i = 0, len = elements.length; i < len; i++) {
        elements[i].start();
    }
};




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