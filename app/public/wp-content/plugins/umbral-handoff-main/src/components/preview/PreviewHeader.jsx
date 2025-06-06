import React from 'react';
import { BreakpointSelector } from '../BreakpointSelector';

export function PreviewHeader({ 
  isRefreshing, 
  onRefresh, 
  onOpenSettings, 
  isRefreshDisabled = false,
  currentBreakpoint,
  onBreakpointChange,
  restNonce
}) {
  return (
    <div style={{
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'space-between',
      padding: 'var(--space-md)',
      background: 'var(--bg-primary)',
      borderBottom: '1px solid var(--border-primary)',
      gap: 'var(--space-md)'
    }}>
      <div style={{
        display: 'flex',
        alignItems: 'center',
        gap: 'var(--space-sm)',
        fontWeight: 'var(--font-weight-semibold)',
        color: 'var(--text-primary)',
        whiteSpace: 'nowrap'
      }}>
        Live Preview
        {isRefreshing && (
          <span style={{
            display: 'flex',
            alignItems: 'center',
            gap: 'var(--space-xs)',
            fontSize: 'var(--font-size-sm)',
            color: 'var(--text-secondary)'
          }}>
            <span style={{
              width: '12px',
              height: '12px',
              border: '2px solid var(--border-tertiary)',
              borderTop: '2px solid var(--interactive-primary)',
              borderRadius: '50%',
              animation: 'spin 1s linear infinite'
            }}></span>
            Refreshing...
          </span>
        )}
      </div>
      
      <div style={{
        flex: '1',
        display: 'flex',
        justifyContent: 'center'
      }}>
        <BreakpointSelector
          currentBreakpoint={currentBreakpoint}
          onBreakpointChange={onBreakpointChange}
          restNonce={restNonce}
        />
      </div>
      
      <div style={{
        display: 'flex',
        alignItems: 'center',
        gap: 'var(--space-sm)'
      }}>
        <button
          onClick={onOpenSettings}
          title="Preview Settings"
          type="button"
          style={{
            padding: 'var(--space-sm)',
            background: 'var(--bg-secondary)',
            border: '1px solid var(--border-primary)',
            borderRadius: 'var(--radius-xs)',
            cursor: 'pointer',
            transition: 'all var(--transition-base)',
            fontSize: 'var(--font-size-sm)',
            fontFamily: 'var(--font-sans)'
          }}
          onMouseEnter={(e) => {
            e.target.style.background = 'var(--bg-tertiary)';
            e.target.style.borderColor = 'var(--border-secondary)';
          }}
          onMouseLeave={(e) => {
            e.target.style.background = 'var(--bg-secondary)';
            e.target.style.borderColor = 'var(--border-primary)';
          }}
        >
          ⚙️
        </button>
        <button
          onClick={onRefresh}
          title="Refresh Preview"
          disabled={isRefreshDisabled}
          style={{
            padding: 'var(--space-sm)',
            background: 'var(--bg-secondary)',
            border: '1px solid var(--border-primary)',
            borderRadius: 'var(--radius-xs)',
            cursor: isRefreshDisabled ? 'not-allowed' : 'pointer',
            transition: 'all var(--transition-base)',
            fontSize: 'var(--font-size-sm)',
            fontFamily: 'var(--font-sans)',
            opacity: isRefreshDisabled ? '0.6' : '1'
          }}
          onMouseEnter={(e) => {
            if (!isRefreshDisabled) {
              e.target.style.background = 'var(--bg-tertiary)';
              e.target.style.borderColor = 'var(--border-secondary)';
            }
          }}
          onMouseLeave={(e) => {
            if (!isRefreshDisabled) {
              e.target.style.background = 'var(--bg-secondary)';
              e.target.style.borderColor = 'var(--border-primary)';
            }
          }}
        >
          🔄 Refresh
        </button>
      </div>
    </div>
  );
}