<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recordatorio de tareas</title>
</head>
<body style="font-family: Arial, sans-serif; color: #172033; line-height: 1.5;">
    <h1 style="color: #b45309;">Recordatorio de tareas pendientes</h1>
    <p>Hola, {{ $recipient->name }}. Estas tareas siguen pendientes al {{ $reminderDate->format('d/m/Y') }}:</p>
    @foreach ($tasks as $task)
        <section style="margin: 20px 0; padding: 16px; border: 1px solid #eadfca; border-radius: 10px;">
            <h2 style="margin-top: 0;">{{ $task->project->name }}</h2>
            <p><strong>Tarea:</strong> {{ $task->description }}</p>
            <p><strong>Vence:</strong> {{ $task->end_date?->format('d/m/Y') }}</p>
            <p><strong>Estado:</strong> {{ $task->status->label() }}</p>
            <a href="{{ \App\Filament\Pages\ProjectBoard::getUrl(['project' => $task->project_id]) }}" style="color: #b45309;">Abrir tablero</a>
        </section>
    @endforeach
</body>
</html>
