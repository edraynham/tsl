/**
 * Owner competition form: discipline search + listbox (static JSON options).
 *
 * @param {HTMLElement} root
 */
export function initDisciplineCombobox(root) {
    const jsonEl = root.querySelector('[data-discipline-json]');
    const hidden = root.querySelector('[data-discipline-hidden]');
    const input = root.querySelector('[data-discipline-input]');
    const list = root.querySelector('[data-discipline-suggestions]');
    const clearBtn = root.querySelector('[data-discipline-clear]');
    if (!jsonEl || !hidden || !input || !list) {
        return;
    }

    let options = [];
    try {
        options = JSON.parse(jsonEl.textContent || '[]');
    } catch {
        return;
    }
    if (!Array.isArray(options)) {
        return;
    }

    const noMatchText = root.dataset.i18nNoMatch || 'No matching disciplines';
    const listId = list.id || 'discipline-suggestions-list';

    /** @param {{ id: number, code?: string|null, name: string }} d */
    function formatLabel(d) {
        const code = (d.code || '').trim();

        return code ? `${code} — ${d.name}` : d.name;
    }

    /** @param {{ id: number, code?: string|null, name: string }} d */
    function matches(d, q) {
        const t = q.trim().toLowerCase();
        if (t === '') {
            return true;
        }
        const code = (d.code || '').toLowerCase();
        const name = (d.name || '').toLowerCase();

        return code.includes(t) || name.includes(t) || formatLabel(d).toLowerCase().includes(t);
    }

    let committedId = hidden.value === '' ? '' : String(hidden.value);
    let committedLabel = '';
    if (committedId) {
        const found = options.find((o) => String(o.id) === committedId);
        committedLabel = found ? formatLabel(found) : '';
        if (!found) {
            committedId = '';
            hidden.value = '';
        }
    }

    let activeIndex = -1;
    let filtered = [];

    function syncInputFromCommitted() {
        input.value = committedLabel;
    }

    syncInputFromCommitted();

    function isOpen() {
        return !list.classList.contains('hidden');
    }

    function setOpen(open) {
        list.classList.toggle('hidden', !open);
        input.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function hide() {
        setOpen(false);
        activeIndex = -1;
        list.querySelectorAll('[role="option"]').forEach((el) => el.removeAttribute('aria-selected'));
    }

    function renderList() {
        const q = input.value;
        filtered = options.filter((d) => matches(d, q));
        list.innerHTML = '';
        activeIndex = filtered.length ? 0 : -1;

        filtered.forEach((d, i) => {
            const li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.id = `${listId}-opt-${i}`;
            li.className =
                'cursor-pointer px-4 py-2.5 text-left text-sm text-stone-800 hover:bg-cream-dark/80 aria-selected:bg-cream-dark/80';
            li.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
            li.dataset.id = String(d.id);
            li.textContent = formatLabel(d);
            li.addEventListener('mousedown', (e) => {
                e.preventDefault();
            });
            li.addEventListener('click', () => {
                selectById(String(d.id));
                hide();
                input.focus();
            });
            list.appendChild(li);
        });

        if (filtered.length === 0) {
            const li = document.createElement('li');
            li.className = 'px-4 py-2.5 text-sm text-stone-500';
            li.setAttribute('role', 'presentation');
            li.textContent = noMatchText;
            list.appendChild(li);
        }

        setOpen(true);
        highlightActive();
    }

    function highlightActive() {
        const opts = list.querySelectorAll('[role="option"]');
        opts.forEach((el, i) => {
            const on = i === activeIndex;
            el.setAttribute('aria-selected', on ? 'true' : 'false');
            el.classList.toggle('bg-cream-dark/80', on);
        });
        if (activeIndex >= 0 && opts[activeIndex]) {
            input.setAttribute('aria-activedescendant', opts[activeIndex].id);
        } else {
            input.removeAttribute('aria-activedescendant');
        }
    }

    /** @param {string} id */
    function selectById(id) {
        const d = options.find((o) => String(o.id) === id);
        if (!d) {
            return;
        }
        committedId = id;
        committedLabel = formatLabel(d);
        hidden.value = id;
        input.value = committedLabel;
    }

    function clearSelection() {
        committedId = '';
        committedLabel = '';
        hidden.value = '';
        input.value = '';
        hide();
    }

    input.addEventListener('focus', () => {
        renderList();
    });

    input.addEventListener('input', () => {
        renderList();
    });

    input.addEventListener('keydown', (e) => {
        if (!isOpen() && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
            renderList();
            e.preventDefault();

            return;
        }
        if (!isOpen()) {
            return;
        }

        if (e.key === 'Escape') {
            hide();
            syncInputFromCommitted();
            e.preventDefault();

            return;
        }

        const opts = list.querySelectorAll('[role="option"]');
        if (opts.length === 0) {
            return;
        }

        if (e.key === 'ArrowDown') {
            activeIndex = Math.min(activeIndex + 1, opts.length - 1);
            highlightActive();
            e.preventDefault();
        } else if (e.key === 'ArrowUp') {
            activeIndex = Math.max(activeIndex - 1, 0);
            highlightActive();
            e.preventDefault();
        } else if (e.key === 'Enter') {
            if (activeIndex >= 0 && filtered[activeIndex]) {
                selectById(String(filtered[activeIndex].id));
                hide();
            }
            e.preventDefault();
        }
    });

    input.addEventListener('blur', () => {
        setTimeout(() => {
            if (!root.contains(document.activeElement)) {
                hide();
                if (committedId) {
                    syncInputFromCommitted();
                } else if (input.value.trim() !== '') {
                    input.value = '';
                }
            }
        }, 120);
    });

    clearBtn?.addEventListener('mousedown', (e) => {
        e.preventDefault();
    });
    clearBtn?.addEventListener('click', () => {
        clearSelection();
        input.focus();
    });

    document.addEventListener('click', (e) => {
        if (!root.contains(e.target)) {
            hide();
        }
    });

    list.setAttribute('role', 'listbox');
    if (!list.id) {
        list.id = listId;
    }
    input.setAttribute('aria-controls', list.id);
}
