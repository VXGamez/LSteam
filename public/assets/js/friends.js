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

function accept (requestID) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/user/friendRequests/accept/'+requestID;
    document.body.appendChild(form);
    form.submit();
}

function deny (requestID) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/user/friendRequests/deny/'+requestID;
    document.body.appendChild(form);
    form.submit();
}

