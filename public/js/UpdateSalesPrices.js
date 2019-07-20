function persistPrice(id, newPrice, defaultPrice)
{
    if (parseFloat(newPrice) > defaultPrice) {
        thisDoc = document.querySelector('#updatePrice')
        var destination = thisDoc.getAttribute("href");
        var id = id;
        

        $.ajax({
            type: "POST",
            url: destination,
            dataType: "json",
            data: {
                id: id,
                salesPrice: parseFloat(newPrice),
            },
            success: function (returnObj) {
                thisDoc.location = returnObj.returnUrl;
                    window.location.reload(history.back());
                },
            error: function(error, status, exception){
                alert('exception: ', exception);
            }

        });
    }
}
