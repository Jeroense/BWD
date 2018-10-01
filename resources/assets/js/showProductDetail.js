var prodDetails = document.querySelectorAll('.productDetail');

prodDetails.forEach(function(button){
    button.addEventListener('click', function(){
        button.text = button.text == 'Details' ? 'Verberg' : 'Details';
        var currentParent = this.parentNode.parentNode.parentNode.parentNode;
        var lastRow = currentParent.rows.length - 1;
        var lastCellIndex = currentParent.rows[lastRow].cells.length-1;
        targetCell = currentParent.rows[lastRow].cells[lastCellIndex];
        targetCell.className = targetCell.className == 'hideDetail' ? 'showDetail' : 'hideDetail';
    });
});


