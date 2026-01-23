console.log('Dashboard JS chargé');

window.addEventListener('DOMContentLoaded', async function() {
    console.log('Vérification de la session...');
    
    try {
        const response = await fetch('../backend/verifier_session.php');
        console.log('Response status:', response.status);
        
        const text = await response.text();
        console.log('Response text:', text);
        
        const data = JSON.parse(text);
        console.log('Data:', data);
        
        if (!data.success) {
            console.log('Session invalide, redirection vers login');
            window.location.href = 'login.html';
            return;
        }
        
        console.log('Session valide');
        
        // Afficher le nom de l'utilisateur
        const userName = document.getElementById('userName');
        if (userName && data.user) {
            userName.textContent = data.user.prenom + ' ' + data.user.nom;
            console.log('Nom utilisateur affiché:', userName.textContent);
        }
        
    } catch (error) {
        console.error('Erreur complète:', error);
        console.log('Redirection vers login à cause d\'une erreur');
        window.location.href = 'login.html';
    }
});