const form = document.querySelector('#validation-circuit-rule-form');
const grid = document.querySelector('#validation-circuit-rules-grid');

async function refresh() {
    const response = await fetch('/api/validation-circuit-rules.json');
    const payload = await response.json();
    grid.innerHTML = (payload.data ?? []).map((rule) => (
        '<div class="border rounded p-2 mb-2">'
        + (rule.department?.name ?? rule.department_id) + ' — '
        + (rule.contracttype?.name ?? rule.contracttype_id) + ' — '
        + (rule.final_role?.name ?? rule.final_role_id)
        + '</div>'
    )).join('');
}

form?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const response = await fetch('/api/validation-circuit-rules/add.json', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(Object.fromEntries(new FormData(form))),
    });
    if ((await response.json()).success) {
        form.reset();
        await refresh();
    }
});

refresh();
