@extends('emails.layouts.hybrid', ['title' => __('emails.ticket.description.updated_body', ['id' => $ticket->id])])

@section('content')
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding: 20px;">
                <x-emails::h1>
                    {{ __('emails.ticket.description.greeting', ['name' => $user->userName]) }}
                </x-emails::h1>

                <x-emails::p>
                    {{ __('emails.ticket.description.updated_body', ['id' => $ticket->id]) }}
                </x-emails::p>

                @if($isStatusChanged)
                    <x-emails::p>
                        {!! __('emails.ticket.description.status_changed_to', ['status' => '<strong>' . e($status) . '</strong>']) !!}
                    </x-emails::p>
                @endif

                @if($ticketMessage)
                    <x-emails::p>
                        {!! __('emails.ticket.description.comment_added', ['name' => '<strong>' . e($ticketMessage->user->userName) . '</strong>']) !!}
                    </x-emails::p>

                    @if(trim($ticketMessage->text))
                        <x-emails::p>
                            {!! nl2br(e($ticketMessage->text)) !!}
                        </x-emails::p>
                    @endif
                @endif

                <x-emails::hr />

                <x-emails::p>
                    {{ __('emails.ticket.description.footer_text') }}
                </x-emails::p>
            </td>
        </tr>
    </table>
@endsection

