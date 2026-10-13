// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  routeRules: {
    // Forward /api/* to the Laravel container (avoids CORS and works during SSR)
    '/api/**': { proxy: `${process.env.NUXT_BACKEND_URL ?? 'http://backend:8000'}/api/**` },
  },
})
