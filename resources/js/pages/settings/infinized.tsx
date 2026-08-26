import { Head, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

type Connection = {
    organization_slug: string;
    organization_name: string | null;
    mode: string;
    is_partner_child: boolean;
    last_health_check_at: string | null;
};

export default function InfinizedSettings({
    connection,
    infinized_url,
}: {
    connection: Connection | null;
    infinized_url: string;
}) {
    const connectForm = useForm({ api_key: '' });

    return (
        <AppLayout breadcrumbs={[{ title: 'Infinized', href: '/settings/infinized' }]}>
            <Head title="Infinized connection" />
            <SettingsLayout>
                <div className="space-y-6">
                    <div>
                        <h3 className="text-heading font-semibold">
                            Infinized brain
                        </h3>
                        <p className="text-muted-foreground text-body mt-1">
                            Connect this workspace to an Infinized organization.
                            Agents, knowledge, and live turns run through{' '}
                            <a
                                href={infinized_url}
                                className="text-primary underline"
                                target="_blank"
                                rel="noreferrer"
                            >
                                Infinized
                            </a>
                            .
                        </p>
                    </div>

                    {connection ? (
                        <div className="bg-card space-y-3 rounded-lg border p-4">
                            <p className="text-label font-medium">
                                {connection.organization_name ??
                                    connection.organization_slug}
                            </p>
                            <p className="text-muted-foreground text-caption">
                                Slug: {connection.organization_slug} · Mode:{' '}
                                {connection.mode}
                            </p>
                            <form
                                method="post"
                                action="/settings/infinized/health"
                            >
                                <input
                                    type="hidden"
                                    name="_token"
                                    value={
                                        document
                                            .querySelector(
                                                'meta[name="csrf-token"]',
                                            )
                                            ?.getAttribute('content') ?? ''
                                    }
                                />
                                <Button type="submit" variant="outline" size="sm">
                                    Run health check
                                </Button>
                            </form>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            <form
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    connectForm.post('/settings/infinized');
                                }}
                                className="space-y-4"
                            >
                                <div>
                                    <Label htmlFor="api_key">Org API key</Label>
                                    <Input
                                        id="api_key"
                                        type="password"
                                        placeholder="ifz_…"
                                        value={connectForm.data.api_key}
                                        onChange={(e) =>
                                            connectForm.setData(
                                                'api_key',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError message={connectForm.errors.api_key} />
                                </div>
                                <Button
                                    type="submit"
                                    disabled={connectForm.processing}
                                >
                                    Connect
                                </Button>
                            </form>
                            <form
                                method="post"
                                action="/settings/infinized/provision"
                            >
                                <input
                                    type="hidden"
                                    name="_token"
                                    value={
                                        document
                                            .querySelector(
                                                'meta[name="csrf-token"]',
                                            )
                                            ?.getAttribute('content') ?? ''
                                    }
                                />
                                <Button type="submit" variant="secondary">
                                    Create Infinized org for me
                                </Button>
                            </form>
                        </div>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
