document.addEventListener('DOMContentLoaded', function() {
    const btnLogout = document.getElementById('btnLogout');
    
    if (btnLogout) {
        btnLogout.addEventListener('click', async function() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                try {
                    const response = await fetch('../backend/logout.php', {
                        method: 'POST'
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        window.location.href = 'login.html';
                    }
                    
                } catch (error) {
                    console.error('Erreur:', error);
                    window.location.href = 'login.html';
                }
            }
        });
    }
});