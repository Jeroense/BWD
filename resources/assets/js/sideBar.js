const accordions = document.getElementsByClassName('has-submenu')
const SlideOutButton = document.getElementById('slideout-button')

// function setSubmenuStyles (submenu, maxHeight, margins) {
//     submenu.style.maxHeight = maxHeight
//     submenu.style.marginTop = margins
//     submenu.style.marginBottom = margins
// }

SlideOutButton.onclick = function() {
    this.classList.toggle('is-active');
    document.getElementById('side-menu').classList.toggle('is-active');
}

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
