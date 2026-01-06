document.getElementById("formPromotion").addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch("../backend/creer_promotion.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const msg = document.getElementById("message");
        msg.textContent = data.message;
        msg.className = data.status;
    });
});
