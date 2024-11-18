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

const ranks = ref([]);
const selectedRank = ref(props.customer.rank);
const loadingRanks = ref(false);
const emit = defineEmits(['update:visible']);

const getRanks = async () => {
    loadingRanks.value = true;
    try {
        const response = await axios.get(`/getRanks?group_id=${props.customer.group.group_id}`);
        ranks.value = response.data;
    } catch (error) {
        console.error('Error fetching countries:', error);
    } finally {
        loadingRanks.value = false;
    }
};

getRanks();

const form = useForm({
    user_id: props.customer.id,
    rank: '',
})

const submitForm = () => {
    form.rank = selectedRank.value;
    form.post(route('customer.upgradeRank'), {
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
            <span class="font-semibold text-sm">{{ $t('public.select_new_rank') }}</span>
            <div class="flex flex-col gap-1 items-start self-stretch">
                <InputLabel
                    for="rank"
                    :value="$t('public.rank')"
                    :invalid="!!form.errors.rank"
                />
                <Select
                    v-model="selectedRank"
                    :options="ranks"
                    filter
                    optionLabel="name"
                    :placeholder="$t('public.select_rank')"
                    class="w-full"
                    :loading="loadingRanks"
                    :invalid="!!form.errors.rank"
                >
                    <template #value="slotProps">
                        <div v-if="slotProps.value" class="flex items-center">
                            {{ $t(`public.${slotProps.value.rank_name}`) }}
                        </div>
                        <span v-else>{{ slotProps.placeholder }}</span>
                    </template>
                    <template #option="slotProps">
                        {{ $t(`public.${slotProps.option.rank_name}`) }}
                    </template>
                </Select>
                <InputError :message="form.errors.rank" />
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
                :disabled="form.processing || loadingRanks"
                class="w-full md:w-auto"
            >
                {{ $t('public.save') }}
            </Button>
        </div>
    </div>
</template>

