// public/js/profil.js
// JavaScript pour la gestion du profil utilisateur EcoRide

document.addEventListener('DOMContentLoaded', function() {
    // J'initialise le formulaire de modification du profil
    initFormModifierProfil();
    
    // J'initialise la gestion des véhicules
    initGestionVehicules();
});

/**
 * J'initialise le formulaire de modification du profil
 */
function initFormModifierProfil() {
    const formModifierProfil = document.getElementById('formModifierProfil');
    
    if (formModifierProfil) {
        formModifierProfil.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Je valide côté client
            if (validateProfilForm()) {
                soumettreModificationProfil();
            }
        });
    }
}

/**
 * Je valide le formulaire de modification du profil
 */
function validateProfilForm() {
    const pseudo = document.getElementById('pseudo').value.trim();
    const email = document.getElementById('email').value.trim();
    
    // Je réinitialise les erreurs
    clearFormErrors();
    
    let hasErrors = false;
    
    // Je valide le pseudo
    if (pseudo.length < 3) {
        showFieldError('pseudo', 'Le pseudo doit contenir au moins 3 caractères.');
        hasErrors = true;
    } else if (pseudo.length > 50) {
        showFieldError('pseudo', 'Le pseudo ne peut pas dépasser 50 caractères.');
        hasErrors = true;
    } else if (!/^[a-zA-Z0-9_-]+$/.test(pseudo)) {
        showFieldError('pseudo', 'Le pseudo ne peut contenir que des lettres, chiffres, tirets et underscores.');
        hasErrors = true;
    }
    
    // Je valide l'email
    if (!isValidEmail(email)) {
        showFieldError('email', 'Veuillez saisir une adresse email valide.');
        hasErrors = true;
    }
    
    return !hasErrors;
}

/**
 * Je soumets la modification du profil via AJAX
 */
function soumettreModificationProfil() {
    const formData = new FormData(document.getElementById('formModifierProfil'));
    const submitBtn = document.querySelector('#formModifierProfil button[type="submit"]');
    
    // J'affiche le loader
    showLoader(submitBtn, 'Enregistrement...');
    
    // Je fais la requête XMLHttpRequest au lieu de fetch()
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/modifier-profil', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            hideLoader(submitBtn, '<i class="fas fa-save me-2"></i>Enregistrer');
            
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    
                    if (data.succes) {
                        // Succès - Je recharge la page pour voir les modifications
                        showSuccessMessage('Profil mis à jour avec succès !');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        // Erreur
                        if (data.erreurs && Array.isArray(data.erreurs)) {
                            showFormErrors(data.erreurs);
                        } else {
                            showGeneralError(data.erreur || 'Une erreur est survenue lors de la modification.');
                        }
                    }
                } catch (e) {
                    showGeneralError('Erreur de communication avec le serveur.');
                }
            } else {
                showGeneralError('Erreur de connexion au serveur.');
            }
        }
    };
    
    xhr.onerror = function() {
        hideLoader(submitBtn, '<i class="fas fa-save me-2"></i>Enregistrer');
        showGeneralError('Une erreur technique est survenue. Veuillez réessayer.');
    };
    
    xhr.send(formData);
}

/**
 * J'initialise la gestion des véhicules
 */
function initGestionVehicules() {
    // Je charge les véhicules existants
    chargerVehicules();
    
    // J'intercepte la soumission du formulaire d'ajout de véhicule
    const formVehicule = document.getElementById('formAjouterVehicule');
    if (formVehicule) {
        formVehicule.addEventListener('submit', function(e) {
            // J'empêche la soumission normale (rechargement de page)
            e.preventDefault();
            e.stopPropagation();
           
            // J'ajoute le véhicule via AJAX
            ajouterVehicule();
        });
    }
}

/**
 * J'ajoute un véhicule via AJAX
 */
function ajouterVehicule() {
    const formData = new FormData(document.getElementById('formAjouterVehicule'));
    const submitBtn = document.querySelector('#formAjouterVehicule button[type="submit"]');
    
    // J'affiche le loader
    showLoader(submitBtn, 'Enregistrement...');
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/api/ajouter-vehicule', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            hideLoader(submitBtn, '<i class="fas fa-save me-2"></i>Enregistrer');
            
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    
                    if (data.succes) {
                        // Je ferme le modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalAjouterVehicule'));
                        if (modal) {
                            modal.hide();
                        }
                        
                        // Je recharge la liste des véhicules
                        chargerVehicules();
                        
                        // Je réinitialise le formulaire
                        document.getElementById('formAjouterVehicule').reset();
                        
                        // J'affiche un message de succès
                        showSuccessMessage('Véhicule ajouté avec succès !');
                        
                    } else {
                        // Erreur
                        if (data.erreurs && Array.isArray(data.erreurs)) {
                            showFormErrors(data.erreurs);
                        } else {
                            showGeneralError(data.erreur || 'Une erreur est survenue lors de l\'ajout du véhicule.');
                        }
                    }
                } catch (e) {
                    showGeneralError('Erreur de communication avec le serveur.');
                }
            } else {
                showGeneralError('Erreur de connexion au serveur.');
            }
        }
    };
    
    xhr.onerror = function() {
        hideLoader(submitBtn, '<i class="fas fa-save me-2"></i>Enregistrer');
        showGeneralError('Une erreur technique est survenue. Veuillez réessayer.');
    };
    
    xhr.send(formData);
}

/**
 * Je charge la liste des véhicules
 */
function chargerVehicules() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '/api/mes-vehicules', true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                const data = JSON.parse(xhr.responseText);
                
                const container = document.getElementById('listeVehicules');
                if (data.succes && data.vehicules && data.vehicules.length > 0) {
                    container.innerHTML = data.vehicules.map(v => `
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>${v.marque} ${v.modele}</strong>
                                ${v.electrique == 1 ? '<span class="badge bg-success ms-2"><i class="fas fa-leaf"></i> Électrique</span>' : ''}
                                <br><small class="text-muted">${v.couleur || 'Couleur non spécifiée'}</small>
                                <br><small class="text-muted">${v.immatriculation || ''}</small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" onclick="supprimerVehicule(${v.id})" title="Supprimer ce véhicule">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = `
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-car fa-2x mb-2"></i>
                            <p>Aucun véhicule enregistré</p>
                            <small>Ajoutez vos véhicules pour proposer des trajets</small>
                        </div>
                    `;
                }
            } catch (e) {
                // Erreur silencieuse pour le chargement des véhicules
            }
        }
    };
    
    xhr.onerror = function() {
        // Erreur silencieuse pour le chargement des véhicules
    };
    
    xhr.send();
}

/**
 * Je supprime un véhicule
 */
function supprimerVehicule(vehiculeId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ?')) {
        return;
    }
    
    const xhr = new XMLHttpRequest();
    xhr.open('DELETE', `/api/supprimer-vehicule/${vehiculeId}`, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Accept', 'application/json');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    
                    if (data.succes) {
                        chargerVehicules();
                        showSuccessMessage(data.message || 'Véhicule supprimé avec succès !');
                    } else {
                        showGeneralError(data.erreur || 'Erreur lors de la suppression du véhicule.');
                    }
                } catch (e) {
                    showGeneralError('Erreur de communication avec le serveur.');
                }
            } else {
                showGeneralError('Erreur de connexion au serveur.');
            }
        }
    };
    
    xhr.onerror = function() {
        showGeneralError('Une erreur technique est survenue.');
    };
    
    xhr.send();
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

function showFormErrors(erreurs) {
    const modalBody = document.querySelector('#modalAjouterVehicule .modal-body') || 
                     document.querySelector('#modalModifierProfil .modal-body');
    
    if (modalBody) {
        // Je crée l'alerte d'erreur
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger';
        
        if (erreurs.length === 1) {
            alertDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>${erreurs[0]}`;
        } else {
            let errorHtml = '<i class="fas fa-exclamation-triangle me-2"></i><ul class="mb-0">';
            erreurs.forEach(erreur => {
                errorHtml += `<li>${erreur}</li>`;
            });
            errorHtml += '</ul>';
            alertDiv.innerHTML = errorHtml;
        }
        
        // J'insère l'alerte en haut du modal
        modalBody.insertBefore(alertDiv, modalBody.firstChild);
    }
}

function showGeneralError(message) {
    showFormErrors([message]);
}

function showSuccessMessage(message) {
    // Je supprime les anciens messages
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Je crée un message de succès global
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Je supprime automatiquement après 3 secondes
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

function showLoader(button, text) {
    if (button) {
        button.disabled = true;
        button.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${text}`;
    }
}

function hideLoader(button, originalHtml) {
    if (button) {
        button.disabled = false;
        button.innerHTML = originalHtml;
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Fonction globale pour la suppression (accessible depuis le HTML)
window.supprimerVehicule = supprimerVehicule;
