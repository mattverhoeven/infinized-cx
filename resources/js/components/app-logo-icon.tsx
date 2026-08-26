import type { SVGAttributes } from 'react';
import { appLogoInk } from '@/lib/app-logo-styles';
import type { AppLogoTone } from '@/lib/app-logo-styles';

type Props = SVGAttributes<SVGElement> & {
    tone?: AppLogoTone;
    'data-test'?: string;
};

/**
 * The Infinized mark: `public/brand/icon_color.svg`, path for path.
 *
 * Two interlocking arcs that read as an infinity — a human and a digital
 * colleague linked into one chain. This is the variant for the places the full
 * lock-up does not fit; where it does fit, use the lock-up.
 *
 * `tone` picks which of the three shipped inkings is drawn — `icon_color`,
 * `icon_white` or `icon_dark`. The geometry is identical in all three files, so
 * only the fills change here. `app-logo-assets.test.tsx` compares what this
 * renders against the files themselves, which is what keeps the two from
 * drifting apart.
 *
 * Note the arcs here are **not** the arcs in `logo_color.svg`: the lock-up
 * draws the same shape at a different radius inside its own viewBox. Copying
 * one into the other is exactly the mistake that test exists to catch.
 *
 * The viewBox is 374×234, so the mark is ~1.6:1. Size it by height and let the
 * width follow; a square box either squashes it or wastes space.
 */
export default function AppLogoIcon({ tone = 'duotone', ...props }: Props) {
    const ink = appLogoInk(tone);

    return (
        <svg
            aria-hidden
            {...props}
            viewBox="0 0 374 234"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                fill={ink.lower}
                d="M173.121 208.101 A107 107 0 1 1 215.959 157.696 L189.557 125.026 A73 73 0 1 0 151.492 181.337 Z"
            />
            <path
                fill={ink.upper}
                d="M200.879 25.899 A107 107 0 1 1 158.041 76.304 L184.443 108.974 A73 73 0 1 0 222.508 52.663 Z"
            />
        </svg>
    );
}
