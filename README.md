# Smart Footnotes

Smart Footnotes adds lightweight, accessible inline footnotes to WordPress. Footnotes appear as numbered markers that open a popover containing the note and, optionally, a source link.

It works with the WordPress block editor and keeps the legacy `[sfn]` shortcode available for existing content.

## Features

- Add footnotes directly from the block editor’s rich-text toolbar.
- Include footnote text, an optional source URL, or a source URL by itself.
- Show footnotes in a compact popover without sending readers away from the page.
- Use keyboard-friendly buttons with accessible labels and Escape-to-close behavior.
- Load the same styling in the editor and on the front end.
- Receive one-click plugin updates from this GitHub repository.

## Installation

1. Download the plugin as a ZIP file from the [GitHub repository](https://github.com/daniellinski/wordpress-smart-footnotes).
2. In WordPress, go to **Plugins → Add New → Upload Plugin**.
3. Upload the ZIP file and activate **Smart Footnotes**.

You can also install it manually by copying the plugin directory to `wp-content/plugins/` and activating it from the WordPress Plugins screen.

## Usage

### Block editor

1. Open a post or page in the WordPress block editor.
2. Place the cursor where the footnote marker should appear.
3. Select **Smart footnotes** from the rich-text toolbar.
4. Enter the footnote text and, optionally, a source URL.
5. Select **Add footnote**.

To edit an existing footnote, select its marker and update the fields in the dialog.

### Legacy shortcode

The `sfn` shortcode is supported for existing content and can also be used manually:

```text
[sfn text="This is the footnote text."]
```

Add a source URL with the `url` attribute:

```text
[sfn text="This is the footnote text." url="https://example.com/source"]
```

For a link-only footnote, omit the `text` attribute:

```text
[sfn url="https://example.com/source"]
```

Footnote text accepts the HTML allowed by WordPress post content. URLs are sanitized before they are rendered.

## Updates

Smart Footnotes uses [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) to check this GitHub repository for new versions. Updates are delivered through the normal WordPress plugin update screen.

To publish an update:

1. Change the version in the plugin header and `SMART_FOOTNOTES_VERSION` in `smart-footnotes.php`.
2. Commit the changes to the `main` branch, or publish a GitHub release/tag.
3. Package the plugin with the complete `wordpress-smart-footnotes` directory, including `plugin-update-checker/`.

## Development

The plugin has no build step. Its main components are:

- `smart-footnotes.php` — plugin bootstrap, rendering, shortcode, and update checker setup.
- `assets/smart-footnotes-editor.js` — block editor rich-text integration.
- `assets/smart-footnotes.js` — front-end popover interaction.
- `assets/smart-footnotes.css` — front-end styling.

Before submitting changes, validate the PHP files with:

```bash
find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l
```

## License

Smart Footnotes is licensed under the GNU General Public License v3.0 or later. See [LICENSE](LICENSE).

The bundled Plugin Update Checker library is separately licensed under the MIT license. See [plugin-update-checker/license.txt](plugin-update-checker/license.txt).
