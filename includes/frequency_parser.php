<?php
function parse_custom_frequency($text)
{
    $text = strtolower(trim($text));

    // ==========================
    // 1) Simple weekly patterns
    // ==========================
    $weekdays = [
        "monday","tuesday","wednesday","thursday",
        "friday","saturday","sunday"
    ];

    foreach ($weekdays as $w) {
        if (strpos($text, "every $w") !== false) {
            return date("Y-m-d", strtotime("next $w"));
        }
    }

    // ==========================
    // 2) Pure “every week”
    // ==========================
    if ($text === "every week") {
        return date("Y-m-d", strtotime("+1 week"));
    }

    // ==========================
    // 3) Every X weeks
    // ==========================
    if (preg_match('/every (\d+) weeks?/', $text, $m)) {
        return date("Y-m-d", strtotime("+$m[1] weeks"));
    }

    // ==========================
    // 4) Every month
    // ==========================
    if ($text === "every month") {
        return date("Y-m-d", strtotime("+1 month"));
    }

    // ==========================
    // 5) Every X months
    // ==========================
    if (preg_match('/every (\d+) months?/', $text, $m)) {
        return date("Y-m-d", strtotime("+$m[1] months"));
    }

    // ==========================
    // 6) First Monday, First Friday, etc.
    // ==========================
    if (preg_match('/every first (monday|tuesday|wednesday|thursday|friday|saturday|sunday)/', $text, $m)) {
        return date("Y-m-d", strtotime("first {$m[1]} of next month"));
    }

    // fallback: return NULL → handled by caller
    return null;
}
?>
