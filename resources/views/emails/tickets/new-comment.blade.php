@extends('emails.layouts.hybrid', ['title' => 'Создана новая заявка.'])

@section('content')
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding: 20px;">
                <x-emails::h1>
                    Здравствуйте, {{ $user->userName }}.
                </x-emails::h1>

                <x-emails::p>
                    Новый комментарий в заявке #{{ $ticket->id }}
                </x-emails::p>

                <x-emails::p>
                    Пользователь <strong>{{ $ticketMessage->user->userName }}</strong> добавил(а) сообщение.
                </x-emails::p>

                @if(trim($ticketMessage->text))
                    <x-emails::p>
                        {!! nl2br(e($ticketMessage->text)) !!}
                    </x-emails::p>
                @endif

                <x-emails::hr />

                <x-emails::p>
                    Вы получили это письмо, так как являетесь сотрудником ответственного отдела.
                </x-emails::p>
            </td>
        </tr>
    </table>
@endsection

