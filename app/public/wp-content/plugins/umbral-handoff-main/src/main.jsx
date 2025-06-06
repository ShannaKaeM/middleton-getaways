import React from 'react';
import r2wc from '@r2wc/react-to-web-component';
import { UmbralApp } from './UmbralApp';

// Create the web component with props support using correct API
const UmbralEditorElement = r2wc(UmbralApp, {
  shadow: 'open',
  props: {
    // Only pass REST nonce - fetch everything else via API
    restNonce: 'string'
  }
});

// Register the web component (commented out - not using demo panel currently)
// customElements.define('umbral-editor-panel', UmbralEditorElement);

// Export for global access
window.UmbralEditor = {
  UmbralApp,
  init: () => {
    console.log('Umbral Editor - CMB2 Components Field initialized');
  }
};

// Auto-initialize
document.addEventListener('DOMContentLoaded', () => {
  window.UmbralEditor.init();
});