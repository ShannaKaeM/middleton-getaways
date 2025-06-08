/* 
 * Middleton Getaways Design System - Windpress CSS
 * Copy this content into your Windpress plugin CSS editor
 */

@layer theme, base, components, utilities;

@import "tailwindcss/theme.css" layer(theme) theme(static);
/* @import "tailwindcss/preflight.css" layer(base); */
@import "tailwindcss/utilities.css" layer(utilities);

/* ===================================================================
   MIDDLETON GETAWAYS DESIGN SYSTEM - WINDPRESS MAIN CSS
   Generic, Reusable Design System Foundation
   =================================================================== */

@theme {
  /* === BASE COLOR SCALE === */
  --color-primary-50: #f7f8f3;
  --color-primary-100: #eef1e6;
  --color-primary-200: #dde3ce;
  --color-primary-300: #c6d0ab;
  --color-primary-400: #a9b87f;
  --color-primary-500: #869648;  /* Main brand color */
  --color-primary-600: #6b7a37;
  --color-primary-700: #545e2e;
  --color-primary-800: #454c26;
  --color-primary-900: #3a4022;
  --color-primary-950: #1d2012;

  --color-secondary-50: #f6f6f6;
  --color-secondary-100: #e7e7e7;
  --color-secondary-200: #d1d1d1;
  --color-secondary-300: #b0b0b0;
  --color-secondary-400: #888888;
  --color-secondary-500: #616161;  /* Main secondary */
  --color-secondary-600: #5a5a5a;
  --color-secondary-700: #4f4f4f;
  --color-secondary-800: #454545;
  --color-secondary-900: #3d3d3d;
  --color-secondary-950: #262626;

  --color-neutral-50: #faf9f7;
  --color-neutral-100: #f3f1ed;
  --color-neutral-200: #e8e4db;
  --color-neutral-300: #d6d0c4;
  --color-neutral-400: #b8b0a1;
  --color-neutral-500: #9c927f;
  --color-neutral-600: #857b68;
  --color-neutral-700: #6f6555;
  --color-neutral-800: #5c5349;
  --color-neutral-900: #4e453e;
  --color-neutral-950: #292520;

  --color-base-50: #fafafa;
  --color-base-100: #f4f4f5;
  --color-base-200: #e4e4e7;
  --color-base-300: #d4d4d8;
  --color-base-400: #a1a1aa;
  --color-base-500: #71717a;
  --color-base-600: #52525b;
  --color-base-700: #3f3f46;
  --color-base-800: #27272a;
  --color-base-900: #18181b;
  --color-base-950: #09090b;

  /* === SEMANTIC COLORS === */
  --color-canvas: var(--color-neutral-50);
  --color-surface: var(--color-base-50);
  --color-border: var(--color-neutral-200);
  --color-text: var(--color-base-800);
  --color-text-muted: var(--color-base-600);

  /* === CARD-SPECIFIC TOKENS === */
  --card-background: var(--color-surface);
  --card-border: var(--color-border);
  --card-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  --card-shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  --card-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
  --card-shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);

  /* === PRETITLE COLORS === */
  --pretitle-color: var(--color-primary-600);

  /* === SPACING SCALE === */
  --spacing-xs: 0.5rem;    /* 8px */
  --spacing-sm: 0.75rem;   /* 12px */
  --spacing-md: 1rem;      /* 16px */
  --spacing-lg: 1.5rem;    /* 24px */
  --spacing-xl: 2rem;      /* 32px */
  --spacing-2xl: 3rem;     /* 48px */
  --spacing-3xl: 4rem;     /* 64px */

  /* === BORDER RADIUS === */
  --radius-sm: 0.375rem;   /* 6px */
  --radius-md: 0.5rem;     /* 8px */
  --radius-lg: 0.75rem;    /* 12px */
  --radius-xl: 1rem;       /* 16px */

  /* === SHADOWS === */
  --shadow-sm: var(--card-shadow-sm);
  --shadow-md: var(--card-shadow-md);
  --shadow-lg: var(--card-shadow-lg);
  --shadow-xl: var(--card-shadow-xl);

  /* === TYPOGRAPHY === */
  --font-size-xs: 0.75rem;     /* 12px */
  --font-size-sm: 0.875rem;    /* 14px */
  --font-size-base: 1rem;      /* 16px */
  --font-size-lg: 1.125rem;    /* 18px */
  --font-size-xl: 1.25rem;     /* 20px */
  --font-size-2xl: 1.5rem;     /* 24px */
  --font-size-3xl: 1.875rem;   /* 30px */
}

/* === CARD SYSTEM === */
@utility card {
  background-color: var(--card-background);
  border: 1px solid var(--card-border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
  padding: var(--spacing-md);
}

@utility card-header {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

@utility card-content {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
  flex: 1;
}

@utility card-title {
  font-size: var(--font-size-xl);
  font-weight: 600;
  line-height: 1.25;
  color: var(--color-text);
}

@utility card-description {
  font-size: var(--font-size-base);
  line-height: 1.5;
  color: var(--color-text-muted);
}

@utility card-footer {
  display: flex;
  gap: var(--spacing-sm);
  align-items: center;
  flex-wrap: wrap;
}

/* === BUTTON SYSTEM === */
@utility btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-md);
  font-weight: 500;
  text-decoration: none;
  transition: all 150ms ease;
  cursor: pointer;
  border: 1px solid transparent;
  gap: var(--spacing-xs);
}

/* Button Sizes */
@utility btn-sm {
  padding: 0.5rem 0.75rem;
  font-size: var(--font-size-sm);
}

@utility btn-md {
  padding: 0.625rem 1rem;
  font-size: var(--font-size-base);
}

@utility btn-lg {
  padding: 0.75rem 1.25rem;
  font-size: var(--font-size-lg);
}

/* Button Styles */
@utility btn-primary {
  background-color: var(--color-primary-600);
  border-color: var(--color-primary-600);
  color: white;
}

@utility btn-primary:hover {
  background-color: var(--color-primary-700);
  border-color: var(--color-primary-700);
}

@utility btn-secondary {
  background-color: var(--color-secondary-600);
  border-color: var(--color-secondary-600);
  color: white;
}

@utility btn-secondary:hover {
  background-color: var(--color-secondary-700);
  border-color: var(--color-secondary-700);
}

@utility btn-outline {
  background-color: transparent;
  border-color: var(--color-primary-600);
  color: var(--color-text);
}

@utility btn-outline:hover {
  background-color: var(--color-surface);
  border-color: var(--color-primary-600);
  color: var(--color-primary-600);
}

@utility btn-ghost {
  background-color: transparent;
  border-color: transparent;
  color: var(--color-primary-600);
}

@utility btn-ghost:hover {
  background-color: var(--color-primary-50);
  color: var(--color-primary-700);
}

/* === CARD SIZE VARIANTS === */
@utility card-sm {
  --card-padding: var(--spacing-sm);
  --card-header-padding: var(--spacing-sm);
  --card-content-padding: var(--spacing-sm);
  --card-footer-padding: var(--spacing-sm);
  --card-gap: var(--spacing-xs);
  --card-radius: var(--radius-sm);
  --card-shadow: var(--shadow-sm);
}

@utility card-md {
  --card-padding: var(--spacing-md);
  --card-header-padding: var(--spacing-md);
  --card-content-padding: var(--spacing-md);
  --card-footer-padding: var(--spacing-md);
  --card-gap: var(--spacing-sm);
  --card-radius: var(--radius-md);
  --card-shadow: var(--shadow-md);
}

@utility card-lg {
  --card-padding: var(--spacing-lg);
  --card-header-padding: var(--spacing-lg);
  --card-content-padding: var(--spacing-lg);
  --card-footer-padding: var(--spacing-lg);
  --card-gap: var(--spacing-md);
  --card-radius: var(--radius-lg);
  --card-shadow: var(--shadow-lg);
}

@utility card-xl {
  --card-padding: var(--spacing-xl);
  --card-header-padding: var(--spacing-xl);
  --card-content-padding: var(--spacing-xl);
  --card-footer-padding: var(--spacing-xl);
  --card-gap: var(--spacing-lg);
  --card-radius: var(--radius-lg);
  --card-shadow: var(--shadow-lg);
}

/* === CONTAINER QUERY UTILITIES === */
@utility \@container\/card {
  container-type: inline-size;
  container-name: card;
}

/* === SEMANTIC COLOR UTILITIES === */
/* Text colors using semantic tokens */
@utility text-primary-600 {
  color: var(--color-primary-600);
}

@utility text-base-800 {
  color: var(--color-base-800);
}

@utility text-base-600 {
  color: var(--color-base-600);
}

@utility text-base-700 {
  color: var(--color-base-700);
}

/* === CARD PRETITLE UTILITY === */
@utility card-pretitle {
  font-size: var(--font-size-xs);
  color: var(--pretitle-color);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  line-height: 1.4;
}

/* === RESPONSIVE GRID UTILITIES === */
@utility grid-cols-1 {
  grid-template-columns: repeat(1, minmax(0, 1fr));
}

@utility md\:grid-cols-2 {
  @media (min-width: 768px) {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@utility lg\:grid-cols-3 {
  @media (min-width: 1024px) {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
