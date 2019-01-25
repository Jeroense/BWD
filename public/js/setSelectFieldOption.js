var selectors = document.querySelectorAll(".select")
var provId = [];
var Poption = [];
for(var i = 0; i < selectors.length; i++ ) {
    var province = selectors[i].dataset.province;
    provId.push(selectors[i].id);
    for(var j = 0; j < selectors[i].length; j++){
        Poption.push(selectors[i][j].value);
        if (selectors[i].dataset.province == selectors[i][j].value) {
            selectors[i].selectedIndex = j;
        }
    }
    document.getElementById("customerForm").click();
}

// Trigger an event to validate the form and enabling the save button
document.addEventListener("DOMContentLoaded", fn, false);

function fn(e) {
    var elem = document.querySelector("#provinceCode");
    var event = new Event("click");
    elem.dispatchEvent(event);
}

