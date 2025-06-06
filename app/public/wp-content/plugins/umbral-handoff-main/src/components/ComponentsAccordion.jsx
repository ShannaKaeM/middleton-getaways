import React, { useState, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { useDrag, useDrop } from 'react-dnd';
import { ComponentEditor } from './ComponentEditor';

const COMPONENT_TYPE = 'component';

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
      delay: index * 0.15, // Individual delay based on index (150ms apart)
      duration: 0.5,
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

// Draggable Component Card
function DraggableComponent({ component, componentDef, index, moveComponent, onClick, onDuplicate, onDelete }) {
  const [{ isDragging }, drag] = useDrag({
    type: COMPONENT_TYPE,
    item: { id: component.id, index },
    collect: (monitor) => ({
      isDragging: monitor.isDragging(),
    }),
  });

  const [, drop] = useDrop({
    accept: COMPONENT_TYPE,
    hover: (draggedItem) => {
      if (draggedItem.id !== component.id) {
        moveComponent(draggedItem.index, index);
        draggedItem.index = index;
      }
    },
  });

  return (
    <motion.div
      ref={(node) => drag(drop(node))}
      className={`umbral-component-card ${isDragging ? 'dragging' : ''}`}
      layout
      variants={getItemVariants(index)}
      initial="hidden"
      animate="visible"
      exit="exit"
      whileHover={{ 
        y: -2,
        transition: { duration: 0.2 }
      }}
      whileTap={{ scale: 0.98 }}
    >
      <div className="umbral-card-header">
        <div className="umbral-drag-handle" title="Drag to reorder">
          <span>⋮⋮</span>
        </div>
        
        <div 
          className="umbral-card-content"
          onClick={() => onClick(component.id)}
        >
          <div className="umbral-component-icon">
            {componentDef.icon}
          </div>
          
          <div className="umbral-component-details">
            <h4 className="umbral-component-title">
              {componentDef.title || componentDef.label}
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
              onDuplicate(component.id);
            }}
            title="Duplicate component"
          >
            📋
          </button>
          
          <button
            type="button"
            className="umbral-action-btn umbral-delete-btn"
            onClick={(e) => {
              e.stopPropagation();
              onDelete(component.id);
            }}
            title="Delete component"
          >
            🗑️
          </button>
        </div>
      </div>
    </motion.div>
  );
}

export function ComponentsAccordion({
  components,
  availableComponents,
  onUpdateComponent,
  onDeleteComponent,
  onDuplicateComponent,
  onReorderComponents,
  nonce,
  restUrl,
  onVisibilityToggle = null,
  visibilityComponent = null
}) {
  const [editingComponent, setEditingComponent] = useState(null);
  
  // Get component definition
  const getComponentDef = (category, component) => {
    return availableComponents.find(comp => 
      comp.category === category && comp.component === component
    );
  };
  
  // Handle component reordering with React DND
  const moveComponent = useCallback((fromIndex, toIndex) => {
    const newComponents = [...components];
    const [draggedItem] = newComponents.splice(fromIndex, 1);
    newComponents.splice(toIndex, 0, draggedItem);
    onReorderComponents(newComponents);
  }, [components, onReorderComponents]);
  
  // Handle delete with confirmation
  const handleDelete = (componentId) => {
    if (window.confirm('Are you sure you want to delete this component?')) {
      onDeleteComponent(componentId);
    }
  };
  
  // Handle visibility toggle
  const handleVisibilityToggle = (component) => {
    if (!onVisibilityToggle) return;
    
    // If this component is already in visibility mode, turn it off
    if (visibilityComponent && visibilityComponent.id === component.id) {
      onVisibilityToggle(null);
    } else {
      // Turn on visibility mode for this component
      onVisibilityToggle({
        id: component.id,
        category: component.category,
        component: component.component
      });
    }
  };
  
  // Handle back button - clear visibility mode and editing
  const handleBackToList = () => {
    setEditingComponent(null);
    if (onVisibilityToggle) {
      onVisibilityToggle(null);
    }
  };
  
  // If editing a specific component, show the editor
  if (editingComponent) {
    const component = components.find(c => c.id === editingComponent);
    const componentDef = component ? getComponentDef(component.category, component.component) : null;
    
    if (!component || !componentDef) {
      return (
        <div className="umbral-component-error">
          <p>⚠️ Component not found</p>
          <button onClick={() => setEditingComponent(null)}>← Back to Components</button>
        </div>
      );
    }
    
    return (
      <div className="umbral-component-editor-view">
        {/* Editor Header */}
        <div className="umbral-editor-header">
          <button
            type="button"
            className="umbral-back-btn"
            onClick={handleBackToList}
            title="Back to Components"
          >
            ←
          </button>
          
          <div className="umbral-editor-title">
            <div className="umbral-component-icon">{componentDef.icon}</div>
            <div>
              <h3>{componentDef.title || componentDef.label}</h3>
            </div>
          </div>
          
          <div className="umbral-component-actions">
            {onVisibilityToggle && (
              <button
                type="button"
                className={`umbral-action-btn umbral-visibility-btn ${
                  visibilityComponent && visibilityComponent.id === component.id ? 'active' : ''
                }`}
                onClick={() => handleVisibilityToggle(component)}
                title={visibilityComponent && visibilityComponent.id === component.id 
                  ? "Show all components" 
                  : "Preview only this component"
                }
              >
                👁️
              </button>
            )}
          </div>
        </div>
        
        {/* Component Editor */}
        <div className="umbral-editor-content">
          <ComponentEditor
            component={component}
            componentDef={componentDef}
            onUpdateFields={(fields) => onUpdateComponent(component.id, fields)}
            nonce={nonce}
            restUrl={restUrl}
          />
        </div>
        
        <style jsx>{`
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
            padding: 0px;
          }
          
          .umbral-component-icon {
            font-size: var(--panel-icon-size);
            width: var(--panel-icon-width);
            height: var(--panel-icon-height);
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            border-radius: 6px;
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
          
          .umbral-visibility-btn {
            background: #f6f7f8;
            transition: all 0.2s ease;
          }
          
          .umbral-visibility-btn:hover {
            background: #e7f3ff;
            color: #0073aa;
          }
          
          .umbral-visibility-btn.active {
            background: #0073aa;
            color: white;
          }
          
          .umbral-visibility-btn.active:hover {
            background: #005177;
          }
          
          .umbral-component-error {
            padding: 16px 20px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 6px;
            margin-bottom: 16px;
          }
          
          .umbral-component-error p {
            margin: 0;
            color: #856404;
            font-weight: 500;
          }
        `}</style>
      </div>
    );
  }
  
  // Default view: show component list
  return (
    <div className="umbral-components-list">
      <AnimatePresence mode="popLayout">
        {components.map((component, index) => {
          const componentDef = getComponentDef(component.category, component.component);
          
          if (!componentDef) {
            return (
              <div key={component.id} className="umbral-component-error">
                <p>⚠️ Component "{component.component}" in category "{component.category}" not found</p>
              </div>
            );
          }
          
          return (
            <DraggableComponent
              key={component.id}
              component={component}
              componentDef={componentDef}
              index={index}
              moveComponent={moveComponent}
              onClick={setEditingComponent}
              onDuplicate={onDuplicateComponent}
              onDelete={handleDelete}
            />
          );
        })}
      </AnimatePresence>
      
      <style jsx>{`
        /* Component List View */
        .umbral-components-list {
          display: flex;
          flex-direction: column;
          overflow: hidden;
        }
        
        .umbral-component-card {
          background: var(--card-bg);
          border-bottom: 1px solid var(--border-tertiary);
        }
        
        .umbral-component-card:last-child {
          border-bottom: none;
        }
        
        .umbral-component-card.dragging {
          opacity: 0.5;
          transform: rotate(2deg);
          border-radius: var(--radius-md);
          border: 1px solid var(--card-border);
          box-shadow: var(--card-shadow-hover);
        }
        
        .umbral-component-card:hover {
          background: var(--surface-hover);
        }
        
        .umbral-card-header {
          display: flex;
          align-items: center;
          gap: var(--space-md);
          padding: var(--space-md) var(--space-lg);
          background: var(--card-bg);
          user-select: none;
        }
        
        .umbral-card-content {
          display: flex;
          align-items: center;
          gap: var(--space-md);
          flex: 1;
          cursor: pointer;
          padding: var(--space-xs) var(--space-sm);
          border-radius: var(--radius-sm);
          transition: background-color var(--transition-base);
        }
        
        .umbral-card-content:hover {
          background: var(--surface-hover);
        }
        
        /* Component Editor View */
        .umbral-component-editor-view {
          background: var(--panel-bg);
          border: 1px solid var(--panel-border);
          border-radius: var(--radius-md);
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
          font-size: var(--panel-title);
          color: #1e1e1e;
          font-weight: 600;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-editor-title p {
          margin: 0;
          font-size: var(--panel-subtitle);
          color: #50575e;
          line-height: 1.4;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-editor-content {
          padding: var(--space-xl);
        }
        
        .umbral-drag-handle {
          color: var(--text-tertiary);
          cursor: grab;
          font-size: var(--font-size-sm);
          line-height: var(--line-height-tight);
        }
        
        .umbral-drag-handle:active {
          cursor: grabbing;
        }
        
        .umbral-component-details {
          flex: 1;
        }
        
        .umbral-component-title {
          margin: 0 0 4px 0;
          font-size: 0.8rem;
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
        
        .umbral-visibility-btn {
          background: #f6f7f8;
          transition: all 0.2s ease;
        }
        
        .umbral-visibility-btn:hover {
          background: #e7f3ff;
          color: #0073aa;
        }
        
        .umbral-visibility-btn.active {
          background: #0073aa;
          color: white;
        }
        
        .umbral-visibility-btn.active:hover {
          background: #005177;
        }
        
        .umbral-panel-content {
          padding: var(--space-xl);
          background: var(--panel-bg);
        }
        
        .umbral-component-error {
          padding: var(--space-lg) var(--space-xl);
          background: var(--status-warning-bg);
          border-left: 4px solid var(--status-warning);
          border-radius: var(--radius-md);
          margin-bottom: var(--space-lg);
        }
        
        .umbral-component-error p {
          margin: 0;
          color: var(--status-warning);
          font-weight: var(--font-weight-medium);
        }
      `}</style>
    </div>
  );
}