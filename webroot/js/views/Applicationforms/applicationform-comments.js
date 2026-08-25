/**
 * Gestionnaire du fil de discussion et des commentaires via API REST.
 *
 * Supports unifiés pour l'Offcanvas (edit.php) et la Vue tabulée (view.php).
 *
 * @module ApplicationformComments
 */
document.addEventListener('DOMContentLoaded', function () {
    const offcanvasEl = document.getElementById('offcanvasComments');
    const commentsContainerOffcanvas = document.getElementById('comments-container');
    const commentsContainerView = document.getElementById('comments-container-view');

    // Détermination du conteneur actif sur la page
    const commentsContainer = commentsContainerOffcanvas || commentsContainerView;

    const commentBadgeCount = document.getElementById('comment-badge-count');
    const addCommentForm = document.getElementById('add-comment-form');

    // Boutons de tri pour l'Offcanvas et la Vue
    const btnToggleSort = document.getElementById('btn-toggle-sort');
    const sortIcon = document.getElementById('sort-icon');
    const sortLabel = document.getElementById('sort-label');

    const btnToggleSortView = document.getElementById('btn-toggle-sort-view');
    const sortIconView = document.getElementById('sort-icon-view');
    const sortLabelView = document.getElementById('sort-label-view');

    // Extraction dynamique de l'ID de l'Applicationform depuis l'URL
    const pathSegments = window.location.pathname.split('?')[0].split('/').filter(Boolean);
    const applicationformId = pathSegments[pathSegments.length - 1];

    /** @type {Array<Object>} Cache local des commentaires pour tri instantané */
    let cachedComments = [];
    /** @type {string} Ordre de tri courant : 'desc' (anti-chronologique par défaut) */
    let currentSortOrder = 'desc';

    if (!applicationformId || isNaN(applicationformId) || !commentsContainer) {
        return;
    }

    /**
     * Ajuste la position du scroll selon l'ordre de tri.
     *
     * @function adjustScrollPosition
     * @returns {void}
     */
    function adjustScrollPosition() {
        if (!commentsContainer) return;
        setTimeout(() => {
            if (currentSortOrder === 'desc') {
                commentsContainer.scrollTop = 0; // Haut de liste (messages les plus récents)
            } else {
                commentsContainer.scrollTop = commentsContainer.scrollHeight; // Bas de liste
            }
        }, 50);
    }

    /**
     * Charge la liste des commentaires depuis l'API REST.
     *
     * @function loadComments
     * @returns {void}
     */
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
            cachedComments = data.comments || [];
            renderComments();
        })
        .catch(err => {
            commentsContainer.innerHTML = `<div class="alert alert-danger m-2 fs-7"><i class="fa-solid fa-triangle-exclamation me-1"></i> Impossible de charger les commentaires : ${escapeHtml(err.message)}</div>`;
        });
    }

    /**
     * Génère le rendu HTML en appliquant le tri configuré.
     *
     * @function renderComments
     * @returns {void}
     */
    function renderComments() {
        commentsContainer.innerHTML = '';

        if (commentBadgeCount) {
            commentBadgeCount.textContent = cachedComments.length;
            commentBadgeCount.classList.toggle('d-none', cachedComments.length === 0);
        }

        if (cachedComments.length === 0) {
            commentsContainer.innerHTML = `<div class="text-center text-muted py-4 fs-7"><i class="fa-solid fa-inbox me-1"></i> Aucun commentaire pour le moment.</div>`;
            return;
        }

        // Tri dynamique des données en mémoire
        const sortedComments = [...cachedComments].sort((a, b) => {
            const dateA = new Date(a.created).getTime();
            const dateB = new Date(b.created).getTime();
            return currentSortOrder === 'desc' ? dateB - dateA : dateA - dateB;
        });

        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex flex-column gap-2';

        sortedComments.forEach(comment => {
            const date = new Date(comment.created).toLocaleString('fr-FR', {
                dateStyle: 'short',
                timeStyle: 'short'
            });
            const author = comment.user ? `${comment.user.firstname || ''} ${comment.user.lastname || ''}`.trim() || comment.user.email : 'Utilisateur';

            const card = document.createElement('div');
            card.className = 'comment-item p-3 rounded bg-light border-start border-3 border-primary shadow-sm';
            card.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <strong class="text-dark fs-7"><i class="fa-solid fa-circle-user me-1 text-secondary"></i>${escapeHtml(author)}</strong>
                    <small class="text-muted fs-8"><i class="fa-regular fa-clock me-1"></i>${date}</small>
                </div>
                <p class="comment-content text-break mb-1 fs-7">${escapeHtml(comment.content)}</p>
                ${comment.type ? `<span class="badge bg-secondary fs-9">${escapeHtml(comment.type)}</span>` : ''}
            `;
            wrapper.appendChild(card);
        });

        commentsContainer.appendChild(wrapper);
        adjustScrollPosition();
    }

    /**
     * Bascule l'ordre de tri et met à jour l'IHM des boutons.
     *
     * @function toggleSortOrder
     * @returns {void}
     */
    function toggleSortOrder() {
        if (currentSortOrder === 'desc') {
            currentSortOrder = 'asc';
            if (sortIcon) sortIcon.className = 'fa-solid fa-arrow-up-short-wide me-1';
            if (sortLabel) sortLabel.textContent = 'Plus anciens';
            if (sortIconView) sortIconView.className = 'fa-solid fa-arrow-up-short-wide me-1';
            if (sortLabelView) sortLabelView.textContent = 'Plus anciens';
        } else {
            currentSortOrder = 'desc';
            if (sortIcon) sortIcon.className = 'fa-solid fa-arrow-down-wide-short me-1';
            if (sortLabel) sortLabel.textContent = 'Plus récents';
            if (sortIconView) sortIconView.className = 'fa-solid fa-arrow-down-wide-short me-1';
            if (sortLabelView) sortLabelView.textContent = 'Plus récents';
        }
        renderComments();
    }

    // Attachement des écouteurs de tri
    if (btnToggleSort) {
        btnToggleSort.addEventListener('click', toggleSortOrder);
    }
    if (btnToggleSortView) {
        btnToggleSortView.addEventListener('click', toggleSortOrder);
    }

    // Soumission du nouveau commentaire (si le formulaire existe)
    if (addCommentForm) {
        addCommentForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const contentInput = document.getElementById('comment-content');
            const typeInput = document.getElementById('comment-type');

            if (!contentInput || !contentInput.value.trim()) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('input[name="_csrfToken"]')?.value;

            const payload = {
                model: 'Applicationforms',
                foreign_key: parseInt(applicationformId, 10),
                content: contentInput.value.trim(),
                type: typeInput ? typeInput.value : 'GENERAL'
            };

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };

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

    /**
     * Échappe les caractères spéciaux HTML.
     *
     * @function escapeHtml
     * @param {string} str
     * @returns {string}
     */
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Événements Bootstrap Offcanvas
    if (offcanvasEl) {
        offcanvasEl.addEventListener('show.bs.offcanvas', loadComments);
        offcanvasEl.addEventListener('shown.bs.offcanvas', adjustScrollPosition);
    }

    // Chargement initial pour alimenter les compteurs et l'onglet
    loadComments();
});
