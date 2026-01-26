<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';

const props = defineProps({
    src: {
        type: String,
        required: true,
    },
    selectedParts: {
        type: Array,
        default: () => [],
    },
    viewBox: {
        type: String,
        default: null
    }
});

const emit = defineEmits(['toggle']);

const svgContent = ref('');
const container = ref(null);

const fetchSvg = async () => {
    try {
        const response = await fetch(props.src);
        if (!response.ok) throw new Error('Failed to load SVG');
        let text = await response.text();
        
        // Remove existing scripts to prevent interference and inline event handlers
        text = text.replace(/<script\b[^>]*>([\s\S]*?)<\/script>/gim, "");
        text = text.replace(/onclick="[^"]*"/gim, "");
        text = text.replace(/onmouseover="[^"]*"/gim, "");
        
        // Ensure SVG takes full size
        if (!text.includes('width="100%"')) {
             text = text.replace('<svg ', '<svg width="100%" height="100%" ');
        }
        
        svgContent.value = text;
        
        nextTick(() => {
            setupInteractions();
            updateHighlights();
        });
    } catch (e) {
        console.error('Error loading SVG:', e);
        svgContent.value = '<div class="text-rose-500 p-4">Failed to load body map.</div>';
    }
};

const setupInteractions = () => {
    if (!container.value) return;
    
    // Attach single listener to container for delegation
    container.value.addEventListener('click', handleSvgClick);
};

const cleanName = (rawName) => {
    if (!rawName) return '';
    // Smart cleaning for Illustrator IDs
    // Replace long digit sequences (e.g., _000123...) with nothing, but preserve surrounding structure
    // Regex to find _ followed by at least 5 digits
    let name = rawName.replace(/_\d{5,}/g, '');
    
    // cleanup double underscores or trailing/leading underscores
    name = name.replace(/__+/g, '_').replace(/_$/, '').replace(/^_/, '');
    
    return name;
};

const handleSvgClick = (event) => {
    // Find closest interactive element
    const interactiveClasses = ['.muscle', '.interactive-muscle', '.interactive-muscle-group', '.static-part']; 
    
    let target = event.target;
    let interactiveElement = null;
    
    // Traverse up
    while (target && target !== container.value) {
        if (target.classList) {
             const isInteractive = interactiveClasses.some(cls => target.matches(cls)) || target.getAttribute('data-name');
             if (isInteractive) {
                 interactiveElement = target;
                 break;
             }
        }
        target = target.parentNode;
    }
    
    if (interactiveElement) {
        // Priority: data-name -> id
        let name = interactiveElement.getAttribute('data-name') || interactiveElement.id;
        
        // Fallback or prefer parent if it has a better name (and child is generic)
        if (interactiveElement.parentNode && interactiveElement.parentNode !== container.value) {
             const pName = interactiveElement.parentNode.getAttribute('data-name') || interactiveElement.parentNode.id;
             // If local name is missing or looks like "Layer_X" or generic, try parent
             if (!name || name.startsWith('Layer_')) {
                 if (pName) name = pName;
             }
        }
        
        if (name) {
            emit('toggle', cleanName(name));
        }
    }
};

const updateHighlights = () => {
    if (!container.value) return;
    
    const allElements = container.value.querySelectorAll('*');
    allElements.forEach(el => {
        if (el.classList) {
            el.classList.remove('selected', 'muscle-highlight', 'active');
            el.style.fill = ''; 
        }
    });
    
    props.selectedParts.forEach(partName => {
        let el = null;
        
        // 1. Try exact data-name match (most reliable for labeled SVGs)
        el = container.value.querySelector(`[data-name="${partName}"]`);
        
        // 2. Try exact ID match
        if (!el) {
            el = container.value.querySelector(`[id="${partName}"]`);
        }
        
        // 3. Fuzzy match using same cleaning logic
        if (!el) {
             const candidates = container.value.querySelectorAll('[id], [data-name]');
             for (let cand of candidates) {
                 const raw = cand.getAttribute('data-name') || cand.id;
                 if (raw && cleanName(raw) === partName) {
                     el = cand;
                     break;
                 }
             }
        }

        if (el) {
            el.classList.add('selected');
            
            // Bring to front logic: Move element to the end of its parent's children
            // This ensures the red border draws ON TOP of adjacent muscles
            if (el.parentNode) {
                el.parentNode.appendChild(el);
            }
        }
    });
};

watch(() => props.src, fetchSvg, { immediate: true });
watch(() => props.selectedParts, () => {
    nextTick(updateHighlights);
}, { deep: true });

</script>

<template>
    <div ref="container" class="interactive-svg-container w-full h-full flex justify-center items-center overflow-hidden" v-html="svgContent">
    </div>
</template>

<style>
/* Global SVG Overrides when inside this container */
.interactive-svg-container svg {
    max-height: 100%;
    width: 100%;
    height: auto;
    transition: all 0.3s ease;
}

.interactive-svg-container .muscle, 
.interactive-svg-container .interactive-muscle,
.interactive-svg-container .interactive-muscle-group,
.interactive-svg-container .static-part { 
    cursor: pointer; 
    transition: fill 0.2s ease, stroke 0.2s ease;
    fill: #ffffff; /* Default fill */
    stroke: #475569; /* Slate 600 - Darker for visibility */
    stroke-width: 1px; /* Thicker */
    vector-effect: non-scaling-stroke; /* Keep stroke constant on zoom */
}

/* Fix for internal detail lines in some SVGs (like hands/feet) that rely on opacity */
.interactive-svg-container .detail-line {
    stroke: #475569 !important;
    stroke-width: 0.75px !important;
    stroke-opacity: 1 !important;
    opacity: 1 !important;
    fill: none !important;
}

/* Hover effects */
.interactive-svg-container .muscle:hover,
.interactive-svg-container .interactive-muscle:hover,
.interactive-svg-container .interactive-muscle-group:hover,
.interactive-svg-container .static-part:hover {
    fill: #fecdd3 !important; /* Rose 200 */
    opacity: 0.8;
}

/* Selected state - Aggressive Red */
.interactive-svg-container .selected,
.interactive-svg-container .muscle-highlight,
.interactive-svg-container .active {
    fill: #ef4444 !important; /* Red 500 */
    stroke: #7f1d1d !important; /* Red 900 - Darker border for selected */
    opacity: 1 !important;
    stroke-width: 1.5px !important;
}

/* Ensure children inherit the red color if the group is selected */
.interactive-svg-container .selected path,
.interactive-svg-container .selected rect,
.interactive-svg-container .selected circle,
.interactive-svg-container .selected polygon,
.interactive-svg-container .selected ellipse,
.interactive-svg-container .muscle-highlight path,
.interactive-svg-container .active path {
    fill: #ef4444 !important;
    stroke: #991b1b !important; 
}
</style>
