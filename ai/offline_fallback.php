<?php
// ai/cache/offline_fallback.php
// A flexible, keyword-driven offline fallback system with category fallbacks.
// Designed for detailed help (style C). Safe and generic troubleshooting only.

if (!function_exists('is_online')) {
    function is_online(): bool {
        $connected = @fsockopen("www.google.com", 80);
        if ($connected) { fclose($connected); return true; }
        return false;
    }
}

/**
 * Main fallback responder.
 * - $prompt: raw user prompt string
 * - returns: string response
 */
function offline_ai_response(string $prompt): string {
    $p = trim(mb_strtolower($prompt, 'UTF-8'));

    // Normalize punctuation/whitespace for simpler matching
    $p_norm = preg_replace('/[^\p{L}\p{N}\s\-]/u', ' ', $p);
    $p_norm = preg_replace('/\s+/', ' ', $p_norm);

    // Primary structured fallbacks (keyword groups -> response)
    $FALLBACKS = [
        // HVAC / Air conditioning
        [
            'keywords' => ['aircon','air conditioner','ac','air-conditioning','split ac','split-unit'],
            'response' => "Air conditioner checklist: 1) Turn off power and clean/replace the air filter (every 1–3 months). 2) Check the outdoor unit for debris and ensure clear airflow. 3) If cooling is weak, confirm thermostat settings and that vents are open. 4) For unusual noises or leaks, schedule a service technician; do not attempt refrigerant repairs yourself."
        ],
        // Refrigerator / Freezer
        [
            'keywords' => ['fridge','refrigerator','freezer','icebox'],
            'response' => "Refrigerator tips: 1) Check door seals for gaps and clean the gasket. 2) If frost builds up, defrost and check drain holes. 3) Clean condenser coils annually. 4) If not cooling, ensure the condenser fan runs and the unit has space around it for airflow. Avoid DIY refrigerant work."
        ],
        // Washing machine
        [
            'keywords' => ['washing machine','washer','washingmachine','washer not spinning','washer leaking','washer smell'],
            'response' => "Washing machine guidance: 1) Run an empty hot cycle with white vinegar monthly to reduce odors. 2) For not spinning, check for unbalanced loads and redistribute laundry. 3) Inspect hoses for leaks and tighten connections. 4) If there's a persistent vibration, check feet leveling."
        ],
        // Dryer
        [
            'keywords' => ['dryer','clothes dryer','dryer not heating','dryer vent'],
            'response' => "Dryer advice: 1) Clean the lint trap after every load. 2) Inspect and clean the exhaust vent regularly to prevent overheating and fire hazard. 3) If not heating, check the breaker and thermal fuse (or call technician)."
        ],
        // Laptop / PC
        [
            'keywords' => ['laptop','notebook','computer','pc','desktop','overheating','slow computer'],
            'response' => "Computer maintenance: 1) Regularly clean fans and vents; power down and use compressed air. 2) Check background apps and update software. 3) If overheating, remove dust and ensure proper ventilation; consider a cooling pad. 4) Back up data before major repairs."
        ],
        // Phone / Tablet
        [
            'keywords' => ['phone','smartphone','tablet','android','iphone','battery drain','slow phone'],
            'response' => "Phone/tablet tips: 1) Update OS and apps. 2) Review battery usage in settings and remove power-hungry apps. 3) For battery swelling or physical damage, stop using it and seek professional service. 4) Keep device and ports clean and dry."
        ],
        // TV / Monitor
        [
            'keywords' => ['tv','television','monitor','screen flicker','no picture'],
            'response' => "TV/Monitor troubleshooting: 1) Check cable connections and input source. 2) Test with another device to isolate HDMI/cable issues. 3) If flicker or lines appear, power cycle and test inputs; do not open high-voltage TV internals yourself."
        ],
        // Microwave / Oven
        [
            'keywords' => ['microwave','oven','stove','range','cooktop'],
            'response' => "Microwave & oven safety: 1) For microwave not heating, ensure door closes properly and circuit is fine; avoid DIY capacitor repairs. 2) Keep ovens clean, and for uneven cooking, try preheating longer or rotating food. 3) For gas cookers, check for gas supply and pilot (if applicable) and call a qualified technician for leaks."
        ],
        // Small kitchen appliances
        [
            'keywords' => ['kettle','rice cooker','blender','food processor','toaster','coffee maker'],
            'response' => "Small appliance tips: 1) Unplug and inspect power cords for damage. 2) Clean removable parts regularly and follow manufacturer maintenance. 3) If a motor hums but doesn't run, check for jammed blades or stuck mechanisms before forcing it."
        ],
        // Lights / Electrical
        [
            'keywords' => ['light','bulb','lights','electrical','socket','power','switch','breaker','short circuit'],
            'response' => "Electrical safety checklist: 1) Replace blown bulbs and test with known-good bulbs. 2) For flickering, check tightness of bulbs and switches. 3) If multiple outlets are dead, check breakers. 4) For any suspected short, exposed wiring, burning smell, or repeated breaker trips — disconnect power and get a licensed electrician."
        ],
        // Plumbing: leaks, faucet, toilet
        [
            'keywords' => ['leak','leaking','faucet','tap','toilet','plumbing','pipe','pipes','blocked drain','clog'],
            'response' => "Plumbing basics: 1) For minor leaks, turn off the fixture supply valve and tighten connections. 2) Use a plunger for simple toilet or sink clogs; avoid chemical drain cleaners if unsure. 3) For active major leaks, shut off the main water valve and call a plumber. 4) Document leaks for possible warranty/insurance claims."
        ],
        // Doors, hinges, locks
        [
            'keywords' => ['door','hinge','lock','latch','stuck door','squeak'],
            'response' => "Door & lock tips: 1) Tighten hinge screws and apply a small amount of lubricant to remove squeaks. 2) For sticky doors, check humidity/swelling and plane the edge if needed. 3) For lock problems, try lubrication or call a locksmith for security issues."
        ],
        // Furniture / Upholstery / Wood care
        [
            'keywords' => ['furniture','sofa','couch','table','wood','scratch','upholstery','stain'],
            'response' => "Furniture care: 1) For wood surfaces, clean with a mild cleaner and use polish for minor scratches. 2) Blot (don't rub) fresh stains from upholstery and test cleaners in an inconspicuous area. 3) Tighten loose screws and joints to prolong life."
        ],
        // Water heater
        [
            'keywords' => ['water heater','geyser','hot water','no hot water','heater'],
            'response' => "Water heater guidance: 1) Check power/gas supply and thermostat settings. 2) Flush sediment from tank annually for electric/gas tanks. 3) For leaks or pilot issues on gas units, shut off and seek professional service."
        ],
        // Router / WiFi
        [
            'keywords' => ['wifi','router','internet','connection','slow internet','modem'],
            'response' => "Wi-Fi troubleshooting: 1) Reboot modem/router (unplug 10 seconds). 2) Move router to a central, elevated location and reduce obstacles. 3) Check device for software updates and try wired connection to isolate the problem."
        ],
        // Battery, charger
        [
            'keywords' => ['battery','charger','charging','not charging','battery swelling'],
            'response' => "Battery & charging: 1) Use the original charger when possible and inspect cable/port for debris. 2) For battery swelling, stop using the device and dispose/replace at an authorized center. 3) Avoid full-discharge cycles frequently — shallow cycles prolong lithium battery life."
        ],
        // Pest / insect signs, mold
        [
            'keywords' => ['pest','cockroach','rodent','mice','ants','termite','mold','damp','humidity'],
            'response' => "Pests & mold: 1) For pests, keep areas clean and store food in sealed containers; consider traps or professional pest control for infestations. 2) For mold, fix moisture sources, ventilate, and clean non-porous surfaces with appropriate cleaners; extensive mold should be handled by pros."
        ],
        // Garage / Tools
        [
            'keywords' => ['drill','sander','power tool','circular saw','tool','garage'],
            'response' => "Tools & safety: 1) Inspect tools for damaged cords or guards before using. 2) Use appropriate PPE (gloves, eye protection). 3) For unusual noises or smoke, stop immediately and inspect for jams or motor failure."
        ],
        // Vehicle basic
        [
            'keywords' => ['car','vehicle','engine','battery car','tyre','tire','flat tire','oil'],
            'response' => "Vehicle basics (general): 1) For flat tires, replace with spare and check pressure. 2) Check battery connections and alternator if car won't start. 3) For oil leaks or engine warnings, consult a mechanic — do not ignore dashboard warnings."
        ],
        // Generic device categories / symptoms (noise, smell, won't turn on, overheating, trip breaker)
        [
            'keywords' => ['noise','humming','buzzing','smell','burning smell','won\'t turn on','not turning on','trip','tripped','overheat','overheating'],
            'response' => "Symptom-based guidance: 1) For strange noises, stop the device and inspect for loose parts or foreign objects. 2) If you detect burning smells, unplug immediately and inspect for visible damage. 3) If a breaker trips repeatedly, disconnect devices and consult an electrician."
        ],
        // Safety and general guidance
        [
            'keywords' => ['safety','danger','fire','smoke','sparks'],
            'response' => "Safety first: If you smell gas, see smoke, or detect sparks, evacuate the area, shut off gas/electric supply if safe to do so, and call emergency services or a licensed professional immediately."
        ],
    ];

    // Add more small-targeted fallbacks (items & common household devices) programmatically.
    // For maintainability we build a list of succinct entries that map many device keywords to short but helpful responses.
    $compactEntries = [
        // kitchen appliances
        'rice cooker'         => "Rice cooker: ensure inner pot sits correctly and clean the steam vent regularly. If not heating, check power and fuse.",
        'pressure cooker'     => "Pressure cooker: ensure lid is locked and steam valve clean before heating. Never force open while under pressure.",
        'coffee maker'        => "Coffee maker: descale with vinegar solution periodically and clean the basket and carafe.",
        'blender'             => "Blender: check blade assembly and remove lodged items; unplug before inspecting.",
        'electric kettle'     => "Kettle: check scale buildup and clean; test the auto-off switch carefully.",
        'air fryer'           => "Air fryer: avoid overcrowding and clean the basket regularly to prevent smoke.",
        // electronics
        'speaker'             => "Speaker: check connections and source device; for distortion test different audio files and cables.",
        'router'              => "Router reminder: secure Wi-Fi with a password and keep firmware updated.",
        'nas'                 => "NAS/storage: ensure backups and check RAID status where applicable.",
        // plumbing smaller things
        'tap'                 => "Tap/faucet: check aerator and tighten fittings to stop minor drips.",
        'shower'              => "Shower: run a descaling cycle and check for low pressure due to mineral buildup.",
        // HVAC smalls
        'fan'                 => "Fan: clean blades and check motor bearings for noise.",
        'heater'              => "Space heater: keep clearance and avoid extension cords; inspect element for damage.",
        // cleaning & maintenance
        'filter'              => "Filter reminder: HVAC and appliance filters should be cleaned or replaced per manufacturer schedule.",
        'battery backup'      => "UPS/Battery backup: test and replace batteries per spec; ensure proper ventilation.",
        // garden / outdoor
        'lawn mower'          => "Lawn mower: check blades for damage and keep fuel fresh; disconnect spark plug before maintenance.",
        'sprinkler'           => "Sprinkler: check zones and replace broken heads; winterize where appropriate.",
        // pet / small household
        'vacuum'              => "Vacuum: check brush roll for hair wrap and clean filters.",
        'iron'                => "Clothes iron: descale reservoir and empty after use to prevent stains.",
        // other common items
        'doorbell'            => "Doorbell: check battery or transformer and wiring connections.",
        'garage door'         => "Garage door: inspect springs and sensors; serious repairs require pros.",
        'smoke detector'      => "Smoke detector: replace batteries annually and test monthly.",
    ];

    // Convert $compactEntries to the same structure as $FALLBACKS
    foreach ($compactEntries as $k => $resp) {
        $FALLBACKS[] = [
            'keywords' => array_filter(array_map('trim', explode(' ', $k))),
            'response' => $resp
        ];
    }

    // Fuzzy / substring match search
    foreach ($FALLBACKS as $entry) {
        foreach ($entry['keywords'] as $kw) {
            if ($kw === '') continue;
            if (mb_strpos($p_norm, mb_strtolower($kw, 'UTF-8')) !== false) {
                return $entry['response'];
            }
        }
    }

    // If no strict keyword hits, try symptom-category matching
    $symptoms = [
        'overheat' => ['overheat','overheated','overheating','hot'],
        'leak'     => ['leak','drip','water coming','spray','seep'],
        'noise'    => ['noise','buzz','rattle','humming','squeak','click'],
        'power'    => ['won\'t turn on','not turning on','no power','dead','not working','power off'],
        'smell'    => ['smell','burning','odor','smoke'],
        'slow'     => ['slow','lag','hang','freeze','unresponsive']
    ];

    foreach ($symptoms as $cat => $tokens) {
        foreach ($tokens as $t) {
            if (mb_strpos($p_norm, $t) !== false) {
                switch ($cat) {
                    case 'overheat':
                        return "Overheating devices: power down, unplug, and allow the device to cool. Check vents/fans for dust; if internal components are hot to touch, get professional servicing.";
                    case 'leak':
                        return "Leaks: stop the water source if safe to do so, contain the water, and arrange repairs. For gas or major water flow, shut off main and call a professional.";
                    case 'noise':
                        return "Strange noises: stop using the device and inspect for foreign objects, loose screws, or damaged bearings; if internal, consult a technician.";
                    case 'power':
                        return "Power problems: check circuit breakers, fuses, and power leads; try a different outlet or cable. If the device is dead but has no visible damage, professional diagnostics may be needed.";
                    case 'smell':
                        return "Burning or unusual smell: unplug immediately and ventilate the area. Do not use the device until a professional has inspected it.";
                    case 'slow':
                        return "Slow/unresponsive device: check for software updates, free disk space, and close background apps; backup important data before any major fixes.";
                }
            }
        }
    }

    // Generic helpful fallback if absolutely nothing matches
    $generic = <<<TXT
I cannot identify the exact device from offline cues. Here are safe general steps you can try:
1) Power: Ensure the device has power (check plugs, switches, breakers).
2) Simple fix: Restart the device and see if the issue persists.
3) Clean & inspect: Look for visible damage, dust, loose parts, water ingress.
4) Safety: If you smell burning, see smoke, or detect gas, stop, evacuate, and call emergency services or a licensed professional.
For more specific help, reconnect to the internet and try the online AI troubleshooter for detailed diagnostics.
TXT;

    return $generic;
}
