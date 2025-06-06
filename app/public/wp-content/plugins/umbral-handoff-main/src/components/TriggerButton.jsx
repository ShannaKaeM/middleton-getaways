import React from 'react';
import { motion } from 'framer-motion';

export function TriggerButton({ onClick }) {
  return (
    <motion.button
      onClick={onClick}
      style={{
        position: 'fixed',
        top: 'var(--space-2xl)',
        right: 'var(--space-2xl)',
        zIndex: 'var(--z-fixed)',
        backgroundColor: 'var(--surface-primary)',
        border: '1px solid var(--border-primary)',
        borderRadius: 'var(--radius-xl)',
        padding: 'var(--space-md) var(--space-lg)',
        color: 'var(--text-primary)',
        fontSize: 'var(--font-size-md)',
        fontWeight: 'var(--font-weight-medium)',
        fontFamily: 'var(--font-sans)',
        cursor: 'pointer',
        display: 'flex',
        alignItems: 'center',
        gap: 'var(--space-sm)',
        backdropFilter: 'blur(20px) saturate(180%)',
        WebkitBackdropFilter: 'blur(20px) saturate(180%)',
        boxShadow: 'var(--card-shadow-hover)',
        transition: 'all var(--transition-base)',
        outline: 'none'
      }}
      initial={{ opacity: 0, scale: 0.8, y: -10 }}
      animate={{ opacity: 1, scale: 1, y: 0 }}
      transition={{ delay: 2, duration: 0.3 }}
      whileHover={{ 
        scale: 1.05,
        backgroundColor: 'var(--surface-hover)',
        transition: { duration: 0.15 }
      }}
      whileTap={{ 
        scale: 0.95,
        transition: { duration: 0.1 }
      }}
    >
      🚀
      <span>Open Command Palette</span>
    </motion.button>
  );
}