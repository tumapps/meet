import { fileURLToPath, URL } from "url";

import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import commonjs from 'vite-plugin-commonjs';
// https://vitejs.dev/config/
export default defineConfig({
  base: '/',
  plugins: [
    vue(),
    commonjs(),
  ],
  resolve: {
    extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
    alias: {
      "@": fileURLToPath(new URL("./src", import.meta.url)),
    },
  },

  server: {
    host: true, // Enable the server to be accessed from the network
    proxy: {
      '/v1': {
        target: 'http://scheduler-back_end:80', // Use the service name of the backend container in Docker
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/api/, ''), // Remove /api from the path
      },
    },
    cors: false, // Ensure CORS is disabled if necessary
  },

  build: {
    outDir: 'dist',
    sourcemap: false, // Disable sourcemaps in production to reduce file size
    // Other build options
  },
  
});
