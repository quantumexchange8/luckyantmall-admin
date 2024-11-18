<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from "@/Components/Button.vue";
import {IconPlus} from "@tabler/icons-vue";
import ProductTable from "@/Pages/Product/ProductTable.vue";

defineProps({
    productsCount: Number
})
</script>

<template>
    <AuthenticatedLayout :title="$t('public.product_listing')">
        <div class="flex flex-col items-center gap-5">
            <div class="flex justify-end w-full">
                <Button
                    type="button"
                    variant="primary-flat"
                    class="w-full md:w-auto"
                    external
                    :href="route('product.add_product')"
                >
                    <IconPlus size="16" />
                    {{ $t('public.add_product') }}
                </Button>
            </div>

            <!-- Products Table-->
            <ProductTable
                :productsCount="productsCount"
            />
        </div>
    </AuthenticatedLayout>
</template>
