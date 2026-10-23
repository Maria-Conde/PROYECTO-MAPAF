<?php
/**
 * PROYECTO MAPAF - Layout: Pie de Página y Modales (Footer)
 * SENA ADSO - Ficha 3413988
 */
?>
</main>
</div>

<!-- Modal: Reconocimiento Formal de Alarma ANSI/ISA-18.2 -->
<div id="ackAlarmModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title">Reconocimiento Trazable ISA-18.2</h3>
            <button type="button" class="modal-close" onclick="SCADA.closeAckModal()">&times;</button>
        </div>
        <p style="font-size:0.82rem; color:var(--text-muted); margin-bottom:1rem;">
            Se registrará el acuse en la bitácora inmutable (21 CFR Part 11). El silenciamiento ciego está bloqueado.
        </p>
        
        <input type="hidden" id="ackAlarmId" value="">

        <div class="modal-form-group">
            <label class="modal-label">Alarma Seleccionada</label>
            <div id="ackAlarmTagText" style="font-family:var(--font-mono); color:var(--primary); font-weight:bold; font-size:1rem; padding:0.4rem 0;">-</div>
        </div>

        <div class="modal-form-group">
            <label class="modal-label" for="ackOperatorName">Operador Responsable (*)</label>
            <input type="text" id="ackOperatorName" class="modal-input" placeholder="Nombre completo del operador en consola">
        </div>

        <div class="modal-form-group">
            <label class="modal-label" for="ackReason">Justificación Técnica Obligatoria (* Mín. 8 caracteres)</label>
            <textarea id="ackReason" class="modal-textarea" rows="3" placeholder="Ej: Se verificó en campo la presión y se purgó la línea de venteo..."></textarea>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="SCADA.closeAckModal()">Cancelar</button>
            <button type="button" class="btn-confirm" onclick="SCADA.confirmAlarmAck()">Confirmar Acuse</button>
        </div>
    </div>
</div>

<!-- Modal: Confirmación Parada de Emergencia ESD-1 -->
<div id="esdModal" class="modal-overlay">
    <div class="modal-box" style="border-color:var(--danger);">
        <div class="modal-header">
            <h3 class="modal-title" style="color:var(--danger);">⚠️ Confirmar Disparo de Emergencia ESD-1</h3>
            <button type="button" class="modal-close" onclick="SCADA.closeEsdModal()">&times;</button>
        </div>
        <p style="font-size:0.85rem; color:#cbd5e1; margin-bottom:1rem; line-height:1.4;">
            Esta maniobra instrumentada aislará inmediatamente todos los cabezales de pozo de la estación: cerrará las válvulas SSV y reducirá los estranguladores al 0%.
        </p>

        <div class="modal-form-group">
            <label class="modal-label" for="esdReason">Motivo de Parada / Disparo</label>
            <input type="text" id="esdReason" class="modal-input" value="Disparo manual preventivo desde consola SCADA" placeholder="Describe la condición de emergencia">
        </div>

        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="SCADA.closeEsdModal()">Cancelar</button>
            <button type="button" class="btn-confirm" style="background:var(--danger); color:#fff;" onclick="SCADA.confirmEsdTrigger()">🚨 EJECUTAR PARADA ESD-1</button>
        </div>
    </div>
</div>

<!-- Contenedor de Alertas Toast -->
<div id="toast-container"></div>

<script src="assets/js/scada.js"></script>
</body>
</html>
