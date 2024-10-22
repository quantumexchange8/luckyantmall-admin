<script setup>
import Button from "@/Components/Button.vue"
import Dialog from "primevue/dialog";
import {ref} from "vue";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import {generalFormat} from "@/Composables/format.js";
import dayjs from "dayjs";
import Image from 'primevue/image';
import Textarea from 'primevue/textarea';
import InputLabel from "@/Components/InputLabel.vue";
import {useForm} from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";

const props = defineProps({
    transaction: Object
});

const visible = ref(false);
const {formatAmount} = generalFormat();
const approvalAction = ref('');

const handleApproval = (action) => {
    approvalAction.value = action;
}

const closeDialog = () => {
    visible.value = false;
    approvalAction.value = '';
}

const form = useForm({
    id: '',
    action: '',
    remarks: '',
})

const submit = (transactionId) => {
    form.id = transactionId;
    form.action = approvalAction.value;

    form.post(route('transaction.pending.pendingApproval'), {
        onSuccess: () => {
            closeDialog();
            form.reset();
        },
    });
};
</script>

<template>
    <Button
        type="button"
        variant="primary-tonal"
        size="sm"
        @click="visible = true"
    >
        {{ $t('public.action') }}
    </Button>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t(`public.${transaction.transaction_type}_request`)"
        class="dialog-xs md:dialog-md"
    >
        <div
            class="flex flex-col-reverse md:flex-row md:items-center gap-3 self-stretch w-full pb-4"
            :class="{'border-b dark:border-surface-600': !approvalAction}"
        >
            <div class="flex items-center gap-3 self-stretch w-full">
                <div class="w-9 h-9 rounded-full overflow-hidden grow-0 shrink-0">
                    <div v-if="transaction.user_profile_photo">
                        <img :src="transaction.user_profile_photo" alt="Profile Photo" />
                    </div>
                    <div v-else>
                        <DefaultProfilePhoto />
                    </div>
                </div>
                <div class="flex flex-col items-start w-full">
                    <span class="text-sm text-surface-950 dark:text-white font-medium">{{ transaction.user.name }}</span>
                    <span class="text-surface-500 text-xs">{{ transaction.user.email }}</span>
                </div>
            </div>
            <div class="min-w-[180px] text-surface-950 dark:text-white font-semibold text-xl md:text-right">
                {{ transaction.to_currency === 'CNY' ? '¥' : '' }}{{ formatAmount(transaction.amount) }}
            </div>
        </div>

        <template
            v-if="!approvalAction"
        >
            <div class="flex flex-col items-center gap-4 divide-y dark:divide-surface-600 self-stretch">
                <div class="flex flex-col gap-3 items-start w-full pt-4">
                    <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                        <div class="w-[140px] text-surface-500 text-xs font-medium">
                            {{ $t('public.requested_date') }}
                        </div>
                        <div class="text-surface-950 dark:text-white text-sm font-medium">
                            {{ dayjs(transaction.created_at).format('YYYY/MM/DD HH:mm:ss') }}
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                        <div class="w-[140px] text-surface-500 text-xs font-medium">
                            {{ $t('public.bank_name') }}
                        </div>
                        <div class="text-surface-950 dark:text-white text-sm font-medium">
                            {{ transaction.to_payment_platform_name }}
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                        <div class="w-[140px] text-surface-500 text-xs font-medium">
                            {{ $t('public.receiver_name') }}
                        </div>
                        <div class="text-surface-950 dark:text-white text-sm font-medium">
                            {{ transaction.to_payment_account_name }}
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                        <div class="w-[140px] text-surface-500 text-xs font-medium">
                            {{ $t('public.account_number') }}
                        </div>
                        <div class="text-surface-950 dark:text-white text-sm font-medium">
                            {{ transaction.to_payment_account_no }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 items-start w-full pt-4">
                    <div class="text-surface-500 text-xs font-medium">
                        {{ $t('public.payment_slip') }}
                    </div>
                    <div class="flex gap-2 items-center self-stretch">
                        <Image
                            :src="transaction.media[0].original_url"
                            alt="Image"
                            imageClass="max-w-full h-9 object-contain rounded"
                            preview
                        />
                        <span class="text-sm">{{ transaction.media[0].file_name }}</span>
                    </div>
                    <div class="flex justify-end items-center pt-5 gap-4 self-stretch sm:pt-7">
                        <Button
                            type="button"
                            variant="error-flat"
                            class="w-full md:w-[120px]"
                            size="base"
                            @click="handleApproval('reject')"
                        >
                            {{ $t('public.reject') }}
                        </Button>
                        <Button
                            variant="success-flat"
                            class="w-full md:w-[120px]"
                            size="base"
                            @click="handleApproval('approve')"
                        >
                            {{ $t('public.approve') }}
                        </Button>
                    </div>
                </div>
            </div>
        </template>

        <template v-else>
            <div class="flex flex-col gap-3 items-start w-full py-4">
                <div class="flex flex-col md:flex-row md:items-center gap-3 self-stretch">
                    <div class="text-surface-500 text-xs font-medium">
                        {{ $t('public.action') }}
                    </div>
                    <div
                        class="text-xs font-medium p-1 rounded-md"
                        :class="{
                            'bg-success-200 dark:bg-success-600/40 text-success-700 dark:text-success-200': approvalAction === 'approve',
                            'bg-error-200 dark:bg-error-600/40 text-error-700 dark:text-error-200': approvalAction === 'reject',
                        }"
                    >
                        {{ $t(`public.${approvalAction}`) }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-start gap-3 h-28 self-stretch">
                <InputLabel for="remarks">{{ $t('public.remarks') }}</InputLabel>

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

                <InputError class="mt-2" :message="form.errors.remarks" />
            </div>

            <div class="flex justify-end items-center pt-5 gap-4 self-stretch sm:pt-7">
                <Button
                    type="button"
                    variant="gray-tonal"
                    class="w-full md:w-[120px]"
                    size="base"
                    @click="closeDialog"
                >
                    {{ $t('public.cancel') }}
                </Button>
                <Button
                    variant="primary-flat"
                    class="w-full md:w-[120px]"
                    size="base"
                    @click="submit(transaction.id)"
                >
                    {{ $t('public.confirm') }}
                </Button>
            </div>
        </template>
    </Dialog>
</template>
