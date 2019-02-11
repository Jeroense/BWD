var values = [];
var forms = document.getElementsByClassName('orderForm');
var customer = document.querySelector('#customer');

customer.addEventListener('mouseup', removeHidden);

function removeHidden() {
    if(customer.value != 'Selecteer Klant') {
        while (forms[0]) {
            forms[0].classList.remove('orderForm')
        }
    }
}

function AmountPlus(iterator) {
    var orderAmount = document.querySelector('#orderAmount' + CSS.escape(iterator));
    orderAmount.value++;
    checkAmount(parseInt(orderAmount.value), iterator);
}

function AmountMin(iterator) {
    var orderAmount = document.querySelector('#orderAmount' + CSS.escape(iterator));
    if (orderAmount.value > 0) {
        orderAmount.value--;
    }
    checkAmount(parseInt(orderAmount.value), iterator);
}

function checkAmount(total, iterator) {
    if(total <= 0 || isNaN(total)) {
        document.querySelector('#orderButton' + CSS.escape(iterator)).disabled = true;
        document.querySelector('#orderButton' + CSS.escape(iterator)).classList.add('is-normal');
        document.querySelector('#orderButton' + CSS.escape(iterator)).classList.remove('is-danger');
        updateOrderItems(iterator);
    } else {
        document.querySelector('#orderButton' + CSS.escape(iterator)).disabled = false;
        document.querySelector('#orderButton' + CSS.escape(iterator)).classList.remove('is-normal');
        document.querySelector('#orderButton' + CSS.escape(iterator)).classList.add('is-danger');
        document.querySelector('#shoppingCart').classList.remove('is-invisible');
    }
}

function updateOrderItems(iterator) {
    var itemAmount = parseInt(document.querySelector('#orderAmount' + CSS.escape(iterator)).value);
    values[iterator] = itemAmount;
    var shoppingCartTotal = values.reduce(sumItems)
    document.querySelector('#shoppingCartValue').innerHTML = shoppingCartTotal;
    if (shoppingCartTotal === 0){
        document.querySelector('#shoppingCart').classList.add('is-invisible');
    }
}

function sumItems(total, num) {
    return total + num;
}

function ToggleDisabled(mountId) {
    if(document.querySelector('#checkBox' + CSS.escape(mountId)).checked) {
        document.querySelector('#ean' + CSS.escape(mountId)).disabled = false;
        document.querySelector('#orderAmount' + CSS.escape(mountId)).disabled = false;
    } else {
        document.querySelector('#ean' + CSS.escape(mountId)).disabled = true;
        document.querySelector('#orderAmount' + CSS.escape(mountId)).disabled = true;
        document.querySelector('#orderAmount' + CSS.escape(mountId)).value = null;
        checkAmount();
    }
}

function finalizeOrder() {
    var routing = document.querySelector('#orderRoute');
    var returnUrl = routing.getAttribute("returnUrl");
    var destination = routing.getAttribute("href");
    var data = collectData();
    $.ajax({
        type: "POST",
        url: destination,
        dataType: "json",
        data: {
            customer: customer.value,
            orderItems: data
        },
        error: function(message, status, xhr){
            alert(message.responseText);
        }
    }).done(function(variantId) {
        window.location.href = returnUrl + '/' + variantId;
    });
}

function collectData() {
    var orderLine = [];
    var numberOfVariants = document.querySelectorAll('.form').length;
    for(var i = 1; i <= numberOfVariants; i++){
        var items = {};
        var orderKey = document.querySelector('#variantId' + i).value;
        var orderValue = document.querySelector('#orderAmount' + i).value;
        items.variantId = orderKey;
        items.qty = orderValue;
        if (items.qty > 0) {
            orderLine.push({items: items});
        }
    }
    
    return JSON.stringify(orderLine);
}
