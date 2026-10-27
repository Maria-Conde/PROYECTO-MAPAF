# PROYECTO MAPAF - REGLAS PERMANENTES DE DESARROLLO Y SINCRONIZACIÓN

## INSTRUCCIÓN PERMANENTE: SINCRONIZACIÓN OBLIGATORIA CON GITHUB

Esta instrucción es de máxima prioridad y debe cumplirse estrictamente en **todas las tareas** que involucren cambios en el proyecto. El usuario no debe tener que pedir guardar, hacer commit ni subir cambios manualmente.

---

### FLUJO DE TRABAJO AUTOMATIZADO TRAS CADA MODIFICACIÓN

Cada vez que el agente cree, edite, renombre o elimine archivos en el proyecto:

1. **Auditoría de Cambios**:
   - Detectar todos los archivos modificados con `git status --porcelain`.
2. **Control de Seguridad y Secretos**:
   - Comprobar que no se incluyan archivos `.env`, `.env.*` (excepto `.env.example`), llaves privadas (`*.pem`, `*.key`), certificados, tokens ni credenciales hardcodeadas.
   - Respetar siempre `.gitignore`.
3. **Verificación de Errores y Sintaxis**:
   - Para archivos PHP modificados, ejecutar `C:\xampp\php\php.exe -l <archivo>` para asegurar que no contengan errores de sintaxis antes de versionar.
4. **Guardado en Git (Commit)**:
   - Crear un commit con un mensaje semántico y descriptivo (ej: `feat(...)`, `fix(...)`, `refactor(...)`, `style(...)`).
   - Nunca usar mensajes vacíos o genéricos sin contexto.
5. **Subida Inmediata (Git Push)**:
   - Subir de inmediato a GitHub mediante `powershell -ExecutionPolicy Bypass -File .\sync_github.ps1 -Message "<mensaje>"` o `git push origin main`.
6. **Verificación de Integridad Remota**:
   - Confirmar que `git rev-parse HEAD` coincide con la rama remota `origin/main`.
7. **Confirmación Obligatoria al Usuario**:
   - Al finalizar la respuesta de cada turno con cambios, incluir siempre el bloque de confirmación:
     * **Estado**: Sincronización exitosa / Fallida / En espera.
     * **Archivos modificados**: Lista de archivos tocados.
     * **Commit creado**: Hash y mensaje.
     * **Rama actual**: `main`.
     * **Verificación de GitHub**: Estado de la rama remota.

---

### GESTIÓN DE ERRORES Y SEGURIDAD
- Si el push no puede realizarse por falta de credenciales o por conflictos no triviales:
  - Mantener todos los cambios locales a salvo en commits.
  - Explicar al usuario la causa exacta.
  - Nunca usar `git push --force` ni comandos destructivos (`git reset --hard` sobre ramas remotas compartidas).
