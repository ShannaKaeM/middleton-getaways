import React from 'react';
import { motion } from 'framer-motion';
import { UmbralStyles } from '../UmbralStyles';
import { SearchInput } from './SearchInput';
import { CommandItem } from './CommandItem';

export function CommandPalette({ 
  availableComponents = [],
  onComponentSelect,
  onClose,
  title = "Add Component"
}) {
  const [currentView, setCurrentView] = React.useState('categories'); // 'categories' or 'components'
  const [selectedCategory, setSelectedCategory] = React.useState(null);
  const [query, setQuery] = React.useState('');
  
  // Group components by category
  const componentsByCategory = React.useMemo(() => {
    const groups = {};
    availableComponents.forEach(component => {
      if (!groups[component.category]) {
        groups[component.category] = {
          name: component.category,
          title: component.categoryTitle || component.category.charAt(0).toUpperCase() + component.category.slice(1),
          icon: component.categoryIcon || '📦',
          components: []
        };
      }
      groups[component.category].components.push(component);
    });
    return groups;
  }, [availableComponents]);
  
  // Get categories for the first view
  const categories = Object.values(componentsByCategory);
  
  // Get components for selected category
  const categoryComponents = selectedCategory ? componentsByCategory[selectedCategory]?.components || [] : [];
  
  // Filter based on search query
  const filteredCategories = query ? 
    categories.filter(cat => cat.title.toLowerCase().includes(query.toLowerCase())) :
    categories;
    
  const filteredComponents = query ?
    categoryComponents.filter(comp => 
      comp.title.toLowerCase().includes(query.toLowerCase()) ||
      comp.description.toLowerCase().includes(query.toLowerCase())
    ) :
    categoryComponents;
  
  const handleCategorySelect = (categoryName) => {
    setSelectedCategory(categoryName);
    setCurrentView('components');
    setQuery(''); // Clear search when switching views
  };
  
  const handleBack = () => {
    setCurrentView('categories');
    setSelectedCategory(null);
    setQuery('');
  };
  
  const handleComponentClick = (component) => {
    onComponentSelect(component);
    onClose();
  };
  return (
    <>
      <UmbralStyles />
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
      
      {/* Command Palette Container */}
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
        {/* Command Palette */}
        <motion.div
          style={{
            width: '100%',
            maxWidth: '640px',
            backgroundColor: 'var(--bg-primary)',
            border: '1px solid var(--border-primary)',
            borderRadius: 'var(--radius-md)',
            overflow: 'hidden',
            boxShadow: 'var(--card-shadow-hover)',
            pointerEvents: 'auto',
            fontFamily: 'var(--font-sans)'
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
          {/* Header with back button for components view */}
          <div style={{
            padding: 'var(--space-md) var(--space-lg)',
            borderBottom: '1px solid var(--border-tertiary)',
            backgroundColor: 'var(--bg-secondary)',
            display: 'flex',
            alignItems: 'center',
            gap: 'var(--space-md)'
          }}>
            {currentView === 'components' && (
              <button
                onClick={handleBack}
                style={{
                  background: 'none',
                  border: 'none',
                  color: 'var(--text-secondary)',
                  cursor: 'pointer',
                  padding: 'var(--space-xs)',
                  borderRadius: 'var(--radius-sm)',
                  display: 'flex',
                  alignItems: 'center',
                  fontSize: 'var(--font-size-md)',
                  fontFamily: 'var(--font-sans)',
                  flexShrink: 0
                }}
              >
                ← Back
              </button>
            )}
            <div style={{ flex: 1 }}>
              <SearchInput 
                query={query}
                setQuery={setQuery}
                placeholder={currentView === 'categories' ? 'Search categories...' : 'Search components...'}
                embedded={true}
              />
            </div>
          </div>

          {/* Results List */}
          <div style={{
            maxHeight: '400px',
            overflowY: 'auto',
            overflowX: 'hidden'
          }}>
            {currentView === 'categories' ? (
              // Categories view
              filteredCategories.length > 0 ? (
                filteredCategories.map((category, index) => (
                  <CategoryItem
                    key={category.name}
                    category={category}
                    index={index}
                    onClick={() => handleCategorySelect(category.name)}
                  />
                ))
              ) : (
                <div style={{
                  padding: 'var(--space-3xl) var(--space-2xl)',
                  textAlign: 'center',
                  color: 'var(--text-secondary)',
                  fontSize: 'var(--font-size-md)'
                }}>
                  No categories found for "{query}"
                </div>
              )
            ) : (
              // Components view
              filteredComponents.length > 0 ? (
                filteredComponents.map((component, index) => (
                  <CommandItem
                    key={component.id}
                    command={component}
                    index={index}
                    onClick={handleComponentClick}
                  />
                ))
              ) : (
                <div style={{
                  padding: 'var(--space-3xl) var(--space-2xl)',
                  textAlign: 'center',
                  color: 'var(--text-secondary)',
                  fontSize: 'var(--font-size-md)'
                }}>
                  No components found for "{query}"
                </div>
              )
            )}
          </div>

          {/* Footer */}
          <div style={{
            padding: 'var(--space-md) var(--space-lg)',
            borderTop: '1px solid var(--border-tertiary)',
            backgroundColor: 'var(--bg-secondary)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between'
          }}>
            <div style={{
              fontSize: 'var(--font-size-sm)',
              color: 'var(--text-secondary)',
              fontWeight: 'var(--font-weight-medium)'
            }}>
              🧩 {currentView === 'categories' ? 'Select Category' : selectedCategory && componentsByCategory[selectedCategory]?.title}
            </div>
            <div style={{
              display: 'flex',
              alignItems: 'center',
              gap: 'var(--space-sm)',
              fontSize: 'var(--font-size-xs)',
              color: 'var(--text-tertiary)'
            }}>
              <span>Press</span>
              <kbd style={{
                backgroundColor: 'var(--bg-primary)',
                color: 'var(--text-secondary)',
                padding: '2px var(--space-sm)',
                borderRadius: 'var(--radius-sm)',
                fontSize: 'var(--font-size-xs)',
                fontWeight: 'var(--font-weight-semibold)',
                border: '1px solid var(--border-primary)',
                fontFamily: 'var(--font-mono)'
              }}>
                Esc
              </kbd>
              <span>to close</span>
            </div>
          </div>
        </motion.div>
      </motion.div>
    </>
  );
}

// Category Item Component
function CategoryItem({ category, index, onClick }) {
  return (
    <div
      style={{
        padding: 'var(--space-lg) var(--space-xl)',
        borderBottom: '1px solid var(--border-tertiary)',
        cursor: 'pointer',
        backgroundColor: 'transparent',
        display: 'flex',
        alignItems: 'center',
        gap: 'var(--space-lg)'
      }}
      onClick={onClick}
    >
      {/* Category Icon */}
      <div style={{
        width: 'var(--icon-2xl)',
        height: 'var(--icon-2xl)',
        backgroundColor: 'var(--bg-primary)',
        border: '1px solid var(--border-primary)',
        borderRadius: 'var(--radius-lg)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: 'var(--font-size-xl)',
        flexShrink: 0
      }}>
        {category.icon}
      </div>
      
      {/* Category Info */}
      <div style={{ flex: 1, minWidth: 0 }}>
        <div style={{
          fontSize: 'var(--font-size-md)',
          fontWeight: 'var(--font-weight-medium)',
          color: 'var(--text-primary)',
          marginBottom: '2px'
        }}>
          {category.title}
        </div>
        <div style={{
          fontSize: 'var(--font-size-sm)',
          color: 'var(--text-secondary)',
          lineHeight: 'var(--line-height-normal)'
        }}>
          {category.components.length} component{category.components.length !== 1 ? 's' : ''}
        </div>
      </div>
      
      {/* Arrow */}
      <div style={{
        color: 'var(--text-tertiary)',
        fontSize: 'var(--font-size-sm)',
        opacity: 0.6
      }}>
        →
      </div>
    </div>
  );
}