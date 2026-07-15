/**
 * Umami External Link Tracking
 * 
 * Automatically adds Umami event tracking to external links
 */
(function() {
    'use strict';

    console.debug('[Umami External Link Tracking] Script loaded');

    function setupExternalLinkTracking() {
        console.debug('[Umami External Link Tracking] Setting up external link tracking');
        
        const eventName = 'external-link';
        const links = document.querySelectorAll('a[href]');
        let externalLinksFound = 0;
        let linksProcessed = 0;
        
        console.debug('[Umami External Link Tracking] Found', links.length, 'links to check');
        
        links.forEach(function(link) {
            // Skip if it already has umami event
            if (link.hasAttribute('data-umami-event')) {
                console.debug('[Umami External Link Tracking] Skipping already tracked link:', link.href);
                return;
            }
            
            try {
                const linkUrl = new URL(link.href);
                const currentUrl = new URL(window.location.href);
                
                // Check if it's an external link
                if (linkUrl.host !== currentUrl.host && linkUrl.protocol.startsWith('http')) {
                    link.setAttribute('data-umami-event', eventName);
                    link.setAttribute('data-umami-event-url', link.href);
                    externalLinksFound++;
                    console.debug('[Umami External Link Tracking] Added tracking to external link:', link.href);
                }
                linksProcessed++;
            } catch (e) {
                console.error('[Umami External Link Tracking] Error processing link:', link.href, e);
            }
        });
        
        console.debug('[Umami External Link Tracking] Processed', linksProcessed, 'links, found', externalLinksFound, 'external links');
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