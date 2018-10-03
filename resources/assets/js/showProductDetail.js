var prodDetails = document.querySelectorAll('.productDetail');

// select last table row and toggle classes to make the row visible or hidden
prodDetails.forEach(function(button){
    button.addEventListener('click', function(){
        button.text = button.text == 'Details' ? 'Verberg' : 'Details';
        var currentParent = this.parentNode.parentNode.parentNode.parentNode;
        var lastRow = currentParent.rows.length - 1;
        currentParent.rows[lastRow].className = currentParent.rows[lastRow].className == 'hideDetail' ? 'showDetail' : 'hideDetail';
    });
});


// prodDetails.forEach(function(button){  // select last table cell !!
//     button.addEventListener('click', function(){
//         button.text = button.text == 'Details' ? 'Verberg' : 'Details';
//         var currentParent = this.parentNode.parentNode.parentNode.parentNode;
//         var lastRow = currentParent.rows.length - 1;
//         var lastCellIndex = currentParent.rows[lastRow].cells.length-1;
//         targetCell = currentParent.rows[lastRow].cells[lastCellIndex];
//         targetCell.className = targetCell.className == 'hideDetail' ? 'showDetail' : 'hideDetail';
//     });
// });


