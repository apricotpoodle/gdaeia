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

function voteControls(step, requirements, templates) {
    if (!step.can_vote) {
        return '';
    }
    const proxyLabel = step.is_proxy_vote ? ' par suppléance' : '';
    const id = escape(step.id);
    const templateOptions = ['accepter', 'refuser'].flatMap((decision) => templates[decision].map((template) =>
        '<option value="' + escape(template.content) + '" data-decision="' + decision + '">' + escape(template.label) + '</option>'
    )).join('');

    return '<div class="mt-2 border-top pt-2">'
        + '<label class="form-label" for="validation-decision-' + id + '">Décision</label>'
        + '<select class="form-select form-select-sm mb-2 validation-decision" id="validation-decision-' + id + '" data-step-id="' + id + '">'
        + '<option value="accepter">Accepter' + proxyLabel + '</option><option value="refuser">Refuser' + proxyLabel + '</option></select>'
        + '<label class="form-label" for="validation-template-' + id + '">Commentaire prédéfini</label>'
        + '<select class="form-select form-select-sm mb-2 validation-template" id="validation-template-' + id + '" data-step-id="' + id + '"><option value="">Saisie libre</option>' + templateOptions + '</select>'
        + '<label class="form-label" for="validation-comment-' + id + '">Commentaire de vote</label>'
        + '<textarea class="form-control form-control-sm mb-2" id="validation-comment-' + id + '" rows="3" placeholder="Commentaire' + proxyLabel + '"></textarea>'
        + '<button class="btn btn-sm btn-primary vote" data-step-id="' + id + '">Enregistrer le vote' + proxyLabel + '</button>'
        + '</div>';
}

function updateTemplateOptions(stepId) {
    const decision = root.querySelector('#validation-decision-' + stepId)?.value;
    const select = root.querySelector('#validation-template-' + stepId);
    if (!select) return;
    select.querySelectorAll('option[data-decision]').forEach((option) => {
        option.hidden = option.dataset.decision !== decision;
    });
    select.value = '';
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
    const requirements = data.commentRequirements || { accepter: false, refuser: true };
    const templates = data.commentTemplates || { accepter: [], refuser: [] };
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
            + dueAt + comment + voteControls(step, requirements, templates) + '</li>';
    }).join('');
    root.innerHTML = '<div class="mb-3"><p class="mb-1"><strong>État : '
        + escape(stateLabels[data.run.state] || data.run.state)
        + '</strong></p><div class="progress" role="progressbar" aria-label="Avancement de la validation" aria-valuenow="'
        + progress.percentage + '" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: '
        + progress.percentage + '%">' + progress.percentage + '%</div></div><small class="text-muted">'
        + progress.completed + ' rôle(s) ayant voté sur ' + progress.total + '</small></div><ul class="list-group">'
        + steps + '</ul>';
    root.querySelectorAll('.validation-decision').forEach((select) => {
        updateTemplateOptions(select.dataset.stepId);
        select.addEventListener('change', () => updateTemplateOptions(select.dataset.stepId));
    });
    root.querySelectorAll('.validation-template').forEach((select) => select.addEventListener('change', () => {
        const stepId = select.dataset.stepId;
        const option = select.selectedOptions[0];
        const textarea = root.querySelector('#validation-comment-' + stepId);
        if (textarea && option?.dataset.decision) textarea.value = option.value;
    }));
    root.querySelectorAll('.vote').forEach((button) => button.addEventListener('click', async () => {
        const stepId = Number(button.dataset.stepId);
        const decision = root.querySelector('#validation-decision-' + stepId)?.value || 'accepter';
        const comment = root.querySelector('#validation-comment-' + stepId)?.value.trim() || '';
        if (requirements[decision] && comment === '') {
            window.alert(decision === 'refuser'
                ? 'Un commentaire est obligatoire lors d’un refus.'
                : 'Un commentaire est obligatoire lors d’une acceptation.');

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

document.querySelector('#reset-validation')?.addEventListener('click', async () => {
    if (!window.confirm('Annuler et supprimer définitivement les étapes et votes du cycle ?')) return;
    try {
        await request('/api/applicationforms/' + id + '/validation/reset.json', { method: 'POST', body: JSON.stringify({ _csrfToken: token }) });
        window.location.reload();
    } catch (error) {
        window.alert(error.message);
    }
});
