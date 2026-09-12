<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    orders: { type: Array, required: true },
});

const liveOrders = ref([...props.orders]);

function upsertOrder(payload) {
    const incoming = payload.order;
    const index = liveOrders.value.findIndex(o => o.id === incoming.id);

    if (index === -1) {
        liveOrders.value.unshift(incoming);
    } else {
        liveOrders.value[index] = { ...liveOrders.value[index], ...incoming };
    }
}

let channel;

onMounted(() => {
    channel = window.Echo.channel('orders-dashboard');
    channel.listen('.order.status.changed', (payload) => {
        upsertOrder(payload);
    });
});

onUnmounted(() => {
    window.Echo.leaveChannel('orders-dashboard');
});

function statusBadgeClass(status) {
    return {
        pending: 'bg-yellow-100 text-yellow-800',
        paid: 'bg-blue-100 text-blue-800',
        shipped: 'bg-indigo-100 text-indigo-800',
        delivered: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    }[status] ?? 'bg-gray-100 text-gray-800';
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Órdenes en tiempo real</h2>
        </template>

        <div class="py-8 max-w-5xl mx-auto">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Orden</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="order in liveOrders" :key="order.id">
                            <td class="px-4 py-3 text-sm">
                                <a :href="`/orders/${order.id}`" class="text-blue-600 hover:underline">
                                    {{ order.order_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="px-2 py-1 rounded-full text-xs font-medium"
                                    :class="statusBadgeClass(order.status)"
                                >
                                    {{ order.status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                ${{ (order.total_cents / 100).toFixed(2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>