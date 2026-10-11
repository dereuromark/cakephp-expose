---
layout: home
hero:
  name: CakePHP Expose
  text: Public UUIDs, internal integer keys
  tagline: Add a public lookup identifier without changing your database relations.
  image:
    src: /logo.svg
    alt: Expose public identifier
  actions:
    - theme: brand
      text: Installation
      link: /guide/installation
    - theme: alt
      text: Getting started
      link: /guide/getting-started
---

Keep integer primary keys for internal joins and use a separate UUID in public URLs.
The default UUIDv4 generator adds no timestamp or record counter to the identifier.
Your application still needs authentication and authorization for private records.
