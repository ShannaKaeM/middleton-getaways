import React, { useState, useEffect } from 'react';
import { AnimatePresence } from 'framer-motion';
import { UmbralStyles } from './UmbralStyles';
import { CommandPalette } from './components/CommandPalette';
import { TriggerButton } from './components/TriggerButton';

export function UmbralApp(props = {}) {
  // Only extract REST nonce from props
  const { restNonce = '' } = props;
  
  const [isOpen, setIsOpen] = useState(false);
  const [query, setQuery] = useState('');
  const [userData, setUserData] = useState(null);
  const [siteData, setSiteData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Fetch data from API
  useEffect(() => {
    const fetchData = async () => {
      if (!restNonce) {
        setError('No REST nonce provided');
        setLoading(false);
        return;
      }

      try {
        setLoading(true);
        
        // Fetch user and site data in parallel
        const [userResponse, siteResponse] = await Promise.all([
          fetch('/wp-json/umbral-editor/v1/user', {
            headers: {
              'X-WP-Nonce': restNonce
            }
          }),
          fetch('/wp-json/umbral-editor/v1/site', {
            headers: {
              'X-WP-Nonce': restNonce
            }
          })
        ]);

        if (!userResponse.ok || !siteResponse.ok) {
          throw new Error('Failed to fetch data');
        }

        const userData = await userResponse.json();
        const siteData = await siteResponse.json();

        if (userData.success && siteData.success) {
          setUserData(userData.data);
          setSiteData(siteData.data);
        } else {
          throw new Error('API returned error');
        }
      } catch (err) {
        setError(err.message);
        console.error('Umbral Editor API Error:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [restNonce]);

  // Generate demo commands based on fetched data
  const commands = React.useMemo(() => {
    if (!userData || !siteData) return [];

    return [
      {
        id: 'working',
        title: 'Umbral Editor API Integration Working! 🎉',
        description: 'React fetched data from WordPress REST API successfully',
        icon: '✅'
      },
      {
        id: 'user',
        title: `User Data: ${userData.userName} (${userData.userRole})`,
        description: `ID: ${userData.userId}, Admin: ${userData.isAdmin ? 'Yes' : 'No'}, Email: ${userData.userEmail}`,
        icon: '👤'
      },
      {
        id: 'site',
        title: `Site: ${siteData.siteName}`,
        description: `${siteData.siteDescription} - WordPress ${siteData.wordpressVersion}`,
        icon: '🌐'
      },
      {
        id: 'api',
        title: 'REST API Security Working',
        description: `Authenticated requests using WordPress REST nonce`,
        icon: '🔐'
      },
      {
        id: 'shadow',
        title: 'Shadow DOM Isolation Active',
        description: 'Complete style isolation from WordPress themes',
        icon: '🛡️'
      },
      {
        id: 'clean',
        title: 'Clean Architecture Demo',
        description: `Plugin v${siteData.pluginVersion} - Only REST nonce passed as prop`,
        icon: '🏗️'
      }
    ];
  }, [userData, siteData]);

  const filteredCommands = commands.filter(command =>
    command.title.toLowerCase().includes(query.toLowerCase()) ||
    command.description.toLowerCase().includes(query.toLowerCase())
  );

  // Keyboard shortcuts
  useEffect(() => {
    const handleKeyDown = (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        setIsOpen(!isOpen);
      }
      if (e.key === 'Escape') {
        setIsOpen(false);
      }
    };

    document.addEventListener('keydown', handleKeyDown);
    return () => document.removeEventListener('keydown', handleKeyDown);
  }, [isOpen]);

  // Auto-open on mount to show it's working
  useEffect(() => {
    const timer = setTimeout(() => setIsOpen(true), 1000);
    return () => clearTimeout(timer);
  }, []);

  // Event handlers
  const handleCommandClick = (command) => {
    console.log(`Clicked: ${command.title}`);
    // Add your command handling logic here
  };

  const handleClose = () => {
    setIsOpen(false);
  };

  const handleOpen = () => {
    setIsOpen(true);
  };

  // Show loading or error states
  if (loading) {
    const loadingCommands = [{
      id: 'loading',
      title: 'Loading data from WordPress API...',
      description: 'Fetching user and site information via REST API',
      icon: '⏳'
    }];

    return (
      <>
        <UmbralStyles />
        <AnimatePresence>
          {isOpen && (
            <CommandPalette
              query={query}
              setQuery={setQuery}
              filteredCommands={loadingCommands}
              onCommandClick={handleCommandClick}
              onClose={handleClose}
            />
          )}
        </AnimatePresence>
        {!isOpen && (
          <TriggerButton onClick={handleOpen} />
        )}
      </>
    );
  }

  if (error) {
    const errorCommands = [{
      id: 'error',
      title: 'API Error: ' + error,
      description: 'Check console for details. Make sure you are logged in.',
      icon: '❌'
    }];

    return (
      <>
        <UmbralStyles />
        <AnimatePresence>
          {isOpen && (
            <CommandPalette
              query={query}
              setQuery={setQuery}
              filteredCommands={errorCommands}
              onCommandClick={handleCommandClick}
              onClose={handleClose}
            />
          )}
        </AnimatePresence>
        {!isOpen && (
          <TriggerButton onClick={handleOpen} />
        )}
      </>
    );
  }

  return (
    <>
      <UmbralStyles />
      
      <AnimatePresence>
        {isOpen && (
          <CommandPalette
            query={query}
            setQuery={setQuery}
            filteredCommands={filteredCommands}
            onCommandClick={handleCommandClick}
            onClose={handleClose}
          />
        )}
      </AnimatePresence>

      {/* Floating trigger button */}
      {!isOpen && (
        <TriggerButton onClick={handleOpen} />
      )}
    </>
  );
}