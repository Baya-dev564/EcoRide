// public/js/nouveau-trajet.js
// JavaScript pour la création de nouveaux trajets EcoRide avec autocomplete

document.addEventListener('DOMContentLoaded', function() {
    // J'initialise toutes les fonctionnalités
    initFormValidation();
    initDateValidation();
    initAutocomplete();
});

/**
 * J'initialise l'autocomplete pour les points de rendez-vous
 */
function initAutocomplete() {
    // J'initialise l'autocomplete pour le départ
    if (document.getElementById('lieu_depart') && typeof PlacesAutocomplete !== 'undefined') {
        new PlacesAutocomplete(document.getElementById('lieu_depart'), {
            onSelect: function(place, input) {
                // Je remplis les champs cachés avec les coordonnées GPS
                if (document.getElementById('depart_latitude')) {
                    document.getElementById('depart_latitude').value = place.latitude;
                }
                if (document.getElementById('depart_longitude')) {
                    document.getElementById('depart_longitude').value = place.longitude;
                }
                if (document.getElementById('depart_place_id')) {
                    document.getElementById('depart_place_id').value = place.id;
                }
                
                console.log('🟢 Point de départ création sélectionné:', place.name);
                
                // Je recalcule le prix si les deux points sont sélectionnés
                calculateTripPrice();
            }
        });
    }
    
    // J'initialise l'autocomplete pour l'arrivée
    if (document.getElementById('lieu_arrivee') && typeof PlacesAutocomplete !== 'undefined') {
        new PlacesAutocomplete(document.getElementById('lieu_arrivee'), {
            onSelect: function(place, input) {
                // Je remplis les champs cachés avec les coordonnées GPS
                if (document.getElementById('arrivee_latitude')) {
                    document.getElementById('arrivee_latitude').value = place.latitude;
                }
                if (document.getElementById('arrivee_longitude')) {
                    document.getElementById('arrivee_longitude').value = place.longitude;
                }
                if (document.getElementById('arrivee_place_id')) {
                    document.getElementById('arrivee_place_id').value = place.id;
                }
                
                console.log('🔴 Point d\'arrivée création sélectionné:', place.name);
                
                // Je recalcule le prix si les deux points sont sélectionnés
                calculateTripPrice();
            }
        });
    }
    
    // J'écoute les changements sur le véhicule électrique pour recalculer le prix
    if (document.getElementById('vehicule_electrique')) {
        document.getElementById('vehicule_electrique').addEventListener('change', calculateTripPrice);
    }
}

/**
 * Je calcule le prix du trajet en fonction de la distance GPS
 */
function calculateTripPrice() {
    const departLat = document.getElementById('depart_latitude') ? document.getElementById('depart_latitude').value : '';
    const departLng = document.getElementById('depart_longitude') ? document.getElementById('depart_longitude').value : '';
    const arriveeLat = document.getElementById('arrivee_latitude') ? document.getElementById('arrivee_latitude').value : '';
    const arriveeLng = document.getElementById('arrivee_longitude') ? document.getElementById('arrivee_longitude').value : '';
    
    if (departLat && departLng && arriveeLat && arriveeLng) {
        // Je calcule la distance entre les deux points
        const distance = calculateDistance(departLat, departLng, arriveeLat, arriveeLng);
        
        // Je calcule le prix (exemple : 0.15€ du km)
        const prixBase = Math.max(5, Math.round(distance * 0.15));
        
        // Je vérifie si c'est électrique pour réduction
        const isElectric = document.getElementById('vehicule_electrique') ? document.getElementById('vehicule_electrique').checked : false;
        const prix = isElectric ? Math.round(prixBase * 0.9) : prixBase;
        
        // J'affiche le prix estimé
        if (document.getElementById('prix-estime')) {
            document.getElementById('prix-estime').innerHTML = `
                <i class="fas fa-coins" aria-hidden="true"></i> ${prix}
            `;
        }
        
        console.log(`💰 Prix calculé: ${distance}km = ${prix} crédits`);
    }
}

/**
 * Je calcule la distance entre deux points GPS (formule haversine)
 */
function calculateDistance(lat1, lng1, lat2, lng2) {
    const R = 6371; // Rayon de la Terre en km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) + 
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
              Math.sin(dLng/2) * Math.sin(dLng/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return Math.round(R * c);
}

/**
 * J'initialise la validation du formulaire
 */
function initFormValidation() {
    const form = document.getElementById('formNouveauTrajet');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            }
        });
        
        // Je valide en temps réel
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
 * J'initialise la validation des dates
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
 * Je valide l'ensemble du formulaire
 */
function validateForm() {
    const requiredFields = [
        'lieu_depart', 'lieu_arrivee', 
        'date_depart', 'heure_depart', 'places'
    ];
    
    let isValid = true;
    
    // Je valide chaque champ requis
    requiredFields.forEach(fieldId => {
        if (!validateField(fieldId)) {
            isValid = false;
        }
    });
    
    // Je valide spécifiquement date/heure
    if (!validateDateHeure()) {
        isValid = false;
    }
    
    // Je vérifie que les coordonnées GPS sont présentes
    if (!validateGPSCoordinates()) {
        isValid = false;
    }
    
    return isValid;
}

/**
 * Je valide que les coordonnées GPS sont présentes
 */
function validateGPSCoordinates() {
    const departLat = document.getElementById('depart_latitude') ? document.getElementById('depart_latitude').value : '';
    const departLng = document.getElementById('depart_longitude') ? document.getElementById('depart_longitude').value : '';
    const arriveeLat = document.getElementById('arrivee_latitude') ? document.getElementById('arrivee_latitude').value : '';
    const arriveeLng = document.getElementById('arrivee_longitude') ? document.getElementById('arrivee_longitude').value : '';
    
    if (!departLat || !departLng) {
        showFieldError('lieu_depart', 'Veuillez sélectionner un point de rendez-vous dans la liste.');
        return false;
    }
    
    if (!arriveeLat || !arriveeLng) {
        showFieldError('lieu_arrivee', 'Veuillez sélectionner un point de rendez-vous dans la liste.');
        return false;
    }
    
    return true;
}

/**
 * Je valide un champ spécifique
 */
function validateField(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return true;
    
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';
    
    // Je valide selon le type de champ
    switch (fieldId) {
        case 'lieu_depart':
        case 'lieu_arrivee':
            if (!value) {
                isValid = false;
                errorMessage = 'Ce champ est obligatoire.';
            } else if (value.length < 2) {
                isValid = false;
                errorMessage = 'Le nom de lieu doit contenir au moins 2 caractères.';
            }
            break;
            
        case 'code_postal_depart':
        case 'code_postal_arrivee':
            // Les codes postaux sont maintenant optionnels
            if (value && !/^\d{5}$/.test(value)) {
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
    
    // J'affiche le résultat
    if (isValid) {
        showFieldSuccess(fieldId);
    } else {
        showFieldError(fieldId, errorMessage);
    }
    
    return isValid;
}

/**
 * Je valide la date et l'heure
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
    
    // Je vérifie que la date/heure est dans le futur
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
 * J'affiche une erreur sur un champ
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
 * J'affiche un succès sur un champ
 */
function showFieldSuccess(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
}
