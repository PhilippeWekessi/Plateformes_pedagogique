document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.getElementById("pointsListe");
    const message = document.getElementById("message");

    fetch("../backend/lister_points_etudiant.php")
        .then(response => response.json())
        .then(data => {
            if(data.length === 0) {
                message.textContent = "Vous n'avez encore aucun point.";
                message.className = "error";
            } else {
                data.forEach(item => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${item.titre}</td>
                        <td>${item.type}</td>
                        <td>${item.note}</td>
                        <td>${item.commentaire}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        })
        .catch(error => {
            message.textContent = "Erreur lors du chargement des points.";
            message.className = "error";
            console.error(error);
        });
});
