import type { SVGAttributes } from 'react';
import { WORDMARK_GLYPHS } from '@/components/app-logo-wordmark';
import { appLogoInk, LOCKUP_DARK_INK_CLASS } from '@/lib/app-logo-styles';
import type { AppLogoTone } from '@/lib/app-logo-styles';
import { cn } from '@/lib/utils';

/**
 * The two arcs as the lock-up draws them. They are **not** the arcs in
 * `icon_color.svg`: the same shape sits at a slightly smaller radius here so it
 * stands to the wordmark's cap height inside the lock-up's own viewBox.
 */
const LOCKUP_ARCS = [
    'M159.95 196.71 A104.92 104.92 0 1 1 201.955 147.285 L176.067 115.25 A71.581 71.581 0 1 0 138.742 170.467 Z',
    'M187.169 18.05 A104.92 104.92 0 1 1 145.163 67.475 L171.052 99.51 A71.581 71.581 0 1 0 208.377 44.293 Z',
] as const;

/**
 * The official distance between the mark and the word. It comes out of the
 * asset rather than out of a flex gap, which is the whole reason the lock-up is
 * one SVG here: a gap in the layout is a guess at this number, and it drifts
 * with the size ladder while this does not.
 */
const WORDMARK_OFFSET = 'translate(71.087 0)';

type Props = SVGAttributes<SVGElement> & {
    tone?: AppLogoTone;
    /** Accessible name. Omit only when adjacent text already names the brand. */
    label?: string;
    'data-test'?: string;
};

/**
 * The Infinized lock-up: `public/brand/logo_color.svg`, path for path.
 *
 * This is the only variant that carries the brand name, and it is the asset the
 * brand guidelines ask for wherever there is room for it. `tone` picks which of
 * the three shipped inkings is drawn:
 *
 * - `duotone` (default) — `logo_color.svg`, with `logo_white.svg`'s ink on a
 *   dark theme, because the coloured wordmark is `#0B0B14` and disappears
 *   against a dark background.
 * - `color` — `logo_color.svg`, pinned, for a surface that stays light.
 * - `white` / `dark` — `logo_white.svg` / `logo_dark.svg`.
 * - `mono` — `currentColor`, the documented exception in `app-logo-styles.ts`.
 *
 * It used to be assembled here out of two separate SVGs with a flex gap between
 * them, and the wordmark was painted in `currentColor` on the theory that the
 * brand had no coloured wordmark. `logo_color.svg` is a coloured wordmark —
 * `#0B0B14`, a specific ink and not whatever `--foreground` happens to be — so
 * what shipped was close to the logo rather than the logo.
 * `app-logo-assets.test.tsx` now compares this against the files themselves.
 *
 * The viewBox is 1383.297×214.76, so the lock-up is 6.44:1. Size it by height:
 * `aspect-square` squeezes it to a sixth of its width, which is how it once
 * came out of a DOCX export as a 32×32 smudge.
 *
 * That width is the wordmark's advance: the **d** stem sits flush with the
 * viewBox edge, and Space Grotesk's bold **d** hangs a hair past it. SVG
 * defaults to `overflow: hidden`, which shears the last letter. The geometry
 * stays the official file; `overflow: visible` is what lets the hang paint.
 */
export default function AppLogoLockup({
    tone = 'duotone',
    label,
    className,
    ...props
}: Props) {
    const ink = appLogoInk(tone);
    const themed = tone === 'duotone' ? LOCKUP_DARK_INK_CLASS : undefined;
    const naming = label
        ? { role: 'img' as const, 'aria-label': label }
        : { 'aria-hidden': true };

    return (
        <svg
            {...naming}
            {...props}
            overflow="visible"
            className={cn('overflow-visible', className)}
            viewBox="0 0 1383.297 214.76"
            xmlns="http://www.w3.org/2000/svg"
        >
            <g data-test="app-logo-mark">
                <path className={themed} fill={ink.lower} d={LOCKUP_ARCS[0]} />
                <path className={themed} fill={ink.upper} d={LOCKUP_ARCS[1]} />
            </g>
            <g data-test="app-logo-wordmark" transform={WORDMARK_OFFSET}>
                {WORDMARK_GLYPHS.map((glyph) => (
                    <path
                        key={glyph}
                        className={themed}
                        fill={ink.word}
                        d={glyph}
                    />
                ))}
            </g>
        </svg>
    );
}
