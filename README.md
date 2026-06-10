# notifica-dev/sdk

SDK PHP oficial do [notifica.dev](https://notifica.dev) — envio de notificações push, e-mail e web a partir do seu backend PHP.

## Requisitos

- PHP 8.1 ou superior
- [Composer](https://getcomposer.org)

## Instalação

```bash
composer require notifica-dev/sdk
```

## Configuração

Instancie o `NotificaClient` com o seu access token e a URL base da API:

```php
use Notifica\NotificaClient;

$notifica = new NotificaClient(
    accessToken: env('NOTIFICA_ACCESS_TOKEN'),
    baseUrl: env('NOTIFICA_API_URL'),
);
```

> O `baseUrl` é opcional. Se omitido, aponta para `https://api.notifica.dev`.

---

## Notificações

O SDK usa uma abordagem baseada em classes para envio de notificações. Cada evento do seu sistema vira uma classe que declara os canais e o conteúdo da mensagem.

### Criando uma notificação

```php
use Notifica\Notification;
use Notifica\Target;
use Notifica\Messages\PushMessage;
use Notifica\Messages\EmailMessage;
use Notifica\Messages\WebMessage;

class PedidoEnviadoNotification extends Notification
{
    public function __construct(private Pedido $pedido) {}

    // Canais que serão utilizados
    public function via(): array
    {
        return ['push', 'email'];
    }

    // Destinatários (opcional — pode ser sobrescrito no send())
    public function targets(): array
    {
        return [
            Target::customer("users:{$this->pedido->userId}"),
        ];
    }

    // Agendamento (opcional)
    public function scheduledAt(): ?\DateTimeInterface
    {
        return null; // envio imediato
    }

    // Chave de idempotência (opcional)
    // O SDK sufixará o canal automaticamente: "pedido-123-push", "pedido-123-email"
    public function code(): ?string
    {
        return "pedido-enviado-{$this->pedido->id}";
    }

    public function toPush(): PushMessage
    {
        return PushMessage::make()
            ->title('Seu pedido foi enviado!')
            ->body("Pedido #{$this->pedido->id} está a caminho.");
    }

    public function toEmail(): EmailMessage
    {
        return EmailMessage::make()
            ->subject("Pedido #{$this->pedido->id} enviado!")
            ->body('Seu pedido está a caminho. Acompanhe a entrega pelo link abaixo.')
            ->to($this->pedido->emailCliente)
            ->from('pedidos@minhaapp.com.br', 'Minha App');
    }

    public function toWeb(): WebMessage
    {
        return WebMessage::make()
            ->title('Pedido enviado!')
            ->body("Pedido #{$this->pedido->id} está a caminho.")
            ->data(['pedido_id' => $this->pedido->id]);
    }
}
```

### Enviando

```php
// Destinatários definidos na própria classe
$notifica->send(new PedidoEnviadoNotification($pedido));

// Destinatários sobrescritos na chamada
$notifica->send(
    new PedidoEnviadoNotification($pedido),
    Target::customer("users:{$user->id}"),
);
```

O método `send()` retorna um array com a resposta da API para cada canal:

```php
$resultados = $notifica->send(new PedidoEnviadoNotification($pedido));

$resultados['push']['id'];  // ID do intent de push
$resultados['email']['id']; // ID do intent de e-mail
```

---

## Destinatários (Target)

Use a classe `Target` para definir para quem a notificação será enviada:

```php
use Notifica\Target;

Target::customer('users:123')          // cliente pelo ID externo
Target::allDevices()                   // todos os dispositivos do app
Target::device('installation-id')      // dispositivo específico
Target::emailAddress('user@email.com') // endereço de e-mail direto
Target::code('usuarios-premium')       // dispositivos com este código
Target::tag('premium', 'beta')         // dispositivos com estas tags
```

---

## Dispositivos

### Registrar instalação web

```php
$notifica->devices->registerWeb(
    installationKey: $installationKey,
    customerExternalId: "users:{$user->id}",
    name: $user->name,
    email: $user->email,
);
```

> Idempotente — conflitos 409 são ignorados silenciosamente.

### Registrar instalação mobile (iOS / Android)

```php
$notifica->devices->registerMobile(
    platform: 'android',         // 'android' ou 'ios'
    pushProvider: 'expo',
    pushToken: $expoPushToken,
    customerExternalId: "users:{$user->id}",
    customerName: $user->name,
    tags: ['premium'],
);
```

---

## Token de sessão do cliente

Necessário para autenticar conexões WebSocket e requisições de inbox no frontend.

```php
$token = $notifica->customerTokens->mint("users:{$user->id}");

$token->token;     // string — token de sessão
$token->expiresAt; // DateTimeImmutable — data de expiração
```

### Iniciar uma sessão web em uma chamada

Registra a instalação web e gera o token de uma vez — ideal para o endpoint de sessão
consumido pelo frontend:

```php
$token = $notifica->startWebSession(
    installationKey: $installationKey,
    customerExternalId: "users:{$user->id}",
    name: $user->name,
    email: $user->email,
);
```

Os objetos de resposta implementam `JsonSerializable` (emitindo `snake_case`), então podem ser
retornados diretamente de um controller:

```php
return response()->json($token); // { "token": "...", "expires_at": "..." }
```

---

## Inbox

### Listar notificações

`list()` retorna um `Notifica\Responses\InboxPage` (também serializável para JSON):

```php
$inbox = $notifica->inbox->list(
    customerExternalId: "users:{$user->id}",
    page: 1,
    perPage: 20,
    readStatus: 'unread', // 'all', 'read' ou 'unread'
);

$inbox->total;
$inbox->hasNextPage;

foreach ($inbox->items as $notification) {
    $notification->id;
    $notification->title;     // ?string
    $notification->body;
    $notification->data;      // payload definido pela aplicação (o SDK não interpreta)
    $notification->createdAt; // DateTimeImmutable
    $notification->isRead();
}

return response()->json($inbox); // { "data": [...], "meta": {...} }
```

### Marcar como lida / não lida

Retornam o `Notifica\Responses\CustomerNotification` atualizado:

```php
$notification = $notifica->inbox->markAsRead($notificationId, "users:{$user->id}");
$notification = $notifica->inbox->markAsUnread($notificationId, "users:{$user->id}");
```

---

## Tempo real (inbox)

O contrato do canal de tempo real é exposto em `Notifica\Realtime` para evitar strings mágicas
no frontend:

```php
use Notifica\Realtime;

Realtime::NAMESPACE;            // "/customer-events" — namespace Socket.IO
Realtime::EVENT_INBOX_UPDATED;  // "inbox.updated" — recarregue a inbox ao receber este evento
```

Conecte um cliente Socket.IO a `{baseUrl}` + `Realtime::NAMESPACE`, autenticado com o token de
sessão, e recarregue a inbox quando `Realtime::EVENT_INBOX_UPDATED` for emitido.

---

## Tratamento de erros

Todas as chamadas à API lançam `Notifica\Exceptions\NotificaException` em caso de erro HTTP:

```php
use Notifica\Exceptions\NotificaException;

try {
    $notifica->send(new PedidoEnviadoNotification($pedido));
} catch (NotificaException $e) {
    $e->statusCode; // código HTTP (ex: 422, 503)
    $e->response;   // corpo da resposta como array
    $e->getMessage();
}
```

---

## Licença

MIT
