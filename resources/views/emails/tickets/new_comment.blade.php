<x-mail::message>
    # Новый комментарий в заявке #{{ $ticket->id }}

    Пользователь **{{ $comment->user->name }}** добавил сообщение:

    <x-mail::panel>
        {{ $comment->text }}
    </x-mail::panel>

    <x-mail::button :url="config('app.url') . '/tickets/' . $ticket->id">
        Ответить в системе
    </x-mail::button>

    Вы получили это письмо, так как являетесь сотрудником ответственного отдела.

    С уважением,<br>
    {{ config('app.name') }}
</x-mail::message>
