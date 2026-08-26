import { Head, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';

export default function GenesysSettings({
    connection,
    regions,
    audiohook,
    architect_recipe,
}: {
    connection: {
        region: string;
        verified_at: string | null;
    } | null;
    regions: Record<string, string>;
    audiohook: { api_key: string; wss_uri: string } | null;
    architect_recipe: {
        audio_connector_id: string;
        bot_intents: string[];
    };
}) {
    const form = useForm({
        region: connection?.region ?? 'mypurecloud.com',
        client_id: '',
        client_secret: '',
    });

    return (
        <AppLayout breadcrumbs={[{ title: 'Genesys', href: '#' }]}>
            <Head title="Genesys Cloud" />
            <div className="mx-auto max-w-2xl space-y-6 p-4">
                <h1 className="text-title font-display font-semibold">
                    Genesys Cloud
                </h1>
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post('genesys');
                    }}
                    className="space-y-4 rounded-lg border p-4"
                >
                    <div>
                        <Label>Region</Label>
                        <Select
                            value={form.data.region}
                            onValueChange={(v) => form.setData('region', v)}
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {Object.entries(regions).map(([value, label]) => (
                                    <SelectItem key={value} value={value}>
                                        {label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div>
                        <Label htmlFor="client_id">Client ID</Label>
                        <Input
                            id="client_id"
                            value={form.data.client_id}
                            onChange={(e) =>
                                form.setData('client_id', e.target.value)
                            }
                        />
                    </div>
                    <div>
                        <Label htmlFor="client_secret">Client secret</Label>
                        <Input
                            id="client_secret"
                            type="password"
                            value={form.data.client_secret}
                            onChange={(e) =>
                                form.setData('client_secret', e.target.value)
                            }
                        />
                        <InputError message={form.errors.genesys} />
                    </div>
                    <Button type="submit" disabled={form.processing}>
                        Save & verify
                    </Button>
                </form>
                {audiohook && (
                    <div className="space-y-2 rounded-lg border p-4 text-body">
                        <h2 className="text-heading font-medium">
                            Audio Connector
                        </h2>
                        <p>WSS URI: {audiohook.wss_uri}</p>
                        <p>API key: {audiohook.api_key}</p>
                        <p className="text-muted-foreground text-caption">
                            Architect Connector ID: use agent connector_id (
                            {architect_recipe.audio_connector_id})
                        </p>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
