import React, { useState, useEffect, useRef, useCallback } from 'react';
import { useDropzone } from 'react-dropzone';
import { motion, AnimatePresence } from 'framer-motion';
import { useDrag, useDrop } from 'react-dnd';

const REPEATER_ITEM_TYPE = 'repeater_item';

// Panel UI Components
function TabsRenderer({ panels, panelDefs, activePanel, setActivePanel, renderFields }) {
  const panelKeys = Object.keys(panels);
  
  return (
    <div className="umbral-panel-tabs">
      <div className="umbral-tab-nav">
        {panelKeys.map(panelKey => (
          <button
            key={panelKey}
            type="button"
            className={`umbral-tab ${activePanel === panelKey ? 'active' : ''}`}
            onClick={() => setActivePanel(panelKey)}
          >
            <span className="umbral-tab-icon">{panelDefs[panelKey]?.icon}</span>
            <span className="umbral-tab-label">{panelDefs[panelKey]?.label || panelKey}</span>
          </button>
        ))}
      </div>
      
      <AnimatePresence mode="wait">
        <motion.div
          key={activePanel}
          className="umbral-tab-content"
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: -10 }}
          transition={{ duration: 0.2 }}
        >
          {panelDefs[activePanel]?.description && (
            <p className="umbral-panel-description">{panelDefs[activePanel].description}</p>
          )}
          <div className="umbral-panel-fields ">
            {renderFields(panels[activePanel] || {})}
          </div>
        </motion.div>
      </AnimatePresence>
    </div>
  );
}

function AccordionRenderer({ panels, panelDefs, openPanels, togglePanel, renderFields }) {
  const panelKeys = Object.keys(panels);
  
  return (
    <div className="umbral-panel-accordion">
      {panelKeys.map(panelKey => (
        <div key={panelKey} className="umbral-accordion-item">
          <button
            type="button"
            className={`umbral-accordion-header ${openPanels[panelKey] ? 'open' : ''}`}
            onClick={() => togglePanel(panelKey)}
          >
            <div className="umbral-accordion-title">
              <span className="umbral-accordion-icon">{panelDefs[panelKey]?.icon}</span>
              <span className="umbral-accordion-label">{panelDefs[panelKey]?.label || panelKey}</span>
            </div>
            <span className={`umbral-accordion-chevron ${openPanels[panelKey] ? 'open' : ''}`}>▼</span>
          </button>
          
          <AnimatePresence>
            {openPanels[panelKey] && (
              <motion.div
                className="umbral-accordion-content"
                initial={{ height: 0, opacity: 0 }}
                animate={{ height: 'auto', opacity: 1 }}
                exit={{ height: 0, opacity: 0 }}
                transition={{ duration: 0.3, ease: [0.4, 0.0, 0.2, 1] }}
                style={{ overflow: 'hidden' }}
              >
                <div className="umbral-accordion-body">
                  {panelDefs[panelKey]?.description && (
                    <p className="umbral-panel-description">{panelDefs[panelKey].description}</p>
                  )}
                  <div className="umbral-panel-fields ">
                    {renderFields(panels[panelKey] || {})}
                  </div>
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </div>
      ))}
    </div>
  );
}

function SectionsRenderer({ panels, panelDefs, renderFields, subPanels, renderSubFields }) {
  const panelKeys = Object.keys(panels);
  const [activeSection, setActiveSection] = React.useState(null);
  const [activeSubPanel, setActiveSubPanel] = React.useState(null);
  
  // Initialize active sub-panel when section changes - MUST be before conditional returns
  React.useEffect(() => {
    if (activeSection !== null) {
      const sectionPanelDef = panelDefs[activeSection];
      console.log('SectionsRenderer: activeSection changed to', activeSection, 'panelDef:', sectionPanelDef);
      
      if (sectionPanelDef?.sub_panels && typeof sectionPanelDef.sub_panels === 'object') {
        const subPanelKeys = Object.keys(sectionPanelDef.sub_panels);
        const hasSubPanels = subPanelKeys.length > 0;
        console.log('SectionsRenderer: hasSubPanels:', hasSubPanels, 'subPanelKeys:', subPanelKeys);
        
        if (hasSubPanels) {
          const firstSubPanel = subPanelKeys[0];
          console.log('SectionsRenderer: setting activeSubPanel to', firstSubPanel);
          setActiveSubPanel(firstSubPanel);
        } else {
          setActiveSubPanel(null);
        }
      } else {
        console.log('SectionsRenderer: no sub_panels found, setting activeSubPanel to null');
        setActiveSubPanel(null);
      }
    } else {
      console.log('SectionsRenderer: activeSection is null, resetting activeSubPanel');
      setActiveSubPanel(null);
    }
  }, [activeSection, panelDefs]); // Remove activeSubPanel from dependencies to prevent loops
  
  // If editing a section, show the editor
  if (activeSection !== null) {
    const sectionPanelDef = panelDefs[activeSection];
    const hasSubPanels = sectionPanelDef?.sub_panels && Object.keys(sectionPanelDef.sub_panels).length > 0;
    const sectionStyle = sectionPanelDef?.style || 'default';
    
    return (
      <div className="umbral-panel-sections">
        <div className="umbral-section-editor-view">
          <div className="umbral-editor-header">
            <button
              type="button"
              className="umbral-back-btn"
              onClick={() => {
                setActiveSection(null);
                setActiveSubPanel(null);
              }}
              title="Back to sections list"
            >
              ←
            </button>
            <div className="umbral-editor-title">
              <div className="umbral-component-icon">
                {sectionPanelDef?.icon}
              </div>
              <div>
                <h3>{sectionPanelDef?.label || activeSection}</h3>
              </div>
            </div>
          </div>
          <div className="umbral-editor-content subpanel-content">
            {hasSubPanels && sectionStyle === 'tabs' ? (
              <div className="umbral-nested-tabs">
                <div className="umbral-tab-nav">
                  {Object.entries(sectionPanelDef.sub_panels).map(([subPanelKey, subPanelDef]) => (
                    <button
                      key={subPanelKey}
                      type="button"
                      className={`umbral-tab ${activeSubPanel === subPanelKey ? 'active' : ''}`}
                      onClick={() => setActiveSubPanel(subPanelKey)}
                    >
                      <span className="umbral-tab-icon">{subPanelDef?.icon}</span>
                      <span className="umbral-tab-label">{subPanelDef?.label || subPanelKey}</span>
                    </button>
                  ))}
                </div>
                
                <AnimatePresence mode="wait">
                  {activeSubPanel && (
                    <motion.div
                      key={activeSubPanel}
                      className="umbral-tab-content"
                      initial={{ opacity: 0, y: 10 }}
                      animate={{ opacity: 1, y: 0 }}
                      exit={{ opacity: 0, y: -10 }}
                      transition={{ duration: 0.2 }}
                    >
                      {sectionPanelDef.sub_panels?.[activeSubPanel]?.description && (
                        <p className="umbral-panel-description">
                          {sectionPanelDef.sub_panels[activeSubPanel].description}
                        </p>
                      )}
                      <div className="umbral-panel-fields ">
                        {renderSubFields && renderSubFields(activeSection, activeSubPanel)}
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>
            ) : (
              <div className="umbral-panel-fields ">
                {renderFields(panels[activeSection] || {})}
              </div>
            )}
          </div>
        </div>
      </div>
    );
  }
  
  // Otherwise show the sections list
  return (
    <div className="umbral-panel-sections">
      <div className="umbral-sections-list">
        <AnimatePresence>
          {panelKeys.map((panelKey, index) => (
            <motion.div
              key={panelKey}
              className="umbral-component-card umbral-section-item"
              layout
              variants={getItemVariants(index)}
              initial="hidden"
              animate="visible"
              exit="exit"
              whileHover={{ 
                y: -1,
                transition: { duration: 0.2 }
              }}
              whileTap={{ scale: 0.98 }}
            >
              <div className="umbral-card-header">
                <div 
                  className="umbral-card-content"
                  onClick={() => setActiveSection(panelKey)}
                >
                  <div className="umbral-component-icon">
                    {panelDefs[panelKey]?.icon || '📝'}
                  </div>
                  
                  <div className="umbral-component-details">
                    <h4 className="umbral-component-title">
                      {panelDefs[panelKey]?.label || panelKey}
                    </h4>
                    <p className="umbral-component-description">
                      {panelDefs[panelKey]?.description || 'Click to configure this section'}
                    </p>
                  </div>
                  
                  <div className="umbral-edit-arrow">→</div>
                </div>
              </div>
            </motion.div>
          ))}
        </AnimatePresence>
      </div>
    </div>
  );
}

// Animation variants for individual items with stagger delay
const getItemVariants = (index) => ({
  hidden: { 
    opacity: 0, 
    y: 20,
    scale: 0.95
  },
  visible: { 
    opacity: 1, 
    y: 0,
    scale: 1,
    transition: {
      delay: index * 0.1, // Individual delay based on index (100ms apart)
      duration: 0.4,
      ease: [0.4, 0.0, 0.2, 1]
    }
  },
  exit: { 
    opacity: 0, 
    y: -10,
    scale: 0.95,
    transition: {
      duration: 0.2,
      ease: "easeIn"
    }
  }
});

// Simple Repeater Item Component (no drag/drop)
function SimpleRepeaterItem({ 
  groupItem, 
  index, 
  fieldDef, 
  onEdit, 
  onDuplicate, 
  onDelete
}) {
  return (
    <motion.div
      className="umbral-component-card umbral-repeater-item"
      layout
      variants={getItemVariants(index)}
      initial="hidden"
      animate="visible"
      exit="exit"
      whileHover={{ 
        y: -1,
        transition: { duration: 0.2 }
      }}
      whileTap={{ scale: 0.98 }}
    >
      <div className="umbral-card-header">
        <div 
          className="umbral-card-content"
          onClick={() => onEdit(index)}
        >
          <div className="umbral-component-icon">
            {fieldDef.icon || '📝'}
          </div>
          
          <div className="umbral-component-details">
            <h4 className="umbral-component-title">
              {fieldDef.options?.group_title?.replace('{#}', index + 1) || `Item ${index + 1}`}
            </h4>
          </div>
          
          <div className="umbral-edit-arrow">→</div>
        </div>
        
        <div className="umbral-component-actions">
          <button
            type="button"
            className="umbral-action-btn umbral-duplicate-btn"
            onClick={(e) => {
              e.stopPropagation();
              onDuplicate(index);
            }}
            title="Duplicate item"
          >
            📋
          </button>
          <button
            type="button"
            className="umbral-action-btn umbral-delete-btn"
            onClick={(e) => {
              e.stopPropagation();
              onDelete(index);
            }}
            title="Delete item"
          >
            🗑️
          </button>
        </div>
      </div>
    </motion.div>
  );
}

// Modern File Dropzone Component with WordPress Media Integration
function FileDropzone({ fieldId, value, isUploading, onFileSelect, onRemove, onMediaSelect, accept = "image/*" }) {
  // Ensure value is always a primitive (handle legacy object values)
  const attachmentId = typeof value === 'object' && value !== null ? value.id : value;
  const {
    getRootProps,
    getInputProps,
    isDragActive,
    isDragReject,
    isDragAccept
  } = useDropzone({
    accept: {
      'image/*': ['.jpeg', '.jpg', '.png', '.gif', '.webp']
    },
    multiple: false,
    noClick: true, // Disable default click to open file dialog
    onDrop: (acceptedFiles) => {
      if (acceptedFiles[0]) {
        onFileSelect(acceptedFiles[0]);
      }
    }
  });

  // Open WordPress Media Manager
  const openMediaManager = () => {
    if (typeof wp !== 'undefined' && wp.media) {
      const mediaFrame = wp.media({
        title: 'Select or Upload Media',
        button: {
          text: 'Use this media'
        },
        multiple: false,
        library: {
          type: 'image'
        }
      });

      mediaFrame.on('select', function() {
        const attachment = mediaFrame.state().get('selection').first().toJSON();
        onMediaSelect(attachment.id, attachment);
      });

      mediaFrame.open();
    } else {
      console.warn('WordPress media manager not available');
    }
  };

  const getDropzoneClass = () => {
    let baseClass = 'umbral-dropzone';
    if (isDragActive) baseClass += ' umbral-dropzone-active';
    if (isDragAccept) baseClass += ' umbral-dropzone-accept';
    if (isDragReject) baseClass += ' umbral-dropzone-reject';
    if (isUploading) baseClass += ' umbral-dropzone-uploading';
    if (value) baseClass += ' umbral-dropzone-success';
    return baseClass;
  };

  return (
    <div className="umbral-file-field">
      {(!attachmentId || attachmentId === '' || attachmentId === null) && (
        <>
          <div 
            {...getRootProps()} 
            className={getDropzoneClass()}
            onClick={openMediaManager}
          >
            <input {...getInputProps()} id={fieldId} />
            <div className="umbral-dropzone-content">
              {isUploading ? (
                <>
                  <div className="umbral-upload-spinner"></div>
                  <p>Uploading to Media Library...</p>
                </>
              ) : (
                <>
                  <div className="umbral-dropzone-icon">
                    {isDragActive ? '📤' : '📁'}
                  </div>
                  <p className="umbral-dropzone-text">
                    {isDragActive
                      ? 'Drop to upload and select from Media Library'
                      : 'Drag & drop to upload, or click to open Media Library'
                    }
                  </p>
                  <p className="umbral-dropzone-hint">
                    Supports: JPG, PNG, GIF, WebP
                  </p>
                </>
              )}
            </div>
          </div>
          
          <div className="umbral-media-actions">
            <button
              type="button"
              onClick={openMediaManager}
              className="umbral-media-btn"
              disabled={isUploading}
            >
              📷 Browse Media Library
            </button>
          </div>
        </>
      )}

      {attachmentId && attachmentId !== '' && attachmentId !== null && !isUploading && (
        <div className="umbral-file-preview">
          <div className="umbral-file-info">
            <span className="umbral-file-icon">✅</span>
            <span>Media selected (ID: {attachmentId})</span>
          </div>
          <div className="umbral-file-actions">
            <button
              type="button"
              onClick={openMediaManager}
              className="umbral-change-file"
            >
              Change
            </button>
            <button
              type="button"
              onClick={onRemove}
              className="umbral-remove-file"
            >
              Remove
            </button>
          </div>
        </div>
      )}
    </div>
  );
}

export function ComponentEditor({ 
  component, 
  componentDef, 
  onUpdateFields,
  nonce,
  restUrl 
}) {
  // Clean up field values to ensure file fields are primitives
  const cleanFieldValues = (fields) => {
    const cleaned = { ...fields };
    Object.entries(componentDef.fields || {}).forEach(([key, fieldDef]) => {
      if (fieldDef.type === 'file' && cleaned[key]) {
        // Convert objects to attachment ID
        if (typeof cleaned[key] === 'object' && cleaned[key] !== null) {
          cleaned[key] = cleaned[key].id ? parseInt(cleaned[key].id) : null;
        } else if (cleaned[key]) {
          cleaned[key] = parseInt(cleaned[key]);
        }
      }
    });
    return cleaned;
  };
  
  const [fieldValues, setFieldValues] = useState(cleanFieldValues(component.fields || {}));
  const [isUploading, setIsUploading] = useState({});
  const [editingRepeaterItem, setEditingRepeaterItem] = useState(null);
  const isInitialMount = useRef(true);
  const lastFieldValues = useRef(component.fields || {});
  
  // Panel state
  const [activePanel, setActivePanel] = useState(null);
  const [openPanels, setOpenPanels] = useState({});
  
  // Process panels and UI configuration
  const uiConfig = componentDef.fields?._ui_config || {};
  const panelDefs = componentDef.fields?._panels || {};
  const uiStyle = uiConfig.style || 'default';
  
  // Group fields by panel and sub-panel
  const { panels, subPanels, noPanelFields } = React.useMemo(() => {
    const panels = {};
    const subPanels = {};
    const noPanelFields = {};
    
    Object.entries(componentDef.fields || {}).forEach(([key, field]) => {
      // Skip internal panel configuration
      if (key.startsWith('_')) return;
      
      if (field.panel && panelDefs[field.panel]) {
        if (!panels[field.panel]) panels[field.panel] = {};
        
        // If field has sub_panel, group it separately
        if (field.sub_panel) {
          if (!subPanels[field.panel]) subPanels[field.panel] = {};
          if (!subPanels[field.panel][field.sub_panel]) subPanels[field.panel][field.sub_panel] = {};
          subPanels[field.panel][field.sub_panel][key] = field;
        } else {
          panels[field.panel][key] = field;
        }
      } else {
        noPanelFields[key] = field;
      }
    });
    
    console.log('ComponentEditor: panels grouping result:', { panels, subPanels, noPanelFields });
    return { panels, subPanels, noPanelFields };
  }, [componentDef.fields, panelDefs]);
  
  const hasPanels = Object.keys(panels).length > 0;
  
  // Initialize active panel
  useEffect(() => {
    if (hasPanels && !activePanel) {
      const firstPanel = Object.keys(panels)[0];
      setActivePanel(firstPanel);
      
      // Initialize accordion open state
      const defaultOpen = uiConfig.default_open || [firstPanel];
      const initialOpenState = {};
      defaultOpen.forEach(panelKey => {
        if (panels[panelKey]) {
          initialOpenState[panelKey] = true;
        }
      });
      setOpenPanels(initialOpenState);
    }
  }, [hasPanels, panels, activePanel, uiConfig.default_open]);
  
  // Panel toggle for accordion - only one panel open at a time
  const togglePanel = useCallback((panelKey) => {
    setOpenPanels(prev => {
      const isCurrentlyOpen = prev[panelKey];
      // Close all panels first, then open the clicked one (unless it was already open)
      const newState = {};
      Object.keys(panels).forEach(key => {
        newState[key] = false;
      });
      // If the clicked panel wasn't open, open it
      if (!isCurrentlyOpen) {
        newState[panelKey] = true;
      }
      return newState;
    });
  }, [panels]);
  
  // Reset editing state when component changes
  useEffect(() => {
    setEditingRepeaterItem(null);
  }, [component.id]);
  
  // Memoized update function to prevent unnecessary re-renders
  const updateFields = useCallback((values) => {
    // Compare values to prevent unnecessary updates
    const valuesChanged = JSON.stringify(values) !== JSON.stringify(lastFieldValues.current);
    
    if (valuesChanged) {
      console.log('Umbral Editor: Component field values changed:', values);
      lastFieldValues.current = values;
      onUpdateFields(values);
    }
  }, [onUpdateFields]);
  
  // Update parent when field values change (but skip initial mount)
  useEffect(() => {
    if (isInitialMount.current) {
      isInitialMount.current = false;
      return;
    }
    
    updateFields(fieldValues);
  }, [fieldValues, updateFields]);
  
  // Handle field value change
  const handleFieldChange = (fieldKey, value) => {
    // For file fields, ensure we store integers
    const fieldDef = componentDef.fields[fieldKey];
    let cleanValue = value;
    
    if (fieldDef && fieldDef.type === 'file') {
      if (typeof value === 'object' && value !== null) {
        cleanValue = value.id ? parseInt(value.id) : null;
      } else if (value && value !== null) {
        cleanValue = parseInt(value);
      } else {
        cleanValue = null;
      }
    }
    
    setFieldValues(prev => ({
      ...prev,
      [fieldKey]: cleanValue
    }));
  };
  
  // Handle file upload and open Media Manager
  const handleFileUpload = async (fieldKey, file) => {
    if (!file) return;
    
    setIsUploading(prev => ({ ...prev, [fieldKey]: true }));
    
    const formData = new FormData();
    formData.append('file', file);
    
    try {
      const response = await fetch(`${restUrl}umbral-editor/v1/upload`, {
        method: 'POST',
        headers: {
          'X-WP-Nonce': nonce
        },
        body: formData
      });
      
      const result = await response.json();
      
      if (result.success && result.data && result.data.id) {
        // File uploaded successfully, now open Media Manager
        if (typeof wp !== 'undefined' && wp.media) {
          const mediaFrame = wp.media({
            title: 'Select or Upload Media',
            button: {
              text: 'Use this media'
            },
            multiple: false,
            library: {
              type: 'image'
            }
          });

          mediaFrame.on('select', function() {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            // Store only the attachment ID (CMB2 standard)
            handleFieldChange(fieldKey, parseInt(attachment.id));
          });

          mediaFrame.open();
        } else {
          // Fallback if Media Manager not available - store only ID
          handleFieldChange(fieldKey, parseInt(result.data.id));
        }
      } else {
        console.error('Upload failed:', result);
        const errorMessage = result.message || 'File upload failed. Please try again.';
        alert(errorMessage);
      }
    } catch (error) {
      console.error('Upload error:', error);
      alert('File upload failed. Please try again.');
    } finally {
      setIsUploading(prev => ({ ...prev, [fieldKey]: false }));
    }
  };
  
  // Handle group field changes
  const handleGroupChange = (fieldKey, groupIndex, subFieldKey, value) => {
    const currentGroup = fieldValues[fieldKey] || [];
    const newGroup = [...currentGroup];
    
    if (!newGroup[groupIndex]) {
      newGroup[groupIndex] = {};
    }
    
    newGroup[groupIndex][subFieldKey] = value;
    handleFieldChange(fieldKey, newGroup);
  };
  
  // Add group item
  const addGroupItem = (fieldKey) => {
    const currentGroup = fieldValues[fieldKey] || [];
    const fieldDef = componentDef.fields[fieldKey];
    const defaultItem = {};
    
    // Set defaults for group fields
    if (fieldDef.fields) {
      Object.entries(fieldDef.fields).forEach(([key, field]) => {
        defaultItem[key] = field.default || '';
      });
    }
    
    handleFieldChange(fieldKey, [...currentGroup, defaultItem]);
  };
  
  // Remove group item
  const removeGroupItem = (fieldKey, index) => {
    const currentGroup = fieldValues[fieldKey] || [];
    const newGroup = currentGroup.filter((_, i) => i !== index);
    handleFieldChange(fieldKey, newGroup);
  };
  
  // Render individual field
  const renderField = (fieldKey, fieldDef, value, parentKey = null, groupIndex = null) => {
    const fieldId = parentKey ? `${parentKey}_${groupIndex}_${fieldKey}` : fieldKey;
    const isRequired = fieldDef.required;
    
    const onChange = (newValue) => {
      if (parentKey !== null && groupIndex !== null) {
        handleGroupChange(parentKey, groupIndex, fieldKey, newValue);
      } else {
        handleFieldChange(fieldKey, newValue);
      }
    };
    
    switch (fieldDef.type) {
      case 'text':
      case 'text_small':
      case 'email':
        return (
          <input
            type={fieldDef.type === 'email' ? 'email' : 'text'}
            id={fieldId}
            value={value || ''}
            onChange={(e) => onChange(e.target.value)}
            className="umbral-field-input"
            placeholder={fieldDef.placeholder || ''}
            required={isRequired}
          />
        );
        
      case 'text_url':
        return (
          <input
            type="url"
            id={fieldId}
            value={value || ''}
            onChange={(e) => onChange(e.target.value)}
            className="umbral-field-input"
            placeholder={fieldDef.placeholder || 'https://'}
            required={isRequired}
          />
        );
        
      case 'textarea':
        return (
          <textarea
            id={fieldId}
            value={value || ''}
            onChange={(e) => onChange(e.target.value)}
            className="umbral-field-textarea"
            rows={fieldDef.rows || 4}
            placeholder={fieldDef.placeholder || ''}
            required={isRequired}
          />
        );
        
      case 'wysiwyg':
        return (
          <div className="umbral-wysiwyg-field">
            <textarea
              id={fieldId}
              value={value || ''}
              onChange={(e) => onChange(e.target.value)}
              className="umbral-field-textarea"
              rows={fieldDef.rows || 6}
              placeholder="Enter HTML content..."
              required={isRequired}
            />
            <small className="umbral-field-note">
              HTML content - basic formatting allowed
            </small>
          </div>
        );
        
      case 'select':
        return (
          <select
            id={fieldId}
            value={value || ''}
            onChange={(e) => onChange(e.target.value)}
            className="umbral-field-select"
            required={isRequired}
          >
            <option value="">Select an option...</option>
            {Object.entries(fieldDef.options || {}).map(([optValue, optLabel]) => (
              <option key={optValue} value={optValue}>
                {optLabel}
              </option>
            ))}
          </select>
        );
        
      case 'radio':
        return (
          <div className="umbral-radio-group">
            {Object.entries(fieldDef.options || {}).map(([optValue, optLabel]) => (
              <label key={optValue} className="umbral-radio-label">
                <input
                  type="radio"
                  name={fieldId}
                  value={optValue}
                  checked={value === optValue}
                  onChange={(e) => onChange(e.target.value)}
                  required={isRequired}
                />
                <span>{optLabel}</span>
              </label>
            ))}
          </div>
        );
        
      case 'checkbox':
        return (
          <label className="umbral-checkbox-label">
            <input
              type="checkbox"
              id={fieldId}
              checked={!!value}
              onChange={(e) => onChange(e.target.checked)}
            />
            <span>{fieldDef.options?.checkbox_label || 'Enable this option'}</span>
          </label>
        );
        
      case 'file':
        return (
          <FileDropzone
            fieldId={fieldId}
            value={value}
            isUploading={isUploading[parentKey || fieldKey]}
            onFileSelect={(file) => handleFileUpload(parentKey || fieldKey, file)}
            onRemove={() => onChange(null)}
            onMediaSelect={(attachmentId, attachment) => {
              // Store only the attachment ID (CMB2 standard)
              onChange(parseInt(attachmentId));
            }}
            accept="image/*"
          />
        );
        
      case 'oembed':
        return (
          <div className="umbral-oembed-field">
            <input
              type="url"
              id={fieldId}
              value={value || ''}
              onChange={(e) => onChange(e.target.value)}
              className="umbral-field-input"
              placeholder="YouTube, Vimeo, or other media URL"
              required={isRequired}
            />
            {value && (
              <div className="umbral-oembed-preview">
                <small>Preview will be generated on frontend</small>
              </div>
            )}
          </div>
        );
        
      case 'group':
        const groupValues = value || [];
        
        // If editing an item, show the editor
        if (editingRepeaterItem !== null) {
          return (
            <div className="umbral-group-field">
              <div className="umbral-component-editor-view">
                <div className="umbral-editor-header">
                  <button
                    type="button"
                    className="umbral-back-btn"
                    onClick={() => setEditingRepeaterItem(null)}
                    title="Back to list"
                  >
                    ←
                  </button>
                  <div className="umbral-editor-title">
                    <div className="umbral-component-icon">
                      {fieldDef.icon || '📝'}
                    </div>
                    <div>
                      <h3>
                        {fieldDef.options?.group_title?.replace('{#}', editingRepeaterItem + 1) || `Item ${editingRepeaterItem + 1}`}
                      </h3>
                      <p>{fieldDef.description || 'Edit this item'}</p>
                    </div>
                  </div>
                </div>
                <div className="umbral-editor-content repeater-editor-content">
                  {Object.entries(fieldDef.fields || {}).map(([subFieldKey, subFieldDef]) => (
                    <div key={subFieldKey} className="umbral-field-group">
                      <label className="umbral-field-label">
                        {subFieldDef.label || subFieldDef.title || subFieldKey}
                        {subFieldDef.required && <span className="required">*</span>}
                      </label>
                      {renderField(subFieldKey, subFieldDef, groupValues[editingRepeaterItem]?.[subFieldKey], fieldKey, editingRepeaterItem)}
                    </div>
                  ))}
                </div>
              </div>
            </div>
          );
        }
        
        // Otherwise show the list view
        return (
          <div className="umbral-group-field">
            <div className="umbral-components-list">
              <AnimatePresence>
                {groupValues.map((groupItem, index) => (
                  <SimpleRepeaterItem
                    key={index}
                    groupItem={groupItem}
                    index={index}
                    fieldDef={fieldDef}
                    onEdit={setEditingRepeaterItem}
                    onDuplicate={(itemIndex) => {
                      // Duplicate the item
                      const newItem = { ...groupValues[itemIndex] };
                      const newValues = [...groupValues];
                      newValues.splice(itemIndex + 1, 0, newItem);
                      handleFieldChange(fieldKey, newValues);
                    }}
                    onDelete={(itemIndex) => {
                      removeGroupItem(fieldKey, itemIndex);
                    }}
                  />
                ))}
              </AnimatePresence>
            </div>
            
            <motion.button
              type="button"
              onClick={() => {
                const newIndex = groupValues.length;
                addGroupItem(fieldKey);
                // Auto-edit the new item
                setEditingRepeaterItem(newIndex);
              }}
              className="umbral-add-group-item umbral-add-panel-item"
              whileHover={{ scale: 1.02 }}
              whileTap={{ scale: 0.98 }}
            >
              <span className="umbral-add-icon">+</span>
              {fieldDef.options?.add_button || 'Add Item'}
            </motion.button>
          </div>
        );
        
      default:
        return (
          <input
            type="text"
            id={fieldId}
            value={value || ''}
            onChange={(e) => onChange(e.target.value)}
            className="umbral-field-input"
            placeholder={fieldDef.placeholder || ''}
            required={isRequired}
          />
        );
    }
  };
  
  // Render fields function for reuse in panels
  const renderFields = useCallback((fieldsToRender) => {
    return Object.entries(fieldsToRender).map(([fieldKey, fieldDef]) => (
      <div key={fieldKey} className="umbral-field-group">
        <label htmlFor={fieldKey} className="umbral-field-label">
          {fieldDef.label || fieldDef.title || fieldKey}
          {fieldDef.required && <span className="required">*</span>}
        </label>
        
        {(fieldDef.description || fieldDef.desc) && (
          <p className="umbral-field-description">{fieldDef.description || fieldDef.desc}</p>
        )}
        
        {renderField(fieldKey, fieldDef, fieldValues[fieldKey])}
      </div>
    ));
  }, [fieldValues, renderField]);
  
  // Render sub-panel fields function
  const renderSubFields = useCallback((panelKey, subPanelKey) => {
    console.log('renderSubFields called with:', { panelKey, subPanelKey, subPanels });
    
    if (!panelKey || !subPanelKey) {
      console.warn('renderSubFields: missing panelKey or subPanelKey');
      return [];
    }
    
    if (!subPanels || typeof subPanels !== 'object') {
      console.warn('renderSubFields: subPanels is not an object', subPanels);
      return [];
    }
    
    const fieldsToRender = subPanels[panelKey]?.[subPanelKey] || {};
    console.log('renderSubFields: fieldsToRender:', fieldsToRender);
    
    return Object.entries(fieldsToRender).map(([fieldKey, fieldDef]) => (
      <div key={fieldKey} className="umbral-field-group subpanel-field">
        <label htmlFor={fieldKey} className="umbral-field-label">
          {fieldDef.label || fieldDef.title || fieldKey}
          {fieldDef.required && <span className="required">*</span>}
        </label>
        
        {(fieldDef.description || fieldDef.desc) && (
          <p className="umbral-field-description">{fieldDef.description || fieldDef.desc}</p>
        )}
        
        {renderField(fieldKey, fieldDef, fieldValues[fieldKey])}
      </div>
    ));
  }, [fieldValues, renderField, subPanels]);

  return (
    <div className="umbral-component-editor">
      {hasPanels ? (
        <div className="umbral-panelized-editor">
          {uiStyle === 'tabs' && (
            <TabsRenderer
              panels={panels}
              panelDefs={panelDefs}
              activePanel={activePanel}
              setActivePanel={setActivePanel}
              renderFields={renderFields}
            />
          )}
          
          {uiStyle === 'accordion' && (
            <AccordionRenderer
              panels={panels}
              panelDefs={panelDefs}
              openPanels={openPanels}
              togglePanel={togglePanel}
              renderFields={renderFields}
            />
          )}
          
          {uiStyle === 'sections' && (
            <SectionsRenderer
              panels={panels}
              panelDefs={panelDefs}
              renderFields={renderFields}
              subPanels={subPanels}
              renderSubFields={renderSubFields}
            />
          )}
          
          {/* Non-panel fields (if any) */}
          {Object.keys(noPanelFields).length > 0 && (
            <div className="umbral-non-panel-fields">
              <div className="umbral-editor-fields">
                {renderFields(noPanelFields)}
              </div>
            </div>
          )}
        </div>
      ) : (
        <div className="umbral-editor-fields">
          {renderFields(componentDef.fields || {})}
        </div>
      )}
      
      <style jsx>{`
        .umbral-component-editor {
          padding: 0;
          background: #ffffff;
          border: none;
        }
        
        .umbral-editor-fields {
          display: grid;
          gap: 20px;
          padding: 16px 20px;
        }
        
        .umbral-field-group {
          display: flex;
          flex-direction: column;
          gap: 6px;
          margin-bottom: 4px;
        }
        
        .umbral-field-label {
          font-weight: 600;
          color: #1e1e1e;
          font-size: 14px;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
          margin-bottom: 2px;
        }
        
        .umbral-field-label .required {
          color: #d63638;
          margin-left: 4px;
        }
        
        .umbral-field-description {
          margin: 0 0 8px 0;
          font-size: 13px;
          color: #50575e;
          line-height: 1.4;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-field-input,
        .umbral-field-textarea,
        .umbral-field-select {
          padding: 10px 12px;
          border: 1px solid #dcdcde;
          border-radius: 4px;
          font-size: 14px;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
          line-height: 1.4;
          background: #ffffff;
          transition: all 0.2s ease;
          color: #1e1e1e;
        }
        
        .umbral-field-input:focus,
        .umbral-field-textarea:focus,
        .umbral-field-select:focus {
          outline: none;
          border-color: #2271b1;
          box-shadow: 0 0 0 1px #2271b1;
        }
        
        .umbral-field-input::placeholder,
        .umbral-field-textarea::placeholder {
          color: #8c8f94;
        }
        
        .umbral-field-textarea {
          resize: vertical;
          min-height: 80px;
        }
        
        .umbral-radio-group {
          display: flex;
          flex-direction: column;
          gap: 12px;
          margin-top: 4px;
        }
        
        .umbral-radio-label,
        .umbral-checkbox-label {
          display: flex;
          align-items: center;
          gap: 8px;
          font-size: 14px;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
          cursor: pointer;
          color: #1e1e1e;
          padding: 8px 12px;
          border: 1px solid #dcdcde;
          border-radius: 4px;
          background: #ffffff;
          transition: all 0.2s ease;
        }
        
        .umbral-radio-label:hover,
        .umbral-checkbox-label:hover {
          border-color: #c3c4c7;
          background: #f9f9f9;
        }
        
        .umbral-radio-label input,
        .umbral-checkbox-label input {
          margin: 0;
        }
        
        .umbral-file-field {
          display: flex;
          flex-direction: column;
          gap: 8px;
        }
        
        /* Modern Dropzone Styles */
        .umbral-dropzone {
          border: 2px dashed #dcdcde;
          border-radius: 8px;
          padding: 32px 24px;
          text-align: center;
          background: #f9f9f9;
          cursor: pointer;
          transition: all 0.2s ease;
          position: relative;
          overflow: hidden;
        }
        
        .umbral-dropzone:hover {
          border-color: #c3c4c7;
          background: #f6f7f8;
        }
        
        .umbral-dropzone-active {
          border-color: #2271b1;
          background: #f0f6fc;
          transform: scale(1.02);
        }
        
        .umbral-dropzone-accept {
          border-color: #00a32a;
          background: #edfaef;
        }
        
        .umbral-dropzone-reject {
          border-color: #d63638;
          background: #fcf0f1;
        }
        
        .umbral-dropzone-uploading {
          border-color: #2271b1;
          background: #f0f6fc;
          cursor: not-allowed;
        }
        
        .umbral-dropzone-content {
          display: flex;
          flex-direction: column;
          align-items: center;
          gap: 8px;
        }
        
        .umbral-dropzone-icon {
          font-size: 32px;
          margin-bottom: 8px;
          transition: transform 0.2s ease;
        }
        
        .umbral-dropzone-active .umbral-dropzone-icon {
          transform: scale(1.1);
        }
        
        .umbral-dropzone-text {
          margin: 0;
          font-size: 16px;
          font-weight: 500;
          color: #1e1e1e;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-dropzone-hint {
          margin: 0;
          font-size: 12px;
          color: #50575e;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-upload-spinner {
          width: 24px;
          height: 24px;
          border: 2px solid #f3f3f3;
          border-top: 2px solid #2271b1;
          border-radius: 50%;
          animation: spin 1s linear infinite;
          margin-bottom: 8px;
        }
        
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
        
        /* File Preview Styles */
        .umbral-file-preview {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 12px 16px;
          background: #edfaef;
          border-radius: 6px;
          border: 1px solid #00a32a;
          font-size: 14px;
        }
        
        .umbral-file-info {
          display: flex;
          align-items: center;
          gap: 8px;
        }
        
        .umbral-file-icon {
          font-size: 16px;
        }
        
        .umbral-file-actions {
          display: flex;
          align-items: center;
          gap: 8px;
        }
        
        .umbral-change-file {
          background: #2271b1;
          color: #ffffff;
          border: none;
          padding: 6px 12px;
          border-radius: 4px;
          cursor: pointer;
          font-size: 12px;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
          font-weight: 500;
          transition: background-color 0.2s ease;
        }
        
        .umbral-change-file:hover {
          background: #135e96;
        }
        
        .umbral-remove-file {
          background: #d63638;
          color: #ffffff;
          border: none;
          padding: 6px 12px;
          border-radius: 4px;
          cursor: pointer;
          font-size: 12px;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
          font-weight: 500;
          transition: background-color 0.2s ease;
        }
        
        .umbral-remove-file:hover {
          background: #b32d2e;
        }
        
        .umbral-media-actions {
          display: flex;
          justify-content: center;
          margin-top: 12px;
        }
        
        .umbral-media-btn {
          background: #f6f7f8;
          color: #2271b1;
          border: 1px solid #dcdcde;
          padding: 8px 16px;
          border-radius: 4px;
          cursor: pointer;
          font-size: 13px;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
          font-weight: 500;
          transition: all 0.2s ease;
          display: flex;
          align-items: center;
          gap: 6px;
        }
        
        .umbral-media-btn:hover {
          background: #f0f0f1;
          border-color: #c3c4c7;
        }
        
        .umbral-media-btn:disabled {
          opacity: 0.6;
          cursor: not-allowed;
        }
        
        .umbral-group-field {
          display: flex;
          flex-direction: column;
          gap: 12px;
          margin-top: 8px;
        }
        
        /* Component Card Styles for Repeater Items */
        .umbral-components-list {
          display: flex;
          flex-direction: column;
          overflow: hidden;
        }
        
        .umbral-component-card {
          background: #ffffff;
          border-bottom: 1px solid #f0f0f0;
        }
        
        .umbral-component-card:last-child {
          border-bottom: none;
        }
        
        .umbral-component-card:hover {
          background: #f9f9f9;
        }
        
        .umbral-component-card.dragging {
          opacity: 0.5;
          transform: rotate(2deg);
          border-radius: 6px;
          border: 1px solid #dcdcde;
          box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }
        
        .umbral-card-header {
          display: flex;
          align-items: center;
          gap: 16px;
          
          background: #ffffff;
          user-select: none;
        }
        
        .umbral-card-content {
          display: flex;
          align-items: center;
          gap: 16px;
          flex: 1;
          cursor: pointer;
          padding: 8px 12px;
          border-radius: 6px;
          min-height: 48px;
        }
        
        .umbral-card-content:hover {
          background: #f0f0f0;
        }
        
        .umbral-drag-handle {
          color: #8c8f94;
          cursor: grab;
          font-size: 12px;
          line-height: 1;
        }
        
        .umbral-drag-handle:active {
          cursor: grabbing;
        }
        
        .umbral-component-details {
          flex: 1;
        }
        
        .umbral-component-title {
          margin: 0 0 4px 0;
          font-size: 16px;
          font-weight: 600;
          color: #1e1e1e;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-component-description {
          margin: 0;
          font-size: 14px;
          color: #50575e;
          line-height: 1.4;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-edit-arrow {
          color: #8c8f94;
          font-size: 16px;
          margin-left: 12px;
        }
        
        .umbral-component-actions {
          display: flex;
          gap: 8px;
          margin-left: 16px;
        }
        
        .umbral-action-btn {
          width: 32px;
          height: 32px;
          border: none;
          border-radius: 4px;
          background: #f6f7f8;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 14px;
          transition: background-color 0.2s ease;
        }
        
        .umbral-action-btn:hover {
          background: #dcdcde;
        }
        
        .umbral-delete-btn:hover {
          background: #fcf0f1;
          color: #d63638;
        }
        
        .umbral-duplicate-btn:hover {
          background: #f0f6fc;
          color: #72aee6;
        }
        
        /* Component Editor View Styles */
        .umbral-component-editor-view {
          background: #ffffff;
          border: 1px solid #dcdcde;
          border-radius: 6px;
          overflow: hidden;
        }
        
        .umbral-editor-header {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 8px 12px;
          background: #f9f9f9;
          border-bottom: 1px solid #f0f0f0;
        }
        
        .umbral-back-btn {
          background: none;
          border: 1px solid #dcdcde;
          padding: 8px;
          border-radius: 4px;
          cursor: pointer;
          font-size: 16px;
          color: #50575e;
          transition: all 0.2s ease;
          width: 32px;
          height: 32px;
          display: flex;
          align-items: center;
          justify-content: center;
          flex-shrink: 0;
        }
        
        .umbral-back-btn:hover {
          background: #f0f0f0;
          border-color: #c3c4c7;
        }
        
        .umbral-editor-title {
          display: flex;
          align-items: center;
          gap: 12px;
          flex: 1;
        }
        
        .umbral-editor-title h3 {
          margin: 0;
          font-size: 16px;
          color: #1e1e1e;
          font-weight: 600;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-editor-title p {
          margin: 0;
          font-size: 14px;
          color: #50575e;
          line-height: 1.4;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-editor-content {
          padding: 0;
        }
        
        .umbral-add-group-item.umbral-add-panel-item {
          width: 100%;
          padding: 16px;
          background: #f8f9fa;
          color: #2271b1;
          border: 2px dashed #c3c4c7;
          border-radius: 8px;
          cursor: pointer;
          font-size: 14px;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
          font-weight: 500;
          transition: all 0.2s ease;
          display: flex;
          align-items: center;
          justify-content: center;
          gap: 8px;
        }
        
        .umbral-add-group-item.umbral-add-panel-item:hover {
          background: #f0f6ff;
          border-color: #2271b1;
          color: #135e96;
        }
        
        .umbral-add-icon {
          font-size: 18px;
          font-weight: bold;
        }
        
        .umbral-wysiwyg-field {
          display: flex;
          flex-direction: column;
          gap: 4px;
        }
        
        .umbral-field-note {
          color: #50575e;
          font-size: 12px;
          font-style: italic;
          margin-top: 4px;
        }
        
        .umbral-oembed-field {
          display: flex;
          flex-direction: column;
          gap: 8px;
        }
        
        .umbral-oembed-preview {
          padding: 8px 12px;
          background: #f0f6fc;
          border-radius: 4px;
          font-size: 12px;
          color: #50575e;
          border-left: 3px solid #72aee6;
          font-style: italic;
        }
        
        /* Panel Styles */
        .umbral-panelized-editor {
          display: flex;
          flex-direction: column;
          gap: 0;
          padding: 0;
        }
        
        .umbral-non-panel-fields {
          margin-top: 24px;
          padding-top: 24px;
          border-top: 1px solid #f0f0f0;
        }
        
        /* Tabs Styles */
        .umbral-panel-tabs {
          display: flex;
          flex-direction: column;
        }
        
        .umbral-tab-nav {
          display: flex;
          background: #f9f9f9;
          border-bottom: 1px solid #dcdcde;
          border-radius: 6px 6px 0 0;
          overflow-x: auto;
        }
        
        .umbral-tab {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 14px 24px;
          background: none;
          border: none;
          border-bottom: 3px solid transparent;
          cursor: pointer;
          font-size: 14px;
          font-weight: 500;
          color: #50575e;
          transition: all 0.2s ease;
          white-space: nowrap;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-tab:hover {
          background: #f0f0f1;
          color: #1e1e1e;
        }
        
        .umbral-tab.active {
          background: #ffffff;
          color: #2271b1;
          border-bottom-color: #2271b1;
        }
        
        .umbral-tab-icon {
          font-size: 16px;
        }
        
        .umbral-tab-content {
          background: #ffffff;
          border-radius: 0;
          border: none;
        }
        
        .umbral-panel-description {
          margin: 0 0 16px 0;
          padding-top: 8px;
          padding-inline: 20px;
          font-size: 13px;
          color: #6b7280;
          line-height: 1.5;
          font-style: italic;
          padding-bottom: 8px;
          border-bottom: 1px solid #f3f4f6;
        }
        
        .umbral-panel-fields {
          display: grid;
          gap: 20px;
          padding: 16px 20px;
        }

        .subpanel-fields {
          padding-block: 8x;
          padding-inline: 10px;
        }
        
        /* Accordion Styles */
        .umbral-panel-accordion {
          display: flex;
          flex-direction: column;
          border: none;
          border-radius: 0;
          overflow: hidden;
        }
        
        .umbral-accordion-item {
          border-bottom: 1px solid #f0f0f0;
        }
        
        .umbral-accordion-item:last-child {
          border-bottom: none;
        }
        
        .umbral-accordion-header {
          width: 100%;
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 18px 20px;
          background: #f9f9f9;
          border: none;
          cursor: pointer;
          font-size: 14px;
          font-weight: 500;
          color: #1e1e1e;
          transition: background-color 0.2s ease;
          text-align: left;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-accordion-header:hover {
          background: #f0f0f1;
        }
        
        .umbral-accordion-header.open {
          background: #ffffff;
          border-bottom: 1px solid #f0f0f0;
        }
        
        .umbral-accordion-title {
          display: flex;
          align-items: center;
          gap: 12px;
        }
        
        .umbral-accordion-icon {
          font-size: 16px;
        }
        
        .umbral-accordion-chevron {
          font-size: 12px;
          color: #8c8f94;
          transition: transform 0.2s ease;
        }
        
        .umbral-accordion-chevron.open {
          transform: rotate(180deg);
        }
        
        .umbral-accordion-content {
          overflow: hidden;
        }
        
        .umbral-accordion-body {
          background: #ffffff;
        }
        
        /* Sections Styles */
        .umbral-panel-sections {
          display: flex;
          flex-direction: column;
          gap: 0;
        }
        
        .umbral-sections-list {
          display: flex;
          flex-direction: column;
          overflow: hidden;
          border: none;
          border-radius: 0;
        }
        
        .umbral-section-item {
          border-bottom: 1px solid #f0f0f0;
        }
        
        .umbral-section-item:last-child {
          border-bottom: none;
        }
        
        .umbral-section-editor-view {
          background: #ffffff;
          border: none;
          border-radius: 0;
          overflow: hidden;
        }
        
        .umbral-section-card {
          background: #ffffff;
          border: 1px solid #dcdcde;
          border-radius: 8px;
          overflow: hidden;
        }
        
        .umbral-section-header {
          padding: 20px 24px;
          background: #f9f9f9;
          border-bottom: 1px solid #f0f0f0;
        }
        
        .umbral-section-title {
          display: flex;
          align-items: center;
          gap: 12px;
          margin-bottom: 8px;
        }
        
        .umbral-section-icon {
          font-size: 18px;
        }
        
        .umbral-section-label {
          margin: 0;
          font-size: 16px;
          font-weight: 600;
          color: #1e1e1e;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-section-description {
          margin: 0;
          font-size: 14px;
          color: #50575e;
          line-height: 1.5;
        }
        
        .umbral-section-body {
          padding: 0;
        }
        
        /* Nested Tabs within Sections */
        .umbral-nested-tabs {
          display: flex;
          flex-direction: column;
        }
        
        .umbral-nested-tabs .umbral-tab-nav {
          background: #f6f7f8;
          border-bottom: 1px solid #e5e7eb;
          border-radius: 0;
          margin-bottom: 0;
        }
        
        .umbral-nested-tabs .umbral-tab {
          background: none;
          border: none;
          border-bottom: 2px solid transparent;
          padding: 12px 20px;
          font-size: 13px;
          color: #6b7280;
        }
        
        .umbral-nested-tabs .umbral-tab:hover {
          background: #f0f0f1;
          color: #374151;
        }
        
        .umbral-nested-tabs .umbral-tab.active {
          background: #ffffff;
          color: #1d4ed8;
          border-bottom-color: #1d4ed8;
        }
        
        .umbral-nested-tabs .umbral-tab-content {
          background: #ffffff;
          border-radius: 0;
          border: none;
        }
        
        .umbral-nested-tabs .umbral-tab-icon {
          font-size: 14px;
        }

        .repeater-editor-content > .umbral-field-group {
          padding: 8px 10px;
        }
      `}</style>
    </div>
  );
}