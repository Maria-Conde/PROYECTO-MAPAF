<?php
/**
 * PROYECTO MAPAF - Vista: Equipos Industriales & Activos de Planta
 * SENA Centro de la Industria, la Empresa y los Servicios (Regional Huila)
 * Ficha: 3413988 - ADSO
 */

$equipos = [];
$kpis = ['total_equipos' => 6, 'operativos' => 6, 'disponibilidad' => 98.2, 'total_produccion' => 14820];

try {
    $equiposService = new EquiposService();
    $equipos = $equiposService->getAllEquipos();
    $kpis = $equiposService->getSummaryKpis();
} catch (Throwable $e) {}
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
    <div>
        <h2 class="section-title" style="margin-bottom:0.25rem;">Dashboard de Control Operativo</h2>
        <div style="font-size:0.9rem; color:#cbd5e1; font-weight:600; font-family:var(--font-mono);">
            Planta de Producción Principal · SPS-01
        </div>
        <p style="font-size:0.8rem; color:#f59e0b; font-family:var(--font-mono); margin-top:0.2rem;">
            👑 Modo Administrador Activo ($2,450 USD/mes).
        </p>
    </div>
    
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <div style="background:#090e1d; border:1px solid var(--border-color); border-radius:8px; padding:0.5rem 1rem; text-align:right;">
            <div style="font-size:0.68rem; color:var(--text-muted); font-family:var(--font-mono);">Disponibilidad Planta</div>
            <div style="font-size:1.4rem; font-weight:800; color:#fff; font-family:var(--font-mono);">
                <?= $kpis['disponibilidad'] ?> <span style="font-size:1rem; color:#94a3b8;">%</span>
            </div>
            <div style="font-size:0.68rem; color:#94a3b8; font-family:var(--font-mono);">Meta industrial: &ge; 95.0 %</div>
        </div>
    </div>
</div>

<!-- Barra de Búsqueda y Filtros de Tipo -->
<div class="scada-card" style="padding:1rem 1.25rem; margin-bottom:1.5rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div style="flex:1; min-width:280px; position:relative;">
            <input type="text" id="equipmentSearchInput" 
                   placeholder="🔍 Buscar por código, nombre o tipo..." 
                   class="modal-input" 
                   style="padding-left:1rem; margin-bottom:0;"
                   oninput="filterEquipmentCards()">
        </div>

        <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
            <button type="button" class="btn-well btn-ssv-open eq-filter-btn" data-filter="all" onclick="setEquipmentFilter('all', this)">
                Todos (<?= count($equipos) ?>)
            </button>
            <button type="button" class="btn-well eq-filter-btn" data-filter="Pozo" onclick="setEquipmentFilter('Pozo', this)">
                Cabezas de Pozo
            </button>
            <button type="button" class="btn-well eq-filter-btn" data-filter="Tanque" onclick="setEquipmentFilter('Tanque', this)">
                Tanques Fiscales
            </button>
            <button type="button" class="btn-well eq-filter-btn" data-filter="Separador" onclick="setEquipmentFilter('Separador', this)">
                Separadores
            </button>
            <button type="button" class="btn-well eq-filter-btn" data-filter="Bomba" onclick="setEquipmentFilter('Bomba', this)">
                Bombas ESP
            </button>
        </div>
    </div>
</div>

<!-- Cuadrícula de Tarjetas de Equipos Industriales (Estilo Exacto del Prototipo) -->
<div class="grid-kpis" id="equipmentCardsGrid">
    <?php foreach ($equipos as $eq): 
        $badgeColor = ($eq['estado'] === 'OPERATIVO') ? '#10b981' : '#f59e0b';
        $badgeClass = ($eq['estado'] === 'OPERATIVO') ? 'badge-nominal' : 'badge-warning';
    ?>
    <div class="scada-card equipment-card" 
         data-tag="<?= htmlspecialchars(strtolower($eq['tag'])) ?>" 
         data-name="<?= htmlspecialchars(strtolower($eq['nombre'])) ?>"
         data-field="<?= htmlspecialchars(strtolower($eq['campo'])) ?>"
         data-type="<?= htmlspecialchars($eq['tipo']) ?>"
         style="border:1px solid #1e293b; background:#0d1527; padding:1.25rem; display:flex; flex-direction:column; justify-content:space-between; transition:transform 0.2s, border-color 0.2s;">
        
        <div>
            <!-- Header de Tarjeta: Estado y Tag -->
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                <div>
                    <span style="font-family:var(--font-mono); font-size:1.15rem; font-weight:800; color:#fff; letter-spacing:0.02em;">
                        <?= htmlspecialchars($eq['tag']) ?>
                    </span>
                    <div style="color:#38bdf8; font-size:0.85rem; font-weight:600; margin-top:0.15rem;">
                        <?= htmlspecialchars($eq['nombre']) ?>
                    </div>
                    <div style="font-size:0.75rem; color:#94a3b8; margin-top:0.25rem;">
                        📍 <?= htmlspecialchars($eq['campo']) ?>
                    </div>
                </div>
                
                <span class="badge-status <?= $badgeClass ?>" style="font-size:0.68rem; font-weight:800; letter-spacing:0.04em;">
                    ● <?= htmlspecialchars($eq['estado']) ?>
                </span>
            </div>

            <!-- Métricas Críticas del Equipo -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; background:#080d1a; border:1px solid var(--border-color); border-radius:8px; padding:0.85rem; margin:1rem 0;">
                <div>
                    <div style="font-size:0.65rem; color:var(--text-muted); font-family:var(--font-mono); text-transform:uppercase;">
                        PRESIÓN CRÍTICA:
                    </div>
                    <div style="font-size:1.1rem; font-weight:800; color:#38bdf8; font-family:var(--font-mono); margin-top:0.2rem;">
                        <?= number_format((float)$eq['presion_critica'], 1) ?> <span style="font-size:0.7rem; color:#94a3b8;">PSI</span>
                    </div>
                </div>
                <div>
                    <div style="font-size:0.65rem; color:var(--text-muted); font-family:var(--font-mono); text-transform:uppercase;">
                        CAUDAL PRODUCCIÓN:
                    </div>
                    <div style="font-size:1.1rem; font-weight:800; color:#34d399; font-family:var(--font-mono); margin-top:0.2rem;">
                        <?= number_format((float)$eq['caudal_produccion']) ?> <span style="font-size:0.7rem; color:#94a3b8;">BPD</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer de Tarjeta: Tiempo de Conexión y Acciones -->
        <div style="border-top:1px solid var(--border-color); padding-top:0.75rem; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-size:0.72rem; color:var(--text-muted); font-family:var(--font-mono);">
                Conexión: <span style="color:#34d399; font-weight:bold;"><?= (int)$eq['tiempo_conexion_seg'] ?> seg</span>
            </div>

            <div style="display:flex; gap:0.4rem;">
                <button type="button" class="btn-well" style="padding:0.35rem 0.65rem; font-size:0.72rem;" onclick="openEquipmentModal('<?= htmlspecialchars($eq['tag']) ?>')">
                    ⚙️ Telemetría
                </button>
            </div>
        </div>

    </div>
    <?php endforeach; ?>
</div>

<script>
let currentFilter = 'all';

function filterEquipmentCards() {
    const q = document.getElementById('equipmentSearchInput').value.toLowerCase().trim();
    const cards = document.querySelectorAll('.equipment-card');
    
    cards.forEach(card => {
        const tag = card.getAttribute('data-tag') || '';
        const name = card.getAttribute('data-name') || '';
        const field = card.getAttribute('data-field') || '';
        const type = card.getAttribute('data-type') || '';
        
        const matchesQuery = !q || tag.includes(q) || name.includes(q) || field.includes(q);
        const matchesType = (currentFilter === 'all') || (type.toLowerCase() === currentFilter.toLowerCase());
        
        if (matchesQuery && matchesType) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function setEquipmentFilter(type, btn) {
    currentFilter = type;
    document.querySelectorAll('.eq-filter-btn').forEach(b => b.classList.remove('btn-ssv-open'));
    btn.classList.add('btn-ssv-open');
    filterEquipmentCards();
}

function openEquipmentModal(tag) {
    if (window.SCADA && window.SCADA.showToast) {
        window.SCADA.showToast('Telemetría en vivo adquirida para ' + tag);
    } else {
        alert('Telemetría adquirida para equipo: ' + tag);
    }
}
</script>
