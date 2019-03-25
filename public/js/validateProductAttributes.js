var hasValue = false;
var nrOfSetElements = 0;
var attributeElements = document.querySelectorAll(".productAttribute");

var checkEnabled = function() {
    for(var i = 0; i < attributeElements.length; i++){
        hasValue  = attributeElements[i].value && attributeElements[i].value != " " ? true : false;
        if(hasValue){
            nrOfSetElements++;
        }
    }
    setEnable(nrOfSetElements === attributeElements.length ? true : false);
    nrOfSetElements = 0;
}

function setEnable(value){
    document.querySelector("#saveButton").disabled = !value;
}

Array.from(attributeElements).forEach(function(element) {
    element.addEventListener('keyup', checkEnabled);
});
