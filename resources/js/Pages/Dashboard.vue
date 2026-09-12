<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    counts: { type: Object, required: true },
    lowStock: { type: Array, required: true },
    recentOrders: { type: Array, required: true },
});

const statCards = [
    { key: 'pending', label: 'Pendientes', color: 'bg-yellow-50 text-yellow-800' },
    { key: 'paid', label: 'Pagadas', color: 'bg-blue-50 text-blue-800' },
    { key: 'shipped', label: 'Enviadas', color: 'bg-indigo-50 text-indigo-800' },
    { key: 'delivered', label: 'Entregadas', color: 'bg-green-50 text-green-800' },
    { key: 'cancelled', label: 'Canceladas', color: 'bg-red-50 text-red-800' },
];

const quickLinks = [
    { href: '/checkout', label: 'Nueva orden', icon: '🛒' },
    { href: '/orders', label: 'Órdenes en vivo', icon: '📦' },
    { href: '/inventory', label: 'Inventario / Simulador', icon: '🧪' },
    { href: '/invoices', label: 'Facturas', icon: '🧾' },
];
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Resumen general</h2>
        </template>

        <div class="py-8 max-w-5xl mx-auto space-y-6">

          
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <Link
                    v-for="link in quickLinks"
                    :key="link.href"
                    :href="link.href"
                    class="bg-white shadow rounded-lg p-4 text-center hover:shadow-md transition-shadow"
                >
                    <div class="text-2xl mb-1">{{ link.icon }}</div>
                    <div class="text-sm font-medium text-gray-700">{{ link.label }}</div>
                </Link>
            </div>

       
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div
                    v-for="card in statCards"
                    :key="card.key"
                    class="rounded-lg p-4 text-center"
                    :class="card.color"
                >
                    <div class="text-2xl font-bold">{{ counts[card.key] }}</div>
                    <div class="text-xs font-medium mt-1">{{ card.label }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-medium text-gray-700 mb-3">Tus órdenes recientes</h3>
                    <ul class="space-y-2">
                        <li v-for="order in recentOrders" :key="order.id" class="flex justify-between text-sm">
                            <Link :href="`/orders/${order.id}`" class="text-blue-600 hover:underline">
                                {{ order.order_number }}
                            </Link>
                            <span class="text-gray-500">{{ order.status_label }} · ${{ (order.total_cents / 100).toFixed(2) }}</span>
                        </li>
                        <li v-if="recentOrders.length === 0" class="text-sm text-gray-400">
                            Aún no tienes órdenes.
                            <Link href="/checkout" class="text-blue-600 hover:underline">Crea la primera</Link>
                        </li>
                    </ul>
                </div>

          
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="font-medium text-gray-700 mb-3">⚠️ Stock bajo (≤ 5 unidades)</h3>
                    <ul class="space-y-2">
                        <li v-for="item in lowStock" :key="item.product_name" class="flex justify-between text-sm">
                            <span>{{ item.product_name }}</span>
                            <span class="font-medium text-red-600">{{ item.available }} disponibles</span>
                        </li>
                        <li v-if="lowStock.length === 0" class="text-sm text-gray-400">
                            Todo el inventario está en niveles saludables.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>