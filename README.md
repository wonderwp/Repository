# WonderWp Repository Component

Repositories wrap WordPress retrieval helpers (`get_posts`, `get_terms`, `get_users`, …) behind a small interface.

## Documentation

- [Repository component](http://wonderwp.net/Framewok_components/Repository/index.html)

## Testing

### Unit (no WordPress instance — CI)

```bash
composer install
composer test
```

Unit tests spy WordPress functions (`get_terms`, `get_posts`, `get_users`, …). No database required.

### Integration (real WordPress — any host project)

Requires a running WordPress where `wonderwp/repository` is installed via Composer:

```bash
wp eval-file vendor/wonderwp/repository/tests/Integration/smoke-taxonomy.php
wp eval-file vendor/wonderwp/repository/tests/Integration/smoke-posts.php

# DDEV example:
ddev wp eval-file vendor/wonderwp/repository/tests/Integration/smoke-taxonomy.php
ddev wp eval-file vendor/wonderwp/repository/tests/Integration/smoke-posts.php
```

- `smoke-taxonomy.php` — `TaxonomyRepository` vs built-in `category` (and host subclasses when present).
- `smoke-posts.php` — `PostRepository` vs published `post` fixtures.
