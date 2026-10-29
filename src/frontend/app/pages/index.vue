<script setup lang="ts">
const searchTerm = ref('')

function searchProducts() {
  const query = searchTerm.value.trim()
  navigateTo({ path: '/products', query: query ? { search: query } : {} })
}

const departments = [
  {
    name: 'Fresh produce',
    note: 'Fruit & vegetables',
    category: 'Fresh Foods',
    image: 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=720&q=85',
    alt: 'Colorful fresh produce at a market',
  },
  {
    name: 'Bakery',
    note: 'Baked this morning',
    category: 'Bakery',
    image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=720&q=85',
    alt: 'Freshly baked artisan bread',
  },
  {
    name: 'Dairy & eggs',
    note: 'Everyday essentials',
    category: 'Dairy & Eggs',
    image: 'https://images.unsplash.com/photo-1550583724-b2692b85b150?auto=format&fit=crop&w=720&q=85',
    alt: 'Milk and dairy products',
  },
  {
    name: 'Pantry',
    note: 'Stock up & save',
    category: 'Pantry & Dry Goods',
    image: 'https://images.unsplash.com/photo-1534723452862-4c874018d66d?auto=format&fit=crop&w=720&q=85',
    alt: 'Well-stocked grocery shelves',
  },
]

useHead({ title: 'FreshKeep Market | Groceries, made fresher' })
</script>

<template>
  <main class="storefront">
    <div class="announcement">
      <span>Freshness, delivered.</span>
      <span class="announcement-divider" aria-hidden="true">·</span>
      <span>Free delivery on your first order</span>
    </div>

    <header class="site-header">
      <NuxtLink class="brand" to="/" aria-label="FreshKeep Market home">
        <!-- <span class="brand-mark" aria-hidden="true">FK</span> -->
        <span class="brand-mark" aria-hidden="true">
          <img src="/images/FreshKeep.png" alt="FreshKeep Market logo" />
        </span>
        <span>FreshKeep <b>Market</b></span>
      </NuxtLink>

      <nav class="main-nav" aria-label="Main navigation">
        <NuxtLink class="active" to="/products">Shop groceries</NuxtLink>
        <a href="#departments">Departments</a>
        <a href="#promise">Our promise</a>
      </nav>

      <UButton to="/products" variant="outline" trailing-icon="i-lucide-arrow-up-right" :ui="{ trailingIcon: 'text-secondary' }">Browse</UButton>
    </header>

    <section class="hero" aria-labelledby="hero-title">
      <div class="hero-copy">
        <p class="eyebrow"><span class="fresh-dot"></span> GOOD FOOD, RIGHT ON TIME</p>
        <h1 id="hero-title">A little fresher.<br><em>A lot closer.</em></h1>
        <p class="hero-description">The good stuff, from the growers and makers we love, brought straight to your door.</p>

        <form class="search-form" role="search" @submit.prevent="searchProducts">
          <UInput
            v-model="searchTerm"
            type="search"
            icon="i-lucide-search"
            placeholder="What are you craving?"
            aria-label="Search groceries"
            variant="none"
            size="lg"
            class="flex-1"
          />
          <UButton type="submit" color="secondary" size="lg">Search</UButton>
        </form>

        <UButton to="/products" variant="link" trailing-icon="i-lucide-arrow-right" class="text-link" :ui="{ trailingIcon: 'text-secondary' }">
          Shop all groceries
        </UButton>
      </div>

      <div class="hero-photo">
        <img
          src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1600&q=90"
          alt="A colorful spread of fresh fruits and vegetables"
          fetchpriority="high"
        >
        <div class="photo-stamp"><span>Picked</span><strong>with care</strong><i aria-hidden="true">✳</i></div>
        <div class="photo-caption"><span class="caption-dot"></span> From good farms to good homes</div>
      </div>

      <div class="hero-side-note" aria-hidden="true">SEASONAL · LOCAL · DELICIOUS</div>
    </section>

    <section id="departments" class="departments" aria-labelledby="departments-title">
      <div class="section-heading">
        <div>
          <p class="eyebrow">THE GOOD STUFF STARTS HERE</p>
          <h2 id="departments-title">Shop by department</h2>
        </div>
        <UButton to="/products" variant="link" trailing-icon="i-lucide-arrow-right" class="text-link" :ui="{ trailingIcon: 'text-secondary' }">
          See everything
        </UButton>
      </div>

      <div class="department-grid">
        <NuxtLink v-for="(department, index) in departments" :key="department.name" class="department" :to="{ path: '/products', query: { category: department.category } }">
          <div class="department-photo" :class="`department-photo-${index + 1}`">
            <img :src="department.image" :alt="department.alt" loading="lazy">
            <span class="department-arrow" aria-hidden="true"><UIcon name="i-lucide-arrow-up-right" /></span>
          </div>
          <span class="department-name">{{ department.name }}</span>
          <span class="department-note">{{ department.note }}</span>
        </NuxtLink>
      </div>
    </section>

    <section id="promise" class="promise">
      <div class="promise-leaf" aria-hidden="true">✳</div>
      <p class="eyebrow">A MARKET WITH A LITTLE MORE HEART</p>
      <h2>Fresh choices.<br><em>Good things follow.</em></h2>
      <p>Thoughtful sourcing, seasonal favorites, and everyday staples worth coming back for.</p>
      <UButton to="/products" variant="link" trailing-icon="i-lucide-arrow-right" class="text-link" :ui="{ trailingIcon: 'text-secondary' }">
        Meet your new market
      </UButton>
    </section>

    <footer class="site-footer">
      <NuxtLink class="brand footer-brand" to="/">
        <span class="brand-mark" aria-hidden="true">
          <img src="/images/FreshKeep.png" alt="FreshKeep Market logo" />
        </span>
        <span>FreshKeep <b>Market</b></span>
      </NuxtLink>
      <span>Good groceries. A fresher kind of everyday.</span>
      <UButton to="/products" variant="link" size="sm" trailing-icon="i-lucide-arrow-right" class="text-link" :ui="{ trailingIcon: 'text-secondary' }">
        Shop groceries
      </UButton>
    </footer>
  </main>
</template>

<style scoped>
.brand-mark img {
    width: 25px;
    height: 25px;
    object-fit: contain;
}

.storefront {
  --ink: #20352c;
  --muted: #6d7b70;
  --green: #2f6848;
  --green-dark: #214c37;
  --leaf: #dce9d5;
  --tomato: #cb6046;
  --paper: #fcfbf6;
  min-height: 100vh;
  padding: 0 clamp(1.25rem, 6vw, 6rem);
  color: var(--ink);
  background: var(--paper);
  font-family: "Trebuchet MS", "Segoe UI", sans-serif;
}

.announcement {
  min-height: 33px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  margin: 0 calc(clamp(1.25rem, 6vw, 6rem) * -1);
  background: var(--green-dark);
  color: #f5f4e9;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .04em;
}
.announcement-divider { color: #d7a866; }
.site-header { min-height: 76px; display: flex; align-items: center; justify-content: space-between; gap: 24px; border-bottom: 1px solid #e9e8dd; }
.brand { display: inline-flex; align-items: center; gap: 9px; color: var(--ink); font-size: 17px; font-weight: 800; text-decoration: none; white-space: nowrap; }
.brand b { color: var(--green); font-family: Georgia, "Times New Roman", serif; font-size: 18px; font-style: italic; font-weight: 500; }
.brand-mark { width: 30px; height: 30px; display: grid; place-items: center; border-radius: 50% 50% 50% 4px; background: var(--green); color: white; font-family: Georgia, "Times New Roman", serif; font-size: 21px; font-style: italic; }
.main-nav { display: flex; align-items: center; gap: clamp(18px, 3vw, 42px); }
.main-nav a { color: #59695e; font-size: 12px; font-weight: 700; text-decoration: none; }
.main-nav a:hover, .main-nav a.active { color: var(--green-dark); }
.hero { position: relative; min-height: 520px; display: grid; grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr); align-items: center; gap: clamp(35px, 7vw, 95px); padding: 54px 0 64px; }
.hero-copy { max-width: 500px; padding: 12px 0; animation: rise-in .55s ease-out both; }
.eyebrow { margin: 0 0 16px; color: var(--green); font-size: 10px; font-weight: 800; letter-spacing: .12em; }
.fresh-dot, .caption-dot { width: 7px; height: 7px; display: inline-block; margin-right: 7px; border-radius: 50%; background: #df8966; vertical-align: 1px; }
h1, h2 { margin: 0; font-family: Georgia, "Times New Roman", serif; font-weight: 500; }
h1 { font-size: clamp(48px, 6vw, 78px); line-height: .99; }
h1 em, .promise h2 em { color: var(--green); font-weight: 400; }
.hero-description { max-width: 360px; margin: 22px 0 25px; color: var(--muted); font-size: 14px; line-height: 1.8; }
.search-form { width: min(100%, 450px); display: flex; align-items: center; gap: 8px; padding: 5px; border: 1px solid #dce2d8; border-radius: 5px; background: #fff; box-shadow: 0 5px 20px #25442b0b; }
.text-link { padding-inline: 0; color: var(--green-dark); font-weight: 800; }
.hero-copy > .text-link { margin-top: 21px; }
.hero-photo { position: relative; min-height: 365px; height: min(37vw, 430px); overflow: hidden; border-radius: 4px 72px 4px 4px; background: #d6e0ce; animation: rise-in .7s .08s ease-out both; }
.hero-photo::after { position: absolute; inset: 45% 0 0; background: linear-gradient(transparent, #152b1d6b); content: ''; pointer-events: none; }
.hero-photo img { width: 100%; height: 100%; position: absolute; inset: 0; object-fit: cover; }
.photo-stamp { position: absolute; z-index: 1; top: 23px; right: 23px; width: 88px; height: 88px; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 50%; background: #f4d477; color: var(--green-dark); font-family: Georgia, "Times New Roman", serif; font-size: 13px; line-height: 1.15; transform: rotate(8deg); }
.photo-stamp strong { font-size: 14px; font-style: italic; font-weight: 500; }
.photo-stamp i { position: absolute; right: 13px; bottom: 11px; color: var(--tomato); font-size: 13px; font-style: normal; }
.photo-caption { position: absolute; z-index: 1; right: 20px; bottom: 18px; left: 20px; color: white; font-size: 11px; font-weight: 700; }
.caption-dot { background: #e9ca70; }
.hero-side-note { position: absolute; right: -32px; bottom: 106px; color: #a1ab9f; font-size: 9px; font-weight: 700; letter-spacing: .14em; writing-mode: vertical-rl; }
.departments { padding: 18px 0 72px; }
.section-heading { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 23px; }
.section-heading .eyebrow { margin-bottom: 8px; }
h2 { font-size: clamp(26px, 3vw, 36px); }
.section-heading > .text-link { margin-bottom: 5px; }
.department-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
.department { min-width: 0; color: var(--ink); text-decoration: none; animation: rise-in .55s ease-out both; }
.department:nth-child(2) { animation-delay: .06s; }
.department:nth-child(3) { animation-delay: .12s; }
.department:nth-child(4) { animation-delay: .18s; }
.department-photo { position: relative; aspect-ratio: 1.52; overflow: hidden; border-radius: 4px; background: var(--leaf); }
.department-photo img { width: 100%; height: 100%; object-fit: cover; transition: transform .35s ease; }
.department:hover .department-photo img { transform: scale(1.045); }
.department-photo-2 img { object-position: center 62%; }
.department-photo-3 img { object-position: center 60%; }
.department-arrow { position: absolute; right: 10px; bottom: 10px; width: 29px; height: 29px; display: grid; place-items: center; border-radius: 50%; background: #fff; color: var(--green-dark); font-size: 14px; }
.department-name, .department-note { display: block; }
.department-name { margin-top: 11px; font-family: Georgia, "Times New Roman", serif; font-size: 17px; }
.department-note { margin-top: 3px; color: var(--muted); font-size: 10px; }
.promise { position: relative; overflow: hidden; padding: 50px 20px 55px; background: #eaf0e3; text-align: center; }
.promise .eyebrow { margin-bottom: 12px; }
.promise h2 { font-size: clamp(34px, 4vw, 48px); line-height: 1.08; }
.promise > p:not(.eyebrow) { max-width: 400px; margin: 14px auto 18px; color: #66776a; font-size: 12px; line-height: 1.7; }
.promise-leaf { position: absolute; top: 15px; right: 9%; color: #ccdbc5; font-size: 65px; transform: rotate(15deg); }
.site-footer { min-height: 82px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
.footer-brand { font-size: 14px; }
.footer-brand .brand-mark { width: 25px; height: 25px; font-size: 18px; }
.footer-brand b { font-size: 15px; }
.site-footer > span { color: var(--muted); font-size: 10px; }

@keyframes rise-in {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 800px) {
  .main-nav { gap: 15px; }
  .main-nav a { font-size: 11px; }
  .hero { min-height: auto; gap: 30px; padding: 45px 0 52px; }
  .hero-photo { min-height: 310px; height: 42vw; }
  .hero-side-note { right: -18px; bottom: 72px; }
}

@media (max-width: 620px) {
  .storefront { padding: 0 19px; }
  .announcement { margin: 0 -19px; font-size: 9px; }
  .site-header { min-height: 66px; gap: 10px; }
  .brand { gap: 7px; font-size: 14px; }
  .brand b { font-size: 15px; }
  .brand-mark { width: 26px; height: 26px; font-size: 18px; }
  .main-nav { display: none; }
  .hero { grid-template-columns: 1fr; gap: 27px; padding: 38px 0 46px; }
  .hero-copy { padding: 0; }
  h1 { font-size: clamp(48px, 13vw, 64px); }
  .hero-description { max-width: 330px; margin: 16px 0 19px; font-size: 13px; }
  .hero-photo { min-height: 260px; height: 68vw; max-height: 360px; border-top-right-radius: 52px; }
  .photo-stamp { top: 14px; right: 14px; width: 73px; height: 73px; font-size: 11px; }
  .photo-stamp strong { font-size: 12px; }
  .hero-side-note { display: none; }
  .departments { padding: 8px 0 48px; }
  .section-heading { align-items: start; }
  .section-heading .text-link { flex: 0 0 auto; margin-top: 25px; font-size: 10px; }
  .department-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px 12px; }
  .department-photo { aspect-ratio: 1.35; }
  .department-name { font-size: 16px; }
  .promise { margin: 0 -19px; padding: 43px 25px 48px; }
  .promise-leaf { right: 4%; font-size: 48px; }
  .site-footer { min-height: 100px; flex-wrap: wrap; justify-content: space-between; padding: 17px 0; }
  .site-footer > span { order: 1; width: 100%; }
}

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
}
</style>