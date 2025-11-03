# WordPress Playground Preview

The files in this folder let you spin up a browser-based WordPress instance that automatically installs the Porto parent and child themes from this repository. It uses [WordPress Playground](https://playground.wordpress.net/), so no PHP or MySQL server is required.

## How to launch the preview

1. Make sure your changes are pushed to GitHub.
2. Open the following URL in a browser (replace `USERNAME`, `REPO`, and `BRANCH` if necessary):
   ```
   https://playground.wordpress.net/?mode=seamless&blueprint-url=https://raw.githubusercontent.com/USERNAME/REPO/BRANCH/playground/porto-blueprint.json
   ```
3. When the Playground finishes loading, log in with:
   - **Username:** `admin`
   - **Password:** `password`
4. You can now browse the site or customize the theme. Any edits are sandboxed in your browser.

Alternatively, you can view [`index.html`](./index.html) through a static HTML proxy such as [htmlpreview.github.io](https://htmlpreview.github.io/) to get an embedded experience:

```
https://htmlpreview.github.io/?https://github.com/USERNAME/REPO/blob/BRANCH/playground/index.html
```

## What’s included

- `porto-blueprint.json` – WordPress Playground blueprint that installs and activates the child theme. It embeds base64-encoded archives of both the parent and child themes, so no binary files are required.
- `index.html` – Friendly launcher page that embeds the Playground and falls back to a direct link.

> **Note:** The Playground runs entirely client-side. Refreshing the page resets the environment, so export any changes you want to keep.
