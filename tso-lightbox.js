/**
 * tso-lightbox.js — Tu Soporte Online
 * Compatible: Chrome, Firefox, Edge, Safari (iOS/macOS), Android
 * - Imatges soltes: botó × gran i visible
 * - Galeries: fletxes grans + swipe mòbil + teclat
 * - No interfereix amb Jetpack (carousel desactivat des de functions.php)
 */
(function () {
    'use strict';

    /* ── Estils inline (no depèn de cap CSS extern) ─────────── */
    var css = [
        '#tso-lb-overlay{display:none;position:fixed;inset:0;z-index:99999;',
        'background:rgba(0,0,0,0.93);align-items:center;justify-content:center;}',
        '#tso-lb-overlay.is-open{display:flex;}',
        '#tso-lb-img{max-width:90vw;max-height:88vh;object-fit:contain;',
        'border-radius:3px;box-shadow:0 0 40px rgba(0,0,0,.7);',
        'user-select:none;display:block;transition:opacity .15s;}',
        'img.tso-lb-img-trigger{cursor:zoom-in;}',
        /* Botó tancar */
        '#tso-lb-close{position:fixed;top:14px;right:14px;width:58px;height:58px;',
        'background:rgba(20,20,20,.8);border:2.5px solid rgba(255,255,255,.9);',
        'border-radius:50%;cursor:pointer;color:#fff;font-size:26px;line-height:1;',
        'display:flex;align-items:center;justify-content:center;',
        'transition:background .2s;z-index:100001;',
        'box-shadow:0 2px 12px rgba(0,0,0,.6);}',
        '#tso-lb-close:hover{background:rgba(255,255,255,.25);}',
        /* Fletxes */
        '#tso-lb-prev,#tso-lb-next{position:fixed;top:50%;',
        'transform:translateY(-50%);width:58px;height:58px;',
        'background:rgba(20,20,20,.8);border:2.5px solid rgba(255,255,255,.9);',
        'border-radius:50%;cursor:pointer;color:#fff;font-size:30px;line-height:1;',
        'display:flex;align-items:center;justify-content:center;',
        'transition:background .2s;z-index:100001;',
        'box-shadow:0 2px 12px rgba(0,0,0,.6);}',
        '#tso-lb-prev{left:14px;}#tso-lb-next{right:14px;}',
        '#tso-lb-prev:hover,#tso-lb-next:hover{background:rgba(255,255,255,.25);}',
        '#tso-lb-prev.hidden,#tso-lb-next.hidden{display:none!important;}',
        /* Comptador */
        '#tso-lb-counter{position:fixed;bottom:18px;left:50%;',
        'transform:translateX(-50%);color:#fff;font-size:14px;',
        'background:rgba(0,0,0,.55);padding:4px 16px;border-radius:20px;',
        'pointer-events:none;white-space:nowrap;}',
        '#tso-lb-counter:empty{display:none;}',
        /* Mòbil */
        '@media(max-width:600px){',
        '#tso-lb-close{width:36px;height:36px;font-size:16px;top:8px;right:8px;',
        'background:rgba(0,0,0,.5);border-width:1.5px;}',
        '#tso-lb-prev,#tso-lb-next{width:34px;height:34px;font-size:16px;',
        'background:rgba(0,0,0,.4);border-width:1.5px;opacity:.75;}',
        '#tso-lb-prev{left:4px;}#tso-lb-next{right:4px;}',
        '#tso-lb-img{max-width:100vw;max-height:82vh;}}'
    ].join('');
    var styleEl = document.createElement('style');
    styleEl.textContent = css;
    document.head.appendChild(styleEl);

    /* ── Crear DOM de l'overlay ─────────────────────────────── */
    var overlay  = document.createElement('div');
    overlay.id   = 'tso-lb-overlay';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-label', 'Visor de imágenes');

    var imgEl    = document.createElement('img');
    imgEl.id     = 'tso-lb-img';
    imgEl.alt    = '';

    var btnClose = document.createElement('button');
    btnClose.id  = 'tso-lb-close';
    btnClose.setAttribute('aria-label', 'Cerrar');
    btnClose.innerHTML = '&#x2715;';

    var btnPrev  = document.createElement('button');
    btnPrev.id   = 'tso-lb-prev';
    btnPrev.setAttribute('aria-label', 'Imagen anterior');
    btnPrev.innerHTML = '&#10094;';

    var btnNext  = document.createElement('button');
    btnNext.id   = 'tso-lb-next';
    btnNext.setAttribute('aria-label', 'Imagen siguiente');
    btnNext.innerHTML = '&#10095;';

    var counter  = document.createElement('div');
    counter.id   = 'tso-lb-counter';

    overlay.appendChild(btnClose);
    overlay.appendChild(btnPrev);
    overlay.appendChild(imgEl);
    overlay.appendChild(btnNext);
    overlay.appendChild(counter);

    /* ── Estat ──────────────────────────────────────────────── */
    var images      = [];
    var current     = 0;
    var lastFocused = null;

    /* ── Obrir ──────────────────────────────────────────────── */
    function open(list, index, trigger) {
        images      = list;
        current     = index;
        lastFocused = trigger || document.activeElement;
        render();
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        btnClose.focus();
    }

    /* ── Tancar ─────────────────────────────────────────────── */
    function close() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        setTimeout(function () { imgEl.src = ''; }, 200);
        // Retornar el focus a l'element que ha obert el lightbox
        // (accessibilitat: teclat / lectors de pantalla).
        if (lastFocused && document.contains(lastFocused) && typeof lastFocused.focus === 'function') {
            lastFocused.focus();
        }
        lastFocused = null;
    }

    /* ── Navegar ────────────────────────────────────────────── */
    function goTo(idx) {
        current = (idx + images.length) % images.length;
        render();
    }

    /* ── Renderitzar imatge ─────────────────────────────────── */
    function render() {
        imgEl.style.opacity = '0';
        var entry = images[current];
        imgEl.alt = entry.alt || '';
        var tmp   = new Image();
        tmp.onload = tmp.onerror = function () {
            imgEl.src            = entry.src;
            imgEl.style.opacity  = '1';
        };
        tmp.src = entry.src;

        var multi = images.length > 1;
        btnPrev.classList.toggle('hidden', !multi);
        btnNext.classList.toggle('hidden', !multi);
        counter.textContent = multi ? (current + 1) + ' / ' + images.length : '';
    }

    /* ── Events botons ──────────────────────────────────────── */
    btnClose.addEventListener('click', close);
    btnPrev.addEventListener('click', function (e) { e.stopPropagation(); goTo(current - 1); });
    btnNext.addEventListener('click', function (e) { e.stopPropagation(); goTo(current + 1); });
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) close();
    });

    /* ── Teclat ─────────────────────────────────────────────── */
    document.addEventListener('keydown', function (e) {
        if (!overlay.classList.contains('is-open')) return;
        if (e.key === 'Escape')     close();
        if (e.key === 'ArrowLeft')  goTo(current - 1);
        if (e.key === 'ArrowRight') goTo(current + 1);
    });

    /* ── Swipe i tap mòbil ──────────────────────────────────── */
    var tx = 0, ty = 0, tTarget = null;
    overlay.addEventListener('touchstart', function (e) {
        tx = e.changedTouches[0].clientX;
        ty = e.changedTouches[0].clientY;
        tTarget = e.target;
    }, { passive: true });
    overlay.addEventListener('touchend', function (e) {
        var dx = e.changedTouches[0].clientX - tx;
        var dy = e.changedTouches[0].clientY - ty;
        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 40) {
            // Swipe horitzontal — navegar
            dx < 0 ? goTo(current + 1) : goTo(current - 1);
        } else if (Math.abs(dx) < 15 && Math.abs(dy) < 15) {
            // Tap — tancar només si ha tocat el marge negre (overlay)
            // i NO la imatge ni cap botó
            if (tTarget === overlay) {
                close();
            }
        }
    }, { passive: true });

    /* ── Detectar si una URL és una imatge ──────────────────── */
    function isImg(url) {
        return /\.(jpe?g|png|gif|webp|avif|svg)(\?|$)/i.test(url) ||
               url.indexOf('/wp-content/uploads/') !== -1;
    }

    /* ── Obtenir la URL real de la imatge des d'un <a> ───────────
     * Gutenberg pot generar hrefs que apunten a pàgines d'adjunt
     * (ex: /?attachment_id=123 o /nom-imatge/) en lloc de la URL
     * directa de la imatge. Resolem la URL per ordre de prioritat:
     * 1. href si ja és una URL directa d'imatge
     * 2. data-full-url (Jetpack Tiled Gallery)
     * 3. data-orig-file (Jetpack)
     * 4. data-large-file (WordPress core)
     * 5. src de la imatge filla (sempre disponible)
     * Això fa que el lightbox funcioni independentment de si el
     * filtre PHP ha canviat l'href o no.
     * ─────────────────────────────────────────────────────────── */
    function resolveImgUrl(a) {
        var href = a.href || '';

        // 1. L'href ja és una imatge directa
        if ( isImg(href) ) return href;

        // 2-5. Fallback via atributs de la imatge filla
        var img = a.querySelector('img');
        if ( img ) {
            return img.getAttribute('data-full-url')   ||
                   img.getAttribute('data-orig-file')  ||
                   img.getAttribute('data-large-file') ||
                   img.src                             ||
                   '';
        }
        return '';
    }

    /* ── Construir llista d'imatges d'un grup de links ───────── */
    function toList(links) {
        return links.map(function (a) {
            var img = a.querySelector('img');
            return {
                src: resolveImgUrl(a),
                alt: img ? (img.alt || '') : ''
            };
        }).filter(function(e) { return !!e.src; });
    }

    /* ── URL d'una imatge sense enllaç pare ─────────────────── */
    function resolveImgSrc(img) {
        if (!img) return '';

        var srcset = img.getAttribute('srcset');
        if (srcset) {
            var best = '', bestW = 0;
            srcset.split(',').forEach(function (part) {
                var bits = part.trim().split(/\s+/);
                if (!bits[0]) return;
                var w = parseInt(bits[1], 10) || 0;
                if (w >= bestW) {
                    bestW = w;
                    best = bits[0];
                }
            });
            if (best) return best;
        }

        return img.getAttribute('data-full-url')   ||
               img.getAttribute('data-orig-file')  ||
               img.getAttribute('data-large-file') ||
               img.currentSrc                      ||
               img.src                             ||
               '';
    }

    /* ── Imatges soltes sense <a> (editor clàssic, etc.) ─────── */
    function bindStandaloneImages(content, galSelector) {
        var imgs = Array.prototype.slice.call(content.querySelectorAll('img'));

        imgs.forEach(function (img) {
            if (img.closest('a')) return;
            if (img.closest(galSelector)) return;

            var src = resolveImgSrc(img);
            if (!src || !isImg(src)) return;

            img.classList.add('tso-lb-img-trigger');
            img.addEventListener('click', function (e) {
                e.preventDefault();
                open([{ src: src, alt: img.alt || '' }], 0, img);
            });
        });
    }

    /* ── Init: recórrer el contingut i assignar events ────────── */
    function isLightboxCandidate(a) {
        if (!a || !a.getAttribute('href')) return false;
        // Descàrregues / enllaços explícits fora del lightbox
        if (a.hasAttribute('download')) return false;
        if (a.classList.contains('tso-portfolio-download')) return false;
        if (a.classList.contains('tso-portfolio-download--go')) return false;
        if (a.closest('.tso-portfolio-downloads')) return false;
        // Només galeries, thumb portfolio o data-tso-lightbox / href d'imatge
        if (a.getAttribute('data-tso-lightbox')) return true;
        if (a.classList.contains('tso-portfolio-thumb')) return true;
        if (a.closest('.wp-block-gallery, .blocks-gallery-grid, .wp-block-jetpack-tiled-gallery, .tiled-gallery, .gallery')) {
            return isImg(a.href) || !!a.querySelector('img');
        }
        // Enllaç solt a una imatge (no CTA amb img + href a pàgina)
        return isImg(a.href);
    }

    function init() {
        document.body.appendChild(overlay);

        var roots = Array.prototype.slice.call(
            document.querySelectorAll('.entry-content, .tso-portfolio-gallery')
        );
        if (!roots.length) return;

        var GAL = [
            '.wp-block-gallery',
            '.blocks-gallery-grid',
            '.wp-block-jetpack-tiled-gallery',
            '.tiled-gallery',
            '.gallery',
            '.tso-portfolio-gallery'
        ].join(',');

        roots.forEach(function(content) {
            var allLinks = Array.prototype.slice.call(
                content.querySelectorAll('a[href]')
            ).filter(isLightboxCandidate);

            var processed = [];

            allLinks.forEach(function(link) {
                if (processed.indexOf(link) !== -1) return;
                if (!resolveImgUrl(link)) return;

                var galContainer = link.closest(GAL);
                var group;

                if (galContainer) {
                    group = Array.prototype.slice.call(
                        galContainer.querySelectorAll('a[href]')
                    ).filter(function(a) {
                        return isLightboxCandidate(a) && resolveImgUrl(a);
                    });
                } else {
                    group = [link];
                }

                group.forEach(function(a) {
                    if (processed.indexOf(a) === -1) processed.push(a);
                });

                var list = toList(group);
                if (!list.length) return;

                group.forEach(function(a) {
                    var srcA = resolveImgUrl(a);
                    var idx  = 0;
                    for (var j = 0; j < list.length; j++) {
                        if (list[j].src === srcA) { idx = j; break; }
                    }
                    a.addEventListener('click', function(e) {
                        e.preventDefault();
                        open(list, idx, a);
                    });
                });
            });

            bindStandaloneImages(content, GAL);
        });
    }

    // Executar quan el DOM estigui llest
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

}());
