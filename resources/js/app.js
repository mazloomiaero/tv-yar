document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    if (toggle && sidebar) {
        const close = () => {
            sidebar.classList.add('translate-x-full');
            sidebar.classList.remove('translate-x-0');
            backdrop?.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        };

        const open = () => {
            sidebar.classList.remove('translate-x-full');
            sidebar.classList.add('translate-x-0');
            backdrop?.classList.remove('hidden');
            toggle.setAttribute('aria-expanded', 'true');
        };

        toggle.addEventListener('click', () => {
            const expanded = toggle.getAttribute('aria-expanded') === 'true';
            if (expanded) {
                close();
            } else {
                open();
            }
        });

        backdrop?.addEventListener('click', close);
    }

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const wrap = button.closest('.ui-password');
            const input = wrap?.querySelector('input');

            if (!input) {
                return;
            }

            const hidden = input.getAttribute('type') === 'password';
            input.setAttribute('type', hidden ? 'text' : 'password');
            button.setAttribute('aria-label', hidden ? 'پنهان کردن رمز عبور' : 'نمایش رمز عبور');
        });
    });
});
