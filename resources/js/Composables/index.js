import { useDark, useToggle } from '@vueuse/core'
import { reactive } from 'vue'

export const isDark = useDark()
export const toggleDarkMode = useToggle(isDark)

export const sidebarState = reactive({
    isOpen: window.innerWidth > 1024,
    isHovered: false,
    handleHover(value) {
        if (window.innerWidth < 1024) {
            return
        }
        sidebarState.isHovered = value
    },
    handleWindowResize() {
        if (window.innerWidth <= 1024) {
            sidebarState.isOpen = false
        } else {
            sidebarState.isOpen = true
        }
    },
})

export const scrolling = reactive({
    down: false,
    up: false,
})

let lastScrollTop = 0

export const handleScroll = () => {
    let st = window.pageYOffset || document.documentElement.scrollTop
    if (st > lastScrollTop) {
        // downscroll
        scrolling.down = true
        scrolling.up = false
    } else {
        // upscroll
        scrolling.down = false
        scrolling.up = true
        if (st == 0) {
            //  reset
            scrolling.down = false
            scrolling.up = false
        }
    }
    lastScrollTop = st <= 0 ? 0 : st // For Mobile or negative scrolling
}


export function transactionFormat() {

    function formatDateTime(date, includeTime = true) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const formattedDate = new Date(date);

        const day = formattedDate.getDate().toString().padStart(2, '0');
        const month = months[formattedDate.getMonth()];
        const year = formattedDate.getFullYear();
        const hours = formattedDate.getHours().toString().padStart(2, '0');
        const minutes = formattedDate.getMinutes().toString().padStart(2, '0');
        const seconds = formattedDate.getSeconds().toString().padStart(2, '0');

        if (includeTime) {
            return `${day} ${month} ${year} ${hours}:${minutes}:${seconds}`;
        } else {
            return `${day} ${month} ${year}`;
        }
    }


    function getStatusClass(status) {
        if (status === 'Successful') {
            return 'success';
        } else if (status === 'Submitted') {
            return 'warning';
        } else if (status === 'Processing') {
            return 'primary';
        } else if (status === 'Rejected') {
            return 'danger';
        } else {
            return ''; // Default case or handle other statuses
        }
    }

    function formatAmount(amount, decimalPlaces = 2) {
        const formattedAmount = parseFloat(amount).toFixed(decimalPlaces);
        const integerPart = formattedAmount.split('.')[0];
        const decimalPart = formattedAmount.split('.')[1];
        const integerWithCommas = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return decimalPlaces > 0 ? `${integerWithCommas}.${decimalPart}` : integerWithCommas;
    }

    const formatType = (type) => {
        const formattedType = type.replace(/_/g, ' ').replace(/([a-z])([A-Z])/g, '$1 $2');
        return formattedType.charAt(0).toUpperCase() + formattedType.slice(1);
    };

    function formatDate(date) {
        const formattedDate = new Date(date).toLocaleDateString('en-CA', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            timeZone: 'Asia/Kuala_Lumpur'
        });

        const [year, month, day] = formattedDate.split('-');
        return `${day}/${month}/${year}`;
    }

    function formatDateString(date) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const formattedDate = new Date(date * 1000);

        const day = formattedDate.getDate().toString().padStart(2, '0');
        const month = months[formattedDate.getMonth()];
        const year = formattedDate.getFullYear();

        return `${day} ${month} ${year}`;
    }

    return {
        formatDateTime,
        formatDate,
        formatDateString,
        getStatusClass,
        formatAmount,
        formatType
    };
}
