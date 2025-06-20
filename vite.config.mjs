import { defineConfig } from 'vite';
import { resolve } from 'path';
import autoprefixer from 'autoprefixer'

// Get the main.js where all your JavaScript files are imported
const ROOT = resolve('../../../')
const BASE = __dirname.replace(ROOT, '')

// Define where the compiled and minified JavaScript files will be saved
const BUILD_DIR = resolve(__dirname, 'dist');

export default defineConfig({
  css: {
    postcss: {
      map: true,
      plugins: [
          autoprefixer({})
      ]
    },
    devSourcemap: true
  },
  base: process.env.NODE_ENV === 'production' ? `${BASE}/dist/` : BASE,
  build: {
    assetsDir: '',
    manifest: true,
    emptyOutDir: true,
    outDir: BUILD_DIR,
    sourcemap: true,
    rollupOptions: {
      input: [
        'src/scripts/main.js',
        'src/styles/main.scss',
        'src/admin/styles/main.scss'
      ],
      output: {
        entryFileNames: '[hash].js',
        assetFileNames: '[hash].[ext]'
      }
    },
  },
});