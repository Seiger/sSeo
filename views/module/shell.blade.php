@php
    $manager = app(\EvoUI\Support\ManagerContext::class);
    $theme = $manager->theme();
    $themeMode = $manager->themeMode($theme);
    $themeClasses = $manager->themeClasses($theme);
    $themeBackground = $manager->themeBackground($theme);
    $moduleTitle = __('sSeo::global.module_title') !== 'sSeo::global.module_title' ? __('sSeo::global.module_title') : __('sSeo::global.title');
    $legacyDashboardTitle = __('sSeo::global.dashboard') . ' ' . $moduleTitle;
@endphp
<!doctype html>
<html
    class="evo-ui-page {{ $themeClasses }}"
    lang="{{ str_replace('_', '-', app()->getLocale() ?: 'uk') }}"
    data-theme="{{ $theme }}"
    data-theme-mode="{{ $themeMode }}"
    style="background-color: var(--evo-ui-bg, {{ $themeBackground }})"
>
<head>
    <meta charset="utf-8">
    <meta name="color-scheme" content="{{ $themeMode === 'dark' ? 'dark light' : 'light dark' }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $moduleTitle }}</title>
    @include('evo::partials.assets')
</head>
<body
    class="evo-ui-page {{ $themeClasses }}"
    data-theme="{{ $theme }}"
    data-theme-mode="{{ $themeMode }}"
    style="background-color: var(--evo-ui-bg, {{ $themeBackground }})"
>
    <div
        class="evo-ui {{ $themeClasses }}"
        data-evo-ui-root
        data-theme="{{ $theme }}"
        data-theme-mode="{{ $themeMode }}"
    >
        <livewire:sseo.module-panel
            :tabs="$tabs"
            :active-tab="$activeTab"
            :context="$context"
        />
    </div>
    <script>
        (() => {
            const moduleTitle = @json($moduleTitle);
            const legacyDashboardTitle = @json($legacyDashboardTitle);

            const replaceLegacyText = (doc) => {
                const root = doc?.body;

                if (!root || !doc.createTreeWalker) {
                    return;
                }

                const walker = doc.createTreeWalker(root, NodeFilter.SHOW_TEXT);
                let node = walker.nextNode();

                while (node) {
                    if (node.nodeValue && node.nodeValue.includes(legacyDashboardTitle)) {
                        node.nodeValue = node.nodeValue.split(legacyDashboardTitle).join(moduleTitle);
                    }

                    node = walker.nextNode();
                }
            };

            const syncDocument = (doc) => {
                if (!doc) {
                    return;
                }

                if (doc.title && doc.title.includes(legacyDashboardTitle)) {
                    doc.title = doc.title.split(legacyDashboardTitle).join(moduleTitle);
                }

                replaceLegacyText(doc);
            };

            const syncManagerTabTitle = () => {
                document.title = moduleTitle;
                syncDocument(document);

                let frame = window;

                for (let level = 0; level < 5; level += 1) {
                    try {
                        if (!frame.parent || frame.parent === frame) {
                            break;
                        }

                        frame = frame.parent;
                        syncDocument(frame.document);
                    } catch (error) {
                        break;
                    }
                }
            };

            syncManagerTabTitle();
            window.addEventListener('load', syncManagerTabTitle, { once: true });
            setTimeout(syncManagerTabTitle, 80);
            setTimeout(syncManagerTabTitle, 500);

            try {
                const parentDoc = window.parent && window.parent !== window ? window.parent.document : null;

                if (parentDoc?.body && window.MutationObserver) {
                    const observer = new MutationObserver(syncManagerTabTitle);
                    observer.observe(parentDoc.body, { childList: true, subtree: true, characterData: true });
                    setTimeout(() => observer.disconnect(), 30000);
                }
            } catch (error) {
                // Ignore parent-frame access issues in isolated render contexts.
            }

            const interval = setInterval(syncManagerTabTitle, 1000);
            setTimeout(() => clearInterval(interval), 10000);
        })();
    </script>
</body>
</html>
