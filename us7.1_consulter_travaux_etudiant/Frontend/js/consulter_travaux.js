document.addEventListener("DOMContentLoaded", () => {
    // Remplace cette valeur par la méthode appropriée pour récupérer l'id_etudiant
    // Pour le développement, on utilise une valeur fixe (id_etudiant = 7 d'après tes données)
    const etudiant_id = 7;

    fetch(`../backend/lister_travaux_etudiant.php?etudiant_id=${etudiant_id}`)
        .then(res => {
            if (!res.ok) {
                return res.text().then(text => {
                    throw new Error(`Erreur HTTP! statut: ${res.status}. Réponse: ${text}`);
                });
            }
            return res.json();
        })
        .then(data => {
            const tbody = document.querySelector("#tableTravaux tbody");
            const message = document.getElementById("message");
            tbody.innerHTML = "";

            if (!data.success) {
                message.textContent = data.message || "Erreur lors de la récupération des travaux";
                return;
            }

            if (!data.travaux || data.travaux.length === 0) {
                message.textContent = "Aucun travail assigné";
                return;
            }

            data.travaux.forEach(travail => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${travail.titre || 'N/A'}</td>
                    <td>${travail.type || 'N/A'}</td>
                    <td>${travail.consignes ? travail.consignes.substring(0, 50) + (travail.consignes.length > 50 ? '...' : '') : 'N/A'}</td>
                    <td>${travail.date_fin ? new Date(travail.date_fin).toLocaleDateString() : 'N/A'}</td>
                    <td>${travail.statut || 'N/A'}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error("Erreur:", error);
            document.getElementById("message").textContent = "Erreur: " + error.message;
        });
});
