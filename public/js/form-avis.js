// ============================
// JS formulaire avis EcoRide (COMPLET ET CORRIGÉ !)
// ============================

document.addEventListener('DOMContentLoaded', function() {
    // --- 1. GESTION DES ÉTOILES (Note globale) ---
    const starContainer = document.querySelector('.star-rating');
    if (starContainer) {
        const stars = starContainer.querySelectorAll('.star');
        const hiddenInput = starContainer.querySelector('input[type="hidden"]');
        stars.forEach((star, idx) => {
            star.addEventListener('click', function() {
                let note = idx + 1;
                hiddenInput.value = note;
                // Mise à jour visuelle active/inactive
                stars.forEach((s, i) => {
                    s.classList.toggle('active', i < note);
                    s.classList.toggle('inactive', i >= note);
                });
            });
        });
    }

    // --- 2. GESTION DES ÉTOILES (CRITÈRES) ---
    document.querySelectorAll('.star-rating-critere').forEach(container => {
        const stars = container.querySelectorAll('.star-small');
        const hiddenInput = container.querySelector('input[type="hidden"]');
        stars.forEach((star, idx) => {
            star.addEventListener('click', function() {
                let note = idx + 1;
                hiddenInput.value = note;
                stars.forEach((s, i) => {
                    s.classList.toggle('active', i < note);
                    s.classList.toggle('inactive', i >= note);
                });
            });
        });
    });

    // --- 3. NOUVEAU : GESTION DES TAGS SUGGÉRÉS CLIQUABLES ---
    const tagsInput = document.getElementById('tags');
    const suggestedTags = document.querySelectorAll('.suggested-tag');
    
    if (tagsInput && suggestedTags.length > 0) {
        suggestedTags.forEach(tagElement => {
            tagElement.addEventListener('click', function() {
                const tagValue = this.dataset.tag;
                const currentTags = tagsInput.value;
                
                // Vérifier si le tag n'est pas déjà présent
                if (!currentTags.includes(tagValue)) {
                    // Ajouter le tag
                    if (currentTags.trim() === '') {
                        tagsInput.value = tagValue;
                    } else {
                        tagsInput.value = currentTags + ', ' + tagValue;
                    }
                    
                    // Effet visuel : tag sélectionné
                    this.classList.remove('bg-light', 'text-dark');
                    this.classList.add('bg-success', 'text-white');
                } else {
                    // Retirer le tag s'il est déjà présent
                    const tagsArray = currentTags.split(',').map(t => t.trim()).filter(t => t !== tagValue);
                    tagsInput.value = tagsArray.join(', ');
                    
                    // Effet visuel : tag désélectionné
                    this.classList.remove('bg-success', 'text-white');
                    this.classList.add('bg-light', 'text-dark');
                }
            });
        });
    }

    // --- 4. NOUVEAU : VALIDATION TEMPS RÉEL DU COMMENTAIRE ---
    const commentaireField = document.getElementById('commentaire');
    const charCountElement = document.getElementById('charCount');
    
    if (commentaireField && charCountElement) {
        // Fonction de validation
        function validateCommentaire() {
            const length = commentaireField.value.length;
            charCountElement.textContent = length;
            
            // Mise à jour du compteur de caractères
            if (length < 10) {
                charCountElement.style.color = '#dc3545'; // Rouge
                commentaireField.classList.add('is-invalid');
                commentaireField.classList.remove('is-valid');
                
                // Afficher le message d'erreur
                let feedback = commentaireField.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.textContent = `Il faut au minimum 10 caractères (${10 - length} restants)`;
                }
            } else if (length > 500) {
                charCountElement.style.color = '#dc3545'; // Rouge
                commentaireField.classList.add('is-invalid');
                commentaireField.classList.remove('is-valid');
                
                let feedback = commentaireField.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.style.display = 'block';
                    feedback.textContent = `Trop de caractères ! (${length - 500} en trop)`;
                }
            } else {
                charCountElement.style.color = '#198754'; // Vert
                commentaireField.classList.remove('is-invalid');
                commentaireField.classList.add('is-valid');
                
                // Masquer le message d'erreur
                let feedback = commentaireField.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.style.display = 'none';
                }
            }
        }
        
        // Validation en temps réel sur chaque saisie
        commentaireField.addEventListener('input', validateCommentaire);
        
        // Validation initiale au chargement de la page
        validateCommentaire();
    }

    // --- 5. VALIDATION ET ENVOI AJAX (AMÉLIORÉ) ---
    const form = document.getElementById('avisForm');
    if (!form) return; // Sécurité

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validation finale avant envoi
        const commentaire = commentaireField.value;
        if (commentaire.length < 10) {
            alert('Le commentaire doit contenir au moins 10 caractères !');
            commentaireField.focus();
            return;
        }

        // Récupération des données du formulaire
        const formData = new FormData(form);
        const avisData = {
            trajet_id: formData.get('trajet_id'),
            conducteur_id: formData.get('conducteur_id'),
            note_globale: parseInt(formData.get('note_globale')),
            criteres: {
                ponctualite: parseInt(formData.get('ponctualite')),
                conduite: parseInt(formData.get('conduite')),
                proprete: parseInt(formData.get('proprete')),
                ambiance: parseInt(formData.get('ambiance'))
            },
            commentaire: formData.get('commentaire'),
            tags: formData.get('tags') 
                ? formData.get('tags').split(',').map(t => t.trim()).filter(Boolean)
                : []
        };

        // Validation rapide (personnalise selon besoins)
        if (
            !avisData.note_globale || 
            Object.values(avisData.criteres).some(val => !val) ||
            !avisData.commentaire ||
            avisData.commentaire.length < 10
        ) {
            alert("Merci de remplir tous les champs obligatoires et d'attribuer une note !");
            return;
        }

        // Désactiver le bouton pendant l'envoi
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Envoi en cours...';

        // --- ENVOI AJAX CORRIGÉ : vérifie que le serveur répond bien en JSON ---
        fetch('/api/avis', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(avisData)
        })
        .then(async response => {
            const contentType = response.headers.get("content-type");
            if (!contentType || !contentType.includes("application/json")) {
                // Le serveur a répondu du HTML / erreur PHP : on affiche le contenu
                const text = await response.text();
                throw new Error("Erreur inattendue du serveur :\n" + text.substring(0, 250));
            }
            // Ici, on est sûr d'avoir du JSON
            return response.json();
        })
        .then(data => {
            if (data.succes) {
                alert('Avis publié avec succès !');
                window.location.href = '/avis';
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            alert("Erreur lors de l'envoi de l'avis ou réponse inattendue du serveur !\n" + error.message);
        })
        .finally(() => {
            // Restaurer le bouton
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});
