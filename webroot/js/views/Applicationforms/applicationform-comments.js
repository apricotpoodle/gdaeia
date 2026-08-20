document.addEventListener('DOMContentLoaded', function () {
    // 1. Récupération du formulaire principal et de l'ID de la demande
    const mainForm = document.querySelector('form#applicationform-main-form');
    if (!mainForm) return;

    // Extraire l'ID de l'enregistrement depuis l'action du formulaire ou l'URL
    const formAction = mainForm.getAttribute('action') || window.location.pathname;
    const urlSegments = formAction.split('?')[0].split('/').filter(Boolean);
    const applicationformId = urlSegments[urlSegments.length - 1];

    const commentsContainer = document.getElementById('comments-container');
    const commentBadgeCount = document.getElementById('comment-badge-count');
    const addCommentForm = document.getElementById('add-comment-form');
    const offcanvasEl = document.getElementById('offcanvasComments');

    if (!applicationformId || isNaN(applicationformId) || !commentsContainer) {
        return;
    }

    // 2. Fonction de chargement des commentaires via l'API REST
    function loadComments() {
        fetch(`/api/comments.json?model=Applicationforms&foreign_key=${applicationformId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Erreur serveur HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            renderComments(data.comments || []);
        })
        .catch(err => {
            commentsContainer.innerHTML = `<div class="alert alert-danger m-2 fs-7">Impossible de charger les commentaires : ${escapeHtml(err.message)}</div>`;
        });
    }

    // 3. Fonction de rendu HTML des cartes de commentaires
    function renderComments(comments) {
        commentsContainer.innerHTML = '';
        
        if (commentBadgeCount) {
            commentBadgeCount.textContent = comments.length;
            commentBadgeCount.classList.toggle('d-none', comments.length === 0);
        }

        if (comments.length === 0) {
            commentsContainer.innerHTML = `<div class="text-center text-muted py-4 fs-7">Aucun commentaire pour le moment.</div>`;
            return;
        }

        comments.forEach(comment => {
            const date = new Date(comment.created).toLocaleString('fr-FR', {
                dateStyle: 'short',
                timeStyle: 'short'
            });
            const author = comment.user ? `${comment.user.firstname || ''} ${comment.user.lastname || ''}`.trim() || comment.user.email : 'Utilisateur';

            const card = document.createElement('div');
            card.className = 'card mb-2 border-0 shadow-sm bg-light';
            card.innerHTML = `
                <div class="card-body p-2 fs-7">
                    <div class="d-flex justify-content-between text-muted mb-1 fs-8">
                        <strong class="text-dark">${escapeHtml(author)}</strong>
                        <small>${date}</small>
                    </div>
                    <p class="mb-1 text-break">${escapeHtml(comment.content)}</p>
                    <span class="badge bg-secondary fs-9">${escapeHtml(comment.type)}</span>
                </div>
            `;
            commentsContainer.appendChild(card);
        });

        // Défilement automatique vers le bas du fil
        commentsContainer.scrollTop = commentsContainer.scrollHeight;
    }

    // 4. Soumission d'un nouveau commentaire
    if (addCommentForm) {
        addCommentForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const contentInput = document.getElementById('comment-content');
            const typeInput = document.getElementById('comment-type');

            if (!contentInput.value.trim()) return;

            // Récupération du jeton CSRF depuis le meta tag ou un champ du formulaire
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
                || document.querySelector('input[name="_csrfToken"]')?.value;

            const payload = {
                model: 'Applicationforms',
                foreign_key: parseInt(applicationformId, 10),
                content: contentInput.value.trim(),
                type: typeInput.value
            };

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };

            // Injection du jeton CSRF dans les entêtes HTTP
            if (csrfToken) {
                headers['X-CSRF-Token'] = csrfToken;
            }

            fetch('/api/comments/add.json', {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(payload)
            })
            .then(async res => {
                const isJson = res.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await res.json() : null;

                if (!res.ok) {
                    const errorMsg = (data && data.message) ? data.message : `Erreur HTTP ${res.status}`;
                    throw new Error(errorMsg);
                }
                return data;
            })
            .then(resData => {
                if (resData && resData.success) {
                    contentInput.value = '';
                    loadComments();
                } else {
                    alert(resData.message || 'Erreur lors de l\'enregistrement.');
                }
            })
            .catch(err => alert(`Erreur lors de la publication : ${err.message}`));
        });
    }

    // 5. Utilitaire de protection XSS
    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // 6. Événements de rafraîchissement
    if (offcanvasEl) {
        offcanvasEl.addEventListener('show.bs.offcanvas', loadComments);
    }

    // Chargement silencieux initial au chargement de la page pour alimenter le badge
    loadComments();
});