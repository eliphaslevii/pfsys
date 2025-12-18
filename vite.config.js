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
        "resources/js/comercial/xml-details.js",
        "resources/js/comercial/recusa-create.js",
        "resources/js/comercial/devolucao-create.js",
        "resources/js/comercial/process-table.js",
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
