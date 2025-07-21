// public/js/mes-trajets.js
// JavaScript pour la gestion des trajets de l'utilisateur EcoRide

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des fonctionnalités
    initGestionTrajets();
});

/**
 * Initialise la gestion des trajets
 */
function initGestionTrajets() {
    // Les fonctions d'annulation sont appelées directement depuis le HTML via onclick
    // Pas besoin d'addEventListener supplémentaire
}

/**
 * Annule un trajet avec confirmation
 * @param {number} trajetId - ID du trajet à annuler
 */
function annulerTrajet(trajetId) {
    // Demande de confirmation
    if (!confirm('Êtes-vous sûr de vouloir annuler ce trajet ? Cette action est irréversible et tous les passagers seront remboursés.')) {
        return;
    }
    
    // Trouver le bouton pour afficher le loader
    const button = document.querySelector(`button[data-trajet-id="${trajetId}"]`);
    
    if (button) {
        showLoader(button, 'Annulation...');
    }
    
    // Envoi de la requête d'annulation
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
            
            // Recharger la page après 2 secondes
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
        // Restaurer le bouton
        if (button) {
            hideLoader(button, '<i class="fas fa-times me-2"></i>Annuler');
        }
    });
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
    button.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${text}`;
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

/**
 * Fonction globale pour l'annulation (appelée depuis le HTML)
 * @param {number} trajetId - ID du trajet
 */
window.annulerTrajet = annulerTrajet;
