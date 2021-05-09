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
    } else if(flag == 2){
        form.method = 'DELETE';
        form.action = '/user/wishlist/'+gameID;
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

function probando(missatge){
    console.log(missatge);
}



