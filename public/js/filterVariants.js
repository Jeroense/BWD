function filter(action)
{
    var items = document.querySelectorAll('.orderForm');

    if (action == "all") {
        items.forEach(element => {
            element.style.display = "table-row";
        });
    }

    if (action == "published") {
        items.forEach(element => {
            if (element.attributes.value.nodeValue != "published") {
                element.style.display = "none";

            } else{
                element.style.display = "table-row";
            }
        });
    }

    if (action == "unpublished") {
        items.forEach(element => {
            if (element.attributes.value.nodeValue == "published") {
                element.style.display = "none";

            } else {
                element.style.display = "table-row";
            }
        });
    }
}

