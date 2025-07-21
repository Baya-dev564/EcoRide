// public/js/reserver.js
// JavaScript pour la réservation de trajets 

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation du formulaire de réservation
    initFormReservation();
    initRecapitulatif();
});

/**
 * Initialise le formulaire de réservation
 */
function initFormReservation() {
    const formReservation = document.getElementById('formReservation');
    
    if (formReservation) {
        formReservation.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validation côté client
            if (validateReservationForm()) {
                confirmerReservation();
            }
        });
    }
}

/**
 * Initialise le récapitulatif dynamique
 */
function initRecapitulatif() {
    const nbPlacesSelect = document.getElementById('nb_places');
    
    if (nbPlacesSelect) {
        nbPlacesSelect.addEventListener('change', function() {
            mettreAJourRecapitulatif();
        });
        
        // Mise à jour initiale
        mettreAJourRecapitulatif();
    }
}

/**
 * Met à jour le récapitulatif des coûts
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
    
    // Mise à jour de l'affichage
    recapPlaces.textContent = nbPlaces;
    recapTotal.textContent = total + ' crédits';
    
    // Vérification des crédits
    if (total > creditsUtilisateur) {
        alertCredits.className = 'alert alert-danger';
        alertCredits.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Vous n\'avez pas assez de crédits pour cette réservation !';
    } else {
        alertCredits.className = 'alert alert-info';
        alertCredits.innerHTML = '<i class="fas fa-info-circle me-2"></i>Vous avez <strong>' + creditsUtilisateur + ' crédits</strong> disponibles';
    }
}

/**
 * Valide le formulaire de réservation
 */
function validateReservationForm() {
    const nbPlaces = parseInt(document.getElementById('nb_places').value);
    const prixParPlace = parseInt(document.getElementById('prixParPlace').value);
    const creditsUtilisateur = parseInt(document.getElementById('creditsUtilisateur').value);
    const conditions = document.getElementById('conditions').checked;
    const total = nbPlaces * prixParPlace;
    
    // Réinitialiser les erreurs
    clearFormErrors();
    
    let hasErrors = false;
    
    // Vérification des crédits
    if (total > creditsUtilisateur) {
        showGeneralError('Vous n\'avez pas assez de crédits pour cette réservation.');
        hasErrors = true;
    }
    
    // Vérification des conditions
    if (!conditions) {
        showFieldError('conditions', 'Vous devez accepter les conditions de réservation.');
        hasErrors = true;
    }
    
    return !hasErrors;
}

/**
 * Confirme et traite la réservation
 */
function confirmerReservation() {
    const nbPlaces = parseInt(document.getElementById('nb_places').value);
    const prixParPlace = parseInt(document.getElementById('prixParPlace').value);
    const total = nbPlaces * prixParPlace;
    
    // Demande de confirmation
    if (!confirm(`Confirmer la réservation de ${nbPlaces} place${nbPlaces > 1 ? 's' : ''} pour ${total} crédits ?`)) {
        return;
    }
    
    // Afficher le loader
    const submitBtn = document.querySelector('#formReservation button[type="submit"]');
    showLoader(submitBtn, 'Réservation en cours...');
    
    // Préparer les données
    const formData = new FormData(document.getElementById('formReservation'));
    formData.append('credits_utilises', total);
    
    // Envoi AJAX
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
            
            // Redirection après 2 secondes
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
        // Restaurer le bouton
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
        
        // Créer ou mettre à jour le message d'erreur
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
    // Supprimer toutes les classes d'erreur
    document.querySelectorAll('.is-invalid').forEach(field => {
        field.classList.remove('is-invalid');
    });
    
    // Masquer tous les messages d'erreur
    document.querySelectorAll('.invalid-feedback').forEach(feedback => {
        feedback.style.display = 'none';
    });
    
    // Supprimer les messages d'erreur généraux
    const existingAlerts = document.querySelectorAll('.alert-danger');
    existingAlerts.forEach(alert => alert.remove());
}

function showGeneralError(message) {
    const cardBody = document.querySelector('#formReservation').parentNode;
    
    // Supprimer les anciennes alertes d'erreur
    const existingAlerts = cardBody.querySelectorAll('.alert-danger');
    existingAlerts.forEach(alert => alert.remove());
    
    // Créer l'alerte d'erreur
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger';
    alertDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${message}`;
    
    // Insérer l'alerte avant le formulaire
    cardBody.insertBefore(alertDiv, cardBody.firstChild);
    
    // Faire défiler vers l'alerte
    alertDiv.scrollIntoView({ behavior: 'smooth' });
}

function showSuccessMessage(message) {
    const cardBody = document.querySelector('#formReservation').parentNode;
    
    // Supprimer les anciennes alertes
    const existingAlerts = cardBody.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Créer l'alerte de succès
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success';
    alertDiv.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
    
    // Insérer l'alerte avant le formulaire
    cardBody.insertBefore(alertDiv, cardBody.firstChild);
    
    // Faire défiler vers l'alerte
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
