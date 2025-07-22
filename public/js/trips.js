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

    // ========================================
    // GESTION DE LA RÉSERVATION (PAGE DÉTAILS)
    // ========================================
    
    const formReservation = document.getElementById('formReservation');
    const btnReserver = document.getElementById('btnReserver');
    const modalElement = document.getElementById('modalConfirmation');
    const btnConfirmerReservation = document.getElementById('btnConfirmerReservation');
    
    // Vérification de l'existence des éléments (uniquement sur page détails)
    if (!formReservation || !btnReserver || !modalElement || !btnConfirmerReservation) {
        return; // Sortir si les éléments n'existent pas (pas sur la page détails)
    }
    
    // Initialisation de la modal Bootstrap
    const modalConfirmation = new bootstrap.Modal(modalElement, {
        keyboard: true,
        backdrop: true
    });
    
    // Gestion de la soumission du formulaire
    formReservation.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Désactiver le bouton pour éviter les doubles clics
        btnReserver.disabled = true;
        btnReserver.innerHTML = '<i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Préparation...';
        
        // Afficher la modal après un court délai
        setTimeout(function() {
            modalConfirmation.show();
            
            // Réactiver le bouton
            btnReserver.disabled = false;
            btnReserver.innerHTML = '<i class="fas fa-check me-2" aria-hidden="true"></i>Réserver ce trajet';
        }, 500);
    });
    
    // Gestion de la confirmation finale
    btnConfirmerReservation.addEventListener('click', function() {
        // Désactiver le bouton de confirmation
        btnConfirmerReservation.disabled = true;
        btnConfirmerReservation.innerHTML = '<i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>Réservation...';
        
        // Soumettre le formulaire
        formReservation.submit();
    });
    
    // Réinitialiser le bouton si la modal est fermée sans confirmation
    modalElement.addEventListener('hidden.bs.modal', function() {
        btnConfirmerReservation.disabled = false;
        btnConfirmerReservation.innerHTML = '<i class="fas fa-check me-2" aria-hidden="true"></i>Confirmer la réservation';
    });
    
    // Gestion du focus pour l'accessibilité
    modalElement.addEventListener('shown.bs.modal', function() {
        btnConfirmerReservation.focus();
    });
});
