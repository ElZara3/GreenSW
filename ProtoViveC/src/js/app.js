function DisplayMenu() {
    var menu = document.querySelector('.CardMenu');
    
    if (menu) {
        var display = window.getComputedStyle(menu).display;

        if (display === "none") 
            menu.style.display = "block";
        else 
            menu.style.display = "none";
    }
}