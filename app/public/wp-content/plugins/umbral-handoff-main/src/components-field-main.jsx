import React from 'react';
import r2wc from '@r2wc/react-to-web-component';
import { ComponentsField } from './components/ComponentsField';
import { UmbralStyles } from './UmbralStyles';

// Wrapper component that includes styles
function ComponentsFieldWrapper(props) {
  return (
    <>
      <UmbralStyles />
      <ComponentsField {...props} />
    </>
  );
}

// Create the web component for CMB2 fields
const UmbralComponentsFieldElement = r2wc(ComponentsFieldWrapper, {
  shadow: 'open',
  props: {
    fieldId: 'string',
    fieldName: 'string',
    postId: 'string',
    restNonce: 'string'
  }
});

// Register the web component
customElements.define('umbral-components-field', UmbralComponentsFieldElement);

// Export for global access
window.UmbralComponentsField = {
  ComponentsField,
  init: () => {
    console.log('Umbral Components Field initialized');
  }
};

// Initialize immediately and also on DOMContentLoaded
window.UmbralComponentsField.init();

// Also initialize on DOMContentLoaded for safety
document.addEventListener('DOMContentLoaded', () => {
  window.UmbralComponentsField.init();
});

// Add debugging
console.log('Umbral Components Field script loaded');
console.log('Web component registered:', customElements.get('umbral-components-field') ? 'YES' : 'NO');