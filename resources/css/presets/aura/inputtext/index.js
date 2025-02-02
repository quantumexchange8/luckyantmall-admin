export default {
    root: ({ props, context, parent }) => ({
        class: [
            // Font
            'text-sm',

            // Flex
            { 'flex-1 w-[1%]': parent.instance.$name == 'InputGroup' },

            // Spacing
            'm-0',
            'w-full',

            // Size
            {
                'py-3 px-3.5': props.size == 'large',
                'py-1.5 px-2': props.size == 'small',
                'py-2 px-3': props.size == null
            },

            // Shape
            { 'rounded-md': parent.instance.$name !== 'InputGroup' },
            { 'first:rounded-l-md rounded-none last:rounded-r-md': parent.instance.$name == 'InputGroup' },
            { 'border-0 border-y border-l last:border-r': parent.instance.$name == 'InputGroup' },
            { 'first:ml-0 -ml-px': parent.instance.$name == 'InputGroup' && !props.showButtons },

            // Colors
            'text-surface-800 dark:text-white/80',
            'placeholder:text-surface-400 dark:placeholder:text-surface-500',
            { 'bg-surface-0 dark:bg-surface-950': !context.disabled },
            'border',
            { 'border-surface-300 dark:border-surface-700': !props.invalid },

            // Invalid State
            { 'border-error-500 dark:border-error-400': props.invalid },

            // States
            {
                'hover:border-surface-400 dark:hover:border-surface-600': !context.disabled && !props.invalid,
                'focus:outline-none focus:ring-0 focus:border-primary-500 dark:focus:border-primary-300': !context.disabled,
                'bg-surface-200 dark:bg-surface-800 disabled:text-gray-500 dark:disabled:text-gray-500 select-none pointer-events-none cursor-default': context.disabled
            },

            // Filled State *for FloatLabel
            { filled: parent.instance?.$name == 'FloatLabel' && context.filled },

            // Misc
            'appearance-none shadow-input',
            'transition-colors duration-200'
        ]
    })
};
