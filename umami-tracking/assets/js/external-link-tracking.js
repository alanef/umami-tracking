/**
 * Umami External Link Tracking
 * 
 * Automatically adds Umami event tracking to external links
 */
(function() {
    'use strict';

    function setupExternalLinkTracking() {
        const eventName = 'external-link';
        const links = document.querySelectorAll('a[href]');
        
        links.forEach(function(link) {
            // Skip if it already has umami event
            if (link.hasAttribute('data-umami-event')) {
                return;
            }
            
            try {
                const linkUrl = new URL(link.href);
                const currentUrl = new URL(window.location.href);
                
                // Check if it's an external link
                if (linkUrl.host !== currentUrl.host && linkUrl.protocol.startsWith('http')) {
                    link.setAttribute('data-umami-event', eventName);
                    link.setAttribute('data-umami-event-url', link.href);
                }
            } catch (e) {
                // Invalid URL, skip
            }
        });
    }

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupExternalLinkTracking);
    } else {
        setupExternalLinkTracking();
    }

    // Also handle dynamically added links
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    setupExternalLinkTracking();
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
})();