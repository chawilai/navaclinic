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
    },
    expandAll: {
        type: Boolean,
        default: false
    },
    showAllViews: {
        type: Boolean,
        default: false
    },
    thumbnail: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

// View Management
const currentView = ref('front');

const viewGroups = [
    {
        label: 'ร่างกาย (Body)',
        options: [
            { id: 'front', label: 'หน้า', fullLabel: 'เต็มตัว (หน้า)', file: '/images/body/Front_body_interactive.svg', prefix: '' },
            { id: 'back', label: 'หลัง', fullLabel: 'เต็มตัว (หลัง)', file: '/images/body/Interactive_Map_Final.svg', prefix: '' },
            { id: 'head', label: 'ศีรษะ', fullLabel: 'ศีรษะ', file: '/images/body/head_interactive.svg', prefix: 'Head_' },
        ]
    },
    {
        label: 'มือ (Hands)',
        options: [
            { id: 'hand_l', label: 'ซ้าย', fullLabel: 'มือซ้าย', file: '/images/body/hand2_interactive_L.svg', prefix: 'Hand_L_' },
            { id: 'hand_r', label: 'ขวา', fullLabel: 'มือขวา', file: '/images/body/hand2_interactive_R.svg', prefix: 'Hand_R_' },
        ]
    },
    {
        label: 'เท้า (Feet)',
        options: [
            { id: 'foot_l', label: 'ซ้าย', fullLabel: 'เท้าซ้าย', file: '/images/body/foot_interactive_L.svg', prefix: 'Foot_L_' },
            { id: 'foot_r', label: 'ขวา', fullLabel: 'เท้าขวา', file: '/images/body/foot_interactive_R.svg', prefix: 'Foot_R_' },
        ]
    }
];

const allViews = computed(() => viewGroups.flatMap(g => g.options));

const currentSvgParams = computed(() => {
    return allViews.value.find(v => v.id === currentView.value);
});

// Selection Logic
// Helper to get parts for a specific view configuration
const getPartsForView = (viewParams) => {
    const currentPrefix = viewParams?.prefix || '';
    
    // Extract just the area/ID names from the modelValue
    return props.modelValue.reduce((acc, item) => {
        const rawName = typeof item === 'object' ? item.area : item;
        
        if (currentPrefix) {
            if (rawName.startsWith(currentPrefix)) {
                acc.push(rawName.substring(currentPrefix.length));
            }
        } else {
            // Body View (Front/Back) - take everything?
            // If we have a prefix match for something else (e.g. Hand_L_), do we show it on Body?
            // Usually not, unless Body SVG has that ID.
            // For safety, Body view gets everything, assuming ID collision is handled by SVG content.
            acc.push(rawName);
        }
        return acc;
    }, []);
};

// Selection Logic for Single View Mode
const selectedParts = computed(() => {
    return getPartsForView(currentSvgParams.value);
});

const handleToggle = (rawPartName, specificViewParams = null) => {
    if (props.readonly) return;
    
    // If specificViewParams is passed (Expand All Mode), use it. Otherwise use current global view.
    const viewParams = specificViewParams || currentSvgParams.value;
    
    // Prepend prefix if applicable (e.g. Hand_L_Thumb)
    const prefix = viewParams?.prefix || '';
    const partName = prefix + rawPartName;
    
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
    <div class="flex flex-col gap-4" :class="{ 'h-full': thumbnail }">
        
        <!-- Thumbnail Mode (Tiny Preview for Dashboard) -->
        <div v-if="thumbnail" class="flex flex-col gap-2 w-full h-full p-1">
             <!-- Row 1: Body & Head (3 items) -->
             <div class="grid grid-cols-3 gap-2 flex-1 min-h-0">
                 <div v-for="view in viewGroups[0].options" :key="view.id" 
                      class="relative flex flex-col items-center justify-center bg-white rounded-lg border border-slate-100 p-1 shadow-sm hover:border-indigo-200 transition-colors">
                      <div class="w-full h-full flex items-center justify-center p-1 pointer-events-none">
                          <InteractiveSvg 
                             :src="view.file"
                             :selected-parts="getPartsForView(view)"
                             class="max-w-full max-h-full w-auto h-auto"
                          />
                      </div>
                      <span class="text-[9px] text-slate-500 font-bold uppercase mb-0.5 leading-none">{{ view.label }}</span>
                 </div>
             </div>
             
             <!-- Row 2: Hands & Feet (4 items) -->
             <div class="grid grid-cols-4 gap-2 flex-1 min-h-0">
                  <div v-for="view in [...viewGroups[1].options, ...viewGroups[2].options]" :key="view.id" 
                      class="relative flex flex-col items-center justify-center bg-white rounded-lg border border-slate-100 p-1 shadow-sm hover:border-indigo-200 transition-colors">
                      <div class="w-full h-full flex items-center justify-center p-1 pointer-events-none">
                          <InteractiveSvg 
                             :src="view.file"
                             :selected-parts="getPartsForView(view)"
                             class="max-w-full max-h-full w-auto h-auto"
                          />
                      </div>
                      <span class="text-[8px] text-slate-400 font-bold uppercase mb-0.5 leading-none">{{ view.label }}</span>
                 </div>
             </div>
        </div>

        <!-- Expand All Mode -->
        <div v-else-if="expandAll" class="space-y-12 animate-fadeIn py-6">
            <div v-for="(group, gIdx) in viewGroups" :key="gIdx">
                <!-- Group Header -->
                <div class="flex items-center gap-4 mb-6">
                     <div class="h-px bg-slate-200 flex-1"></div>
                     <h4 class="font-bold text-slate-400 text-sm uppercase tracking-wider">{{ group.label }}</h4>
                     <div class="h-px bg-slate-200 flex-1"></div>
                </div>

                <div :class="[
                    'grid gap-8 px-4', 
                    'grid-cols-1 md:grid-cols-2 max-w-4xl mx-auto'
                ]">
                     <div v-for="view in group.options" :key="view.id" 
                          class="relative flex flex-col items-center bg-transparent rounded-xl">
                         
                         <!-- Label -->
                         <div class="mb-2 z-10 hidden">
                            <span class="px-4 py-1.5 rounded-full bg-slate-50 text-slate-400 text-[10px] font-bold uppercase tracking-wider border border-slate-100">
                                {{ view.fullLabel }}
                            </span>
                         </div>
                         
                         <!-- Floating Label (Top Right) -->
                         <div class="absolute top-0 right-0 z-10 block">
                             <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">{{ view.label }}</span>
                         </div>

                         <!-- SVG Content -->
                         <div class="w-full flex items-start justify-center">
                             <InteractiveSvg 
                                :src="view.file"
                                :selected-parts="getPartsForView(view)"
                                @toggle="(name) => handleToggle(name, view)"
                                :class="['w-full h-auto', group.options.length === 3 ? '' : 'max-h-[500px]']"
                             />
                         </div>
                     </div>
                </div>
            </div>
        </div>

        <!-- Show All Views Mode (Compact Grid for Zoom Modal) -->
        <div v-else-if="showAllViews" class="space-y-8 animate-fadeIn py-4">
             <div v-for="(group, gIdx) in viewGroups" :key="gIdx">
                <!-- Group Header -->
                <div class="flex items-center gap-4 mb-4">
                     <div class="h-px bg-slate-200 flex-1"></div>
                     <h4 class="font-bold text-slate-400 text-sm uppercase tracking-wider">{{ group.label }}</h4>
                     <div class="h-px bg-slate-200 flex-1"></div>
                </div>

                <div class="flex flex-wrap justify-center gap-6">
                     <div v-for="view in group.options" :key="view.id" 
                          class="relative flex flex-col items-center bg-white rounded-xl border border-slate-100 p-4 shadow-sm min-w-[300px]">
                          
                          <div class="mb-4">
                             <span class="px-3 py-1 rounded-full bg-slate-50 text-slate-500 text-xs font-bold uppercase tracking-wider border border-slate-100">
                                 {{ view.fullLabel }}
                             </span>
                          </div>

                          <!-- SVG Content -->
                          <div class="w-full flex items-start justify-center">
                              <InteractiveSvg 
                                 :src="view.file"
                                 :selected-parts="getPartsForView(view)"
                                 @toggle="(name) => handleToggle(name, view)"
                                 class="w-full h-auto max-h-[500px]"
                              />
                          </div>
                      </div>
                </div>
            </div>
        </div>

        <!-- Single View Mode (Tabs) -->
        <div v-else class="flex flex-col gap-4">
            <!-- View Switcher -->
            <div :class="['flex flex-col gap-3 justify-center items-center', embedded ? '' : 'bg-slate-50 p-3 rounded-xl border border-slate-200']">
                <div v-for="(group, idx) in viewGroups" :key="idx" class="flex items-center gap-3">
                    <span :class="['text-[10px] font-bold uppercase tracking-wider w-16 text-right', embedded ? 'text-slate-400' : 'text-slate-500']">{{ group.label }}</span>
                    <div class="flex gap-1">
                        <button 
                            type="button"
                            v-for="view in group.options" 
                            :key="view.id"
                            @click="currentView = view.id"
                            class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all border min-w-[3rem]"
                            :class="currentView === view.id 
                                ? 'bg-indigo-600 text-white border-indigo-600 shadow-md transform scale-105' 
                                : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-100 hover:border-slate-300'"
                        >
                            {{ view.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Interactive SVG Area -->
            <div :class="[
                'relative overflow-hidden flex flex-col transition-all',
                embedded ? 'bg-transparent' : 'bg-white rounded-2xl border border-slate-200 shadow-sm min-h-[600px]'
            ]">
                <div class="p-4 flex-1 relative flex justify-center items-start overflow-auto custom-scrollbar">
                    <!-- Wrapper to constraint size -->
                    <div class="relative w-full" :class="embedded ? 'min-h-[1000px] max-w-none' : 'h-[600px] max-w-[500px]'">
                        <InteractiveSvg 
                            :src="currentSvgParams.file" 
                            :selected-parts="selectedParts"
                            @toggle="handleToggle"
                            class="w-full h-full"
                            :class="{ 'unconstrained': embedded }"
                        />
                    </div>
                </div>
                
                <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm text-[10px] text-slate-400 font-mono">
                    View: {{ currentSvgParams.fullLabel }}
                </div>
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
