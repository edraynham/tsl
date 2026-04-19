/**
 * Homepage hero search: fetch matching grounds and show a dropdown (Open-Meteo-free).
 */
export function initHomeSearchAutocomplete() {
    const form = document.querySelector('[data-home-search-autocomplete]');
    if (!form) {
        return;
    }

    const url = form.getAttribute('data-suggestions-url');
    const input = form.querySelector('input[name="q"]');
    const fieldWrap = form.querySelector('[data-home-search-field]');
    if (!url || !input || !fieldWrap) {
        return;
    }

    const listId = 'home-search-suggestions';
    let debounceTimer = 0;
    let activeIndex = -1;

    const list = document.createElement('ul');
    list.id = listId;
    list.setAttribute('role', 'listbox');
    list.setAttribute('aria-label', 'Matching shooting grounds');
    list.className =
        'absolute left-0 right-0 top-full z-30 mt-1 max-h-64 overflow-auto rounded-xl border border-stone-200 bg-white py-1 text-left shadow-lg ring-1 ring-stone-900/5 hidden';
    fieldWrap.appendChild(list);

    const items = [];

    function hide() {
        list.classList.add('hidden');
        list.innerHTML = '';
        items.length = 0;
        activeIndex = -1;
        input.removeAttribute('aria-activedescendant');
    }

    function showLoading() {
        list.innerHTML = '';
        items.length = 0;
        const li = document.createElement('li');
        li.className = 'px-4 py-3 text-sm text-stone-500';
        li.textContent = 'Searching…';
        list.appendChild(li);
        list.classList.remove('hidden');
    }

    function render(suggestions) {
        list.innerHTML = '';
        items.length = 0;

        if (!suggestions.length) {
            const li = document.createElement('li');
            li.className = 'px-4 py-3 text-sm text-stone-500';
            li.textContent = 'No matching grounds';
            list.appendChild(li);
            list.classList.remove('hidden');

            return;
        }

        activeIndex = -1;

        suggestions.forEach((s, i) => {
            const id = `${listId}-opt-${i}`;
            const li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.id = id;

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className =
                'flex w-full flex-col gap-0.5 px-4 py-2.5 text-left text-sm transition hover:bg-cream-dark/80 focus:bg-cream-dark/80 focus:outline-none';
            const nameSpan = document.createElement('span');
            nameSpan.className = 'font-medium text-stone-900';
            nameSpan.textContent = s.name;
            btn.appendChild(nameSpan);
            if (s.subtitle) {
                const sub = document.createElement('span');
                sub.className = 'text-xs text-stone-500';
                sub.textContent = s.subtitle;
                btn.appendChild(sub);
            }

            btn.addEventListener('click', () => {
                window.location.href = s.url;
            });

            li.appendChild(btn);
            list.appendChild(li);
            items.push({ id, btn, url: s.url });
        });

        list.classList.remove('hidden');
    }

    function highlightActive() {
        items.forEach((item, i) => {
            const on = activeIndex >= 0 && i === activeIndex;
            item.btn.classList.toggle('bg-cream-dark/80', on);
        });
        if (activeIndex >= 0 && items[activeIndex]) {
            input.setAttribute('aria-activedescendant', items[activeIndex].id);
        } else {
            input.removeAttribute('aria-activedescendant');
        }
    }

    async function fetchSuggestions(term) {
        if (term.length < 2) {
            hide();

            return;
        }

        showLoading();

        try {
            const res = await fetch(`${url}?q=${encodeURIComponent(term)}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) {
                hide();

                return;
            }
            const data = await res.json();
            render(Array.isArray(data.suggestions) ? data.suggestions : []);
        } catch {
            hide();
        }
    }

    input.setAttribute('autocomplete', 'off');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-controls', listId);
    input.setAttribute('aria-expanded', 'false');

    input.addEventListener('input', () => {
        window.clearTimeout(debounceTimer);
        const term = input.value.trim();
        input.setAttribute('aria-expanded', term.length >= 2 ? 'true' : 'false');
        debounceTimer = window.setTimeout(() => {
            fetchSuggestions(term);
        }, 250);
    });

    input.addEventListener('keydown', (e) => {
        if (!list.classList.contains('hidden') && items.length) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                highlightActive();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, -1);
                highlightActive();
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                window.location.href = items[activeIndex].url;
            } else if (e.key === 'Escape') {
                hide();
                input.setAttribute('aria-expanded', 'false');
            }
        }
    });

    document.addEventListener('click', (e) => {
        if (!form.contains(e.target)) {
            hide();
            input.setAttribute('aria-expanded', 'false');
        }
    });

    form.addEventListener('submit', () => {
        hide();
    });
}
