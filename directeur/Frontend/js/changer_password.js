document.addEventListener('DOMContentLoaded', function() {
    const changePasswordForm = document.getElementById('changePasswordForm');
    
    if (!changePasswordForm) {
        console.error('Formulaire non trouvé');
        return;
    }
    
    changePasswordForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const ancienPassword = document.getElementById('ancien_password').value;
        const nouveauPassword = document.getElementById('nouveau_password').value;
        const confirmerPassword = document.getElementById('confirmer_password').value;
        const messageDiv = document.getElementById('message');
        
        // Validation
        if (nouveauPassword !== confirmerPassword) {
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Les mots de passe ne correspondent pas';
            return;
        }
        
        // Validation du format
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(nouveauPassword)) {
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial';
            return;
        }
        
        messageDiv.className = 'message';
        messageDiv.style.display = 'block';
        messageDiv.style.background = '#d1ecf1';
        messageDiv.style.color = '#0c5460';
        messageDiv.textContent = 'Modification en cours...';
        
        try {
            const response = await fetch('../backend/changer_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ancien_password: ancienPassword,
                    nouveau_password: nouveauPassword
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                messageDiv.className = 'message success';
                messageDiv.textContent = 'Mot de passe modifié avec succès ! Redirection...';
                
                setTimeout(function() {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                messageDiv.className = 'message error';
                messageDiv.textContent = data.message || 'Erreur lors du changement de mot de passe';
            }
            
        } catch (error) {
            console.error('Erreur:', error);
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Erreur de connexion au serveur';
        }
    });
});