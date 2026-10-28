# Lazy Load Comments

A dependency-free WordPress plugin: comments load only when the reader scrolls to them, via the REST API — so long comment threads stop slowing the initial page load.

## How it works

1. The theme's comments template is replaced with a tiny placeholder.
2. An IntersectionObserver (400px pre-load margin) fetches rendered comments from `wllc/v1/comments/{post}`.
3. Comments render through the theme's own template, so styling is unchanged.

## Features

- Zero libraries, ~2 KB of JS
- Only approved comments on published posts are ever served
- No-JS fallback renders comments normally
- Failed fetch shows a retry link instead of a blank area
- Nothing is stored — no options, no tables

## Install

Upload the plugin folder to `wp-content/plugins/` and activate it.

## License

MIT
