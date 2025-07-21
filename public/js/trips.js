// Script pour la page trajets EcoRide
document.addEventListener('DOMContentLoaded', function() {
    // S'assurer que tous les trajets sont visibles
    const tripCards = document.querySelectorAll('.trip-card, .card');
    tripCards.forEach(card => {
        card.style.display = 'block';
        card.style.visibility = 'visible';
    });
    
    // Gestion du formulaire de recherche
    const formRecherche = document.getElementById('formRechercheTrajet');
    if (formRecherche) {
        formRecherche.addEventListener('submit', function(e) {
            // Traitement de la recherche
        });
    }
});
