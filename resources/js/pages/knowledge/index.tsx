import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';

type Source = { id: number; title: string; type?: string };

export default function KnowledgeIndex({
    sources,
    infinized_url,
    organization_slug,
}: {
    sources: Source[];
    infinized_url: string;
    organization_slug: string;
}) {
    const form = useForm({ title: '', content: '' });

    return (
        <AppLayout breadcrumbs={[{ title: 'Knowledge', href: '#' }]}>
            <Head title="Knowledge" />
            <div className="mx-auto max-w-3xl space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-title font-display font-semibold">
                        Knowledge
                    </h1>
                    <a
                        href={`${infinized_url}/${organization_slug}/knowledge`}
                        className="text-primary text-label underline"
                        target="_blank"
                        rel="noreferrer"
                    >
                        Open in Infinized
                    </a>
                </div>
                <ul className="divide-border divide-y rounded-lg border">
                    {sources.map((source) => (
                        <li key={source.id} className="p-3 text-body">
                            {source.title}
                        </li>
                    ))}
                    {sources.length === 0 && (
                        <li className="text-muted-foreground p-3 text-body">
                            No sources yet.
                        </li>
                    )}
                </ul>
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post('knowledge');
                    }}
                    className="space-y-3 rounded-lg border p-4"
                >
                    <h2 className="text-heading font-medium">Add text source</h2>
                    <div>
                        <Label htmlFor="title">Title</Label>
                        <Input
                            id="title"
                            value={form.data.title}
                            onChange={(e) =>
                                form.setData('title', e.target.value)
                            }
                        />
                    </div>
                    <div>
                        <Label htmlFor="content">Content</Label>
                        <textarea
                            id="content"
                            className="border-input bg-background mt-1 min-h-[100px] w-full rounded-md border p-2 text-body"
                            value={form.data.content}
                            onChange={(e) =>
                                form.setData('content', e.target.value)
                            }
                        />
                    </div>
                    <Button type="submit" size="sm" disabled={form.processing}>
                        Add source
                    </Button>
                </form>
            </div>
        </AppLayout>
    );
}
