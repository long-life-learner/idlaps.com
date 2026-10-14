const confirmationDialog = document.getElementById('cms-confirm');
let confirmedForm = null;
let pendingSubmission = null;
document.addEventListener('submit', event => {
    const form = event.target;
    const question = event.submitter?.dataset.confirm || form.dataset.confirm;
    if (!question || confirmedForm === form) { confirmedForm = null; return; }
    event.preventDefault();
    pendingSubmission = {form, submitter: event.submitter};
    document.getElementById('confirm-message').textContent = question;
    confirmationDialog.showModal();
}, true);
document.querySelector('[data-dialog-cancel]')?.addEventListener('click', () => { pendingSubmission = null; confirmationDialog.close(); });
document.querySelector('[data-dialog-confirm]')?.addEventListener('click', () => {
    const pending = pendingSubmission;
    pendingSubmission = null;
    confirmationDialog.close();
    if (pending) { confirmedForm = pending.form; pending.form.requestSubmit(pending.submitter); }
});
const editor = document.querySelector('[data-editor]');
let dirty = false;
if (editor) {
    editor.addEventListener('input', () => { dirty = true; document.getElementById('save-hint').textContent = 'Modifikasi belum disimpan.'; });
    editor.querySelector('[name="event_date"]').addEventListener('change', event => {
        if (event.target.value) editor.querySelector('[name="year"]').value = event.target.value.slice(0, 4);
    });
    editor.addEventListener('submit', event => {
        if (event.defaultPrevented) return;
        dirty = false;
        document.getElementById('save-hint').textContent = 'Menyimpan… Tunggu hingga halaman selesai dimuat.';
    });
    window.addEventListener('beforeunload', event => {
        if (dirty) { event.preventDefault(); event.returnValue = ''; }
    });
    const photos = document.getElementById('photos');
    let previews = [];
    photos.addEventListener('change', () => {
        previews.forEach(url => URL.revokeObjectURL(url));
        previews = [];
        const container = document.getElementById('upload-previews');
        container.replaceChildren();
        const files = [...photos.files];
        const invalid = files.length > 10 || files.some(file => file.size > 8 * 1024 * 1024);
        photos.setCustomValidity(invalid ? 'Pilih maksimal 10 foto, masing-masing tidak lebih dari 8 MB.' : '');
        document.getElementById('upload-feedback').textContent = invalid ? photos.validationMessage : `${files.length} foto dipilih. Foto akan diunggah saat Anda menyimpan.`;
        files.slice(0, 10).forEach(file => {
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) return;
            const img = document.createElement('img');
            const url = URL.createObjectURL(file);
            previews.push(url); img.src = url; img.alt = file.name; container.append(img);
        });
    });
}
