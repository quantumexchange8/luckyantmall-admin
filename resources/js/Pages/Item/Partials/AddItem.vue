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

const props = defineProps({
    locales: Array
})

const visible = ref(false);

const form = useForm({
    locales: props.locales,
    name_translations: {
        en: ''
    }
});

const submitForm = () => {
    form.post(route('item.addItem'), {
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
        @click="visible = true"
    >
        <IconPlus size="16" />
        {{ $t('public.new_item') }}
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.new_item')"
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
                            :placeholder="`${$t('public.item_name_placeholder')} (${$t(`public.${locale}`)})`"
                            :invalid="!!form.errors[`name_translations.${locale}`]"
                        />
                        <InputError :message="form.errors[`name_translations.${locale}`]" />
                    </div>
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
