// Dark Mode Toggle Script
(function() {
    'use strict';

    // Get theme preference from localStorage or default to 'light'
    function getThemePreference() {
        return localStorage.getItem('theme') || 'light';
    }

    // Set theme preference in localStorage
    function setThemePreference(theme) {
        localStorage.setItem('theme', theme);
    }

    // Apply theme to document
    function applyTheme(theme) {
        if (theme === 'auto') {
            // Check system preference
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            theme = systemPrefersDark ? 'dark' : 'light';
        }

        document.documentElement.setAttribute('data-theme', theme);
        updateToggleIcon(theme);
    }

    // Update toggle icon based on current theme
    function updateToggleIcon(theme) {
        const sunIcon = document.getElementById('sun-icon');
        const moonIcon = document.getElementById('moon-icon');

        if (sunIcon && moonIcon) {
            if (theme === 'dark') {
                sunIcon.classList.add('hidden');
                moonIcon.classList.remove('hidden');
            } else {
                sunIcon.classList.remove('hidden');
                moonIcon.classList.add('hidden');
            }
        }
    }

    // Toggle between light and dark themes
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';

        setThemePreference(newTheme);
        applyTheme(newTheme);

        // Update user preference via API if logged in
        if (window.updateThemePreference) {
            window.updateThemePreference(newTheme);
        }
    }

    // Initialize theme on page load
    function initTheme() {
        const savedTheme = getThemePreference();
        applyTheme(savedTheme);

        // Add click handler to toggle button
        const toggleButton = document.getElementById('theme-toggle');
        if (toggleButton) {
            toggleButton.addEventListener('click', toggleTheme);
        }

        // Listen for system theme changes if auto mode
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (getThemePreference() === 'auto') {
                applyTheme('auto');
            }
        });
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }

    // Expose toggle function globally
    window.toggleTheme = toggleTheme;

    // Function to update theme preference via API
    window.updateThemePreference = async function(theme) {
        try {
            const response = await fetch('/profile/settings', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    theme_preference: theme
                })
            });

            if (!response.ok) {
                console.error('Failed to update theme preference');
            }
        } catch (error) {
            console.error('Error updating theme preference:', error);
        }
    };
})();
