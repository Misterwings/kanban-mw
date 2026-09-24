# Documentación de Kanban MW

Esta carpeta documenta la versión del sistema que existe en este repositorio. La documentación está dividida en dos perspectivas:

- [Manual de usuario](MANUAL_DE_USUARIO.md): guía práctica para iniciar sesión, consultar proyectos y operar el tablero según el perfil.
- [Documentación técnica](DOCUMENTACION_TECNICA.md): arquitectura, modelo de datos, reglas de negocio, configuración, operación, pruebas y limitaciones conocidas.

## Alcance

Kanban MW es una aplicación interna para gestionar proyectos, tareas, responsables, seguimiento y costos. La interfaz operativa es el panel Filament disponible en `/admin`; no existe una API pública ni un frontend separado.

## Lectura recomendada

- Usuarios finales: comenzar por el manual de usuario y consultar la sección de permisos cuando una acción no aparezca.
- Administradores: leer el manual completo y las secciones de configuración, correo y notificaciones de la documentación técnica.
- Desarrollo y mantenimiento: leer primero la arquitectura y las reglas de negocio; después revisar despliegue, pruebas y limitaciones.

## Fuente de verdad

Los documentos describen el comportamiento implementado en el código actual. Cuando una validación o una limitación depende de la implementación y no de una decisión de producto explícita, se marca como nota técnica para evitar confundirla con una promesa funcional.
