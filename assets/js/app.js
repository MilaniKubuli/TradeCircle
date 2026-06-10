document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('tradecircle-theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);

    const navToggle = document.querySelector('[data-nav-toggle]');
    const navMenu = document.querySelector('[data-nav-menu]');
    const navBackdrop = document.querySelector('[data-nav-backdrop]');

    if (navToggle && navMenu) {
        const closeNav = () => {
            navMenu.classList.remove('is-open');
            document.body.classList.remove('nav-open');
            navToggle.setAttribute('aria-expanded', 'false');
        };
        const openNav = () => {
            navMenu.classList.add('is-open');
            document.body.classList.add('nav-open');
            navToggle.setAttribute('aria-expanded', 'true');
        };
        navToggle.addEventListener('click', () => {
            navMenu.classList.contains('is-open') ? closeNav() : openNav();
        });
        navBackdrop?.addEventListener('click', closeNav);
        navMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeNav);
        });
    }

    const profileToggle = document.querySelector('[data-profile-toggle]');
    const profileMenu = document.querySelector('[data-profile-menu]');
    if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', (event) => {
            event.stopPropagation();
            profileMenu.classList.toggle('is-open');
            profileToggle.setAttribute('aria-expanded', profileMenu.classList.contains('is-open') ? 'true' : 'false');
        });
        document.addEventListener('click', () => {
            profileMenu.classList.remove('is-open');
            profileToggle.setAttribute('aria-expanded', 'false');
        });
    }

    const themeToggle = document.querySelector('[data-theme-toggle]');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('tradecircle-theme', next);
        });
    }

    const backButton = document.querySelector('[data-back-button]');
    if (backButton) {
        backButton.addEventListener('click', () => {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = backButton.dataset.homeUrl || 'index.php';
            }
        });
    }

    document.querySelectorAll('[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-confirm-button]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const message = button.getAttribute('data-confirm-button') || 'Are you sure?';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-character-count]').forEach((field) => {
        const output = field.closest('form')?.querySelector('[data-character-output]');
        const update = () => {
            if (output) {
                output.textContent = `${field.value.length} characters`;
            }
        };
        field.addEventListener('input', update);
        update();
    });

    document.querySelectorAll('input[type="number"]').forEach((input) => {
        input.addEventListener('change', () => {
            const min = Number(input.min || 0);
            const max = Number(input.max || 999999);
            const value = Number(input.value || min);
            if (value < min) input.value = String(min);
            if (value > max) input.value = String(max);
        });
    });

    const genreMapElement = document.querySelector('[data-genre-map]');
    const categorySelect = document.querySelector('[data-category-filter]');
    const genreSelect = document.querySelector('[data-genre-filter]');
    
    if (genreMapElement && categorySelect && genreSelect) {
        const genreMap = JSON.parse(genreMapElement.textContent || '{}');
        const selectedGenre = genreSelect.dataset.selected || '';
        const renderGenres = () => {
            const categoryName = categorySelect.options[categorySelect.selectedIndex]?.dataset.categoryName || '';
            const genres = genreMap[categoryName] || [];
            genreSelect.innerHTML = '<option value="">All genres</option>';
            genres.forEach((genre) => {
                const option = document.createElement('option');
                option.value = genre;
                option.textContent = genre;
                option.selected = genre === selectedGenre;
                genreSelect.appendChild(option);
            });
            genreSelect.disabled = genres.length === 0;
        };
        categorySelect.addEventListener('change', renderGenres);
        renderGenres();
    }

    const imageInput = document.querySelector('[data-image-input]');
    const imagePreview = document.querySelector('[data-image-preview]');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', () => {
            const file = imageInput.files?.[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = () => {
                imagePreview.src = String(reader.result);
                imagePreview.hidden = false;
            };
            reader.readAsDataURL(file);
        });
    }

    document.querySelectorAll('[data-status-choice]').forEach((button) => {
        button.addEventListener('click', () => {
            const group = button.closest('[data-status-group]');
            const target = group?.querySelector('[data-status-value]');
            if (!group || !target) return;
            target.value = button.dataset.statusChoice || 'active';
            group.querySelectorAll('[data-status-choice]').forEach((item) => {
                item.classList.toggle('is-selected', item === button);
            });
        });
    });

    const deliveryMethods = document.querySelectorAll('[data-delivery-method]');
    const deliveryAddressRow = document.querySelector('[data-delivery-address-row]');
    const deliveryAddressInput = document.querySelector('[data-delivery-address-input]');
    if (deliveryMethods.length && deliveryAddressRow && deliveryAddressInput) {
        const updateDeliveryAddress = () => {
            const selected = Array.from(deliveryMethods).find((item) => item.checked)?.value || 'pickup';
            const needsAddress = selected === 'local_delivery';
            deliveryAddressRow.hidden = !needsAddress;
            deliveryAddressInput.required = needsAddress;
        };
        deliveryMethods.forEach((method) => method.addEventListener('change', updateDeliveryAddress));
        updateDeliveryAddress();
    }

});
