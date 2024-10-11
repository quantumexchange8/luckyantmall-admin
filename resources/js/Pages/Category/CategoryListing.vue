<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import AddCategory from "@/Pages/Category/Partials/AddCategory.vue";
import CategoryTable from "@/Pages/Category/Partials/CategoryTable.vue";

const props = defineProps({
    categoryCounts: Number,
    locales: Array
})
</script>

<template>
    <AuthenticatedLayout :title="$t('public.category')">
        <div class="flex flex-col items-center gap-5">
            <div class="flex justify-end w-full">
                <AddCategory
                    :locales="locales"
                />
            </div>

            <!-- Items Table-->
            <CategoryTable
                :categoryCounts="categoryCounts"
            />
        </div>
    </AuthenticatedLayout>
</template>
