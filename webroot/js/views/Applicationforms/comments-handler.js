document.addEventListener('DOMContentLoaded', function() {
    const commentForm = document.getElementById('form-add-comment');
    if (!commentForm) {
        return;
    }

    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(commentForm);
        const csrfToken = commentForm.getAttribute('data-csrf-token');

        fetch('/api/comments/add.json', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur serveur (' + response.status + ')');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Rechargement pour afficher le nouveau commentaire dans le fil
                window.location.reload();
            } else {
                alert(data.message || 'Impossible d\'enregistrer le commentaire.');
            }
        })
        .catch(err => {
            alert('Erreur lors de l\'envoi du commentaire : ' + err.message);
        });
    });
});