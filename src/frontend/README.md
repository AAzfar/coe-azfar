# Frontend — Nuxt 4 website

The FreshKeep Market website: Nuxt 4 with [Nuxt UI](https://ui.nuxt.com) and Tailwind CSS. It is started by the root `docker-compose.yml` on http://localhost:3000. See the [root README](../../README.md) for setup.

## Pages

| Route              | File                          | What it does |
|--------------------|-------------------------------|--------------|
| `/`                | `app/pages/index.vue`         | Storefront landing page; department tiles link to `/products?category=<slug>` |
| `/products`        | `app/pages/products/index.vue`| Product list: search, department filter, sortable columns, pagination |
| `/products/create` | `app/pages/products/create.vue`| Add-product form; departments come from `/api/categories` |
| `/products/:id`    | `app/pages/products/[id].vue` | Product detail |

The product list does not filter or sort in the browser. Each change sends a new `GET /api/products` with `search`, `category`, `sort`, `page` and `per_page`, so it scales with the catalogue. Search waits 300 ms after typing stops before sending.

API response types live in `app/types/api.ts`. Errors follow the backend's `{ error: { status, code, message, details } }` shape. The create form maps `details` back onto the matching form fields.

## Talking to the API

The browser only calls `/api/**` on the Nuxt server. `nuxt.config.ts` proxies those requests to Laravel, at `http://backend:8000` inside Docker, so there is no CORS setup. This works the same during server-side rendering.

## Other files

| Path                     | What's there |
|--------------------------|--------------|
| `app/app.vue`            | Root component: wraps every page in Nuxt UI's `<UApp>` |
| `app/app.config.ts`      | Nuxt UI colour theme (`forest`, `tomato`, `stone`) |
| `app/assets/css/main.css`| Tailwind setup, fonts and custom colour scales |
| `public/`                | Static files served from `/` (e.g. `public/images/FreshKeep.jpg` → `/images/FreshKeep.jpg`) |

## Running outside Docker

With the backend still running in Docker on port 8000:

```bash
cd src/frontend
npm install
NUXT_BACKEND_URL=http://localhost:8000 npm run dev
```

Other scripts: `npm run build` for a production build, and `npm run preview` to serve that build.
