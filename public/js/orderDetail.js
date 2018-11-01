function AmountPlus(mountId) {
    if(document.querySelector('#checkBox' + CSS.escape(mountId)).checked) {
        var orderAmount = document.querySelector('#orderAmount' + CSS.escape(mountId));
        orderAmount.value++;
    }
    checkAmount();
}

function AmountMin(mountId) {
    if(document.querySelector('#checkBox' + CSS.escape(mountId)).checked) {
        var orderAmount = document.querySelector('#orderAmount' + CSS.escape(mountId));
        if (orderAmount.value > 0) {
            orderAmount.value--;
        }
    }
    checkAmount();
}

function checkAmount() {
    var numberOfSizes = document.querySelectorAll("input[id^='orderAmount']");
    var count = numberOfSizes.length;
    var total = 0;
    for (i = 1; i <= count; i++){
        total += parseInt(document.querySelector('#orderAmount' + CSS.escape(i)).value);
    }
    if(total === 0) {
        document.querySelector('#bestelButton').disabled = true;
    } else {
        document.querySelector('#bestelButton').disabled = false;
    }
}
