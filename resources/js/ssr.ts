import { createSSRApp, h, type DefineComponent } from 'vue';
import { renderToString } from '@vue/server-renderer';
import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { createPinia } from 'pinia';

const appName = import.meta.env.VITE_APP_NAME || 'KasKita';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: (name) => {
            const pages = import.meta.glob<DefineComponent>(['./Pages/**/*.vue', './pages/**/*.vue'], { eager: true });
            return pages[`./Pages/${name}.vue`] || pages[`./pages/${name}.vue`];
        },
        setup({ App, props, plugin }) {
            const pinia = createPinia();
            return createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .use(pinia);
        },
    }),
);
