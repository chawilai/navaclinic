
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

    'Head_eye_area': 'ตา',
    'Head_mouth_area': 'ปาก',
    'Head_forehead': 'หน้าผาก',
    'Head_ear_right': 'หู (ขวา)',
    'Head_ear_left': 'หู (ซ้าย)',
    'Head_cheeks_lower_face': 'แก้ม',
    'Head_neck': 'คอ',
    'Head_head_top': 'ศีรษะด้านบน',
    'Head_hair_left': 'ศีรษะด้านบน (ซ้าย)',
    'Head_hair_right': 'ศีรษะด้านบน (ขวา)',


    'side_front_hair': 'ศีรษะด้านข้าง: หน้าผาก',
    'side_top_head': 'ศีรษะด้านข้าง: กระหม่อม',
    'side_cheeks_lower_face': 'ศีรษะด้านข้าง: แก้ม/ขากรรไกร',
    'side_back_head': 'ศีรษะด้านข้าง: ศีรษะด้านหลัง',
    'side_ear': 'ศีรษะด้านข้าง: หู',
    'side_head_upper': 'ศีรษะด้านข้าง: เหนือหู',
    'side_head_lower': 'ศีรษะด้านข้าง: ขมับ',

    // Adjusted based on user feedback
    'side_neck_front': 'ศีรษะด้านข้าง: ปาก',
    'side_neck_mid': 'ศีรษะด้านข้าง: คอด้านข้าง',
    'side_neck_back': 'ศีรษะด้านข้าง: คอด้านหลัง',

    // ----------------------------------------------------
    // 2. VIEW PREFIXES (With Directions)
    // ----------------------------------------------------
    'Head_': 'ศีรษะ (หน้า) ',
    'HeadSide_': '',
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

    // Normalize: Replace spaces with underscores and trim
    // This fixes issues where data might have been saved with spaces (e.g. "UpperLegB upper leg back part 6 R")
    name = name.trim().replace(/\s+/g, '_');


    // ----------------------------------------------------
    // Step 1: Handle "Part X" Patterns
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
