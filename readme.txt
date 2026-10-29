=== TSO Blog ===
Contributors: deadko
Tags: blog, two-columns, right-sidebar, custom-colors, custom-header, custom-background, custom-logo, custom-menu, editor-style, featured-images, footer-widgets, full-width-template, sticky-post, theme-options, threaded-comments, translation-ready, wide-blocks, block-styles
Requires at least: 6.1
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.10
License: GNU General Public License v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Classic blog theme with a two-column grid, live search, day and night mode, and page templates.

== Description ==

TSO Blog is a classic (PHP template) WordPress theme built for Tu Soporte Online sites. It provides a two-column home grid with “Load more”, single-post layout with breadcrumb and related posts, a built-in lightbox compatible with Jetpack galleries, AJAX live search, and Customizer panels for colors, typography, logo size, social icons, announcements, and optional integrations.

Requires WordPress 6.1 or newer and PHP 7.4–8.x.

Features:

* Home/blog layouts: 2-column grid, list, or magazine (featured + grid), with “Load more”
* Single post: breadcrumb, Schema.org markup, related posts
* Built-in lightbox (Jetpack carousel disabled when present)
* Compatible with Jetpack Tiled Gallery and Gutenberg galleries
* Live AJAX search
* Optional GTranslate header shortcode (off by default; requires the GTranslate plugin)
* Compatible with LiteSpeed Cache, WP Super Cache, and W3 Total Cache
* Customizer: colors, typography (system + optional Google Fonts), logo width, social icon order
* Announcement bars (up to five, with rotation)
* Page template “Portfolio / Wallpaper gallery”: image gallery with wallpaper-size downloads
* Page template “Contact”: address, phone, email, optional form shortcode, optional Google Maps embed
* 11 additional page templates, ready to use from Appearance → Add New Page → Template: Full width, Landing, Team, Services, Pricing, Testimonials, FAQ (accessible accordion), Resources/Downloads (from File blocks), Legal document (auto table of contents), Timeline, and Links (link-in-bio)
* Page template “About Me”: avatar, bio, and an optional contact card reusing the Contact template settings (email, phone, form shortcode, social icons)
* Starter block patterns for the templates above (team grid, service cards, pricing table, testimonials, timeline, links list)
* Customizer panels for Team, Pricing, and Landing (add/remove/reorder rows with photo, name, role, price, features, buttons…); each template falls back to its block pattern when no rows are configured

== Installation ==

1. In your WordPress admin, go to Appearance → Themes → Add New.
2. Click Upload Theme and select the theme ZIP file.
3. Activate the theme.
4. Go to Appearance → Customize to configure colors, typography, logo, blog layout, and social links.

== Frequently Asked Questions ==

= How do I change typography? =

Appearance → Customize → Typography. Choose body and heading fonts, sizes, line height, and colors. Fonts labeled “(Google)” load from Google Fonts only when selected.

= How do I change the logo size? =

Appearance → Customize → Site Identity. Upload the image with the WordPress Site Logo control, then set the desktop and mobile display widths in the same section. Height scales automatically. The theme does not add a second logo uploader.

= How do I make the site wider? =

Appearance → Customize → Layout / Width. Choose 960, 1060 (default), 1200, 1400, 1600 px, or “Nearly full screen”. On small screens the layout stays full-width of the device.

= How do I change the blog / homepage layout? =

Appearance → Customize → Layout / Width → Blog / home layout. Choose Grid (2 columns), List (image + text), or Magazine (one featured post + grid).

= How do I reorder social icons? =

Appearance → Customize → Social networks. Drag the menu handles to change left-to-right order. Add full URLs below; networks without a URL are hidden on the front end.

= How do footer widget columns work? =

Appearance → Widgets (or Customize → Widgets) has three footer areas (columns 1–3).

* Use only column 1 (leave 2 and 3 empty) for a horizontal link bar (Contact / Legal).
* Use two or three columns for a classic multi-column footer.

**Images in columns 2 and 3:** prepare both files at the **same size**, recommended **600×450 px (4:3)** or **600×600 px (square)**. If one image is portrait and the other landscape, they will not look paired. Each footer column is about 300 px wide on desktop; 600 px wide sources are enough and look sharp on retina screens.

= Why does the Customizer say there is 1 more widget area not shown on this page? =

That message is from WordPress core, not an error. The theme registers **News Sidebar**, which only appears on single posts, default pages, archives, and search. It does **not** appear on the blog posts index, or on pages using the **Contact** or **Portfolio / Wallpaper gallery** templates. While customizing, use the preview to open a post or a normal page to see and edit that sidebar. Footer columns 1–3 are in the site footer on every page.

= How do I create a wallpaper / portfolio gallery page? =

1. Pages → Add New.
2. In Page Attributes (or the Template panel in the block editor), choose **Portfolio / Wallpaper gallery**.
3. Add a Gallery or Image blocks (JPG, PNG, WebP, or GIF). Other blocks are limited on this template.
4. Publish. The front end shows a photo grid with the image name and download links for wallpaper sizes (1280×720, 1920×1080, 2560×1440, 3840×2160, 1080×1920 mobile) plus the original file.
5. New uploads get those sizes automatically. For images uploaded before activating this theme version, regenerate thumbnails (e.g. with a “Regenerate Thumbnails” plugin) so every download size appears.

= How do I create a contact page with Google Maps? =

1. Pages → Add New → set template **Contact**.
2. Optional: set a Featured Image for the hero background.
3. Customize → Templates → Contact: fill address, phone, email, intro text.
4. Google Maps: open the place → Share → Embed a map → paste the embed `src` URL (or the full embed code) into “Google Maps (embed URL)”. Leave empty to hide the map (no remote request).
5. Form: install a form plugin (e.g. Contact Form 7) and paste its shortcode under Customize → Templates → Contact. This theme does not send emails by itself (WordPress.org theme guidelines).

= How do I enable the GTranslate language switcher? =

1. Install and activate the GTranslate plugin.
2. In Appearance → Customize → Integrations, enable “Show GTranslate in the header”.
3. Save. If the plugin is inactive, the theme shows nothing (no raw shortcode).

= How should I configure LiteSpeed Cache (or similar) with this theme? =

The theme does not replace a cache plugin; it should remain compatible with it.

1. Do not cache logged-in users (“Do Not Cache Logged-in Users” / disable caching for logged-in users). Otherwise the posts page may serve a guest HTML copy without the admin bar.
2. Keep automatic purge on publish/update.
3. After updating the theme, Purge All (or purge affected URLs).
4. The theme versions assets with `filemtime`. If CSS/JS combine causes issues, exclude: `tso-live-search.js`, `tso-load-more.js`, `tso-lightbox.js`, `tso-widget-css-fix.js`, `tso-announcement.js`, `tso-contact-map.js`.
5. If a CDN sits in front of the cache, do not cache responses with the `wordpress_logged_in_*` cookie.

Quick check: while logged in, open the blog posts page, view source, and search for `wpadminbar`. If it is missing there but present on other pages, fix logged-in caching and purge.

= Recommended wp-config.php constants? =

Optional (not required by the theme). Add before “That’s all, stop editing!” in `wp-config.php` if you want them:

`define( 'WP_POST_REVISIONS', 5 );`
`define( 'DISALLOW_FILE_EDIT', true );`

Do not duplicate them if they already exist.

== External services ==

=== Google Fonts ===

This theme can load selected webfonts from Google Fonts when you choose a font marked “(Google)” under Appearance → Customize → Typography.

* What: CSS stylesheet and font files from Google Fonts.
* When: only if a Google font is selected for body and/or headings (default is system Arial; no remote request).
* Data sent: the visitor’s IP address and user-agent are sent to Google as with any remote stylesheet. The theme does not send account or form data.
* Terms of Service: https://developers.google.com/fonts/terms
* Privacy Policy: https://policies.google.com/privacy

=== Google Maps (embed) ===

The Contact page template can show an embedded Google Map when you paste a Maps embed URL under Customize → Templates → Contact.

* What: embedded map loading tiles and scripts from Google Maps.
* When: only if an embed URL is saved (default empty; no remote request).
* Data sent: visitor IP and user-agent to Google as with any embedded map. No form or account data is sent by the theme.
* Terms of Service: https://maps.google.com/help/terms_maps/
* Privacy Policy: https://policies.google.com/privacy

== Changelog ==

= 1.0.10 =
* Description rewritten to match the current theme. The readme summary stays within 150 characters.
* Tags checked against the WordPress.org list. All current tags are allowed. Only subject tags are limited to three; this theme uses one: blog.

= 1.0.9 =
* Theme review: the core Site Logo control stays in Site Identity. Logo width settings sit next to it. Removed the separate Logo section and the bundled logo-file fallback.
* Theme review: at most three Customizer textareas (footer legal text, contact intro, contact address). The Maps embed and repeater notes are single-line fields.
* Theme review: the pricing plans repeater accepts at most 5 plans.
* Theme review: screenshot.png is original theme artwork. License and source are listed under Resources.

= 1.0.8 =
* Escape color-mode root class in header.php (Theme Check: no echo get_theme_mod).

= 1.0.7 =
* Customizer: Sidebar (layout + colors), Typography consolidation (Body/Titles include cards and widgets; announcement and menu font family/size; day content text colors under Colors → General)
* Customizer: edit shortcuts (pencils) for announcement, social, footer texts, color mode, related title, Load more; do not clip widget shortcuts
* Footer: copyright/legal size under Footer → Footer texts; stronger containment for Custom HTML / CurrencyRate floats
* Fix: orange outer frame with body.wp-embed-responsive / LiteSpeed; related-posts title contrast; Contact path Templates → Contact
* Widgets admin: do not style bare .widget; skip night-mode scripts in legacy widget preview iframes

= 1.0.6 =
* Content images: raise $content_width to 1024 and improve sizes attribute so desktop/HiDPI can pick sharper srcset files (mobile unchanged)

= 1.0.5 =
* Theme Check: footer placeholder docs use {home_url} (value still from home_url()); legacy token still works

= 1.0.4 =
* Customizer i18n: related posts count label and eyedropper aria-label are translatable; announcement “Text” color label translated in Spanish
* Defaults for “Load more” and related title use English msgids (existing Customizer values unchanged)

= 1.0.3 =
* Announcement: slower movement; Customizer choice of 3 effects (slide, fade, rise)
* Fix: announcement bar class mismatch broke CSS/JS (no rotation, no highlight); restored slide-in from the side and rotation
* Layout: news sidebar width set to the standard 300 px (was 280); content width options keep a flexing main column
* Customizer: logo/content width help texts state theme maximums; mobile logo max raised to 500 px
* Fix: logo width Customizer control now sets display width (was max-width only, so values above the image size looked unchanged)
* Fix: header image from Customize is now shown under the logo row (it was registered but never output)
* Fix: menu highlight, skip link, mobile menu in widget previews, editor script scope; removed no-op footer help checkbox

= 1.0.2 =
* Theme review: removed Theme URI (Author URI only); removed redundant wp_lazy_loading_enabled filter
* Theme review: all PHP user-facing strings are now English msgids with CA/ES/EN catalogs
* Theme review: long titles wrap within the content area; skip-to-content no longer sits under the admin bar
* Page template names translated to English in the template headers
* Theme review (Required): removed Appearance → Import demo (demo imports are not allowed)
* Theme review (Required): removed theme Open Graph / meta description output (SEO plugin territory)

= 1.0.1 =
* Corrección: la búsqueda en directo ya no falla para los visitantes cuando la página está en caché
* Mejora: descripción y etiquetas del tema más completas, y la descripción se muestra traducida según el idioma de WordPress (catalán, español, inglés)
* Mejora: el modo noche tiene ahora colores propios para el color principal, el menú, el pie, el anuncio y los textos, ya que los colores del modo día no se aplican de noche
* Mejora: el tamaño de imagen de Artículos relacionados pasa a llamarse tsothm-related (hay que regenerar miniaturas para las imágenes ya subidas)
* Mejora: el formulario de comentarios ya no se muestra en una caja dentro de otra; la barra de título queda alineada como en Artículos relacionados
* Corrección: TSO Image Master ya no marca como no registrados los tamaños de fondos de pantalla del portfolio
* Mejora: en modo noche las plantillas de página (galería/portfolio, precios, contacto, landing, 404) usan colores de contraste correctos en botones y píldoras de descarga
* Mejora: en modo noche se aclaran los colores de texto en línea del contenido antiguo (p. ej. negro sobre fondo oscuro) para que sea legible
* Mejora: el formulario de comentarios de Jetpack se adapta al modo noche
* Mejora: en modo noche las barras de título y los botones tienen mejor contraste, y los enlaces del sidebar usan un azul más suave
* Mejora: en modo noche los widgets del pie (pestañas de comentarios y cotizaciones) dejan de mostrarse con fondo blanco
* Mejora: los recortes de fondos de pantalla (tsothm-wall-*) solo se generan para imágenes de páginas de galería/portfolio, no para todas las subidas
* Nuevo: sección «Modo noche» en Personalizar > Colores (fondo exterior, contenido, cabecera, menú, pie, acento y enlaces); la cabecera clara ahora usa por defecto el color del contenido (#17181c) en vez de negro
* Corrección: en el selector día/noche, el botón activo y el icono en hover ya no se vuelven invisibles (usan el color de acento)
* Corrección: en modo noche el texto y los campos de los widgets del sidebar (p. ej. suscripción por correo) ya se leen bien
* Nuevo: paneles del Personalizador para Equipo, Precios y Landing (repetidor con fotos, precios, características y botones; sin configurar, la página muestra el patrón de bloques)
* Nuevo: 11 plantillas de página adicionales (Ancho completo, Landing, Equipo, Servicios, Precios, Testimonios, FAQ, Recursos/Descargas, Legal con índice automático, Cronología y Enlaces) y patrones de bloques de arranque para cada una
* Nuevo: plantilla “Sobre mí” con tarjeta de contacto opcional (reutiliza los ajustes de la plantilla Contacto)
* Mejora: el filtro de enlaces de Jetpack Tiled Gallery ahora tolera clases adicionales en el bloque y añade data-orig-file como alternativa antes de caer al src recortado
* Mejora: metadatos Twitter Card (twitter:card, twitter:title, twitter:description, twitter:image) junto al Open Graph existente
* Mejora de accesibilidad: el lightbox (tso-lightbox.js) devuelve el foco al elemento que lo abrió al cerrarse
* Nuevo: menú móvil moderno con 4 versiones (botón flotante con panel inferior, pantalla completa, barra de pestañas inferior o panel lateral) elegible en Personalizar > Apariencia > Menú móvil

= 1.0 =
* Requires WordPress 6.1+
* theme.json schema v2 (compatible with WP 6.1)
* Initial release of TSO Blog

== Copyright ==

TSO Blog WordPress Theme, Copyright (C) 2024-2026 Tu Soporte Online / deadko
TSO Blog is distributed under the terms of the GNU General Public License v2 or later.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

== Resources ==

Bundled scripts and styles are original to this theme unless noted below, Copyright Tu Soporte Online, GNU GPL v2 or later.

* normalize.css, https://necolas.github.io/normalize.css/, (C) Nicolas Gallagher and Jonathan Neal, MIT
* Dashicons (Customizer social order handle), Copyright WordPress contributors, GNU GPL v2 or later
* Google Fonts (optional remote), Copyright respective font authors, SIL Open Font License / Apache 2.0 as declared by each family on fonts.google.com

* Screenshot (screenshot.png)
  Copyright: 2026 Tu Soporte Online
  License: GNU General Public License v2 or later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Source: original artwork created for this theme (orange frame, header, menu and post cards with solid-color placeholders). It does not include third-party photographs, illustrations or logos, so there is no external image URL.
