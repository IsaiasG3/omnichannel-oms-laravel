<script setup>
import { router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderStateStepper from '@/Components/OrderStateStepper.vue';
import { computed } from 'vue';

const props = defineProps({
    order: { type: Object, required: true },
    history: { type: Array, required: true },
});

function pay() {
    router.post(route('orders.pay', props.order.id));
}

function ship() {
    router.post(route('orders.ship', props.order.id));
}
const page = usePage();
const errors = computed(() => page.props.errors);
function cancel() {
    if (!confirm('¿Seguro que quieres cancelar esta orden? Esta acción no se puede deshacer.')) return;
    router.post(route('orders.cancel', props.order.id));
}
function deliver() {
    router.post(route('orders.deliver', props.order.id));
}
const isWarehouseStaff = computed(() => page.props.auth.user?.is_warehouse_staff ?? false);
</script>
<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">
                Orden {{ order.order_number }}
            </h2>
            <div v-if="errors.status" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm mb-4">
                {{ errors.status }}
            </div>
        </template>

        <div class="py-8 max-w-3xl mx-auto space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <OrderStateStepper :status="order.status" />
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="mt-4 flex gap-2">
    <button
        v-if="order.status === 'pending'"
        @click="pay"
        class="px-4 py-2 rounded-md bg-blue-600 text-white text-sm font-medium hover:bg-blue-700"
    >
        Simular pago
    </button>
    <button
    v-if="['pending', 'paid'].includes(order.status)"
    @click="cancel"
    class="px-4 py-2 rounded-md bg-white border border-red-300 text-red-600 text-sm font-medium hover:bg-red-50"
>
    Cancelar orden
</button>
    <button
        v-if="order.status === 'paid' && isWarehouseStaff" 
        @click="ship"
        class="px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700"
    >
        Marcar como enviado
    </button>
    <button
     v-if="order.status === 'shipped' && isWarehouseStaff"
    @click="deliver"
    class="px-4 py-2 rounded-md bg-green-600 text-white text-sm font-medium hover:bg-green-700"
>
    Confirmar entrega
</button>
</div>
                <h3 class="font-medium text-gray-700 mb-3">Historial de estados</h3>
                <ul class="space-y-2 text-sm">
                    <li v-for="(h, i) in history" :key="i" class="text-gray-600">
                        <span class="font-medium">{{ h.from_status ?? '—' }} → {{ h.to_status }}</span>
                        <span class="text-gray-400"> · {{ new Date(h.created_at).toLocaleString() }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>