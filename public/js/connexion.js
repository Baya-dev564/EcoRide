// public/js/connexion.js
// Validation côté client pour le formulaire de connexion EcoRide avec AJAX

document.addEventListener('DOMContentLoaded', function() {
    // Récupération des éléments du formulaire
    const form = document.getElementById('formConnexion');
    const email = document.getElementById('email');
    const motDePasse = document.getElementById('mot_de_passe');
    const togglePassword = document.getElementById('togglePassword');
    
    // Vérification que tous les éléments existent
    if (!form || !email || !motDePasse) {
        return;
    }
    
    // IMPORTANT : Désactiver la validation HTML5 native
    form.setAttribute('novalidate', 'true');
    
    /**
     * Gestion de l'affichage/masquage du mot de passe avec animation
     */
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = motDePasse.getAttribute('type') === 'password' ? 'text' : 'password';
            motDePasse.setAttribute('type', type);
            
            // Animation de l'icône
            const icon = this.querySelector('i');
            icon.classList.add('animate-pulse');
            
            if (type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
            
            setTimeout(() => icon.classList.remove('animate-pulse'), 300);
            
            // Maintient le focus sur le champ mot de passe
            motDePasse.focus();
        });
    }
    
    /**
     * Validation en temps réel de l'email avec animations
     */
    email.addEventListener('input', function() {
        clearFieldError('email');
        
        const emailValue = this.value.trim();
        if (emailValue.length > 0) {
            if (isValidEmail(emailValue)) {
                showFieldSuccess('email');
            } else {
                showFieldError('email', 'Format d\'email invalide.');
            }
        }
    });
    
    email.addEventListener('blur', function() {
        validateEmail();
    });
    
    /**
     * Validation en temps réel du mot de passe avec animations
     */
    motDePasse.addEventListener('input', function() {
        clearFieldError('mot_de_passe');
        
        if (this.value.length > 0) {
            showFieldSuccess('mot_de_passe');
        }
    });
    
    motDePasse.addEventListener('blur', function() {
        validatePassword();
    });
    
    /**
     * Gestion de la touche Entrée pour soumission rapide
     */
    [email, motDePasse].forEach(input => {
        input.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });
    });
    
    /**
     * CRUCIAL : Intercepter la soumission du formulaire pour AJAX
     */
    form.addEventListener('submit', function(event) {
        // EMPÊCHER la soumission normale (rechargement de page)
        event.preventDefault();
        event.stopPropagation();
        
        // Validation complète du formulaire
        const isValid = validateForm();
        
        if (isValid) {
            // Animation de succès
            animateFormSuccess();
            // Soumission AJAX
            submitLoginForm();
        } else {
            // Animation d'erreur
            animateFormError();
            
            // Focus sur le premier champ en erreur
            const firstError = this.querySelector('.is-invalid');
            if (firstError) {
                firstError.focus();
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    /**
     * Soumission AJAX du formulaire de connexion EcoRide
     * CORRECTION : Headers AJAX appropriés pour éviter l'erreur "Requête invalide"
     */
    function submitLoginForm() {
        // Affichage du loader pendant la connexion
        showLoader();
        
        // Récupération des données du formulaire
        const formData = new FormData();
        formData.append('email', email.value.trim());
        formData.append('mot_de_passe', motDePasse.value);
        
        // Requête AJAX vers l'API de connexion EcoRide avec headers corrigés
        fetch('/api/connexion', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',  // Header AJAX requis par le serveur
                'Accept': 'application/json'           // Accepter les réponses JSON
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            hideLoader();
            
            if (data.succes) {
                // Connexion réussie - Animation de succès
                showSuccessMessage(data.message);
                animateFormSuccess();
                
                // Redirection vers l'accueil après 1.5 secondes
                setTimeout(() => {
                    window.location.href = data.redirect || '/';
                }, 1500);
                
            } else {
                // Erreur de connexion - Affichage du message d'erreur
                showErrorMessage(data.erreur);
                animateFormError();
            }
        })
        .catch(error => {
            hideLoader();
            showErrorMessage('Une erreur technique est survenue. Veuillez réessayer.');
            animateFormError();
        });
    }
    
    /**
     * Validation en temps réel de tous les champs requis
     */
    const requiredInputs = form.querySelectorAll('input[required]');
    requiredInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Animation lors de la saisie
            this.classList.add('animate-pulse-success');
            setTimeout(() => this.classList.remove('animate-pulse-success'), 300);
            
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    });
    
    /**
     * Fonction de validation de l'email avec animation
     */
    function validateEmail() {
        const value = email.value.trim();
        let isValid = true;
        let message = '';
        
        if (value.length === 0) {
            message = 'L\'email est obligatoire pour vous connecter.';
            isValid = false;
        } else if (!isValidEmail(value)) {
            message = 'Veuillez saisir une adresse email valide.';
            isValid = false;
        }
        
        if (!isValid) {
            showFieldError('email', message);
        } else {
            showFieldSuccess('email');
        }
        
        return isValid;
    }
    
    /**
     * Fonction de validation du mot de passe avec animation
     */
    function validatePassword() {
        const value = motDePasse.value;
        let isValid = true;
        let message = '';
        
        if (value.length === 0) {
            message = 'Le mot de passe est obligatoire pour vous connecter.';
            isValid = false;
        }
        
        if (!isValid) {
            showFieldError('mot_de_passe', message);
        } else {
            showFieldSuccess('mot_de_passe');
        }
        
        return isValid;
    }
    
    /**
     * Fonction de validation complète du formulaire
     */
    function validateForm() {
        const emailValid = validateEmail();
        const passwordValid = validatePassword();
        
        return emailValid && passwordValid;
    }
    
    /**
     * Fonctions d'affichage des messages et animations
     */
    function showLoader() {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Connexion en cours...';
            submitBtn.classList.add('animate-pulse');
        }
    }
    
    function hideLoader() {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Se connecter à EcoRide';
            submitBtn.classList.remove('animate-pulse');
        }
    }
    
    function showSuccessMessage(message) {
        // Supprimer les anciens messages
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Créer et afficher un message de succès
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success border-0 shadow-sm animate-slideDown';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <div>${message}</div>
            </div>
        `;
        
        // Insérer le message en haut du formulaire
        form.parentNode.insertBefore(alertDiv, form);
        
        // Faire défiler vers le message
        alertDiv.scrollIntoView({ behavior: 'smooth' });
    }
    
    function showErrorMessage(message) {
        // Supprimer les anciens messages
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Créer et afficher un message d'erreur
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger border-0 shadow-sm animate-slideDown';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <div>${message}</div>
            </div>
        `;
        
        // Insérer le message en haut du formulaire
        form.parentNode.insertBefore(alertDiv, form);
        
        // Faire défiler vers le message
        alertDiv.scrollIntoView({ behavior: 'smooth' });
    }
    
    /**
     * Met à jour l'affichage de validation d'un champ avec animations
     */
    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) {
            // Animation de secousse
            field.classList.add('animate-shake');
            setTimeout(() => field.classList.remove('animate-shake'), 600);
            
            field.classList.remove('is-valid');
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
            feedback.classList.add('animate-fadeIn');
        }
    }
    
    function showFieldSuccess(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            // Animation de succès
            field.classList.add('animate-pulse-success');
            setTimeout(() => field.classList.remove('animate-pulse-success'), 600);
            
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            
            // Masquer le message d'erreur
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.style.display = 'none';
            }
        }
    }
    
    function clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.remove('is-invalid', 'is-valid', 'animate-shake', 'animate-pulse-success');
            
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.style.display = 'none';
            }
        }
    }
    
    /**
     * Animations spéciales pour le formulaire
     */
    function animateFormError() {
        form.classList.add('animate-shake');
        setTimeout(() => form.classList.remove('animate-shake'), 600);
    }
    
    function animateFormSuccess() {
        form.classList.add('animate-pulse-success');
        setTimeout(() => form.classList.remove('animate-pulse-success'), 1000);
    }
    
    /**
     * Fonction utilitaire de validation email
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
