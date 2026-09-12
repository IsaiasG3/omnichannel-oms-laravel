<script setup>
import { reactive, computed, ref } from 'vue'; 
import { router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    products: { type: Array, required: true },
});

const page = usePage();
const errors = computed(() => page.props.errors);

const quantities = reactive(
    Object.fromEntries(props.products.map(p => [p.id, 0]))
);

const isSubmitting = ref(false); 

function submit() {
    const items = props.products
        .filter(p => quantities[p.id] > 0)
        .map(p => ({ product_id: p.id, quantity: quantities[p.id] }));

    if (items.length === 0) return;

    isSubmitting.value = true;

    router.post(route('checkout.store'), { items }, {
        onFinish: () => { isSubmitting.value = false; },
    });
}
</script>
<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Nueva orden</h2>
            <div v-if="errors.stock" class="max-w-2xl mx-auto mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md text-sm">
                {{ errors.stock }}
            </div>
        </template>

        <div class="py-8 max-w-2xl mx-auto space-y-4">
            <div
                v-for="product in products"
                :key="product.id"
                class="bg-white shadow rounded-lg p-4 flex items-center justify-between"
            >
                <div>
                    <p class="font-medium">{{ product.name }}</p>
                    <p class="text-sm text-gray-500">
                        ${{ (product.price_cents / 100).toFixed(2) }} · {{ product.available }} disponibles
                    </p>
                </div>
                <input
                    type="number"
                    min="0"
                    :max="product.available"
                    v-model.number="quantities[product.id]"
                    class="w-20 rounded-md border-gray-300"
                />
            </div>

                <button
                    @click="submit"
                    :disabled="isSubmitting"
                    class="px-4 py-2 rounded-md bg-blue-600 text-white font-medium hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ isSubmitting ? 'Creando orden...' : 'Crear orden' }}
                </button>
        </div>
    </AuthenticatedLayout>
</template>