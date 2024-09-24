<script setup>
import {isDark, sidebarState, toggleDarkMode} from '@/Composables'
import {
    IconWorld,
    IconLogout,
    IconMenu2,
    IconMoon,
    IconSun
} from '@tabler/icons-vue';
// import ProfilePhoto from "@/Components/ProfilePhoto.vue";
import {Link, router, usePage} from "@inertiajs/vue3";
import Menu from 'primevue/menu';
import Button from '@/Components/Button.vue';
import {ref} from "vue";
import {loadLanguageAsync} from "laravel-vue-i18n";

defineProps({
    title: String
})

const menu = ref();
const locales = ref([
    {
        label: 'English',
        command: () => {
            changeLanguage('en');
        }
    },
    {
        label: '中文',
        command: () => {
            changeLanguage('tw')
        }
    }
]);

const toggle = (event) => {
    menu.value.toggle(event);
};

const changeLanguage = async (langVal) => {
    try {
        await loadLanguageAsync(langVal);
        await axios.get(`/locale/${langVal}`);
    } catch (error) {
        console.error('Error changing locale:', error);
    }
};

const handleLogOut = () => {
    router.post(route('logout'))
}
</script>

<template>
    <nav
        aria-label="secondary"
        class="sticky top-0 z-10 py-2 px-3 md:px-5 bg-primary-50 dark:bg-gray-950 flex items-center gap-3"
    >
        <Button
            type="button"
            variant="gray-text"
            icon-only
            pill
            @click="sidebarState.isOpen = !sidebarState.isOpen"
        >
            <IconMenu2 size="20" stroke-width="1.25" />
        </Button>
        <div
            class="font-semibold text-gray-700 dark:text-gray-300 w-full"
        >
            {{ title }}
        </div>
        <div class="flex items-center">
            <Button
                type="button"
                variant="gray-text"
                icon-only
                pill
                @click="() => { toggleDarkMode() }"
            >
                <IconSun v-if="!isDark" size="20" stroke-width="1.5" />
                <IconMoon v-if="isDark" size="20" stroke-width="1.5" />
            </Button>
            <Button
                type="button"
                variant="gray-text"
                icon-only
                @click="toggle"
                aria-haspopup="true"
                aria-controls="overlay_menu"
                pill
            >
                <IconWorld size="20" stroke-width="1.5" />
            </Button>
            <Button
                external
                type="button"
                variant="gray-text"
                icon-only
                pill
                @click="handleLogOut"
            >
                <IconLogout size="20" stroke-width="1.5" />
            </Button>
            <Link
                class="w-12 h-12 p-2 items-center justify-center rounded-full hover:cursor-pointer hover:bg-gray-100 hidden md:block"
                :href="route('profile.edit')"
            >
<!--                <ProfilePhoto class="w-8 h-8" />-->
            </Link>
        </div>
    </nav>

    <Menu
        ref="menu"
        id="overlay_menu"
        :model="locales"
        :popup="true"
        class="w-32"
    />
</template>
