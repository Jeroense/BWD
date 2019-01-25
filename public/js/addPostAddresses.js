var thisDocument = document
    .querySelector("#createPostAddress")
    .addEventListener("click", function (createPostAddress){
        createPostAddress.preventDefault();
        var btn = document.createElement("BUTTON");
        var location = document.querySelector("#addedPostAddress");
        location.appendChild(btn);
    }) ;

