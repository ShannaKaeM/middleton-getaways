import React, { useState, useEffect, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

export function BreakpointsManager({ isOpen, onClose, restNonce }) {
  const [breakpoints, setBreakpoints] = useState({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [editingKey, setEditingKey] = useState(null);
  const [editForm, setEditForm] = useState({});
  const [showAddForm, setShowAddForm] = useState(false);
  const [addForm, setAddForm] = useState({
    key: '',
    label: '',
    min_width: 0,
    max_width: null,
    icon: '📱',
    description: ''
  });

  // Fetch breakpoints from API
  const fetchBreakpoints = useCallback(async () => {
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
      } else {
        console.error('Failed to fetch breakpoints');
      }
    } catch (error) {
      console.error('Error fetching breakpoints:', error);
    } finally {
      setLoading(false);
    }
  }, [restNonce]);

  useEffect(() => {
    if (isOpen) {
      fetchBreakpoints();
    }
  }, [isOpen, fetchBreakpoints]);

  // Save single breakpoint
  const saveBreakpoint = async (key, data) => {
    try {
      setSaving(true);
      const response = await fetch(`/wp-json/umbral-editor/v1/breakpoints/${key}`, {
        method: 'PUT',
        headers: {
          'X-WP-Nonce': restNonce,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      });

      if (response.ok) {
        await fetchBreakpoints();
        setEditingKey(null);
        setEditForm({});
      } else {
        console.error('Failed to save breakpoint');
      }
    } catch (error) {
      console.error('Error saving breakpoint:', error);
    } finally {
      setSaving(false);
    }
  };

  // Add new breakpoint
  const addBreakpoint = async () => {
    try {
      setSaving(true);
      const response = await fetch('/wp-json/umbral-editor/v1/breakpoints', {
        method: 'POST',
        headers: {
          'X-WP-Nonce': restNonce,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(addForm)
      });

      if (response.ok) {
        await fetchBreakpoints();
        setShowAddForm(false);
        setAddForm({
          key: '',
          label: '',
          min_width: 0,
          max_width: null,
          icon: '📱',
          description: ''
        });
      } else {
        console.error('Failed to add breakpoint');
      }
    } catch (error) {
      console.error('Error adding breakpoint:', error);
    } finally {
      setSaving(false);
    }
  };

  // Delete breakpoint
  const deleteBreakpoint = async (key) => {
    if (!confirm(`Are you sure you want to delete the "${breakpoints[key]?.label}" breakpoint?`)) {
      return;
    }

    try {
      setSaving(true);
      const response = await fetch(`/wp-json/umbral-editor/v1/breakpoints/${key}`, {
        method: 'DELETE',
        headers: {
          'X-WP-Nonce': restNonce,
          'Content-Type': 'application/json'
        }
      });

      if (response.ok) {
        await fetchBreakpoints();
      } else {
        console.error('Failed to delete breakpoint');
      }
    } catch (error) {
      console.error('Error deleting breakpoint:', error);
    } finally {
      setSaving(false);
    }
  };

  // Reset to defaults
  const resetToDefaults = async () => {
    if (!confirm('Are you sure you want to reset all breakpoints to defaults? This will overwrite any custom breakpoints.')) {
      return;
    }

    try {
      setSaving(true);
      const response = await fetch('/wp-json/umbral-editor/v1/breakpoints/reset', {
        method: 'POST',
        headers: {
          'X-WP-Nonce': restNonce,
          'Content-Type': 'application/json'
        }
      });

      if (response.ok) {
        await fetchBreakpoints();
      } else {
        console.error('Failed to reset breakpoints');
      }
    } catch (error) {
      console.error('Error resetting breakpoints:', error);
    } finally {
      setSaving(false);
    }
  };

  // Handle edit form changes
  const handleEditChange = (field, value) => {
    setEditForm(prev => ({
      ...prev,
      [field]: field === 'min_width' || field === 'max_width' ? 
        (value === '' ? null : parseInt(value)) : value
    }));
  };

  // Handle add form changes
  const handleAddChange = (field, value) => {
    setAddForm(prev => ({
      ...prev,
      [field]: field === 'min_width' || field === 'max_width' ? 
        (value === '' ? null : parseInt(value)) : value
    }));
  };

  // Start editing
  const startEdit = (key) => {
    setEditingKey(key);
    setEditForm(breakpoints[key]);
  };

  // Cancel editing
  const cancelEdit = () => {
    setEditingKey(null);
    setEditForm({});
  };

  // Icon options
  const iconOptions = ['📱', '📋', '💻', '🖥️', '📐', '⚡', '🎯'];

  if (!isOpen) return null;

  return (
    <AnimatePresence>
      <motion.div
        initial={{ opacity: 0 }}
        animate={{ opacity: 1 }}
        exit={{ opacity: 0 }}
        className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        onClick={onClose}
      >
        <motion.div
          initial={{ scale: 0.95, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          exit={{ scale: 0.95, opacity: 0 }}
          className="bg-white rounded-lg shadow-xl max-w-4xl max-h-[90vh] overflow-hidden flex flex-col"
          onClick={(e) => e.stopPropagation()}
        >
          {/* Header */}
          <div className="flex items-center justify-between p-6 border-b">
            <div>
              <h2 className="text-lg font-semibold text-gray-900">Breakpoints Manager</h2>
              <p className="text-sm text-gray-500 mt-1">Manage responsive breakpoints for your design system</p>
            </div>
            <button
              onClick={onClose}
              className="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          {/* Content */}
          <div className="flex-1 overflow-y-auto p-6">
            {loading ? (
              <div className="flex items-center justify-center h-32">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              </div>
            ) : (
              <div className="space-y-4">
                {/* Action buttons */}
                <div className="flex gap-3 mb-6">
                  <button
                    onClick={() => setShowAddForm(true)}
                    className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                    disabled={saving}
                  >
                    Add Breakpoint
                  </button>
                  <button
                    onClick={resetToDefaults}
                    className="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors"
                    disabled={saving}
                  >
                    Reset to Defaults
                  </button>
                </div>

                {/* Add Form */}
                {showAddForm && (
                  <motion.div
                    initial={{ opacity: 0, height: 0 }}
                    animate={{ opacity: 1, height: 'auto' }}
                    exit={{ opacity: 0, height: 0 }}
                    className="bg-gray-50 rounded-lg p-4 border space-y-4"
                  >
                    <h3 className="font-medium text-gray-900">Add New Breakpoint</h3>
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Key</label>
                        <input
                          type="text"
                          value={addForm.key}
                          onChange={(e) => handleAddChange('key', e.target.value)}
                          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="um_custom"
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Label</label>
                        <input
                          type="text"
                          value={addForm.label}
                          onChange={(e) => handleAddChange('label', e.target.value)}
                          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Custom Size"
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Min Width (px)</label>
                        <input
                          type="number"
                          value={addForm.min_width || ''}
                          onChange={(e) => handleAddChange('min_width', e.target.value)}
                          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          min="0"
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Max Width (px)</label>
                        <input
                          type="number"
                          value={addForm.max_width || ''}
                          onChange={(e) => handleAddChange('max_width', e.target.value)}
                          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Leave empty for no max"
                        />
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                        <select
                          value={addForm.icon}
                          onChange={(e) => handleAddChange('icon', e.target.value)}
                          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                          {iconOptions.map(icon => (
                            <option key={icon} value={icon}>{icon}</option>
                          ))}
                        </select>
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <input
                          type="text"
                          value={addForm.description}
                          onChange={(e) => handleAddChange('description', e.target.value)}
                          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Custom devices"
                        />
                      </div>
                    </div>
                    <div className="flex gap-2">
                      <button
                        onClick={addBreakpoint}
                        className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                        disabled={saving || !addForm.key || !addForm.label}
                      >
                        {saving ? 'Adding...' : 'Add Breakpoint'}
                      </button>
                      <button
                        onClick={() => setShowAddForm(false)}
                        className="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                      >
                        Cancel
                      </button>
                    </div>
                  </motion.div>
                )}

                {/* Breakpoints List */}
                <div className="space-y-3">
                  {Object.entries(breakpoints).map(([key, bp]) => (
                    <motion.div
                      key={key}
                      initial={{ opacity: 0, y: 10 }}
                      animate={{ opacity: 1, y: 0 }}
                      className="bg-white border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors"
                    >
                      {editingKey === key ? (
                        // Edit form
                        <div className="space-y-4">
                          <div className="grid grid-cols-2 gap-4">
                            <div>
                              <label className="block text-sm font-medium text-gray-700 mb-1">Label</label>
                              <input
                                type="text"
                                value={editForm.label || ''}
                                onChange={(e) => handleEditChange('label', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              />
                            </div>
                            <div>
                              <label className="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                              <select
                                value={editForm.icon || '📱'}
                                onChange={(e) => handleEditChange('icon', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              >
                                {iconOptions.map(icon => (
                                  <option key={icon} value={icon}>{icon}</option>
                                ))}
                              </select>
                            </div>
                            <div>
                              <label className="block text-sm font-medium text-gray-700 mb-1">Min Width (px)</label>
                              <input
                                type="number"
                                value={editForm.min_width || ''}
                                onChange={(e) => handleEditChange('min_width', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                min="0"
                              />
                            </div>
                            <div>
                              <label className="block text-sm font-medium text-gray-700 mb-1">Max Width (px)</label>
                              <input
                                type="number"
                                value={editForm.max_width || ''}
                                onChange={(e) => handleEditChange('max_width', e.target.value)}
                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Leave empty for no max"
                              />
                            </div>
                          </div>
                          <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <input
                              type="text"
                              value={editForm.description || ''}
                              onChange={(e) => handleEditChange('description', e.target.value)}
                              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                          </div>
                          <div className="flex gap-2">
                            <button
                              onClick={() => saveBreakpoint(key, editForm)}
                              className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                              disabled={saving}
                            >
                              {saving ? 'Saving...' : 'Save'}
                            </button>
                            <button
                              onClick={cancelEdit}
                              className="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                            >
                              Cancel
                            </button>
                          </div>
                        </div>
                      ) : (
                        // Display mode
                        <div className="flex items-center justify-between">
                          <div className="flex items-center gap-4">
                            <span className="text-2xl">{bp.icon}</span>
                            <div>
                              <div className="flex items-center gap-2">
                                <h3 className="font-medium text-gray-900">{bp.label}</h3>
                                <code className="text-sm bg-gray-100 px-2 py-1 rounded text-gray-600">{key}</code>
                              </div>
                              <p className="text-sm text-gray-500">{bp.description}</p>
                              <p className="text-xs text-gray-400 mt-1">
                                {bp.min_width}px{bp.max_width ? ` - ${bp.max_width}px` : '+'}
                              </p>
                            </div>
                          </div>
                          <div className="flex items-center gap-2">
                            <button
                              onClick={() => startEdit(key)}
                              className="p-2 text-gray-500 hover:text-blue-600 transition-colors"
                              title="Edit breakpoint"
                            >
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                              </svg>
                            </button>
                            <button
                              onClick={() => deleteBreakpoint(key)}
                              className="p-2 text-gray-500 hover:text-red-600 transition-colors"
                              title="Delete breakpoint"
                            >
                              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                              </svg>
                            </button>
                          </div>
                        </div>
                      )}
                    </motion.div>
                  ))}
                </div>
              </div>
            )}
          </div>
        </motion.div>
      </motion.div>
    </AnimatePresence>
  );
}