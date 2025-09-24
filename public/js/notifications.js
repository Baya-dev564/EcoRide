// Gestion des notifications de messages - EcoRide
document.addEventListener('DOMContentLoaded', function() {
    // Je vérifie s'il y a un utilisateur connecté
    if (typeof userConnected !== 'undefined' && userConnected) {
        updateUnreadCount();
        
        // Je rafraîchis le compteur toutes les 30 secondes
        setInterval(updateUnreadCount, 30000);
    }
});

// Fonction pour mettre à jour le compteur
function updateUnreadCount() {
    fetch('/api/messages/unread-count')
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('unreadBadge');
            const count = document.getElementById('unreadCount');
            
            if (data.count > 0) {
                count.textContent = data.count;
                badge.style.display = 'inline-flex';
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Erreur compteur messages:', error));
}
