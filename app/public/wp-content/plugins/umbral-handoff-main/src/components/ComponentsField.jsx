import React, { useState, useEffect, useCallback, useRef } from 'react';
import { AnimatePresence } from 'framer-motion';
import { DndProvider } from 'react-dnd';
import { HTML5Backend } from 'react-dnd-html5-backend';
import { CommandPalette } from './CommandPalette';
import { ComponentsAccordion } from './ComponentsAccordion';

export function ComponentsField(props = {}) {
  // Get data from web component attributes
  const {
    fieldId = 'unknown',
    fieldName = 'unknown',
    postId = null,
    restNonce = '',
    onVisibilityToggle = null,
    visibilityComponent = null
  } = props;
  
  // Debug logging
  console.log('Umbral Components Field initialized:', {
    fieldId,
    fieldName,
    postId,
    restNonce: restNonce ? 'present' : 'missing'
  });
  
  const [isCommandPaletteOpen, setIsCommandPaletteOpen] = useState(false);
  const [currentComponents, setCurrentComponents] = useState([]);
  const [savedComponents, setSavedComponents] = useState([]);
  const [availableComponents, setAvailableComponents] = useState([]);
  const [query, setQuery] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  const [isSaving, setIsSaving] = useState(false);
  const [hasUnsavedChanges, setHasUnsavedChanges] = useState(false);
  const [isInitialLoad, setIsInitialLoad] = useState(true);
  
  // Use refs to avoid stale closure issues with timers
  const autoSaveTimerRef = useRef(null);
  const isSavingRef = useRef(false);
  const latestComponentsRef = useRef([]);
  
  
  // Load available components from REST API
  useEffect(() => {
    const loadAvailableComponents = async () => {
      try {
        const response = await fetch('/wp-json/umbral-editor/v1/components/available', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': restNonce
          }
        });
        
        const result = await response.json();
        
        if (result.success && result.data && Array.isArray(result.data.components)) {
          setAvailableComponents(result.data.components);
          console.log('Umbral Editor: Loaded available components:', result.data.components);
        }
      } catch (error) {
        console.error('Umbral Editor: Error loading available components:', error);
      }
    };
    
    if (restNonce) {
      loadAvailableComponents();
    }
  }, [restNonce]);
  
  // Load components data from REST API
  useEffect(() => {
    if (!postId || !fieldId || fieldId === 'unknown') {
      setIsLoading(false);
      return;
    }
    
    const loadComponentsData = async () => {
      try {
        setIsLoading(true);
        const response = await fetch(`/wp-json/umbral-editor/v1/components-field/${postId}/${fieldId}`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': restNonce
          }
        });
        
        const result = await response.json();
        
        if (result.success && result.data && Array.isArray(result.data.components)) {
          const loadedComponents = result.data.components;
          setCurrentComponents(loadedComponents);
          setSavedComponents(loadedComponents);
          setHasUnsavedChanges(false);
          console.log('Umbral Editor: Loaded components from REST API:', loadedComponents);
        } else {
          console.log('Umbral Editor: No existing components data found');
          setCurrentComponents([]);
          setSavedComponents([]);
          setHasUnsavedChanges(false);
        }
      } catch (error) {
        console.error('Umbral Editor: Error loading components data:', error);
        setCurrentComponents([]);
      } finally {
        setIsLoading(false);
        setIsInitialLoad(false);
      }
    };
    
    loadComponentsData();
  }, [postId, fieldId, restNonce]);
  
  // Save components data to REST API
  const saveComponentsData = async (componentsToSave) => {
    if (!postId || !fieldId || fieldId === 'unknown') {
      console.error('Umbral Editor: Cannot save - missing post ID or field ID');
      return false;
    }
    
    // Prevent multiple concurrent saves
    if (isSavingRef.current) {
      console.log('Umbral Editor: Save already in progress, skipping duplicate request');
      return false;
    }
    
    try {
      isSavingRef.current = true;
      setIsSaving(true);
      
      const response = await fetch(`/wp-json/umbral-editor/v1/components-field/${postId}/${fieldId}`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': restNonce
        },
        body: JSON.stringify({
          components: componentsToSave
        })
      });
      
      const result = await response.json();
      
      if (result.success) {
        console.log('Umbral Editor: Components saved successfully via REST API:', result.data);
        setSavedComponents([...componentsToSave]);
        setHasUnsavedChanges(false);
        
        // Dispatch custom event for frontend editor (escape shadow DOM) - only after save
        const customEvent = new CustomEvent('umbral-components-updated', {
          detail: {
            type: 'components-updated',
            fieldId: fieldId,
            components: componentsToSave,
            timestamp: Date.now()
          },
          bubbles: true,
          composed: true // This allows the event to cross shadow DOM boundaries
        });
        
        document.dispatchEvent(customEvent);
        console.log('Umbral Editor: Dispatched components-updated event after save', customEvent.detail);
        
        return true;
      } else {
        console.error('Umbral Editor: Failed to save components:', result);
        return false;
      }
    } catch (error) {
      console.error('Umbral Editor: Error saving components data:', error);
      return false;
    } finally {
      isSavingRef.current = false;
      setIsSaving(false);
      
      // Check if we need to save again with newer data
      const currentLatest = latestComponentsRef.current;
      if (JSON.stringify(currentLatest) !== JSON.stringify(componentsToSave)) {
        console.log('Umbral Editor: Newer changes detected, scheduling follow-up save');
        scheduleAutoSave(currentLatest);
      }
    }
  };
  
  // Update hidden input immediately (for form compatibility) and detect changes
  useEffect(() => {
    // Update hidden input for form compatibility (immediate, not after save)
    const hiddenInput = document.getElementById(fieldId);
    if (hiddenInput) {
      const jsonData = JSON.stringify(currentComponents);
      hiddenInput.value = jsonData;
      
      // Trigger change event for form validation
      hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
      hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    // Check if there are unsaved changes (skip during initial load)
    if (!isLoading && savedComponents.length >= 0) {
      const hasChanges = JSON.stringify(currentComponents) !== JSON.stringify(savedComponents);
      setHasUnsavedChanges(hasChanges);
    }
  }, [currentComponents, savedComponents, fieldId, isLoading]);
  
  // Schedule auto-save with debouncing
  const scheduleAutoSave = useCallback((componentsToSave) => {
    // Clear any existing timer
    if (autoSaveTimerRef.current) {
      clearTimeout(autoSaveTimerRef.current);
    }
    
    // Don't schedule if we're in initial load or no changes
    if (isLoading || isInitialLoad) {
      return;
    }
    
    console.log('Umbral Editor: Scheduling auto-save in 300ms...');
    autoSaveTimerRef.current = setTimeout(async () => {
      autoSaveTimerRef.current = null;
      
      // Use the latest components from ref to avoid stale closure
      const latestComponents = latestComponentsRef.current;
      
      // Only save if we have changes and aren't already saving
      if (!isSavingRef.current && latestComponents.length >= 0) {
        const hasChanges = JSON.stringify(latestComponents) !== JSON.stringify(savedComponents);
        if (hasChanges) {
          console.log('Umbral Editor: Auto-saving components after 300ms idle...');
          await saveComponentsData(latestComponents);
        }
      }
    }, 300);
  }, [isLoading, isInitialLoad, savedComponents]);
  
  // Update refs when components change and trigger auto-save
  useEffect(() => {
    latestComponentsRef.current = currentComponents;
    
    if (!isLoading && !isInitialLoad) {
      const hasChanges = JSON.stringify(currentComponents) !== JSON.stringify(savedComponents);
      setHasUnsavedChanges(hasChanges);
      
      if (hasChanges) {
        scheduleAutoSave(currentComponents);
      }
    }
  }, [currentComponents, savedComponents, isLoading, isInitialLoad, scheduleAutoSave]);
  
  // Manual save function (keeping for backward compatibility if needed)
  const handleSave = async () => {
    if (autoSaveTimerRef.current) {
      clearTimeout(autoSaveTimerRef.current);
      autoSaveTimerRef.current = null;
    }
    const success = await saveComponentsData(currentComponents);
    return success;
  };
  
  // Cleanup timer on unmount
  useEffect(() => {
    return () => {
      if (autoSaveTimerRef.current) {
        clearTimeout(autoSaveTimerRef.current);
      }
    };
  }, []);
  
  // Handle adding a new component
  const handleAddComponent = (command) => {
    if (command.type === 'component') {
      console.log('Umbral Editor: Adding component:', command);
      const defaultFields = getDefaultFieldValues(command.fields || {});
      console.log('Umbral Editor: Default fields generated:', defaultFields);
      
      const newComponent = {
        id: `comp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
        category: command.category,
        component: command.component,
        fields: defaultFields
      };
      
      console.log('Umbral Editor: New component created:', newComponent);
      setCurrentComponents(prev => [...prev, newComponent]);
      setIsCommandPaletteOpen(false);
      setQuery('');
    }
  };
  
  // Get default values for component fields
  const getDefaultFieldValues = (fields) => {
    const defaults = {};
    Object.entries(fields).forEach(([key, field]) => {
      switch (field.type) {
        case 'text':
        case 'textarea':
        case 'wysiwyg':
        case 'email':
        case 'text_url':
        case 'oembed':
          defaults[key] = field.default || '';
          break;
        case 'checkbox':
          defaults[key] = field.default || false;
          break;
        case 'select':
        case 'radio':
          defaults[key] = field.default || (field.options ? Object.keys(field.options)[0] : '');
          break;
        case 'file':
          defaults[key] = field.default || null;
          break;
        case 'group':
          defaults[key] = field.default || [];
          break;
        default:
          defaults[key] = field.default || '';
      }
    });
    return defaults;
  };
  
  // Handle component updates (memoized to prevent infinite loops)
  const handleUpdateComponent = useCallback((componentId, updatedFields) => {
    console.log('Umbral Editor: Updating component', componentId, 'with fields:', updatedFields);
    setCurrentComponents(prev => {
      const updated = prev.map(comp => 
        comp.id === componentId 
          ? { ...comp, fields: { ...comp.fields, ...updatedFields } }
          : comp
      );
      console.log('Umbral Editor: New components state:', updated);
      return updated;
    });
  }, []);
  
  // Handle component deletion
  const handleDeleteComponent = useCallback((componentId) => {
    setCurrentComponents(prev => prev.filter(comp => comp.id !== componentId));
  }, []);
  
  // Handle component duplication
  const handleDuplicateComponent = useCallback((componentId) => {
    setCurrentComponents(prev => {
      const componentToDuplicate = prev.find(comp => comp.id === componentId);
      if (componentToDuplicate) {
        const duplicatedComponent = {
          ...componentToDuplicate,
          id: `comp_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
        };
        
        const originalIndex = prev.findIndex(comp => comp.id === componentId);
        const newComponents = [...prev];
        newComponents.splice(originalIndex + 1, 0, duplicatedComponent);
        
        return newComponents;
      }
      return prev;
    });
  }, []);
  
  // Handle component reordering
  const handleReorderComponents = useCallback((newOrder) => {
    setCurrentComponents(newOrder);
  }, []);
  
  return (
    <DndProvider backend={HTML5Backend}>
      <div className="umbral-components-field">
      {/* Loading State */}
      {isLoading && (
        <div className="umbral-loading-state">
          <div className="umbral-spinner"></div>
          <p>Loading components...</p>
        </div>
      )}
      
      {/* Add Component Button */}
      {!isLoading && (
        <div className="umbral-add-component-section">
          <div className="umbral-add-component-header">
            <button
              type="button"
              className="umbral-add-component-btn"
              onClick={() => setIsCommandPaletteOpen(true)}
              disabled={isSaving}
            >
              <span className="umbral-add-icon">+</span>
              Add Component
            </button>
            
            {/* Save Button and Status */}
            <div className="umbral-save-section">
              <button
                type="button"
                className={`umbral-save-btn ${
                  hasUnsavedChanges ? 'umbral-save-btn-unsaved' : 'umbral-save-btn-saved'
                }`}
                onClick={handleSave}
                disabled={isSaving || !hasUnsavedChanges}
              >
                {isSaving && (
                  <div className="umbral-saving-spinner"></div>
                )}
                {!isSaving && hasUnsavedChanges && (
                  <span>Save Changes</span>
                )}
                {!isSaving && !hasUnsavedChanges && (
                  <>
                    <span className="umbral-saved-icon">✓</span>
                    <span>Saved</span>
                  </>
                )}
              </button>
            </div>
            
          </div>
          
          {currentComponents.length === 0 && (
            <p className="umbral-empty-state">
              No components added yet. Click "Add Component" to get started!
            </p>
          )}
        </div>
      )}
      
      {/* Components List */}
      {currentComponents.length > 0 && (
        <ComponentsAccordion
          components={currentComponents}
          availableComponents={availableComponents}
          onUpdateComponent={handleUpdateComponent}
          onDeleteComponent={handleDeleteComponent}
          onDuplicateComponent={handleDuplicateComponent}
          onReorderComponents={handleReorderComponents}
          nonce={restNonce}
          restUrl="/wp-json/"
          onVisibilityToggle={onVisibilityToggle}
          visibilityComponent={visibilityComponent}
        />
      )}
      
      {/* Command Palette */}
      <AnimatePresence>
        {isCommandPaletteOpen && (
          <CommandPalette
            availableComponents={availableComponents}
            onComponentSelect={handleAddComponent}
            onClose={() => {
              setIsCommandPaletteOpen(false);
              setQuery('');
            }}
          />
        )}
      </AnimatePresence>
      
      <style jsx>{`
        .umbral-components-field {
          background: var(--bg-primary);
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
        
        
        .umbral-saved-icon {
          font-size: var(--font-size-base);
        }
        
        @keyframes pulse {
          0%, 100% { opacity: 1; }
          50% { opacity: 0.5; }
        }
        
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
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
      `}</style>
      </div>
    </DndProvider>
  );
}