@extends('emails.layouts.hybrid', ['title' => __('emails.ticket.description.created_body', ['id' => $ticket->id])])

@section('content')
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding: 20px;">
                <x-emails::h1>
                    {{ __('emails.ticket.description.greeting', ['name' => $user->userName]) }}
                </x-emails::h1>

                <x-emails::p>
                    {{ __('emails.ticket.description.created_body', ['id' => $ticket->id]) }}
                </x-emails::p>

                <x-emails::p>
                    {{ __('emails.ticket.description.subject_title', ['title' => $ticket->title]) }}
                </x-emails::p>

                @if(trim($ticketMessage->text))
                    <x-emails::p style="margin: 0;">
                        {!! nl2br(e($ticketMessage->text)) !!}
                    </x-emails::p>
                @endif

                <x-emails::hr />

                <x-emails::p>
                    {{ __('emails.ticket.description.footer_text') }}
                </x-emails::p>
            </td>
        </tr>
    </table>
@endsection
