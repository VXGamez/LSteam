function compra(gameID, title, salePrice, storeID, dealRating, thumb){
    let params = {
        title: title,
        salePrice: salePrice,
        storeID: storeID,
        dealRating: dealRating,
        thumb: thumb
    };


    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/store/buy/'+gameID;

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



