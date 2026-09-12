<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
    invoices: { type: Array, required: true },
});

function statusLabel(status) {
    return {
        generating: 'Generando...',
        generated: 'Generada',
        failed: 'Falló',
    }[status] ?? status;
}

function statusClass(status) {
    return {
        generating: 'bg-yellow-100 text-yellow-800',
        generated: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
    }[status] ?? 'bg-gray-100 text-gray-800';
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Facturas</h2>
        </template>

        <div class="py-8 max-w-3xl mx-auto">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Factura</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Orden</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="inv in invoices" :key="inv.id">
                            <td class="px-4 py-3 text-sm font-medium">{{ inv.invoice_number }}</td>
                            <td class="px-4 py-3 text-sm">
                                <a :href="`/orders/${inv.order_id}`" class="text-blue-600 hover:underline">
                                    {{ inv.order_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-xs font-medium" :class="statusClass(inv.status)">
                                    {{ statusLabel(inv.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ new Date(inv.created_at).toLocaleString() }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a
                                    v-if="inv.status === 'generated'"
                                    :href="`/invoices/${inv.id}/download`"
                                    target="_blank"
                                    class="text-blue-600 hover:underline"
                                >
                                    Descargar PDF
                                </a>
                            </td>
                        </tr>
                        <tr v-if="invoices.length === 0">
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">
                                Aún no tienes facturas. Se generan automáticamente al pagar una orden.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>