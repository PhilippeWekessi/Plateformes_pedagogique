document.getElementById("formPromotion").addEventListener("submit", async function(e) {
    e.preventDefault();
    
    const messageElement = document.getElementById("message");
    const submitButton = this.querySelector("button[type='submit']");
    
    // Réinitialiser le message
    messageElement.textContent = "";
    messageElement.className = "";
    
    // Désactiver le bouton pendant le traitement
    submitButton.disabled = true;
    submitButton.textContent = "Création en cours...";
    
    try {
        const formData = new FormData(this);
        
        // Ajouter des logs pour débogage
        console.log("Données envoyées:");
        for (let [key, value] of formData.entries()) {
            console.log(key + ": " + value);
        }
        
        const response = await fetch("../backend/creer_promotion.php", {
            method: "POST",
            body: formData
        });
        
        console.log("Statut HTTP:", response.status);
        
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        
        const data = await response.json();
        console.log("Réponse:", data);
        
        // Afficher le message
        messageElement.textContent = data.message;
        messageElement.className = data.status;
        
        // Si succès, réinitialiser le formulaire après 2 secondes
        if (data.status === "success") {
            setTimeout(() => {
                this.reset();
                messageElement.textContent = "";
                messageElement.className = "";
            }, 2000);
        }
        
    } catch (error) {
        console.error("Erreur:", error);
        messageElement.textContent = "Erreur de connexion au serveur";
        messageElement.className = "error";
    } finally {
        // Réactiver le bouton
        submitButton.disabled = false;
        submitButton.textContent = "Créer la promotion";
    }
});