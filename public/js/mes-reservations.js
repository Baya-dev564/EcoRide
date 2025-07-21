// public/js/mes-reservations.js
// JavaScript pour la gestion des réservations EcoRide

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des fonctionnalités
    initAnnulationReservations();
    initModalAnnulation();
});

/**
 * Initialise la gestion de l'annulation des réservations
 */
function initAnnulationReservations() {
    
    // Attacher les événements aux boutons d'annulation
    const boutons = document.querySelectorAll('button[data-reservation-id]');
    
    boutons.forEach(bouton => {
        bouton.addEventListener('click', function() {
            const reservationId = this.getAttribute('data-reservation-id');
            ouvrirModalAnnulation(reservationId);
        });
    });
}

/**
 * Ouvre le modal d'annulation
 * @param {number} reservationId - ID de la réservation à annuler
 */
function ouvrirModalAnnulation(reservationId) {
    // Mettre l'ID dans le champ caché du modal
    document.getElementById('reservation_id').value = reservationId;
    
    // Ouvrir le modal
    const modal = new bootstrap.Modal(document.getElementById('modalAnnulation'));
    modal.show();
}

/**
 * Initialise la gestion du formulaire du modal
 */
function initModalAnnulation() {
    const formAnnulation = document.querySelector('#modalAnnulation form');
    
    if (formAnnulation) {
        formAnnulation.addEventListener('submit', function(e) {
            e.preventDefault(); // Empêcher soumission normale
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const reservationId = document.getElementById('reservation_id').value;
            const motif = document.getElementById('motif_annulation').value;
            
            // Afficher loader
            showLoader(submitBtn, 'Annulation...');
            
            // Préparer les données
            const formData = new FormData();
            formData.append('reservation_id', reservationId);
            formData.append('motif_annulation', motif);
            
            // Envoyer la requête SANS attendre de JSON
           fetch('/annuler-reservation', {
    method: 'POST',
    body: formData,
    headers: {
        'X-Requested-With': 'XMLHttpRequest'  // REMETTRE ÇA
    }
})
.then(response => response.json())  // Maintenant ça va marcher
.then(data => {
    if (data.succes) {
        // Fermer le modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalAnnulation'));
        modal.hide();
        
        // Afficher succès et recharger
        showSuccessMessage(data.message);
        setTimeout(() => location.reload(), 2000);
    } else {
        showErrorMessage(data.erreur);
    }
})

            .finally(() => {
                // Restaurer le bouton
                hideLoader(submitBtn, '<i class="fas fa-times me-2"></i>Confirmer l\'annulation');
            });
        });
    }
}


/**
 * Affiche un message de succès
 * @param {string} message - Message à afficher
 */
function showSuccessMessage(message) {
    // Supprimer les anciens messages
    removeExistingMessages();
    
    // Créer le message de succès
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show shadow-sm';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insérer le message en haut de la page
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Faire défiler vers le message
    alertDiv.scrollIntoView({ behavior: 'smooth' });
}

/**
 * Affiche un message d'erreur
 * @param {string} message - Message d'erreur à afficher
 */
function showErrorMessage(message) {
    // Supprimer les anciens messages
    removeExistingMessages();
    
    // Créer le message d'erreur
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show shadow-sm';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insérer le message en haut de la page
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Faire défiler vers le message
    alertDiv.scrollIntoView({ behavior: 'smooth' });
}

/**
 * Supprime les messages existants
 */
function removeExistingMessages() {
    const existingAlerts = document.querySelectorAll('.alert-success, .alert-danger');
    existingAlerts.forEach(alert => alert.remove());
}

/**
 * Affiche un loader sur un bouton
 * @param {HTMLElement} button - Bouton à modifier
 * @param {string} text - Texte à afficher
 */
function showLoader(button, text) {
    button.disabled = true;
    button.innerHTML = `<i class="fas fa-spinner fa-spin me-1"></i>${text}`;
}

/**
 * Masque le loader et restaure le bouton
 * @param {HTMLElement} button - Bouton à restaurer
 * @param {string} originalHtml - HTML original du bouton
 */
function hideLoader(button, originalHtml) {
    button.disabled = false;
    button.innerHTML = originalHtml;
}
