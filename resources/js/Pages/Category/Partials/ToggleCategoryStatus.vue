<script setup>
import ToggleSwitch from 'primevue/toggleswitch';
import {ref, watch} from "vue";
import {useConfirm} from "primevue/useconfirm";
import {trans} from "laravel-vue-i18n";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    category: Object,
})

const checked = ref(props.category.status === 'active')

watch(() => props.category.status, (newStatus) => {
    checked.value = newStatus === 'active';
});
const confirm = useConfirm();

const requireConfirmation = (action_type) => {
    const messages = {
        activate_category: {
            group: 'headless-success',
            header: trans('public.activate_category'),
            text: trans('public.activate_category_caption'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.confirm'),
            action: () => {
                router.visit(route('category.updateCategoryStatus', props.category.id), {
                    method: 'patch',
                    data: {
                        id: props.category.id,
                    },
                })

                checked.value = !checked.value;
            }
        },
        deactivate_category: {
            group: 'headless-primary',
            header: trans('public.deactivate_category'),
            text: trans('public.deactivate_category_caption'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.confirm'),
            action: () => {
                router.visit(route('category.updateCategoryStatus', props.category.id), {
                    method: 'patch',
                    data: {
                        id: props.category.id,
                    },
                })

                checked.value = !checked.value;
            }
        },
    };

    const { group, header, text, dynamicText, suffix, actionType, cancelButton, acceptButton, action } = messages[action_type];

    confirm.require({
        group,
        header,
        actionType,
        text,
        dynamicText,
        suffix,
        cancelButton,
        acceptButton,
        accept: action
    });
};

const handleStatus = () => {
    if (props.category.status === 'active') {
        requireConfirmation('deactivate_category')
    } else {
        requireConfirmation('activate_category')
    }
}
</script>

<template>
    <div class="flex items-center">
        <ToggleSwitch
            v-model="checked"
            readonly
            @click="handleStatus"
        />
    </div>
</template>
