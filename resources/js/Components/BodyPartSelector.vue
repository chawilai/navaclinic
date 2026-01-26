<script setup>
import { ref, computed, watch } from 'vue';
import InteractiveSvg from '@/Components/InteractiveSvg.vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    embedded: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

// View Management
const currentView = ref('front');
const views = [
    { id: 'front', label: 'Body (Front)', file: '/images/body/Front_body_interactive.svg' },
    { id: 'back', label: 'Body (Back)', file: '/images/body/Interactive_Map_Final.svg' },
    { id: 'head', label: 'Head', file: '/images/body/head_interactive.svg' },
    { id: 'hand_l', label: 'Hand (L)', file: '/images/body/hand2_interactive_L.svg' },
    { id: 'hand_r', label: 'Hand (R)', file: '/images/body/hand2_interactive_R.svg' },
    { id: 'foot_l', label: 'Foot (L)', file: '/images/body/foot_interactive_L.svg' },
    { id: 'foot_r', label: 'Foot (R)', file: '/images/body/foot_interactive_R.svg' },
];

const currentSvgParams = computed(() => {
    return views.find(v => v.id === currentView.value);
});

// Selection Logic
const selectedParts = computed(() => {
    // Extract just the area/ID names from the modelValue
    return props.modelValue.map(item => {
        return typeof item === 'object' ? item.area : item;
    });
});

const handleToggle = (partName) => {
    if (props.readonly) return;
    
    // Check if exists
    const existingIndex = props.modelValue.findIndex(item => {
        const name = typeof item === 'object' ? item.area : item;
        return name === partName;
    });

    const newValue = [...props.modelValue];
    
    if (existingIndex !== -1) {
        // Remove
        newValue.splice(existingIndex, 1);
    } else {
        // Add
        // If the modelValue contains objects, add an object structure
        // If strings, add string.
        // Heuristic: check first element. If empty, default to object if complex usage expected, or string if simple.
        // In this app, Create.vue converts strings to objects on load, but starts with empty.
        // Standardize: If we are adding to an empty list, assume object structure for consistency with Visit/Treatment record which stores { area:..., symptom:... }
        // BUT, Create.vue expects objects for the form.
        
        // Default to string mode if empty, assuming parent manages objects if explicit mapping wasn't used.
        // This prevents the bug where new items act as objects when the parent expects strings.
        const isObjectMode = props.modelValue.length > 0 && typeof props.modelValue[0] === 'object';
        
        if (isObjectMode) {
            newValue.push({
                area: partName,
                symptom: '',
                pain_level: '',
                pain_level_after: '',
                characteristic: ''
            });
        } else {
            newValue.push(partName);
        }
    }
    
    emit('update:modelValue', newValue);
};

const formatLabel = (part) => {
    if (typeof part === 'string') return part.replace(/_/g, ' ');
    if (!part) return '';
    
    let label = part.area.replace(/_/g, ' ');
    if (part.symptom) label += `: ${part.symptom}`;
    
    const before = part.pain_level;
    const after = part.pain_level_after;
    
    if (before || after) {
        label += ` (Pain: ${before || '-'} → ${after || '-'})`;
    }
    
    return label;
};

// Helper to remove item from list below
const removeItem = (item) => {
    if (props.readonly) return;
    const name = typeof item === 'object' ? item.area : item;
    handleToggle(name);
};

</script>

<template>
    <div class="flex flex-col gap-4">
        <!-- View Switcher -->
        <div :class="['flex flex-wrap gap-2 justify-center', embedded ? '' : 'bg-slate-50 p-2 rounded-xl border border-slate-200']">
            <button 
                v-for="view in views" 
                :key="view.id"
                @click="currentView = view.id"
                class="px-4 py-2 text-xs font-bold rounded-lg transition-all border"
                :class="currentView === view.id 
                    ? 'bg-indigo-600 text-white border-indigo-600 shadow-md transform scale-105' 
                    : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100 hover:border-slate-300'"
            >
                {{ view.label }}
            </button>
        </div>

        <!-- Interactive SVG Area -->
        <!-- Interactive SVG Area -->
        <div :class="[
            'relative overflow-hidden flex flex-col transition-all',
            embedded ? 'bg-transparent' : 'bg-white rounded-2xl border border-slate-200 shadow-sm min-h-[600px]'
        ]">
            <div class="p-4 flex-1 relative flex justify-center items-start overflow-auto custom-scrollbar">
                <!-- Wrapper to constraint size -->
                <!-- Wrapper to constraint size -->
                <div class="relative w-full" :class="embedded ? 'h-full max-w-full' : 'h-[600px] max-w-[500px]'">
                    <InteractiveSvg 
                        :src="currentSvgParams.file" 
                        :selected-parts="selectedParts"
                        @toggle="handleToggle"
                        class="w-full h-full"
                    />
                </div>
            </div>
            
            <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm text-[10px] text-slate-400 font-mono">
                View: {{ currentSvgParams.label }}
            </div>
        </div>
        
        <!-- Selected Items List (only if not readonly, or strict readonly mode might show tags elsewhere) -->
        <!-- In 'Show.vue', it displays tags itself elsewhere? No, Show.vue calls BodyPartSelector with readonly=true for the map, 
             and then displays a list below separately in Show.vue template (lines 318+). 
             So we don't strictly need to show them here if readonly. 
             But Create.vue might rely on this component not showing the list, or showing it? 
             Create.vue shows the list in "Col 2: Symptom List". 
             BodyPartSelector in Create.vue is just the map.
             BodyPartSelector in Show.vue is the map.
             
             Wait, BodyPartSelector.vue *used* to show tags at the bottom. 
             Show.vue *also* creates its own list "Pain Details List" below the BodyPartSelector component.
             So to avoid duplication, we should probably hide the built-in list if readonly, or let parent handle it.
             The previous implementation showed tags at bottom if !readonly.
        -->
        <div v-if="!readonly && modelValue.length > 0" class="flex flex-wrap gap-2 justify-center mt-2">
            <span v-for="(item, idx) in modelValue" :key="idx" 
                class="px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded-lg text-xs font-bold border border-indigo-100 flex items-center gap-2 shadow-sm animate-fadeIn">
                {{ formatLabel(item) }}
                <button @click.prevent="removeItem(item)" class="text-indigo-400 hover:text-rose-500 transition-colors bg-white rounded-full p-0.5 hover:bg-rose-50">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3 h-3">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </span>
        </div>
        <div v-else-if="!readonly" class="text-center text-slate-400 text-xs italic mt-2">
            Click on body parts to select
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
