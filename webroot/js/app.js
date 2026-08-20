document.addEventListener('DOMContentLoaded', function () {
    // Auto-initialisation et fermeture automatique des Toasts Bootstrap
    const toastElements = document.querySelectorAll('.toast.auto-toast');
    toastElements.forEach(function (toastEl) {
        const bsToast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 4000 // Disparaît automatiquement après 4 secondes (4000 ms)
        });
        bsToast.show();

        // Suppression du conteneur HTML après disparition complète
        toastEl.addEventListener('hidden.bs.toast', function () {
            const container = toastEl.closest('.toast-container');
            if (container) {
                container.remove();
            }
        });
    });
});