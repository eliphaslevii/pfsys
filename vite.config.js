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
        'resources/js/modules/returnProcess/return-process-flow.js',
        'resources/js/modules/returnProcess/return-process-utils.js',
        'resources/js/modules/returnProcess/return-process-ui.js',
        'resources/js/modules/users/users-create.js',
        'resources/js/modules/users/users-edit.js',
        "resources/js/modules/sectors/delete.js",
        "resources/js/modules/sectors/create.js",
        "resources/js/modules/sectors/edit.js",
        "resources/js/modules/levels/delete.js",
        "resources/js/modules/levels/create.js",
        "resources/js/modules/levels/edit.js",
        "resources/js/modules/reasons/delete.js",
        "resources/js/modules/workflowTemplate/edit.js",
        "resources/js/modules/workflowTemplate/create.js",
        "resources/js/modules/steps/form.js",

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
