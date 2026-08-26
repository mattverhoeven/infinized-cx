import AppLogoIcon from '@/components/app-logo-icon';
import AppLogoLockup from '@/components/app-logo-lockup';
import { appLogoBadgeClasses, appLogoSizeSpec } from '@/lib/app-logo-styles';
import type {
    AppLogoSize,
    AppLogoSurface,
    AppLogoTone,
    AppLogoVariant,
} from '@/lib/app-logo-styles';
import { cn } from '@/lib/utils';
import { usePage } from '@inertiajs/react';

function LogoBadge({
    size,
    surface,
}: {
    size: AppLogoSize;
    surface: AppLogoSurface;
}) {
    const spec = appLogoSizeSpec(size);

    return (
        <span
            className={cn(
                'flex shrink-0 items-center justify-center',
                spec.badge,
                spec.radius,
                appLogoBadgeClasses(surface),
            )}
        >
            <AppLogoIcon tone="mono" className={spec.markInBadge} />
        </span>
    );
}

type Props = {
    variant?: AppLogoVariant;
    tone?: AppLogoTone;
    surface?: AppLogoSurface;
    size?: AppLogoSize;
    className?: string;
};

export default function AppLogo({
    variant = 'lockup',
    tone = 'duotone',
    surface = 'brand',
    size = 'md',
    className,
}: Props) {
    const { name } = usePage().props;
    const spec = appLogoSizeSpec(size);
    const productLabel = name ? `${name}` : 'Infinized CX';

    if (variant === 'mark') {
        return (
            <AppLogoIcon
                tone={tone}
                className={cn(spec.height, className)}
                data-test="app-logo-mark"
            />
        );
    }

    if (variant === 'badge') {
        return (
            <span className={cn('inline-flex', className)}>
                <LogoBadge size={size} surface={surface} />
            </span>
        );
    }

    return (
        <span
            className={cn('flex items-center gap-2 overflow-visible', className)}
        >
            <AppLogoLockup
                tone={tone}
                label={productLabel}
                className={cn(
                    'shrink-0',
                    spec.height,
                    'group-data-[collapsible=icon]:hidden',
                )}
            />
            <AppLogoIcon
                tone={tone}
                className="hidden h-auto w-4 shrink-0 group-data-[collapsible=icon]:block"
            />
        </span>
    );
}
