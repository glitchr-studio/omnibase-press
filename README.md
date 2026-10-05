# Press

The press kit for [omnibase](https://github.com/glitchr-studio/omnibase): what
an artist's agency - or a presenter's programme editor - asks a site for, on
one page. First made for two musicians' sites (a harpist, a violinist), it
holds nothing that is theirs alone.

A `Quote` is what was said or written: the words, who said them ("Joshua
Bell", "violinist"), where (the publication, its link, its date), what about
(an album's title, a concert - free words, no relation), its kind (`press`,
`colleague`, `audience`). One row is one language - the text and its `locale`;
a quote with no locale is shown in every language. The featured ones are what
a home page shows.

A `Photo` is the original as the photographer delivered it (an omnibase
upload, 64 MB at most), its credit - printed "© Harald Hoffmann" -, a
caption, whether the original may be downloaded, and where it is shown: in
the press kit (`visible`, on `/press`), in the site's own gallery (`gallery`,
`press_gallery()`), or both - a concert photograph the photographer has not
cleared for the press goes in the gallery only. `fileUrl` is the address an
`<img>` takes (`/uploads/…`; `file` is the storage's path on the server). The
download is named `<site>-<slug>.<ext>`, so the file in a journalist's folder
still says whose it is.

A `Bio` is the biography once per language and per length: `short` (about
100 words, a season brochure), `medium` (about 250, a programme note), `long`
(the full one). Plain text, a blank line between two paragraphs: it is copied
into a programme, not laid out here. The page shows the reader's language and
falls back on English.

## Install

```bash
composer require omnibase/press:dev-main
```

```php
// config/bundles.php
Base\Press\PressBundle::class => ['all' => true],
```

```yaml
# config/routes.yaml
press_controller:
    resource: "@PressBundle/src/Controller/Client"
    type: attribute
    prefix: /
```

```yaml
# config/packages/press.yaml (every key optional)
press:
    download_kit: true                  # the HD downloads at all
    bio_lengths: [short, medium, long]  # the biographies shown, in this order
    contacts:
        - name: "Anna Keller"
          role: "General management"
          organisation: "Keller Artists"
          email: "anna@keller-artists.example"
          phone: "+49 30 000 00 00"
          url: "https://keller-artists.example"
```

Then `bin/console doctrine:migrations:diff && bin/console doctrine:migrations:migrate`
and `bin/console assets:install` (the stylesheet lives in `public/css/press.css`).

## Routes

| Route                  | Path                          |                                             |
|------------------------|-------------------------------|---------------------------------------------|
| `press_index`          | `GET /press`                  | the page, in the sitemap                    |
| `press_photo_download` | `GET /press/photo/{id}/download` | the original as an attachment; 404 when the picture is not downloadable or `download_kit` is off |

## What the host provides

The template extends `layout1.html.twig` and fills `title`, `description`,
`content` and `stylesheets`: that is the whole contract. Three Twig functions
help the host's own pages:

- `press_quotes(limit = 3, kind = null)` - the quotes of the reader's
  language, the featured ones first (`kind`: `press`, `colleague`, `audience`);
  `{% include '@Press/client/_quote.html.twig' with {quote: quote} %}` renders one;
- `press_bio(length = 'short', locale = null)` - one biography (`.content`,
  `.paragraphs`, `.wordCount`), in the reader's language or English;
- `press_photos(limit = null)` - the press kit's pictures (`.fileUrl`, `.alt`, `.caption`, `.creditLine`).
- `press_gallery(limit = null)` - the pictures of the site's own gallery (`gallery` ticked in the back office), in the press kit or not.

The look follows the host's custom properties when it defines them:
`--press-accent`, `--press-ink`, `--press-soft`, `--press-line`,
`--press-surface`, `--press-measure`.

The back office gets three CRUDs: `Quote` (filters by kind, featured,
language; a "feature" button), `Photo` and `Bio` (the language from the
site's `framework.enabled_locales`, the words counted as they are typed).
An application takes an entity over by declaring `App\Entity\Press\Quote`
extending ours.

The three CRUDs are written by the site's administrator (`ROLE_ADMIN`), not
by the super-admin only: they carry omnibase/admin's `#[OpenToAdmins]` -
creating, editing, deleting, and the quotes' own `feature`. The attribute
needs an omnibase/admin that has it (main from 7474f85); on an
older one the bundle declares a stand-in of that name (`compat/OpenToAdmins.php`:
omnibase instantiates every attribute of a controller, and a class that does
not exist stopped the site), nothing applies it and the screens are the
super-admin's to write, as they were.

## Tests

```bash
vendor/bin/phpunit
```

The unit tests (`tests/Entity`, `tests/Service`) need no kernel.
`tests/Controller/Admin/OpenToAdminsTest` (an administrator writes in the back
office, a plain user does not) runs inside a host application
(`php vendor/bin/phpunit -c vendor/omnibase/press/phpunit.xml.dist`) and is
skipped elsewhere.
