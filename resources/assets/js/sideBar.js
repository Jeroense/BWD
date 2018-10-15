const accordions = document.getElementsByClassName('has-submenu')

document.getElementById('slideout-button').addEventListener('click', function(e){
    this.classList.toggle('is-active');
    document.getElementById('side-menu').classList.toggle('is-active');
});


for (var i = 0; i < accordions.length; i++) {
    if (accordions[i].classList.contains('is-active')) {
        const submenu = accordions[i].nextElementSibling;
        submenu.style.maxHeight = submenu.scrollHeight + "px"
        submenu.style.marginTop = "0.75em"
        submenu.style.marginBottom = "0.75em"
    }

    accordions[i].onclick = function () {
        this.classList.toggle('is-active');

        const submenu = this.nextElementSibling;
        if (submenu.style.maxHeight) {
            submenu.style.maxHeight = null
            submenu.style.marginTop = null
            submenu.style.marginBottom = null
        } else {
            submenu.style.maxHeight = submenu.scrollHeight + "px"
            submenu.style.marginTop = "0.75em"
            submenu.style.marginBottom = "0.75em"
        }
    }
}

/*jQuery time*/
$(document).ready(function(){
	$("#accordian h3").click(function(){
		//slide up all the link lists
		$("#accordian ul ul").slideUp();
		//slide down the link list below the h3 clicked - only if its closed
		if(!$(this).next().is(":visible"))
		{
			$(this).next().slideDown();
		}
	})
})
