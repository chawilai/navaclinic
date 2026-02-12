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
    compactGrid: {
        type: Boolean,
        default: false
    },
    thumbnail: {
        type: Boolean,
        default: false
    },
    overview: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:modelValue']);

    // View Management
    const currentView = ref('upper_front');
    
    const viewGroups = [
        {
            label: 'ด้านหน้า',
            options: [
                { id: 'head_v2', label: 'ศีรษะ (หน้า)', fullLabel: 'ศีรษะ (ด้านหน้า)', file: '/images/body/v2/head_model2.svg', prefix: 'Head_' },
                { id: 'upper_front_v2', label: 'อก/ไหล่ (หน้า)', fullLabel: 'ลำตัวบน (ด้านหน้า)', file: '/images/body/v2/upper_front.svg', prefix: 'UpperFront_' },
                { id: 'middle_front_v2', label: 'ท้อง (หน้า)', fullLabel: 'ลำตัวกลาง (ด้านหน้า)', file: '/images/body/v2/middle_front.svg', prefix: 'MidFront_' },
                { id: 'arm_model_v2', label: 'แขน (หน้า)', fullLabel: 'แขน (ด้านหน้า)', file: '/images/body/v2/arm_model3.svg', prefix: 'Arm_' },
                { id: 'bizeps_v2', label: 'กล้ามแขน (หน้า)', fullLabel: 'กล้ามแขน (ด้านหน้า)', file: '/images/body/v2/Bizeps2.svg', prefix: 'Biceps_' },
                { id: 'upper_leg_front_v2', label: 'ขาบน (หน้า)', fullLabel: 'ขาบน (ด้านหน้า)', file: '/images/body/v2/upper_leg_front.svg', prefix: 'UpperLegF_' },
                { id: 'lower_leg_front_v2', label: 'ขาล่าง (หน้า)', fullLabel: 'ขาล่าง (ด้านหน้า)', file: '/images/body/v2/lower_leg_front.svg', prefix: 'LowerLegF_' },
            ]
        },
        {
            label: 'ด้านหลัง',
            options: [
                { id: 'full_upper_back_v2', label: 'หลังบน (หลัง)', fullLabel: 'หลังบน (ด้านหลัง)', file: '/images/body/v2/full_upper_back.svg', prefix: 'UpperBack_' },
                { id: 'full_middle_back_v2', label: 'หลังกลาง (หลัง)', fullLabel: 'หลังกลาง (ด้านหลัง)', file: '/images/body/v2/full_middle_back.svg', prefix: 'MidBack_' },
                { id: 'full_lower_back_v2', label: 'หลังล่าง (หลัง)', fullLabel: 'หลังล่าง (ด้านหลัง)', file: '/images/body/v2/Full_lower_back.svg', prefix: 'LowerBack_' },
                { id: 'upper_leg_back_v2', label: 'ขาบน (หลัง)', fullLabel: 'ขาบน (ด้านหลัง)', file: '/images/body/v2/upper_leg_back.svg', prefix: 'UpperLegB_' },
                { id: 'lower_leg_back_v2', label: 'ขาล่าง (หลัง)', fullLabel: 'ขาล่าง (ด้านหลัง)', file: '/images/body/v2/lower_leg_back.svg', prefix: 'LowerLegB_' },
            ]
        },
        {
            label: 'ด้านข้าง',
            options: [
                { id: 'head_side_v2', label: 'ศีรษะ (ข้าง)', fullLabel: 'ศีรษะ (ด้านข้าง)', file: '/images/body/v2/head_side2.svg', prefix: 'HeadSide_' },
                { id: 'side_leg_v2', label: 'ขา (ข้าง)', fullLabel: 'ขา (ด้านข้าง)', file: '/images/body/v2/side_leg.svg', prefix: 'SideLeg_' },
            ]
        }
    ];
    
    const allViews = computed(() => viewGroups.flatMap(g => g.options));
    
    const currentSvgParams = computed(() => {
        return allViews.value.find(v => v.id === currentView.value) || allViews.value[0];
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
                // Should not happen with new setup as all have prefixes
                acc.push(rawName);
            }
            return acc;
        }, []);
    };
    
    // Selection Logic for Single View Mode
    const selectedParts = computed(() => {
        return getPartsForView(currentSvgParams.value);
    });
    
    const handleToggle = (rawPartName, specificViewParams = null, event, element) => {
        if (props.readonly) return;
        
        // If specificViewParams is passed (Expand All Mode), use it. Otherwise use current global view.
        const viewParams = specificViewParams || currentSvgParams.value;
        const prefix = viewParams?.prefix || '';
        let partName = prefix + rawPartName;
        
        // --- Determine Side (L/R) if not present ---
        if (event && element) {
            // Check if name already has side info
            const hasSide = partName.match(/(_L|_R|_Left|_Right)$/i);
            
            if (!hasSide) {
                const rect = element.getBoundingClientRect();
                const svgRect = element.closest('svg').getBoundingClientRect();
                
                const centerX = svgRect.left + (svgRect.width / 2);
                const elemX = rect.left + (rect.width / 2);
                
                // Threshold for "Center" parts (e.g. spine)
                const threshold = svgRect.width * 0.05; // 5% center zone
                
                if (Math.abs(elemX - centerX) > threshold) {
                    const isScreenLeft = elemX < centerX;
                    
                    // Determine contextual Left/Right
                    // Front View: Screen Left = Patient Right
                    // Back View: Screen Left = Patient Left
                    const isFrontView = viewParams.label.includes('หน้า') || viewParams.fullLabel.includes('หน้า');
                    
                    if (isFrontView) {
                         partName += isScreenLeft ? '_R' : '_L';
                    } else {
                         // Back or maybe side (default to screen logic??)
                         // For Back view: Screen Left is Patient Left.
                         partName += isScreenLeft ? '_L' : '_R';
                    }
                }
            }
        }
        
        // Check if exists (Check for exact match OR base match if we just added suffix)
        // Actually, we want to toggle specific side if added. 
        // If user clicks Left side, toggle Left side.
        
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
        <div class="flex flex-col gap-4" :class="{ 'h-full': thumbnail || compactGrid }">
            
            <!-- Compact Grid Mode (Updated for new assets) -->
            <div v-if="compactGrid" class="w-full h-full p-2 overflow-y-auto custom-scrollbar">
                 <div class="grid grid-cols-4 gap-2">
                     <div v-for="view in allViews" :key="view.id" 
                          class="relative flex flex-col items-center justify-center bg-white rounded-lg border border-slate-100 p-1 shadow-sm hover:border-indigo-200 transition-colors aspect-square">
                              <div class="w-full h-full flex items-center justify-center p-1">
                                  <InteractiveSvg 
                                     :src="view.file"
                                     :selected-parts="getPartsForView(view)"
                                     class="max-w-full max-h-full w-auto h-auto"
                                  />
                              </div>
                          <span class="text-[8px] text-slate-500 font-bold uppercase mt-0.5 leading-none text-center line-clamp-1">{{ view.label }}</span>
                     </div>
                 </div>
            </div>
            
            <!-- Thumbnail Mode (Tiny Preview) -->
            <div v-else-if="thumbnail" class="grid grid-cols-4 gap-1 w-full h-full p-1 overflow-hidden">
                 <div v-for="view in allViews.slice(0, 16)" :key="view.id" 
                      class="relative flex flex-col items-center justify-center bg-white rounded border border-slate-100 p-0.5 shadow-sm">
                      <div class="w-full h-full flex items-center justify-center pointer-events-none">
                          <InteractiveSvg 
                             :src="view.file"
                             :selected-parts="getPartsForView(view)"
                             class="max-w-full max-h-full w-auto h-auto"
                          />
                      </div>
                 </div>
            </div>
    
            <!-- Overview Mode (Medium size for Dashboard) -->
            <div v-else-if="overview" class="w-full px-2 py-4 animate-fadeIn">
                 <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                     <div v-for="view in allViews" :key="view.id" 
                          class="relative flex flex-col items-center justify-center bg-white/50 rounded-xl border border-slate-100 p-2 shadow-sm hover:border-indigo-200 transition-colors h-[180px]">
                          <div class="absolute top-2 right-2 z-10">
                              <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ view.label }}</span>
                          </div>
                          <div class="w-full h-full flex items-center justify-center p-1">
                              <InteractiveSvg 
                                 :src="view.file"
                                 :selected-parts="getPartsForView(view)"
                                 class="max-w-full max-h-full w-auto h-auto"
                              />
                          </div>
                     </div>
                 </div>
            </div>
    
            <!-- Expand All Mode -->
            <div v-else-if="expandAll" class="space-y-12 animate-fadeIn py-6">
                <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 px-4">
                     <div v-for="view in allViews" :key="view.id" 
                          class="relative flex flex-col items-center bg-white rounded-xl border border-slate-100 p-4 shadow-sm">
                         
                         <div class="absolute top-2 right-2 z-10 block">
                             <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">{{ view.label }}</span>
                         </div>

                         <!-- SVG Content -->
                         <div class="w-full flex items-center justify-center h-[300px]">
                             <InteractiveSvg 
                                :src="view.file"
                                :selected-parts="getPartsForView(view)"
                                @toggle="(name, event, element) => handleToggle(name, view, event, element)"
                                class="w-full h-full"
                             />
                         </div>
                     </div>
                </div>
            </div>
    
            <!-- Show All Views Mode (Compact Grid for Zoom Modal) -->
            <div v-else-if="showAllViews" class="space-y-8 animate-fadeIn py-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                     <div v-for="view in allViews" :key="view.id" 
                          class="relative flex flex-col items-center bg-white rounded-xl border border-slate-100 p-2 shadow-sm">
                          
                          <div class="mb-2">
                             <span class="px-3 py-1 rounded-full bg-slate-50 text-slate-500 text-[10px] font-bold uppercase tracking-wider border border-slate-100">
                                 {{ view.label }}
                             </span>
                          </div>

                          <div class="w-full flex items-start justify-center h-[200px]">
                              <InteractiveSvg 
                                 :src="view.file"
                                 :selected-parts="getPartsForView(view)"
                                 @toggle="(name, event, element) => handleToggle(name, view, event, element)"
                                 class="w-full h-full"
                              />
                          </div>
                      </div>
                </div>
            </div>

        <!-- Single View Mode (Tabs) -->
        <div v-else class="flex flex-col gap-4">
            <!-- View Switcher -->
            <div :class="['', embedded ? '' : 'bg-slate-50/50 p-2 rounded-xl border border-slate-200']">
                <div class="grid grid-cols-4 sm:grid-cols-5 gap-1.5">
                    <button 
                        type="button"
                        v-for="view in allViews" 
                        :key="view.id"
                        @click="currentView = view.id"
                        class="px-1 py-1.5 text-[10px] font-bold rounded-md transition-all border flex items-center justify-center text-center leading-none min-h-[32px]"
                        :class="currentView === view.id 
                            ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' 
                            : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50 hover:border-slate-300'"
                    >
                        {{ view.label }}
                    </button>
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
                            @toggle="(name, event, element) => handleToggle(name, null, event, element)"
                            class="w-full h-full"
                            :class="{ 'unconstrained': embedded }"
                        />
                    </div>
                </div>
                
                <div class="absolute bottom-4 right-4 bg-white/90 backdrop-blur px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm text-[10px] text-slate-400 font-mono">
                    มุมมอง: {{ currentSvgParams.fullLabel }}
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

        <div v-if="!readonly" class="text-center text-slate-400 text-xs italic mt-2">
            คลิกที่อวัยวะเพื่อเลือก
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
