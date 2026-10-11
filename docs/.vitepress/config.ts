import { defineConfig } from 'vitepress'

const sidebar = [
  { text: 'Guide', items: [
    { text: 'Installation', link: '/guide/installation' },
    { text: 'Getting started', link: '/guide/getting-started' },
    { text: 'Public lookups', link: '/guide/lookups' },
    { text: 'Existing records', link: '/guide/existing-records' },
    { text: 'Superimposition', link: '/guide/superimposition' },
    { text: 'Short UUIDs', link: '/guide/short-uuids' },
    { text: 'Administration', link: '/guide/admin' },
    { text: 'Choosing identifiers', link: '/guide/motivation' },
  ] },
  { text: 'Reference', items: [
    { text: 'Configuration', link: '/reference/configuration' },
    { text: 'Upgrading', link: '/reference/upgrading' },
  ] },
]

export default defineConfig({
  title: 'CakePHP Expose',
  description: 'Public UUID identifiers alongside internal integer primary keys.',
  base: '/cakephp-expose/',
  cleanUrls: true,
  lastUpdated: true,
  srcExclude: ['README.md', 'Migrating.md', 'Motivation.md'],
  sitemap: { hostname: 'https://dereuromark.github.io/cakephp-expose/' },
  head: [['link', { rel: 'icon', href: '/cakephp-expose/favicon.svg', type: 'image/svg+xml' }]],
  themeConfig: {
    logo: '/logo.svg',
    nav: [
      { text: 'Installation', link: '/guide/installation' },
      { text: 'Getting started', link: '/guide/getting-started' },
      { text: 'Reference', link: '/reference/configuration' },
    ],
    sidebar,
    socialLinks: [{ icon: 'github', link: 'https://github.com/dereuromark/cakephp-expose' }],
    search: { provider: 'local' },
    editLink: { pattern: 'https://github.com/dereuromark/cakephp-expose/edit/cake6/docs/:path' },
    footer: { message: 'Released under the MIT License.', copyright: 'Copyright Mark Scherer' },
  },
})
