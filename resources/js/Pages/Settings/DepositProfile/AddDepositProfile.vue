<script setup>
import {IconPlus} from "@tabler/icons-vue";
import Dialog from "primevue/dialog";
import Button from "@/Components/Button.vue";
import {ref, watch} from "vue";
import InputText from "primevue/inputtext";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import {useForm} from "@inertiajs/vue3";
import Select from "primevue/select";
import MultiSelect from 'primevue/multiselect';

const visible = ref(false);
const types = ref([
    {
        name_label: 'receiver_name',
        value: 'bank',
    },
    {
        name_label: 'wallet_name',
        value: 'crypto'
    },
])
const selectedType = ref('bank');
const nameLabel = ref('receiver_name')
const countries = ref([]);
const selectedCountry = ref();
const loadingCountries = ref(false);

const selectedNetwork = ref();
const networks = ref([
    { name: 'TRC20'},
    { name: 'ERC20'},
    { name: 'BEP20'},
]);

const openDialog = () => {
    visible.value = true;
    getCountries();
}

const getCountries = async () => {
    loadingCountries.value = true;
    try {
        const response = await axios.get('/getCountries');
        countries.value = response.data;
    } catch (error) {
        console.error('Error fetching countries:', error);
    } finally {
        loadingCountries.value = false;
    }
};

watch(selectedType, (newType) => {
    const selected = types.value.find(type => type.value === newType);
    nameLabel.value = selected ? selected.name_label : '';
});

const form = useForm({
    type: '',
    name: '',
    bank_name: '',
    bank_branch: '',
    currency: '',
    crypto_tether: '',
    crypto_network: '',
    account_number: '',
});

const closeDialog = () => {
    visible.value = false;
}

const submitForm = () => {
    form.type = selectedType.value;

    if (form.type === 'bank') {
        form.currency = selectedCountry.value;
        form.crypto_tether = '';
        form.crypto_network = '';
    } else {
        form.crypto_network = selectedNetwork.value;
        form.bank_name = '';
        form.bank_branch = '';
        form.currency = 'USD'
    }

    form.post(route('settings.addDepositProfile'), {
        onSuccess: (values) => {
            closeDialog();
            form.reset();
        }
    })
}
</script>

<template>
    <Button
        type="button"
        variant="primary-flat"
        class="w-full md:w-auto"
        @click="openDialog"
    >
        <IconPlus size="16" />
        {{ $t('public.new_deposit_profile') }}
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.new_deposit_profile')"
        class="dialog-xs md:dialog-md"
    >
        <form class="flex flex-col gap-6 items-start self-stretch">
            <!-- Basics -->
            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t('public.basics') }}</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full">
                    <!-- Type -->
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="type"
                            :value="$t('public.type')"
                            :invalid="!!form.errors.type"
                        />
                        <Select
                            inputId="type"
                            v-model="selectedType"
                            :options="types"
                            optionValue="value"
                            :placeholder="$t('public.select_type')"
                            class="w-full"
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center">
                                    <div>{{ $t(`public.${slotProps.value}`) }}</div>
                                </div>
                                <span v-else>{{ slotProps.placeholder }}</span>
                            </template>
                            <template #option="slotProps">
                                <div class="flex items-center gap-1 max-w-[220px] truncate">
                                    <span>{{ $t(`public.${slotProps.option.value}`) }}</span>
                                </div>
                            </template>
                        </Select>
                        <InputError :message="form.errors.type" />
                    </div>

                    <!-- Name -->
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="name"
                            :value="$t(`public.${nameLabel}`)"
                            :invalid="!!form.errors.name"
                        />
                        <InputText
                            id="name"
                            type="text"
                            class="block w-full"
                            v-model="form.name"
                            :placeholder="$t(`public.${nameLabel}`)"
                            :invalid="!!form.errors.name"
                            autofocus
                        />
                        <InputError :message="form.errors.name" />
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="flex flex-col gap-3 items-center self-stretch">
                <span class="font-bold text-sm text-gray-950 dark:text-white w-full text-left">{{ $t(`public.${selectedType}_details`) }}</span>

                <!-- bank -->
                <div
                    v-if="selectedType === 'bank'"
                    class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full"
                >
                    <!-- Bank Name -->
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="bank_name"
                            :value="$t('public.bank_name')"
                            :invalid="!!form.errors.bank_name"
                        />
                        <InputText
                            id="bank_name"
                            type="text"
                            class="block w-full"
                            v-model="form.bank_name"
                            :placeholder="$t('public.bank_name')"
                            :invalid="!!form.errors.bank_name"
                            autofocus
                        />
                        <InputError :message="form.errors.bank_name" />
                    </div>

                    <!-- Bank Branch -->
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="bank_branch"
                            :value="$t('public.bank_branch')"
                            :invalid="!!form.errors.bank_branch"
                        />
                        <InputText
                            id="bank_branch"
                            type="text"
                            class="block w-full"
                            v-model="form.bank_branch"
                            :placeholder="$t('public.bank_branch')"
                            :invalid="!!form.errors.bank_branch"
                            autofocus
                        />
                        <InputError :message="form.errors.bank_branch" />
                    </div>

                    <!-- Account Number -->
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="account_number"
                            :value="$t('public.account_number')"
                            :invalid="!!form.errors.account_number"
                        />
                        <InputText
                            id="account_number"
                            type="text"
                            class="block w-full"
                            v-model="form.account_number"
                            :placeholder="$t('public.account_number')"
                            :invalid="!!form.errors.account_number"
                            autofocus
                        />
                        <InputError :message="form.errors.account_number" />
                    </div>

                    <!-- Currency -->
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="currency"
                            :value="$t('public.currency')"
                            :invalid="!!form.errors.currency"
                        />
                        <Select
                            v-model="selectedCountry"
                            :options="countries"
                            filter
                            :filterFields="['name', 'iso2', 'currency']"
                            optionLabel="name"
                            :placeholder="$t('public.select_currency')"
                            class="w-full"
                            :loading="loadingCountries"
                            :invalid="!!form.errors.currency"
                        >
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center">
                                    <div>{{ slotProps.value.currency }}</div>
                                </div>
                                <span v-else>{{ slotProps.placeholder }}</span>
                            </template>
                            <template #option="slotProps">
                                <div class="flex items-center gap-1">
                                    <div class="max-w-[200px] truncate">
                                        {{ slotProps.option.currency }} - <span class="text-surface-500">{{ slotProps.option.currency_symbol }}</span>
                                    </div>
                                </div>
                            </template>
                        </Select>
                        <InputError :message="form.errors.currency" />
                    </div>
                </div>

                <!-- crypto -->
                <div
                    v-else
                    class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-5 w-full"
                >
                    <!-- Bank Name -->
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="crypto_tether"
                            :value="$t('public.tether')"
                            :invalid="!!form.errors.crypto_tether"
                        />
                        <InputText
                            id="crypto_tether"
                            type="text"
                            class="block w-full"
                            v-model="form.crypto_tether"
                            :placeholder="$t('public.crypto_tether_placeholder')"
                            :invalid="!!form.errors.crypto_tether"
                            autofocus
                        />
                        <InputError :message="form.errors.crypto_tether" />
                    </div>

                    <!-- Network -->
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="network"
                            :value="$t('public.network')"
                            :invalid="!!form.errors.crypto_network"
                        />
                        <MultiSelect
                            v-model="selectedNetwork"
                            display="chip"
                            :options="networks"
                            optionLabel="name"
                            optionValue="name"
                            filter
                            :placeholder="$t('public.network_placeholder')"
                            :maxSelectedLabels="3"
                            class="w-full"
                            :invalid="!!form.errors.crypto_network"
                        />
                        <InputError :message="form.errors.crypto_network" />
                    </div>

                    <!-- Account Number -->
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel
                            for="wallet_address"
                            :value="$t('public.wallet_address')"
                            :invalid="!!form.errors.account_number"
                        />
                        <InputText
                            id="wallet_address"
                            type="text"
                            class="block w-full"
                            v-model="form.account_number"
                            :placeholder="$t('public.wallet_address')"
                            :invalid="!!form.errors.account_number"
                            autofocus
                        />
                        <InputError :message="form.errors.account_number" />
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
                    {{ $t('public.save') }}
                </Button>
            </div>
        </form>
    </Dialog>
</template>
