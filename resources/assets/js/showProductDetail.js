var prodDetails = document.querySelectorAll('.productDetail');

// select last table row and toggle classes to make the row visible or hidden
prodDetails.forEach(function(button){
    button.addEventListener('click', function(){
        button.text = button.text == 'Toon Varianten' ? 'Verbergen' : 'Toon Varianten';
        var currentParent = this.parentNode.parentNode.parentNode.parentNode;
        var lastRow = currentParent.rows.length - 1;
        currentParent.rows[lastRow].className = currentParent.rows[lastRow].className == 'hideDetail' ? 'showDetail' : 'hideDetail';
    });
});



