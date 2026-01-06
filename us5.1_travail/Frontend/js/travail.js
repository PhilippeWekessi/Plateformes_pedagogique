document.getElementById("formTravail").addEventListener("submit", function(e){
    e.preventDefault();

    const data = {
        titre: this.titre.value,
        type: this.type.value,
        consignes: this.consignes.value,
        date_limite: this.date_limite.value
    };

    fetch("../backend/creer_travail.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(resp => {
        const msg = document.getElementById("message");
        msg.innerText = resp.message;
        if(resp.success){
            msg.style.color = "green";
            this.reset();
        } else {
            msg.style.color = "red";
        }
    })
    .catch(err => console.error(err));
});
