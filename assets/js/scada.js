/**
 * PROYECTO MAPAF - Cliente SCADA Táctico Asíncrono (Vanilla JavaScript)
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 */

const SCADA = {
    pollIntervalMs: 3000,
    currentOperator: null,
    isEsdActive: false,
    charts: {
        pressure: { canvas: null, ctx: null, history: [] },
        production: { canvas: null, ctx: null, history: [] }
    },

    init() {
        this.bindEvents();
        this.fetchOperatorInfo();
        this.initCharts();
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
        // Alimentar motor de gráficos en tiempo real
        this.addTelemetryDataPoint(data);
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
    },

    // ==========================================
    // MOTOR DE GRÁFICOS Y CURVAS DE TENDENCIA SCADA
    // ==========================================
    initCharts() {
        const cPres = document.getElementById('chartPressure');
        const cProd = document.getElementById('chartProduction');
        if (!cPres || !cProd) return;

        this.charts.pressure.canvas = cPres;
        this.charts.pressure.ctx = cPres.getContext('2d');
        this.charts.production.canvas = cProd;
        this.charts.production.ctx = cProd.getContext('2d');

        // Generar historial inicial realista para 20 muestras
        const now = Date.now();
        for (let i = 20; i >= 0; i--) {
            const t = new Date(now - i * 3000);
            const timeStr = t.toTimeString().split(' ')[0];
            const noise1 = (Math.sin(i * 0.45) * 18) + (Math.cos(i * 0.2) * 8);
            const noise2 = (Math.cos(i * 0.35) * 12) + (Math.sin(i * 0.15) * 5);
            const noiseProd = (Math.sin(i * 0.4) * 110) + (Math.cos(i * 0.25) * 45);

            this.charts.pressure.history.push({
                time: timeStr,
                whtp: 1842.0 + noise1,
                casing: 312.0 + noise2
            });

            this.charts.production.history.push({
                time: timeStr,
                rate: 14820.0 + noiseProd,
                target: 15000.0
            });
        }

        window.addEventListener('resize', () => {
            this.renderCharts();
        });

        this.renderCharts();
    },

    addTelemetryDataPoint(telData) {
        if (!this.charts.pressure.canvas) return;

        const timeStr = new Date().toTimeString().split(' ')[0];
        let whtpVal = 1842.0;
        let casingVal = 312.0;
        let prodVal = 14820.0;

        if (telData && telData.kpis) {
            if (telData.kpis.avg_tubing_pressure) whtpVal = parseFloat(telData.kpis.avg_tubing_pressure);
            if (telData.kpis.total_production) prodVal = parseFloat(telData.kpis.total_production);
        }

        if (telData && telData.metrics) {
            const mCasing = telData.metrics.find(m => m.tag && m.tag.includes('CASING'));
            if (mCasing) casingVal = parseFloat(mCasing.valor);
        }

        // Variación sutil de proceso de planta
        const jitter1 = (Math.random() * 3.2 - 1.6);
        const jitter2 = (Math.random() * 1.8 - 0.9);
        const jitterProd = (Math.random() * 25.0 - 12.5);

        this.charts.pressure.history.push({
            time: timeStr,
            whtp: whtpVal + jitter1,
            casing: casingVal + jitter2
        });
        if (this.charts.pressure.history.length > 24) {
            this.charts.pressure.history.shift();
        }

        this.charts.production.history.push({
            time: timeStr,
            rate: prodVal + jitterProd,
            target: 15000.0
        });
        if (this.charts.production.history.length > 24) {
            this.charts.production.history.shift();
        }

        this.renderCharts();
    },

    renderCharts() {
        this.drawPressureChart();
        this.drawProductionChart();
    },

    setupCanvas(canvas, ctx) {
        if (!canvas) return null;
        const rect = canvas.getBoundingClientRect();
        if (!rect.width || !rect.height) return null;
        const dpr = window.devicePixelRatio || 1;
        const w = Math.floor(rect.width);
        const h = Math.floor(rect.height);

        if (canvas.width !== Math.floor(w * dpr) || canvas.height !== Math.floor(h * dpr)) {
            canvas.width = Math.floor(w * dpr);
            canvas.height = Math.floor(h * dpr);
        }

        ctx.resetTransform();
        ctx.scale(dpr, dpr);
        ctx.clearRect(0, 0, w, h);
        return { w, h };
    },

    drawPressureChart() {
        const { canvas, ctx, history } = this.charts.pressure;
        if (!canvas || !ctx || history.length < 2) return;

        const dims = this.setupCanvas(canvas, ctx);
        if (!dims) return;
        const { w, h } = dims;

        const pad = { top: 28, bottom: 26, left: 45, right: 45 };
        const plotW = w - pad.left - pad.right;
        const plotH = h - pad.top - pad.bottom;

        // Escalas: WHTP (1700 - 2000 PSI) en Izquierda, Casing (200 - 450 PSI) en Derecha
        const minWhtp = 1700, maxWhtp = 2000;
        const minCas = 200, maxCas = 450;

        // Cuadrícula y Ticks
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.05)';
        ctx.lineWidth = 1;
        ctx.setLineDash([3, 4]);

        const steps = 4;
        for (let i = 0; i <= steps; i++) {
            const y = pad.top + (plotH / steps) * i;
            ctx.beginPath();
            ctx.moveTo(pad.left, y);
            ctx.lineTo(pad.left + plotW, y);
            ctx.stroke();

            // Etiquetas WHTP (Izquierda)
            const wVal = Math.round(maxWhtp - (i / steps) * (maxWhtp - minWhtp));
            ctx.font = '9px "JetBrains Mono", monospace';
            ctx.fillStyle = '#38bdf8';
            ctx.textAlign = 'right';
            ctx.fillText(wVal + '', pad.left - 6, y + 3);

            // Etiquetas Casing (Derecha)
            const cVal = Math.round(maxCas - (i / steps) * (maxCas - minCas));
            ctx.fillStyle = '#fbbf24';
            ctx.textAlign = 'left';
            ctx.fillText(cVal + '', pad.left + plotW + 6, y + 3);
        }
        ctx.setLineDash([]);

        // Función de mapeo X
        const getX = (idx) => pad.left + (idx / (history.length - 1)) * plotW;
        const getYw = (val) => pad.top + plotH - ((val - minWhtp) / (maxWhtp - minWhtp)) * plotH;
        const getYc = (val) => pad.top + plotH - ((val - minCas) / (maxCas - minCas)) * plotH;

        // 1. Curva WHTP con relleno degradado
        const gradWhtp = ctx.createLinearGradient(0, pad.top, 0, pad.top + plotH);
        gradWhtp.addColorStop(0, 'rgba(56, 189, 248, 0.22)');
        gradWhtp.addColorStop(1, 'rgba(56, 189, 248, 0.0)');

        ctx.beginPath();
        ctx.moveTo(getX(0), getYw(history[0].whtp));
        for (let i = 1; i < history.length; i++) {
            const prevX = getX(i - 1);
            const prevY = getYw(history[i - 1].whtp);
            const curX = getX(i);
            const curY = getYw(history[i].whtp);
            const cpX = (prevX + curX) / 2;
            ctx.bezierCurveTo(cpX, prevY, cpX, curY, curX, curY);
        }
        ctx.lineTo(getX(history.length - 1), pad.top + plotH);
        ctx.lineTo(getX(0), pad.top + plotH);
        ctx.closePath();
        ctx.fillStyle = gradWhtp;
        ctx.fill();

        // Trazo WHTP
        ctx.beginPath();
        ctx.moveTo(getX(0), getYw(history[0].whtp));
        for (let i = 1; i < history.length; i++) {
            const prevX = getX(i - 1);
            const prevY = getYw(history[i - 1].whtp);
            const curX = getX(i);
            const curY = getYw(history[i].whtp);
            const cpX = (prevX + curX) / 2;
            ctx.bezierCurveTo(cpX, prevY, cpX, curY, curX, curY);
        }
        ctx.strokeStyle = '#38bdf8';
        ctx.lineWidth = 2.2;
        ctx.shadowColor = 'rgba(56, 189, 248, 0.5)';
        ctx.shadowBlur = 6;
        ctx.stroke();
        ctx.shadowBlur = 0;

        // 2. Curva Casing
        ctx.beginPath();
        ctx.moveTo(getX(0), getYc(history[0].casing));
        for (let i = 1; i < history.length; i++) {
            const prevX = getX(i - 1);
            const prevY = getYc(history[i - 1].casing);
            const curX = getX(i);
            const curY = getYc(history[i].casing);
            const cpX = (prevX + curX) / 2;
            ctx.bezierCurveTo(cpX, prevY, cpX, curY, curX, curY);
        }
        ctx.strokeStyle = '#fbbf24';
        ctx.lineWidth = 1.8;
        ctx.shadowColor = 'rgba(251, 191, 36, 0.4)';
        ctx.shadowBlur = 5;
        ctx.stroke();
        ctx.shadowBlur = 0;

        // Balizas luminosas en el punto más reciente
        const lastIdx = history.length - 1;
        const lastWhtp = history[lastIdx].whtp;
        const lastCas = history[lastIdx].casing;
        const ptX = getX(lastIdx);
        const ptYw = getYw(lastWhtp);
        const ptYc = getYc(lastCas);

        // Baliza WHTP
        ctx.beginPath();
        ctx.arc(ptX, ptYw, 7, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(56, 189, 248, 0.3)';
        ctx.fill();
        ctx.beginPath();
        ctx.arc(ptX, ptYw, 3.5, 0, Math.PI * 2);
        ctx.fillStyle = '#38bdf8';
        ctx.fill();

        // Baliza Casing
        ctx.beginPath();
        ctx.arc(ptX, ptYc, 6, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(251, 191, 36, 0.3)';
        ctx.fill();
        ctx.beginPath();
        ctx.arc(ptX, ptYc, 3, 0, Math.PI * 2);
        ctx.fillStyle = '#fbbf24';
        ctx.fill();

        // Marcas de tiempo en el eje X
        ctx.font = '8.5px "JetBrains Mono", monospace';
        ctx.fillStyle = '#64748b';
        ctx.textAlign = 'center';
        const timeIndices = [0, Math.floor(history.length / 2), history.length - 1];
        timeIndices.forEach(idx => {
            if (history[idx]) {
                ctx.fillText(history[idx].time, getX(idx), pad.top + plotH + 16);
            }
        });

        // Pill con lecturas en vivo en la esquina superior
        ctx.textAlign = 'right';
        ctx.font = '10px "JetBrains Mono", monospace';
        ctx.fillStyle = '#cbd5e1';
        ctx.fillText(`WHTP: `, pad.left + plotW - 120, 16);
        ctx.fillStyle = '#38bdf8';
        ctx.fillText(`${lastWhtp.toFixed(1)} PSI`, pad.left + plotW - 75, 16);
        ctx.fillStyle = '#cbd5e1';
        ctx.fillText(` | CASING: `, pad.left + plotW - 45, 16);
        ctx.fillStyle = '#fbbf24';
        ctx.fillText(`${lastCas.toFixed(1)} PSI`, pad.left + plotW, 16);
    },

    drawProductionChart() {
        const { canvas, ctx, history } = this.charts.production;
        if (!canvas || !ctx || history.length < 2) return;

        const dims = this.setupCanvas(canvas, ctx);
        if (!dims) return;
        const { w, h } = dims;

        const pad = { top: 28, bottom: 26, left: 55, right: 30 };
        const plotW = w - pad.left - pad.right;
        const plotH = h - pad.top - pad.bottom;

        const minRate = 12000, maxRate = 16500;

        // Cuadrícula y Ticks
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.05)';
        ctx.lineWidth = 1;
        ctx.setLineDash([3, 4]);

        const steps = 4;
        for (let i = 0; i <= steps; i++) {
            const y = pad.top + (plotH / steps) * i;
            ctx.beginPath();
            ctx.moveTo(pad.left, y);
            ctx.lineTo(pad.left + plotW, y);
            ctx.stroke();

            const rVal = Math.round(maxRate - (i / steps) * (maxRate - minRate));
            ctx.font = '9px "JetBrains Mono", monospace';
            ctx.fillStyle = '#94a3b8';
            ctx.textAlign = 'right';
            ctx.fillText(rVal.toLocaleString() + ' BPD', pad.left - 6, y + 3);
        }
        ctx.setLineDash([]);

        const getX = (idx) => pad.left + (idx / (history.length - 1)) * plotW;
        const getY = (val) => pad.top + plotH - ((val - minRate) / (maxRate - minRate)) * plotH;

        // Línea Setpoint de Target (15,000 BPD)
        const targetY = getY(15000);
        ctx.strokeStyle = 'rgba(168, 85, 247, 0.7)';
        ctx.lineWidth = 1.5;
        ctx.setLineDash([5, 4]);
        ctx.beginPath();
        ctx.moveTo(pad.left, targetY);
        ctx.lineTo(pad.left + plotW, targetY);
        ctx.stroke();
        ctx.setLineDash([]);

        ctx.font = '8.5px "JetBrains Mono", monospace';
        ctx.fillStyle = '#c084fc';
        ctx.textAlign = 'left';
        ctx.fillText('TARGET: 15,000 BPD', pad.left + 8, targetY - 5);

        // Curva de Producción Actual con relleno degradado
        const gradProd = ctx.createLinearGradient(0, pad.top, 0, pad.top + plotH);
        gradProd.addColorStop(0, 'rgba(16, 185, 129, 0.28)');
        gradProd.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        ctx.beginPath();
        ctx.moveTo(getX(0), getY(history[0].rate));
        for (let i = 1; i < history.length; i++) {
            const prevX = getX(i - 1);
            const prevY = getY(history[i - 1].rate);
            const curX = getX(i);
            const curY = getY(history[i].rate);
            const cpX = (prevX + curX) / 2;
            ctx.bezierCurveTo(cpX, prevY, cpX, curY, curX, curY);
        }
        ctx.lineTo(getX(history.length - 1), pad.top + plotH);
        ctx.lineTo(getX(0), pad.top + plotH);
        ctx.closePath();
        ctx.fillStyle = gradProd;
        ctx.fill();

        // Trazo de Producción
        ctx.beginPath();
        ctx.moveTo(getX(0), getY(history[0].rate));
        for (let i = 1; i < history.length; i++) {
            const prevX = getX(i - 1);
            const prevY = getY(history[i - 1].rate);
            const curX = getX(i);
            const curY = getY(history[i].rate);
            const cpX = (prevX + curX) / 2;
            ctx.bezierCurveTo(cpX, prevY, cpX, curY, curX, curY);
        }
        ctx.strokeStyle = '#10b981';
        ctx.lineWidth = 2.4;
        ctx.shadowColor = 'rgba(16, 185, 129, 0.55)';
        ctx.shadowBlur = 6;
        ctx.stroke();
        ctx.shadowBlur = 0;

        // Baliza en el punto más reciente
        const lastIdx = history.length - 1;
        const lastRate = history[lastIdx].rate;
        const ptX = getX(lastIdx);
        const ptY = getY(lastRate);

        ctx.beginPath();
        ctx.arc(ptX, ptY, 7.5, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(16, 185, 129, 0.35)';
        ctx.fill();
        ctx.beginPath();
        ctx.arc(ptX, ptY, 3.5, 0, Math.PI * 2);
        ctx.fillStyle = '#10b981';
        ctx.fill();

        // Ticks de tiempo
        ctx.font = '8.5px "JetBrains Mono", monospace';
        ctx.fillStyle = '#64748b';
        ctx.textAlign = 'center';
        const timeIndices = [0, Math.floor(history.length / 2), history.length - 1];
        timeIndices.forEach(idx => {
            if (history[idx]) {
                ctx.fillText(history[idx].time, getX(idx), pad.top + plotH + 16);
            }
        });

        // Pill con lectura en vivo
        const efficiency = ((lastRate / 15000) * 100).toFixed(1);
        ctx.textAlign = 'right';
        ctx.font = '10px "JetBrains Mono", monospace';
        ctx.fillStyle = '#cbd5e1';
        ctx.fillText(`ACTUAL: `, pad.left + plotW - 130, 16);
        ctx.fillStyle = '#10b981';
        ctx.fillText(`${Math.round(lastRate).toLocaleString()} BPD`, pad.left + plotW - 75, 16);
        ctx.fillStyle = '#cbd5e1';
        ctx.fillText(` | EFIC: `, pad.left + plotW - 40, 16);
        ctx.fillStyle = '#38bdf8';
        ctx.fillText(`${efficiency}%`, pad.left + plotW, 16);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    SCADA.init();
});
