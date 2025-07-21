// public/js/nouveau-trajet.js
// JavaScript pour la création de nouveaux trajets EcoRide

document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des fonctionnalités
    initFormValidation();
    initDateValidation();
});

/**
 * Initialise la validation du formulaire
 */
function initFormValidation() {
    const form = document.getElementById('formNouveauTrajet');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            }
        });
        
        // Validation en temps réel
        const fields = ['lieu_depart', 'lieu_arrivee', 'code_postal_depart', 'code_postal_arrivee'];
        fields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('blur', function() {
                    validateField(fieldId);
                });
            }
        });
    }
}

/**
 * Initialise la validation des dates
 */
function initDateValidation() {
    const dateField = document.getElementById('date_depart');
    const heureField = document.getElementById('heure_depart');
    
    if (dateField) {
        dateField.addEventListener('change', function() {
            validateDateHeure();
        });
    }
    
    if (heureField) {
        heureField.addEventListener('change', function() {
            validateDateHeure();
        });
    }
}

/**
 * Valide l'ensemble du formulaire
 */
function validateForm() {
    const requiredFields = [
        'lieu_depart', 'lieu_arrivee', 
        'code_postal_depart', 'code_postal_arrivee',
        'date_depart', 'heure_depart', 'places'
    ];
    
    let isValid = true;
    
    // Validation de chaque champ requis
    requiredFields.forEach(fieldId => {
        if (!validateField(fieldId)) {
            isValid = false;
        }
    });
    
    // Validation spécifique date/heure
    if (!validateDateHeure()) {
        isValid = false;
    }
    
    return isValid;
}

/**
 * Valide un champ spécifique
 */
function validateField(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return true;
    
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Validation selon le type de champ
    switch (fieldId) {
        case 'lieu_depart':
        case 'lieu_arrivee':
            if (!value) {
                isValid = false;
                errorMessage = 'Ce champ est obligatoire.';
            } else if (value.length < 2) {
                isValid = false;
                errorMessage = 'Le nom de ville doit contenir au moins 2 caractères.';
            }
            break;
            
        case 'code_postal_depart':
        case 'code_postal_arrivee':
            if (!value) {
                isValid = false;
                errorMessage = 'Le code postal est obligatoire.';
            } else if (!/^\d{5}$/.test(value)) {
                isValid = false;
                errorMessage = 'Le code postal doit contenir exactement 5 chiffres.';
            }
            break;
            
        case 'places':
            const places = parseInt(value);
            if (!value || places < 1 || places > 8) {
                isValid = false;
                errorMessage = 'Veuillez sélectionner entre 1 et 8 places.';
            }
            break;
    }
    
    // Affichage du résultat
    if (isValid) {
        showFieldSuccess(fieldId);
    } else {
        showFieldError(fieldId, errorMessage);
    }
    
    return isValid;
}

/**
 * Valide la date et l'heure
 */
function validateDateHeure() {
    const dateField = document.getElementById('date_depart');
    const heureField = document.getElementById('heure_depart');
    
    if (!dateField || !heureField) return true;
    
    const dateValue = dateField.value;
    const heureValue = heureField.value;
    
    if (!dateValue || !heureValue) {
        return false; // Sera géré par la validation des champs individuels
    }
    
    // Vérification que la date/heure est dans le futur
    const dateHeure = new Date(dateValue + 'T' + heureValue);
    const maintenant = new Date();
    
    if (dateHeure <= maintenant) {
        showFieldError('date_depart', 'La date et l\'heure doivent être dans le futur.');
        showFieldError('heure_depart', 'La date et l\'heure doivent être dans le futur.');
        return false;
    }
    
    showFieldSuccess('date_depart');
    showFieldSuccess('heure_depart');
    return true;
}

/**
 * Affiche une erreur sur un champ
 */
function showFieldError(fieldId, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.remove('is-valid');
    field.classList.add('is-invalid');
    
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.textContent = message;
    }
}

/**
 * Affiche un succès sur un champ
 */
function showFieldSuccess(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
}
