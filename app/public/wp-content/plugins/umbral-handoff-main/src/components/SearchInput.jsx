import React from 'react';

export function SearchInput({ query, setQuery, placeholder = "Search...", embedded = false }) {
  return (
    <div style={{
      padding: embedded ? '0' : 'var(--space-xl) var(--space-2xl) var(--space-lg) var(--space-2xl)',
      borderBottom: embedded ? 'none' : '1px solid var(--border-tertiary)'
    }}>
      <div style={{
        position: 'relative',
        display: 'flex',
        alignItems: 'center'
      }}>
        <div className="umbral-search-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.35-4.35"/>
          </svg>
        </div>
        <input
          type="text"
          placeholder={placeholder}
          value={query}
          onChange={(e) => setQuery(e.target.value)}
          autoFocus
          className="umbral-search-input"
        />
      </div>
      
      <style jsx>{`
        .umbral-search-icon {
          position: absolute;
          left: 16px;
          color: #8c8f94;
          pointer-events: none;
          z-index: 1;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        
        .umbral-search-input {
          width: 100%;
          height: ${embedded ? '32px' : '40px'};
          padding: 0 16px 0 48px;
          background: transparent;
          border: none;
          outline: none;
          color: #1e1e1e;
          font-size: ${embedded ? '16px' : '18px'};
          font-weight: 400;
          line-height: 1.4;
          font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        
        .umbral-search-input::placeholder {
          color: #8c8f94;
        }
      `}</style>
    </div>
  );
}