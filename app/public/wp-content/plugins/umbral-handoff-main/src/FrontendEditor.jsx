import React, { useState, useEffect, useRef, useCallback } from 'react';
import r2wc from '@r2wc/react-to-web-component';
import { ComponentsField } from './components/ComponentsField';
import { PreviewPanel } from './components/preview';
import { BreakpointsManager } from './components/BreakpointsManager';
import { UmbralStyles } from './UmbralStyles';

// Floating Toast Web Component
function UmbralEditorToast({ editUrl, postId }) {
  const [isVisible, setIsVisible] = useState(true);
  
  if (!isVisible) return null;
  
  return (
    <>
      <UmbralStyles />
      <div className="umbral-toast">
        <div className="umbral-toast-content">
          <div className="umbral-toast-icon">✏️</div>
          <div className="umbral-toast-text">
            <div className="umbral-toast-title">Umbral Editor</div>
            <div className="umbral-toast-subtitle">Edit this page</div>
          </div>
          <div className="umbral-toast-actions">
            <a 
              href={editUrl}
              className="umbral-toast-button"
              title="Open Umbral Editor"
            >
              Edit
            </a>
            <button
              onClick={() => setIsVisible(false)}
              className="umbral-toast-close"
              title="Close"
            >
              ✕
            </button>
          </div>
        </div>
      </div>
      
      <style>{`
        .umbral-toast {
          position: fixed;
          bottom: 20px;
          right: 20px;
          z-index: 99999;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-toast-content {
          display: flex;
          align-items: center;
          gap: 16px;
          background: #ffffff;
          border: 1px solid #dcdcde;
          border-radius: 8px;
          padding: 16px 20px;
          box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
          min-width: 360px;
          max-width: 420px;
          animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
          from {
            transform: translateX(100%);
            opacity: 0;
          }
          to {
            transform: translateX(0);
            opacity: 1;
          }
        }
        
        .umbral-toast-icon {
          font-size: 24px;
          flex-shrink: 0;
        }
        
        .umbral-toast-text {
          flex: 1;
        }
        
        .umbral-toast-title {
          font-weight: 600;
          font-size: 15px;
          color: #1e1e1e;
          margin-bottom: 3px;
        }
        
        .umbral-toast-subtitle {
          font-size: 13px;
          color: #50575e;
        }
        
        .umbral-toast-actions {
          display: flex;
          align-items: center;
          gap: 10px;
          flex-shrink: 0;
        }
        
        .umbral-toast-button {
          background: #2271b1;
          color: white;
          border: none;
          border-radius: 6px;
          padding: 10px 20px;
          font-size: 13px;
          font-weight: 500;
          text-decoration: none;
          cursor: pointer;
          transition: background-color 0.2s ease;
          white-space: nowrap;
        }
        
        .umbral-toast-button:hover {
          background: #135e96;
          color: white;
          text-decoration: none;
        }
        
        .umbral-toast-close {
          background: none;
          border: none;
          color: #50575e;
          cursor: pointer;
          padding: 6px;
          border-radius: 6px;
          font-size: 14px;
          transition: all 0.2s ease;
          width: 32px;
          height: 32px;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
        }
        
        .umbral-toast-close:hover {
          background: #f0f0f1;
          color: #1e1e1e;
        }
      `}</style>
    </>
  );
}

// Full Screen Editor Web Component
function UmbralFullEditor({ postId, closeUrl, restUrl, restNonce }) {
  const [previewKey, setPreviewKey] = useState(0);
  const [isRefreshing, setIsRefreshing] = useState(false);
  const [showBreakpoints, setShowBreakpoints] = useState(false);
  const [visibilityComponent, setVisibilityComponent] = useState(null);
  const debounceTimeoutRef = useRef(null);
  
  // Get URL parameters for source_url and other context
  const urlParams = new URLSearchParams(window.location.search);
  const sourceUrl = urlParams.get('source_url') || window.location.origin;
  const mode = urlParams.get('mode') || 'single';
  const postType = urlParams.get('post_type') || '';
  const previewPostId = urlParams.get('preview_post_id') || '';
  
  console.log('Umbral Frontend Editor: URL params:', {
    sourceUrl,
    mode,
    postType,
    previewPostId,
    sourceId: postId
  });
  
  // Components field is now rendered directly as React component (see JSX below)
  // No need for web component creation in frontend editor context
  
  // Debounced refresh function
  const debouncedRefresh = useCallback(() => {
    if (debounceTimeoutRef.current) {
      clearTimeout(debounceTimeoutRef.current);
    }
    
    setIsRefreshing(true);
    debounceTimeoutRef.current = setTimeout(() => {
      console.log('Umbral Editor: Refreshing preview due to field changes');
      setPreviewKey(prev => prev + 1);
      setIsRefreshing(false);
    }, 1500); // 1.5 second debounce
  }, []);
  
  // Listen for component field changes
  useEffect(() => {
    const handleFieldChange = (event) => {
      // Listen for custom events from the components field
      if (event.detail && event.detail.type === 'components-updated') {
        console.log('Umbral Editor: Components field updated, scheduling refresh');
        debouncedRefresh();
      }
    };
    
    // Listen for any changes in the components field container
    const handleMutation = (mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'childList' || mutation.type === 'attributes') {
          debouncedRefresh();
        }
      });
    };
    
    // Add event listeners
    document.addEventListener('umbral-components-updated', handleFieldChange);
    
    return () => {
      document.removeEventListener('umbral-components-updated', handleFieldChange);
      if (debounceTimeoutRef.current) {
        clearTimeout(debounceTimeoutRef.current);
      }
    };
  }, [debouncedRefresh]);
  
  // Handle manual refresh
  const handleManualRefresh = useCallback(() => {
    if (debounceTimeoutRef.current) {
      clearTimeout(debounceTimeoutRef.current);
    }
    setIsRefreshing(true);
    setPreviewKey(prev => prev + 1);
    // Reset refreshing state after iframe loads
    setTimeout(() => setIsRefreshing(false), 1000);
  }, []);
  
  return (
    <>
      <UmbralStyles />
      <div className="umbral-full-editor">
        {/* Header */}
        <div className="umbral-editor-header">
          <div className="umbral-editor-title">
            <span className="umbral-editor-icon">✏️</span>
            Umbral Editor
            <span className="umbral-editor-context">
              {mode === 'archive' ? `${postType} Archive` : 
               mode === 'single' ? `${postType} Post` : 
               'Editing'}
            </span>
          </div>
          <div className="umbral-editor-actions">
            <button
              onClick={() => setShowBreakpoints(true)}
              className="umbral-breakpoints-btn"
              title="Manage Breakpoints"
            >
              📐 Breakpoints
            </button>
            <a href={closeUrl} className="umbral-close-btn">
              ← Back to Page
            </a>
          </div>
        </div>
        
        {/* Main Editor */}
        <div className="umbral-editor-main">
          {/* Left Panel - Components Field */}
          <div className="umbral-editor-sidebar">
            <div className="umbral-sidebar-content">
              <ComponentsField 
                fieldId="components"
                fieldName="components"
                postId={postId}
                restNonce={restNonce}
                onVisibilityToggle={setVisibilityComponent}
                visibilityComponent={visibilityComponent}
              />
            </div>
          </div>
          
          {/* Right Panel - Preview */}
          <PreviewPanel
            baseUrl={(() => {
              const params = new URLSearchParams();
              params.set('umbral', 'preview');
              params.set('source_id', postId);
              
              // Add mode-specific parameters
              if (mode) {
                params.set('mode', mode);
              }
              if (postType) {
                params.set('post_type', postType);
              }
              if (previewPostId) {
                params.set('preview_post_id', previewPostId);
              }
              
              // Get core_page parameter if it exists
              const corePage = urlParams.get('core_page');
              if (corePage) {
                params.set('core_page', corePage);
              }
              
              return sourceUrl + (sourceUrl.includes('?') ? '&' : '?') + params.toString();
            })()}
            refreshKey={previewKey}
            isRefreshing={isRefreshing}
            onRefresh={handleManualRefresh}
            restNonce={restNonce}
            sourceId={postId}
            mode={mode}
            postType={postType}
            visibilityComponent={visibilityComponent}
          />
        </div>
      </div>
      
      {/* Breakpoints Manager Modal */}
      <BreakpointsManager
        isOpen={showBreakpoints}
        onClose={() => setShowBreakpoints(false)}
        restNonce={restNonce}
      />
      
      <style>{`
        .umbral-full-editor {
          width: 100%;
          height: 100vh;
          display: flex;
          flex-direction: column;
          background: #f0f0f1;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-editor-header {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 16px 24px;
          background: #ffffff;
          border-bottom: 1px solid #dcdcde;
        }
        
        .umbral-editor-title {
          display: flex;
          align-items: center;
          gap: 12px;
          font-size: 18px;
          font-weight: 600;
          color: #1e1e1e;
        }
        
        .umbral-editor-context {
          font-size: 14px;
          font-weight: 400;
          color: #666;
          padding: 4px 8px;
          background: #f0f0f1;
          border-radius: 4px;
          margin-left: 8px;
        }
        
        .umbral-editor-icon {
          font-size: 24px;
        }
        
        .umbral-close-btn {
          background: none;
          border: 1px solid #dcdcde;
          color: #50575e;
          padding: 8px 16px;
          border-radius: 4px;
          text-decoration: none;
          font-size: 14px;
          font-weight: 500;
          transition: all 0.2s ease;
        }
        
        .umbral-close-btn:hover {
          background: #f6f7f8;
          border-color: #c3c4c7;
          color: #1e1e1e;
          text-decoration: none;
        }
        
        .umbral-breakpoints-btn {
          background: none;
          border: 1px solid #dcdcde;
          color: #50575e;
          padding: 8px 16px;
          border-radius: 4px;
          font-size: 14px;
          font-weight: 500;
          transition: all 0.2s ease;
          cursor: pointer;
          margin-right: 12px;
        }
        
        .umbral-breakpoints-btn:hover {
          background: #f6f7f8;
          border-color: #c3c4c7;
          color: #1e1e1e;
        }
        
        .umbral-editor-main {
          flex: 1;
          display: flex;
          overflow: hidden;
        }
        
        .umbral-editor-sidebar {
          width: 480px;
          background: #ffffff;
          border-right: 1px solid #dcdcde;
          display: flex;
          flex-direction: column;
          overflow: hidden;
        }
        
        .umbral-sidebar-content {
          flex: 1;
          overflow-y: auto;
          padding: 0;
        }
        
        .umbral-editor-preview {
          flex: 1;
          display: flex;
          flex-direction: column;
          overflow: hidden;
        }
        
        .umbral-preview-header {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 12px 16px;
          background: #ffffff;
          border-bottom: 1px solid #dcdcde;
        }
        
        .umbral-preview-title {
          font-weight: 600;
          color: #1e1e1e;
          font-size: 14px;
          display: flex;
          align-items: center;
          gap: 12px;
        }
        
        .umbral-refreshing-indicator {
          display: flex;
          align-items: center;
          gap: 6px;
          font-size: 12px;
          color: #2271b1;
          font-weight: 500;
        }
        
        .umbral-spinner {
          width: 12px;
          height: 12px;
          border: 2px solid #f0f0f1;
          border-top: 2px solid #2271b1;
          border-radius: 50%;
          animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
        
        .umbral-refresh-btn {
          background: #f6f7f8;
          border: 1px solid #dcdcde;
          color: #50575e;
          padding: 6px 12px;
          border-radius: 4px;
          font-size: 12px;
          cursor: pointer;
          transition: all 0.2s ease;
        }
        
        .umbral-refresh-btn:hover {
          background: #ffffff;
          border-color: #c3c4c7;
          color: #1e1e1e;
        }
        
        .umbral-refresh-btn:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
        
        .umbral-refresh-btn:disabled:hover {
          background: #f6f7f8;
          border-color: #dcdcde;
          color: #50575e;
        }
        
        .umbral-preview-actions {
          display: flex;
          align-items: center;
          gap: 8px;
        }
        
        .umbral-settings-btn {
          background: #f6f7f8;
          border: 1px solid #dcdcde;
          color: #50575e;
          padding: 6px 8px;
          border-radius: 4px;
          font-size: 14px;
          cursor: pointer;
          transition: all 0.2s ease;
          width: 32px;
          height: 32px;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        
        .umbral-settings-btn:hover {
          background: #ffffff;
          border-color: #c3c4c7;
          color: #1e1e1e;
        }
        
        .umbral-preview-content {
          flex: 1;
          background: #f0f0f1;
          padding: 16px;
        }
        
        .umbral-preview-frame {
          width: 100%;
          height: 100%;
          border: 1px solid #dcdcde;
          border-radius: 8px;
          background: #ffffff;
        }
        
        @media (max-width: 1200px) {
          .umbral-editor-sidebar {
            width: 420px;
          }
        }
        
        @media (max-width: 1024px) {
          .umbral-editor-sidebar {
            width: 380px;
          }
        }
        
        @media (max-width: 768px) {
          .umbral-editor-main {
            flex-direction: column;
          }
          
          .umbral-editor-sidebar {
            width: 100%;
            height: 40%;
          }
          
          .umbral-editor-preview {
            height: 60%;
          }
        }
      `}</style>
    </>
  );
}

// Convert React components to Web Components
const UmbralEditorToastElement = r2wc(UmbralEditorToast, {
  shadow: 'open',
  props: {
    editUrl: 'string',
    postId: 'string'
  }
});

const UmbralFullEditorElement = r2wc(UmbralFullEditor, {
  shadow: 'open',
  props: {
    postId: 'string',
    closeUrl: 'string', 
    restUrl: 'string',
    restNonce: 'string'
  }
});

// Register the web components
customElements.define('umbral-editor-toast', UmbralEditorToastElement);
customElements.define('umbral-full-editor', UmbralFullEditorElement);

// Auto-initialize based on containers
document.addEventListener('DOMContentLoaded', () => {
  // Initialize toast if container exists
  const toastContainer = document.getElementById('umbral-editor-toast-container');
  if (toastContainer && window.umbralFrontendEditor) {
    const { editUrl, postId } = window.umbralFrontendEditor;
    toastContainer.innerHTML = `<umbral-editor-toast edit-url="${editUrl}" post-id="${postId}"></umbral-editor-toast>`;
  }
  
  // Initialize full editor if container exists
  const editorContainer = document.getElementById('umbral-full-editor-container');
  if (editorContainer && window.umbralFrontendEditor) {
    const { postId, closeUrl, restUrl, restNonce } = window.umbralFrontendEditor;
    editorContainer.innerHTML = `<umbral-full-editor post-id="${postId}" close-url="${closeUrl}" rest-url="${restUrl}" rest-nonce="${restNonce}"></umbral-full-editor>`;
  }
});

export { UmbralEditorToast, UmbralFullEditor };