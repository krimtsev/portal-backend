@extends('emails.layouts.basic')

@section('content')
    <h1>Здравствуйте.</h1>

    <div style="margin-bottom: 24px;">
        Создана новая заявка <strong>#{{ $ticket->id }}</strong>.
    </div>

    <div style="font-size: 14px; line-height: 20px;">
        <div style="margin-bottom: 8px;"><strong>Тема:</strong> {{ $ticket->title }}</div>
        <div style="margin-bottom: 8px;"><strong>Тип:</strong> {{ $ticket->type }}</div>
        <div style="margin-bottom: 16px;"><strong>Ссылка:</strong> {{ config('app.url') }}/tickets/{{ $ticket->id }}</div>
    </div>
@endsection
