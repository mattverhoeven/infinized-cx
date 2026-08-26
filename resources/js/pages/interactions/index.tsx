import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

type Interaction = {
    id: number;
    channel: string;
    state: string;
    agent_name: string | null;
    started_at: string | null;
};

export default function InteractionsIndex({
    interactions,
}: {
    interactions: Interaction[];
}) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Interactions', href: '#' }]}>
            <Head title="Interactions" />
            <div className="p-4">
                <h1 className="text-title font-display mb-4 font-semibold">
                    Interactions
                </h1>
                <ul className="divide-border divide-y rounded-lg border">
                    {interactions.map((interaction) => (
                        <li key={interaction.id} className="p-3">
                            <Link
                                href={`interactions/${interaction.id}`}
                                className="text-heading font-medium hover:underline"
                            >
                                {interaction.agent_name ?? 'Agent'} ·{' '}
                                {interaction.channel}
                            </Link>
                            <p className="text-muted-foreground text-caption">
                                {interaction.state} ·{' '}
                                {interaction.started_at ?? '—'}
                            </p>
                        </li>
                    ))}
                    {interactions.length === 0 && (
                        <li className="text-muted-foreground p-3 text-body">
                            No interactions yet.
                        </li>
                    )}
                </ul>
            </div>
        </AppLayout>
    );
}
