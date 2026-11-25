<script>
    const menuToggle = document.getElementById('menuToggle');
    const mainSidebar = document.getElementById('mainSidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const mobileViewport = window.matchMedia('(max-width: 650px)');
    let sidebarIsOpen = false;
    const setSidebarOpen = (isOpen) => {
        sidebarIsOpen = mobileViewport.matches && isOpen;
        document.body.classList.toggle('overflow-hidden', sidebarIsOpen);
        mainSidebar?.classList.toggle('-translate-x-[calc(100%+24px)]', !sidebarIsOpen);
        mainSidebar?.classList.toggle('translate-x-0', sidebarIsOpen);
        sidebarBackdrop?.classList.toggle('hidden', !sidebarIsOpen);
        sidebarBackdrop?.setAttribute('aria-hidden', String(!sidebarIsOpen));
        menuToggle?.setAttribute('aria-expanded', String(sidebarIsOpen));
        menuToggle?.setAttribute('aria-label', sidebarIsOpen ? 'Tutup menu' : 'Buka menu');
    };

    menuToggle?.addEventListener('click', () => {
        setSidebarOpen(!sidebarIsOpen);
    });

    sidebarBackdrop?.addEventListener('click', () => setSidebarOpen(false));

    mobileViewport.addEventListener('change', (event) => {
        if (!event.matches) {
            setSidebarOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebarIsOpen) {
            setSidebarOpen(false);
            menuToggle?.focus();
        }
    });
</script>
