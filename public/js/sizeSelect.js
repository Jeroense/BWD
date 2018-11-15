var loopCounter = document.querySelectorAll('.ean').length;
var isEan = /^(\d{13})?$/;
var TagsSelected = [];
var validEans = [];
resetCounter();

for (var i = 1; i <= loopCounter; i++) {
    var selectedEan = '#ean' + i;
    document.querySelector(selectedEan).addEventListener('keyup', function(){
        var currentInput = this.id;
        if (isEan.test(document.querySelector('#' + currentInput).value)){
            document.querySelector('#' + currentInput).classList.remove('is-danger');
            document.querySelector('#' + currentInput).classList.add('is-success');
            validEans[parseInt(currentInput.slice(-1)) - 1] = 1;
        } else {
            if (document.querySelector('#' + currentInput).classList.contains('is-success')) {
                document.querySelector('#' + currentInput).classList.remove('is-success');
                document.querySelector('#' + currentInput).classList.add('is-danger');
                validEans[parseInt(currentInput.slice(-1)) - 1] = 0;
            }
        }
        ValidateInput();
    });
}

function ToggleDisabled(id) {
    if (document.querySelector('#checkBox' + CSS.escape(id)).checked) {
        document.querySelector('#ean' + CSS.escape(id)).disabled = false;
        TagsSelected[parseInt(id - 1)] = 1;
    } else {
        document.querySelector('#ean' + CSS.escape(id)).disabled = true;
        document.querySelector('#ean' + CSS.escape(id)).value = null;
        document.querySelector('#ean' + CSS.escape(id)).classList.remove('is-danger');
        validEans[parseInt(id-1)] = 0;
        TagsSelected[parseInt(id - 1)] = 0;
    }
    ValidateInput();
}

function resetCounter() {
    for (var i = 0; i < loopCounter; i++) {
        TagsSelected[i] = 0;
        validEans[i] = 0;
    }
}

function ValidateInput() {
    inputIsValid = false;
    for (var i = 0; i < TagsSelected.length; i++){
        if (TagsSelected[i] > 0) {
            inputIsValid = true;
        }
        if (TagsSelected[i] != validEans[i]){
            inputIsValid = false;
            break;
        }
    }
    inputIsValid ? document.querySelector('#save').disabled = false : document.querySelector('#save').disabled = true;

}
