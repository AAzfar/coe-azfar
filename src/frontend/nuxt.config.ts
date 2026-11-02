// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: ['@nuxt/ui'],
  css: ['~/assets/css/main.css'],
  // The site is designed for a light background only
  colorMode: { preference: 'light' },
  // Keep the icon endpoint out of /api/**, which is proxied to Laravel below
  icon: { localApiEndpoint: '/_nuxt_icon' },
  routeRules: {
    // Forward /api/* to the Laravel container (avoids CORS and works during SSR)
    '/api/**': { proxy: `${process.env.NUXT_BACKEND_URL ?? 'http://backend:8000'}/api/**` },
  },
})
