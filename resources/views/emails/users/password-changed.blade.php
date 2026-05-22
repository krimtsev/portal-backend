@extends('emails.layouts.hybrid', ['title' => 'Безопасность учетной записи'])

@section('content')
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding: 20px;">
                <x-emails::h1>
                    Здравствуйте, {{ $user->userName }}.
                </x-emails::h1>

                <x-emails::p>
                    Уведомляем вас о том, что пароль от вашей учетной записи был успешно изменен.
                </x-emails::p>

                <x-emails::hr />

                <x-emails::p>
                    <strong>Если вы не совершали этого действия, пожалуйста, немедленно свяжитесь с системным администратором.</strong>
                </x-emails::p>
            </td>
        </tr>
    </table>
@endsection
