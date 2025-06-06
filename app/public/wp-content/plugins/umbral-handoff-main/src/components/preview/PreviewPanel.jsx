import React, { useState, useCallback, useEffect } from 'react';
import { PreviewHeader } from './PreviewHeader';
import { PreviewFrame } from './PreviewFrame';
import { PreviewSettingsModal } from './PreviewSettingsModal';

export function PreviewPanel({ 
  baseUrl, 
  refreshKey, 
  isRefreshing, 
  onRefresh,
  restNonce,
  visibilityComponent = null
}) {
  const [showSettings, setShowSettings] = useState(false);
  const [settings, setSettings] = useState({
    header: true,
    footer: true
  });
  const [currentBreakpoint, setCurrentBreakpoint] = useState(null);
  const [breakpoints, setBreakpoints] = useState({});

  // Fetch breakpoints on mount
  useEffect(() => {
    const fetchBreakpoints = async () => {
      try {
        const response = await fetch('/wp-json/umbral-editor/v1/breakpoints', {
          headers: {
            'X-WP-Nonce': restNonce,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.ok) {
          const result = await response.json();
          setBreakpoints(result.data || {});
          
          // Set default breakpoint (largest one for desktop view)
          if (result.data && !currentBreakpoint) {
            const sortedKeys = Object.keys(result.data).sort((a, b) => {
              return (result.data[b].min_width || 0) - (result.data[a].min_width || 0);
            });
            if (sortedKeys.length > 0) {
              setCurrentBreakpoint(sortedKeys[0]);
            }
          }
        }
      } catch (error) {
        console.error('Error fetching breakpoints:', error);
      }
    };

    if (restNonce) {
      fetchBreakpoints();
    }
  }, [restNonce, currentBreakpoint]);

  // Handle breakpoint change
  const handleBreakpointChange = useCallback((breakpointKey) => {
    setCurrentBreakpoint(breakpointKey);
  }, []);

  // Get current breakpoint width
  const getCurrentBreakpointWidth = useCallback(() => {
    if (!currentBreakpoint || !breakpoints[currentBreakpoint]) {
      return null;
    }
    
    const bp = breakpoints[currentBreakpoint];
    // Use max_width if available, otherwise min_width + reasonable margin
    return bp.max_width || (bp.min_width + 200);
  }, [currentBreakpoint, breakpoints]);

  // Generate preview URL with current settings
  const getPreviewUrl = useCallback(() => {
    const url = new URL(baseUrl);
    
    if (!settings.header) {
      url.searchParams.set('header', 'false');
    } else {
      url.searchParams.delete('header');
    }
    
    if (!settings.footer) {
      url.searchParams.set('footer', 'false');
    } else {
      url.searchParams.delete('footer');
    }
    
    // Add component parameter for single component visibility
    if (visibilityComponent) {
      const componentParam = `${visibilityComponent.category}-${visibilityComponent.component}`;
      url.searchParams.set('component', componentParam);
    } else {
      url.searchParams.delete('component');
    }
    
    return url.toString();
  }, [baseUrl, settings, visibilityComponent]);

  // Handle setting changes
  const handleSettingChange = useCallback((key, value) => {
    setSettings(prev => ({
      ...prev,
      [key]: value
    }));
    // Trigger refresh after setting change
    setTimeout(onRefresh, 100);
  }, [onRefresh]);

  // Reset settings to defaults
  const handleReset = useCallback(() => {
    setSettings({ header: true, footer: true });
    setTimeout(onRefresh, 100);
  }, [onRefresh]);

  const currentBreakpointData = currentBreakpoint ? breakpoints[currentBreakpoint] : null;
  const previewWidth = getCurrentBreakpointWidth();

  return (
    <div className="umbral-editor-preview">
      <PreviewHeader
        isRefreshing={isRefreshing}
        onRefresh={onRefresh}
        onOpenSettings={() => setShowSettings(true)}
        isRefreshDisabled={isRefreshing}
        currentBreakpoint={currentBreakpoint}
        onBreakpointChange={handleBreakpointChange}
        restNonce={restNonce}
      />
      
      <PreviewFrame
        src={getPreviewUrl()}
        refreshKey={refreshKey}
        width={previewWidth}
        breakpointLabel={currentBreakpointData?.label}
        isRefreshing={isRefreshing}
      />
      
      <PreviewSettingsModal
        isOpen={showSettings}
        onClose={() => setShowSettings(false)}
        settings={settings}
        onSettingChange={handleSettingChange}
        onReset={handleReset}
      />
    </div>
  );
}