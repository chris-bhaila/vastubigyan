document.addEventListener('DOMContentLoaded', () => {
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
