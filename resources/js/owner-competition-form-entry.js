import { initDisciplineCombobox } from './owner-competition-discipline-combobox.js';

document.querySelectorAll('[data-discipline-combobox]').forEach((root) => {
    initDisciplineCombobox(root);
});
