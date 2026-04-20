/**
 * @param {string} dateStr YYYY-MM-DD
 * @param {string} timeStr e.g. 9:00, 14:30, 2:30pm
 * @returns {Date|null}
 */
function parseSquadDateTime(dateStr, timeStr) {
    if (!dateStr || !timeStr) {
        return null;
    }
    const time = timeStr.trim().replace(/\./g, ':');
    if (!time) {
        return null;
    }

    const isPm = /\bpm\b\s*$/i.test(time) || time.toLowerCase().endsWith('pm');
    const isAm = /\bam\b\s*$/i.test(time) || time.toLowerCase().endsWith('am');
    const core = time.replace(/\s*(a\.?m\.?|p\.?m\.?)\s*$/i, '').trim();
    const match = core.match(/^(\d{1,2})\s*:\s*(\d{1,2})(?:\s*:\s*(\d{1,2}))?$/);
    if (!match) {
        return null;
    }

    let h = parseInt(match[1], 10);
    const m = parseInt(match[2], 10);
    if (Number.isNaN(h) || Number.isNaN(m) || h > 23 || m > 59) {
        return null;
    }

    if (isAm || isPm) {
        if (isAm && h === 12) {
            h = 0;
        } else if (isPm && h < 12) {
            h += 12;
        }
    }

    const parts = dateStr.split('-').map((n) => parseInt(n, 10));
    if (parts.length !== 3 || parts.some((n) => Number.isNaN(n))) {
        return null;
    }
    const [Y, Mo, D] = parts;

    return new Date(Y, Mo - 1, D, h, m, 0, 0);
}

/**
 * @param {Date} d
 * @returns {string}
 */
function formatDateInput(d) {
    const y = d.getFullYear();
    const mo = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');

    return `${y}-${mo}-${day}`;
}

/**
 * @param {Date} d
 * @returns {string}
 */
function formatTimeInput(d) {
    const h = d.getHours();
    const m = String(d.getMinutes()).padStart(2, '0');

    return `${h}:${m}`;
}

/**
 * Dynamic squad rows for owner competition squads form.
 */
export function initOwnerCompetitionSquads(root) {
    const list = root.querySelector('[data-squad-rows]');
    const template = root.querySelector('[data-squad-row-template]');
    const addBtn = root.querySelector('[data-add-squad-row]');
    if (!list || !template || !addBtn) {
        return;
    }

    let nextIndex = parseInt(root.dataset.nextSquadIndex || '0', 10) || 0;

    function renumberSquadLabels() {
        list.querySelectorAll('[data-squad-label-index]').forEach((el, i) => {
            el.textContent = String(i + 1);
        });
    }

    function removeButtonsState() {
        const rows = list.querySelectorAll('li[data-squad-row]');
        const showRemove = rows.length > 1;
        rows.forEach((li) => {
            const btn = li.querySelector('[data-remove-squad-row]');
            if (btn) {
                btn.classList.toggle('hidden', !showRemove);
                btn.disabled = !showRemove;
            }
        });
    }

    function bindRow(li) {
        li.querySelector('[data-remove-squad-row]')?.addEventListener('click', () => {
            if (list.querySelectorAll('li[data-squad-row]').length <= 1) {
                return;
            }
            li.remove();
            renumberSquadLabels();
            removeButtonsState();
        });
    }

    list.querySelectorAll('li[data-squad-row]').forEach((li) => bindRow(li));
    renumberSquadLabels();
    removeButtonsState();

    addBtn.addEventListener('click', () => {
        const idx = nextIndex;
        nextIndex += 1;
        const html = template.innerHTML.replaceAll('__INDEX__', String(idx));
        const wrapper = document.createElement('ul');
        wrapper.innerHTML = html.trim();
        const li = wrapper.firstElementChild;
        if (!li) {
            return;
        }
        list.appendChild(li);

        const prev = li.previousElementSibling;
        if (prev?.matches('li[data-squad-row]')) {
            const prevDate = prev.querySelector('input[type="date"][name*="[start_date]"]');
            const prevTime = prev.querySelector('input[name*="[start_time]"]');
            const prevSize = prev.querySelector('select[name*="[max_participants]"]');
            const newDate = li.querySelector('input[type="date"][name*="[start_date]"]');
            const newTime = li.querySelector('input[name*="[start_time]"]');
            const newSize = li.querySelector('select[name*="[max_participants]"]');

            if (prevSize && newSize) {
                newSize.value = prevSize.value;
            }

            const parsed = parseSquadDateTime(prevDate?.value ?? '', prevTime?.value ?? '');
            if (parsed && newDate && newTime && !Number.isNaN(parsed.getTime())) {
                const next = new Date(parsed.getTime() + 10 * 60 * 1000);
                newDate.value = formatDateInput(next);
                newTime.value = formatTimeInput(next);
            } else {
                if (prevDate && newDate) {
                    newDate.value = prevDate.value;
                }
                if (prevTime && newTime) {
                    newTime.value = prevTime.value;
                }
            }
        }

        bindRow(li);
        renumberSquadLabels();
        removeButtonsState();
    });
}
