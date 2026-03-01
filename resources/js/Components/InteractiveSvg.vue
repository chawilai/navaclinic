<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { translateBodyPart } from '@/Utils/BodyPartTranslations';

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

const hoveredPart = ref(null);
const tooltipStyle = ref({ top: '0px', left: '0px', opacity: 0 });
const showTooltip = ref(false);

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
    container.value.addEventListener('mouseover', handleMouseOver);
    container.value.addEventListener('mouseout', handleMouseOut);
    container.value.addEventListener('mousemove', handleMouseMove);
};

const cleanupInteractions = () => {
    if (!container.value) return;
    container.value.removeEventListener('click', handleSvgClick);
    container.value.removeEventListener('mouseover', handleMouseOver);
    container.value.removeEventListener('mouseout', handleMouseOut);
    container.value.removeEventListener('mousemove', handleMouseMove);
};

onBeforeUnmount(() => {
    cleanupInteractions();
});

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

const getPartByName = (foundName) => {
    let match = props.selectedParts.find(p => p.id === foundName || (p.rawItem && cleanName(p.rawItem.area) === foundName));
    if (match) return match;
    return props.selectedParts.find(p => {
        const base = p.id.replace(/(_L|_R|_Left|_Right)$/i, '');
        return base === foundName;
    });
};

const handleMouseOver = (event) => {
    const target = event.target;
    let current = target;
    let foundName = null;
    
    while (current && current !== container.value) {
        if (current.classList && current.classList.contains('interactive-part')) {
            foundName = current.getAttribute('data-clean-name') || cleanName(current.getAttribute('data-name') || current.id);
            break;
        }
        current = current.parentNode;
    }

    if (foundName) {
        const partData = getPartByName(foundName);
        if (partData) {
            hoveredPart.value = partData;
            showTooltip.value = true;
        } else {
            hoveredPart.value = { id: foundName, rawItem: null, isUnselected: true };
            showTooltip.value = true;
        }
    }
};

const handleMouseOut = (event) => {
    showTooltip.value = false;
    hoveredPart.value = null;
};

const handleMouseMove = (event) => {
    if (showTooltip.value) {
        tooltipStyle.value = {
            top: `${event.clientY + 15}px`,
            left: `${event.clientX + 15}px`,
            opacity: 1
        };
    }
};

const updateHighlights = () => {
    if (!container.value) return;
    
    // Clear badges
    const badges = container.value.querySelectorAll('.part-badge');
    badges.forEach(b => b.remove());

    // Reset all highlights
    const all = container.value.querySelectorAll('.selected, .muscle-highlight, .active');
    all.forEach(el => {
        el.classList.remove('selected', 'muscle-highlight', 'active');
        el.style.removeProperty('fill');
        el.style.removeProperty('stroke');
        
        const children = el.querySelectorAll('*');
        children.forEach(child => {
            if (child.style) {
                 child.style.removeProperty('fill');
                 child.style.removeProperty('stroke');
            }
        });
    });

    // Helper: Find element for a part
    const findPartElement = (partName) => {
        let el = findElement(partName);
        if (!el) {
            const baseName = partName.replace(/(_L|_R|_Left|_Right)$/, '');
            if (baseName !== partName) {
                el = findElement(baseName);
            }
        }
        return el;
    };

    // Pass 1: Highlight and Bring to Front
    props.selectedParts.forEach(part => {
        const partName = typeof part === 'object' ? part.id : part;
        const el = findPartElement(partName);

        if (el) {
            el.classList.add('selected');
            
            if (typeof part === 'object' && part.rawItem) {
                let level = null;
                const before = parseInt(part.rawItem.pain_level);
                const after = parseInt(part.rawItem.pain_level_after);

                if (!isNaN(before) && before > 0) level = before;
                if (!isNaN(after) && after > 0) level = after;

                if (level) {
                    const hue = Math.max(0, 130 - ((level - 1) * (130 / 9)));
                    const bgColor = `hsl(${hue}, 85%, 50%)`;
                    const strokeColor = `hsl(${hue}, 85%, 40%)`;

                    el.style.setProperty('fill', bgColor, 'important');
                    el.style.setProperty('stroke', strokeColor, 'important');
                    
                    const children = el.querySelectorAll('path, rect, circle, polygon, ellipse, line, polyline');
                    children.forEach(child => {
                        child.style.setProperty('fill', bgColor, 'important');
                        child.style.setProperty('stroke', strokeColor, 'important');
                    });
                }
            }

            if (el.tagName !== 'g' && el.parentNode) {
                el.parentNode.appendChild(el);
            }
        }
    });

    // Pass 2: Draw Badges
    const svg = container.value.querySelector('svg');
    let scaleFactor = 1;
    
    // Calculate scale factor based on viewbox to keep badges consistent
    if (svg && svg.viewBox && svg.viewBox.baseVal) {
        const vb = svg.viewBox.baseVal;
        // logic: if viewbox is 500 wide, scale is 1. If 1000, scale 2.
        const dim = Math.min(vb.width, vb.height);
        if (dim > 0) scaleFactor = dim / 500;
    }

    props.selectedParts.forEach(part => {
        // Only draw if we have an index (object mode)
        if (typeof part !== 'object' || !part.index) return;
        
        const partName = part.id;
        const el = findPartElement(partName);
        
        if (el) {
            const bbox = el.getBBox();
            const cx = bbox.x + bbox.width / 2;
            const cy = bbox.y + bbox.height / 2;
            
            const group = document.createElementNS("http://www.w3.org/2000/svg", "g");
            group.setAttribute("class", "part-badge");
            group.style.pointerEvents = "none";
            
            // Sizes
            const r = 14 * scaleFactor; 
            const fontSize = 14 * scaleFactor;
            const strokeWidth = 2 * scaleFactor;
            
            // Color logic
            let badgeBg = "#ef4444";
            if (part.rawItem) {
                let level = null;
                const before = parseInt(part.rawItem.pain_level);
                const after = parseInt(part.rawItem.pain_level_after);
                if (!isNaN(before) && before > 0) level = before;
                if (!isNaN(after) && after > 0) level = after;

                if (level) {
                    const hue = Math.max(0, 130 - ((level - 1) * (130 / 9)));
                    badgeBg = `hsl(${hue}, 85%, 50%)`;
                }
            }
            
            // Circle
            const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
            circle.setAttribute("cx", cx);
            circle.setAttribute("cy", cy);
            circle.setAttribute("r", r);
            circle.setAttribute("fill", badgeBg);
            circle.setAttribute("stroke", "#ffffff");
            circle.setAttribute("stroke-width", strokeWidth);
            
            // Text
            const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
            text.setAttribute("x", cx);
            text.setAttribute("y", cy);
            text.setAttribute("dy", "0.35em"); // vertically center
            text.setAttribute("text-anchor", "middle");
            text.setAttribute("fill", "white");
            text.setAttribute("font-size", fontSize);
            text.setAttribute("font-weight", "bold");
            text.setAttribute("font-family", "Arial, sans-serif");
            text.textContent = part.index;
            
            group.appendChild(circle);
            group.appendChild(text);
            
            // Append to element's parent (after element)
            if (el.parentNode) {
                el.parentNode.appendChild(group);
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
    <div class="relative w-full h-full">
        <div ref="container" class="interactive-svg-container w-full h-full flex justify-center items-center" v-html="svgContent">
        </div>

        <!-- Popover / Tooltip -->
        <Teleport to="body">
            <div v-if="showTooltip && hoveredPart"
                 class="fixed z-[9999] pointer-events-none transition-opacity duration-200"
                 :style="tooltipStyle"
            >
                <div class="bg-slate-800 text-white text-xs rounded-xl shadow-xl border border-slate-700 p-3 min-w-[150px] max-w-[250px]">
                    <div class="font-bold flex items-center gap-2 mb-1">
                        <span v-if="hoveredPart.index" class="w-4 h-4 rounded-full bg-red-500 text-white flex items-center justify-center text-[9px]">{{ hoveredPart.index }}</span>
                        <span class="text-indigo-200">{{ translateBodyPart(hoveredPart.rawItem?.area || hoveredPart.id) }}</span>
                    </div>
                    
                    <div v-if="hoveredPart.rawItem" class="flex flex-col gap-1 mt-2 pt-2 border-t border-slate-600">
                        <div v-if="hoveredPart.rawItem.symptom" class="text-slate-300">
                            <span class="text-slate-400">อาการ:</span> {{ hoveredPart.rawItem.symptom }}
                        </div>
                        <div v-if="hoveredPart.rawItem.pain_level || hoveredPart.rawItem.pain_level_after" class="flex flex-wrap items-center gap-2 mt-1">
                            <span class="text-slate-400">VAS:</span>
                            <span v-if="hoveredPart.rawItem.pain_level" class="font-bold px-1.5 py-0.5 rounded bg-slate-700">{{ hoveredPart.rawItem.pain_level }}</span>
                            <svg v-if="hoveredPart.rawItem.pain_level && hoveredPart.rawItem.pain_level_after" xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-slate-500 mx-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            <span v-if="hoveredPart.rawItem.pain_level_after" class="font-bold px-1.5 py-0.5 rounded bg-slate-700 text-emerald-400">{{ hoveredPart.rawItem.pain_level_after }}</span>
                        </div>
                    </div>
                    <div v-else class="text-slate-400 mt-1 italic text-[10px]">
                        คลิกเพื่อระบุอาการ
                    </div>
                </div>
            </div>
        </Teleport>
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
