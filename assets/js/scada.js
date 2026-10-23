/**
 * PROYECTO MAPAF - Cliente SCADA Táctico Asíncrono (Vanilla JavaScript)
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 */

const SCADA = {
    pollIntervalMs: 3000,
    currentOperator: null,
    isEsdActive: false,

    init() {
        this.bindEvents();
        this.fetchOperatorInfo();
        this.pollData();
        setInterval(() => this.pollData(), this.pollIntervalMs);
    },

    bindEvents() {
        // Selector de operador en cabecera
        const opSelect = document.getElementById('operatorSelect');
        if (opSelect) {
            opSelect.addEventListener('change', (e) => {
                this.switchOperator(e.target.value);
            });
        }

        // Botón superior de Parada de Emergencia ESD-1
        const btnEsdTop = document.getElementById('btnEsdTop');
        if (btnEsdTop) {
            btnEsdTop.addEventListener('click', () => {
                this.openEsdModal();
            });
        }

        // Botón de rearme de ESD-1 en el banner
        const btnResetEsd = document.getElementById('btnResetEsd');
        if (btnResetEsd) {
            btnResetEsd.addEventListener('click', () => {
                this.resetEsd();
            });
        }
    },

    showToast(message, isError = false) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = 'toast ' + (isError ? 'toast-error' : '');
        toast.textContent = message;
        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    },

    async fetchOperatorInfo() {
        try {
            const res = await fetch('api/auth.php');
            const data = await res.json();
            if (data.success && data.current_user) {
                this.currentOperator = data.current_user;
                const opSelect = document.getElementById('operatorSelect');
                if (opSelect && data.users) {
                    opSelect.innerHTML = data.users.map(u => `
                        <option value="${u.codigo_empleado}" ${u.codigo_empleado === data.current_user.codigo_empleado ? 'selected' : ''}>
                            ${u.codigo_empleado} · ${u.nombre_completo} (${u.rol_nombre})
                        </option>
                    `).join('');
                }
            }
        } catch (e) {
            console.warn('Error cargando información de operador:', e);
        }
    },

    async switchOperator(code) {
        try {
            const res = await fetch('api/auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ codigo_empleado: code })
            });
            const data = await res.json();
            if (data.success) {
                this.currentOperator = data.user;
                this.showToast(data.message);
                this.pollData();
            } else {
                this.showToast(data.error || 'Error al cambiar operador', true);
            }
        } catch (e) {
            this.showToast('Fallo de conexión al conmutar operador', true);
        }
    },

    async pollData() {
        try {
            // 1. Estado ESD y Planta
            const esdRes = await fetch('api/esd.php');
            const esdData = await esdRes.json();
            if (esdData.success) {
                this.updateEsdState(esdData.state);
            }

            // 2. Telemetría y KPIs
            const telRes = await fetch('api/telemetry.php');
            const telData = await telRes.json();
            if (telData.success) {
                this.updateTelemetryUi(telData);
            }

            // 3. Pozos
            const wellsRes = await fetch('api/wellheads.php');
            const wellsData = await wellsRes.json();
            if (wellsData.success) {
                this.updateWellsUi(wellsData.data);
            }

            // 4. Alarmas
            const alarmsRes = await fetch('api/alarms.php');
            const alarmsData = await alarmsRes.json();
            if (alarmsData.success) {
                this.updateAlarmsUi(alarmsData);
            }
        } catch (e) {
            console.warn('Error en sondeo de telemetría:', e);
        }
    },

    updateEsdState(state) {
        this.isEsdActive = !!state.esd_triggered;
        const banner = document.getElementById('esdAlertBanner');
        const triggerTime = document.getElementById('esdTriggerTime');

        if (banner) {
            if (this.isEsdActive) {
                banner.classList.add('active');
                if (triggerTime) triggerTime.textContent = state.esd_trigger_time || '--:--:--';
            } else {
                banner.classList.remove('active');
            }
        }
    },

    updateTelemetryUi(data) {
        // Actualizar KPIs superiores
        const kpiFlow = document.getElementById('topKpiFlow');
        if (kpiFlow && data.kpis) {
            kpiFlow.textContent = Number(data.kpis.total_production).toLocaleString() + ' BPD';
        }
        const kpiWells = document.getElementById('topKpiWells');
        if (kpiWells && data.kpis) {
            kpiWells.textContent = `${data.kpis.active_wells_count} / ${data.kpis.total_wells_count}`;
        }
        const kpiPress = document.getElementById('topKpiPressure');
        if (kpiPress && data.kpis) {
            kpiPress.textContent = data.kpis.avg_tubing_pressure + ' PSI';
        }

        // Actualizar tarjetas de telemetría en el dashboard
        if (data.metrics) {
            data.metrics.forEach(m => {
                const valEl = document.getElementById(`val-${m.id}`);
                const badgeEl = document.getElementById(`badge-${m.id}`);
                if (valEl) valEl.textContent = Number(m.valor).toFixed(1);
                if (badgeEl) {
                    badgeEl.className = `badge-status badge-${m.severidad}`;
                    badgeEl.textContent = m.severidad.toUpperCase();
                }
            });
        }
    },

    updateWellsUi(wells) {
        wells.forEach(w => {
            const chokeValEl = document.getElementById(`chokeVal-${w.id}`);
            const chokeSlider = document.getElementById(`chokeSlider-${w.id}`);
            const ssvBtn = document.getElementById(`ssvBtn-${w.id}`);
            const statusBadge = document.getElementById(`wellStatus-${w.id}`);

            if (chokeValEl) chokeValEl.textContent = Number(w.apertura_choke).toFixed(1) + '%';
            if (chokeSlider && document.activeElement !== chokeSlider) {
                chokeSlider.value = w.apertura_choke;
            }

            if (statusBadge) {
                let badgeClass = 'badge-online';
                if (w.estado === 'DISPARADO') badgeClass = 'badge-tripped';
                else if (w.estado === 'ESTRANGULADO') badgeClass = 'badge-throttled';
                else if (w.estado === 'MANTENIMIENTO') badgeClass = 'badge-maintenance';
                statusBadge.className = `badge-status ${badgeClass}`;
                statusBadge.textContent = w.estado;
            }

            if (ssvBtn) {
                const isOpen = (w.valvula_ssv_abierta == 1);
                ssvBtn.className = `btn-well ${isOpen ? 'btn-ssv-open' : 'btn-ssv-closed'}`;
                ssvBtn.textContent = isOpen ? 'SSV: ABIERTA' : 'SSV: CERRADA';
            }
        });
    },

    updateAlarmsUi(data) {
        const badgePending = document.getElementById('badgePendingAlarms');
        if (badgePending && data.counts) {
            badgePending.textContent = data.counts.total;
            badgePending.style.display = data.counts.total > 0 ? 'inline-block' : 'none';
        }
    },

    // Maniobra: Ajustar Choke
    async setChoke(wellId, val) {
        try {
            const res = await fetch('api/wellheads.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: wellId,
                    chokeOpening: parseFloat(val),
                    operatorCode: this.currentOperator?.codigo_empleado,
                    operatorName: this.currentOperator?.nombre_completo
                })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message);
                this.pollData();
            } else {
                this.showToast(data.error || 'Maniobra rechazada', true);
            }
        } catch (e) {
            this.showToast('Error de red al aplicar consigna de Choke', true);
        }
    },

    // Maniobra: Conmutar Válvula SSV
    async toggleSsv(wellId, currentOpen) {
        const nextState = !currentOpen;
        try {
            const res = await fetch('api/wellheads.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: wellId,
                    ssvOpen: nextState,
                    operatorCode: this.currentOperator?.codigo_empleado,
                    operatorName: this.currentOperator?.nombre_completo
                })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message);
                this.pollData();
            } else {
                this.showToast(data.error || 'Maniobra denegada', true);
            }
        } catch (e) {
            this.showToast('Error de red al cambiar estado de SSV', true);
        }
    },

    // Maniobra: Rearmar Pozo Disparado
    async resetWell(wellId) {
        try {
            const res = await fetch('api/wellheads.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: wellId,
                    action: 'reset',
                    operatorCode: this.currentOperator?.codigo_empleado,
                    operatorName: this.currentOperator?.nombre_completo
                })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message);
                this.pollData();
            } else {
                this.showToast(data.error || 'Error rearmando pozo', true);
            }
        } catch (e) {
            this.showToast('Error de red en rearme', true);
        }
    },

    // Modal de Reconocimiento de Alarmas (ANSI/ISA-18.2)
    openAckModal(alarmId, tag) {
        const modal = document.getElementById('ackAlarmModal');
        if (!modal) return;
        document.getElementById('ackAlarmId').value = alarmId;
        document.getElementById('ackAlarmTagText').textContent = tag;
        document.getElementById('ackOperatorName').value = this.currentOperator?.nombre_completo || '';
        document.getElementById('ackReason').value = '';
        modal.classList.add('active');
    },

    closeAckModal() {
        const modal = document.getElementById('ackAlarmModal');
        if (modal) modal.classList.remove('active');
    },

    async confirmAlarmAck() {
        const alarmId = document.getElementById('ackAlarmId').value;
        const operatorName = document.getElementById('ackOperatorName').value.trim();
        const reason = document.getElementById('ackReason').value.trim();

        if (!operatorName) {
            this.showToast('Debe ingresar el nombre del operador (Regla ISA-18.2)', true);
            return;
        }

        if (reason.length < 8) {
            this.showToast('La justificación técnica debe tener al menos 8 caracteres explicativos', true);
            return;
        }

        try {
            const res = await fetch('api/alarms.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: alarmId,
                    operatorCode: this.currentOperator?.codigo_empleado,
                    operatorName: operatorName,
                    reason: reason
                })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message);
                this.closeAckModal();
                this.pollData();
                setTimeout(() => location.reload(), 1000);
            } else {
                this.showToast(data.error || 'Acuse rechazado', true);
            }
        } catch (e) {
            this.showToast('Error de conexión al reconocer alarma', true);
        }
    },

    // Parada de Emergencia ESD-1
    openEsdModal() {
        const modal = document.getElementById('esdModal');
        if (modal) modal.classList.add('active');
    },

    closeEsdModal() {
        const modal = document.getElementById('esdModal');
        if (modal) modal.classList.remove('active');
    },

    async confirmEsdTrigger() {
        const reasonInput = document.getElementById('esdReason');
        const reason = reasonInput ? reasonInput.value.trim() : 'Disparo manual preventivo desde consola SCADA';

        try {
            const res = await fetch('api/esd.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'trigger',
                    operatorCode: this.currentOperator?.codigo_empleado,
                    operatorName: this.currentOperator?.nombre_completo,
                    reason: reason
                })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message, true);
                this.closeEsdModal();
                this.pollData();
            } else {
                this.showToast(data.error || 'Fallo en disparo ESD', true);
            }
        } catch (e) {
            this.showToast('Error de red al activar ESD-1', true);
        }
    },

    async resetEsd() {
        if (!confirm('¿Confirma el rearme del sistema de seguridad ESD-1? Se reabrirán las válvulas y la planta volverá a estado nominal.')) {
            return;
        }

        try {
            const res = await fetch('api/esd.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'reset',
                    operatorCode: this.currentOperator?.codigo_empleado,
                    operatorName: this.currentOperator?.nombre_completo
                })
            });
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message);
                this.pollData();
            } else {
                this.showToast(data.error || 'Fallo en rearme ESD', true);
            }
        } catch (e) {
            this.showToast('Error de red al rearmar ESD-1', true);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    SCADA.init();
});
