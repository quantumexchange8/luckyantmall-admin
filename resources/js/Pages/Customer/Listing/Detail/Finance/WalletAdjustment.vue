<script setup>
import {IconCreditCard} from "@tabler/icons-vue";
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import Dialog from "primevue/dialog";
import {useForm} from "@inertiajs/vue3";
import InputLabel from "@/Components/InputLabel.vue";
import RadioButton from 'primevue/radiobutton';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import InputError from "@/Components/InputError.vue";
import {generalFormat} from "@/Composables/format.js";

const props = defineProps({
    user: Object,
    wallet: Object,
})

const visible = ref(false);
const amount = ref(0);
const {formatAmount} = generalFormat();

const form = useForm({
    wallet_id: '',
    user_role: props.user.role,
    action: 'deposit',
    fund_type: 'demo_fund',
    amount: '',
    remarks: '',
});

const closeDialog = () => {
    visible.value = false;
    form.reset();
};

const submit = () => {
    if (props.wallet) {
        form.wallet_id = props.wallet.id;
    }

    if (amount.value > 0) {
        form.amount = amount.value;
    }
    form.post(route('customer.detail.walletAdjustment'), {
        onSuccess: () => {
            closeDialog();
        },
    });
};
</script>

<template>
    <Button
        type="button"
        variant="gray-outlined"
        icon-only
        pill
        size="sm"
        @click="visible = true"
        :disabled="!wallet"
    >
        <IconCreditCard size="20" stroke-width="1.5" />
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.adjustment')"
        class="dialog-xs md:dialog-sm"
    >
        <form>
            <div class="flex flex-col gap-5">
                <div class="flex flex-col justify-center items-center px-8 py-4 gap-2 self-stretch bg-surface-100 dark:bg-surface-800">
                    <div class="text-surface-100 text-center text-xs font-medium">
                        {{ $t(`public.${wallet.type}_balance`) }}
                    </div>
                    <div class="text-xl text-primary-500 font-semibold">
                        <span v-if="wallet.currency_symbol !== 'point'">{{ wallet.currency_symbol }}{{ formatAmount(wallet.balance) }}</span>
                        <span v-else>{{ formatAmount(wallet.balance) }} {{ $t(`public.${wallet.currency_symbol}`) }}</span>
                    </div>
                </div>

                <!-- action -->
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel for="action" :value="$t('public.action')" />
                    <div class="flex items-center gap-5">
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex p-2 justify-center items-center">
                                <RadioButton v-model="form.action" inputId="deposit" :name="$t('public.deposit')" value="deposit" class="w-4 h-4" />
                            </div>
                            <label for="deposit">{{ $t('public.deposit') }}</label>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex p-2 justify-center items-center">
                                <RadioButton v-model="form.action" inputId="withdrawal" :name="$t('public.withdrawal')" value="withdrawal" class="w-4 h-4" />
                            </div>
                            <label for="withdrawal">{{ $t('public.withdrawal') }}</label>
                        </div>
                    </div>
                </div>

                <!-- fund type -->
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel for="action" :value="$t('public.type')" />
                    <div class="flex items-center gap-5">
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex p-2 justify-center items-center">
                                <RadioButton
                                    v-model="form.fund_type"
                                    inputId="demo_fund"
                                    value="demo_fund"
                                    class="w-4 h-4"
                                />
                            </div>
                            <label for="demo_fund">{{ $t('public.demo_fund') }}</label>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <div class="flex p-2 justify-center items-center">
                                <RadioButton
                                    v-model="form.fund_type"
                                    inputId="real_fund"
                                    value="real_fund"
                                    class="w-4 h-4"
                                    :disabled="['demo_fund', 'special_fund', 'special_demo_fund'].includes(user.role)"
                                />
                            </div>
                            <label for="real_fund">{{ $t('public.real_fund') }}</label>
                        </div>
                    </div>
                </div>

                <!-- amount -->
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="amount"
                        :value="$t('public.amount')"
                        :invalid="!!form.errors.amount"
                    />
                    <InputNumber
                        v-model="amount"
                        inputId="currency-us"
                        :prefix="wallet.currency_symbol !== 'point' ? wallet.currency_symbol : ''"
                        :suffix="wallet.currency_symbol === 'point' ? $t(`public.${wallet.currency_symbol}`) : ''"
                        class="w-full"
                        inputClass="py-3 px-4"
                        :min="0"
                        :step="100"
                        :minFractionDigits="2"
                        fluid
                        autofocus
                        :invalid="!!form.errors.amount"
                    />
                    <InputError :message="form.errors.amount" />
                </div>

                <!-- remarks -->
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <InputLabel
                        for="remarks"
                        :value="$t('public.remarks')"
                        :invalid="!!form.errors.remarks"
                    />
                    <Textarea
                        id="remarks"
                        type="text"
                        class="flex flex-1 self-stretch"
                        v-model="form.remarks"
                        :placeholder="$t('public.enter_remarks')"
                        :invalid="!!form.errors.remarks"
                        rows="5"
                        cols="30"
                    />
                    <InputError :message="form.errors.remarks" />
                </div>
            </div>
            <div class="flex justify-end items-center pt-10 md:pt-7 gap-3 md:gap-4 self-stretch">
                <Button
                    type="button"
                    variant="gray-tonal"
                    class="flex flex-1 md:flex-none md:w-[120px]"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click.prevent="closeDialog"
                >
                    {{ $t('public.cancel') }}
                </Button>
                <Button
                    variant="primary-flat"
                    class="flex flex-1 md:flex-none md:w-[120px]"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    @click="submit"
                >
                    {{ $t('public.confirm') }}
                </Button>
            </div>
        </form>
    </Dialog>
</template>
