// function getImage(input)
window.getImage = function(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var tshirt = document.querySelector('#uploadedImage');
            tshirt.src = e.target.result;
            tshirt.width = 200;
            tshirt.className = 'showDetail';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
