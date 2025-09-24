// public/js/reserver.js
// JavaScript pour la réservation de trajets 

document.addEventListener('DOMContentLoaded', function() {
    // J'initialise le formulaire de réservation
    initFormReservation();
    initRecapitulatif();
});

/**
 * J'initialise le formulaire de réservation
 */
function initFormReservation() {
    const formReservation = document.getElementById('formReservation');
    
    if (formReservation) {
        formReservation.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Je valide côté client
            if (validateReservationForm()) {
                confirmerReservation();
            }
        });
    }
}

/**
 * J'initialise le récapitulatif dynamique
 */
function initRecapitulatif() {
    const nbPlacesSelect = document.getElementById('nb_places');
    
    if (nbPlacesSelect) {
        nbPlacesSelect.addEventListener('change', function() {
            mettreAJourRecapitulatif();
        });
        
        // Je fais la mise à jour initiale
        mettreAJourRecapitulatif();
    }
}

/**
 * Je mets à jour le récapitulatif des coûts
 */
function mettreAJourRecapitulatif() {
    const nbPlacesSelect = document.getElementById('nb_places');
    const recapPlaces = document.getElementById('recap-places');
    const recapTotal = document.getElementById('recap-total');
    const alertCredits = document.getElementById('alert-credits');
    
    const nbPlaces = parseInt(nbPlacesSelect.value);
    const prixParPlace = parseInt(document.getElementById('prixParPlace').value);
    const creditsUtilisateur = parseInt(document.getElementById('creditsUtilisateur').value);
    const total = nbPlaces * prixParPlace;
    
    // Je mets à jour l'affichage
    recapPlaces.textContent = nbPlaces;
    recapTotal.textContent = total + ' crédits';
    
    // Je vérifie les crédits
    if (total > creditsUtilisateur) {
        alertCredits.className = 'alert alert-danger';
        alertCredits.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Vous n\'avez pas assez de crédits pour cette réservation !';
    } else {
        alertCredits.className = 'alert alert-info';
        alertCredits.innerHTML = '<i class="fas fa-info-circle me-2"></i>Vous avez <strong>' + creditsUtilisateur + ' crédits</strong> disponibles';
    }
}

/**
 * Je valide le formulaire de réservation
 */
function validateReservationForm() {
    const nbPlaces = parseInt(document.getElementById('nb_places').value);
    const prixParPlace = parseInt(document.getElementById('prixParPlace').value);
    const creditsUtilisateur = parseInt(document.getElementById('creditsUtilisateur').value);
    const conditions = document.getElementById('conditions').checked;
    const total = nbPlaces * prixParPlace;
    
    // Je réinitialise les erreurs
    clearFormErrors();
    
    let hasErrors = false;
    
    // Je vérifie les crédits
    if (total > creditsUtilisateur) {
        showGeneralError('Vous n\'avez pas assez de crédits pour cette réservation.');
        hasErrors = true;
    }
    
    // Je vérifie les conditions
    if (!conditions) {
        showFieldError('conditions', 'Vous devez accepter les conditions de réservation.');
        hasErrors = true;
    }
    
    return !hasErrors;
}

/**
 * Je confirme et traite la réservation
 */
function confirmerReservation() {
    const nbPlaces = parseInt(document.getElementById('nb_places').value);
    const prixParPlace = parseInt(document.getElementById('prixParPlace').value);
    const total = nbPlaces * prixParPlace;
    
    // Je demande une confirmation
    if (!confirm(`Confirmer la réservation de ${nbPlaces} place${nbPlaces > 1 ? 's' : ''} pour ${total} crédits ?`)) {
        return;
    }
    
    // J'affiche le loader
    const submitBtn = document.querySelector('#formReservation button[type="submit"]');
    showLoader(submitBtn, 'Réservation en cours...');
    
    // Je prépare les données
    const formData = new FormData(document.getElementById('formReservation'));
    formData.append('credits_utilises', total);
    
    // J'envoie en AJAX
    fetch('/EcoRide/public/api/reserver', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.succes) {
            // Succès
            showSuccessMessage(data.message);
            
            // Je redirige après 2 secondes
            setTimeout(() => {
                window.location.href = '/EcoRide/public/mes-reservations';
            }, 2000);
        } else {
            // Erreur
            showGeneralError(data.erreur || 'Une erreur est survenue lors de la réservation.');
        }
    })
    .catch(error => {
        showGeneralError('Une erreur technique est survenue. Veuillez réessayer.');
    })
    .finally(() => {
        // Je restaure le bouton
        hideLoader(submitBtn, '<i class="fas fa-check me-2"></i>Confirmer la réservation');
    });
}

/**
 * Fonctions utilitaires pour la gestion des erreurs et messages
 */
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.classList.add('is-invalid');
        
        // Je crée ou mets à jour le message d'erreur
        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }
        feedback.textContent = message;
        feedback.style.display = 'block';
    }
}

function clearFormErrors() {
    // Je supprime toutes les classes d'erreur
    document.querySelectorAll('.is-invalid').forEach(field => {
        field.classList.remove('is-invalid');
    });
    
    // Je masque tous les messages d'erreur
    document.querySelectorAll('.invalid-feedback').forEach(feedback => {
        feedback.style.display = 'none';
    });
    
    // Je supprime les messages d'erreur généraux
    const existingAlerts = document.querySelectorAll('.alert-danger');
    existingAlerts.forEach(alert => alert.remove());
}

function showGeneralError(message) {
    const cardBody = document.querySelector('#formReservation').parentNode;
    
    // Je supprime les anciennes alertes d'erreur
    const existingAlerts = cardBody.querySelectorAll('.alert-danger');
    existingAlerts.forEach(alert => alert.remove());
    
    // Je crée l'alerte d'erreur
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger';
    alertDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${message}`;
    
    // J'insère l'alerte avant le formulaire
    cardBody.insertBefore(alertDiv, cardBody.firstChild);
    
    // Je fais défiler vers l'alerte
    alertDiv.scrollIntoView({ behavior: 'smooth' });
}

function showSuccessMessage(message) {
    const cardBody = document.querySelector('#formReservation').parentNode;
    
    // Je supprime les anciennes alertes
    const existingAlerts = cardBody.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Je crée l'alerte de succès
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success';
    alertDiv.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
    
    // J'insère l'alerte avant le formulaire
    cardBody.insertBefore(alertDiv, cardBody.firstChild);
    
    // Je fais défiler vers l'alerte
    alertDiv.scrollIntoView({ behavior: 'smooth' });
}

function showLoader(button, text) {
    button.disabled = true;
    button.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${text}`;
}

function hideLoader(button, originalHtml) {
    button.disabled = false;
    button.innerHTML = originalHtml;
}
