<script setup>
import Button from "@/Components/Button.vue";
import {IconPlus} from "@tabler/icons-vue";
import Dialog from "primevue/dialog";
import {ref, watch} from "vue";
import InputText from "primevue/inputtext";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import Checkbox from 'primevue/checkbox';
import Select from "primevue/select";

const props = defineProps({
    locales: Array
})

const visible = ref(false);
const items = ref([]);
const selectedItem = ref();
const loadingItems = ref(false);

const openDialog = () => {
    visible.value = true;
    getItems();
}

const form = useForm({
    locales: props.locales,
    name_translations: {
        en: ''
    },
    item_id: ''
});

const getItems = async () => {
    loadingItems.value = true;
    try {
        const response = await axios.get('/getItems');
        items.value = response.data;
    } catch (error) {
        console.error('Error fetching items:', error);
    } finally {
        loadingItems.value = false;
    }
};

const submitForm = () => {
    form.item_id = selectedItem.value?.id;
    form.post(route('category.addCategory'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
        }
    })
}

const closeDialog = () => {
    visible.value = false;
}

// Watch the form.locales array to dynamically add translation fields
watch(() => form.locales, (newLocales) => {
    newLocales.forEach((locale) => {
        if (!form.name_translations[locale]) {
            form.name_translations[locale] = '';
        }
    });

    // Remove translations that are unchecked
    Object.keys(form.name_translations).forEach((locale) => {
        if (!newLocales.includes(locale)) {
            delete form.name_translations[locale];
        }
    });
});
</script>

<template>
    <Button
        type="button"
        variant="primary-flat"
        class="w-full md:w-auto"
        @click="openDialog"
    >
        <IconPlus size="16" />
        {{ $t('public.new_category') }}
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.new_category')"
        class="dialog-xs md:dialog-md"
    >
        <form class="flex flex-col gap-6 items-start self-stretch">
            <!-- Checkbox for selecting locales -->
            <div class="flex flex-col gap-3">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.languages') }}</span>
                <div class="flex gap-3">
                    <div
                        v-for="locale in locales"
                        :key="locale"
                        class="flex items-center"
                    >
                        <Checkbox
                            v-model="form.locales"
                            :inputId="locale"
                            :value="locale"
                            :disabled="locale === 'en'"
                        />
                        <label :for="locale" class="ml-2 text-sm">{{ $t(`public.${locale}`) }}</label>
                    </div>
                </div>
            </div>

            <!-- Dynamically generated input fields for each selected locale -->
            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.basics') }}</span>
                <div class="grid md:grid-cols-2 gap-3 w-full">
                    <div
                        v-for="locale in form.locales"
                        :key="'input-' + locale"
                        class="flex flex-col items-start gap-1 self-stretch"
                    >
                        <InputLabel
                            :for="'name_' + locale"
                            :value="`${$t('public.name')} (${$t(`public.${locale}`)})`"
                            :invalid="!!form.errors[`name_translations.${locale}`]"
                        />
                        <InputText
                            :id="'name_' + locale"
                            type="text"
                            class="block w-full"
                            v-model="form.name_translations[locale]"
                            :placeholder="`${$t('public.category_name_placeholder')} (${$t(`public.${locale}`)})`"
                            :invalid="!!form.errors[`name_translations.${locale}`]"
                        />
                        <InputError :message="form.errors[`name_translations.${locale}`]" />
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.item') }}</span>
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <Select
                        v-model="selectedItem"
                        :options="items"
                        filter
                        optionLabel="item_name"
                        :placeholder="$t('public.select_item')"
                        class="w-full md:w-1/2"
                        :loading="loadingItems"
                        :invalid="!!form.errors.item_id"
                    >
                        <template #value="slotProps">
                            <div v-if="slotProps.value" class="flex items-center">
                                <div>{{ slotProps.value.item_name }}</div>
                            </div>
                            <span v-else>{{ slotProps.placeholder }}</span>
                        </template>
                        <template #option="slotProps">
                            <div class="flex items-center gap-1 max-w-[220px] truncate">
                                <span>{{ slotProps.option.item_name }}</span>
                            </div>
                        </template>
                    </Select>
                    <InputError :message="form.errors.item_id" />
                </div>
            </div>

            <div class="flex gap-3 justify-end self-stretch pt-2 w-full">
                <Button
                    type="button"
                    variant="gray-tonal"
                    @click="closeDialog"
                    :disabled="form.processing"
                    class="w-full md:w-auto"
                >
                    {{ $t('public.cancel') }}
                </Button>
                <Button
                    variant="primary-flat"
                    @click="submitForm"
                    :disabled="form.processing"
                    class="w-full md:w-auto"
                >
                    {{ $t('public.create') }}
                </Button>
            </div>
        </form>
    </Dialog>
</template>
