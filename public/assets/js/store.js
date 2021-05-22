/************************************************
* Funció que hem emprat per realitzar tant la petició POST de compra de un joc, com la de afegir a la wishlist donat que gairebé tenen la mateixa informació

Per fer-ho fem un formulari ocult amb elements ocults al que li fem el submit desde aqui.
************************************************/
function compra(gameID, title, normalPrice, salePrice, storeID, dealRating, thumb, flag){
    let params = {
        title: title,
        salePrice: salePrice,
        normalPrice: normalPrice,
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

/************************************************
* Aquesta funció, tal i com indica el seu nom, se'n encarregar de peticionar l'adreça /user/wishlist/{gameID} amb el mètode DELETE, i eliminar aquell joc dels nostres favoritos.
************************************************/
function deleteWish(gameID){

    fetch('http://localhost:8030/user/wishlist/' + gameID, {
        method: 'DELETE',
    })
        .then(response => {location.reload()});

}


