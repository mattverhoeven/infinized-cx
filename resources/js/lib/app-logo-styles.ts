/**
 * The Infinized logo contract: one place that decides how big the logo is, how
 * much room the mark gets inside a badge, and which tokens colour it. Before
 * this existed every call site invented its own box, radius and gap, which is
 * why the logo sat differently on almost every screen.
 *
 * Everything is sized by **height**, never by a square box. All three official
 * assets are wide — the mark is 374×234 (1.6:1), the wordmark 969×215 (4.5:1)
 * and the lock-up 1383×215 (6.4:1) — so forcing them into `aspect-square`
 * either squashed the geometry or left dead space around it. Width follows from
 * the viewBox.
 *
 * | size | logo height | badge | radius      |
 * | ---- | ----------- | ----- | ----------- |
 * | sm   | 12px        | 28px  | rounded-md  |
 * | md   | 16px        | 32px  | rounded-md  |
 * | lg   | 20px        | 36px  | rounded-lg  |
 * | xl   | 28px        | 44px  | rounded-xl  |
 * | 2xl  | 40px        | 60px  | rounded-2xl |
 * | 3xl  | 56px        | 80px  | rounded-3xl |
 *
 * There is no gap in the ladder because the lock-up is one SVG: the distance
 * between the mark and the word is `translate(71.087 0)` inside the asset, and
 * a flex gap next to it is a guess at that number that drifts as the logo
 * scales.
 *
 * The ladder used to stop at `xl`, which is 28px — chrome sizes only. Anything
 * that wanted the logo as a *subject* rather than as furniture (a title slide
 * on a projector, the brand canvas behind the auth screens) had to reach past
 * the API for the bare mark and hand it a height, because the lock-up wrote its
 * height onto its own children where no `className` from outside could reach
 * it. The two extra rungs and `AppLogo`'s `height` prop are the way to ask for
 * that now.
 */

/**
 * The official brand inks, exactly as they appear in `public/brand/*.svg`.
 * These are deliberately raw hex values rather than theme tokens: they are the
 * identity itself, not a themeable surface. The lower arc sits at roughly
 * `oklch(0.50 0.20 272)`, a hair from the token primary `oklch(0.55 0.20 277)`,
 * which is why the duotone reads as native to the palette on a neutral surface.
 */
export const BRAND_MARK_LOWER = '#4F49DF';
export const BRAND_MARK_UPPER = '#6D74F5';
/** The wordmark ink in `logo_color.svg`, `logo_dark.svg` and `icon_dark.svg`. */
export const BRAND_INK = '#0B0B14';
/** The single ink in `logo_white.svg` and `icon_white.svg`. */
export const BRAND_WHITE = '#FFFFFF';

export type AppLogoSize = 'sm' | 'md' | 'lg' | 'xl' | '2xl' | '3xl';

/**
 * Which official file the logo is drawn from.
 *
 * The brand ships three inkings of both the lock-up and the mark, and the tone
 * selects between them rather than inventing a colour treatment:
 *
 * | tone      | lock-up            | mark               |
 * | --------- | ------------------ | ------------------ |
 * | `duotone` | `logo_color.svg`   | `icon_color.svg`   |
 * | `color`   | `logo_color.svg`   | `icon_color.svg`   |
 * | `white`   | `logo_white.svg`   | `icon_white.svg`   |
 * | `dark`    | `logo_dark.svg`    | `icon_dark.svg`    |
 * | `mono`    | — see below        | — see below        |
 *
 * `duotone` is the default and the only tone that changes file with the theme:
 * the coloured wordmark is `#0B0B14`, which is invisible on a dark surface, so
 * a dark theme gets the white file instead. `color` pins the coloured file for
 * callers who know their surface is light whatever the theme does.
 *
 * `mono` is the **documented exception**: it collapses everything onto
 * `currentColor`, which is not an official file. It exists for the two places
 * no shipped variant covers — the mark on a filled brand chip, where it has to
 * take the chip's own paired foreground token, and an always-dark surface
 * inside a light-themed app.
 */
export type AppLogoTone = 'duotone' | 'color' | 'white' | 'dark' | 'mono';

/** The `fill` attributes of the official file a tone selects. */
export type AppLogoInk = {
    /** Lower-left arc of the mark. */
    lower: string;
    /** Upper-right arc of the mark. */
    upper: string;
    /** Every glyph of the wordmark. */
    word: string;
};

export function appLogoInk(tone: AppLogoTone): AppLogoInk {
    switch (tone) {
        case 'white':
            return {
                lower: BRAND_WHITE,
                upper: BRAND_WHITE,
                word: BRAND_WHITE,
            };
        case 'dark':
            return { lower: BRAND_INK, upper: BRAND_INK, word: BRAND_INK };
        case 'mono':
            return {
                lower: 'currentColor',
                upper: 'currentColor',
                word: 'currentColor',
            };
        default:
            return {
                lower: BRAND_MARK_LOWER,
                upper: BRAND_MARK_UPPER,
                word: BRAND_INK,
            };
    }
}

/**
 * The dark-theme half of `duotone` on the lock-up. A `fill` utility outranks
 * the `fill` attribute, so the attributes stay exactly what `logo_color.svg`
 * says while a dark theme swaps the whole lock-up to `logo_white.svg`'s ink.
 *
 * Spelled out rather than interpolated from `BRAND_WHITE`: Tailwind generates
 * utilities by scanning source text, so a class assembled at runtime is a class
 * that never reaches the stylesheet. It looked right in the DOM and did
 * nothing. `app-logo-assets.test.tsx` holds this literal and the ink together.
 */
export const LOCKUP_DARK_INK_CLASS = 'dark:fill-[#FFFFFF]';

/** Chip behind the mark in the `badge` variant. */
export type AppLogoSurface = 'brand' | 'sidebar' | 'onDark';

export type AppLogoVariant = 'lockup' | 'badge' | 'mark';

type SizeSpec = {
    /** Height-driven sizing; width comes from the asset's own ratio. */
    height: string;
    badge: string;
    radius: string;
    /** Width-driven inside a square chip, so a wide mark still fits. */
    markInBadge: string;
};

const SIZES: Record<AppLogoSize, SizeSpec> = {
    sm: {
        height: 'h-3 w-auto',
        badge: 'size-7',
        radius: 'rounded-md',
        markInBadge: 'w-4 h-auto',
    },
    md: {
        height: 'h-4 w-auto',
        badge: 'size-8',
        radius: 'rounded-md',
        markInBadge: 'w-5 h-auto',
    },
    lg: {
        height: 'h-5 w-auto',
        badge: 'size-9',
        radius: 'rounded-lg',
        markInBadge: 'w-5.5 h-auto',
    },
    xl: {
        height: 'h-7 w-auto',
        badge: 'size-11',
        radius: 'rounded-xl',
        markInBadge: 'w-7 h-auto',
    },
    '2xl': {
        height: 'h-10 w-auto',
        badge: 'size-15',
        radius: 'rounded-2xl',
        markInBadge: 'w-9 h-auto',
    },
    '3xl': {
        height: 'h-14 w-auto',
        badge: 'size-20',
        radius: 'rounded-3xl',
        markInBadge: 'w-12 h-auto',
    },
};

export function appLogoSizeSpec(size: AppLogoSize): SizeSpec {
    return SIZES[size];
}

/**
 * Chip surface plus the colour the mark inherits from it. The mark in a badge is
 * always `mono` — a duotone indigo mark on a filled indigo chip is unreadable,
 * and hardcoding a colour on the mark instead is what used to make
 * `bg-sidebar-primary` and its paired foreground token disagree.
 */
export function appLogoBadgeClasses(surface: AppLogoSurface): string {
    switch (surface) {
        case 'sidebar':
            return 'bg-sidebar-primary text-sidebar-primary-foreground';
        case 'onDark':
            return 'bg-white/10 text-white';
        default:
            return 'bg-primary text-primary-foreground';
    }
}
