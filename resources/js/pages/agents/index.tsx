import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';

type Agent = {
    id: number;
    name: string;
    connector_id: string;
    channels: string[];
    status: string;
};

export default function AgentsIndex({
    agents,
    hasInfinizedConnection,
}: {
    agents: Agent[];
    hasInfinizedConnection: boolean;
}) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Agents', href: '#' }]}>
            <Head title="Agents" />
            <div className="flex flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-title font-display font-semibold">
                        CX agents
                    </h1>
                    {hasInfinizedConnection && (
                        <Button asChild size="sm">
                            <Link href="agents/create">New agent</Link>
                        </Button>
                    )}
                </div>
                {!hasInfinizedConnection && (
                    <p className="text-muted-foreground text-body">
                        <Link href="/settings/infinized" className="text-primary underline">
                            Connect Infinized
                        </Link>{' '}
                        before creating agents.
                    </p>
                )}
                <ul className="divide-border divide-y rounded-lg border">
                    {agents.map((agent) => (
                        <li key={agent.id} className="p-4">
                            <Link
                                href={`agents/${agent.id}`}
                                className="text-heading font-medium hover:underline"
                            >
                                {agent.name}
                            </Link>
                            <p className="text-muted-foreground text-caption mt-1">
                                Connector: {agent.connector_id} ·{' '}
                                {agent.channels.join(', ')}
                            </p>
                        </li>
                    ))}
                    {agents.length === 0 && (
                        <li className="text-muted-foreground p-4 text-body">
                            No agents yet.
                        </li>
                    )}
                </ul>
            </div>
        </AppLayout>
    );
}
