# Manual de usuario

## 1. Sobre este manual

Este manual explica el uso de Kanban MW desde el panel web. Está escrito para administradores, colaboradores y observadores. Las opciones visibles pueden variar según el perfil, la relación del usuario con cada proyecto y el estado del proyecto.

## 2. Acceso al sistema

1. Abra la dirección de la instalación y entre a `/admin`.
2. Escriba su correo electrónico.
3. Escriba su contraseña.
4. Seleccione la opción de inicio de sesión.

Solo pueden acceder usuarios activos con un perfil válido. Si la cuenta fue desactivada o la contraseña se olvidó, solicite ayuda a un administrador; el flujo actual no ofrece una gestión autónoma documentada de cuentas.

En el entorno local de demostración existen estas cuentas, todas con contraseña `password`:

| Perfil | Correo |
| --- | --- |
| Administrador | `admin@kanban.test` |
| Colaborador | `colaborador@kanban.test` |
| Observador | `observador@kanban.test` |

Estas credenciales son exclusivamente para desarrollo/testing. No deben usarse en producción.

## 3. Perfiles y permisos

| Acción | Administrador | Colaborador | Observador |
| --- | --- | --- | --- |
| Ver todos los proyectos | Sí | No, solo proyectos relacionados | Sí |
| Crear proyecto | Sí | Sí | No |
| Editar encabezado de proyecto | Cualquiera | Solo propios | No |
| Clonar proyecto | Cualquiera | Solo propios | No |
| Crear y editar tareas | Cualquiera, si está activo | Solo en proyectos propios activos | No |
| Cambiar estado de tareas | Cualquiera, si está activo | Solo en proyectos propios activos | No |
| Ver proyecto | Sí | Si es propietario, líder o responsable | Sí |
| Añadir observación | En proyectos visibles | En proyectos visibles | En proyectos visibles |
| Añadir nota y costo | En proyectos activos visibles | En proyectos activos visibles | No |
| Añadir oportunidad de mejora | Proyectos finalizados | Si es propietario o líder | No |
| Administrar usuarios | Sí | No | No |

Importante: ser líder o responsable permite ver un proyecto, pero no convierte al usuario en editor. El control de tareas corresponde al propietario del proyecto y al administrador.

## 4. Conocer la pantalla principal

Después de iniciar sesión aparece el **Escritorio**:

- **Resumen de actividad**: tarjetas con métricas relevantes para el perfil.
- **Atención operativa** o **Mi agenda**: tareas vencidas, tareas próximas y proyectos que necesitan configuración.
- **Portafolio de proyectos** o **Mis proyectos**: listado de proyectos visibles, estado, líder, avance y fecha final planeada.
- **Cuenta**: opciones estándar del panel para la sesión del usuario.

La barra lateral contiene:

- **Trabajo > Proyectos**: consultar, crear y abrir proyectos.
- **Administración > Usuarios**: administrar cuentas; solo aparece para administradores activos.

Las cifras del escritorio usan la zona horaria configurada por la instalación. En la configuración predeterminada es `America/Bogota`.

## 5. Estados que debe conocer

### Proyectos

- **Creado**: todavía no tiene tareas.
- **En proceso**: tiene tareas y al menos una no está hecha.
- **Finalizado**: todas sus tareas están en estado Hecho.

El estado del proyecto se actualiza automáticamente; no se cambia con un selector manual.

### Tareas

- **Pendiente**: aún no inició.
- **En curso**: se está trabajando.
- **Hecho**: terminó.

Una tarea queda como borrador si no tiene fechas completas o responsables. Una tarea borrador no puede pasar a En curso o Hecho hasta completar esos datos.

## 6. Crear un proyecto

Disponible para administradores y colaboradores.

1. Abra **Trabajo > Proyectos**.
2. Seleccione **Crear proyecto**.
3. Complete **Nombre del proyecto**.
4. Seleccione **Fecha de inicio**.
5. Seleccione **Fecha final planeada**.
6. Seleccione un **Líder**.
7. Seleccione **Crear** o la acción equivalente de guardado.

El propietario será automáticamente el usuario que lo creó. El líder debe ser un administrador o colaborador activo. Si no se selecciona otro líder, el formulario propone al usuario actual.

Al guardar se abre directamente el tablero del proyecto.

## 7. Consultar y editar proyectos

### Lista de proyectos

La lista permite:

- Buscar por nombre.
- Ordenar por nombre, estado, cantidad de tareas y fechas.
- Ver líder, propietario, tareas, inicio, fin planeado y fecha de finalización.
- Abrir el tablero seleccionando la fila.
- Editar el encabezado mediante **Editar**, si el perfil tiene permiso.

### Encabezado del proyecto

Desde el tablero, el propietario o un administrador puede seleccionar **Editar encabezado** para modificar:

- Nombre.
- Fecha de inicio.
- Fecha final planeada.
- Líder.

El propietario no se cambia desde esta pantalla y el estado se calcula a partir de las tareas.

## 8. Trabajar en el tablero Kanban

### Abrir el tablero

Abra **Trabajo > Proyectos** y seleccione una fila. También puede entrar desde una tarjeta del escritorio o desde un enlace de correo.

El encabezado muestra:

- Nombre del proyecto.
- Estado.
- Líder.
- Propietario.
- Fechas planeadas.
- Acciones disponibles para su perfil.

### Crear una tarea

Disponible para el propietario del proyecto y administradores mientras el proyecto no esté finalizado.

1. Seleccione **+ Nueva tarea**.
2. Escriba la **Descripción** o resultado esperado.
3. Indique **Fecha de inicio**.
4. Indique **Fecha final**.
5. En **Responsables**, seleccione una o varias personas.
6. Mantenga presionada la tecla `Ctrl` en Windows/Linux o `Cmd` en macOS para seleccionar varios responsables.
7. Seleccione **Guardar tarea**.

La fecha final no puede ser anterior a la fecha de inicio. Los responsables deben ser usuarios activos con perfil Administrador o Colaborador.

Al guardar, la tarea aparece en **Pendiente**. Cada nuevo responsable puede recibir un correo de asignación cuando el worker procese la cola.

### Editar una tarea

1. Ubique la tarjeta de la tarea.
2. Seleccione **Editar**.
3. Cambie descripción, fechas o responsables.
4. Seleccione **Guardar tarea**.

En la línea de tiempo también puede seleccionar la barra de una tarea programada para abrir su edición.

### Cambiar el estado

Hay dos formas:

- Seleccionar el estado actual en la lista desplegable de la tarjeta.
- Arrastrar la tarjeta a otra columna.

Las columnas son **Pendiente**, **En curso** y **Hecho**. El arrastre cambia el estado, no reordena las tarjetas.

Cuando la última tarea pasa a Hecho, el proyecto se marca automáticamente como Finalizado. En un proyecto finalizado las tareas quedan bloqueadas y no se muestran controles para moverlas.

## 9. Usar la línea de tiempo

1. Abra el proyecto.
2. Seleccione **Línea de tiempo** en el selector de vistas.
3. Consulte el rango, duración estimada y tareas programadas.
4. Use el desplazamiento horizontal si el calendario es amplio.

La escala cambia automáticamente:

- Días para periodos de hasta 31 días.
- Semanas para periodos de 32 a 120 días.
- Meses para periodos mayores.

La línea vertical **Hoy** aparece cuando la fecha actual está dentro del rango. Las tareas sin un periodo completo aparecen en **Tareas por programar** y no se dibujan como barras. Si tiene permiso, seleccione **Programar** para completar sus fechas.

## 10. Registrar seguimiento

### Observación

Una observación sirve para anotar hallazgos, comentarios o información visible para el equipo.

1. Abra el proyecto.
2. En **Seguimiento**, escriba en **Nueva observación**.
3. Seleccione **Guardar observación**.

Puede hacerlo cualquier usuario que pueda ver el proyecto, incluido un observador. El máximo es 5.000 caracteres y también está permitido después del cierre.

### Nota de seguimiento

Una nota se usa para información operativa durante el proyecto.

1. Abra un proyecto activo.
2. Escriba en **Nota de seguimiento**.
3. Seleccione **Guardar nota**.

Solo está disponible para administradores y colaboradores con acceso al proyecto. No aparece en proyectos finalizados.

Las entradas muestran tipo, autor y fecha. Actualmente no se pueden editar ni eliminar.

## 11. Registrar y consultar costos

Administradores y colaboradores con acceso pueden registrar costos mientras el proyecto esté activo.

1. En el proyecto, vaya a **Costos**.
2. Introduzca el **Valor (COP)**, mayor que cero.
3. Seleccione la **Fecha**.
4. Escriba una **Descripción** de hasta 255 caracteres.
5. Seleccione **Registrar costo**.

El sistema muestra cada costo con descripción, fecha, usuario e importe, y calcula el **Total** acumulado en pesos colombianos. No existe edición ni eliminación de costos desde la pantalla actual.

## 12. Registrar oportunidades de mejora

Esta sección aparece cuando el proyecto está **Finalizado**.

- Administradores: pueden registrar la retroalimentación.
- Colaboradores: pueden hacerlo si son propietarios o líderes del proyecto.
- Observadores y demás usuarios: solo pueden leer el texto guardado.

1. Abra el proyecto finalizado.
2. En **Oportunidades de mejora**, escriba qué debería hacerse diferente en próximos proyectos.
3. Seleccione **Guardar retroalimentación**.

El campo admite hasta 10.000 caracteres.

## 13. Clonar un proyecto

El propietario o un administrador puede seleccionar **Clonar Proyecto** desde el encabezado del tablero.

La copia conserva:

- Nombre con el sufijo ` (Copia)`.
- Descripción y orden de las tareas.

La copia no conserva:

- Fechas del proyecto.
- Líder.
- Fechas de tareas.
- Responsables.
- Estados y fechas de finalización.
- Comentarios y notas.
- Costos.
- Oportunidades de mejora.

### Configurar una copia

1. Después de clonar, revise el aviso de copia pendiente de configurar.
2. Seleccione **Editar encabezado**.
3. Complete fechas y líder.
4. Vuelva al tablero.
5. Edite cada tarea para asignar fechas y responsables.
6. Mueva las tareas de Pendiente cuando estén listas para iniciar.

No se pueden iniciar tareas clonadas incompletas. Una copia con tareas puede mostrar temporalmente el estado En proceso hasta que se normalice su planificación; el aviso de configuración sigue siendo la referencia práctica.

## 14. Administrar usuarios

Solo disponible para administradores activos.

### Crear un usuario

1. Abra **Administración > Usuarios**.
2. Seleccione **Crear usuario**.
3. Complete nombre, correo, perfil y estado activo.
4. Introduzca una contraseña de al menos 8 caracteres.
5. Repita la contraseña en el campo de confirmación.
6. Guarde.

El correo debe ser único. Para que una persona pueda ser líder o responsable debe estar activa y tener perfil Administrador o Colaborador.

### Editar un usuario

1. Abra **Administración > Usuarios**.
2. Seleccione **Editar** en la fila correspondiente.
3. Modifique datos, perfil o estado.
4. Para conservar la contraseña actual, deje vacíos los campos de contraseña.
5. Para cambiarla, complete contraseña y confirmación.
6. Guarde.

Desactivar un usuario impide su acceso y evita nuevas asignaciones, pero no elimina sus tareas históricas ni sus asignaciones existentes.

### Elegir el perfil correcto

- **Administrador**: personal que administra usuarios y necesita control global.
- **Colaborador**: personal que crea proyectos, lidera o ejecuta tareas.
- **Observador**: personal que necesita consultar el portafolio y registrar observaciones, sin modificar la operación.

## 15. Correos automáticos

### Correo de asignación

Se genera cuando se asigna una tarea nueva o se añade un responsable a una tarea existente. El correo incluye proyecto, descripción, periodo, estado, responsables y enlace al tablero.

### Recordatorio diario

Se envía una vez por día, en la hora configurada por la instalación, para tareas abiertas cuya ventana incluye la fecha actual. Solo se consideran tareas de proyectos no finalizados.

En una instalación local con `MAIL_MAILER=log`, revise `storage/logs/laravel.log`. En producción, confirme la configuración SMTP y que el worker de cola esté activo.

## 16. Problemas frecuentes

### No veo un proyecto

- Un administrador y un observador activo ven todos los proyectos.
- Un colaborador solo ve proyectos propios, liderados o con una tarea asignada.
- Verifique que su cuenta esté activa.
- Solicite al propietario que lo asigne a una tarea o que le confirme como líder.

### Veo el proyecto, pero no puedo crear o editar tareas

Ser responsable o líder da visibilidad, pero no permiso de edición. Debe ser propietario del proyecto o administrador, y el proyecto no puede estar finalizado.

### No puedo mover una tarea clonada

Complete sus fechas de inicio y final y seleccione al menos un responsable activo. Las tareas incompletas solo pueden permanecer en Pendiente.

### No aparece la nota o el formulario de costos

Las notas y costos requieren un proyecto activo, visibilidad y perfil Administrador o Colaborador.

### No aparece la oportunidad de mejora

Solo aparece al finalizar el proyecto. Para escribirla debe ser administrador, propietario o colaborador líder.

### La línea de tiempo está vacía

Complete las fechas de inicio y final del proyecto o de las tareas. Las tareas sin un periodo completo se muestran aparte y no se dibujan como barras.

### No recibí un correo

- La asignación se procesa mediante cola; confirme que el worker esté ejecutándose.
- Revise spam y el correo configurado en su usuario.
- En local, revise `storage/logs/laravel.log`.
- Para recordatorios, confirme que la tarea esté abierta, dentro de sus fechas y que la persona siga asignada.

### Una tarea aparece fuera del periodo del proyecto

La aplicación actualmente permite que una tarea tenga fechas anteriores o posteriores a las fechas del proyecto. La línea de tiempo amplía el rango para mostrarla.

## 17. Recomendaciones de uso

- Defina líder y fechas del proyecto antes de crear tareas.
- Use descripciones orientadas a un resultado verificable.
- Asigne responsables activos desde el principio para activar visibilidad y correos.
- Mantenga las fechas de tareas coherentes con el proyecto aunque el sistema no las fuerce.
- Use Observación para hallazgos generales y Nota para seguimiento operativo.
- Registre la descripción de cada costo con suficiente contexto.
- Cierre tareas únicamente cuando el trabajo esté terminado; el cierre del proyecto es automático.
- Después de clonar, trate la copia como un proyecto nuevo y revise todas sus tareas.
