// Obtenez l'élément modal
var modal = document.getElementById("loginModal");

// Obtenez le bouton qui ouvre la modal
var btn = document.getElementById("openModalBtn");

// Obtenez l'élément <span> qui ferme la modal
var span = document.getElementsByClassName("close")[0];

// Quand l'utilisateur clique sur le bouton, ouvrez la modale
btn.onclick = function() {
    modal.style.display = "block";
}

// Quand l'utilisateur clique sur <span> (x), fermez la modale
span.onclick = function() {
    modal.style.display = "none";
}

// Quand l'utilisateur clique en dehors de la fenêtre modale, fermez la modale
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
