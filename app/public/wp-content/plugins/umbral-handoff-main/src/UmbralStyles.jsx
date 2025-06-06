import React from 'react';

const UMBRAL_STYLES = `
/* Umbral Editor Design System - Raycast Light + WordPress Admin */
:host {
  --panel-title: 12px;
  --panel-subtitle: 10px;

  --panel-icon-width: 24px;
  --panel-icon-height: 24px;
  --panel-icon-size: 10px;

  /* Surface Colors - Light theme with WordPress-like grays */
  --surface-primary: #ffffff;
  --surface-secondary: #f9f9f9;
  --surface-tertiary: #f0f0f0;
  --surface-hover: #f5f5f5;
  --surface-active: #eeeeee;
  --surface-overlay: rgba(0, 0, 0, 0.05);
  
  /* Background Colors */
  --bg-primary: #ffffff;
  --bg-secondary: #fafafa;
  --bg-tertiary: #f6f7f8;
  --bg-input: #ffffff;
  --bg-disabled: #f3f4f6;
  
  /* Text Colors - WordPress admin inspired */
  --text-primary: #1e1e1e;
  --text-secondary: #50575e;
  --text-tertiary: #8c8f94;
  --text-disabled: #a7aaad;
  --text-inverse: #ffffff;
  --text-link: #2271b1;
  --text-link-hover: #135e96;
  
  /* Border Colors */
  --border-primary: #dcdcde;
  --border-secondary: #e0e0e0;
  --border-tertiary: #f0f0f0;
  --border-focus: #2271b1;
  --border-hover: #c3c4c7;
  --border-error: #d63638;
  --border-success: #00a32a;
  --border-warning: #dba617;
  
  /* Status Colors - WordPress admin colors */
  --status-error: #d63638;
  --status-error-bg: #fcf0f1;
  --status-error-border: #f1a1a3;
  --status-success: #00a32a;
  --status-success-bg: #edfaef;
  --status-success-border: #68de7c;
  --status-warning: #dba617;
  --status-warning-bg: #fcf9e8;
  --status-warning-border: #f0d000;
  --status-info: #72aee6;
  --status-info-bg: #f0f6fc;
  --status-info-border: #a7d8f0;
  
  /* Interactive Colors */
  --interactive-primary: #2271b1;
  --interactive-primary-hover: #135e96;
  --interactive-primary-active: #043959;
  --interactive-secondary: #f6f7f8;
  --interactive-secondary-hover: #dcdcde;
  --interactive-secondary-active: #c3c4c7;
  
  /* Component Specific Colors */
  --card-bg: #ffffff;
  --card-border: #dcdcde;
  --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  --card-shadow-hover: 0 2px 6px rgba(0, 0, 0, 0.15);
  --panel-bg: #ffffff;
  --panel-border: #dcdcde;
  --input-bg: #ffffff;
  --input-border: #8c8f94;
  --input-border-focus: #2271b1;
  --button-primary-bg: #2271b1;
  --button-primary-hover: #135e96;
  --button-secondary-bg: #f6f7f8;
  --button-secondary-hover: #dcdcde;
  
  /* Spacing Scale - Consistent with Raycast */
  --space-xs: 4px;
  --space-sm: 8px;
  --space-md: 12px;
  --space-lg: 16px;
  --space-xl: 20px;
  --space-2xl: 24px;
  --space-3xl: 32px;
  --space-4xl: 40px;
  
  /* Border Radius */
  --radius-xs: 2px;
  --radius-sm: 4px;
  --radius-md: 6px;
  --radius-lg: 8px;
  --radius-xl: 12px;
  
  /* Typography Scale */
  --font-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  --font-mono: -apple-system, BlinkMacSystemFont, "SF Mono", Monaco, "Cascadia Code", monospace;
  --font-size-xs: 11px;
  --font-size-sm: 12px;
  --font-size-base: 13px;
  --font-size-md: 14px;
  --font-size-lg: 16px;
  --font-size-xl: 18px;
  --font-size-2xl: 20px;
  --font-weight-normal: 400;
  --font-weight-medium: 500;
  --font-weight-semibold: 600;
  --font-weight-bold: 700;
  --line-height-tight: 1.25;
  --line-height-normal: 1.4;
  --line-height-relaxed: 1.6;
  
  /* Animation & Transitions */
  --transition-fast: 0.15s ease;
  --transition-base: 0.2s ease;
  --transition-slow: 0.3s ease;
  --transition-bounce: 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  
  /* Z-Index Scale */
  --z-dropdown: 1000;
  --z-sticky: 1020;
  --z-fixed: 1030;
  --z-modal: 1040;
  --z-popover: 1050;
  --z-tooltip: 1060;
  
  /* Component Heights */
  --height-input: 32px;
  --height-button: 32px;
  --height-button-sm: 28px;
  --height-button-lg: 40px;
  
  /* Icon Sizes */
  --icon-xs: 12px;
  --icon-sm: 16px;
  --icon-md: 20px;
  --icon-lg: 24px;
  --icon-xl: 32px;
  --icon-2xl: 40px;
}

/* Essential Shadow DOM normalization */
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

/* Base typography */
body, html {
  font-family: var(--font-sans);
  font-size: var(--font-size-base);
  line-height: var(--line-height-normal);
  color: var(--text-primary);
  background: var(--bg-primary);
}

/* Smooth scrollbars - WordPress admin inspired */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: var(--bg-tertiary);
  border-radius: var(--radius-sm);
}

::-webkit-scrollbar-thumb {
  background: var(--border-hover);
  border-radius: var(--radius-sm);
  transition: background-color var(--transition-fast);
}

::-webkit-scrollbar-thumb:hover {
  background: var(--text-tertiary);
}

/* Focus styles */
:focus {
  outline: 2px solid var(--border-focus);
  outline-offset: 2px;
}

:focus:not(:focus-visible) {
  outline: none;
}

/* Animation utilities */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes slideIn {
  from { 
    opacity: 0;
    transform: translateY(-10px);
  }
  to { 
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Utility classes */
.umbral-sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.umbral-transition {
  transition: all var(--transition-base);
}

.umbral-shadow {
  box-shadow: var(--card-shadow);
}

.umbral-shadow-hover:hover {
  box-shadow: var(--card-shadow-hover);
}

/* Components Field Styles */
.umbral-components-field {
  background: var(--bg-primary);
  border-radius: var(--radius-md);
  overflow: hidden;
}

.umbral-loading-state {
  padding: var(--space-4xl) var(--space-xl);
  text-align: center;
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border-tertiary);
}

.umbral-spinner {
  width: var(--icon-md);
  height: var(--icon-md);
  border: 2px solid var(--border-tertiary);
  border-top: 2px solid var(--interactive-primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto var(--space-sm) auto;
}

.umbral-add-component-section {
  padding: var(--space-xl);
  border-bottom: 1px solid var(--border-tertiary);
  background: var(--bg-secondary);
}

.umbral-add-component-header {
  display: flex;
  align-items: center;
  gap: var(--space-lg);
  flex-wrap: wrap;
  justify-content: space-between;
}

.umbral-save-section {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  justify-content: flex-end;
  min-width: 200px;
}

.umbral-add-component-btn {
  display: inline-flex;
  align-items: center;
  gap: var(--space-sm);
  padding: var(--space-sm) var(--space-lg);
  background: var(--button-primary-bg);
  color: var(--text-inverse);
  border: none;
  border-radius: var(--radius-sm);
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-medium);
  font-family: var(--font-sans);
  cursor: pointer;
  transition: background-color var(--transition-base);
}

.umbral-add-component-btn:hover:not(:disabled) {
  background: var(--button-primary-hover);
}

.umbral-add-component-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.umbral-save-btn {
  display: inline-flex;
  align-items: center;
  gap: var(--space-xs);
  padding: var(--space-sm) var(--space-lg);
  border: 1px solid;
  border-radius: var(--radius-sm);
  font-size: var(--font-size-base);
  font-weight: var(--font-weight-medium);
  font-family: var(--font-sans);
  cursor: pointer;
  transition: all var(--transition-base);
  background: var(--bg-primary);
}

.umbral-save-btn-unsaved {
  border-color: var(--status-error);
  color: var(--status-error);
}

.umbral-save-btn-unsaved:hover:not(:disabled) {
  background: var(--status-error);
  color: var(--text-inverse);
}

.umbral-save-btn-saved {
  border-color: var(--status-success);
  color: var(--status-success);
}

.umbral-save-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.umbral-unsaved-indicator {
  display: flex;
  align-items: center;
  gap: var(--space-xs);
  color: var(--status-error);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
}

.umbral-unsaved-dot {
  width: var(--space-sm);
  height: var(--space-sm);
  background: var(--status-error);
  border-radius: 50%;
  animation: pulse 2s infinite;
}

.umbral-saving-spinner {
  width: var(--font-size-sm);
  height: var(--font-size-sm);
  border: 1px solid var(--border-tertiary);
  border-top: 1px solid var(--text-secondary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.umbral-add-icon {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-bold);
}

.umbral-empty-state {
  margin: var(--space-lg) 0 0 0;
  color: var(--text-secondary);
  font-style: italic;
  font-size: var(--font-size-md);
}
`;

export function UmbralStyles() {
  return (
    <style id="umbral-editor-styles" dangerouslySetInnerHTML={{ __html: UMBRAL_STYLES }} />
  );
}