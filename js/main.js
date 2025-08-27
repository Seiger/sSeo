window.sSeo = window.sSeo || {};

/**
 * Auto-initialize lucide after load
 */
window.addEventListener('DOMContentLoaded', () => {
    if (window.lucide?.createIcons) {
        lucide.createIcons();
    } else {
        const lucideScript = document.querySelector('script[src*="lucide"]');
        if (lucideScript) {
            lucideScript.addEventListener('load', () => {
                lucide.createIcons();
                document.dispatchEvent(new Event('lucide:ready'));
            });
        }
    }
});

/**
 * Event handler
 */
document.addEventListener("click", async function(e) {
    if (e.target) {
        // Search
        if (Boolean(e.target.closest('.js_search')?.classList.contains('js_search'))) {
            e.preventDefault();
            runSearch(e.target.closest('.js_search').parentElement.querySelector('[name="s"]').value);
        }
        // Change value for Toggle button
        if (Boolean(e.target?.closest('input[type="checkbox"].peer'))) {
            changeToggleValue(e.target);
        }
        // Ordering Table
        if (Boolean(e.target.closest('[data-by]')?.hasAttribute("data-by"))) {
            e.preventDefault();
            let clickedElement = e.target.closest('[data-by]');
            if ('disabled' in e.target) e.target.disabled = true;
            let attrValue = clickedElement.getAttribute('data-by').trim().toLowerCase() || '';
            setOrder(attrValue);
            if ('disabled' in e.target) e.target.disabled = false;
        }
    }
});

document.addEventListener('DOMContentLoaded', function () {
    /**
     * Per Page selector
     */
    const selector = document.getElementById('perPageSelector');
    if (selector) {
        const cookieName = 'sSeoPerPage';

        const getCookie = (name) => {
            const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
            return match ? match[2] : null;
        };

        const setCookie = (name, value, days = 365) => {
            const expires = new Date(Date.now() + days * 864e5).toUTCString();
            document.cookie = `${name}=${value}; expires=${expires}; path=/`;
        };

        const saved = getCookie(cookieName) || '150';
        selector.value = saved;

        selector.addEventListener('change', function () {
            setCookie(cookieName, this.value);
            window.location.reload();
        });
    }

    /**
    * Search
    */
    const searchInput = document.querySelector('[name="s"]');
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                runSearch(searchInput.value);
            }
        });
    }

    /**
     * Placeholders
     */
    const elements = document.querySelectorAll('.placeholders');
    if (elements) {
        elements.forEach(el => {
            el.innerHTML = el.innerHTML.replace(
                /(\[\([^)]+\)\]|\[\*[^*]+\*\])/g,
                match => `<code class="placeholder">${match}</code>`
            );
        });
    }
});

/**
 * Handle pinning and hover behavior.
 */
window.sSeo.sPinner = function sPinner(key) {
    const saved = localStorage.getItem(key) === 'true';
    return {
        pinned: saved,
        open: saved,
        skipLeave: false,
        togglePin() {
            this.pinned = !this.pinned;
            this.open = this.pinned;
            this.skipLeave = true;
            setTimeout(() => this.skipLeave = false, 50);
            localStorage.setItem(key, this.pinned);
            window.sSeo.queueLucide();
        },
        handleEnter() {
            if (!this.pinned) {
                this.open = true;
                window.sSeo.queueLucide();
            }
        },
        handleLeave() {
            if (this.skipLeave) return;
            if (!this.pinned) {
                this.open = false;
                window.sSeo.queueLucide();
            }
        },
    }
}

/**
 * Queue Lucide icon rendering.
 */
window.sSeo.queueLucide = function queueLucide() {
    if (window.lucide?.createIcons) {
        lucide.createIcons();
    } else {
        document.addEventListener('lucide:ready', () => {
            lucide.createIcons();
        }, {once: true});
    }
}

/**
 * Makes a Fetch API call with robust error handling.
 *
 * @param {string} url - The endpoint URL.
 * @param {FormData|object|null} form - The form data or null.
 * @param {string} [method='POST'] - HTTP method.
 * @param {string} [type='json'] - Response type: json, text, blob, formData, arrayBuffer.
 * @returns {Promise<any|null>} - Parsed response or null on failure.
 */
window.sSeo.callApi = async function callApi(url, form = null, method = 'POST', type = 'json', headers = {}) {
    try {
        const finalHeaders = {
            'X-Requested-With': 'XMLHttpRequest',
            ...headers
        };

        let body = form;

        if (form instanceof FormData && ['DELETE', 'PUT'].includes(method.toUpperCase())) {
            const jsonObject = {};
            for (const [key, value] of form.entries()) {
                jsonObject[key] = value;
            }
            body = JSON.stringify(jsonObject);
            finalHeaders['Content-Type'] = 'application/json';
        }

        const response = await fetch(url, {
            method,
            cache: 'no-store',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            body
        });

        if (!response.ok) {
            if (response.status === 404) throw new Error('404, Not Found');
            if (response.status === 500) throw new Error('500, Internal Server Error');
            throw new Error(`HTTP error: ${response.status}`);
        }

        switch (type) {
            case 'text': return await response.text();
            case 'json': return await response.json();
            case 'blob': return await response.blob();
            case 'formData': return await response.formData();
            case 'arrayBuffer': return await response.arrayBuffer();
            default: throw new Error('Unsupported response type');
        }
    } catch (error) {
        console.error('Request failed:', error);
        return null;
    }
}

function submitForm(selector) {
    const form = document.querySelector(selector);
    if (form) {
        documentDirty = false;
        form.submit();
    }
}

function runSearch(s) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.delete('s');
    if (s.trim().length > 0) {
        urlParams.append('s', s.trim());
    }
    window.location.href = window.location.pathname + '?' + urlParams.toString();
}

function changeToggleValue(checkbox) {
    const targetId = checkbox.dataset.target;
    const hiddenInput = document.getElementById(targetId);
    if (hiddenInput) {
        hiddenInput.value = checkbox.checked ? '1' : '0';
    }
    documentDirty = true;
}

function setOrder(b = '') {
    if (b.trim().length > 0) {
        const urlParams = new URLSearchParams(window.location.search);
        let checkB = urlParams.get('b') === b;
        let checkD = urlParams.get('d') !== 'desc';

        urlParams.delete('b');
        urlParams.delete('d');
        urlParams.append('b', b.trim());
        if (checkB && checkD) {
            urlParams.append('d', 'desc');
        }

        window.location.href = window.location.pathname + '?' + urlParams.toString();
    }
}
