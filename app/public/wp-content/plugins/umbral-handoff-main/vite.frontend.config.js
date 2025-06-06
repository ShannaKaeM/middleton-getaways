import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  plugins: [react()],
  
  define: {
    'process.env.NODE_ENV': JSON.stringify('production'),
    'process.env': {}
  },
  
  build: {
    outDir: 'dist/js',
    emptyOutDir: false,
    lib: {
      entry: 'src/FrontendEditor.jsx',
      name: 'UmbralFrontendEditor',
      fileName: () => 'umbral-frontend-editor.js',
      formats: ['iife']
    },
    rollupOptions: {
      external: [],
      output: {
        globals: {}
      }
    },
    sourcemap: false,
    minify: 'terser'
  },
  
  server: {
    port: 3001,
    host: true
  }
});