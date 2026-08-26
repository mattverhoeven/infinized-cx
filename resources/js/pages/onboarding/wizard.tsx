import { Head, useForm } from '@inertiajs/react';
import { Brain, CheckCircle2, Sparkles, Users } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type ChannelOption = { value: string; label: string };

type InfinizedProps = {
    url: string;
    connected: boolean;
    organization_name: string | null;
    organization_slug: string | null;
    mode: string | null;
    is_partner_child: boolean;
};

const STEPS = [
    { key: 'welcome', label: 'Workspace' },
    { key: 'infinized', label: 'Infinized' },
    { key: 'agent', label: 'Eerste agent' },
] as const;

export default function OnboardingWizard({
    step,
    workspace,
    infinized,
    can_auto_provision,
    channels,
}: {
    step: string;
    workspace: { name: string; slug: string };
    infinized: InfinizedProps;
    can_auto_provision: boolean;
    channels: ChannelOption[];
}) {
    const workspaceForm = useForm({ name: workspace.name });
    const infinizedForm = useForm({ path: 'provision', api_key: '' });
    const agentForm = useForm({
        name: '',
        instructions:
            'Je bent een vriendelijke klantenservice-agent. Help bezoekers helder en professioneel.',
        channels: ['genesys_digital'] as string[],
    });
    const skipForm = useForm({});

    const currentIndex = STEPS.findIndex((s) => s.key === step);

    const toggleChannel = (value: string, checked: boolean) => {
        const set = new Set(agentForm.data.channels);
        if (checked) {
            set.add(value);
        } else {
            set.delete(value);
        }
        agentForm.setData('channels', [...set]);
    };

    return (
        <AuthLayout
            title="Welkom bij Infinized CX"
            description="Stel je workspace in. Infinized zit standaard ingebouwd als het brein achter elke agent."
        >
            <Head title="Onboarding" />

            <div className="space-y-6">
                <nav className="flex justify-center gap-2">
                    {STEPS.map((s, index) => {
                        const done = index < currentIndex;
                        const active = s.key === step;

                        return (
                            <div
                                key={s.key}
                                className={`flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium ${
                                    active
                                        ? 'bg-primary text-primary-foreground'
                                        : done
                                          ? 'bg-muted text-foreground'
                                          : 'text-muted-foreground'
                                }`}
                            >
                                {done ? (
                                    <CheckCircle2 className="size-3.5" />
                                ) : (
                                    <span>{index + 1}</span>
                                )}
                                {s.label}
                            </div>
                        );
                    })}
                </nav>

                {step === 'welcome' && (
                    <div className="space-y-4">
                        <div className="rounded-lg border bg-muted/40 p-4 text-sm text-muted-foreground">
                            <div className="mb-2 flex items-center gap-2 font-medium text-foreground">
                                <Users className="size-4" />
                                Je CX-workspace
                            </div>
                            Eén workspace koppelt 1-op-1 aan één Infinized-organisatie —
                            agents, kennis en geheugen draaien daar.
                        </div>
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                workspaceForm.post('/onboarding/workspace');
                            }}
                            className="space-y-4"
                        >
                            <div>
                                <Label htmlFor="workspace_name">
                                    Workspace naam
                                </Label>
                                <Input
                                    id="workspace_name"
                                    value={workspaceForm.data.name}
                                    onChange={(e) =>
                                        workspaceForm.setData(
                                            'name',
                                            e.target.value,
                                        )
                                    }
                                />
                                <InputError message={workspaceForm.errors.name} />
                            </div>
                            <Button
                                type="submit"
                                disabled={workspaceForm.processing}
                                className="w-full"
                            >
                                Verder naar Infinized
                            </Button>
                        </form>
                    </div>
                )}

                {step === 'infinized' && (
                    <div className="space-y-4">
                        <div className="rounded-lg border border-primary/20 bg-primary/5 p-4 text-sm">
                            <div className="mb-2 flex items-center gap-2 font-medium">
                                <Brain className="size-4 text-primary" />
                                Infinized is ingebouwd
                            </div>
                            <p className="text-muted-foreground">
                                Je hebt geen apart Infinized-account nodig. We
                                maken er één voor je aan, of je koppelt een
                                bestaande organisatie met een API-sleutel.
                                Alles wat je hier configureert, draait op
                                Infinized.
                            </p>
                        </div>

                        {infinized.connected && (
                            <div className="rounded-lg border bg-muted/40 p-3 text-sm">
                                <p className="font-medium text-foreground">
                                    {infinized.organization_name ??
                                        infinized.organization_slug}
                                </p>
                                <p className="text-muted-foreground">
                                    {infinized.is_partner_child
                                        ? 'Nieuw aangemaakt via CX'
                                        : 'Gekoppeld met API-sleutel'}
                                </p>
                            </div>
                        )}

                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                infinizedForm.post('/onboarding/infinized');
                            }}
                            className="space-y-4"
                        >
                            <InputError message={infinizedForm.errors.infinized} />

                            {can_auto_provision && (
                                <Button
                                    type="submit"
                                    disabled={infinizedForm.processing}
                                    className="w-full"
                                    onClick={() =>
                                        infinizedForm.setData('path', 'provision')
                                    }
                                >
                                    <Sparkles className="mr-2 size-4" />
                                    Maak Infinized-organisatie voor mij
                                </Button>
                            )}

                            <div className="space-y-2">
                                <Label htmlFor="api_key">
                                    Of plak een bestaande API-sleutel
                                </Label>
                                <Input
                                    id="api_key"
                                    type="password"
                                    placeholder="ifz_…"
                                    value={infinizedForm.data.api_key}
                                    onChange={(e) =>
                                        infinizedForm.setData(
                                            'api_key',
                                            e.target.value,
                                        )
                                    }
                                />
                                <InputError
                                    message={infinizedForm.errors.api_key}
                                />
                                <Button
                                    type="submit"
                                    variant="secondary"
                                    disabled={
                                        infinizedForm.processing ||
                                        !infinizedForm.data.api_key
                                    }
                                    className="w-full"
                                    onClick={() =>
                                        infinizedForm.setData('path', 'api_key')
                                    }
                                >
                                    Koppelen met API-sleutel
                                </Button>
                            </div>

                            <p className="text-center text-xs text-muted-foreground">
                                Beheer kennis en agents ook op{' '}
                                <a
                                    href={infinized.url}
                                    className="text-primary underline"
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    infinized.com
                                </a>
                            </p>
                        </form>
                    </div>
                )}

                {step === 'agent' && (
                    <div className="space-y-4">
                        <div className="rounded-lg border bg-muted/40 p-4 text-sm text-muted-foreground">
                            Optioneel: maak je eerste CX-agent. Je kunt dit
                            later ook doen in het dashboard.
                        </div>
                        <form
                            onSubmit={(e) => {
                                e.preventDefault();
                                agentForm.post('/onboarding/agent');
                            }}
                            className="space-y-4"
                        >
                            <div>
                                <Label htmlFor="agent_name">Agent naam</Label>
                                <Input
                                    id="agent_name"
                                    placeholder="Klantenservice"
                                    value={agentForm.data.name}
                                    onChange={(e) =>
                                        agentForm.setData(
                                            'name',
                                            e.target.value,
                                        )
                                    }
                                />
                                <InputError message={agentForm.errors.name} />
                            </div>
                            <div>
                                <Label htmlFor="instructions">
                                    Instructies
                                </Label>
                                <textarea
                                    id="instructions"
                                    className="border-input bg-background mt-1 min-h-[100px] w-full rounded-md border p-2 text-sm"
                                    value={agentForm.data.instructions}
                                    onChange={(e) =>
                                        agentForm.setData(
                                            'instructions',
                                            e.target.value,
                                        )
                                    }
                                />
                                <InputError
                                    message={agentForm.errors.instructions}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>Kanalen</Label>
                                {channels.map((channel) => (
                                    <div
                                        key={channel.value}
                                        className="flex items-center gap-2"
                                    >
                                        <Checkbox
                                            checked={agentForm.data.channels.includes(
                                                channel.value,
                                            )}
                                            onCheckedChange={(c) =>
                                                toggleChannel(
                                                    channel.value,
                                                    c === true,
                                                )
                                            }
                                        />
                                        <span className="text-sm">
                                            {channel.label}
                                        </span>
                                    </div>
                                ))}
                                <InputError
                                    message={agentForm.errors.channels}
                                />
                            </div>
                            <Button
                                type="submit"
                                disabled={agentForm.processing}
                                className="w-full"
                            >
                                Agent aanmaken en starten
                            </Button>
                        </form>
                        <Button
                            type="button"
                            variant="ghost"
                            className="w-full"
                            disabled={
                                agentForm.processing || skipForm.processing
                            }
                            onClick={() => skipForm.post('/onboarding/skip-agent')}
                        >
                            Overslaan — naar dashboard
                        </Button>
                    </div>
                )}
            </div>
        </AuthLayout>
    );
}
