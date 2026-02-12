
const dictionary = {
    // ----------------------------------------------------
    // 1. SPECIFIC BODY PARTS (Longest First)
    // ----------------------------------------------------
    'UpperLegF_Thigh': 'ต้นขาหน้า',
    'LowerLegF_Knee': 'หัวเข่า (หน้า)',
    'LowerLegF_Shin': 'หน้าแข้ง',
    'LowerLegF_Ankle': 'ข้อเท้า (หน้า)',
    'LowerLegF_Foot': 'เท้า (หน้า)',

    'UpperLegB_Hamstring': 'ต้นขาหลัง',
    'LowerLegB_Calf': 'น่อง (หลัง)',
    'LowerLegB_Heel': 'ส้นเท้า',

    'SideLeg_Hip': 'สะโพก (ข้าง)',
    'SideLeg_Thigh': 'ต้นขา (ข้าง)',
    'SideLeg_Knee': 'เข่า (ข้าง)',
    'SideLeg_Calf': 'น่อง (ข้าง)',
    'SideLeg_Ankle': 'ตาตุ่ม',

    'Biceps': 'ต้นแขน (หน้า)',
    'Arm_Elbow': 'ข้อศอก',
    'Arm_Forearm': 'แขนท่อนล่าง',
    'Arm_Wrist': 'ข้อมือ',
    'Arm_Hand': 'มือ',

    'UpperBack_Neck': 'ต้นคอ',
    'UpperBack_Traps': 'บ่า',
    'UpperBack_Scapula': 'สะบัก',
    'UpperBack_Spine': 'กระดูกสันหลัง (อก)',

    'MidBack_Spine': 'กระดูกสันหลัง (เอว)',
    'MidBack': 'หลังกลาง',

    'LowerBack_Spine': 'กระดูกสันหลัง (ก้นกบ)',
    'LowerBack_Glute': 'แก้มก้น',
    'LowerBack': 'หลังล่าง',

    'UpperFront_Chest': 'หน้าอก',
    'UpperFront_Shoulder': 'หัวไหล่ (หน้า)',
    'UpperFront_Neck': 'คอ (หน้า)',
    'UpperFront_Clavicle': 'ไหปลาร้า',

    'MidFront_Stomach': 'ท้องน้อย',
    'MidFront_Abs': 'หน้าท้อง',
    'MidFront_Ribs': 'ซี่โครง (หน้า)',

    'Head_Forehead': 'หน้าผาก',
    'Head_Eye': 'ตา',
    'Head_Nose': 'จมูก',
    'Head_Mouth': 'ปาก',
    'Head_Chin': 'คาง',
    'Head_Cheek': 'แก้ม',
    'Head_Ear': 'หู',
    'Head_Hair': 'ศีรษะ (ผม)',
    'Head_Head_Top': 'กลางกระหม่อม',
    'Head_Neck': 'คอ',

    'HeadSide_Temple': 'ขมับ',
    'HeadSide_Jaw': 'กราม',
    'HeadSide_Ear': 'หู',
    'HeadSide_Neck': 'คอ (ข้าง)',

    // ----------------------------------------------------
    // 2. VIEW PREFIXES (With Directions)
    // ----------------------------------------------------
    'Head_': 'ศีรษะ (หน้า) ',
    'HeadSide_': 'ศีรษะ (ข้าง) ',
    'UpperFront_': 'ลำตัวบน (หน้า) ',
    'MidFront_': 'ลำตัวกลาง (หน้า) ',
    'UpperBack_': 'หลังบน ',
    'MidBack_': 'หลังกลาง ',
    'LowerBack_': 'หลังล่าง ',
    'Biceps_': 'ต้นแขน (หน้า) ',
    'Arm_': 'แขน (หน้า) ', // Arm model implies front
    'UpperLegF_': 'ขาบน (หน้า) ',
    'LowerLegF_': 'ขาล่าง (หน้า) ',
    'UpperLegB_': 'ขาบน (หลัง) ',
    'LowerLegB_': 'ขาล่าง (หลัง) ',
    'SideLeg_': 'ขา (ข้าง) ',

    // ----------------------------------------------------
    // 3. GENERIC TERMS & FALLBACKS
    // ----------------------------------------------------
    'Neck': 'คอ',
    'Shoulder': 'ไหล่',
    'Back': 'หลัง',
    'Leg': 'ขา',
    'Arm': 'แขน',
    'Hand': 'มือ',
    'Foot': 'เท้า',
    'Face': 'หน้า',
    'Head': 'ศีรษะ',
    'Chest': 'หน้าอก',
    'Stomach': 'ท้อง',

    // Directions (Suffixes)
    '_L': ' (ซ้าย)',
    '_R': ' (ขวา)',
    'Left': ' (ซ้าย)',
    'Right': ' (ขวา)',
};

export const translateBodyPart = (partName) => {
    if (!partName) return '';
    let name = partName;

    // ----------------------------------------------------
    // Step 1: Handle Known Specific IDs
    // ----------------------------------------------------
    if (name.includes('cheeks_lower_face')) name = name.replace('cheeks_lower_face', 'แก้มล่าง');
    if (name.includes('head_top')) name = name.replace('head_top', 'กลางกระหม่อม');
    if (name.includes('hair')) name = name.replace('hair', 'ศีรษะ');

    // ----------------------------------------------------
    // Step 2: Handle "Part X" Patterns
    // ----------------------------------------------------
    // Map generic parts to "Point X"
    const partReplacer = (match, p1) => `จุดที่ ${p1}`;

    name = name.replace(/full_upper_back_part_(\d+)/gi, partReplacer);
    name = name.replace(/full_middle_back_part_(\d+)/gi, partReplacer);
    name = name.replace(/full_lower_back_part_(\d+)/gi, partReplacer);

    name = name.replace(/upper_leg_back_part_(\d+)/gi, partReplacer);
    name = name.replace(/upper_leg_front_part_(\d+)/gi, partReplacer);

    name = name.replace(/lower_leg_back_part_(\d+)/gi, partReplacer);
    name = name.replace(/lower_leg_front_part_(\d+)/gi, partReplacer);

    name = name.replace(/upper_front_part_(\d+)/gi, partReplacer);
    name = name.replace(/middle_front_part_(\d+)/gi, partReplacer);

    name = name.replace(/side_leg_part_(\d+)/gi, partReplacer);
    name = name.replace(/bizeps2_part_(\d+)/gi, partReplacer);

    // General Catch-all
    name = name.replace(/_part_(\d+)/gi, ' จุดที่ $1');
    name = name.replace(/part_(\d+)/gi, 'จุดที่ $1');

    // ----------------------------------------------------
    // Step 3: Dictionary Replacement (Longest Match First)
    // ----------------------------------------------------
    const keys = Object.keys(dictionary).sort((a, b) => b.length - a.length);

    for (const key of keys) {
        const regex = new RegExp(key, 'gi');
        if (name.match(regex)) {
            name = name.replace(regex, dictionary[key]);
        }
    }

    // ----------------------------------------------------
    // Step 4: Final Cleanup
    // ----------------------------------------------------
    name = name.replace(/_/g, ' ');
    name = name.replace(/\s+/g, ' ');

    return name.trim();
};
