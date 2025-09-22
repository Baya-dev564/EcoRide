// Script pour la page trajets EcoRide avec autocomplete
document.addEventListener('DOMContentLoaded', function() {
    // Je m'assure que tous les trajets sont visibles
    const tripCards = document.querySelectorAll('.trip-card, .card');
    tripCards.forEach(card => {
        card.style.display = 'block';
        card.style.visibility = 'visible';
    });
    
    // Je gère le formulaire de recherche
    const formRecherche = document.getElementById('formRechercheTrajet');
    if (formRecherche) {
        formRecherche.addEventListener('submit', function(e) {
            // Traitement de la recherche
        });
    }

    // ========================================
    // AUTOCOMPLETE POUR LA RECHERCHE DE TRAJETS
    // ========================================
    
    // J'initialise l'autocomplete pour le départ de recherche
    if (document.getElementById('lieu_depart') && typeof PlacesAutocomplete !== 'undefined') {
        new PlacesAutocomplete(document.getElementById('lieu_depart'), {
            onSelect: function(place, input) {
                // Je remplis les champs cachés avec les coordonnées GPS
                if (document.getElementById('search_depart_lat')) {
                    document.getElementById('search_depart_lat').value = place.latitude;
                }
                if (document.getElementById('search_depart_lng')) {
                    document.getElementById('search_depart_lng').value = place.longitude;
                }
                
                console.log('🟢 Point de départ recherche sélectionné:', place.name);
            }
        });
    }
    
    // J'initialise l'autocomplete pour l'arrivée de recherche
    if (document.getElementById('lieu_arrivee') && typeof PlacesAutocomplete !== 'undefined') {
        new PlacesAutocomplete(document.getElementById('lieu_arrivee'), {
            onSelect: function(place, input) {
                // Je remplis les champs cachés avec les coordonnées GPS
                if (document.getElementById('search_arrivee_lat')) {
                    document.getElementById('search_arrivee_lat').value = place.latitude;
                }
                if (document.getElementById('search_arrivee_lng')) {
                    document.getElementById('search_arrivee_lng').value = place.longitude;
                }
                
                console.log('🔴 Point d\'arrivée recherche sélectionné:', place.name);
            }
        });
    }

    // ========================================
    // GESTION DE LA RÉSERVATION (PAGE DÉTAILS)
    // ========================================
    
    const formReservation = document.getElementById('formReservation');
    const btnReserver = document.getElementById('btnReserver');
    const modalElement = document.getElementById('modalConfirmation');
    const btnConfirmerReservation = document.getElementById('btnConfirmerReservation');
    
    // Je vérifie l'existence des éléments (uniquement sur page détails)
    if (!formReservation || !btnReserver || !modalElement || !btnConfirmerReservation) {
        return; // Je sors si les éléments n'existent pas (pas sur la page détails)
    }
    
    // J'initialise la modal Bootstrap
    const modalConfirmation = new bootstrap.Modal(modalElement, {
        keyboard: true,
        backdrop: true
    });
    
    // Je gère la soumission du formulaire
    formReservation.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Je désactive le bouton pour éviter les doubles clics
        btnReserver.disabled = true;
        btnReserver.innerHTML = '<i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Préparation...';
        
        // J'affiche la modal après un court délai
        setTimeout(function() {
            modalConfirmation.show();
            
            // Je réactive le bouton
            btnReserver.disabled = false;
            btnReserver.innerHTML = '<i class="fas fa-check me-2" aria-hidden="true"></i>Réserver ce trajet';
        }, 500);
    });
    
    // Je gère la confirmation finale
    btnConfirmerReservation.addEventListener('click', function() {
        // Je désactive le bouton de confirmation
        btnConfirmerReservation.disabled = true;
        btnConfirmerReservation.innerHTML = '<i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Réservation...';
        
        // Je soumets le formulaire
        formReservation.submit();
    });
    
    // Je réinitialise le bouton si la modal est fermée sans confirmation
    modalElement.addEventListener('hidden.bs.modal', function() {
        btnConfirmerReservation.disabled = false;
        btnConfirmerReservation.innerHTML = '<i class="fas fa-check me-2" aria-hidden="true"></i>Confirmer la réservation';
    });
    
    // Je gère le focus pour l'accessibilité
    modalElement.addEventListener('shown.bs.modal', function() {
        btnConfirmerReservation.focus();
    });
});
