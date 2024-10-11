<script setup>
import ToggleSwitch from 'primevue/toggleswitch';
import {ref, watch} from "vue";
import {useConfirm} from "primevue/useconfirm";
import {trans} from "laravel-vue-i18n";
import {router} from "@inertiajs/vue3";

const props = defineProps({
    item: Object,
})

const checked = ref(props.item.status === 'active')

watch(() => props.item.status, (newStatus) => {
    checked.value = newStatus === 'active';
});
const confirm = useConfirm();

const requireConfirmation = (action_type) => {
    const messages = {
        activate_item: {
            group: 'headless-success',
            header: trans('public.activate_item'),
            text: trans('public.activate_item_caption'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.confirm'),
            action: () => {
                router.visit(route('item.updateItemStatus', props.item.id), {
                    method: 'patch',
                    data: {
                        id: props.item.id,
                    },
                })

                checked.value = !checked.value;
            }
        },
        deactivate_item: {
            group: 'headless-primary',
            header: trans('public.deactivate_item'),
            text: trans('public.deactivate_item_caption'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.confirm'),
            action: () => {
                router.visit(route('item.updateItemStatus', props.item.id), {
                    method: 'patch',
                    data: {
                        id: props.item.id,
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
    if (props.item.status === 'active') {
        requireConfirmation('deactivate_item')
    } else {
        requireConfirmation('activate_item')
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
