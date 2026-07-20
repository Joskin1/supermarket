import { defineConfig, externalizeDepsPlugin } from 'electron-vite';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const electronPath = dirname(fileURLToPath(import.meta.url));
const appPath = process.env.APP_PATH ?? join(electronPath, '..', '..');

export default defineConfig({
    main: {
        build: {
            rollupOptions: {
                plugins: [
                    {
                        name: 'watch-external',
                        buildStart() {
                            this.addWatchFile(join(appPath, 'app', 'Providers', 'NativeAppServiceProvider.php'));
                        },
                    },
                ],
            },
        },
        plugins: [externalizeDepsPlugin()],
    },
});
