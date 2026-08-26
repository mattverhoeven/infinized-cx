import type { SVGAttributes } from 'react';
import { appLogoInk } from '@/lib/app-logo-styles';
import type { AppLogoTone } from '@/lib/app-logo-styles';

/**
 * The eight glyphs of the word "Infinized", shared by `wordmark_*.svg` and by
 * the `<g>` inside `logo_*.svg`. The two files place them differently — the
 * standalone wordmark normalises them into its own viewBox, the lock-up shifts
 * them clear of the mark — but the paths themselves are byte-identical, so the
 * lock-up imports them from here rather than keeping a second copy that could
 * drift.
 */
export const WORDMARK_GLYPHS = [
    'M343.18,2.46h28.41v206.82h-28.41V2.46Z',
    'M424.22,87.84c8.08-19.76,24.52-29.28,45.58-29.28,39.37,0,54.95,32.31,54.95,67.5v83.22h-27.26v-82.93c0-23.66-11.1-41.54-36.63-41.54s-36.63,18.32-36.63,41.54v82.93h-27.12V61.59h27.12v26.25Z',
    'M632.17,88.12h-51.35v121.15h-26.97v-121.15h-22.64v-26.54h22.64v-15.43c0-30.72,25.53-46.15,53.8-46.15s51.49,15.43,51.49,46.15h-26.97c.14-29.42-51.35-27.69-51.35,0v15.43h78.32v147.69h-26.97v-121.15Z',
    'M710.03,87.84c8.08-19.76,24.52-29.28,45.58-29.28,39.37,0,54.95,32.31,54.95,67.5v83.22h-27.26v-82.93c0-23.66-11.11-41.54-36.63-41.54s-36.63,18.32-36.63,41.54v82.93h-27.12V61.59h27.12v26.25Z',
    'M832.16,2.46h27.26v32.74h-27.26V2.46ZM832.16,61.59h27.26v147.69h-27.26V61.59Z',
    'M999.87,61.59v23.36l-89.71,97.79h89.71v26.54h-127.5v-23.36l89.71-97.64h-89.71v-26.68h127.5Z',
    'M999.13,135.58c0-42.26,32.88-77.02,75.58-77.02,49.33,0,80.91,42.98,74.85,90.14h-121.87c5.05,23.08,25.53,37.79,48.75,37.79,17.74,0,33.46-7.5,45.14-20.77l19.61,17.45c-48.46,54.95-142.06,28.41-142.06-47.6ZM1121.58,123.17c-10.96-51.2-84.81-50.19-93.75,0h93.75Z',
    'M1234.19,212.3c-42.69,0-75-34.32-75-76.58,0-70.09,89.28-106.15,125.91-49.47V2.46h27.11v206.82h-27.11v-24.37c-11.68,18.03-29.42,27.4-50.91,27.4ZM1236.64,186.05c28.27,0,50.33-22.21,50.33-50.48s-22.21-50.77-50.33-50.77-50.33,22.64-50.33,50.77,21.92,50.48,50.33,50.48Z',
] as const;

type Props = SVGAttributes<SVGElement> & {
    /**
     * Turns the wordmark into the accessible name for the logo. Without it the
     * wordmark is decorative, which is correct when nearby text already names
     * the brand.
     */
    label?: string;
    tone?: AppLogoTone;
    'data-test'?: string;
};

/**
 * The Infinized wordmark on its own: `public/brand/wordmark_dark.svg` and
 * `wordmark_white.svg`, path for path.
 *
 * The brand ships the wordmark in a single ink only, so `tone` picks between
 * the near-black `#0B0B14` and white. Reach for this when the mark is already
 * present somewhere else in the composition; in every other case the lock-up
 * is the asset that carries the brand name.
 *
 * The source paths start at x=343.18; the wrapping translate normalises them
 * into the 969.03-wide viewBox, matching the official file. Aspect is ~4.5:1 —
 * size by height.
 */
export default function AppLogoWordmark({
    label,
    tone = 'dark',
    ...props
}: Props) {
    const naming = label
        ? { role: 'img' as const, 'aria-label': label }
        : { 'aria-hidden': true };
    const ink = appLogoInk(tone);

    return (
        <svg
            {...naming}
            {...props}
            viewBox="0 0 969.03 214.76"
            xmlns="http://www.w3.org/2000/svg"
        >
            <g transform="translate(-343.18 0)">
                {WORDMARK_GLYPHS.map((glyph) => (
                    <path key={glyph} fill={ink.word} d={glyph} />
                ))}
            </g>
        </svg>
    );
}
