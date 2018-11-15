// function AmountPlus(mountId) {
//     if(document.querySelector('#checkBox' + CSS.escape(mountId)).checked) {
//         var orderAmount = document.querySelector('#orderAmount' + CSS.escape(mountId));
//         orderAmount.value++;
//     }
//     checkAmount();
// }

// function AmountMin(mountId) {
//     if(document.querySelector('#checkBox' + CSS.escape(mountId)).checked) {
//         var orderAmount = document.querySelector('#orderAmount' + CSS.escape(mountId));
//         if (orderAmount.value > 0) {
//             orderAmount.value--;
//         }
//     }
//     checkAmount();
// }

// function checkAmount() {
//     var numberOfSizes = document.querySelectorAll("input[id^='orderAmount']");
//     var count = numberOfSizes.length;
//     var total = 0;
//     for (i = 1; i <= count; i++){
//         if(parseInt(document.querySelector('#orderAmount' + CSS.escape(i)).value) > 0){
//             total += parseInt(document.querySelector('#orderAmount' + CSS.escape(i)).value);
//         }
//     }

//     if(total === 0 || isNaN(total)) {
//         document.querySelector('#bestelButton').disabled = true;
//     } else {
//         document.querySelector('#bestelButton').disabled = false;
//     }
// }

// function ToggleDisabled(mountId) {
//     if(document.querySelector('#checkBox' + CSS.escape(mountId)).checked) {
//         document.querySelector('#ean' + CSS.escape(mountId)).disabled = false;
//         document.querySelector('#orderAmount' + CSS.escape(mountId)).disabled = false;
//     } else {
//         document.querySelector('#ean' + CSS.escape(mountId)).disabled = true;
//         document.querySelector('#orderAmount' + CSS.escape(mountId)).disabled = true;
//         document.querySelector('#orderAmount' + CSS.escape(mountId)).value = null;
//         checkAmount();
//     }
// }
