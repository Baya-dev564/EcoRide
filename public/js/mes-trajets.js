// public/js/mes-trajets.js
// JavaScript pour la gestion des trajets de l'utilisateur EcoRide

document.addEventListener('DOMContentLoaded', function() {
    // J'initialise les fonctionnalités
    initGestionTrajets();
});

/**
 * J'initialise la gestion des trajets
 */
function initGestionTrajets() {
    // Les fonctions d'annulation sont appelées directement depuis le HTML via onclick
    // Pas besoin d'addEventListener supplémentaire
}

/**
 * J'annule un trajet avec confirmation
 * @param {number} trajetId - ID du trajet à annuler
 */
function annulerTrajet(trajetId) {
    // Je demande une confirmation
    if (!confirm('Êtes-vous sûr de vouloir annuler ce trajet ? Cette action est irréversible et tous les passagers seront remboursés.')) {
        return;
    }
    
    // Je trouve le bouton pour afficher le loader
    const button = document.querySelector(`button[data-trajet-id="${trajetId}"]`);
    
    if (button) {
        showLoader(button, 'Annulation...');
    }
    
    // J'envoie la requête d'annulation
    fetch(`/EcoRide/public/api/annuler-trajet/${trajetId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.succes) {
            // Succès
            showSuccessMessage(data.message);
            
            // Je recharge la page après 2 secondes
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            // Erreur
            showErrorMessage('Erreur : ' + data.erreur);
        }
    })
    .catch(error => {
        showErrorMessage('Une erreur technique est survenue. Veuillez réessayer.');
    })
    .finally(() => {
        // Je restaure le bouton
        if (button) {
            hideLoader(button, '<i class="fas fa-times me-2"></i>Annuler');
        }
    });
}

/**
 * J'affiche un message de succès
 * @param {string} message - Message à afficher
 */
function showSuccessMessage(message) {
    // Je supprime les anciens messages
    removeExistingMessages();
    
    // Je crée le message de succès
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show shadow-sm';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // J'insère le message en haut de la page
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Je fais défiler vers le message
    alertDiv.scrollIntoView({ behavior: 'smooth' });
}

/**
 * J'affiche un message d'erreur
 * @param {string} message - Message d'erreur à afficher
 */
function showErrorMessage(message) {
    // Je supprime les anciens messages
     removeExistingMessages();
    
    // Je crée le message d'erreur
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show shadow-sm';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <div>${message}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // J'insère le message en haut de la page
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Je fais défiler vers le message
    alertDiv.scrollIntoView({ behavior: 'smooth' });
}

/**
 * Je supprime les messages existants
 */
function removeExistingMessages() {
    const existingAlerts = document.querySelectorAll('.alert-success, .alert-danger');
    existingAlerts.forEach(alert => alert.remove());
}

/**
 * J'affiche un loader sur un bouton
 * @param {HTMLElement} button - Bouton à modifier
 * @param {string} text - Texte à afficher
 */
function showLoader(button, text) {
    button.disabled = true;
    button.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${text}`;
}

/**
 * Je masque le loader et restaure le bouton
 * @param {HTMLElement} button - Bouton à restaurer
 * @param {string} originalHtml - HTML original du bouton
 */
function hideLoader(button, originalHtml) {
    button.disabled = false;
    button.innerHTML = originalHtml;
}

/**
 * Fonction globale pour l'annulation (appelée depuis le HTML)
 * @param {number} trajetId - ID du trajet
 */
window.annulerTrajet = annulerTrajet;
