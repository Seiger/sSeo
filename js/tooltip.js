document.addEventListener('DOMContentLoaded', () => {
    const tooltipSelector = '[data-tooltip]';
    let activeTooltip = null;
    let hideTimeout = null;

    document.body.addEventListener('mouseenter', (e) => {
        const target = e.target.closest(tooltipSelector);
        if (!target) return;

        clearTimeout(hideTimeout);
        if (activeTooltip) return;

        const tooltip = document.createElement('div');
        tooltip.className = 's-tooltip';
        tooltip.innerHTML = target.getAttribute('data-tooltip');
        tooltip.style.position = 'absolute';
        tooltip.style.zIndex = '99999';
        tooltip.style.opacity = '0';
        tooltip.style.pointerEvents = 'auto';
        tooltip.style.transition = 'opacity 0.15s ease';
        document.body.appendChild(tooltip);

        const targetRect = target.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        const top = targetRect.top + window.scrollY + targetRect.height / 2 - tooltip.offsetHeight / 2;
        const left = targetRect.right + 8 + window.scrollX;

        tooltip.style.top = `${top}px`;
        tooltip.style.left = `${left}px`;

        requestAnimationFrame(() => {tooltip.style.opacity = '1';});
        tooltip.addEventListener('mouseenter', () => {clearTimeout(hideTimeout);});
        tooltip.addEventListener('mouseleave', () => {hideTooltip();});
        activeTooltip = tooltip;
    }, true);

    document.body.addEventListener('mouseleave', (e) => {
        const target = e.target.closest(tooltipSelector);
        if (!target) return;
        hideTooltip();
    }, true);

    function hideTooltip() {
        if (!activeTooltip) return;

        clearTimeout(hideTimeout);
        hideTimeout = setTimeout(() => {
            if (!activeTooltip) return;
            activeTooltip.style.opacity = '0';
            setTimeout(() => {
                if (activeTooltip?.parentNode) {
                    activeTooltip.remove();
                }
                activeTooltip = null;
            }, 150);
        }, 300);
    }
});
