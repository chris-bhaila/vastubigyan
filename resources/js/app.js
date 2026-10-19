document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('[data-sidebar]');
    const overlay = document.querySelector('[data-sidebar-overlay]');

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            sidebar?.classList.toggle('-translate-x-full');
            overlay?.classList.toggle('hidden');
        });
    });

    overlay?.addEventListener('click', () => {
        sidebar?.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });

    const collapseToggle = document.querySelector('[data-sidebar-collapse-toggle]');

    collapseToggle?.addEventListener('click', () => {
        const collapsed = sidebar?.classList.toggle('lg:w-20');
        sidebar?.classList.toggle('lg:p-3', collapsed);
        collapseToggle.classList.toggle('rotate-180', collapsed);
        document.querySelectorAll('[data-sidebar-text]').forEach((el) => el.classList.toggle('hidden', collapsed));

        try {
            localStorage.setItem('sidebar-collapsed', String(Boolean(collapsed)));
        } catch (e) {}
    });

    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);

            if (!input) {
                return;
            }

            const showing = input.type === 'password';

            input.type = showing ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(showing));
            button.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
            button.querySelector('[data-icon-show]')?.classList.toggle('hidden', showing);
            button.querySelector('[data-icon-hide]')?.classList.toggle('hidden', !showing);
        });
    });
});
