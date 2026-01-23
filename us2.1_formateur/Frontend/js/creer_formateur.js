document.addEventListener('DOMContentLoaded', function() {
    console.log('Script créer formateur chargé');
    
    const createForm = document.getElementById('createFormateurForm');
    
    if (!createForm) {
        console.error('Formulaire non trouvé');
        return;
    }
    
    createForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Formulaire soumis');
        
        const nom = document.getElementById('nom').value.trim();
        const prenom = document.getElementById('prenom').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const messageDiv = document.getElementById('message');
        
        console.log('Données:', { nom, prenom, email, password: '***' });
        
        // Validation
        if (!nom || !prenom || !email || !password) {
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Tous les champs sont requis';
            return;
        }
        
        // Validation du mot de passe
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        if (!passwordRegex.test(password)) {
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial';
            return;
        }
        
        // Message de chargement
        messageDiv.className = 'message';
        messageDiv.style.display = 'block';
        messageDiv.style.background = '#d1ecf1';
        messageDiv.style.color = '#0c5460';
        messageDiv.textContent = 'Création en cours...';
        
        const url = '../backend/creer_formateur.php';
        console.log('URL:', url);
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onload = function() {
            console.log('Réponse reçue');
            console.log('Status:', xhr.status);
            console.log('Response:', xhr.responseText);
            
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    console.log('Data parsée:', data);
                    
                    if (data.success) {
                        messageDiv.className = 'message success';
                        messageDiv.textContent = 'Formateur créé avec succès !';
                        
                        // Réinitialiser le formulaire
                        createForm.reset();
                        
                        // Redirection après 2 secondes
                        setTimeout(function() {
                            window.location.href = '../../directeur/frontend/dashboard.html';
                        }, 2000);
                    } else {
                        messageDiv.className = 'message error';
                        messageDiv.textContent = data.message || 'Erreur lors de la création';
                    }
                } catch (error) {
                    console.error('Erreur parsing:', error);
                    messageDiv.className = 'message error';
                    messageDiv.textContent = 'Erreur de traitement: ' + error.message;
                }
            } else {
                console.error('Status non 200');
                messageDiv.className = 'message error';
                messageDiv.textContent = 'Erreur serveur: ' + xhr.status;
            }
        };
        
        xhr.onerror = function() {
            console.error('Erreur XHR');
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Erreur de connexion au serveur';
        };
        
        const data = {
            nom: nom,
            prenom: prenom,
            email: email,
            password: password
        };
        
        const jsonData = JSON.stringify(data);
        console.log('JSON envoyé:', jsonData);
        
        xhr.send(jsonData);
    });
});