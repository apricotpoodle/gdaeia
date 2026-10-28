/** Met à jour l'aperçu du salaire de base sans remplacer le calcul serveur. */
const form = document.getElementById('applicationform-main-form');
const period = document.getElementById('period-id');
const amount = document.getElementById('grossremuneration');
const workingDays = document.getElementById('working-days');
const salaryBase = document.getElementById('salary-base');

if (form && period && amount && salaryBase) {
    let requestId = 0;

    const calculate = async () => {
        if (!period.value || !amount.value || (workingDays && workingDays.value === '')) {
            salaryBase.value = '';
            return;
        }

        const currentRequest = ++requestId;
        const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
        const response = await fetch('/api/applicationforms/calculate-salary.json', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                period_id: period.value,
                grossremuneration: amount.value,
                working_days: workingDays?.value || null,
            }),
        });
        const payload = await response.json();
        if (currentRequest !== requestId) return;

        salaryBase.value = response.ok && payload.success ? payload.salary_base : '';
    };

    [period, amount, workingDays].filter(Boolean).forEach((element) => {
        element.addEventListener('input', calculate);
        element.addEventListener('change', calculate);
    });
}
