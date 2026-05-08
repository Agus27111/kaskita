import { createInertiaApp } from '@inertiajs/vue3'
import { createPinia } from 'pinia'
import { createApp, h } from 'vue'

const appName = import.meta.env.VITE_APP_NAME || 'KasKita'

createInertiaApp({
    title: (title) => `${title} - ${appName}`,

    resolve: name => {
        const pages = import.meta.glob(['./Pages/**/*.vue', './pages/**/*.vue'], { eager: true })

        return pages[`./Pages/${name}.vue`] || pages[`./pages/${name}.vue`]
    },

    setup({ el, App, props, plugin }) {
        const pinia = createPinia()

        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .mount(el)
    },

    progress: {
        color: '#10b981',
    },
})