import React from 'react';

export function CommandItem({ command, index, onClick }) {
  return (
    <div
      style={{
        padding: 'var(--space-lg) var(--space-2xl)',
        display: 'flex',
        alignItems: 'center',
        gap: 'var(--space-lg)',
        cursor: 'pointer',
        borderBottom: '1px solid var(--border-tertiary)',
        backgroundColor: 'transparent',
        position: 'relative'
      }}
      onClick={() => onClick?.(command)}
    >
      <div style={{
        fontSize: 'var(--icon-lg)',
        lineHeight: 'var(--line-height-tight)',
        flexShrink: 0
      }}>
        {command.icon}
      </div>
      <div style={{ 
        flex: 1, 
        minWidth: 0,
        display: 'flex',
        flexDirection: 'column',
        gap: 'var(--space-xs)'
      }}>
        <div style={{
          fontSize: 'var(--font-size-lg)',
          fontWeight: 'var(--font-weight-medium)',
          color: 'var(--text-primary)',
          lineHeight: 'var(--line-height-tight)',
          wordBreak: 'break-word',
          fontFamily: 'var(--font-sans)'
        }}>
          {command.title}
        </div>
        <div style={{
          fontSize: 'var(--font-size-base)',
          color: 'var(--text-secondary)',
          lineHeight: 'var(--line-height-normal)',
          wordBreak: 'break-word',
          fontFamily: 'var(--font-sans)'
        }}>
          {command.description}
        </div>
      </div>
    </div>
  );
}