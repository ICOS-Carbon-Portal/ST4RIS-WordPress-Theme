# ST4RIS WordPress Theme

ST4RIS is a WordPress block child theme for the ST4RIS project website. It extends the default `twentytwentyfour` theme with custom branding, templates, patterns, custom post types, and a staff-story carousel.

## Requirements

- WordPress 6.x or later
- The Twenty Twenty-Four theme installed (`twentytwentyfour`)

## Features

- Child theme of Twenty Twenty-Four
- Full-site editing/block theme support
- Custom ST4RIS color palette and typography in `theme.json`
- Homepage pattern with hero, project intro, feature cards, stories, events, CTA, and consortium sections
- Carousel for staff stories

## Custom post types

The theme registers two custom post types:

### Staff Stories

Admin label: **Staff Stories**  
Post type: `staff_story`

Used by the shortcode:

```text
[st4ris_staff_stories]
```

Each published story can use:

- Title: shown as the attribution/footer
- Content or excerpt: shown as the quote
- Featured image: shown as the story image

If no stories are published, the theme displays fallback sample quotes.

### Events

Admin label: **Events**  
Post type: `st4ris_event`

Used by the shortcode:

```text
[st4ris_events]
```

Event metadata:

- `event_date`
- `event_location`
- `event_type` (`event` or `achievement`)

If no events are published, the theme displays fallback sample event cards.

## Shortcodes

Use these in block content, pages, or patterns:

```text
[st4ris_staff_stories]
[st4ris_events]
```

## Editing the homepage

The main editable homepage pattern is located at:

```text
patterns/homepage.php
```

It includes hard-coded image paths that assume the theme directory is named `st4ris`:

```text
/wp-content/themes/st4ris/assets/...
```

If the folder name changes, update these paths in the pattern and template parts.

## Development notes

- Styles are loaded from the parent theme and then `style.css`.
- `style.css` uses `filemtime()` for cache busting during development.
- `js/carousel.js` is also loaded with `filemtime()` cache busting.
- No build step or package manager is required.

## License

GNU General Public License v2 or later.
