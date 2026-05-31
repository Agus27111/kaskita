import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { createApp, h } from 'vue';
import type { DefineComponent } from 'vue';

const appName = import.meta.env.VITE_APP_NAME || 'KasKita';
type PageModule = { default: DefineComponent };
const pages = import.meta.glob<PageModule>('./pages/**/*.vue');

createInertiaApp({
    title: (title) => `${title} - ${appName}`,

    resolve: (name) => {
        const page = pages[`./pages/${name}.vue`];

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }

        return page().then((module) => module.default);
    },

    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .mount(el);
    },

    progress: {
        color: '#10b981',
    },
});
