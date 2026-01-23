document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé - login.js');
    
    const loginForm = document.getElementById('loginForm');
    console.log('Formulaire:', loginForm);
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Formulaire soumis');
        
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const messageDiv = document.getElementById('message');
        
        console.log('Email:', email);
        console.log('Password length:', password.length);
        
        if (!email || !password) {
            messageDiv.className = 'message error';
            messageDiv.textContent = 'Veuillez remplir tous les champs';
            return;
        }
        
        messageDiv.className = 'message';
        messageDiv.style.display = 'block';
        messageDiv.style.background = '#d1ecf1';
        messageDiv.style.color = '#0c5460';
        messageDiv.textContent = 'Connexion en cours...';
        
        const xhr = new XMLHttpRequest();
        const url = '../backend/login.php';
        console.log('URL:', url);
        
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onload = function() {
            console.log('Réponse reçue');
            console.log('Status:', xhr.status);
            console.log('Response Text:', xhr.responseText);
            
            if (xhr.status === 200) {
                try {
                    const data = JSON.parse(xhr.responseText);
                    console.log('Data parsée:', data);
                    
                    if (data.success) {
                        messageDiv.className = 'message success';
                        messageDiv.textContent = 'Connexion réussie ! Redirection...';
                        
                        setTimeout(function() {
                            window.location.href = 'dashboard.html';
                        }, 1000);
                    } else {
                        messageDiv.className = 'message error';
                        messageDiv.textContent = data.message || 'Email ou mot de passe incorrect';
                    }
                } catch (error) {
                    console.error('Erreur de parsing:', error);
                    console.error('Texte reçu:', xhr.responseText);
                    messageDiv.className = 'message error';
                    messageDiv.textContent = 'Erreur de traitement: ' + error.message;
                }
            } else {
                console.error('Status non 200:', xhr.status);
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
            email: email,
            password: password
        };
        
        const jsonData = JSON.stringify(data);
        console.log('JSON envoyé:', jsonData);
        
        xhr.send(jsonData);
    });
});