document.getElementById("formFormateur").addEventListener("submit", async function(e){
    e.preventDefault();

    const nom = this.nom.value.trim();
    const prenom = this.prenom.value.trim();
    const email = this.email.value.trim();
    const password = this.password.value;
    const role = this.role.value;

    const messageEl = document.getElementById("message");

    // Vérification email existant avant envoi
    try {
        const check = await fetch(`../backend/verifier_email.php?email=${encodeURIComponent(email)}`);
        const dataCheck = await check.json();

        if(dataCheck.exists){
            messageEl.innerText = "Email déjà utilisé";
            messageEl.className = "error";
            return;
        }

        // Envoyer les données pour créer le formateur
        const response = await fetch("../backend/creer_formateur.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ nom, prenom, email, password, role })
        });

        const result = await response.json();

        if(result.success){
            messageEl.innerText = result.message;
            messageEl.className = "success";
            this.reset();
        } else {
            messageEl.innerText = result.message;
            messageEl.className = "error";
        }

    } catch(err){
        console.error(err);
        messageEl.innerText = "Erreur réseau ou serveur";
        messageEl.className = "error";
    }
});