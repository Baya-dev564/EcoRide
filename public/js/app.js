// public/js/app.js
// JavaScript global EcoRide 
document.addEventListener('DOMContentLoaded', function() {
    console.log('🌱 EcoRide JavaScript chargé');
    
    // Auto-fermeture des alertes après 5 secondes
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });
});
