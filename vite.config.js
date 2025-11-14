import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',

        // 🔹 Entrypoints modulares (um por tela ou módulo)
        'resources/js/modules/returnProcess/create.js',
        'resources/js/modules/returnProcess/index.js',
        'resources/js/modules/returnProcess/steps.js',

        'resources/js/modules/returnProcess/return-process.js',
        'resources/js/modules/returnProcess/return-process-index.js',
      ],
      refresh: true,
    }),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources/js'),
      '~': path.resolve(__dirname, 'resources'),
    },
  },
});
