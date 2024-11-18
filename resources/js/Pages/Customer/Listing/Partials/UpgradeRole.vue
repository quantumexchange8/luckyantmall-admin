<script setup>
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import Select from "primevue/select";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import {ref} from "vue";
import {useForm} from "@inertiajs/vue3";
import Button from "@/Components/Button.vue";

const props = defineProps({
    customer: Object
});

const roles = ref([]);
const selectedRole = ref(props.customer.role);
const loadingRole = ref(false);
const emit = defineEmits(['update:visible']);

const getRanks = async () => {
    loadingRole.value = true;
    try {
        const response = await axios.get('/getRoles');
        roles.value = response.data;
    } catch (error) {
        console.error('Error fetching countries:', error);
    } finally {
        loadingRole.value = false;
    }
};

getRanks();

const form = useForm({
    user_id: props.customer.id,
    role: '',
})

const submitForm = () => {
    form.role = selectedRole.value;
    form.post(route('customer.upgradeRole'), {
        onSuccess: () => {
            closeDialog();
        }
    })
}

const closeDialog = () => {
    emit('update:visible', false);
}
</script>

<template>
    <div class="flex flex-col gap-5 items-center self-stretch">
        <div class="flex items-center gap-3 self-stretch">
            <div class="w-8 h-8 rounded-full overflow-hidden grow-0 shrink-0">
                <template v-if="customer.profile_photo">
                    <img :src="customer.profile_photo" alt="profile_photo">
                </template>
                <template v-else>
                    <DefaultProfilePhoto/>
                </template>
            </div>
            <div class="flex flex-col items-start">
                <div class="text-sm font-medium">
                    {{ customer.name }}
                </div>
                <div class="text-gray-500 text-xs">
                    @{{ customer.username }}
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 self-stretch">
            <span class="font-semibold text-sm">{{ $t('public.select_new_role') }}</span>
            <div class="flex flex-col gap-1 items-start self-stretch">
                <InputLabel
                    for="role"
                    :value="$t('public.role')"
                    :invalid="!!form.errors.role"
                />
                <Select
                    v-model="selectedRole"
                    :options="roles"
                    filter
                    optionLabel="name"
                    optionValue="name"
                    :placeholder="$t('public.select_role')"
                    class="w-full"
                    :loading="loadingRole"
                    :invalid="!!form.errors.role"
                >
                    <template #value="slotProps">
                        <div v-if="slotProps.value" class="flex items-center">
                            {{ $t(`public.${slotProps.value}`) }}
                        </div>
                        <span v-else>{{ slotProps.placeholder }}</span>
                    </template>
                    <template #option="slotProps">
                        {{ $t(`public.${slotProps.option.name}`) }}
                    </template>
                </Select>
                <InputError :message="form.errors.role" />
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
                :disabled="form.processing || loadingRole"
                class="w-full md:w-auto"
            >
                {{ $t('public.save') }}
            </Button>
        </div>
    </div>
</template>

