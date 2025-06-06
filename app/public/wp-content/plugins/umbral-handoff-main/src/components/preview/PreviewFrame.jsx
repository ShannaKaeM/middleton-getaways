import React, { useRef, useEffect, useState } from 'react';

export function PreviewFrame({ src, refreshKey, width, breakpointLabel, isRefreshing }) {
  const iframeRef = useRef(null);
  const containerRef = useRef(null);
  const [scale, setScale] = useState(1);
  const [containerHeight, setContainerHeight] = useState(600);

  // Trigger navigation-based refresh using view transitions
  useEffect(() => {
    if (isRefreshing && iframeRef.current) {
      const iframe = iframeRef.current;
      
      // Wait for iframe to be ready, then trigger navigation
      const triggerViewTransition = () => {
        try {
          const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
          
          // Add view transition CSS to the iframe if not present
          let viewTransitionStyle = iframeDoc.getElementById('umbral-view-transitions');
          if (!viewTransitionStyle) {
            viewTransitionStyle = iframeDoc.createElement('style');
            viewTransitionStyle.id = 'umbral-view-transitions';
            viewTransitionStyle.innerHTML = `
              @view-transition {
                navigation: auto;
              }
              
              ::view-transition-old(root),
              ::view-transition-new(root) {
                animation-duration: 0.4s;
                animation-timing-function: ease-in-out;
              }
              
              ::view-transition-old(root) {
                animation-name: fade-out;
              }
              
              ::view-transition-new(root) {
                animation-name: fade-in;
              }
              
              @keyframes fade-out {
                to { opacity: 0; }
              }
              
              @keyframes fade-in {
                from { opacity: 0; }
              }
            `;
            iframeDoc.head.appendChild(viewTransitionStyle);
          }
          
          // Create hidden refresh link
          let refreshLink = iframeDoc.getElementById('umbral-refresh-link');
          if (!refreshLink) {
            refreshLink = iframeDoc.createElement('a');
            refreshLink.id = 'umbral-refresh-link';
            refreshLink.href = iframe.src;
            refreshLink.style.display = 'none';
            iframeDoc.body.appendChild(refreshLink);
          }
          
          // Trigger view transition navigation
          if (iframeDoc.startViewTransition) {
            iframeDoc.startViewTransition(() => {
              refreshLink.click();
            });
          } else {
            // Fallback for browsers without view transitions
            refreshLink.click();
          }
        } catch (error) {
          console.log('View transition failed, falling back to src update:', error);
          // Fallback to traditional refresh
          iframe.src = iframe.src;
        }
      };
      
      // Wait a bit for iframe to be ready
      setTimeout(triggerViewTransition, 100);
    }
  }, [isRefreshing]);

  // Calculate scale when breakpoint width or container size changes
  useEffect(() => {
    if (!width || !containerRef.current) {
      setScale(1);
      return;
    }

    const updateScale = () => {
      const container = containerRef.current;
      if (!container) return;

      const containerRect = container.getBoundingClientRect();
      const availableWidth = containerRect.width - 32; // Account for padding (16px * 2)
      const availableHeight = containerRect.height; // Use full height
      
      setContainerHeight(availableHeight);

      // Always calculate scale based on available width vs breakpoint width
      const calculatedScale = Math.min(availableWidth / width, 1);
      setScale(calculatedScale);
    };

    // Initial calculation
    updateScale();

    // Update on resize
    const resizeObserver = new ResizeObserver(updateScale);
    resizeObserver.observe(containerRef.current);

    return () => {
      resizeObserver.disconnect();
    };
  }, [width]);

  return (
    <div style={{
      flex: '1',
      display: 'flex',
      flexDirection: 'column',
      overflow: 'hidden'
    }}>
      {/* Always visible size indicator to prevent layout shifts */}
      <div style={{
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 'var(--space-sm)',
        padding: 'var(--space-sm)',
        background: 'var(--bg-tertiary)',
        borderBottom: '1px solid var(--border-tertiary)',
        fontSize: 'var(--font-size-sm)',
        fontWeight: 'var(--font-weight-medium)',
        color: 'var(--text-secondary)',
        minHeight: '44px' // Fixed height to prevent shifts
      }}>
        <span style={{
          color: 'var(--text-primary)'
        }}>
          {width ? breakpointLabel : 'Full Width'}
        </span>
        {width && (
          <span style={{
            fontFamily: 'var(--font-mono)',
            background: 'var(--bg-secondary)',
            padding: 'var(--space-xs) var(--space-sm)',
            borderRadius: 'var(--radius-xs)',
            border: '1px solid var(--border-primary)'
          }}>
            {width}px
          </span>
        )}
      </div>
      
      <div 
        ref={containerRef}
        style={{
          flex: '1',
          display: 'flex',
          overflow: 'auto',
          padding: 'var(--space-md)',
          background: 'var(--bg-secondary)'
        }}>
        <div
          style={{
            width: width ? `${width * scale}px` : '100%',
            height: width ? `${containerHeight}px` : '100%',
            maxWidth: '100%',
            margin: '0 auto',
            border: width ? '1px solid var(--border-secondary)' : 'none',
            borderRadius: width ? 'var(--radius-sm)' : 0,
            overflow: 'hidden',
            background: 'white',
            position: 'relative',
            transition: 'width 0.4s ease-in-out, height 0.4s ease-in-out, border 0.2s ease-in-out, border-radius 0.2s ease-in-out'
          }}
        >
          <iframe
            ref={iframeRef}
            src={src}
            title="Page Preview"
            style={{
              width: width ? `${width}px` : '100%',
              height: width ? `${containerHeight / scale}px` : '100%',
              border: 'none',
              background: 'white',
              transform: `scale(${scale})`,
              transformOrigin: 'top left',
              transition: 'transform 0.4s ease-in-out'
            }}
          />
        </div>
      </div>
    </div>
  );
}