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
        
        // Remove existing scripts and inline handlers
        text = text.replace(/<script\b[^>]*>([\s\S]*?)<\/script>/gim, "");
        text = text.replace(/onclick="[^"]*"/gim, "");
        text = text.replace(/onmouseover="[^"]*"/gim, "");
        
        // Remove filters and clipping masks
        text = text.replace(/<filter\b[^>]*>([\s\S]*?)<\/filter>/gim, "");
        text = text.replace(/filter="url\(#[^"]+\)"/gim, "");
        text = text.replace(/style="[^"]*filter:[^"]*"/gim, "");
        text = text.replace(/<clipPath\b[^>]*>([\s\S]*?)<\/clipPath>/gim, "");
        text = text.replace(/clip-path="url\(#[^"]+\)"/gim, "");
        text = text.replace(/mask="url\(#[^"]+\)"/gim, "");

        // Ensure SVG takes full size
        if (!text.includes('width="100%"')) {
             text = text.replace('<svg ', '<svg width="100%" height="100%" ');
        }
        
        svgContent.value = text;
        
        nextTick(() => {
            initInteractiveElements();
            setupInteractions();
            updateHighlights();
        });
    } catch (e) {
        console.error('Error loading SVG:', e);
        svgContent.value = '<div class="text-rose-500 p-4">Failed to load body map.</div>';
    }
};

const initInteractiveElements = () => {
    if (!container.value) return;
    const svg = container.value.querySelector('svg');
    if (!svg) return;

    // Helper to determine if a name is valid for interaction
    const isValidName = (name) => {
        if (!name) return false;
        const n = name.toLowerCase();
        // Filter out generic Illustrator names
        if (n.startsWith('layer')) return false;
        if (n.startsWith('group')) return false;
        if (n.startsWith('path')) return false;
        if (n.startsWith('line')) return false;
        if (n.startsWith('rect')) return false;
        if (n.startsWith('poly')) return false;
        if (n.startsWith('clippath')) return false;
        if (n.startsWith('image')) return false;
        if (n.includes('background')) return false;
        if (n.includes('shadow')) return false;
        if (n.includes('outline')) return false;
        return true;
    };

    // Auto-tag elements that have meaningful IDs or data-names
    const potentialParts = svg.querySelectorAll('[id], [data-name]');
    potentialParts.forEach(el => {
        const rawId = el.id;
        const rawDataName = el.getAttribute('data-name');
        
        // Check if explicitly marked as static
        if (el.classList.contains('static-part')) return;

        // Use data-name first, then ID
        const name = rawDataName || rawId;
        
        // Clean and validate
        const cleaned = cleanName(name);
        
        if (isValidName(cleaned)) {
            // Apply class for interaction
            el.classList.add('interactive-part');
            
            // Ensure strict pointer events
            el.style.pointerEvents = 'all';
             // Store cleaned name for easy access
            el.setAttribute('data-clean-name', cleaned);
        }
    });
};

const setupInteractions = () => {
    if (!container.value) return;
    container.value.addEventListener('click', handleSvgClick);
};

const cleanName = (rawName) => {
    if (!rawName) return '';
    // Smart cleaning for Illustrator IDs
    // Remove variations like _x31_, _1_, etc if they appear garbage-like
    // But preserve meaningful suffixes like _L, _R
    let name = rawName;
    
    // Remove "Layer_X" prefix if present inside a string (though isValidName catches most)
    name = name.replace(/^Layer_\d+_/i, '');
    
    // Replace hex codes often found in Illustrator exports (e.g. _x30_)
    name = name.replace(/_x[0-9a-fA-F]{2,}_/g, '');

    // Replace long digit sequences (ids)
    name = name.replace(/_\d{5,}/g, '');
    
    // cleanup double underscores
    name = name.replace(/__+/g, '_').replace(/_$/, '').replace(/^_/, '');
    
    return name;
};

const handleSvgClick = (event) => {
    const target = event.target;
    
    // Traverse up to find the closest interactive part
    let current = target;
    while (current && current !== container.value) {
        if (current.classList && current.classList.contains('interactive-part')) {
            const name = current.getAttribute('data-clean-name') || cleanName(current.getAttribute('data-name') || current.id);
            if (name) {
                emit('toggle', name, event, current); // Emit event and element
                event.stopPropagation();
                return;
            }
        }
        current = current.parentNode;
    }
};

const updateHighlights = () => {
    if (!container.value) return;
    
    // Reset all
    const all = container.value.querySelectorAll('.selected, .muscle-highlight, .active');
    all.forEach(el => {
        el.classList.remove('selected', 'muscle-highlight', 'active');
        el.style.fill = ''; 
    });
    
    // Highlight specific items
    props.selectedParts.forEach(partName => {
        // 1. Try exact match
        let el = findElement(partName);
        
        // 2. Try stripping _L / _R / _Left / _Right suffix
        // This allows 'part_1_L' to highlight 'part_1'
        if (!el) {
            const baseName = partName.replace(/(_L|_R|_Left|_Right)$/, '');
            if (baseName !== partName) {
                el = findElement(baseName);
            }
        }

        if (el) {
            el.classList.add('selected');
            if (el.tagName !== 'g' && el.parentNode) {
                el.parentNode.appendChild(el);
            }
        }
    });
};

const findElement = (name) => {
    // Try data-clean-name
    let el = container.value.querySelector(`.interactive-part[data-clean-name="${name}"]`);
    // Fallback to data-name or id
    if (!el) el = container.value.querySelector(`[data-name="${name}"]`);
    if (!el) el = container.value.querySelector(`[id="${name}"]`);
    return el;
};

watch(() => props.src, fetchSvg, { immediate: true });
watch(() => props.selectedParts, () => {
    nextTick(updateHighlights);
}, { deep: true });

</script>

<template>
    <div ref="container" class="interactive-svg-container w-full h-full flex justify-center items-center" v-html="svgContent">
    </div>
</template>

<style>
/* Global SVG Overrides inside container */
.interactive-svg-container svg {
    max-height: 100%;
    width: 100%;
    height: auto;
    overflow: visible !important;
    transition: all 0.3s ease;
    filter: none !important;
    transform: scale(0.95);
    transform-origin: center;
    pointer-events: none; /* Disable global pointer events */
}

.interactive-svg-container.unconstrained svg {
    max-height: none;
}

/* 
   Normalize ALL strokes to be equal 
   This fixes specific body parts having thicker/thinner lines 
*/
.interactive-svg-container svg path,
.interactive-svg-container svg rect,
.interactive-svg-container svg circle,
.interactive-svg-container svg polygon,
.interactive-svg-container svg ellipse,
.interactive-svg-container svg line,
.interactive-svg-container svg polyline {
    stroke: #64748b !important; /* Slate 500 */
    stroke-width: 0.75px !important; /* Fine, uniform lines */
    stroke-linecap: round !important;
    stroke-linejoin: round !important;
    vector-effect: non-scaling-stroke !important; /* Prevent scaling distortion */
    fill: none; /* Default to no fill for safety */
}

/* Enable pointer events & specific styling ONLY on interactive parts */
.interactive-svg-container .interactive-part { 
    pointer-events: all !important;
    cursor: pointer; 
    transition: all 0.2s ease;
    fill: #ffffff !important; /* Force white fill for interactive areas */
    stroke: #475569 !important; /* Slate 600 - Slightly darker for interactive */
    stroke-width: 0.75px !important; /* Match the global width */
}

/* Hover effects */
.interactive-svg-container .interactive-part:hover {
    fill: #86efac !important; /* Green 300 */
    stroke: #16a34a !important; /* Green 600 */
    stroke-width: 1px !important; /* Subtle bold on hover */
    z-index: 10;
}

/* Selected state */
.interactive-svg-container .selected,
.interactive-svg-container .muscle-highlight,
.interactive-svg-container .active {
    fill: #ef4444 !important; /* Red 500 */
    stroke: #7f1d1d !important; /* Red 900 */
    opacity: 1 !important;
    stroke-width: 1px !important; /* Keep it relatively fine but distinct */
    pointer-events: all !important;
}

/* Ensure children inherit active state if group is selected */
.interactive-svg-container .selected path,
.interactive-svg-container .selected rect,
.interactive-svg-container .selected circle,
.interactive-svg-container .selected polygon {
    fill: #ef4444 !important;
    stroke: #7f1d1d !important; 
    stroke-width: 1px !important;
}
</style>
