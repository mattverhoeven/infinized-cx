import { Head, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';

type ChannelOption = { value: string; label: string };

export default function AgentsCreate({
    channels,
}: {
    channels: ChannelOption[];
}) {
    const form = useForm({
        name: '',
        instructions: '',
        channels: [] as string[],
    });

    const toggleChannel = (value: string, checked: boolean) => {
        const set = new Set(form.data.channels);
        if (checked) {
            set.add(value);
        } else {
            set.delete(value);
        }
        form.setData('channels', [...set]);
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'New agent', href: '#' }]}>
            <Head title="New agent" />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post('agents');
                }}
                className="mx-auto max-w-2xl space-y-6 p-4"
            >
                <div>
                    <Label htmlFor="name">Name</Label>
                    <Input
                        id="name"
                        value={form.data.name}
                        onChange={(e) => form.setData('name', e.target.value)}
                    />
                    <InputError message={form.errors.name} />
                </div>
                <div>
                    <Label htmlFor="instructions">Instructions</Label>
                    <textarea
                        id="instructions"
                        className="border-input bg-background mt-1 min-h-[120px] w-full rounded-md border p-2 text-body"
                        value={form.data.instructions}
                        onChange={(e) =>
                            form.setData('instructions', e.target.value)
                        }
                    />
                    <InputError message={form.errors.instructions} />
                </div>
                <div className="space-y-2">
                    <Label>Channels</Label>
                    {channels.map((channel) => (
                        <div
                            key={channel.value}
                            className="flex items-center gap-2"
                        >
                            <Checkbox
                                checked={form.data.channels.includes(
                                    channel.value,
                                )}
                                onCheckedChange={(c) =>
                                    toggleChannel(channel.value, c === true)
                                }
                            />
                            <span className="text-body">{channel.label}</span>
                        </div>
                    ))}
                    <InputError message={form.errors.channels} />
                </div>
                <Button type="submit" disabled={form.processing}>
                    Create agent
                </Button>
            </form>
        </AppLayout>
    );
}
