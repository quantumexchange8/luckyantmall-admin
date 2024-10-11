<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AddItem from "@/Pages/Item/Partials/AddItem.vue";
import ItemTable from "@/Pages/Item/Partials/ItemTable.vue";

const props = defineProps({
    itemCounts: Number,
    locales: Array
})
</script>

<template>
    <AuthenticatedLayout :title="$t('public.item')">
        <div class="flex flex-col items-center gap-5">
            <div class="flex justify-end w-full">
                <AddItem
                    :locales="locales"
                />
            </div>

            <!-- Items Table-->
            <ItemTable
                :itemCounts="itemCounts"
            />
        </div>
    </AuthenticatedLayout>
</template>
