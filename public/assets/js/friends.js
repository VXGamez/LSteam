
/************************************************
* Hem barrejat jQuery amb JS. Essencialment volem que en carregar la pàgina es mostre la pestanya pertinent a el link.
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
* En aquesta funció creem un formulari que adherim al body del web per realitzar la petició post i acceptar una solicitud d'amistad
************************************************/
function accept (requestID) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/user/friendRequests/accept/'+requestID;
    document.body.appendChild(form);
    form.submit();
}

/************************************************
* En aquesta funció creem un formulari que adherim al body del web per realitzar la petició post i denegar una solicitud d'amistad
************************************************/
function deny (requestID) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/user/friendRequests/deny/'+requestID;
    document.body.appendChild(form);
    form.submit();
}

