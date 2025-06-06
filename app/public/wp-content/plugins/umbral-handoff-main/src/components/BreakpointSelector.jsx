import React, { useState, useEffect } from 'react';

export function BreakpointSelector({ 
  currentBreakpoint, 
  onBreakpointChange, 
  restNonce 
}) {
  const [breakpoints, setBreakpoints] = useState({});
  const [loading, setLoading] = useState(true);
  const [displayedActive, setDisplayedActive] = useState(null);

  // Fetch breakpoints from API
  useEffect(() => {
    const fetchBreakpoints = async () => {
      try {
        setLoading(true);
        const response = await fetch('/wp-json/umbral-editor/v1/breakpoints', {
          headers: {
            'X-WP-Nonce': restNonce,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.ok) {
          const result = await response.json();
          setBreakpoints(result.data || {});
          
          // Set default breakpoint if none selected
          if (!currentBreakpoint && result.data) {
            const firstKey = Object.keys(result.data)[0];
            if (firstKey) {
              onBreakpointChange(firstKey);
            }
          }
        } else {
          console.error('Failed to fetch breakpoints');
        }
      } catch (error) {
        console.error('Error fetching breakpoints:', error);
      } finally {
        setLoading(false);
      }
    };

    if (restNonce) {
      fetchBreakpoints();
    }
  }, [restNonce]);

  // Initialize displayedActive when breakpoints are loaded
  useEffect(() => {
    if (!loading && currentBreakpoint && displayedActive === null) {
      setDisplayedActive(currentBreakpoint);
    }
  }, [loading, currentBreakpoint, displayedActive]);

  // Handle delayed highlight: highlight new first, then unhighlight old
  useEffect(() => {
    if (currentBreakpoint === displayedActive) return;
    
    if (currentBreakpoint) {
      // Highlight new breakpoint immediately
      setDisplayedActive(currentBreakpoint);
    } else {
      // If no current breakpoint, wait a bit before unhighlighting
      const timer = setTimeout(() => {
        setDisplayedActive(currentBreakpoint);
      }, 100);
      return () => clearTimeout(timer);
    }
  }, [currentBreakpoint, displayedActive]);

  if (loading) {
    return (
      <div style={{
        display: 'flex',
        alignItems: 'center',
        gap: 'var(--space-xs)',
        padding: 'var(--space-xs)',
        background: 'var(--bg-tertiary)',
        borderRadius: 'var(--radius-sm)',
        border: '1px solid var(--border-tertiary)'
      }}>
        <div style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          padding: 'var(--space-sm)'
        }}>
          <div style={{
            width: '16px',
            height: '16px',
            border: '2px solid var(--border-tertiary)',
            borderTop: '2px solid var(--interactive-primary)',
            borderRadius: '50%',
            animation: 'spin 1s linear infinite'
          }}></div>
        </div>
      </div>
    );
  }

  const sortedBreakpoints = Object.entries(breakpoints).sort(([, a], [, b]) => {
    return (a.min_width || 0) - (b.min_width || 0);
  });

  return (
    <div style={{
      display: 'flex',
      alignItems: 'center',
      gap: 'var(--space-xs)',
      padding: 'var(--space-xs)',
      background: 'var(--bg-tertiary)',
      borderRadius: 'var(--radius-sm)',
      border: '1px solid var(--border-tertiary)'
    }}>
      {sortedBreakpoints.map(([key, breakpoint]) => {
        const isActive = displayedActive === key;
        
        return (
          <button
            key={key}
            type="button"
            onClick={() => onBreakpointChange(key)}
            title={`${breakpoint.label} (${breakpoint.min_width}px${breakpoint.max_width ? ` - ${breakpoint.max_width}px` : '+'})`}
            style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              width: '40px',
              height: '40px',
              padding: 'var(--space-sm)',
              background: isActive ? '#ffffff' : 'transparent',
              border: isActive ? '1px solid var(--border-secondary)' : '1px solid transparent',
              borderRadius: 'var(--radius-sm)',
              cursor: 'pointer',
              fontFamily: 'var(--font-sans)',
              fontSize: 'var(--font-size-lg)',
              lineHeight: '1',
              color: isActive ? 'var(--text-primary)' : 'var(--text-secondary)',
              transition: 'color 0.2s ease-out, background-color 0.3s ease-out, border-color 0.3s ease-out'
            }}
            onMouseEnter={(e) => {
              if (!isActive) {
                e.target.style.color = 'var(--text-primary)';
                e.target.style.backgroundColor = 'var(--bg-secondary)';
              }
            }}
            onMouseLeave={(e) => {
              if (!isActive) {
                e.target.style.color = 'var(--text-secondary)';
                e.target.style.backgroundColor = 'transparent';
              }
            }}
          >
            {breakpoint.icon}
          </button>
        );
      })}
    </div>
  );
}