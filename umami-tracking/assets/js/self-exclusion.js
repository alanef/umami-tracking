/**
 * Umami Self-Exclusion Toggle
 * 
 * Allows users to exclude/include their own visits from tracking
 */
(function() {
    'use strict';

    // Check if we have the required configuration
    if (!window.umamiSelfExclusion) {
        return;
    }

    const storageKey = 'umami.disabled';
    const buttonId = 'umami-exclusion-toggle';
    
    function isExcluded() {
        return localStorage.getItem(storageKey) === '1';
    }

    function toggleExclusion() {
        // Check if user is logged in and has permission
        if (!umamiSelfExclusion.isLoggedIn) {
            // Redirect to login page
            showNotification(umamiSelfExclusion.messages.loginRequired);
            setTimeout(function() {
                window.location.href = umamiSelfExclusion.loginUrl;
            }, 1500);
            return;
        }

        if (!umamiSelfExclusion.canExclude) {
            return;
        }

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
        if (button) {
            button.textContent = excluded ? 
                umamiSelfExclusion.messages.excludedButton : 
                umamiSelfExclusion.messages.includedButton;
            
            if (excluded) {
                button.classList.add('excluded');
                button.classList.remove('included');
            } else {
                button.classList.add('included');
                button.classList.remove('excluded');
            }
        }

        // Update admin bar if it exists and user is logged in
        if (umamiSelfExclusion.showAdminBar) {
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
        
        // Set initial button state
        updateUI(isExcluded());

        return button;
    }

    function initAdminBarToggle() {
        if (!umamiSelfExclusion.showAdminBar) {
            return;
        }

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
        // Initialize admin bar toggle for logged-in users
        if (umamiSelfExclusion.isLoggedIn && umamiSelfExclusion.canExclude) {
            initAdminBarToggle();
        }

        // Add floating button if enabled (for all users)
        if (umamiSelfExclusion.showButton) {
            const button = createToggleButton();
            document.body.appendChild(button);
        }

        // Set initial UI state
        updateUI(isExcluded());
    });

})();