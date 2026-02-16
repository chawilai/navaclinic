<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [Number, String],
        default: ''
    },
    label: {
        type: String,
        default: 'Pain Level'
    },
    readonly: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

const levels = Array.from({ length: 10 }, (_, i) => i + 1);

const getStyle = (level) => {
    const isSelected = parseInt(props.modelValue) === level;
    const hasSelection = props.modelValue !== '' && props.modelValue !== null;

    // If something is selected, but not this one -> Gray out
    if (hasSelection && !isSelected) {
        return {
            backgroundColor: '#f1f5f9', // slate-100
            color: '#cbd5e1', // slate-400
            borderColor: '#e2e8f0', // slate-200
        };
    }

    // Default (colorful) state
    // 1 = 130 (Green)
    // 10 = 0 (Red)
    const hue = Math.max(0, 130 - ((level - 1) * (130 / 9)));
    
    return {
        backgroundColor: `hsl(${hue}, 85%, 50%)`,
        color: 'white',
        borderColor: `hsl(${hue}, 85%, 40%)`, // slightly darker border
    };
};

const selectLevel = (level) => {
    if (props.readonly) return;
    if (parseInt(props.modelValue) === level) {
        emit('update:modelValue', '');
    } else {
        emit('update:modelValue', level);
    }
};

// Badge color for the selected value
const badgeStyle = computed(() => {
    const val = parseInt(props.modelValue);
    if (!val) return {};
    const hue = Math.max(0, 130 - ((val - 1) * (130 / 9)));
    return {
        backgroundColor: `hsl(${hue}, 85%, 50%)`,
        color: 'white'
    };
});
</script>

<template>
    <div class="w-full">
        <div class="mb-2">
            <label class="block text-xs font-medium text-slate-600">{{ label }}</label>
        </div>
        
        <div class="flex gap-1">
            <button 
                v-for="level in levels" 
                :key="level"
                type="button"
                @click="selectLevel(level)"
                class="flex-1 aspect-square md:aspect-auto md:h-8 rounded-md flex items-center justify-center text-sm shadow-sm transition-all duration-200 border-b-[3px]"
                :style="getStyle(level)"
                :class="[
                    parseInt(modelValue) === level 
                        ? 'ring-2 ring-offset-2 ring-indigo-100 scale-110 z-10 font-bold shadow-md brightness-110' 
                        : (modelValue ? '' : 'opacity-40 hover:opacity-100 hover:scale-105 hover:shadow-sm grayscale-[0.3] hover:grayscale-0')
                ]"
            >
                {{ level }}
            </button>
        </div>
        <div class="flex justify-between mt-1 px-1">
            <span class="text-[10px] text-slate-400 font-medium">Low (น้อย)</span>
            <span class="text-[10px] text-slate-400 font-medium">Severe (มาก)</span>
        </div>
    </div>
</template>
