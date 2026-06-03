@extends('emails.layouts.hybrid', ['title' => __('emails.user.description.password_changed_title')])

@section('content')
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding: 20px;">
                <x-emails::h1>
                    {{ __('emails.user.description.greeting', ['name' => $user->userName]) }}
                </x-emails::h1>

                <x-emails::p>
                    {{ __('emails.user.description.password_changed_body') }}
                </x-emails::p>

                <x-emails::hr />

                <x-emails::p>
                    <strong>{{ __('emails.user.description.warning_text') }}</strong>
                </x-emails::p>
            </td>
        </tr>
    </table>
@endsection
