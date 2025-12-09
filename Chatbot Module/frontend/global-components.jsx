import React from 'react';
import { createRoot } from 'react-dom/client';
import CookieConsent from './components/CookieConsent';
import HumanVerification from './components/HumanVerification';

/**
 * Global Components Initializer
 * Adds Cookie Consent and Human Verification to all pages
 */

// Initialize Cookie Consent Banner
const initCookieConsent = () => {
  const cookieRoot = document.createElement('div');
  cookieRoot.id = 'cookie-consent-root';
  document.body.appendChild(cookieRoot);

  const root = createRoot(cookieRoot);
  root.render(<CookieConsent />);
};

// Initialize Human Verification with auto-show for authenticated pages
const initHumanVerification = (targetId = 'human-verification-root', autoShow = false) => {
  const container = document.getElementById(targetId);
  if (container) {
    const root = createRoot(container);
    root.render(
      <HumanVerification
        autoShow={autoShow}
        onVerified={() => {
          console.log('✓ User verified as human!');
          // Dispatch event for other components to listen
          window.dispatchEvent(new CustomEvent('humanVerified'));
        }}
        onFailed={() => {
          console.log('✗ Verification failed');
          // Optionally redirect or show error
        }}
      />
    );
  }
};

// Initialize on page load
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    initCookieConsent();

    // Check if user is authenticated (look for specific element or data attribute)
    const isAuthenticated = document.querySelector('[data-authenticated]') !== null ||
                           document.body.dataset.authenticated === 'true';

    initHumanVerification('human-verification-root', isAuthenticated);
  });
} else {
  initCookieConsent();

  const isAuthenticated = document.querySelector('[data-authenticated]') !== null ||
                         document.body.dataset.authenticated === 'true';

  initHumanVerification('human-verification-root', isAuthenticated);
}

// Export for use in other components
export { CookieConsent, HumanVerification };
