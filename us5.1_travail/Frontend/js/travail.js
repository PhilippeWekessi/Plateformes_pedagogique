document.getElementById("formTravail").addEventListener("submit", function(e){
    e.preventDefault();

    // Ajout d'un champ caché pour id_espace (à récupérer depuis l'URL ou une autre source)
    const idEspace = 1; // Remplace cette valeur par la méthode appropriée pour récupérer l'id_espace

    const data = {
        titre: this.titre.value,
        type: this.type.value,
        consignes: this.consignes.value,
        date_limite: this.date_limite.value,
        id_espace: idEspace
    };

    fetch("../backend/creer_travail.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
    .then(res => {
        if (!res.ok) {
            throw new Error(`Erreur HTTP! statut: ${res.status}`);
        }
        return res.json();
    })
    .then(resp => {
        const msg = document.getElementById("message");
        msg.innerText = resp.message;
        msg.style.color = resp.success ? "var(--success)" : "var(--danger)";
        if(resp.success){
            this.reset();
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById("message").innerText = "Erreur lors de la création du travail";
        document.getElementById("message").style.color = "var(--danger)";
    });
});
