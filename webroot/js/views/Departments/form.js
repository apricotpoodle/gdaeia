import { FlashManager } from '../../core/FlashManager.js';
import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { TabulatorMutationClient } from '../../core/Tabulator/TabulatorMutationClient.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';

const tableElement = document.querySelector('#department-cgr-codes-table');
if (tableElement) {
    const departmentId = document.querySelector('#department-id')?.value || window.location.pathname.split('/').pop();
    const table = TabulatorFactory.createDepartmentCgrCodesGrid('#department-cgr-codes-table', departmentId);
    const client = new TabulatorMutationClient();
    const form = document.querySelector('#cgr-code-form');

    const reset = () => {
        form.reset();
        form.querySelector('[name="id"]').value = '';
        form.querySelector('[name="active"]').checked = true;
        form.classList.add('d-none');
    };
    const edit = (code) => {
        form.querySelector('[name="id"]').value = code.id;
        form.querySelector('[name="type"]').value = code.type;
        form.querySelector('[name="code"]').value = code.code;
        form.querySelector('[name="label"]').value = code.label;
        form.querySelector('[name="active"]').checked = Boolean(code.active);
        form.classList.remove('d-none');
    };

    document.querySelector('#add-cgr-code')?.addEventListener('click', () => { reset(); form.classList.remove('d-none'); });
    document.querySelector('#cancel-cgr-code')?.addEventListener('click', reset);
    globalTabulatorObserver.subscribe('#department-cgr-codes-table:action:edit', edit);
    globalTabulatorObserver.subscribe('#department-cgr-codes-table:action:deactivate', async (code) => {
        if (!window.confirm(`Désactiver le code « ${code.code} » ?`)) return;
        try {
            await client.send({ url: `/api/departments/${departmentId}/cgr-codes/${code.id}/deactivate.json` });
            await table.replaceData();
            FlashManager.success('Code CGR désactivé.');
        } catch (error) { FlashManager.error(error.message); }
    });
    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const data = Object.fromEntries(new FormData(form));
        data.active = form.querySelector('[name="active"]').checked;
        const id = data.id;
        try {
            await client.send({
                url: id ? `/api/departments/${departmentId}/cgr-codes/${id}.json` : `/api/departments/${departmentId}/cgr-codes/add.json`,
                body: JSON.stringify(data),
                headers: { 'Content-Type': 'application/json' },
            });
            reset();
            await table.replaceData();
            FlashManager.success('Code CGR enregistré.');
        } catch (error) { FlashManager.error(error.message); }
    });
}
