document
    .querySelector("#customerForm")
    .addEventListener("keyup", validate, false);
document
    .querySelector("select")
    .addEventListener("click", validate, false);
var pristine = document.querySelectorAll("input[data-pristine], select");

function validate(e) {
    var formValidated = 1;
    var inputFieldIsValid = 0;
    if (e.target !== e.currentTarget || e.target.id == "provinceCode") {
        for (var i = 0; i < pristine.length; i++) {
            if (pristine[i].attributes[1].value != true || e.target.id == pristine[i].id) {
                var regExpr = "";
                switch (pristine[i].id) {
                    case "firstName":
                    case "lastName":
                    case "street":
                    case "city":
                    case "houseNr":
                        regExpr = /^([a-zA-Z0-9_-\s]){1,100}$/;
                        inputFieldIsValid = 1;
                        if (!matchValue(pristine[i].value, regExpr)) {
                            formValidated = 0;
                            inputFieldIsValid = 0;
                        }
                        if (e.target.id == pristine[i].id) {
                            addVisuals(e.target, inputFieldIsValid);
                        }
                        break;
                    case "provinceCode":
                        regExpr = /^[a-zA-Z]{2}$/;
                        inputFieldIsValid = 1;
                        if (!matchValue(pristine[i].value, regExpr)) {
                            formValidated = 0;
                            inputFieldIsValid = 0;
                        }
                        if (e.target.id == pristine[i].id) {
                            addVisuals(e.target, inputFieldIsValid);
                        }
                        break;
                    case "postalCode":
                        regExpr = /^[1-9][0-9]{3}\s?([A-RT-Za-rt-z][A-Za-z]|[sS][BCbcE-Re-rT-Zt-z])$/;
                        inputFieldIsValid = 1;
                        if (!matchValue(pristine[i].value, regExpr)) {
                            formValidated = 0;
                            inputFieldIsValid = 0;
                        }
                        if (e.target.id == pristine[i].id) {
                            addVisuals(e.target, inputFieldIsValid);
                        }
                        break;
                    case "phone":
                        regExpr = /^[0-9]{10}$/;
                        inputFieldIsValid = 1;
                        if (!matchValue(pristine[i].value.replace(/\D/g, ""), regExpr)) {
                            formValidated = 0;
                            inputFieldIsValid = 0;
                        }
                        if (e.target.id == pristine[i].id) {
                            addVisuals(e.target, inputFieldIsValid);
                        }
                        break;
                    case "email":
                        regExpr = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        inputFieldIsValid = 1;
                        if (!matchValue(pristine[i].value, regExpr)) {
                            formValidated = 0;
                            inputFieldIsValid = 0;
                        }
                        if (e.target.id === pristine[i].id) {
                            addVisuals(e.target, inputFieldIsValid);
                        }
                        break;
                }
                e.target.id == 'save' ? '' : e.target.attributes[1].value = "false";
            } else {
                formValidated = 0;
            }
        }
        formValidated == 1 ? document.querySelector("#save").disabled = false : document.querySelector("#save").disabled = true
    }
    e.stopPropagation();
}

function matchValue(target, expr) {
    if(expr.test(target) == true){
        return 1;
    }
    return 0;
}

function addVisuals(target, formValidated) {
    if (formValidated == 1) {
        target.classList.add("inputValid");
        target.classList.remove("inputError");
    } else {
        target.classList.add("inputError");
        target.classList.remove("inputValid");
    }
    return
}

