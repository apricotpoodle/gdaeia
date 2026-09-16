const token = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';
const root = document.querySelector('#validation-workflow');
const id = root?.dataset.applicationformId;

const stateLabels = {
    en_attente: 'En attente de vote',
    a_venir: 'À venir',
    acceptee: 'Acceptée',
    refusee: 'Refusée',
    annulee: 'Annulée',
};

async function request(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-CSRF-Token': token,
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        ...options,
    });
    const body = await response.text();
    let payload;
    try {
        payload = JSON.parse(body);
    } catch (_) {
        throw new Error('Réponse serveur inattendue (' + response.status + ') : ' + body.slice(0, 160));
    }
    if (!response.ok || !payload.success) {
        throw new Error(payload.message || 'Opération impossible.');
    }

    return payload;
}

function escape(value) {
    const node = document.createElement('div');
    node.textContent = value ?? '';

    return node.innerHTML;
}

function voteControls(step) {
    if (!step.can_vote) {
        return '';
    }
    const proxyLabel = step.is_proxy_vote ? ' par suppléance' : '';
    const id = escape(step.id);

    return '<div class="mt-2 border-top pt-2">'
        + '<label class="form-label visually-hidden" for="validation-comment-' + id + '">Commentaire de vote</label>'
        + '<textarea class="form-control form-control-sm mb-2" id="validation-comment-' + id + '" rows="2" placeholder="Commentaire' + proxyLabel + ' (obligatoire en cas de refus)"></textarea>'
        + '<button class="btn btn-sm btn-success vote" data-decision="accepter" data-step-id="' + id + '">Accepter' + proxyLabel + '</button>'
        + '<button class="btn btn-sm btn-danger vote ms-1" data-decision="refuser" data-step-id="' + id + '">Refuser' + proxyLabel + '</button>'
        + '</div>';
}

function activateValidationTabFromUrl() {
    if (new URLSearchParams(window.location.search).get('tab') === 'validation') {
        document.querySelector('#validation-tab')?.click();
    }
}

async function render() {
    if (!root || !id) {
        return;
    }
    const response = await fetch('/api/applicationforms/' + id + '/validation.json', {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
    });
    const data = await response.json();
    if (!data.run) {
        root.innerHTML = '<p class="text-muted mb-0">Cycle non lancé.</p>';

        return;
    }
    const progress = data.progress || { completed: 0, total: 0, percentage: 0 };
    const steps = data.steps.map((step) => {
        const dueAt = step.due_at
            ? '<small class="text-muted">Échéance : ' + escape(new Date(step.due_at).toLocaleString('fr-FR')) + '</small>'
            : '';
        const comment = step.comment
            ? '<p class="mb-0 mt-2"><small>Commentaire : ' + escape(step.comment) + '</small></p>'
            : '';

        return '<li class="list-group-item">'
            + '<div class="d-flex justify-content-between align-items-start gap-2">'
            + '<span>Séquence ' + escape(step.sequence_number) + ' — ' + escape(step.role?.name || 'Rôle') + '</span>'
            + '<strong>' + escape(stateLabels[step.state] || step.state) + '</strong></div>'
            + dueAt + comment + voteControls(step) + '</li>';
    }).join('');
    root.innerHTML = '<div class="mb-3"><p class="mb-1"><strong>État : '
        + escape(stateLabels[data.run.state] || data.run.state)
        + '</strong></p><div class="progress" role="progressbar" aria-label="Avancement de la validation" aria-valuenow="'
        + progress.percentage + '" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: '
        + progress.percentage + '%">' + progress.percentage + '%</div></div><small class="text-muted">'
        + progress.completed + ' rôle(s) ayant voté sur ' + progress.total + '</small></div><ul class="list-group">'
        + steps + '</ul>';
    root.querySelectorAll('.vote').forEach((button) => button.addEventListener('click', async () => {
        const decision = button.dataset.decision;
        const stepId = Number(button.dataset.stepId);
        const comment = root.querySelector('#validation-comment-' + stepId)?.value.trim() || '';
        if (decision === 'refuser' && comment === '') {
            window.alert('Un commentaire est obligatoire lors d’un refus.');

            return;
        }
        try {
            await request('/api/applicationforms/' + id + '/validation/vote.json', {
                method: 'POST',
                body: JSON.stringify({ step_id: stepId, decision, comment, _csrfToken: token }),
            });
            await render();
        } catch (error) {
            window.alert(error.message);
        }
    }));
}

activateValidationTabFromUrl();
render();
