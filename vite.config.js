import { fileURLToPath, URL } from 'url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import commonjs from 'vite-plugin-commonjs'
// https://vitejs.dev/config/
export default defineConfig({
  base: '/',
  plugins: [vue(), commonjs()],
  test: {
    environment: 'jsdom',  // 👈 This enables the browser-like DOM
  },
  resolve: {
    extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    host: true, // Enable the server to be accessed from the network
    proxy: {
      '/v1': {
        target: 'http://127.0.0.1:9002/',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, '') // Remove /api from the path
      }
    },
    cors: false // Ensure CORS is disabled if necessary
  },

  build: {
    outDir: 'dist', // Specify the output directory
    minify: 'esbuild', // Use esbuild for minification
    sourcemap: false, // Disable sourcemaps in production to reduce file size
    rollupOptions: {
      output: {
        manualChunks: {
          pdfjs: ['pdfjs-dist']
        }
      }
    }
    // Other build options
  }
})
