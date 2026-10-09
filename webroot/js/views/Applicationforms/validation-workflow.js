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
    const proxyLabel = step.is_proxy_vote ? ' par outrepassement' : '';
    const id = escape(step.id);
    const templateOptions = ['accepter', 'refuser'].flatMap((decision) => templates[decision].map((template) =>
        '<option value="' + escape(template.content) + '" data-decision="' + decision + '">' + escape(template.label) + '</option>'
    )).join('');

    return '<div class="mt-2 border-top pt-2">'
        + '<span class="form-label d-block">Décision</span>'
        + '<div class="btn-group w-100 mb-2 validation-decision-group" role="group" aria-label="Décision de validation" data-step-id="' + id + '" data-decision="accepter">'
        + '<button type="button" class="btn btn-lg btn-success validation-decision-button" data-decision="accepter" aria-pressed="true">Accepter' + proxyLabel + '</button>'
        + '<button type="button" class="btn btn-lg btn-outline-danger validation-decision-button" data-decision="refuser" aria-pressed="false">Refuser' + proxyLabel + '</button>'
        + '</div>'
        + '<label class="form-label" for="validation-template-' + id + '">Commentaire prédéfini</label>'
        + '<select class="form-select form-select-sm mb-2 validation-template" id="validation-template-' + id + '" data-step-id="' + id + '"><option value="">Saisie libre</option>' + templateOptions + '</select>'
        + '<label class="form-label" for="validation-comment-' + id + '">Commentaire de vote</label>'
        + '<textarea class="form-control form-control-sm mb-2" id="validation-comment-' + id + '" rows="3" placeholder="Commentaire' + proxyLabel + '"></textarea>'
        + '<button class="btn btn-sm btn-primary vote" data-step-id="' + id + '">Enregistrer le vote' + proxyLabel + '</button>'
        + '</div>';
}

function updateTemplateOptions(stepId, decision) {
    const select = root.querySelector('#validation-template-' + stepId);
    if (!select) return;
    select.querySelectorAll('option[data-decision]').forEach((option) => {
        option.hidden = option.dataset.decision !== decision;
    });
    select.value = '';
}

function updateDecisionButtons(group, decision) {
    group.dataset.decision = decision;
    group.querySelectorAll('.validation-decision-button').forEach((button) => {
        const selected = button.dataset.decision === decision;
        const accepter = button.dataset.decision === 'accepter';
        button.classList.toggle('btn-success', accepter && selected);
        button.classList.toggle('btn-outline-success', accepter && !selected);
        button.classList.toggle('btn-danger', !accepter && selected);
        button.classList.toggle('btn-outline-danger', !accepter && !selected);
        button.setAttribute('aria-pressed', selected ? 'true' : 'false');
    });
    updateTemplateOptions(group.dataset.stepId, decision);
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
    const scrollTop = root.scrollTop;
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
    const legend = '<div class="validation-legend mb-3" aria-label="Légende des états de validation">'
        + '<span class="validation-legend-item validation-legend--blocked">Bloquée</span>'
        + '<span class="validation-legend-item validation-legend--upcoming">À venir</span>'
        + '<span class="validation-legend-item validation-legend--pending">En attente</span>'
        + '<span class="validation-legend-item validation-legend--accepted">Acceptée</span>'
        + '<span class="validation-legend-item validation-legend--rejected">Refusée</span>'
        + '</div>';
    const steps = data.steps.map((step) => {
        const dueAt = step.state === 'en_attente' && step.due_at
            ? '<small class="text-muted">Échéance : ' + escape(new Date(step.due_at).toLocaleString('fr-FR')) + '</small>'
            : '';
        const completedAt = ['acceptee', 'refusee'].includes(step.state) && step.completed_at
            ? '<small class="text-muted">Vote enregistré le ' + escape(new Date(step.completed_at).toLocaleString('fr-FR')) + '</small>'
            : '';
        const comment = step.comment
            ? '<p class="mb-0 mt-2"><small>Commentaire : ' + escape(step.comment) + '</small></p>'
            : '';
        const visualState = step.is_blocked ? 'blocked' : step.state === 'a_venir' ? 'upcoming' : step.state;
        const blockedDetails = step.is_blocked
            ? '<small class="validation-blocked-details">Blocage détecté le '
                + escape(new Date(step.blocked_since).toLocaleDateString('fr-FR'))
                + ' — ' + escape(step.blocked_business_days) + ' jour(s) ouvré(s)</small>'
            : '';
        const stateBadge = '<span class="validation-state-badge validation-state-badge--' + visualState + '">'
            + escape(step.is_blocked ? 'Bloquée' : stateLabels[step.state] || step.state)
            + '</span>';

        return '<li class="list-group-item validation-step validation-step--' + visualState + '">'
            + '<div class="d-flex justify-content-start align-items-start gap-2">'
            + stateBadge
            + '<span>Séquence ' + escape(step.sequence_number) + ' — ' + escape(step.role?.name || 'Rôle') + '</span></div>'
            + blockedDetails + dueAt + completedAt + comment + voteControls(step, requirements, templates) + '</li>';
    }).join('');
    root.innerHTML = legend + '<div class="mb-3"><p class="mb-1"><strong>État : '
        + escape(stateLabels[data.run.state] || data.run.state)
        + '</strong></p><div class="progress" role="progressbar" aria-label="Avancement de la validation" aria-valuenow="'
        + progress.percentage + '" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar" style="width: '
        + progress.percentage + '%">' + progress.percentage + '%</div></div><small class="text-muted">'
        + progress.completed + ' rôle(s) ayant voté sur ' + progress.total + '</small></div><ul class="list-group">'
        + steps + '</ul>';
    root.scrollTop = scrollTop;
    root.querySelectorAll('.validation-decision-group').forEach((group) => {
        updateDecisionButtons(group, group.dataset.decision || 'accepter');
        group.querySelectorAll('.validation-decision-button').forEach((button) => button.addEventListener('click', () => {
            updateDecisionButtons(group, button.dataset.decision);
        }));
    });
    root.querySelectorAll('.validation-template').forEach((select) => select.addEventListener('change', () => {
        const stepId = select.dataset.stepId;
        const option = select.selectedOptions[0];
        const textarea = root.querySelector('#validation-comment-' + stepId);
        if (textarea && option?.dataset.decision) textarea.value = option.value;
    }));
    root.querySelectorAll('.vote').forEach((button) => button.addEventListener('click', async () => {
        const stepId = Number(button.dataset.stepId);
        const decision = root.querySelector('.validation-decision-group[data-step-id="' + stepId + '"]')?.dataset.decision || 'accepter';
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
                body: JSON.stringify({
                    step_id: stepId,
                    decision,
                    comment,
                    override: Boolean(data.steps.find((step) => Number(step.id) === stepId)?.is_proxy_vote),
                    _csrfToken: token,
                }),
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
