document
    .querySelector("#customerForm")
    .addEventListener("keyup", validate, false);
document
    .querySelector("select")
    .addEventListener("click", validate, false);
document
    .querySelector("#hasDeliveryAddress")
    .addEventListener("click", selectPristine, false);
    // .addEventListener("click", validate, false);

var pristine = document.querySelectorAll(".address");

function validate(e) {
    var formValidated = 1;
    var inputFieldIsValid = 0;
    if (e.target !== e.currentTarget || e.target.id == "provinceCode") {
        for (var i = 0; i < pristine.length; i++) {
            if (pristine[i].attributes[1].value != true || e.target.id == pristine[i].id) {
                var regExpr = "";
                switch (pristine[i].id) {
                    case "firstName":
                    case "post_firstName":
                    case "lastName":
                    case "post_lastName":
                    case "street":
                    case "post_street":
                    case "city":
                    case "post_city":
                    case "houseNr":
                    case "post_houseNr":
                        regExpr = /^(['a-zA-Z0-9_-\s]){3,100}$/;
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
                    case "post_provinceCode":
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
                    case "post_postalCode":
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
                    case "post_phone":
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
                    case "post_email":
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

function selectPristine(e) {
    var deliverySection = document.querySelector("#deliveryAddress");
    var selectDeliveryAddress = document.querySelector("#deliveryAddressSelected");
    if (e.target.checked == true) {
        deliverySection.classList.remove("hideDetail");
        deliverySection.classList.add("showBlock");
        selectDeliveryAddress.value = "true";
        pristine = document.querySelectorAll(".address, .delivery");
        document.querySelector("#post_firstName").focus();
        document.querySelector("#save").disabled = true;
    } else {
        deliverySection.classList.remove("showBlock");
        deliverySection.classList.add("hideDetail");
        selectDeliveryAddress.value = "false";
        pristine = document.querySelectorAll(".address");
    }
}


