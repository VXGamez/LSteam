function compra(gameID, title, salePrice, storeID, dealRating, thumb, flag){
    let params = {
        title: title,
        salePrice: salePrice,
        storeID: storeID,
        dealRating: dealRating,
        thumb: thumb
    };


    const form = document.createElement('form');
    form.method = 'POST';
    if (flag == 0) {
        form.action = '/store/buy/'+gameID;
    }else {
        form.action = '/user/wishlist/'+gameID;
    }
   

    for (const key in params) {
        if (params.hasOwnProperty(key)) {
            const hiddenField = document.createElement('input');
            hiddenField.type = 'hidden';
            hiddenField.name = key;
            hiddenField.value = params[key];
            form.appendChild(hiddenField);
        }
    }

    document.body.appendChild(form);
    form.submit();
}

function deleteWish(gameID){

    fetch('http://localhost:8030/user/wishlist/' + gameID, {
        method: 'DELETE',
    })
        .then(response => {location.reload()});

}

//TODO: ELIMINAR ELEMENT DE LA WISHLIST
//Per fer el delete del favorito probablement estaria bé enlloc de fer servir el flag==2, fer una funció nova que faci això. Sobrecarregui el mètode del post.
//però ja que estem desde js, potser podriem fer la petició delete desde aqui, sense un formulari, sinó amb un fetch o algo asin. o con ajax mismo
//$app->add(new Slim\Middleware\MethodOverrideMiddleware);

/*
<form action="/users/wishlist/elIdDelJuego" method="post">
   ... lo que sea ...
    <input type="hidden" name="_METHOD" value="DELETE"/>
    <input type="submit" value="Eliminar Favorito"/>
</form>
* */


