// JS formulaire avis EcoRide

document.addEventListener('DOMContentLoaded', function() {
    // Je gère les étoiles (Note globale)
    const starContainer = document.querySelector('.star-rating');
    if (starContainer) {
        const stars = starContainer.querySelectorAll('.star');
        const hiddenInput = starContainer.querySelector('input[type="hidden"]');
        stars.forEach((star, idx) => {
            star.addEventListener('click', function() {
                let note = idx + 1;
                hiddenInput.value = note;
                // Je mets à jour l'affichage visuel active/inactive
                stars.forEach((s, i) => {
                    s.classList.toggle('active', i < note);
                    s.classList.toggle('inactive', i >= note);
                });
            });
        });
    }

    // Je gère les étoiles (Critères)
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

    // Je gère les tags suggérés cliquables
    const tagsInput = document.getElementById('tags');
    const suggestedTags = document.querySelectorAll('.suggested-tag');
    
    if (tagsInput && suggestedTags.length > 0) {
        suggestedTags.forEach(tagElement => {
            tagElement.addEventListener('click', function() {
                const tagValue = this.dataset.tag;
                const currentTags = tagsInput.value;
                
                // Je vérifie si le tag n'est pas déjà présent
                if (!currentTags.includes(tagValue)) {
                    // J'ajoute le tag
                    if (currentTags.trim() === '') {
                        tagsInput.value = tagValue;
                    } else {
                        tagsInput.value = currentTags + ', ' + tagValue;
                    }
                    
                    // J'applique l'effet visuel : tag sélectionné
                    this.classList.remove('bg-light', 'text-dark');
                    this.classList.add('bg-success', 'text-white');
                } else {
                    // Je retire le tag s'il est déjà présent
                    const tagsArray = currentTags.split(',').map(t => t.trim()).filter(t => t !== tagValue);
                    tagsInput.value = tagsArray.join(', ');
                    
                    // J'applique l'effet visuel : tag désélectionné
                    this.classList.remove('bg-success', 'text-white');
                    this.classList.add('bg-light', 'text-dark');
                }
            });
        });
    }

    // Je valide en temps réel le commentaire
    const commentaireField = document.getElementById('commentaire');
    const charCountElement = document.getElementById('charCount');
    
    if (commentaireField && charCountElement) {
        // Fonction de validation
        function validateCommentaire() {
            const length = commentaireField.value.length;
            charCountElement.textContent = length;
            
            // Je mets à jour le compteur de caractères
            if (length < 10) {
                charCountElement.style.color = '#dc3545'; // Rouge
                commentaireField.classList.add('is-invalid');
                commentaireField.classList.remove('is-valid');
                
                // J'affiche le message d'erreur
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
                
                // Je masque le message d'erreur
                let feedback = commentaireField.parentNode.querySelector('.invalid-feedback');
                if (feedback) {
                    feedback.style.display = 'none';
                }
            }
        }
        
        // Je valide en temps réel sur chaque saisie
        commentaireField.addEventListener('input', validateCommentaire);
        
        // Je fais la validation initiale au chargement de la page
        validateCommentaire();
    }

    // Je valide et envoie en AJAX
    const form = document.getElementById('avisForm');
    if (!form) return; // Sécurité

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Je fais la validation finale avant envoi
        const commentaire = commentaireField.value;
        if (commentaire.length < 10) {
            alert('Le commentaire doit contenir au moins 10 caractères !');
            commentaireField.focus();
            return;
        }

        // Je récupère les données du formulaire
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

        // Je fais une validation rapide (personnalise selon besoins)
        if (
            !avisData.note_globale || 
            Object.values(avisData.criteres).some(val => !val) ||
            !avisData.commentaire ||
            avisData.commentaire.length < 10
        ) {
            alert("Merci de remplir tous les champs obligatoires et d'attribuer une note !");
            return;
        }

        // Je désactive le bouton pendant l'envoi
        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Envoi en cours...';

       // Je fais l'envoi AJAX corrigé
        fetch('/api/avis', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                trajet_id: avisData.trajet_id,
                conducteur_id: avisData.conducteur_id,
                note: avisData.note_globale,
                commentaire: avisData.commentaire,
                nom_utilisateur: 'Utilisateur' // Ou récupère depuis une variable
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Avis publié avec succès !');
                window.location.href = '/avis';
            } else {
                alert('Erreur : ' + (data.error || data.message));
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert("Erreur lors de l'envoi de l'avis !");
        })
        .finally(() => {
            // Je restaure le bouton
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    })
});
