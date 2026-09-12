<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    inventoryLocationId: { type: Number, required: true },
    initialAvailable: { type: Number, required: true },
});

const isRunning = ref(false);
const results = ref(null);
const currentAvailable = ref(props.initialAvailable);

async function runStressTest() {
    isRunning.value = true;
    results.value = null;

 
    const requests = Array.from({ length: 50 }, () =>
        axios.post('/purchase-attempt', {
            inventory_location_id: props.inventoryLocationId,
            quantity: 1,
        }).then(res => ({ status: 'success', data: res.data }))
        .catch(err => {
    if (err.response?.status === 429) {
        return { status: 'rate_limited', data: err.response.data };
    }
    return { status: 'failed', data: err.response?.data };
})
    );

    const settled = await Promise.all(requests);

    const successful = settled.filter(r => r.status === 'success').length;
    const failed = settled.filter(r => r.status === 'failed').length;

    results.value = { successful, failed, total: settled.length };
    isRunning.value = false;

 
    const res = await axios.get(`/inventory-locations/${props.inventoryLocationId}/available`);
    currentAvailable.value = res.data.available;
}
</script>

<template>
    <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <h3 class="font-semibold text-gray-800">🧪 Simulador de concurrencia</h3>
        <p class="text-sm text-gray-500">
            Envía 50 intentos de compra simultáneos sobre el mismo lote de inventario
            para demostrar que el bloqueo pesimista evita el overselling.
        </p>

        <div class="text-sm text-gray-600">
            Stock disponible actual: <span class="font-bold">{{ currentAvailable }}</span>
        </div>

        <button
            @click="runStressTest"
            :disabled="isRunning"
            class="px-4 py-2 rounded-md font-medium text-white transition-colors"
            :class="isRunning ? 'bg-red-300 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700'"
        >
            {{ isRunning ? 'Disparando 50 peticiones...' : '🚨 Botón de pánico (50 compras simultáneas)' }}
        </button>

        <div v-if="results" class="mt-3 p-3 rounded-md bg-gray-50 text-sm space-y-1">
            <p>Total de peticiones: <strong>{{ results.total }}</strong></p>
            <p class="text-green-600">Reservas exitosas: <strong>{{ results.successful }}</strong></p>
            <p class="text-red-600">Rechazadas por falta de stock: <strong>{{ results.failed }}</strong></p>
            <p class="text-xs text-gray-400 mt-2">
                Si el stock inicial era menor a 50, el número de "exitosas" debe ser
                EXACTAMENTE igual al stock disponible — ni una unidad de más.
            </p>
        </div>
    </div>
</template>