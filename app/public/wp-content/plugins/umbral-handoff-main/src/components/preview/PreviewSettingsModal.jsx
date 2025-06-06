import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';

export function PreviewSettingsModal({ 
  isOpen, 
  onClose, 
  settings, 
  onSettingChange, 
  onReset 
}) {
  if (!isOpen) return null;

  return (
    <AnimatePresence>
      <React.Fragment>
        {/* Backdrop */}
        <motion.div
          style={{
            position: 'fixed',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            backgroundColor: 'rgba(0, 0, 0, 0.5)',
            zIndex: 999999998,
            cursor: 'pointer'
          }}
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.2 }}
          onClick={onClose}
        />
        
        {/* Modal Container */}
        <motion.div
          style={{
            position: 'fixed',
            top: '0px',
            left: '0px',
            right: '0px',
            bottom: '0px',
            zIndex: 999999999,
            display: 'flex',
            alignItems: 'flex-start',
            justifyContent: 'center',
            paddingTop: '15vh',
            paddingLeft: '16px',
            paddingRight: '16px',
            pointerEvents: 'none'
          }}
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
        >
          {/* Settings Modal */}
          <motion.div
            style={{
              width: '100%',
              maxWidth: '480px',
              backgroundColor: '#ffffff',
              border: '1px solid #dcdcde',
              borderRadius: '8px',
              overflow: 'hidden',
              boxShadow: '0 4px 20px rgba(0, 0, 0, 0.15)',
              pointerEvents: 'auto',
              fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'
            }}
            initial={{ opacity: 0, y: -20, scale: 0.95 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: -20, scale: 0.95 }}
            transition={{ 
              type: 'spring', 
              damping: 30, 
              stiffness: 300,
              duration: 0.4
            }}
          >
            {/* Header */}
            <div style={{
              padding: '16px 20px',
              borderBottom: '1px solid #f0f0f0',
              backgroundColor: '#f6f7f8',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between'
            }}>
              <h3 style={{
                margin: 0,
                fontSize: '16px',
                fontWeight: '600',
                color: '#1e1e1e'
              }}>
                ⚙️ Preview Settings
              </h3>
              <button
                onClick={onClose}
                style={{
                  background: 'none',
                  border: 'none',
                  color: '#50575e',
                  cursor: 'pointer',
                  padding: '6px',
                  borderRadius: '4px',
                  fontSize: '14px',
                  transition: 'all 0.2s ease',
                  width: '28px',
                  height: '28px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center'
                }}
              >
                ✕
              </button>
            </div>

            {/* Settings List */}
            <div style={{ padding: '0' }}>
              {/* Header Setting */}
              <SettingItem
                title="Show Header"
                description="Include WordPress theme header in preview"
                enabled={settings.header}
                onClick={() => onSettingChange('header', !settings.header)}
              />

              {/* Footer Setting */}
              <SettingItem
                title="Show Footer"
                description="Include WordPress theme footer in preview"
                enabled={settings.footer}
                onClick={() => onSettingChange('footer', !settings.footer)}
              />

              {/* Reset Action */}
              <div
                onClick={onReset}
                style={{
                  padding: '16px 20px',
                  cursor: 'pointer',
                  backgroundColor: 'transparent',
                  transition: 'background-color 0.2s ease',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center'
                }}
                onMouseEnter={(e) => e.target.style.backgroundColor = '#f6f7f8'}
                onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
              >
                <div style={{
                  fontSize: '14px',
                  fontWeight: '500',
                  color: '#2271b1'
                }}>
                  🔄 Reset to Defaults
                </div>
              </div>
            </div>
          </motion.div>
        </motion.div>
      </React.Fragment>
    </AnimatePresence>
  );
}

// Individual Setting Item Component
function SettingItem({ title, description, enabled, onClick }) {
  return (
    <div
      onClick={onClick}
      style={{
        padding: '16px 20px',
        borderBottom: '1px solid #f0f0f0',
        cursor: 'pointer',
        backgroundColor: 'transparent',
        transition: 'background-color 0.2s ease',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between'
      }}
      onMouseEnter={(e) => e.target.style.backgroundColor = '#f6f7f8'}
      onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
    >
      <div>
        <div style={{
          fontSize: '14px',
          fontWeight: '500',
          color: '#1e1e1e',
          marginBottom: '4px'
        }}>
          {title}
        </div>
        <div style={{
          fontSize: '12px',
          color: '#50575e',
          lineHeight: '1.4'
        }}>
          {description}
        </div>
      </div>
      <div style={{
        width: '20px',
        height: '20px',
        borderRadius: '4px',
        border: '2px solid #dcdcde',
        backgroundColor: enabled ? '#2271b1' : 'transparent',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        color: 'white',
        fontSize: '12px',
        fontWeight: 'bold'
      }}>
        {enabled ? '✓' : ''}
      </div>
    </div>
  );
}