<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Tareas asignadas</title>
</head>
<body style="font-family: Arial, sans-serif; color: #172033; line-height: 1.5;">
    <h1 style="color: #0f766e;">Hola, {{ $recipient->name }}</h1>
    <p>Se te asignaron las siguientes tareas en Kanban MW:</p>
    @foreach ($tasks as $task)
        <section style="margin: 20px 0; padding: 16px; border: 1px solid #dce5e5; border-radius: 10px;">
            <h2 style="margin-top: 0;">{{ $task->project->name }}</h2>
            <p><strong>Tarea:</strong> {{ $task->description }}</p>
            <p><strong>Periodo:</strong> {{ $task->start_date?->format('d/m/Y') }} a {{ $task->end_date?->format('d/m/Y') }}</p>
            <p><strong>Estado:</strong> {{ $task->status->label() }}</p>
            <p><strong>Responsables:</strong> {{ $task->assignees->pluck('name')->join(', ') }}</p>
            <a href="{{ \App\Filament\Pages\ProjectBoard::getUrl(['project' => $task->project_id]) }}" style="color: #0f766e;">Abrir tablero</a>
        </section>
    @endforeach
</body>
</html>
