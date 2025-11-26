jQuery(document).ready(function($) {
    'use strict';

    /**
     * OKLCH-baseret farvepalette-generator.
     * Denne IIFE (Immediately Invoked Function Expression) indeholder al den
     * matematiske logik for at konvertere og generere farver.
     */
    const PaletteGenerator = (() => {
        // OKLCH farvekonverterings-matematik (uændret)
        const M1 = [[0.4122214708, 0.5363325363, 0.0514459929], [0.2119034982, 0.6806995451, 0.1073969566], [0.0883024619, 0.2817188376, 0.6299787005]];
        const M2 = [[0.2104542553, 0.7936177850, -0.0040720468], [1.9779984951, -2.4285922050, 0.4505937099], [0.0259040371, 0.7827717662, -0.8086757660]];
        const M2_INV = [[1.0, 0.3963377774, 0.2158037573], [1.0, -0.1055613458, -0.0638541728], [1.0, -0.0894841775, -1.2914855480]];
        function hexToSrgb(hex) { const r = parseInt(hex.slice(1, 3), 16) / 255, g = parseInt(hex.slice(3, 5), 16) / 255, b = parseInt(hex.slice(5, 7), 16) / 255; return { r, g, b }; }
        function srgbToHex(srgb) { const r = Math.round(Math.max(0, Math.min(1, srgb.r)) * 255), g = Math.round(Math.max(0, Math.min(1, srgb.g)) * 255), b = Math.round(Math.max(0, Math.min(1, srgb.b)) * 255); return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`.toUpperCase(); }
        function _gammaDecode(c) { return c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4); }
        function _gammaEncode(c) { return c <= 0.0031308 ? c * 12.92 : 1.055 * Math.pow(c, 1 / 2.4) - 0.055; }
        function srgbToLinearSrgb(srgb) { return { r: _gammaDecode(srgb.r), g: _gammaDecode(srgb.g), b: _gammaDecode(srgb.b) }; }
        function linearSrgbToSrgb(linearSrgb) { return { r: _gammaEncode(linearSrgb.r), g: _gammaEncode(linearSrgb.g), b: _gammaEncode(linearSrgb.b) }; }
        function linearSrgbToOklab(c) { const l = M1[0][0] * c.r + M1[0][1] * c.g + M1[0][2] * c.b, m = M1[1][0] * c.r + M1[1][1] * c.g + M1[1][2] * c.b, s = M1[2][0] * c.r + M1[2][1] * c.g + M1[2][2] * c.b; const l_ = Math.cbrt(l), m_ = Math.cbrt(m), s_ = Math.cbrt(s); return { L: M2[0][0] * l_ + M2[0][1] * m_ + M2[0][2] * s_, a: M2[1][0] * l_ + M2[1][1] * m_ + M2[1][2] * s_, b: M2[2][0] * l_ + M2[2][1] * m_ + M2[2][2] * s_ }; }
        function oklabToLinearSrgb(c) { const l_ = M2_INV[0][0] * c.L + M2_INV[0][1] * c.a + M2_INV[0][2] * c.b, m_ = M2_INV[1][0] * c.L + M2_INV[1][1] * c.a + M2_INV[1][2] * c.b, s_ = M2_INV[2][0] * c.L + M2_INV[2][1] * c.a + M2_INV[2][2] * c.b; const l = l_ * l_ * l_, m = m_ * m_ * m_, s = s_ * s_ * s_; return { r: 4.0767416621 * l - 3.3077115913 * m + 0.2309699292 * s, g: -1.2684380046 * l + 2.6097574011 * m - 0.3413193965 * s, b: -0.0041960863 * l - 0.7034186147 * m + 1.7076147010 * s }; }
        function oklabToOklch(c) { const C = Math.sqrt(c.a * c.a + c.b * c.b); let H = Math.atan2(c.b, c.a) * 180 / Math.PI; if (H < 0) H += 360; return { L: c.L, C: C, H: H }; }
        function oklchToOklab(c) { const rad = c.H * Math.PI / 180; return { L: c.L, a: c.C * Math.cos(rad), b: c.C * Math.sin(rad) }; }
        function hexToOklch(hex) { return oklabToOklch(linearSrgbToOklab(srgbToLinearSrgb(hexToSrgb(hex)))); }
        function oklchToHex(oklch) { return srgbToHex(linearSrgbToSrgb(oklabToLinearSrgb(oklchToOklab(oklch)))); }
        function isInGamut(oklch) { const srgb = linearSrgbToSrgb(oklabToLinearSrgb(oklchToOklab(oklch))); return srgb.r >= -0.0001 && srgb.r <= 1.0001 && srgb.g >= -0.0001 && srgb.g <= 1.0001 && srgb.b >= -0.0001 && srgb.b <= 1.0001; }
        function ensureGamut(oklch) { let correctedOklch = { ...oklch }; if (isInGamut(correctedOklch)) return correctedOklch; let C = correctedOklch.C; while (C > 0) { C -= 0.001; correctedOklch.C = C; if (isInGamut(correctedOklch)) return correctedOklch; } correctedOklch.C = 0; return correctedOklch; }
        function generate(baseHex, mode = 'monochromatic') { const accentOklch = hexToOklch(baseHex); const { H: Ha } = accentOklch; let paletteOklch = {}; let baseHue = Ha, contrastHue = Ha; if (mode === 'analogous') { baseHue = (Ha + 30) % 360; contrastHue = baseHue; } else if (mode === 'complementary') { contrastHue = (Ha + 180) % 360; } paletteOklch.accent = accentOklch; paletteOklch.base = { L: 0.98, C: 0.01, H: baseHue }; paletteOklch.base2 = { L: 0.95, C: 0.015, H: baseHue }; paletteOklch.base3 = { L: 0.92, C: 0.02, H: baseHue }; paletteOklch.contrast = { L: 0.25, C: 0.04, H: contrastHue }; paletteOklch.contrast2 = { L: 0.35, C: 0.03, H: contrastHue }; paletteOklch.contrast3 = { L: 0.45, C: 0.02, H: contrastHue }; const finalPalette = {}; for (const key in paletteOklch) { finalPalette[key] = oklchToHex(ensureGamut(paletteOklch[key])); } return finalPalette; }
        return { generate };
    })();

    // Cache jQuery-objekter for bedre ydeevne
    const $accentInput = $('#faktaform-accent');
    const $accentHexInput = $('#faktaform-accent-hex');
    const $customSwatch = $('#faktaform-custom-swatch');
    const $resultsWrapper = $('#faktaform-results-wrapper');
    const $paletteContainer = $resultsWrapper.find('#faktaform-palette-container');

    // Initialiser hoved-farvevælger
    $accentInput.wpColorPicker({
        change: (event, ui) => {
            const hexColor = ui.color.toString();
            $customSwatch.css('background-color', hexColor);
            $accentHexInput.val(hexColor);
        }
    });

    // Synkroniser UI-elementer for hoved-farvevælgeren
    $customSwatch.on('click', () => $accentInput.iris('show'));
    $accentHexInput.on('change', () => $accentInput.wpColorPicker('color', $accentHexInput.val()));
    
    const initialColor = $accentInput.val();
    $customSwatch.css('background-color', initialColor);
    $accentHexInput.val(initialColor);

    // Håndter Eyedropper API, hvis browseren understøtter det
    const $eyedropperBtn = $('#faktaform-eyedropper-btn');
    if ('EyeDropper' in window) {
        $eyedropperBtn.on('click', async () => {
            try {
                const eyeDropper = new EyeDropper();
                const result = await eyeDropper.open();
                $accentInput.wpColorPicker('color', result.sRGBHex);
            } catch (e) {
                // Brugeren annullerede handlingen
            }
        });
    } else {
        $eyedropperBtn.hide();
    }

    // Håndter "Generér"-knap
    $('#faktaform-generate-btn').on('click', (event) => {
        event.preventDefault();
        generateAndDisplayPalette();
        $resultsWrapper.show();
    });

    function generateAndDisplayPalette() {
        const baseHex = $accentInput.val();
        const mode = $('input[name="palette-type"]:checked').val();
        
        if (!/^#[0-9A-F]{6}$/i.test(baseHex)) {
            $paletteContainer.html('<p>Indtast venligst en gyldig 6-cifret HEX farvekode (f.eks. #1A2B3C).</p>');
            return;
        }

        const newPalette = PaletteGenerator.generate(baseHex, mode);
        displayPalette(newPalette);
    }

    function displayPalette(palette) {
        const paletteMap = {
            Accent: [{ name: "Accent", hex: palette.accent }],
            Base: [
                { name: "Base", hex: palette.base },
                { name: "Base 2", hex: palette.base2 },
                { name: "Base 3", hex: palette.base3 }
            ],
            Contrast: [
                { name: "Contrast", hex: palette.contrast },
                { name: "Contrast 2", hex: palette.contrast2 },
                { name: "Contrast 3", hex: palette.contrast3 }
            ]
        };

        const html = Object.entries(paletteMap).map(([title, colors]) => createFamilyHtml(title, colors)).join('');
        $paletteContainer.html(html);
    }

    function createFamilyHtml(title, colors) {
        const colorsHtml = colors.map(color => `
            <div class="faktaform-color-block">
                <button class="faktaform-edit-color-btn dashicons dashicons-edit" title="Redigér farve"></button>
                <div class="color-swatch" style="background-color: ${color.hex};"></div>
                <div>
                    <div class="color-name">${color.name}</div>
                    <div class="color-hex">${color.hex}</div>
                </div>
            </div>
        `).join('');

        return `<div class="faktaform-palette-family"><h3>${title}</h3><div class="faktaform-palette">${colorsHtml}</div></div>`;
    }

    // Håndter redigering af de 7 individuelle farvefelter
    $resultsWrapper.on('click', '.faktaform-edit-color-btn', function(event) {
        event.preventDefault();
        event.stopPropagation();
        const $colorBlock = $(this).closest('.faktaform-color-block');

        if ($colorBlock.hasClass('is-editing')) return;

        // Luk eventuelle andre åbne farvevælgere
        $('.faktaform-color-block.is-editing').removeClass('is-editing').find('.temp-color-picker-container').remove();

        $colorBlock.addClass('is-editing');
        const initialColor = $colorBlock.find('.color-hex').text();
        
        const $tempInput = $(`<input type="text" value="${initialColor}" />`).appendTo($colorBlock.find('.color-swatch'));

        $tempInput.wpColorPicker({
            change: (e, ui) => {
                const newColor = ui.color.toString();
                $colorBlock.find('.color-swatch').css('background-color', newColor);
                $colorBlock.find('.color-hex').text(newColor);
            }
        });

        // Brug 'iris:show' eventet til at positionere farvevælgeren,
        // da det sikrer, at elementet er klar i DOM.
        $tempInput.on('iris:show', () => {
            const $pickerHolder = $('body > .wp-picker-holder').last();
            if (!$pickerHolder.length) return;

            const blockRect = $colorBlock[0].getBoundingClientRect();
            const pickerHeight = $pickerHolder.outerHeight();
            const spaceBelow = window.innerHeight - blockRect.bottom;
            
            let pickerTop = (spaceBelow >= pickerHeight + 10) 
                ? blockRect.bottom + 5 
                : blockRect.top - pickerHeight - 5;

            $pickerHolder.css({
                position: 'fixed',
                top: `${pickerTop}px`,
                left: `${blockRect.left}px`,
                zIndex: 9999
            });
        });

        $tempInput.iris('show');
    });

    // Global 'mousedown' handler til at lukke åbne farvevælgere
    $(document).on('mousedown', (event) => {
        const $target = $(event.target);

        // Ignorer klik, hvis det er på en trigger-knap eller inde i en farvevælger
        if ($target.closest('#faktaform-custom-swatch, .faktaform-edit-color-btn, .wp-picker-holder').length) {
            return;
        }

        // Luk hoved-farvevælgeren
        if ($accentInput.iris('instance')) {
            $accentInput.iris('hide');
        }

        // Luk en eventuel åben palette-farvevælger
        $('.faktaform-color-block.is-editing').removeClass('is-editing').find('.temp-color-picker-container').remove();
    });

    // Håndter "Gem til GeneratePress"
    $('#faktaform-save-to-gp').on('click', function() {
        const $button = $(this);
        const $spinner = $button.next('.spinner');

        $button.prop('disabled', true);
        $spinner.css('visibility', 'visible');

        const colors = [];
        $resultsWrapper.find('.faktaform-color-block').each(function() {
            const $block = $(this);
            colors.push({
                name: $block.find('.color-name').text().trim(),
                slug: $block.find('.color-name').text().trim().toLowerCase().replace(/\s+/g, '-'),
                color: $block.find('.color-hex').text().trim()
            });
        });

        $.post(faktaform_ajax.ajax_url, {
            action: 'faktaform_save_generatepress_colors',
            nonce: faktaform_ajax.nonce,
            colors: JSON.stringify(colors)
        })
        .done((response) => {
            if (response.success) {
                $button.text('Gemt!');
                setTimeout(() => $button.text('Gem farver til GeneratePress'), 2000);
            } else {
                alert('Fejl: ' + (response.data || 'Ukendt fejl.'));
                $button.text('Fejl - Prøv igen');
            }
        })
        .fail(() => {
            alert('Der skete en serverfejl. Kunne ikke gemme farverne.');
            $button.text('Fejl - Prøv igen');
        })
        .always(() => {
            $button.prop('disabled', false);
            $spinner.css('visibility', 'hidden');
        });
    });
});