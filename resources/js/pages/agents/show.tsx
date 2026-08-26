import { Head, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';

type Agent = {
    id: number;
    name: string;
    connector_id: string;
    channels: string[];
    status: string;
    infinized_agent_id: number;
};

export default function AgentsShow({
    agent,
    infinized_url,
    organization_slug,
    test_chat_reply,
}: {
    agent: Agent;
    infinized_url: string;
    organization_slug: string | null;
    test_chat_reply?: string;
}) {
    const form = useForm({ message: '' });

    return (
        <AppLayout breadcrumbs={[{ title: agent.name, href: '#' }]}>
            <Head title={agent.name} />
            <div className="mx-auto max-w-2xl space-y-6 p-4">
                <div>
                    <h1 className="text-title font-display font-semibold">
                        {agent.name}
                    </h1>
                    <p className="text-muted-foreground text-caption mt-1">
                        Connector ID: {agent.connector_id} · Infinized agent{' '}
                        {agent.infinized_agent_id}
                    </p>
                    {organization_slug && (
                        <a
                            href={`${infinized_url}/${organization_slug}/agents`}
                            className="text-primary text-label mt-2 inline-block underline"
                            target="_blank"
                            rel="noreferrer"
                        >
                            Open in Infinized
                        </a>
                    )}
                </div>
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post(`agents/${agent.id}/test-chat`);
                    }}
                    className="space-y-3 rounded-lg border p-4"
                >
                    <Label htmlFor="message">Test chat</Label>
                    <Input
                        id="message"
                        value={form.data.message}
                        onChange={(e) =>
                            form.setData('message', e.target.value)
                        }
                    />
                    <InputError message={form.errors.message} />
                    <Button type="submit" size="sm" disabled={form.processing}>
                        Send test message
                    </Button>
                    {test_chat_reply && (
                        <p className="bg-muted mt-3 rounded-md p-3 text-body">
                            {test_chat_reply}
                        </p>
                    )}
                </form>
            </div>
        </AppLayout>
    );
}
