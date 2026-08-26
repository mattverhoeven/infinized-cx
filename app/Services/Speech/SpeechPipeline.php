<?php

namespace App\Services\Speech;

use RuntimeException;

/**
 * Pluggable STT/TTS pipeline. Defaults are stubs that CX replaces with real
 * providers; Infinized Labs can be wired via config('cx.speech.labs_enabled').
 */
class SpeechPipeline
{
    public function transcribe(string $audioPayload): string
    {
        $provider = config('cx.speech.stt_provider');

        if (config('cx.speech.labs_enabled')) {
            return $this->labsTranscribe($audioPayload);
        }

        return match ($provider) {
            'deepgram' => $this->deepgramTranscribe($audioPayload),
            default => throw new RuntimeException("STT provider [{$provider}] is not configured."),
        };
    }

    /**
     * @param  array<string, mixed>  $voiceSettings
     */
    public function synthesize(string $text, array $voiceSettings = []): string
    {
        $provider = config('cx.speech.tts_provider');

        if (config('cx.speech.labs_enabled')) {
            return $this->labsSynthesize($text, $voiceSettings);
        }

        return match ($provider) {
            'elevenlabs' => $this->elevenLabsSynthesize($text, $voiceSettings),
            default => throw new RuntimeException("TTS provider [{$provider}] is not configured."),
        };
    }

    protected function deepgramTranscribe(string $audioPayload): string
    {
        // μ-law 8kHz payload from Genesys; production uses streaming API.
        if ($audioPayload === '') {
            return '';
        }

        return '[voice input received]';
    }

    /**
     * @param  array<string, mixed>  $voiceSettings
     */
    protected function elevenLabsSynthesize(string $text, array $voiceSettings): string
    {
        return base64_encode($text);
    }

    protected function labsTranscribe(string $audioPayload): string
    {
        return $this->deepgramTranscribe($audioPayload);
    }

    /**
     * @param  array<string, mixed>  $voiceSettings
     */
    protected function labsSynthesize(string $text, array $voiceSettings): string
    {
        return $this->elevenLabsSynthesize($text, $voiceSettings);
    }
}
