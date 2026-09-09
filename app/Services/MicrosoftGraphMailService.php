<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Servicio de envío de correos vía Microsoft Graph (Laravel 13 / PHP 8.3+).
 *
 * -----------------------------------------------------------------------------
 * 1) Azure Portal > App registrations > tu app > API permissions:
 *    Microsoft Graph > Application permissions > Mail.Send  (+ Grant admin consent)
 *
 * 2) .env
 *    MSGRAPH_TENANT_ID=00000000-0000-0000-0000-000000000000
 *    MSGRAPH_CLIENT_ID=00000000-0000-0000-0000-000000000000
 *    MSGRAPH_CLIENT_SECRET=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
 *    MSGRAPH_FROM=no-reply@tudominio.com
 *    MSGRAPH_SAVE_TO_SENT=true
 *
 * 3) config/services.php
 *    'microsoft_graph' => [
 *        'tenant_id'     => env('MSGRAPH_TENANT_ID'),
 *        'client_id'     => env('MSGRAPH_CLIENT_ID'),
 *        'client_secret' => env('MSGRAPH_CLIENT_SECRET'),
 *        'from'          => env('MSGRAPH_FROM'),
 *        'save_to_sent'  => env('MSGRAPH_SAVE_TO_SENT', true),
 *    ],
 *
 * 4) Uso (API fluida)
 *    app(MicrosoftGraphMailService::class)
 *        ->to('cliente@dominio.com')
 *        ->cc(['jefe@dominio.com' => 'Jefe de Planta'])
 *        ->subject('Ticket #1234 asignado')
 *        ->html(view('mails.ticket', compact('ticket'))->render())
 *        ->attachPath(storage_path('app/reportes/ticket-1234.pdf'))
 *        ->send();
 *
 *    Uso (una sola llamada)
 *    app(MicrosoftGraphMailService::class)->sendMail(
 *        to: ['a@dominio.com', 'b@dominio.com' => 'Beto'],
 *        subject: 'Hola',
 *        html: '<h1>Hola</h1>',
 *        attachments: [storage_path('app/x.pdf')],
 *    );
 * -----------------------------------------------------------------------------
 */
class MicrosoftGraphMailService
{
    private const GRAPH_BASE = 'https://graph.microsoft.com/v1.0';

    private const LOGIN_BASE = 'https://login.microsoftonline.com';

    /** Graph rechaza sendMail con payload > ~4 MB; dejamos margen para el JSON. */
    private const SMALL_ATTACHMENT_LIMIT = 3_000_000;

    /** Los chunks de una upload session deben ser múltiplo de 320 KiB. */
    private const UPLOAD_CHUNK_SIZE = 3_932_160; // 12 * 320 KiB

    private string $tenantId;

    private string $clientId;

    private string $clientSecret;

    private string $defaultFrom;

    private bool $saveToSentItems;

    /** Estado del builder fluido. */
    private array $message = [];

    private array $attachments = [];

    private ?string $from = null;

    public function __construct(
        ?string $tenantId = null,
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $from = null,
        ?bool $saveToSentItems = null,
    ) {
        $this->tenantId = $tenantId ?? (string) config('services.microsoft_graph.tenant_id');
        $this->clientId = $clientId ?? (string) config('services.microsoft_graph.client_id');
        $this->clientSecret = $clientSecret ?? (string) config('services.microsoft_graph.client_secret');
        $this->defaultFrom = $from ?? (string) config('services.microsoft_graph.from');
        $this->saveToSentItems = $saveToSentItems ?? (bool) config('services.microsoft_graph.save_to_sent', true);

        foreach (['tenantId', 'clientId', 'clientSecret', 'defaultFrom'] as $required) {
            if ($this->{$required} === '') {
                throw new RuntimeException("Microsoft Graph: falta configurar [{$required}].");
            }
        }

        $this->reset();
    }

    // -------------------------------------------------------------------------
    // API fluida
    // -------------------------------------------------------------------------

    /** Buzón remitente (UPN o id de usuario). La app debe tener permiso sobre él. */
    public function fromMailbox(string $mailbox): static
    {
        $this->from = $mailbox;

        return $this;
    }

    public function to(string|array $recipients): static
    {
        return $this->addRecipients('toRecipients', $recipients);
    }

    public function cc(string|array $recipients): static
    {
        return $this->addRecipients('ccRecipients', $recipients);
    }

    public function bcc(string|array $recipients): static
    {
        return $this->addRecipients('bccRecipients', $recipients);
    }

    public function replyTo(string|array $recipients): static
    {
        return $this->addRecipients('replyTo', $recipients);
    }

    public function subject(string $subject): static
    {
        $this->message['subject'] = $subject;

        return $this;
    }

    public function html(string $html): static
    {
        $this->message['body'] = ['contentType' => 'HTML', 'content' => $html];

        return $this;
    }

    public function text(string $text): static
    {
        $this->message['body'] = ['contentType' => 'Text', 'content' => $text];

        return $this;
    }

    /** low | normal | high */
    public function importance(string $importance): static
    {
        $this->message['importance'] = $importance;

        return $this;
    }

    /** Cabeceras personalizadas. Graph exige que el nombre empiece con "x-". */
    public function headers(array $headers): static
    {
        $this->message['internetMessageHeaders'] = array_map(
            static fn (string $name, mixed $value): array => ['name' => $name, 'value' => (string) $value],
            array_keys($headers),
            array_values($headers),
        );

        return $this;
    }

    public function attachPath(string $path, ?string $name = null, ?string $mime = null): static
    {
        if (! is_readable($path)) {
            throw new RuntimeException("Microsoft Graph: no se puede leer el adjunto [{$path}].");
        }

        return $this->attachData(
            (string) file_get_contents($path),
            $name ?? basename($path),
            $mime ?? (mime_content_type($path) ?: 'application/octet-stream'),
        );
    }

    public function attachData(string $content, string $name, string $mime = 'application/octet-stream'): static
    {
        $this->attachments[] = [
            'name' => $name,
            'content' => $content,
            'mime' => $mime,
        ];

        return $this;
    }

    /** Imagen embebida: referenciarla en el HTML como <img src="cid:mi-logo"> */
    public function embed(string $path, string $cid, ?string $mime = null): static
    {
        $this->attachPath($path, $cid, $mime);

        $last = array_key_last($this->attachments);
        $this->attachments[$last]['inline'] = true;
        $this->attachments[$last]['cid'] = $cid;

        return $this;
    }

    /**
     * Envía el mensaje acumulado y reinicia el builder.
     *
     * @return string|null id del mensaje cuando se usó la ruta de adjuntos
     *                     grandes (borrador + upload session); null en envío directo.
     */
    public function send(): ?string
    {
        $mailbox = $this->from ?: $this->defaultFrom;
        $message = $this->message;
        $attachments = $this->attachments;

        $this->reset();

        if ($message['toRecipients'] === []) {
            throw new RuntimeException('Microsoft Graph: el mensaje no tiene destinatarios.');
        }

        $message['body'] ??= ['contentType' => 'Text', 'content' => ''];
        $message = array_filter($message, static fn (mixed $v): bool => $v !== []);

        $attachments = array_values($attachments);

        $hasLarge = array_filter(
            $attachments,
            static fn (array $a): bool => strlen($a['content']) > self::SMALL_ATTACHMENT_LIMIT,
        ) !== [];

        if ($hasLarge) {
            return $this->sendWithLargeAttachments($mailbox, $message, $attachments);
        }

        if ($attachments !== []) {
            $message['attachments'] = array_map($this->toFileAttachment(...), $attachments);
        }

        $this->request()->post("/users/{$this->encode($mailbox)}/sendMail", [
            'message' => $message,
            'saveToSentItems' => $this->saveToSentItems,
        ]);

        return null;
    }

    /** Atajo de una sola llamada. */
    public function sendMail(
        string|array $to,
        string $subject,
        ?string $html = null,
        ?string $text = null,
        string|array $cc = [],
        string|array $bcc = [],
        string|array $replyTo = [],
        array $attachments = [],
        ?string $from = null,
    ): ?string {
        $this->to($to)->subject($subject);

        $html !== null ? $this->html($html) : $this->text($text ?? '');

        if ($cc !== []) {
            $this->cc($cc);
        }

        if ($bcc !== []) {
            $this->bcc($bcc);
        }

        if ($replyTo !== []) {
            $this->replyTo($replyTo);
        }

        if ($from !== null) {
            $this->fromMailbox($from);
        }

        foreach ($attachments as $attachment) {
            is_string($attachment)
                ? $this->attachPath($attachment)
                : $this->attachData(
                    $attachment['content'],
                    $attachment['name'],
                    $attachment['mime'] ?? 'application/octet-stream',
                );
        }

        return $this->send();
    }

    // -------------------------------------------------------------------------
    // Adjuntos grandes: borrador -> upload session -> send
    // -------------------------------------------------------------------------

    private function sendWithLargeAttachments(string $mailbox, array $message, array $attachments): string
    {
        $user = $this->encode($mailbox);

        $small = [];
        $large = [];

        foreach ($attachments as $attachment) {
            strlen($attachment['content']) > self::SMALL_ATTACHMENT_LIMIT
                ? $large[] = $attachment
                : $small[] = $attachment;
        }

        if ($small !== []) {
            $message['attachments'] = array_map($this->toFileAttachment(...), $small);
        }

        $messageId = (string) $this->request()
            ->post("/users/{$user}/messages", $message)
            ->json('id');

        foreach ($large as $attachment) {
            $this->uploadLargeAttachment($user, $messageId, $attachment);
        }

        $this->request()->post("/users/{$user}/messages/{$messageId}/send");

        return $messageId;
    }

    private function uploadLargeAttachment(string $user, string $messageId, array $attachment): void
    {
        $size = strlen($attachment['content']);

        $uploadUrl = (string) $this->request()
            ->post("/users/{$user}/messages/{$messageId}/attachments/createUploadSession", [
                'AttachmentItem' => [
                    'attachmentType' => 'file',
                    'name' => $attachment['name'],
                    'size' => $size,
                    'contentType' => $attachment['mime'],
                    'isInline' => $attachment['inline'] ?? false,
                ],
            ])
            ->json('uploadUrl');

        for ($offset = 0; $offset < $size; $offset += self::UPLOAD_CHUNK_SIZE) {
            $chunk = substr($attachment['content'], $offset, self::UPLOAD_CHUNK_SIZE);
            $end = $offset + strlen($chunk) - 1;

            // La uploadUrl ya viene pre-autenticada: no se manda el Bearer.
            Http::withHeaders([
                'Content-Length' => (string) strlen($chunk),
                'Content-Range' => "bytes {$offset}-{$end}/{$size}",
            ])
                ->withBody($chunk, 'application/octet-stream')
                ->timeout(180)
                ->retry(3, 2000, throw: false)
                ->put($uploadUrl)
                ->throw();
        }
    }

    // -------------------------------------------------------------------------
    // Token / cliente HTTP
    // -------------------------------------------------------------------------

    /** Token de aplicación (client credentials), cacheado con margen antes de expirar. */
    public function accessToken(): string
    {
        return Cache::remember($this->tokenCacheKey(), now()->addMinutes(50), function (): string {
            $response = Http::asForm()
                ->timeout(30)
                ->retry(3, 500, throw: false)
                ->post(self::LOGIN_BASE."/{$this->tenantId}/oauth2/v2.0/token", [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'https://graph.microsoft.com/.default',
                ]);

            if ($response->failed()) {
                throw new RuntimeException(
                    'Microsoft Graph: no se pudo obtener el token. '.$this->errorMessage($response)
                );
            }

            $token = (string) $response->json('access_token');

            if ($token === '') {
                throw new RuntimeException('Microsoft Graph: la respuesta del token vino vacía.');
            }

            return $token;
        });
    }

    public function forgetToken(): void
    {
        Cache::forget($this->tokenCacheKey());
    }

    private function tokenCacheKey(): string
    {
        return 'msgraph:token:'.sha1($this->tenantId.'|'.$this->clientId);
    }

    /** Cliente Graph: token, reintentos ante 429/5xx y errores como RuntimeException legible. */
    private function request(): PendingRequest
    {
        return Http::baseUrl(self::GRAPH_BASE)
            ->withToken($this->accessToken())
            ->acceptJson()
            ->timeout(120)
            ->retry(3, 1000, static function (Throwable $e): bool {
                if (! $e instanceof RequestException) {
                    return true; // ConnectionException: reintentar.
                }

                return $e->response->status() === 429 || $e->response->serverError();
            }, throw: false)
            ->throw(function (Response $response): void {
                throw new RuntimeException('Microsoft Graph: '.$this->errorMessage($response));
            });
    }

    private function errorMessage(Response $response): string
    {
        $payload = $response->json();

        $detail = is_array($payload)
            ? ($payload['error']['message'] ?? $payload['error_description'] ?? null)
            : null;

        return "[{$response->status()}] ".mb_substr((string) ($detail ?? $response->body()), 0, 500);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function reset(): void
    {
        $this->message = [
            'toRecipients' => [],
            'ccRecipients' => [],
            'bccRecipients' => [],
            'replyTo' => [],
        ];
        $this->attachments = [];
        $this->from = null;
    }

    private function addRecipients(string $bucket, string|array $recipients): static
    {
        foreach ($this->normalizeRecipients($recipients) as $recipient) {
            $this->message[$bucket][] = $recipient;
        }

        return $this;
    }

    /**
     * Acepta: 'a@b.com'
     *       | ['a@b.com', 'c@d.com']
     *       | ['a@b.com' => 'Nombre']
     *       | [['email' => 'a@b.com', 'name' => 'Nombre']]
     */
    private function normalizeRecipients(string|array $recipients): array
    {
        if (is_string($recipients)) {
            $recipients = [$recipients];
        }

        $out = [];

        foreach ($recipients as $key => $value) {
            if (is_array($value)) {
                $email = $value['email'] ?? $value['address'] ?? null;
                $name = $value['name'] ?? null;
            } elseif (is_string($key)) {
                $email = $key;
                $name = is_string($value) ? $value : null;
            } else {
                $email = $value;
                $name = null;
            }

            if (! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                throw new RuntimeException('Microsoft Graph: destinatario inválido.');
            }

            $address = ['address' => $email];

            if (is_string($name) && $name !== '') {
                $address['name'] = $name;
            }

            $out[] = ['emailAddress' => $address];
        }

        return $out;
    }

    private function toFileAttachment(array $attachment): array
    {
        $payload = [
            '@odata.type' => '#microsoft.graph.fileAttachment',
            'name' => $attachment['name'],
            'contentType' => $attachment['mime'],
            'contentBytes' => base64_encode($attachment['content']),
        ];

        if ($attachment['inline'] ?? false) {
            $payload['isInline'] = true;
            $payload['contentId'] = $attachment['cid'];
        }

        return $payload;
    }

    private function encode(string $mailbox): string
    {
        return rawurlencode($mailbox);
    }
}