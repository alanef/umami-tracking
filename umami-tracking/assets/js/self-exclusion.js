/**
 * Umami Self-Exclusion Toggle
 * 
 * Allows users to exclude/include their own visits from tracking
 */
(function() {
    'use strict';

    // Check if user has permission (logged in with appropriate role)
    if (!umamiSelfExclusion || !umamiSelfExclusion.canExclude) {
        return;
    }

    const storageKey = 'umami.disabled';
    const buttonId = 'umami-exclusion-toggle';
    
    function isExcluded() {
        return localStorage.getItem(storageKey) === '1';
    }

    function toggleExclusion() {
        if (isExcluded()) {
            localStorage.removeItem(storageKey);
            updateUI(false);
            showNotification(umamiSelfExclusion.messages.included);
        } else {
            localStorage.setItem(storageKey, '1');
            updateUI(true);
            showNotification(umamiSelfExclusion.messages.excluded);
        }
    }

    function updateUI(excluded) {
        const button = document.getElementById(buttonId);
        if (!button) return;

        if (excluded) {
            button.textContent = umamiSelfExclusion.messages.excludedButton;
            button.classList.add('excluded');
            button.classList.remove('included');
        } else {
            button.textContent = umamiSelfExclusion.messages.includedButton;
            button.classList.add('included');
            button.classList.remove('excluded');
        }

        // Update admin bar if it exists
        const adminBarItem = document.getElementById('wp-admin-bar-umami-tracking-toggle');
        if (adminBarItem) {
            const link = adminBarItem.querySelector('a');
            if (link) {
                link.textContent = excluded ? 
                    umamiSelfExclusion.messages.adminBarExcluded : 
                    umamiSelfExclusion.messages.adminBarIncluded;
                link.className = excluded ? 'umami-excluded' : 'umami-included';
            }
        }
    }

    function showNotification(message) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'umami-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 999999;
            font-size: 14px;
            animation: slideIn 0.3s ease-out;
        `;

        // Add animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => {
                notification.remove();
                style.remove();
            }, 300);
        }, 3000);
    }

    function createToggleButton() {
        const button = document.createElement('button');
        button.id = buttonId;
        button.type = 'button';
        button.className = 'umami-exclusion-toggle-button';
        button.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 8px 16px;
            background: #0073aa;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            z-index: 99999;
            transition: all 0.3s ease;
        `;

        // Add hover effect
        button.addEventListener('mouseenter', function() {
            this.style.background = '#005177';
        });
        button.addEventListener('mouseleave', function() {
            this.style.background = isExcluded() ? '#666' : '#0073aa';
        });

        button.addEventListener('click', toggleExclusion);
        updateUI(isExcluded());

        return button;
    }

    function initAdminBarToggle() {
        const adminBarItem = document.getElementById('wp-admin-bar-umami-tracking-toggle');
        if (adminBarItem) {
            const link = adminBarItem.querySelector('a');
            if (link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    toggleExclusion();
                });
                
                // Set initial state
                updateUI(isExcluded());
            }
        }
    }

    // Initialize based on settings
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize admin bar toggle
        initAdminBarToggle();

        // Add floating button if enabled
        if (umamiSelfExclusion.showButton) {
            const button = createToggleButton();
            document.body.appendChild(button);
        }

        // Set initial UI state
        updateUI(isExcluded());
    });

})();