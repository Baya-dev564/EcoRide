// public/js/inscription.js
// Gestion de l'inscription EcoRide avec validation temps réel et AJAX

document.addEventListener('DOMContentLoaded', function() {
    const formInscription = document.getElementById('formInscription');
    
    if (!formInscription) {
        return;
    }
    
    // Désactiver la validation HTML5 native pour utiliser notre validation JavaScript
    formInscription.setAttribute('novalidate', 'true');
    
    // Initialiser la validation en temps réel
    initValidationTempsReel();
    
    // Initialiser la fonctionnalité œil mot de passe
    initTogglePassword();
    
    // Intercepter la soumission du formulaire pour AJAX
    formInscription.addEventListener('submit', function(e) {
        // Empêcher la soumission normale (rechargement de page)
        e.preventDefault();
        e.stopPropagation();
        
        // Validation côté client d'abord
        if (validateForm()) {
            // Soumission AJAX si validation OK
            soumettreInscriptionAjax();
        }
    });
    
    /**
     * Initialise la fonctionnalité d'affichage/masquage du mot de passe
     */
    function initTogglePassword() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('mot_de_passe');
        
        if (togglePassword && passwordField) {
            togglePassword.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Toggle du type de champ
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    // Changer l'icône : œil fermé
                    this.querySelector('i').classList.remove('fa-eye');
                    this.querySelector('i').classList.add('fa-eye-slash');
                } else {
                    passwordField.type = 'password';
                    // Changer l'icône : œil ouvert
                    this.querySelector('i').classList.remove('fa-eye-slash');
                    this.querySelector('i').classList.add('fa-eye');
                }
            });
        }
    }
    
    /**
     * Validation en temps réel pendant la saisie
     */
    function initValidationTempsReel() {
        // Validation du pseudo en temps réel
        const pseudoField = document.getElementById('pseudo');
        if (pseudoField) {
            pseudoField.addEventListener('input', function() {
                const pseudo = this.value.trim();
                clearFieldError('pseudo');
                
                if (pseudo.length > 0 && pseudo.length < 3) {
                    showFieldError('pseudo', 'Le pseudo doit contenir au moins 3 caractères.');
                } else if (pseudo.length >= 50) {
                    showFieldError('pseudo', 'Le pseudo ne peut pas dépasser 50 caractères.');
                } else if (pseudo.length > 0 && !/^[a-zA-Z0-9_-]+$/.test(pseudo)) {
                    showFieldError('pseudo', 'Le pseudo ne peut contenir que des lettres, chiffres, tirets et underscores.');
                } else if (pseudo.length >= 3) {
                    showFieldSuccess('pseudo');
                }
            });
        }
        
        // Validation de l'email en temps réel
        const emailField = document.getElementById('email');
        if (emailField) {
            emailField.addEventListener('input', function() {
                const email = this.value.trim();
                clearFieldError('email');
                
                if (email.length > 0 && !isValidEmail(email)) {
                    showFieldError('email', 'Veuillez saisir une adresse email valide.');
                } else if (email.length > 0 && isValidEmail(email)) {
                    showFieldSuccess('email');
                }
            });
        }
        
        // Validation du mot de passe en temps réel
        const motDePasseField = document.getElementById('mot_de_passe');
        if (motDePasseField) {
            motDePasseField.addEventListener('input', function() {
                const motDePasse = this.value;
                clearFieldError('mot_de_passe');
                
                if (motDePasse.length > 0) {
                    if (motDePasse.length < 6) {
                        showFieldError('mot_de_passe', 'Le mot de passe doit contenir au moins 6 caractères.');
                    } else {
                        showFieldSuccess('mot_de_passe');
                    }
                }
                
                // Revalider la confirmation si elle est remplie
                const confirmerField = document.getElementById('confirmer_mot_de_passe');
                if (confirmerField && confirmerField.value.length > 0) {
                    validateConfirmationMotDePasse();
                }
            });
        }
        
        // Validation de la confirmation du mot de passe en temps réel
        const confirmerField = document.getElementById('confirmer_mot_de_passe');
        if (confirmerField) {
            confirmerField.addEventListener('input', function() {
                validateConfirmationMotDePasse();
            });
        }
        
        // Validation du consentement RGPD
        const rgpdField = document.getElementById('consentement_rgpd');
        if (rgpdField) {
            rgpdField.addEventListener('change', function() {
                clearFieldError('consentement_rgpd');
                if (this.checked) {
                    showFieldSuccess('consentement_rgpd');
                } else {
                    showFieldError('consentement_rgpd', 'Vous devez accepter la politique de confidentialité.');
                }
            });
        }
    }
    
    /**
     * Validation complète du formulaire avant soumission
     */
    function validateForm() {
        // Réinitialiser les erreurs
        clearAllErrors();
        
        let hasErrors = false;
        
        // Validation des champs obligatoires
        if (!validatePseudo()) hasErrors = true;
        if (!validateEmail()) hasErrors = true;
        if (!validateMotDePasse()) hasErrors = true;
        if (!validateConfirmationMotDePasse()) hasErrors = true;
        
        // Validation du consentement RGPD
        const consentementRgpd = document.getElementById('consentement_rgpd');
        if (consentementRgpd && !consentementRgpd.checked) {
            showFieldError('consentement_rgpd', 'Vous devez accepter la politique de confidentialité.');
            hasErrors = true;
        }
        
        return !hasErrors;
    }
    
    /**
     * Validation du pseudo
     */
    function validatePseudo() {
        const pseudoField = document.getElementById('pseudo');
        if (!pseudoField) return true;
        
        const pseudo = pseudoField.value.trim();
        
        if (pseudo.length === 0) {
            showFieldError('pseudo', 'Le pseudo est obligatoire.');
            return false;
        } else if (pseudo.length < 3) {
            showFieldError('pseudo', 'Le pseudo doit contenir au moins 3 caractères.');
            return false;
        } else if (pseudo.length >= 50) {
            showFieldError('pseudo', 'Le pseudo ne peut pas dépasser 50 caractères.');
            return false;
        } else if (!/^[a-zA-Z0-9_-]+$/.test(pseudo)) {
            showFieldError('pseudo', 'Le pseudo ne peut contenir que des lettres, chiffres, tirets et underscores.');
            return false;
        }
        
        showFieldSuccess('pseudo');
        return true;
    }
    
    /**
     * Validation de l'email
     */
    function validateEmail() {
        const emailField = document.getElementById('email');
        if (!emailField) return true;
        
        const email = emailField.value.trim();
        
        if (email.length === 0) {
            showFieldError('email', 'L\'adresse email est obligatoire.');
            return false;
        } else if (!isValidEmail(email)) {
            showFieldError('email', 'Veuillez saisir une adresse email valide.');
            return false;
        }
        
        showFieldSuccess('email');
        return true;
    }
    
    /**
     * Validation du mot de passe
     */
    function validateMotDePasse() {
        const motDePasseField = document.getElementById('mot_de_passe');
        if (!motDePasseField) return true;
        
        const motDePasse = motDePasseField.value;
        
        if (motDePasse.length === 0) {
            showFieldError('mot_de_passe', 'Le mot de passe est obligatoire.');
            return false;
        } else if (motDePasse.length < 6) {
            showFieldError('mot_de_passe', 'Le mot de passe doit contenir au moins 6 caractères.');
            return false;
        }
        
        showFieldSuccess('mot_de_passe');
        return true;
    }
    
    /**
     * Validation de la confirmation du mot de passe
     */
    function validateConfirmationMotDePasse() {
        const motDePasseField = document.getElementById('mot_de_passe');
        const confirmerField = document.getElementById('confirmer_mot_de_passe');
        
        if (!motDePasseField || !confirmerField) return true;
        
        const motDePasse = motDePasseField.value;
        const confirmerMotDePasse = confirmerField.value;
        
        clearFieldError('confirmer_mot_de_passe');
        
        if (confirmerMotDePasse.length === 0) {
            showFieldError('confirmer_mot_de_passe', 'La confirmation du mot de passe est obligatoire.');
            return false;
        } else if (motDePasse !== confirmerMotDePasse) {
            showFieldError('confirmer_mot_de_passe', 'Les mots de passe ne correspondent pas.');
            return false;
        }
        
        showFieldSuccess('confirmer_mot_de_passe');
        return true;
    }
    
    /**
     * Soumission AJAX du formulaire d'inscription EcoRide
     */
    function soumettreInscriptionAjax() {
        // Afficher le loader
        showLoader();
        
        // Récupération des données du formulaire
        const formData = new FormData();
        formData.append('pseudo', document.getElementById('pseudo').value.trim());
        formData.append('email', document.getElementById('email').value.trim());
        formData.append('mot_de_passe', document.getElementById('mot_de_passe').value);
        formData.append('confirmer_mot_de_passe', document.getElementById('confirmer_mot_de_passe').value);
        
        // Champs optionnels (seulement s'ils existent et ont une valeur)
        const optionalFields = ['nom', 'prenom', 'telephone', 'ville'];
        optionalFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && field.value.trim()) {
                formData.append(fieldName, field.value.trim());
            }
        });
        
        // Champs booléens
        const consentementField = document.getElementById('consentement_rgpd');
        if (consentementField && consentementField.checked) {
            formData.append('consentement_rgpd', '1');
        }
        
        const permisField = document.getElementById('permis_conduire');
        if (permisField && permisField.checked) {
            formData.append('permis_conduire', '1');
        }
        
        // Configuration de la requête AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/inscription', true);
        
        // Headers AJAX
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        
        // Gestion de la réponse
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                hideLoader();
                
                if (xhr.status === 200) {
                    try {
                        const data = JSON.parse(xhr.responseText);
                        
                        if (data.succes) {
                            // Inscription réussie
                            showSuccessMessage(data.message);
                            
                            // Redirection vers la connexion après 2 secondes
                            setTimeout(() => {
                                window.location.href = '/connexion';
                            }, 2000);
                            
                        } else {
                            // Erreurs d'inscription
                            if (data.erreurs && Array.isArray(data.erreurs)) {
                                showGeneralErrors(data.erreurs);
                            } else {
                                showGeneralError(data.erreur || 'Une erreur est survenue lors de l\'inscription.');
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
        
        // Gestion des erreurs réseau
        xhr.onerror = function() {
            hideLoader();
            showGeneralError('Erreur de connexion réseau.');
        };
        
        // Envoi de la requête
        xhr.send(formData);
    }
    
    /**
     * Affiche un message d'erreur sur un champ spécifique
     */
    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        if (field) {
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
        }
    }
    
    /**
     * Affiche un feedback de succès sur un champ
     */
    function showFieldSuccess(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            
            // Masquer le message d'erreur s'il existe
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.style.display = 'none';
            }
        }
    }
    
    /**
     * Efface les erreurs d'un champ spécifique
     */
    function clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.remove('is-invalid', 'is-valid');
            
            const feedback = field.parentNode.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.style.display = 'none';
            }
        }
    }
    
    /**
     * Efface toutes les erreurs du formulaire
     */
    function clearAllErrors() {
        // Supprimer toutes les classes d'erreur et de succès
        document.querySelectorAll('.is-invalid, .is-valid').forEach(field => {
            field.classList.remove('is-invalid', 'is-valid');
        });
        
        // Masquer tous les messages d'erreur
        document.querySelectorAll('.invalid-feedback').forEach(feedback => {
            feedback.style.display = 'none';
        });
        
        // Supprimer les messages d'erreur généraux
        document.querySelectorAll('.alert-danger, .alert-success').forEach(alert => {
            alert.remove();
        });
    }
    
    /**
     * Affiche plusieurs messages d'erreur généraux
     */
    function showGeneralErrors(erreurs) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger border-0 shadow-sm';
        
        let errorHtml = '<div class="d-flex align-items-start"><i class="fas fa-exclamation-triangle me-2 mt-1"></i><div>';
        
        if (erreurs.length === 1) {
            errorHtml += erreurs[0];
        } else {
            errorHtml += '<ul class="mb-0">';
            erreurs.forEach(erreur => {
                errorHtml += '<li>' + erreur + '</li>';
            });
            errorHtml += '</ul>';
        }
        
        errorHtml += '</div></div>';
        alertDiv.innerHTML = errorHtml;
        
        // Insérer le message en haut du formulaire
        const form = document.getElementById('formInscription');
        form.parentNode.insertBefore(alertDiv, form);
        
        // Faire défiler vers le message
        alertDiv.scrollIntoView({ behavior: 'smooth' });
    }
    
    /**
     * Affiche un message d'erreur général
     */
    function showGeneralError(message) {
        showGeneralErrors([message]);
    }
    
    /**
     * Affiche un message de succès
     */
    function showSuccessMessage(message) {
        // Supprimer les anciens messages
        document.querySelectorAll('.alert').forEach(alert => alert.remove());
        
        // Créer et afficher un message de succès
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success border-0 shadow-sm';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2"></i>
                <div>${message}</div>
            </div>
        `;
        
        // Insérer le message en haut du formulaire
        const form = document.getElementById('formInscription');
        form.parentNode.insertBefore(alertDiv, form);
        
        // Faire défiler vers le message
        alertDiv.scrollIntoView({ behavior: 'smooth' });
    }
    
    /**
     * Affiche le loader sur le bouton de soumission
     */
    function showLoader() {
        const submitBtn = document.querySelector('#formInscription button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Création en cours...';
        }
    }
    
    /**
     * Masque le loader et restaure le bouton
     */
    function hideLoader() {
        const submitBtn = document.querySelector('#formInscription button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-user-plus me-2"></i>Créer mon compte EcoRide';
        }
    }
    
    /**
     * Valide le format d'une adresse email
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
